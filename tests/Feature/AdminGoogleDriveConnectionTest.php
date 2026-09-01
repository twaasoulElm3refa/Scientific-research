<?php

namespace Tests\Feature;

use App\Exceptions\GoogleDriveException;
use App\Models\GoogleDriveConnection;
use App\Models\User;
use App\Services\GoogleDriveOAuthService;
use App\Services\GoogleDriveService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminGoogleDriveConnectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set([
            'app.url' => 'http://localhost',
            'services.google_drive.client_id' => 'oauth-client.apps.googleusercontent.com',
            'services.google_drive.client_secret' => 'oauth-client-secret',
            'services.google_drive.redirect_uri' => 'http://localhost/api/admin/google-drive/callback',
            'services.google_drive.folder_id' => 'root-folder-id',
            'services.google_drive.scopes' => 'https://www.googleapis.com/auth/drive',
        ]);

        Cache::flush();
    }

    public function test_admin_can_complete_oauth_connection_and_tokens_are_encrypted(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'plain-access-token',
                'refresh_token' => 'plain-refresh-token',
                'expires_in' => 3600,
            ]),
        ]);

        $authorize = $this->withToken($admin->createToken('oauth')->plainTextToken)
            ->postJson('/api/admin/google-drive/authorize')
            ->assertOk();

        $authorizationUrl = $authorize->json('authorization_url');
        parse_str((string) parse_url($authorizationUrl, PHP_URL_QUERY), $query);

        $this->assertSame('oauth-client.apps.googleusercontent.com', $query['client_id']);
        $this->assertSame('offline', $query['access_type']);
        $this->assertSame('consent', $query['prompt']);
        $this->assertSame('https://www.googleapis.com/auth/drive', $query['scope']);
        $this->assertNotEmpty($query['state']);
        $this->assertStringNotContainsString('oauth-client-secret', $authorizationUrl);

        $this->get('/api/admin/google-drive/callback?'.http_build_query([
            'state' => $query['state'],
            'code' => 'authorization-code',
        ]))->assertRedirect('http://localhost/admin/settings/google-drive?google_drive=connected');

        $connection = GoogleDriveConnection::query()->firstOrFail();
        $this->assertSame($admin->id, $connection->user_id);
        $this->assertSame('plain-access-token', $connection->access_token);
        $this->assertSame('plain-refresh-token', $connection->refresh_token);

        $stored = DB::table('google_drive_connections')->first();
        $this->assertNotSame('plain-access-token', $stored->access_token);
        $this->assertNotSame('plain-refresh-token', $stored->refresh_token);

        $this->withToken($admin->createToken('status')->plainTextToken)
            ->getJson('/api/admin/google-drive')
            ->assertOk()
            ->assertJsonPath('connected', true)
            ->assertJsonPath('connected_by.email', $admin->email)
            ->assertJsonMissingPath('access_token')
            ->assertJsonMissingPath('refresh_token');

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://oauth2.googleapis.com/token'
            && $request['grant_type'] === 'authorization_code'
            && $request['code'] === 'authorization-code'
        );
    }

    public function test_expired_access_token_is_refreshed_and_persisted(): void
    {
        $connection = $this->connection([
            'access_token' => 'expired-token',
            'refresh_token' => 'valid-refresh-token',
            'expires_at' => now()->subMinute(),
        ]);
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'fresh-access-token',
                'expires_in' => 1800,
            ]),
        ]);

        $this->assertSame('fresh-access-token', app(GoogleDriveOAuthService::class)->accessToken());
        $this->assertSame('fresh-access-token', $connection->fresh()->access_token);
        $this->assertTrue($connection->fresh()->expires_at->isFuture());

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://oauth2.googleapis.com/token'
            && $request['grant_type'] === 'refresh_token'
            && $request['refresh_token'] === 'valid-refresh-token'
        );
    }

    public function test_invalid_refresh_token_removes_connection_and_returns_clean_error(): void
    {
        $this->connection(['expires_at' => now()->subMinute()]);
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'error' => 'invalid_grant',
                'error_description' => 'sensitive provider detail',
            ], 400),
        ]);

        try {
            app(GoogleDriveOAuthService::class)->accessToken();
            $this->fail('Expected an expired authorization exception.');
        } catch (GoogleDriveException $exception) {
            $this->assertSame(
                'Google Drive authorization has expired. Reconnect it from Admin settings.',
                $exception->getMessage(),
            );
            $this->assertStringNotContainsString('sensitive provider detail', $exception->getMessage());
        }

        $this->assertDatabaseCount('google_drive_connections', 0);
    }

    public function test_my_drive_upload_uses_oauth_without_shared_drive_parameters(): void
    {
        $this->connection();
        Http::fake([
            'https://www.googleapis.com/upload/drive/v3/files*' => Http::response(
                [],
                200,
                ['Location' => 'https://upload.example/session'],
            ),
            'https://upload.example/*' => Http::response([
                'id' => 'uploaded-file-id',
                'name' => 'stored-document.pdf',
                'mimeType' => 'application/pdf',
                'size' => '18',
                'webViewLink' => 'https://drive.google.com/file/d/uploaded-file-id/view',
                'parents' => ['month-folder-id'],
            ]),
            'https://www.googleapis.com/drive/v3/files*' => Http::sequence()
                ->push(['files' => [['id' => 'year-folder-id', 'name' => '2026']]])
                ->push(['files' => [['id' => 'month-folder-id', 'name' => '04']]]),
        ]);

        $file = UploadedFile::fake()->createWithContent('research.pdf', "%PDF-1.4\ncontent");
        $uploaded = app(GoogleDriveService::class)->uploadFile(
            $file,
            'Research Document',
            CarbonImmutable::create(2026, 4, 1),
        );

        $this->assertSame('uploaded-file-id', $uploaded['id']);
        $this->assertSame('month-folder-id', $uploaded['folder_id']);
        $this->assertSame('https://drive.google.com/file/d/uploaded-file-id/view', $uploaded['web_view_link']);

        foreach (Http::recorded() as [$request]) {
            if (! str_starts_with($request->url(), 'https://www.googleapis.com/')) {
                continue;
            }

            $urlAndData = $request->url().' '.json_encode($request->data());

            $this->assertTrue($request->hasHeader('Authorization', 'Bearer current-access-token'));
            $this->assertStringNotContainsString('supportsAllDrives', $urlAndData);
            $this->assertStringNotContainsString('includeItemsFromAllDrives', $urlAndData);
            $this->assertStringNotContainsString('driveId', $urlAndData);
            $this->assertStringNotContainsString('corpora', $urlAndData);
        }
    }

    public function test_non_admin_and_unauthenticated_users_cannot_manage_connection(): void
    {
        $user = User::factory()->create(['role' => 'user', 'is_active' => true]);

        $this->postJson('/api/admin/google-drive/authorize')->assertUnauthorized();
        $this->getJson('/api/admin/google-drive')->assertUnauthorized();

        $token = $user->createToken('not-admin')->plainTextToken;
        $this->withToken($token)->postJson('/api/admin/google-drive/authorize')->assertForbidden();
        $this->withToken($token)->postJson('/api/admin/google-drive/refresh')->assertForbidden();
        $this->withToken($token)->deleteJson('/api/admin/google-drive')->assertForbidden();
    }

    public function test_invalid_oauth_credentials_are_handled_without_storing_tokens(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'error' => 'invalid_client',
                'error_description' => 'do not expose this provider response',
            ], 401),
        ]);

        $authorize = $this->withToken($admin->createToken('oauth')->plainTextToken)
            ->postJson('/api/admin/google-drive/authorize');
        parse_str((string) parse_url($authorize->json('authorization_url'), PHP_URL_QUERY), $query);

        $this->get('/api/admin/google-drive/callback?'.http_build_query([
            'state' => $query['state'],
            'code' => 'invalid-code',
        ]))->assertRedirect('http://localhost/admin/settings/google-drive?google_drive=failed');

        $this->assertDatabaseCount('google_drive_connections', 0);
    }

    public function test_denied_oauth_request_returns_to_settings_without_connecting(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $authorize = $this->withToken($admin->createToken('oauth')->plainTextToken)
            ->postJson('/api/admin/google-drive/authorize');
        parse_str((string) parse_url($authorize->json('authorization_url'), PHP_URL_QUERY), $query);

        $this->get('/api/admin/google-drive/callback?'.http_build_query([
            'state' => $query['state'],
            'error' => 'access_denied',
        ]))->assertRedirect('http://localhost/admin/settings/google-drive?google_drive=denied');

        $this->assertDatabaseCount('google_drive_connections', 0);
        Http::assertNothingSent();
    }

    public function test_admin_can_revoke_and_disconnect_google_drive(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->connection(['user_id' => $admin->id]);
        Http::fake([
            'https://oauth2.googleapis.com/revoke' => Http::response([], 200),
        ]);

        $this->withToken($admin->createToken('disconnect')->plainTextToken)
            ->deleteJson('/api/admin/google-drive')
            ->assertOk()
            ->assertJsonPath('message', 'Google Drive disconnected successfully.');

        $this->assertDatabaseCount('google_drive_connections', 0);
        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://oauth2.googleapis.com/revoke'
            && $request['token'] === 'valid-refresh-token'
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function connection(array $attributes = []): GoogleDriveConnection
    {
        return GoogleDriveConnection::query()->create(array_merge([
            'provider' => GoogleDriveConnection::PROVIDER,
            'access_token' => 'current-access-token',
            'refresh_token' => 'valid-refresh-token',
            'expires_at' => now()->addHour(),
        ], $attributes));
    }
}
