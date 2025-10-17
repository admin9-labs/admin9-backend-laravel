<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_can_register_with_username_and_password(): void
    {
        $userData = [
            'name' => 'testuser123',
            'password' => 'Password123!',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'token_type',
                    'expires_in',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'testuser123',
        ]);
    }

    public function test_user_registration_validates_required_fields(): void
    {
        $response = $this->postJson('/api/auth/register', []);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
                'code' => 422,
            ])
            ->assertJsonStructure([
                'errors' => ['name', 'password']
            ]);
    }

    public function test_user_registration_validates_unique_username(): void
    {
        User::factory()->create(['name' => 'existinguser']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'existinguser',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
                'code' => 422,
            ])
            ->assertJsonStructure([
                'errors' => ['name']
            ]);
    }

    public function test_user_can_login_with_account(): void
    {
        $user = User::factory()->create([
            'name' => 'testuser',
            'password' => 'Password123!',
        ]);

        $response = $this->postJson('/api/auth/login/account', [
            'name' => 'testuser',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'token_type',
                    'expires_in',
                ]
            ]);
    }

    public function test_user_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'name' => 'testuser',
            'password' => 'Password123!',
        ]);

        $response = $this->postJson('/api/auth/login/account', [
            'name' => 'testuser',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
                'code' => 422,
            ]);
    }

    public function test_user_can_login_with_mobile_verification_code(): void
    {
        Cache::put('verify_code:13812345678', '123456', 600);

        $response = $this->postJson('/api/auth/login/mobile', [
            'mobile' => '13812345678',
            'code' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'token_type',
                    'expires_in',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'mobile' => '13812345678',
        ]);
    }

    public function test_user_can_login_with_email_verification_code(): void
    {
        Cache::put('verify_code:test@example.com', '123456', 600);

        $response = $this->postJson('/api/auth/login/email', [
            'email' => 'test@example.com',
            'code' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'token_type',
                    'expires_in',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = auth('user')->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_set_password(): void
    {
        // Create a user through mobile login (users created this way have auto-generated passwords)
        $user = User::factory()->create(['mobile' => '13812345678']);
        $token = auth('user')->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/set-password', [
            'password' => 'NewPassword123!',
        ]);

        $response->assertStatus(200)
            ->assertJson(['data' => '密码设置成功']);
    }

    public function test_authenticated_user_can_change_password_with_current_password(): void
    {
        $user = User::factory()->create(['password' => 'OldPassword123!']);
        $token = auth('user')->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/change-password', [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword123!',
        ]);

        $response->assertStatus(200)
            ->assertJson(['data' => '密码修改成功']);
    }
}
