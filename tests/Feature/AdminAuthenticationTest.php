<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_admin_can_log_in_and_receive_a_sanctum_token(): void
    {
        $admin = $this->createUser('admin', true);

        $response = $this->postJson('/api/admin/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Login successful.')
            ->assertJsonPath('user.id', $admin->id)
            ->assertJsonPath('user.name', $admin->name)
            ->assertJsonPath('user.email', $admin->email)
            ->assertJsonPath('user.role', 'admin')
            ->assertJsonStructure(['message', 'token', 'user' => ['id', 'name', 'email', 'role']])
            ->assertJsonMissingPath('user.password')
            ->assertJsonMissingPath('user.remember_token');

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_invalid_credentials_return_a_generic_unauthorized_response(): void
    {
        $admin = $this->createUser('admin', true);

        $this->postJson('/api/admin/login', [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ])->assertUnauthorized()->assertExactJson([
            'message' => 'Invalid email or password.',
        ]);

        $this->postJson('/api/admin/login', [
            'email' => 'missing@example.com',
            'password' => 'wrong-password',
        ])->assertUnauthorized()->assertExactJson([
            'message' => 'Invalid email or password.',
        ]);
    }

    public function test_non_admin_cannot_log_in_to_the_admin_panel(): void
    {
        $user = $this->createUser('user', true);

        $this->postJson('/api/admin/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertForbidden()->assertExactJson([
            'message' => 'You are not authorized to access the admin panel.',
        ]);
    }

    public function test_inactive_admin_cannot_log_in(): void
    {
        $admin = $this->createUser('admin', false);

        $this->postJson('/api/admin/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertForbidden()->assertExactJson([
            'message' => 'Your account is inactive.',
        ]);
    }

    public function test_login_validation_errors_return_unprocessable_entity(): void
    {
        $this->postJson('/api/admin/login', [
            'email' => 'not-an-email',
            'password' => '',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/admin/me')->assertUnauthorized();
    }

    public function test_me_rejects_a_non_admin_token(): void
    {
        $user = $this->createUser('user', true);
        $userToken = $user->createToken('test')->plainTextToken;
        $this->withToken($userToken)->getJson('/api/admin/me')
            ->assertForbidden()
            ->assertExactJson(['message' => 'Admin access required.']);
    }

    public function test_me_rejects_an_inactive_admin_token(): void
    {
        $inactiveAdmin = $this->createUser('admin', false);
        $inactiveToken = $inactiveAdmin->createToken('test')->plainTextToken;
        $this->withToken($inactiveToken)->getJson('/api/admin/me')
            ->assertForbidden()
            ->assertExactJson(['message' => 'Your account is inactive.']);
    }

    public function test_me_returns_only_safe_admin_data(): void
    {
        $admin = $this->createUser('admin', true);
        $token = $admin->createToken('admin-panel')->plainTextToken;

        $this->withToken($token)->getJson('/api/admin/me')
            ->assertOk()
            ->assertExactJson([
                'user' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'role' => 'admin',
                ],
            ]);
    }

    public function test_logout_deletes_only_the_current_access_token(): void
    {
        $admin = $this->createUser('admin', true);
        $currentToken = $admin->createToken('admin-panel')->plainTextToken;
        $otherToken = $admin->createToken('other-device')->plainTextToken;
        $currentTokenId = (int) explode('|', $currentToken, 2)[0];
        $otherTokenId = (int) explode('|', $otherToken, 2)[0];

        $this->withToken($currentToken)->postJson('/api/admin/logout')
            ->assertOk()
            ->assertExactJson(['message' => 'Logged out successfully.']);

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $currentTokenId]);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $otherTokenId]);
    }

    private function createUser(string $role, bool $isActive): User
    {
        return User::factory()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => $role,
            'is_active' => $isActive,
        ]);
    }
}
