<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function register_login_refresh_change_password_and_old_password_fails(): void
    {
        // 注册
        $register = $this->postJson('/api/auth/register', [
            'name' => 'tester',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ])->assertStatus(200)
            ->assertJsonPath('data.user.name', 'tester');

        $token = $register->json('data.token');
        $this->assertNotEmpty($token);

        // 密码登录
        $login = $this->postJson('/api/auth/login', [
            'name' => 'tester',
            'password' => 'Password123',
        ])->assertStatus(200);
        $loginToken = $login->json('data.token');
        $this->assertNotEmpty($loginToken);

        // 刷新 token（需要带上 Authorization）
        $refresh = $this->postJson('/api/auth/refresh', [], [
            'Authorization' => 'Bearer '.$loginToken,
        ])->assertStatus(200);
        $newToken = $refresh->json('data.token');
        $this->assertNotEmpty($newToken);

        // 修改密码
        $change = $this->patchJson('/api/user/password', [
            'current_password' => 'Password123',
            'password' => 'NewPassword456',
            'password_confirmation' => 'NewPassword456',
        ], [
            'Authorization' => 'Bearer '.$newToken,
        ])->assertStatus(200)
            ->assertJsonPath('data.updated', true);

        // 使用旧密码登录应失败
        $this->postJson('/api/auth/login', [
            'name' => 'tester',
            'password' => 'Password123',
        ])->assertStatus(200) // 统一返回 200 包装 deny，具体看实现
            ->assertJsonPath('data.success', false);

        // 使用新密码登录成功
        $this->postJson('/api/auth/login', [
            'name' => 'tester',
            'password' => 'NewPassword456',
        ])->assertStatus(200)
            ->assertJsonPath('data.success', true);
    }
}
