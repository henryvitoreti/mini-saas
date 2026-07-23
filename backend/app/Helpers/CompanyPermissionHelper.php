<?php

namespace App\Helpers;

use App\Models\Company;
use App\Models\Permission;
use App\Repositories\CompanyRepository;
use App\Repositories\PermissionRepository;
use Illuminate\Support\Facades\Cache;

class CompanyPermissionHelper
{
    private const int CACHE_TTL_MINUTES = 30;

    /**
     * @return array<int,array<string,mixed>>
     */
    public static function getCurrentPermissions(): array
    {
        return Cache::remember(
            self::cacheKey(),
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            fn (): array => self::resolveCurrentPermissions()
        );
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public static function rememberCurrentPermissions(): array
    {
        self::forgetCurrentPermissions();

        return self::getCurrentPermissions();
    }

    public static function forgetCurrentPermissions(): void
    {
        Cache::forget(self::cacheKey());
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private static function resolveCurrentPermissions(): array
    {
        $company = app(CompanyRepository::class)->getCurrentCompany();

        if (!$company instanceof Company || $company->role_id === null) {
            return [];
        }

        $permissions = app(PermissionRepository::class)->getByRoleId((int)$company->role_id);

        return $permissions
            ->map(fn (Permission $permission): array => self::formatPermission($permission))
            ->values()
            ->all();
    }

    /**
     * @return array<string,mixed>
     */
    private static function formatPermission(Permission $permission): array
    {
        return [
            'id' => $permission->id,
            'name' => $permission->name,
            'slug' => $permission->slug,
            'base_front_url' => $permission->base_front_url,
            'base_api_url' => $permission->base_api_url,
            'show_locked_routes' => (bool)$permission->show_locked_routes,
            'is_active' => (bool)$permission->is_active,
        ];
    }

    private static function cacheKey(): string
    {
        return 'tenant:'.self::tenantKey().':company-permissions';
    }

    private static function tenantKey(): string
    {
        $tenantId = tenant('id');

        if (is_int($tenantId) || is_string($tenantId)) {
            return (string)$tenantId;
        }

        if (app()->bound('request')) {
            return 'host:'.sha1(request()->getHost());
        }

        return 'central';
    }
}
