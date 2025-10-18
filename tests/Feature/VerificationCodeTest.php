<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class VerificationCodeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Cache::flush();
    }

    /** @test */
    public function can_send_verification_code_to_email(): void
    {
        $response = $this->postJson('/api/auth/verification-code', [
            'email' => 'test@example.com',
            'scene' => 'login_email',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sent', true);

        // Verify code is cached
        $cacheKey = 'verify_code:test@example.com';
        $this->assertTrue(Cache::has($cacheKey));
        $this->assertEquals('111111', Cache::get($cacheKey));
    }

    /** @test */
    public function can_send_verification_code_to_mobile(): void
    {
        $response = $this->postJson('/api/auth/verification-code', [
            'mobile' => '13800138000',
            'scene' => 'login_email',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sent', true);

        // Verify code is cached
        $cacheKey = 'verify_code:13800138000';
        $this->assertTrue(Cache::has($cacheKey));
        $this->assertEquals('111111', Cache::get($cacheKey));
    }

    /** @test */
    public function requires_email_or_mobile(): void
    {
        $response = $this->postJson('/api/auth/verification-code', [
            'scene' => 'login_email',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 422)
            ->assertJsonValidationErrors(['mobile', 'email']);
    }

    /** @test */
    public function validates_mobile_format(): void
    {
        $response = $this->postJson('/api/auth/verification-code', [
            'mobile' => '123456',
            'scene' => 'login_email',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 422)
            ->assertJsonValidationErrors(['mobile']);
    }

    /** @test */
    public function validates_email_format(): void
    {
        $response = $this->postJson('/api/auth/verification-code', [
            'email' => 'not-an-email',
            'scene' => 'login_email',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function validates_scene_values(): void
    {
        $response = $this->postJson('/api/auth/verification-code', [
            'email' => 'test@example.com',
            'scene' => 'invalid_scene',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 422)
            ->assertJsonValidationErrors(['scene']);
    }
}
