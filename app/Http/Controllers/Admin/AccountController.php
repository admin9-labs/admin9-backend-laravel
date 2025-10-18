<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

class AccountController extends Controller
{
    public function __construct(#[Auth('admin')] protected JWTGuard $auth) {}

    public function show(): JsonResponse
    {
        /** @var Admin $admin */
        $admin = admin();

        return $this->success([
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => $admin->role()->value,
        ]);
    }

    public function update(): JsonResponse
    {
        $data = $this->validate(request(), [
            'name' => 'sometimes|required|string|alpha_dash|max:255',
            'email' => 'sometimes|required|string|max:255|email:filter',
        ]);

        /** @var Admin $admin */
        $admin = admin();
        $admin->fill($data)->save();

        return $this->success();
    }

    public function updatePassword(): JsonResponse
    {
        $this->validate(request(), [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ]);

        /** @var Admin $admin */
        $admin = admin();

        if (! Hash::check(request('current_password'), $admin->password)) {
            return $this->deny(__('auth.password'));
        }

        $admin->password = argon(request('password'));
        $admin->save();

        $this->auth->invalidate(true);

        return $this->success();
    }
}
