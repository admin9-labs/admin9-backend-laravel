<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required_without:mobile|email|max:255',
            'mobile' => 'required_without:email|string|max:11|regex:/^1[3-9]\d{9}$/',
            'code' => 'required|numeric|digits:6',
            'password' => ['required', 'string', Password::defaults()],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.required_without' => '请输入邮箱或手机号',
            'email.email' => '请输入有效的邮箱地址',
            'mobile.required_without' => '请输入邮箱或手机号',
            'mobile.regex' => '请输入有效的手机号码',
            'code.required' => '验证码不能为空',
            'code.digits' => '验证码必须是6位数字',
            'password.required' => '新密码不能为空',
        ];
    }
}
