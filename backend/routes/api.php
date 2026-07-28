<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TenantController;
use App\Support\Routing\DefaultCrudRoutes;
use Illuminate\Support\Facades\Route;

Route::middleware(['tenant.domain', 'mutable.request.lock'])->group(function (): void {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('jwt.auth')->group(function (): void {
        Route::get('/auth/check', [AuthController::class, 'check']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/auth/permissions', [AuthController::class, 'refreshPermissions']);

        Route::middleware('tenant.base')->group(function (): void {
            Route::group(['prefix' => 'tenants', 'as' => 'tenants.', 'controller' => TenantController::class], function (): void {
                DefaultCrudRoutes::getDefaultRoutes(delete: false, restore: false);
            });
        });

        Route::middleware('company.permission')->group(function (): void {
            Route::group(['prefix' => 'roles', 'as' => 'roles.', 'controller' => RoleController::class], function (): void {
                Route::get('/options', 'options')
                    ->name('options')
                    ->withoutMiddleware('company.permission');
            });

            Route::group(['prefix' => 'customers', 'controller' => CustomerController::class], function (): void {
                Route::get('/options', 'options')->withoutMiddleware('company.permission');
                DefaultCrudRoutes::getDefaultRoutes(restore: false);
            });
        });
    });
});
