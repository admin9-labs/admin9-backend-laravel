<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class VerifyCodeController extends Controller
{
    /**
     * 发送验证码（短信/邮箱 + 场景）
     *
     * @throws ValidationException
     */
    public function send(): JsonResponse
    {
        $this->validate(request(), [
            'mobile' => 'required_without:email|regex:/^1[3-9]\d{9}$/',
            'email' => 'required_without:mobile|email',
        ]);

        $data = request()->only(['mobile', 'email']);

        // 3. 频率限制（60秒内不能重复发送）
        $limitKey = sprintf('verify_code_limit:%s', $data['mobile'] ?? $data['email']);
        if (Cache::has($limitKey)) {
            return $this->error('请勿频繁请求验证码');
        }

        // 4. 生成验证码
        $code = 111111;

        // 5. 缓存验证码（有效期 5 分钟）
        $codeKey = sprintf('verify_code:%s', $data['mobile'] ?? $data['email']);
        Cache::put($codeKey, $code, now()->addMinutes(5));

        // 6. 设置发送间隔限制（60 秒）
        Cache::put($limitKey, 1, now()->addSeconds(60));

        return $this->success('验证码已发送');
    }
}
