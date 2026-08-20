<?php

namespace App\Http\Middleware;

use App\Helpers\CompanyPermissionHelper;
use App\Repositories\RoleRepository;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class EnsureCompanyPermission
{
    public function __construct(private RoleRepository $rolePermission)
    {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $defaultErrorResponse = [
            'message' => 'Você não tem permissão para acessar este recurso.',
            'error_code' => 'domain_permission_denied'
        ];

        $roleId = CompanyPermissionHelper::getCurrentRoleId();

        if ($roleId === null) {
            return response()->json($defaultErrorResponse, Response::HTTP_FORBIDDEN);
        }

        $matches = $this->getBaseApiUrlCandidates($request);
        $hasPermission = $this->rolePermission->hasActivePermissionForApiUrls($roleId, $matches);

        if (!$hasPermission) {
            return response()->json($defaultErrorResponse, Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }

    /**
     * @return array<int, string>
     */
    private function getBaseApiUrlCandidates(Request $request): array
    {
        $segments = explode('/', trim($request->path(), '/'));
        $candidates = [];

        while (count($segments) > 1) {
            $candidates[] = implode('/', $segments);
            array_pop($segments);
        }

        return $candidates;
    }
}
