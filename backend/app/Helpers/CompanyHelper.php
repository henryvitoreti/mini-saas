<?php

namespace App\Helpers;

use App\Http\Resources\CompanyResource;
use App\Models\Company;
use App\Repositories\CompanyRepository;
use Illuminate\Support\Facades\Cache;

class CompanyHelper
{
    private const int CACHE_TTL_HOURS = 2;

    public static function getCurrentCompanyResource(): array|null
    {
        return Cache::remember(
            self::cacheKey(),
            now()->addHours(self::CACHE_TTL_HOURS),
            fn (): array|null => self::resolveCurrentCompanyResource()
        );
    }

    public static function rememberCurrentCompanyResource(): array|null
    {
        self::forgetCurrentCompanyResource();

        return self::getCurrentCompanyResource();
    }

    public static function forgetCurrentCompanyResource(): void
    {
        Cache::forget(self::cacheKey());
    }

    private static function resolveCurrentCompanyResource(): array|null
    {
        $company = app(CompanyRepository::class)->getCurrentCompanyWithRole();

        if (!$company instanceof Company) {
            return null;
        }

        return CompanyResource::make($company)->resolve(request());
    }

    private static function cacheKey(): string
    {
        return 'tenant:'.self::tenantKey().':company';
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
