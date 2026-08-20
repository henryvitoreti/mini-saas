<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\JWTAuth;

readonly class EnsureTokenTenant
{
    public function __construct(private JWTAuth $jwtAuth) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tokenTenantId = $this->jwtAuth->getPayload()->get('tenant_id');
        $currentTenantId = tenant('id');

        if (!is_string($tokenTenantId) || !is_string($currentTenantId) || !hash_equals($currentTenantId, $tokenTenantId)) {
            return response()->json([
                'message' => 'Token inválido para este tenant.',
                'error_code' => 'token_tenant_mismatch',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
