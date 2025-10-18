<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

class VerificationCodeController extends Controller
{
    public function store(): JsonResponse
    {
        $this->validate(request(), [
            'mobile' => 'required_without:email|regex:/^1[3-9]\d{9}$/',
            'email' => 'required_without:mobile|email',
            'scene' => 'nullable|string|in:bind_email,change_email,unbind_email,login_email',
        ]);

        $data = request()->only(['mobile', 'email']);
        $scene = request('scene');

        if (is_prod()) {
            $limitKey = sprintf('verify_code_limit:%s', $data['mobile'] ?? $data['email']);
            if (Cache::has($limitKey)) {
                return $this->error('请勿频繁请求验证码');
            }

            // 生成验证码
            $code = (string) random_int(100000, 999999);

            // 缓存验证码（有效期 5 分钟）
            $codeKey = sprintf('verify_code:%s', $data['mobile'] ?? $data['email']);
            Cache::put($codeKey, $code, now()->addMinutes(5));

            // 设置发送间隔限制（60 秒）
            Cache::put($limitKey, 1, now()->addSeconds(60));

            // 通过通知多渠道发送
            if (! empty($data['email'])) {
                Notification::route('mail', $data['email'])->notify(new VerifyCodeNotification($code, $scene));
            }
            if (! empty($data['mobile'])) {
                Notification::route('sms', $data['mobile'])->notify(new VerifyCodeNotification($code, $scene));
            }
        } else {
            $codeKey = sprintf('verify_code:%s', $data['mobile'] ?? $data['email']);
            Cache::put($codeKey, '111111', now()->addMinutes(10));
        }
    }

    protected function cacheKey(string $channel, string $target, string $scene): string
    {
        return 'verify:'.$channel.':'.$target.':'.$scene;
    }
}
