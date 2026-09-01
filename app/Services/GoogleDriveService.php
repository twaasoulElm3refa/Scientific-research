<?php

namespace App\Services;

use App\Contracts\GoogleDrive;
use App\Exceptions\GoogleDriveException;
use Carbon\CarbonInterface;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use JsonException;

class GoogleDriveService implements GoogleDrive
{
    private const DRIVE_API_URL = 'https://www.googleapis.com/drive/v3';

    private const DRIVE_UPLOAD_URL = 'https://www.googleapis.com/upload/drive/v3';

    private const DRIVE_SCOPE = 'https://www.googleapis.com/auth/drive';

    public function uploadFile(UploadedFile $file, string $displayName, CarbonInterface $folderDate): array
    {
        $rootFolderId = $this->requiredConfig('folder_id');
        $yearFolderId = $this->ensureFolder($rootFolderId, $folderDate->format('Y'));
        $monthFolderId = $this->ensureFolder($yearFolderId, $folderDate->format('m'));
        $extension = strtolower($file->getClientOriginalExtension());
        $storedName = $this->buildSafeFileName($displayName, $extension);
        $metadata = [
            'name' => $storedName,
            'parents' => [$monthFolderId],
        ];
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';

        $sessionResponse = $this->authorizedRequest()
            ->timeout(60)
            ->withHeaders([
                'X-Upload-Content-Type' => $mimeType,
                'X-Upload-Content-Length' => (string) $file->getSize(),
            ])
            ->post(self::DRIVE_UPLOAD_URL.'/files?'.http_build_query([
                'uploadType' => 'resumable',
                'supportsAllDrives' => 'true',
                'fields' => 'id,name,mimeType,size,webViewLink,parents',
            ]), $metadata);

        $uploadUrl = $sessionResponse->header('Location');

        if ($sessionResponse->failed() || ! $uploadUrl) {
            throw new GoogleDriveException('Google Drive could not initialize the file upload.');
        }

        $stream = fopen($file->getRealPath(), 'rb');

        if ($stream === false) {
            throw new GoogleDriveException('The temporary upload could not be opened.');
        }

        $body = Utils::streamFor($stream);

        try {
            $response = $this->authorizedRequest()
                ->timeout(300)
                ->withHeaders(['Content-Length' => (string) $file->getSize()])
                ->withBody($body, $mimeType)
                ->put($uploadUrl);
        } finally {
            $body->close();
        }

        if ($response->failed()) {
            throw new GoogleDriveException('Google Drive rejected the file upload.');
        }

        $uploaded = $response->json();

        if (! is_array($uploaded) || empty($uploaded['id'])) {
            throw new GoogleDriveException('Google Drive returned an invalid upload response.');
        }

        return [
            'id' => (string) $uploaded['id'],
            'name' => (string) ($uploaded['name'] ?? $storedName),
            'folder_id' => $monthFolderId,
            'mime_type' => isset($uploaded['mimeType']) ? (string) $uploaded['mimeType'] : $file->getMimeType(),
            'size' => isset($uploaded['size']) ? (int) $uploaded['size'] : $file->getSize(),
            'web_view_link' => isset($uploaded['webViewLink']) ? (string) $uploaded['webViewLink'] : null,
        ];
    }

    public function deleteFile(string $fileId): void
    {
        $response = $this->authorizedRequest()
            ->timeout(60)
            ->delete(self::DRIVE_API_URL.'/files/'.rawurlencode($fileId).'?supportsAllDrives=true');

        if ($response->failed() && $response->status() !== 404) {
            throw new GoogleDriveException('Google Drive could not delete the uploaded file.');
        }
    }

    public function getFileMetadata(string $fileId): array
    {
        $response = $this->authorizedRequest()
            ->timeout(60)
            ->get(self::DRIVE_API_URL.'/files/'.rawurlencode($fileId), [
                'supportsAllDrives' => 'true',
                'fields' => 'id,name,mimeType,size,webViewLink,parents,trashed',
            ]);

        if ($response->failed()) {
            throw new GoogleDriveException('Google Drive metadata could not be retrieved.');
        }

        return $response->json();
    }

