<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TenantController;
use App\Support\Routing\DefaultCrudRoutes;
use Illuminate\Support\Facades\Route;

Route::middleware(['tenant.domain', 'mutable.request.lock'])->group(function (): void {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('jwt.tenant.auth')->group(function (): void {
        Route::get('/auth/check', [AuthController::class, 'check']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/auth/permissions', [AuthController::class, 'refreshPermissions']);

        Route::middleware('tenant.base')->group(function (): void {
            Route::group(['prefix' => 'tenants', 'controller' => TenantController::class], function (): void {
                DefaultCrudRoutes::getDefaultRoutes(delete: false, restore: false);
            });

            Route::group(['prefix' => 'roles', 'controller' => RoleController::class], function (): void {
                Route::get('/options', 'options');
                DefaultCrudRoutes::getDefaultRoutes(restore: false);
            });

            Route::group(['prefix' => 'permissions', 'controller' => PermissionController::class], function (): void {
                Route::post('/search', 'search')->withoutMiddleware('mutable.request.lock');
            });
        });

        Route::middleware('company.permission')->group(function (): void {
            Route::group(['prefix' => 'customers', 'controller' => CustomerController::class], function (): void {
                Route::get('/options', 'options')->withoutMiddleware('company.permission');
                DefaultCrudRoutes::getDefaultRoutes(restore: false);
            });
        });
    });
});
