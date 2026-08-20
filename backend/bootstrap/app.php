<?php

use App\Http\Middleware\EnsureBaseTenant;
use App\Http\Middleware\EnsureCompanyPermission;
use App\Http\Middleware\EnsureTokenTenant;
use App\Http\Middleware\InitializeTenantByDomain;
use App\Http\Middleware\PreventConcurrentMutableRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(HandleCors::class);
        $middleware->alias([
            'tenant.base' => EnsureBaseTenant::class,
            'company.permission' => EnsureCompanyPermission::class,
            'tenant.domain' => InitializeTenantByDomain::class,
            'tenant.token' => EnsureTokenTenant::class,
            'mutable.request.lock' => PreventConcurrentMutableRequests::class,
        ]);

        $middleware->group('jwt.tenant.auth', [
            'jwt.auth',
            'tenant.token',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
