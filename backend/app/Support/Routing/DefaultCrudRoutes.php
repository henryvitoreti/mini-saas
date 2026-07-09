<?php

namespace App\Support\Routing;

use Illuminate\Support\Facades\Route;

class DefaultCrudRoutes
{
    public static function getDefaultRoutes(
        bool $index = true,
        bool $search = true,
        bool $show = true,
        bool $store = true,
        bool $update = true,
        bool $delete = true,
        bool $restore = true,
        string $routeKey = 'id'
    ): void {
        if ($index) {
            Route::get('/', 'index');
        }

        if ($search) {
            Route::post('/search', 'search');
        }

        if ($store) {
            Route::post('/', 'store');
        }

        if ($show) {
            Route::get('/{'.$routeKey.'}', 'show');
        }

        if ($update) {
            Route::put('/{'.$routeKey.'}', 'update');
        }

        if ($delete) {
            Route::delete('/{'.$routeKey.'}', 'delete');
        }

        if ($restore) {
            Route::patch('/{'.$routeKey.'}/restore', 'restore');
        }
    }
}
