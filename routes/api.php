<?php

use App\Http\Controllers\Api\PortalController;

Route::name('api.')->group(function () {
    Route::get('/', [PortalController::class, 'welcome']);
    Route::get('/portal/home', [PortalController::class, 'home']);
});

