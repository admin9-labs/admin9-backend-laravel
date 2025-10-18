<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;
use Throwable;

class LoginController extends Controller
{
    /**
     * @param  JWTGuard  $auth
     */
    public function __construct(#[Auth('user')] protected Guard $auth) {}

    public function login(Request $request): JsonResponse
    {
        $data = $this->validate($request, [
            // 这里只支持用户名登录，可后续扩展为 identifier 多字段
            'name' => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            /** @var User|null $user */
            $user = User::where('name', $data['name'])->first();
            if (! $user || ! Hash::check($data['password'], $user->password)) {
                return $this->deny(__('auth.failed'));
            }

            $payload = $this->issueTokenPayload($user);

            return $this->success($payload);
        } catch (Throwable $e) {
            return $this->deny(format_exception($e));
        }
    }

    public function loginBySms(Request $request): JsonResponse
    {
        $data = $this->validate($request, [
            'phone' => 'required|string',
            'code' => 'required|digits:6',
        ]);
        $key = $this->verificationKey('sms', $data['phone'], 'login');
        $cached = Cache::get($key);
        if (! $cached || (string) $cached !== (string) $data['code']) {
            return $this->deny(__('auth.code_invalid'));
        }
        /** @var User|null $user */
        $user = User::where('phone', $data['phone'])->first();
        if (! $user) {
            return $this->deny(__('auth.failed'));
        }
        // 使验证码一次性
        Cache::forget($key);
        $payload = $this->issueTokenPayload($user);

        return $this->success($payload);
    }

    public function loginByEmail(Request $request): JsonResponse
    {
        $data = $this->validate($request, [
            'email' => 'required|email',
            'code' => 'required|digits:6',
        ]);

        $key = $this->verificationKey('email', $data['email'], 'login');
        $cached = Cache::get($key);
        if (! $cached || (string) $cached !== (string) $data['code']) {
            return $this->deny(__('auth.code_invalid'));
        }
        /** @var User|null $user */
        $user = User::where('email', $data['email'])->first();
        if (! $user) {
            return $this->deny(__('auth.failed'));
        }
        Cache::forget($key);
        $payload = $this->issueTokenPayload($user);

        return $this->success($payload);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->auth->logout();

        return $this->success(['logout' => true]);
    }

    public function refresh(Request $request): JsonResponse
    {

        $token = $this->auth->refresh();
        $this->queueJwtCookie($token);

        return $this->success([
            'token' => $token,
            'ttl' => config('jwt.ttl'),
        ]);
    }

    // ================= Helpers =================
    protected function issueTokenPayload(User $user): array
    {
        $token = $this->auth->login($user);
        $this->queueJwtCookie($token);

        return [
            'user' => $this->userResource($user),
            'token' => $token,
            'ttl' => config('jwt.ttl'),
        ];
    }

    protected function userResource(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role()->value,
            'avatar' => $user->avatar,
            'email' => $user->email,
            'email_verified' => ! is_null($user->email_verified_at),
        ];
    }

    protected function verificationKey(string $channel, string $target, string $scene): string
    {
        return "verify:{$channel}:{$target}:{$scene}";
    }

    protected function queueJwtCookie(string $token): void
    {
        Cookie::queue(
            Cookie::make(
                config('jwt.cookie_key_name'),
                $token,
                config('jwt.ttl'),
                secure: true,
                httpOnly: false,
                sameSite: 'lax'
            )
        );
    }
}
