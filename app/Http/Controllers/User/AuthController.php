<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class AuthController extends Controller
{
    /**
     * @param  JWTGuard  $auth
     */
    public function __construct(#[Auth('user')] protected Guard $auth) {}

    /**
     * 用户注册
     *
     * @throws ValidationException
     */
    public function register(): JsonResponse
    {
        $data = $this->validate(request(), [
            'name' => 'required|unique:users,name',
            'password' => [self::$passwordRules, 'confirmed'],
            'invite_code' => 'required|string|max:32',
        ]);

        $data['avatar'] = '/avatars/'.rand(0, 12).'.png';

        $user = User::create($data);

        $token = $this->auth->login($user);

        return $this->respondWithToken($token);
    }

    /**
     * 账号密码登录
     *
     * @throws ValidationException
     */
    public function loginByAccount(): JsonResponse
    {
        $this->validate(request(), [
            'name' => 'required|string|max:255|alpha_dash',
            'password' => 'required|string',
        ]);

        $credentials = request(['name', 'password']);
        if (! $token = $this->auth->attempt($credentials)) {
            return $this->error('账户或密码错误');
        }

        return $this->respondWithToken($token);
    }

    /**
     * 手机号验证码登录
     *
     * @throws ValidationException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function loginByMobile(): JsonResponse
    {
        $this->validate(request(), [
            'mobile' => 'required|string|max:11|regex:/^1[3-9]\d{9}$/',
            'code' => 'required|numeric|digits:6',
        ]);

        $mobile = request('mobile');
        $cacheCode = cache()->get('verify_code:'.$mobile);
        if (! $cacheCode || $cacheCode != request('code')) {
            return $this->error('验证码错误');
        } else {
            cache()->forget('verify_code:'.$mobile);
        }

        // 初始密码
        $password = Str::random(6);

        $user = User::firstOrCreate(['mobile' => $mobile], [
            'name' => $this->generateUsername(),
            'password' => $password,
            'avatar' => '/avatars/'.rand(0, 12).'.png',
        ]);

        $token = $this->auth->login($user);

        return $this->respondWithToken($token);
    }

    /**
     * 邮箱验证码登录
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ValidationException
     */
    public function loginByEmail(): JsonResponse
    {
        $this->validate(request(), [
            'email' => 'required|email|max:255',
            'code' => 'required|numeric|digits:6',
        ]);

        $email = strtolower(request('email'));
        $cacheCode = cache()->get('verify_code:'.$email);
        if (! $cacheCode || $cacheCode != request('code')) {
            return $this->error('验证码错误');
        } else {
            cache()->forget('verify_code:'.$email);
        }

        // 初始密码
        $password = Str::random(6);

        $user = User::firstOrCreate(['email' => $email], [
            'name' => $this->generateUsername(),
            'password' => $password,
            'register_password' => $password,
            'avatar' => '/avatars/'.rand(0, 12).'.png',
            // 'email_verified_at' => now(),
        ]);

        $token = $this->auth->login($user);

        return $this->respondWithToken($token);
    }

    /**
     * 刷新token
     */
    public function refresh(): JsonResponse
    {
        $token = $this->auth->refresh();

        return $this->respondWithToken($token);
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
     * 首次设置密码
     *
     * @throws ValidationException
     */
    public function setPassword(): JsonResponse
    {
        $this->validate(request(), [
            'name' => 'required|unique:users,name',
            'password' => self::$passwordRules,
        ]);

        $user = user();
        if ($user->is_password_set) {
            return $this->error('请勿重复设置');
        }

        $user->update([
            'name' => request('name'),
            'password' => request('password'),
            'register_password' => null,
        ]);

        return $this->success('账号密码登录设置成功');
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
            'password' => self::$passwordRules,
        ]);

        $user = user();

        if (! Hash::check(request('current_password'), $user->password)) {
            return $this->error('原密码错误');
        }

        $user->update(['password' => request('password')]);

        return $this->success();
    }

    /**
     * 绑定手机号
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ValidationException
     */
    public function bindMobile(): JsonResponse
    {
        $this->validate(request(), [
            'mobile' => 'required|string|max:11|regex:/^1[3-9]\d{9}$/|unique:users,mobile',
            'code' => 'required|numeric|digits:6',
        ]);

        $user = user();
        if ($user->mobile) {
            return $this->error('您已绑定过手机号');
        }

        $mobile = request('mobile');
        $cacheCode = cache()->get('verify_code:'.$mobile);
        if (! $cacheCode || $cacheCode != request('code')) {
            return $this->error('验证码错误');
        } else {
            cache()->forget('verify_code:'.$mobile);
        }

        $user = user();
        $user->update(['mobile' => $mobile]);

        return $this->success('手机号绑定成功');
    }

    /**
     * 解绑手机号
     */
    public function unbindMobile(): JsonResponse
    {
        $this->validate(request(), [
            'mobile' => 'required|string|max:11|regex:/^1[3-9]\d{9}$/|unique:users,mobile',
            'code' => 'required|numeric|digits:6',
        ]);

        $mobile = request('mobile');
        $cacheCode = cache()->get('verify_code:'.$mobile);
        if (! $cacheCode || $cacheCode != request('code')) {
            return $this->error('验证码错误');
        } else {
            cache()->forget('verify_code:'.$mobile);
        }

        $user = user();
        if (! $user->mobile) {
            return $this->error('未绑定手机号');
        }

        $user->update(['mobile' => null]);

        return $this->success('手机号已解绑');
    }

    /**
     * 绑定邮箱
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ValidationException
     */
    public function bindEmail(): JsonResponse
    {
        $this->validate(request(), [
            'email' => 'required|email|max:255|unique:users,email',
            'code' => 'required|numeric|digits:6',
        ]);

        $user = user();
        if ($user->email) {
            return $this->error('您已绑定过邮箱');
        }

        $email = strtolower(request('email'));
        $cacheCode = cache()->get('verify_code:'.$email);
        if (! $cacheCode || $cacheCode != request('code')) {
            return $this->error('验证码错误');
        } else {
            cache()->forget('verify_code:'.$email);
        }

        $user->update(['email' => $email]);

        return $this->success('邮箱绑定成功');
    }

    /**
     * 解绑邮箱
     */
    public function unbindEmail(): JsonResponse
    {
        $this->validate(request(), [
            'email' => 'required|email|max:255|unique:users,email',
            'code' => 'required|numeric|digits:6',
        ]);

        $email = strtolower(request('email'));
        $cacheCode = cache()->get('verify_code:'.$email);
        if (! $cacheCode || $cacheCode != request('code')) {
            return $this->error('验证码错误');
        } else {
            cache()->forget('verify_code:'.$email);
        }

        $user = user();
        if (! $user->email) {
            return $this->error('未绑定邮箱');
        }

        $user->update(['email' => null]);

        return $this->success('邮箱已解绑');
    }

    /**
     * 返回token信息
     */
    protected function respondWithToken(string $token): JsonResponse
    {
        return $this->success([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->auth->factory()->getTTL() * 60,
        ]);
    }

    /**
     * 生成默认用户名
     */
    protected function generateUsername(): string
    {
        return 'user_'.Str::random(10);
    }

    protected static array $passwordRules = [
        'required',
        'string',
        'min:6',             // 最小长度
        'regex:/[a-z]/',     // 至少一个小写字母
        'regex:/[A-Z]/',     // 至少一个大写字母
        'regex:/[0-9]/',     // 至少一个数字
        // 'regex:/[@$!%*?&]/'  // 至少一个特殊字符
    ];
}
