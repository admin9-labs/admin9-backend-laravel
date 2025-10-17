<?php

namespace App\Providers;

use App\Support\Auth\EloquentUserProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::provider('eloquent', function ($app, $config) {
            return new EloquentUserProvider($app['hash'], $config['model']);
        });

        // 配置认证相关接口的速率限制
        RateLimiter::for('auth', function (Request $request) {
            return [
                // 基于IP的限制：每分钟最多5次尝试
                Limit::perMinute(5)->by($request->ip()),
                // 基于用户的限制：每小时最多20次尝试
                Limit::perHour(20)->by($request->input('name') ?? $request->input('email') ?? $request->input('mobile') ?? $request->ip()),
            ];
        });
    }
}
