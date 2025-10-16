<?php

use App\Http\Controllers\Api\PortalController;
use App\Http\Controllers\Api\VerifyCodeController;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\ProfileController;

Route::post('/verify-code', [VerifyCodeController::class, 'send']);
Route::get('/portal/home', [PortalController::class, 'home']);

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login/account', [AuthController::class, 'loginByAccount']);
    Route::post('login/mobile', [AuthController::class, 'loginByMobile']);
    Route::post('login/email', [AuthController::class, 'loginByEmail']);
    Route::middleware('auth:user')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('set-password', [AuthController::class, 'setPassword']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::post('bind/mobile', [AuthController::class, 'bindMobile']);
        Route::post('unbind/mobile', [AuthController::class, 'unbindMobile']);
        Route::post('bind/email', [AuthController::class, 'bindEmail']);
        Route::post('unbind/email', [AuthController::class, 'unbindEmail']);
    });
});

Route::group(['middleware' => ['auth:user']], function () {
    Route::group(['prefix' => 'user'], function () {
        Route::get('profile', [ProfileController::class, 'show']);
        Route::put('profile', [ProfileController::class, 'update']);
    });
});
