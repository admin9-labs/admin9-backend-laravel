<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class LoginByMobileRequest extends FormRequest
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
            'mobile' => 'required|string|max:11|regex:/^1[3-9]\d{9}$/',
            'code' => 'required|numeric|digits:6',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'mobile.required' => '手机号不能为空',
            'mobile.regex' => '请输入有效的手机号码',
            'code.required' => '验证码不能为空',
            'code.digits' => '验证码必须是6位数字',
        ];
    }
}
