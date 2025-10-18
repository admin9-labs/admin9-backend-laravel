<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AuthController;

Route::prefix('admin')->name('admin.')->group(function () {
    // Guest
    Route::post('login', [AuthController::class, 'login'])->name('auth.login');

    // Authenticated
    Route::middleware('auth:admin')->group(function () {
        Route::post('refresh', [AuthController::class, 'refresh'])->name('auth.refresh');
        Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');

        // Account
        Route::prefix('account')->name('account.')->group(function () {
            Route::get('', [AccountController::class, 'show'])->name('show');
            Route::patch('', [AccountController::class, 'update'])->name('update');
            Route::patch('password', [AccountController::class, 'updatePassword'])->name('password.update');
        });
    });
});
