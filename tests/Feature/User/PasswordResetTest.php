<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_can_request_password_reset_with_email(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson(['data' => '验证码已发送']);
    }

    public function test_user_can_request_password_reset_with_mobile(): void
    {
        $user = User::factory()->create(['mobile' => '13812345678']);

        $response = $this->postJson('/api/auth/forgot-password', [
            'mobile' => '13812345678',
        ]);

        $response->assertStatus(200)
            ->assertJson(['data' => '验证码已发送']);
    }

    public function test_password_reset_request_validates_existing_email(): void
    {
        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_password_reset_request_validates_existing_mobile(): void
    {
        $response = $this->postJson('/api/auth/forgot-password', [
            'mobile' => '13812345678',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['mobile']);
    }

    public function test_user_can_reset_password_with_valid_code(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        Cache::put('reset_code:test@example.com', '123456', 600);

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => 'test@example.com',
            'code' => '123456',
            'password' => 'NewPassword123!',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => '密码重置成功']);
    }

    public function test_password_reset_fails_with_invalid_code(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => 'test@example.com',
            'code' => '999999',
            'password' => 'NewPassword123!',
        ]);

        $response->assertStatus(422);
    }

    public function test_password_reset_with_mobile_and_valid_code(): void
    {
        $user = User::factory()->create(['mobile' => '13812345678']);
        Cache::put('reset_code:13812345678', '123456', 600);

        $response = $this->postJson('/api/auth/reset-password', [
            'mobile' => '13812345678',
            'code' => '123456',
            'password' => 'NewPassword123!',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => '密码重置成功']);
    }

    public function test_password_reset_validates_password_strength(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        Cache::put('reset_code:test@example.com', '123456', 600);

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => 'test@example.com',
            'code' => '123456',
            'password' => '123', // Too weak
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
