<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OAuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationCodeController;
use App\Http\Controllers\User\AvatarController;
use App\Http\Controllers\User\ChangePasswordController;
use App\Http\Controllers\User\EmailController;
use App\Http\Controllers\User\PhoneController;
use App\Http\Controllers\User\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Regular User - Guest Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->name('auth.')->group(function () {
    // Registration
    Route::post('register', [RegisterController::class, 'store'])->name('register');

    // Unified login (channel in payload: password|sms|email)
    Route::post('login', [LoginController::class, 'login'])->name('login');
    Route::post('login/sms', [LoginController::class, 'loginBySms'])->name('sms.login');
    Route::post('login/email', [LoginController::class, 'loginByEmail'])->name('email.login');

    // OAuth (generic provider parameter)
    Route::get('oauth/{provider}', [OAuthController::class, 'redirect'])->name('oauth.redirect');
    Route::get('oauth/callback/{provider}', [OAuthController::class, 'callback'])->name('oauth.callback');

    // Unified verification code endpoint (channel in payload)
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('verification-code', [VerificationCodeController::class, 'store'])->name('verification-code.store');
    });

    // Password reset (RESTful style)
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('password/reset', [PasswordResetController::class, 'store'])->name('password.reset.store');
        Route::put('password/reset/{token}', [PasswordResetController::class, 'update'])->name('password.reset.update');
    });
});

/*
|--------------------------------------------------------------------------
| Regular User - Authenticated Routes (Token Required)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:user')->group(function () {
    // Logout & token refresh
    Route::post('auth/logout', [LoginController::class, 'logout'])->name('auth.logout');
    Route::post('auth/refresh', [LoginController::class, 'refresh'])->name('auth.refresh');

    // User root resource (profile show/update)
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');

        // Avatar
        Route::put('avatar', [AvatarController::class, 'update'])->name('avatar.update');
        Route::delete('avatar', [AvatarController::class, 'destroy'])->name('avatar.destroy');

        // Password (authenticated change)
        Route::patch('password', [ChangePasswordController::class, 'update'])->name('password.update');

        // Phone
        Route::put('phone', [PhoneController::class, 'update'])->name('phone.update');
        Route::delete('phone', [PhoneController::class, 'destroy'])->name('phone.destroy');

        // Email
        Route::put('email', [EmailController::class, 'update'])->name('email.update');
        Route::delete('email', [EmailController::class, 'destroy'])->name('email.destroy');
    });
});
