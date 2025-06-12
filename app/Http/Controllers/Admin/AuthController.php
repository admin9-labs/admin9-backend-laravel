<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

class AuthController extends Controller
{

    /**
     * @param  JWTGuard  $auth
     */
    public function __construct(#[Auth('admin')] protected Guard $auth) {}

    public function login()
    {
        $this->validate(request(), [
            'email' => 'required|string|max:255|email:filter',
            'password' => 'required|string|min:6',
        ]);

        if (! ($token = $this->auth->attempt(request(['email', 'password'])))) {
            return $this->error('账户或密码错误');
        }

        return $this->success([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->auth->factory()->getTTL() * 60,
        ]);
    }

    public function me()
    {
        /** @var User $user */
        $user = $this->auth->user();

        return $this->success([
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role()->value,
        ]);
    }

    public function changePassword()
    {
        $this->validate(request(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = admin();

        if (! Hash::check(request('current_password'), $user->password)) {
            return $this->error('原密码错误');
        }

        $user->update(['password' => request('password')]);

        return $this->success();
    }

    public function logout()
    {
        $this->auth->logout();

        return $this->success();
    }
}
