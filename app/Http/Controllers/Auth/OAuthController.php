<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProvider;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Cookie;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class OAuthController extends Controller
{
    public function __construct(#[Auth('user')] protected Guard $auth) {}

    /**
     * Redirect to provider authorization page.
     */
    public function redirect(string $provider): RedirectResponse
    {
        if (! $this->isSupportedProvider($provider)) {
            abort(404);
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle provider callback, create or link user then issue JWT.
     */
    public function callback(string $provider): Redirector|RedirectResponse
    {
        if (! $this->isSupportedProvider($provider)) {
            return redirect('/')
                ->with('error', __('auth.failed'));
        }
        try {
            $oauth = Socialite::driver($provider)->user();
            if (! $oauth?->token) {
                return redirect('/')->with('error', __('auth.failed'));
            }

            $userProvider = UserProvider::select(['id', 'user_id'])
                ->where('name', $provider)
                ->where('provider_id', $oauth->id)
                ->first();

            if (! $userProvider) {
                // 尝试用邮箱关联已有用户
                $user = null;
                if (! empty($oauth->email)) {
                    $user = User::where('email', $oauth->email)->first();
                }
                if (! $user) {
                    $user = new User;
                    $user->name = $oauth->name ?? ('user_'.str()->random(8));
                    $user->email = $oauth->email;
                    $user->password = null; // 标识为未设置密码
                    $user->avatar = $oauth->avatar;
                    if (! empty($oauth->email)) {
                        $user->email_verified_at = now();
                    }
                    $user->save();
                }
                // 创建 provider 关联
                UserProvider::create([
                    'user_id' => $user->id,
                    'provider_id' => $oauth->id,
                    'name' => $provider,
                ]);
            } else {
                $user = $userProvider->user;
            }

            Cookie::queue(
                Cookie::make(
                    config('jwt.cookie_key_name'),
                    $this->auth->login($user),
                    config('jwt.ttl'),
                    secure: true,
                    httpOnly: false,
                    sameSite: 'lax'
                )
            );

            return redirect('/');
        } catch (Throwable) {
            return redirect('/')->with('error', __('auth.failed'));
        }
    }

    protected function isSupportedProvider(string $provider): bool
    {
        $list = config('services.oauth_providers', []);

        return in_array($provider, $list, true);
    }
}
