<?php

namespace App\Services\Auth;

use App\Enums\OAuthProvider;
use App\Models\User;
use App\Models\UserProvider;
use Laravel\Socialite\AbstractUser;

class OAuthService
{
    /**
     * 根据第三方用户信息获取或创建本地用户并建立 provider 关联。
     */
    public function resolveUser(OAuthProvider $providerEnum, AbstractUser $oauthUser): User
    {
        $provider = $providerEnum->value;

        $userProvider = UserProvider::select(['id', 'user_id'])
            ->where('name', $provider)
            ->where('provider_id', $oauthUser->id)
            ->first();

        if ($userProvider) {
            return $userProvider->user;
        }

        // 尝试邮箱关联已有用户
        $user = null;
        if (! empty($oauthUser->email)) {
            $user = User::where('email', $oauthUser->email)->first();
        }
        if (! $user) {
            $user = new User;
            $user->name = $oauthUser->name ?? ('user_'.str()->random(8));
            $user->email = $oauthUser->email;
            $user->password = null; // 未设置密码
            $user->avatar = $oauthUser->avatar;
            if (! empty($oauthUser->email)) {
                $user->email_verified_at = now();
            }
            $user->save();
        }

        UserProvider::create([
            'user_id' => $user->id,
            'provider_id' => $oauthUser->id,
            'name' => $provider,
        ]);

        return $user;
    }
}
