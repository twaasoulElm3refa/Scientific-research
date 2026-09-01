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

class GoogleDriveService implements GoogleDrive
{
    private const DRIVE_API_URL = 'https://www.googleapis.com/drive/v3';

    private const DRIVE_UPLOAD_URL = 'https://www.googleapis.com/upload/drive/v3';

    public function __construct(private readonly GoogleDriveOAuthService $oauth) {}

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
            ->delete(self::DRIVE_API_URL.'/files/'.rawurlencode($fileId));

        if ($response->failed() && $response->status() !== 404) {
            throw new GoogleDriveException('Google Drive could not delete the uploaded file.');
        }
    }

    public function getFileMetadata(string $fileId): array
    {
        $response = $this->authorizedRequest()
            ->timeout(60)
            ->get(self::DRIVE_API_URL.'/files/'.rawurlencode($fileId), [
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
            $response = $this->authorizedRequest()
                ->timeout(60)
                ->get(self::DRIVE_API_URL.'/files', [
                    'q' => sprintf(
                        "mimeType = 'application/vnd.google-apps.folder' and name = '%s' and '%s' in parents and trashed = false",
                        $this->escapeDriveQueryValue($name),
                        $this->escapeDriveQueryValue($parentFolderId),
                    ),
                    'fields' => 'files(id,name)',
                    'pageSize' => 1,
                    'spaces' => 'drive',
                ]);

            if ($response->failed()) {
                throw new GoogleDriveException('Google Drive folders could not be searched.');
            }

            $existingId = data_get($response->json(), 'files.0.id');

            if ($existingId) {
                return (string) $existingId;
            }

            $createResponse = $this->authorizedRequest()
                ->timeout(60)
                ->post(self::DRIVE_API_URL.'/files', [
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
            ->withToken($this->oauth->accessToken());
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
}
