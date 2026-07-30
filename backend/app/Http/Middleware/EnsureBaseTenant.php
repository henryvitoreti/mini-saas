<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBaseTenant
{
    private const string BASE_TENANT_ID = 'base';

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ((string)tenant('id') !== self::BASE_TENANT_ID) {
            return response()->json([
                'message' => 'Página não encontrada.',
                'error_code' => 'force_not_found'
            ], Response::HTTP_NOT_FOUND);
        }

        return $next($request);
    }
}
