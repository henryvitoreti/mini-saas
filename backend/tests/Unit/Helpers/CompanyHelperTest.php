<?php

namespace Tests\Unit\Helpers;

use App\Helpers\CompanyHelper;
use App\Models\Company;
use App\Models\Role;
use App\Models\Tenant;
use App\Repositories\CompanyRepository;
use Illuminate\Support\Facades\Cache;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\UnitTestCase;

class CompanyHelperTest extends UnitTestCase
{
    public function test_cache_miss_resolves_company_and_role_with_two_hour_expiry(): void
    {
        $this->freezeTime();
        $this->bindRequest();

        $company = new Company;
        $company->setRawAttributes(['id' => 1, 'name' => 'Acme', 'role_id' => 7]);

        $role = new Role;
        $role->setRawAttributes(['id' => 7, 'name' => 'Admin', 'slug' => 'admin', 'description' => null, 'is_active' => true]);

        $company->setRelation('role', $role);
        $repository = Mockery::mock(CompanyRepository::class);
        $repository->shouldReceive('getCurrentCompanyWithRole')->once()->andReturn($company);

        $this->app->instance(CompanyRepository::class, $repository);

        Cache::shouldReceive('remember')->once()->with('tenant:host:'.sha1('example.test').':company',
            Mockery::on(fn ($expiry) => $expiry->equalTo(now()->addHours(2))), Mockery::type('Closure'))
            ->andReturnUsing(fn ($key, $expiry, $resolve) => $resolve());

        $result = CompanyHelper::getCurrentCompanyResource();

        $this->assertSame(1, $result['id']);
        $this->assertSame('Acme', $result['name']);
        $this->assertSame(['id' => 7, 'name' => 'Admin', 'slug' => 'admin', 'description' => null, 'is_active' => true], $result['role']);
    }

    public function test_missing_company_returns_null(): void
    {
        $repository = Mockery::mock(CompanyRepository::class);
        $repository->shouldReceive('getCurrentCompanyWithRole')->once()->andReturnNull();

        $this->app->instance(CompanyRepository::class, $repository);
        Cache::shouldReceive('remember')->once()->andReturnUsing(fn ($key, $expiry, $resolve) => $resolve());

        $this->assertNull(CompanyHelper::getCurrentCompanyResource());
    }

    public function test_cache_hit_does_not_resolve_repository(): void
    {
        $this->app->instance(CompanyRepository::class, Mockery::mock(CompanyRepository::class));
        Cache::shouldReceive('remember')->once()->andReturn(['id' => 1]);

        $this->assertSame(['id' => 1], CompanyHelper::getCurrentCompanyResource());
    }

    public static function contexts(): array
    {
        return [['alpha', true, 'alpha'], ['beta', true, 'beta'], [null, true, 'host:'.sha1('example.test')], [null, false, 'central']];
    }

    #[DataProvider('contexts')]
    public function test_refresh_and_forget_use_context_specific_key(?string $id, bool $request, string $suffix): void
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

        $key = 'tenant:'.$suffix.':company';
        Cache::shouldReceive('forget')->once()->with($key)->ordered();
        Cache::shouldReceive('remember')->once()->with($key, Mockery::any(), Mockery::type('Closure'))->ordered()->andReturn(['id' => 1]);

        $this->assertSame(['id' => 1], CompanyHelper::rememberCurrentCompanyResource());
    }
}
