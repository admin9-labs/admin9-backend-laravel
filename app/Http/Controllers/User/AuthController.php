<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\BindContactRequest;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\ForgotPasswordRequest;
use App\Http\Requests\User\LoginByAccountRequest;
use App\Http\Requests\User\LoginByEmailRequest;
use App\Http\Requests\User\LoginByMobileRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Requests\User\ResetPasswordRequest;
use App\Http\Requests\User\SetPasswordRequest;
use App\Http\Requests\User\UnbindContactRequest;
use App\Services\User\AuthService;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
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
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->service->register($request->validated());

        return $this->service->respondWithToken($this->auth, $user);
    }

    /**
     * 账号密码登录
     */
    public function loginByAccount(LoginByAccountRequest $request): JsonResponse
    {
        return $this->service->loginByAccount($this->auth, $request->only(['name', 'password']));
    }

    /**
     * 手机号验证码登录
     */
    public function loginByMobile(LoginByMobileRequest $request): JsonResponse
    {
        $user = $this->service->loginByMobile($request->input('mobile'), $request->input('code'));

        return $this->service->respondWithToken($this->auth, $user);
    }

    /**
     * 邮箱验证码登录
     */
    public function loginByEmail(LoginByEmailRequest $request): JsonResponse
    {
        $user = $this->service->loginByEmail($request->input('email'), $request->input('code'));

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
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->service->changePassword($request->validated());

        return $this->success('密码修改成功');
    }

    /**
     * 设置密码（用于没有密码的用户）
     */
    public function setPassword(SetPasswordRequest $request): JsonResponse
    {
        $this->service->setPassword($request->input('password'));

        return $this->success('密码设置成功');
    }

    /**
     * 忘记密码（发送重置验证码）
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->service->sendPasswordResetCode($request->validated());

        return $this->success('验证码已发送');
    }

    /**
     * 重置密码
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->service->resetPassword($request->validated());

        return $this->success('密码重置成功');
    }

    /**
     * 绑定手机号
     */
    public function bindMobile(BindContactRequest $request): JsonResponse
    {
        $this->service->bindContact($request->only(['mobile', 'code']));

        return $this->success('手机号绑定成功');
    }

    /**
     * 解绑手机号
     */
    public function unbindMobile(UnbindContactRequest $request): JsonResponse
    {
        $this->service->unbindContact($request->only(['mobile', 'code']));

        return $this->success('手机号已解绑');
    }

    /**
     * 绑定邮箱
     */
    public function bindEmail(BindContactRequest $request): JsonResponse
    {
        $this->service->bindContact($request->only(['email', 'code']));

        return $this->success('邮箱绑定成功');
    }

    /**
     * 解绑邮箱
     */
    public function unbindEmail(UnbindContactRequest $request): JsonResponse
    {
        $this->service->unbindContact($request->only(['email', 'code']));

        return $this->success('邮箱已解绑');
    }
}
