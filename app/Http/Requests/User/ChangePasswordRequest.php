<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('user')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => 'required_without_all:current_email_code,current_mobile_code|string',
            'current_email_code' => 'required_without_all:current_password,current_mobile_code|numeric|digits:6',
            'current_mobile_code' => 'required_without_all:current_password,current_email_code|numeric|digits:6',
            'password' => ['required', 'string', Password::defaults()],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'current_password.required_without_all' => '请提供当前密码或验证码进行身份验证',
            'current_email_code.required_without_all' => '请提供当前密码或验证码进行身份验证',
            'current_mobile_code.required_without_all' => '请提供当前密码或验证码进行身份验证',
            'current_email_code.digits' => '邮箱验证码必须是6位数字',
            'current_mobile_code.digits' => '手机验证码必须是6位数字',
            'password.required' => '新密码不能为空',
        ];
    }
}
