<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
            'name' => 'required|string|min:3|max:30|alpha_dash|unique:users',
            'password' => ['required', 'string', Password::defaults()],
            'email' => 'nullable|email|max:255|unique:users',
            'mobile' => 'nullable|string|max:11|regex:/^1[3-9]\d{9}$/|unique:users',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => '用户名不能为空',
            'name.unique' => '用户名已存在',
            'name.alpha_dash' => '用户名只能包含字母、数字、破折号和下划线',
            'password.required' => '密码不能为空',
            'email.email' => '请输入有效的邮箱地址',
            'email.unique' => '邮箱已被注册',
            'mobile.regex' => '请输入有效的手机号码',
            'mobile.unique' => '手机号已被注册',
        ];
    }
}
