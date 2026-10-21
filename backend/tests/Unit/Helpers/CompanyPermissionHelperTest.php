<?php

namespace Tests\Unit\Helpers;

use App\Helpers\CompanyPermissionHelper;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Tenant;
use App\Repositories\CompanyRepository;
use App\Repositories\PermissionRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\UnitTestCase;

class CompanyPermissionHelperTest extends UnitTestCase
{
    public static function roles(): array
    {
        return [[null], [7]];
    }

    #[DataProvider('roles')]
    public function test_get_current_role_id_handles_missing_company(?int $roleId): void
    {
        $company = $roleId === null ? null : new Company(['role_id' => $roleId]);
        $repository = Mockery::mock(CompanyRepository::class);
        $repository->shouldReceive('getCurrentCompany')->once()->andReturn($company);

        $this->app->instance(CompanyRepository::class, $repository);

        $this->assertSame($roleId, CompanyPermissionHelper::getCurrentRoleId());
    }

    #[DataProvider('roles')]
    public function test_cache_miss_resolves_permissions_with_thirty_minute_expiry(?int $roleId): void
    {
        $this->freezeTime();
        $this->bindRequest();

        $company = $roleId === null ? null : new Company(['role_id' => $roleId]);
        $companies = Mockery::mock(CompanyRepository::class);
        $companies->shouldReceive('getCurrentCompany')->once()->andReturn($company);

        $this->app->instance(CompanyRepository::class, $companies);
        $permissions = Mockery::mock(PermissionRepository::class);
        $this->app->instance(PermissionRepository::class, $permissions);

        $permission = new Permission;
        $permission->setRawAttributes(['id' => 7, 'slug' => 'customers', 'is_active' => 1, 'show_locked_routes' => 0]);

        if ($roleId !== null) {
            $permissions->shouldReceive('getByRoleId')->once()->with(7)->andReturn(new Collection([$permission]));
        } else {
            $permissions->shouldNotReceive('getByRoleId');
        }

        Cache::shouldReceive('remember')->once()->with('tenant:host:'.sha1('example.test').':company-permissions',
            Mockery::on(fn ($expiry) => $expiry->equalTo(now()->addMinutes(30))), Mockery::type('Closure'))
            ->andReturnUsing(fn ($key, $expiry, $resolve) => $resolve());

        $this->assertSame($roleId === null ? [] : [['id' => 7, 'slug' => 'customers', 'is_active' => true, 'show_locked_routes' => false]],
            CompanyPermissionHelper::getCurrentPermissions());
    }

    public function test_cache_hit_does_not_query_company(): void
    {
        $this->app->instance(CompanyRepository::class, Mockery::mock(CompanyRepository::class));
        Cache::shouldReceive('remember')->once()->andReturn([['id' => 7]]);
        $this->assertSame([['id' => 7]], CompanyPermissionHelper::getCurrentPermissions());
    }

    public static function contexts(): array
    {
        return [['alpha', true, 'alpha'], ['beta', true, 'beta'], [null, true, 'host:'.sha1('example.test')], [null, false, 'central']];
    }

    #[DataProvider('contexts')]
    public function test_refresh_uses_context_specific_key(?string $id, bool $request, string $suffix): void
    {
        if ($id !== null) {
            $tenant = new Tenant;
            $tenant->setRawAttributes(['id' => $id]);
            $this->app->instance(\Stancl\Tenancy\Contracts\Tenant::class, $tenant);
        }

        if ($request) {
            $this->bindRequest();
        } else {
            $this->app->offsetUnset('request');
        }

        $key = 'tenant:'.$suffix.':company-permissions';
        Cache::shouldReceive('forget')->once()->with($key)->ordered();
        Cache::shouldReceive('remember')->once()->with($key, Mockery::any(), Mockery::type('Closure'))->ordered()->andReturn([['id' => 7]]);
        $this->assertSame([['id' => 7]], CompanyPermissionHelper::rememberCurrentPermissions());
    }
}
