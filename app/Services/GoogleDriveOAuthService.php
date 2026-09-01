<?php

namespace App\Services;

use App\Exceptions\GoogleDriveException;
use App\Models\GoogleDriveConnection;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleDriveOAuthService
{
    private const AUTHORIZATION_URL = 'https://accounts.google.com/o/oauth2/v2/auth';

    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const REVOCATION_URL = 'https://oauth2.googleapis.com/revoke';

    public function authorizationUrl(User $admin): string
    {
        $this->requiredConfig('client_id');
        $this->requiredConfig('client_secret');
        $state = Str::random(80);

        Cache::put($this->stateCacheKey($state), $admin->getKey(), now()->addMinutes(10));

        return self::AUTHORIZATION_URL.'?'.http_build_query([
            'client_id' => $this->requiredConfig('client_id'),
            'redirect_uri' => $this->requiredConfig('redirect_uri'),
            'response_type' => 'code',
            'scope' => $this->scopes(),
            'access_type' => 'offline',
            'include_granted_scopes' => 'true',
            'prompt' => 'consent',
            'state' => $state,
        ]);
    }

    public function consumeState(string $state): User
    {
        $userId = Cache::pull($this->stateCacheKey($state));
        $admin = is_numeric($userId) ? User::query()->find((int) $userId) : null;

        if (! $admin || $admin->role !== 'admin' || ! $admin->is_active) {
            throw new GoogleDriveException('The Google Drive connection request has expired. Please try again.');
        }

        return $admin;
    }

    public function connect(User $admin, string $authorizationCode): GoogleDriveConnection
    {
        $response = $this->postForm(self::TOKEN_URL, [
            'code' => $authorizationCode,
            'client_id' => $this->requiredConfig('client_id'),
            'client_secret' => $this->requiredConfig('client_secret'),
            'redirect_uri' => $this->requiredConfig('redirect_uri'),
            'grant_type' => 'authorization_code',
        ], 'Google Drive authorization service could not be reached. Please try again.');

        $accessToken = $response->json('access_token');
        $refreshToken = $response->json('refresh_token');

        if ($response->failed() || ! is_string($accessToken) || $accessToken === '') {
            throw new GoogleDriveException('Google Drive authorization could not be completed. Please try again.');
        }

        if (! is_string($refreshToken) || $refreshToken === '') {
            throw new GoogleDriveException('Google did not return long-term access. Revoke the app in your Google Account and connect again.');
        }

        return GoogleDriveConnection::query()->updateOrCreate(
            ['provider' => GoogleDriveConnection::PROVIDER],
            [
                'user_id' => $admin->getKey(),
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'expires_at' => now()->addSeconds(max(60, (int) $response->json('expires_in', 3600))),
            ],
        );
    }

    public function accessToken(bool $forceRefresh = false): string
    {
        $accessToken = DB::transaction(function () use ($forceRefresh) {
            $connection = GoogleDriveConnection::query()
                ->where('provider', GoogleDriveConnection::PROVIDER)
                ->lockForUpdate()
                ->first();

            if (! $connection || ! is_string($connection->refresh_token) || $connection->refresh_token === '') {
                throw new GoogleDriveException('Google Drive is not connected. Connect it from Admin settings.');
            }

            if (! $forceRefresh
                && is_string($connection->access_token)
                && $connection->access_token !== ''
                && $connection->expires_at?->isAfter(now()->addMinute())) {
                return $connection->access_token;
            }

            $response = $this->postForm(self::TOKEN_URL, [
                'client_id' => $this->requiredConfig('client_id'),
                'client_secret' => $this->requiredConfig('client_secret'),
                'refresh_token' => $connection->refresh_token,
                'grant_type' => 'refresh_token',
            ], 'Google Drive authorization service could not be reached. Please try again.');
            $accessToken = $response->json('access_token');

            if ($response->failed() || ! is_string($accessToken) || $accessToken === '') {
                if ($response->json('error') === 'invalid_grant') {
                    $connection->delete();

                    return null;
                }

                throw new GoogleDriveException('Google Drive access could not be refreshed. Please try again.');
            }

            $updates = [
                'access_token' => $accessToken,
                'expires_at' => now()->addSeconds(max(60, (int) $response->json('expires_in', 3600))),
            ];
            $rotatedRefreshToken = $response->json('refresh_token');

            if (is_string($rotatedRefreshToken) && $rotatedRefreshToken !== '') {
                $updates['refresh_token'] = $rotatedRefreshToken;
            }

            $connection->update($updates);

            return $accessToken;
        });

        if (! is_string($accessToken) || $accessToken === '') {
            throw new GoogleDriveException('Google Drive authorization has expired. Reconnect it from Admin settings.');
        }

        return $accessToken;
    }

    public function disconnect(): void
    {
        $connection = $this->connection();

        if (! $connection) {
            return;
        }

        $token = $connection->refresh_token ?: $connection->access_token;

        if (is_string($token) && $token !== '') {
            $response = $this->postForm(self::REVOCATION_URL, [
                'token' => $token,
            ], 'Google Drive revocation service could not be reached. Please try again.');

            if ($response->failed() && $response->status() !== 400) {
                throw new GoogleDriveException('Google Drive could not be disconnected. Please try again.');
            }
        }

        $connection->delete();
    }

    public function connection(): ?GoogleDriveConnection
    {
        return GoogleDriveConnection::query()
            ->with('user:id,name,email')
            ->where('provider', GoogleDriveConnection::PROVIDER)
            ->first();
    }

    private function scopes(): string
    {
        $configured = $this->requiredConfig('scopes');
        $scopes = preg_split('/[\s,]+/', $configured, -1, PREG_SPLIT_NO_EMPTY);

        if (! is_array($scopes) || $scopes === []) {
            throw new GoogleDriveException('Google Drive OAuth scopes are not configured.');
        }

        return implode(' ', array_unique($scopes));
    }

    private function requiredConfig(string $key): string
    {
        $value = config('services.google_drive.'.$key);

        if (! is_string($value) || trim($value) === '') {
            throw new GoogleDriveException('Google Drive OAuth is not configured.');
        }

        return trim($value);
    }

    private function stateCacheKey(string $state): string
    {
        return 'google-drive.oauth-state.'.hash('sha256', $state);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function postForm(string $url, array $data, string $connectionError): Response
    {
        try {
            return Http::asForm()->acceptJson()->timeout(30)->post($url, $data);
        } catch (ConnectionException $exception) {
            throw new GoogleDriveException($connectionError, previous: $exception);
        }
    }
}
