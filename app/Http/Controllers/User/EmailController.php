<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    /**
     * 绑定或更新邮箱
     */
    public function update(Request $request): JsonResponse
    {
        $user = user();
        $data = $this->validate($request, [
            'email' => 'required|email|unique:users,email,'.$user->id,
            'code' => 'required|numeric',
        ]);
        $cacheKey = 'verify:email:'.$data['email'].':bind-email';
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if (! $cached || (string) $cached !== (string) $data['code']) {
            return $this->deny(__('auth.code_invalid'));
        }
        $user->email = $data['email'];
        $user->email_verified_at = now();
        $user->save();

        return $this->success(['email' => $user->email]);
    }

    /**
     * 解绑邮箱
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = user();
        $user->email = null;
        $user->email_verified_at = null;
        $user->save();

        return $this->success(['email' => null]);
    }
}
