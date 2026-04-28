<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/tenant/check', function () {
    return response()->json([
        'tenant_id' => tenant('id'),
        'initialized' => tenancy()->initialized,
        'database' => DB::connection()->getDatabaseName(),
        'default_connection' => config('database.default'),
    ]);
});