    public function ensureFolder(string $parentFolderId, string $name): string
    {
        $cacheKey = 'google-drive.folder.'.sha1($parentFolderId.'|'.$name);

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($parentFolderId, $name) {
            $params = [
                'q' => sprintf(
                    "mimeType = 'application/vnd.google-apps.folder' and name = '%s' and '%s' in parents and trashed = false",
                    $this->escapeDriveQueryValue($name),
                    $this->escapeDriveQueryValue($parentFolderId),
                ),
                'fields' => 'files(id,name)',
                'pageSize' => 1,
                'spaces' => 'drive',
                'supportsAllDrives' => 'true',
                'includeItemsFromAllDrives' => 'true',
            ];

            $sharedDriveId = $this->requiredConfig('shared_drive_id');
            $params['corpora'] = 'drive';
            $params['driveId'] = $sharedDriveId;

            $response = $this->authorizedRequest()
                ->timeout(60)
                ->get(self::DRIVE_API_URL.'/files', $params);

            if ($response->failed()) {
                throw new GoogleDriveException('Google Drive folders could not be searched.');
            }

            $existingId = data_get($response->json(), 'files.0.id');

            if ($existingId) {
                return (string) $existingId;
            }

            $createResponse = $this->authorizedRequest()
                ->timeout(60)
                ->post(self::DRIVE_API_URL.'/files?supportsAllDrives=true', [
                    'name' => $name,
                    'mimeType' => 'application/vnd.google-apps.folder',
                    'parents' => [$parentFolderId],
                ]);

            $folderId = $createResponse->json('id');

            if ($createResponse->failed() || ! $folderId) {
                throw new GoogleDriveException('Google Drive could not create the document folder.');
            }

            return (string) $folderId;
        });
    }

    public function buildSafeFileName(string $displayName, string $extension): string
    {
        $safeBaseName = preg_replace('/[^A-Za-z0-9._-]+/', '-', Str::ascii($displayName));
        $safeBaseName = trim((string) $safeBaseName, '.-_');
        $safeBaseName = Str::limit($safeBaseName ?: 'document', 160, '');
        $safeExtension = preg_replace('/[^a-z0-9]+/', '', strtolower($extension));

        return Str::uuid().'-'.$safeBaseName.($safeExtension ? '.'.$safeExtension : '');
    }

    private function authorizedRequest(): PendingRequest
    {
        return Http::acceptJson()
            ->asJson()
            ->withToken($this->accessToken());
    }

    private function accessToken(): string
    {
        $credentials = $this->credentials();
        $cacheKey = 'google-drive.service-account-token.'.sha1($credentials['client_email']);
        $cachedToken = Cache::get($cacheKey);

        if (is_string($cachedToken) && $cachedToken !== '') {
            return $cachedToken;
        }

        $now = time();
        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ], JSON_THROW_ON_ERROR));
        $claims = $this->base64UrlEncode(json_encode([
            'iss' => $credentials['client_email'],
            'scope' => self::DRIVE_SCOPE,
            'aud' => $credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ], JSON_THROW_ON_ERROR));
        $unsignedToken = $header.'.'.$claims;

        if (! openssl_sign($unsignedToken, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256)) {
            throw new GoogleDriveException('The Google service-account token could not be signed.');
        }

        $assertion = $unsignedToken.'.'.$this->base64UrlEncode($signature);
        $response = Http::asForm()->acceptJson()->timeout(30)->post(
            $credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token',
            [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $assertion,
            ],
        );

        $token = $response->json('access_token');

        if ($response->failed() || ! is_string($token) || $token === '') {
            throw new GoogleDriveException('Google service-account authentication failed.');
        }

        Cache::put($cacheKey, $token, max(60, ((int) $response->json('expires_in', 3600)) - 60));

        return $token;
    }

    /**
     * @return array{client_email: string, private_key: string, token_uri?: string}
     */
    private function credentials(): array
    {
        $configuredValue = $this->requiredConfig('service_account_json');
        $json = is_file($configuredValue) ? file_get_contents($configuredValue) : $configuredValue;

        if (! is_string($json) || $json === '') {
            throw new GoogleDriveException('The Google service-account credential file could not be read.');
        }

        try {
            $credentials = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new GoogleDriveException('The Google service-account JSON is invalid.', 0, $exception);
        }

        if (! is_array($credentials) || empty($credentials['client_email']) || empty($credentials['private_key'])) {
            throw new GoogleDriveException('The Google service-account credentials are incomplete.');
        }

        return $credentials;
    }

    private function requiredConfig(string $key): string
    {
        $value = config('services.google_drive.'.$key);

        if (! is_string($value) || trim($value) === '') {
            throw new GoogleDriveException('Google Drive is not configured.');
        }

        return trim($value);
    }

    private function escapeDriveQueryValue(string $value): string
    {
        return str_replace(['\\', "'"], ['\\\\', "\\'"], $value);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
