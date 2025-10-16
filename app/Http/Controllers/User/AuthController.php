<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\User\AuthService;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

class AuthController extends Controller
{
    /**
     * @param  JWTGuard  $auth
     */
    public function __construct(
        #[Auth('user')] protected Guard $auth,
        protected AuthService $service
    ) {}

    /**
     * 用户注册
     *
     * @throws ValidationException
     */
    public function register(): JsonResponse
    {
        $data = $this->validate(request(), [
            'name' => 'required|string|min:3|max:30|alpha_dash|unique:users',
            'password' => ['required', 'string', Password::defaults()],
        ]);

        $user = $this->service->register($data);

        return $this->service->respondWithToken($this->auth, $user);
    }

    /**
     * 账号密码登录
     *
     * @throws ValidationException
     */
    public function loginByAccount(): JsonResponse
    {
        $this->validate(request(), [
            'name' => 'required|string|min:3|max:30|alpha_dash',
            'password' => 'required|string',
        ]);

        return $this->service->loginByAccount($this->auth, request(['name', 'password']));
    }

    /**
     * 手机号验证码登录
     *
     * @throws ValidationException
     */
    public function loginByMobile(): JsonResponse
    {
        $this->validate(request(), [
            'mobile' => 'required|string|max:11|regex:/^1[3-9]\d{9}$/',
            'code' => 'required|numeric|digits:6',
        ]);

        $user = $this->service->loginByMobile(request('mobile'), request('code'));

        return $this->service->respondWithToken($this->auth, $user);
    }

    /**
     * 邮箱验证码登录
     *
     * @throws ValidationException
     */
    public function loginByEmail(): JsonResponse
    {
        $this->validate(request(), [
            'email' => 'required|email|max:255',
            'code' => 'required|numeric|digits:6',
        ]);

        $user = $this->service->loginByEmail(request('email'), request('code'));

        return $this->service->respondWithToken($this->auth, $user);
    }

    /**
     * 刷新token
     */
    public function refresh(): JsonResponse
    {
        if (! auth('user')->user()) {
            return $this->error('未登录');
        }

        return $this->service->respondWithToken($this->auth, user());
    }

    /**
     * 注销登录
     */
    public function logout(): JsonResponse
    {
        $this->auth->logout();

        return $this->success();
    }

    /**
     * 修改密码
     *
     * @throws ValidationException
     */
    public function changePassword(): JsonResponse
    {
        $this->validate(request(), [
            'current_password' => 'required|string',
            'password' => ['required', 'string', Password::defaults()],
        ]);

        $this->service->changePassword(request('current_password', 'password'));

        return $this->success();
    }

    /**
     * 绑定手机号
     *
     * @throws ValidationException
     */
    public function bindMobile(): JsonResponse
    {
        $this->validate(request(), [
            'mobile' => 'required|string|max:11|regex:/^1[3-9]\d{9}$/|unique:users,mobile',
            'code' => 'required|numeric|digits:6',
        ]);

        $this->service->bindContact(request(['mobile', 'code']));

        return $this->success('手机号绑定成功');
    }

    /**
     * 解绑手机号
     *
     * @throws ValidationException
     */
    public function unbindMobile(): JsonResponse
    {
        $this->validate(request(), [
            'mobile' => 'required|string|max:11|regex:/^1[3-9]\d{9}$/|unique:users,mobile',
            'code' => 'required|numeric|digits:6',
        ]);

        $this->service->unbindContact(request(['mobile', 'code']));

        return $this->success('手机号已解绑');
    }

    /**
     * 绑定邮箱
     *
     * @throws ValidationException
     */
    public function bindEmail(): JsonResponse
    {
        $this->validate(request(), [
            'email' => 'required|email|max:255|unique:users,email',
            'code' => 'required|numeric|digits:6',
        ]);

        $this->service->changeEmail(request(['email', 'code']));

        return $this->success('邮箱绑定成功');
    }

    /**
     * 解绑邮箱
     *
     * @throws ValidationException
     */
    public function unbindEmail(): JsonResponse
    {
        $this->validate(request(), [
            'email' => 'required|email',
            'code' => 'required|numeric|digits:6',
        ]);

        $this->service->unbindContact(request(['email', 'code']));

        return $this->success('邮箱已解绑');
    }
}
