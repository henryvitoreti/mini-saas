<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Stancl\Tenancy\Contracts\TenantCouldNotBeIdentifiedException;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedById;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;
use Symfony\Component\HttpFoundation\Response;

readonly class InitializeTenantByDomain
{
    public function __construct(private DomainTenantResolver $tenantResolver)
    {}

    /**
     * @throws TenantCouldNotBeIdentifiedById
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $tenant = $this->tenantResolver->resolve($request->getHost());
        } catch (TenantCouldNotBeIdentifiedException) {
            return response()->json([
                'message' => 'Tenant não encontrado para este domínio.',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($tenant instanceof Model && $tenant->getAttribute('active') === false) {
            return response()->json([
                'message' => 'Tenant inativo.',
            ], Response::HTTP_FORBIDDEN);
        }

        tenancy()->initialize($tenant);

        return $next($request);
    }
}
