<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

class RegisterController extends Controller
{
    /**
     * @param  JWTGuard  $auth
     */
    public function __construct(#[Auth('user')] protected Guard $auth) {}

    public function store(Request $request): JsonResponse
    {
        $data = $this->validate($request, [
            'name' => 'required|string|min:2|max:32|alpha_dash',
            'email' => 'required|email:filter|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create($data);

        return $this->success([
            'token' => $this->auth->login($user),
            'token_type' => 'bearer',
            'expires_in' => $this->auth->factory()->getTTL() * 60,
        ]);
    }
}
