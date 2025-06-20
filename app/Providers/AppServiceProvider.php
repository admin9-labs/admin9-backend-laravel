<?php

namespace App\Providers;

use App\Support\Auth\EloquentUserProvider;
use Illuminate\Support\Facades\Auth;
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
    }
}
