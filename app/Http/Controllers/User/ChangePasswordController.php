<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ChangePasswordController
 * 已登录用户修改自身密码。与忘记密码重置流程分离。
 */
class ChangePasswordController extends Controller
{
    /**
     * PATCH /api/user/password
     * body: current_password, password, password_confirmation
     */
    public function update(Request $request): JsonResponse
    {
        $this->validate($request, [
            'current_password' => 'required|string',
            // 新密码需不同于当前密码
            'password' => 'required|string|min:8|confirmed|different:current_password',
        ]);
        $user = user();
        if (! \Illuminate\Support\Facades\Hash::check($request->input('current_password'), $user->password)) {
            return $this->deny(__('auth.password')); // generic error
        }
        // 使用 Hash::make 或模型 casts hashed（此处显式调用提高可读性）
        $user->password = \Illuminate\Support\Facades\Hash::make($request->input('password'));
        $user->save();

        return $this->success(['updated' => true]);
    }
}
