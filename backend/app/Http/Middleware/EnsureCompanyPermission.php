<?php

namespace App\Http\Middleware;

use App\Helpers\CompanyPermissionHelper;
use App\Repositories\PermissionRepository;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class EnsureCompanyPermission
{
    public function __construct(private PermissionRepository $permissionRepository)
    {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $roleId = CompanyPermissionHelper::getCurrentRoleId();
        $matches = $this->getBaseApiUrlCandidates($request);
        $permissions = $this->permissionRepository->roleHasActivePermissionForApiUrls($roleId, $matches);

        if ($roleId === null || !$permissions) {
            return response()->json([
                'message' => 'Você não tem permissão para acessar este recurso.',
                'error_code' => 'domain_permission_denied'
            ], Response::HTTP_FORBIDDEN);
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
