<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    /**
     * Request a password reset (send code / link).
     */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validate($request, [
            'email' => 'required|email|exists:users,email',
        ]);
        $status = Password::sendResetLink(['email' => $data['email']]);

        return $status === Password::RESET_LINK_SENT
            ? $this->success(['sent' => true])
            : $this->deny(__('auth.failed'));
    }

    /**
     * Perform the password reset with token / code.
     */
    public function update(Request $request, string $token): JsonResponse
    {
        $data = $this->validate($request, [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $status = Password::reset(
            [
                'email' => $data['email'],
                'password' => $data['password'],
                'password_confirmation' => $request->input('password_confirmation'),
                'token' => $token,
            ],
            function ($user) use ($data) {
                $user->forceFill([
                    'password' => Hash::make($data['password']),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? $this->success(['reset' => true])
            : $this->deny(__('auth.failed'));
    }
}
