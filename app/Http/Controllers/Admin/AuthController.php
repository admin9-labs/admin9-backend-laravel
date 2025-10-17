<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\JsonResponse;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

class AuthController extends Controller
{
    public function __construct(#[Auth('admin')] protected JWTGuard $auth) {}

    public function login(): JsonResponse
    {
        $this->validate(request(), [
            'email' => 'required|string|max:255|email:filter',
            'password' => 'required|string|min:6',
        ]);

        if ($this->auth->check()) {
            return $this->deny(__('auth.authenticated'));
        }

        $credentials = request()->only(['email', 'password']);
        if (! ($token = $this->auth->attempt($credentials))) {
            return $this->deny(__('auth.failed'));
        }

        return $this->success(
            $this->tokenPayload($token)
        );
    }

    public function refresh(): JsonResponse
    {
        $token = $this->auth->refresh();

        return $this->success(
            $this->tokenPayload($token)
        );
    }

    public function logout(): JsonResponse
    {
        $this->auth->logout();

        return $this->success();
    }

    private function tokenPayload(string $token): array
    {
        return [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->auth->factory()->getTTL() * 60,
        ];
    }
}
