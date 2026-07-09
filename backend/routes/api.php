<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Support\Routing\DefaultCrudRoutes;
use Illuminate\Support\Facades\Route;

Route::middleware(['tenant.domain', 'mutable.request.lock'])->group(function (): void {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('jwt.auth')->group(function (): void {
        Route::get('/auth/check', [AuthController::class, 'check']);
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::group(['prefix' => 'customers', 'controller' => CustomerController::class], function (): void {
            Route::get('/options', 'options');
            DefaultCrudRoutes::getDefaultRoutes(restore: false);
        });
    });
});
