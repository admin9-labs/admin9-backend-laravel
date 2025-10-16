<?php

use App\Http\Controllers\Admin\AuthController;

Route::prefix('/admin')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::group(['middleware' => ['auth:admin']], function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::put('change-password', [AuthController::class, 'changePassword']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});
