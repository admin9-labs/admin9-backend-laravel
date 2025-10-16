<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

class AuthService
{
    /**
     * 注册
     */
    public function register(array $data): User
    {
        return User::create($data);
    }

    /**
     * 登录（账号密码）
     */
    public function loginByAccount(JWTGuard $auth, array $credentials): JsonResponse
    {
        if (! $auth->attempt($credentials)) {
            abort(422, '账户或密码错误');
        }

        return $this->respondWithToken($auth, $auth->user());
    }

    /**
     * 手机号登录
     */
    public function loginByMobile(string $mobile, string $code): User
    {
        $this->verifyOrFail($mobile, $code);

        return User::firstOrCreate(
            ['mobile' => $mobile],
            [
                'name' => $this->generateUsername(),
                'password' => Str::password(12),
            ]
        );
    }

    /**
     * 邮箱登录
     */
    public function loginByEmail(string $email, string $code): User
    {
        $email = strtolower($email);
        $this->verifyOrFail($email, $code);

        return User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $this->generateUsername(),
                'password' => Str::password(12),
                'email_verified_at' => now(),
            ]
        );
    }

    /**
     * 修改密码
     */
    public function changePassword(array $data): void
    {
        $user = user();

        if (! $this->verifyByAny($user, $data)) {
            abort(422, '验证失败');
        }

        $user->update(['password' => $data['password']]);
    }

    /**
     * 绑定手机号或邮箱
     */
    public function bindContact(array $data): void
    {
        $user = auth('user')->user();
        $field = isset($data['mobile']) ? 'mobile' : 'email';
        $value = strtolower($data[$field]);

        $this->verifyOrFail($value, $data['code']);

        if ($user->$field) {
            abort(422, '您已绑定过'.$this->label($field));
        }

        $user->update([$field => $value]);
    }

    /**
     * 解绑手机号或邮箱
     */
    public function unbindContact(array $data): void
    {
        $user = auth('user')->user();
        $field = isset($data['mobile']) ? 'mobile' : 'email';
        $value = strtolower($data[$field]);

        if (! $user->$field) {
            abort(422, '未绑定'.$this->label($field));
        }

        $this->verifyOrFail($value, $data['code']);

        $user->update([$field => null]);
    }

    /**
     * 更换邮箱
     */
    public function changeEmail(array $data): void
    {
        $user = user();

        if (! $this->verifyByAny($user, $data)) {
            abort(422, '身份验证失败');
        }

        $newEmail = strtolower($data['new_email']);
        $this->verifyOrFail($newEmail, $data['new_email_code']);

        $user->update(['email' => $newEmail]);
    }

    /**
     * 验证任意身份方式
     */
    private function verifyByAny(User $user, array $data): bool
    {
        if (! empty($data['current_email_code']) && $user->email) {
            if ($this->verify($user->email, $data['current_email_code'])) {
                return true;
            }
        }

        if (! empty($data['current_mobile_code']) && $user->mobile) {
            if ($this->verify($user->mobile, $data['current_mobile_code'])) {
                return true;
            }
        }

        if (! empty($data['current_password'])) {
            if (Hash::check($data['current_password'], $user->password)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 验证码验证（失败直接中断）
     */
    private function verifyOrFail(string $key, string $code): void
    {
        if (! $this->verify($key, $code)) {
            abort(422, '验证码错误');
        }
    }

    /**
     * 验证码验证并删除缓存
     */
    private function verify(string $key, string $code): bool
    {
        return Cache::pull('verify_code:'.$key) === $code;
    }

    /**
     * 生成 token 响应
     */
    public function respondWithToken(JWTGuard $auth, User $user): JsonResponse
    {
        $token = $auth->login($user);

        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl'),
        ]);
    }

    /**
     * 刷新 token
     */
    public function refresh(JWTGuard $auth): JsonResponse
    {
        $user = auth('user')->user();
        if (! $user) {
            abort(401, '未登录');
        }

        return $this->respondWithToken($auth, $user);
    }

    /**
     * 工具方法
     */
    private function generateUsername(): string
    {
        return 'user_'.Str::random(10);
    }

    private function label(string $field): string
    {
        return $field === 'mobile' ? '手机号' : '邮箱';
    }
}
