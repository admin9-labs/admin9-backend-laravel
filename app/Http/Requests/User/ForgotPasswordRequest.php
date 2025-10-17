<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
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
            'email' => 'required_without:mobile|email|max:255|exists:users,email',
            'mobile' => 'required_without:email|string|max:11|regex:/^1[3-9]\d{9}$/|exists:users,mobile',
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
            'email.exists' => '该邮箱未注册',
            'mobile.required_without' => '请输入邮箱或手机号',
            'mobile.regex' => '请输入有效的手机号码',
            'mobile.exists' => '该手机号未注册',
        ];
    }
}
