<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhoneController extends Controller
{
    /**
     * 绑定或更新手机号码
     */
    public function update(Request $request): JsonResponse
    {
        $user = user();
        $data = $this->validate($request, [
            'phone' => 'required|string|unique:users,phone,'.$user->id,
            'code' => 'required|numeric',
        ]);
        $cacheKey = 'verify:sms:'.$data['phone'].':bind-phone';
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if (! $cached || (string) $cached !== (string) $data['code']) {
            return $this->deny(__('auth.code_invalid'));
        }
        $user->phone = $data['phone'];
        $user->phone_verified_at = now();
        $user->save();

        return $this->success(['phone' => $user->phone]);
    }

    /**
     * 解绑手机号码
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = user();
        $user->phone = null;
        $user->phone_verified_at = null;
        $user->save();

        return $this->success(['phone' => null]);
    }
}
