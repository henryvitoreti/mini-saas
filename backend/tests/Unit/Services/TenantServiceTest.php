<?php

namespace Tests\Unit\Services;

use App\Models\Company;
use App\Models\Role;
use App\Models\Tenant;
use App\Repositories\CompanyRepository;
use App\Repositories\DomainRepository;
use App\Repositories\RoleRepository;
use App\Repositories\TenantRepository;
use App\Repositories\UserRepository;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Stancl\Tenancy\Tenancy;
use Tests\Unit\UnitTestCase;

class TenantServiceTest extends UnitTestCase
{
    private $tenants;

    private $roles;

    private $users;

    private $companies;

    private $domains;

    private $tenancy;

    private TenantService $service;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'https://api.example.test']);
        $this->tenants = Mockery::mock(TenantRepository::class);
        $this->roles = Mockery::mock(RoleRepository::class);
        $this->users = Mockery::mock(UserRepository::class);
        $this->companies = Mockery::mock(CompanyRepository::class);
        $this->domains = Mockery::mock(DomainRepository::class);
        $this->tenancy = Mockery::mock(Tenancy::class);
        $this->tenancy->initialized = false;
        $this->app->instance(Tenancy::class, $this->tenancy);
        $this->service = new TenantService($this->tenants, $this->roles, $this->users, $this->companies, $this->domains);
    }

    private function data(): array
    {
        return ['id' => 'acme', 'company' => ['name' => 'Acme', 'role_id' => '7', 'document' => '123'],
            'user' => ['name' => 'Ana', 'email' => 'ana@example.test', 'password' => 'secret']];
    }

    private function tenant(): Tenant
    {
        $tenant = Mockery::mock(Tenant::class)->makePartial();
        $tenant->setRawAttributes(['id' => 'acme', 'role_id' => 7, 'data' => '{"name":"Old","other":"preserved"}']);

        return $tenant;
    }

    private function lifecycle(Tenant $tenant, int $cycles = 1, bool $alreadyInitialized = false): void
    {
        $this->tenancy->initialized = $alreadyInitialized;
        $this->tenancy->shouldReceive('initialize')->times($cycles)->with($tenant)->andReturnUsing(function (): void {
            $this->assertFalse($this->tenancy->initialized);
            $this->tenancy->initialized = true;
        });

        $this->tenancy->shouldReceive('end')->times($cycles + ($alreadyInitialized ? 1 : 0))->andReturnUsing(function (): void {
            $this->tenancy->initialized = false;
        });
    }

    private function transaction(): void
    {
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());
    }

    public function test_index_preserves_request_and_pagination(): void
    {
        $request = new Request(['search' => 'acme']);
        $paginator = new LengthAwarePaginator([], 0, 15);

        $this->tenants->shouldReceive('paginateSearch')->once()->with($request)->andReturn($paginator);
        $this->assertSame($paginator, $this->service->index($request));
    }

    public static function companies(): array
    {
        return [[false, false], [true, false], [true, true]];
    }

    #[DataProvider('companies')]
    public function test_show_loads_company_and_always_ends_tenancy(bool $hasCompany, bool $alreadyInitialized): void
    {
        $tenant = $this->tenant();

        $this->lifecycle($tenant, alreadyInitialized: $alreadyInitialized);
        $this->tenants->shouldReceive('findWithDomains')->once()->with('acme')->andReturn($tenant);

        $company = new Company;
        $company->setRawAttributes(['id' => 1, 'name' => 'Acme', 'role_id' => 7, 'document' => '123', 'private_field' => 'hidden']);

        $this->companies->shouldReceive('getCurrentCompany')->once()->andReturn($hasCompany ? $company : null);
        $this->assertSame($tenant, $this->service->show('acme'));
        $this->assertFalse($this->tenancy->initialized);

        if ($hasCompany) {
            $this->assertSame('Acme', $tenant->company_data['name']);
            $this->assertSame('123', $tenant->company_data['document']);
            $this->assertArrayNotHasKey('id', $tenant->company_data);
            $this->assertArrayNotHasKey('private_field', $tenant->company_data);
        } else {
            $this->assertNull($tenant->company_data);
        }
    }

    public function test_show_ends_tenancy_when_company_lookup_fails(): void
    {
        $tenant = $this->tenant();

        $this->lifecycle($tenant);
        $this->tenants->shouldReceive('findWithDomains')->once()->with('acme')->andReturn($tenant);

        $exception = new RuntimeException('company failed');

        $this->companies->shouldReceive('getCurrentCompany')->once()->andThrow($exception);

        try {
            $this->service->show('acme');
            $this->fail('Expected company failure.');
        } catch (RuntimeException $actual) {
            $this->assertSame($exception, $actual);
            $this->assertFalse($this->tenancy->initialized);
        }
    }

    public static function creationFailures(): array
    {
        return [['inactive', 'O perfil de acesso informado não está disponível.'],
            ['domain', 'O domínio informado já está em uso.'], ['create', 'Não foi possível criar o tenant.'],
            ['url', 'APP_URL não está configurada corretamente.']];
    }

    #[DataProvider('creationFailures')]
    public function test_store_rejects_invalid_preconditions(string $failure, string $message): void
    {
        $this->roles->shouldReceive('findActiveById')->once()->with(7)->andReturn($failure === 'inactive' ? null : new Role);

        if ($failure === 'url') {
            config(['app.url' => 'invalid']);
        }

        if (in_array($failure, ['domain', 'create'], true)) {
            $this->domains->shouldReceive('domainExists')->once()->with('acme.api.example.test')->andReturn($failure === 'domain');
        }

        if ($failure === 'create') {
            $this->tenants->shouldReceive('create')->once()->andReturnNull();
        } else {
            $this->tenants->shouldNotReceive('create');
        }

        $this->companies->shouldNotReceive('saveCurrentCompany');
        $this->users->shouldNotReceive('create');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($message);
        $this->service->store($this->data());
    }

    public static function provisioning(): array
    {
        return [['success', false], ['success', true], ['company', false], ['user', false], ['reload', false]];
    }

    #[DataProvider('provisioning')]
    public function test_store_provisions_initial_data_and_cleans_up_on_failure(string $outcome, bool $alreadyInitialized): void
    {
        $tenant = $this->tenant();

        $this->lifecycle($tenant, alreadyInitialized: $alreadyInitialized);
        $this->transaction();
        $this->roles->shouldReceive('findActiveById')->once()->with(7)->andReturn(new Role);
        $this->domains->shouldReceive('domainExists')->once()->with('acme.api.example.test')->andReturnFalse();
        $this->tenants->shouldReceive('create')->once()->with(['id' => 'acme', 'active' => true, 'role_id' => 7, 'data' => ['name' => 'Acme']])->andReturn($tenant);

        $tenant->shouldReceive('createDomain')->once()->with('acme.api.example.test');
        $exception = new RuntimeException('provisioning failed');
        $companyExpectation = $this->companies->shouldReceive('saveCurrentCompany')->once()
            ->with(['name' => 'Acme', 'role_id' => 7, 'document' => '123']);

        if ($outcome === 'company') {
            $companyExpectation->andThrow($exception);
            $this->users->shouldNotReceive('create');
        } else {
            $companyExpectation->andReturn(new Company);
            $userExpectation = $this->users->shouldReceive('create')->once()
                ->with(['name' => 'Ana', 'email' => 'ana@example.test', 'password' => 'secret', 'is_active' => true]);
            if ($outcome === 'user') {
                $userExpectation->andThrow($exception);
            }
        }

        if (in_array($outcome, ['success', 'reload'], true)) {
            $reload = $this->tenants->shouldReceive('findWithDomains')->once()->with('acme');
            $outcome === 'reload' ? $reload->andThrow($exception) : $reload->andReturn($tenant);
        }

        if ($outcome === 'success') {
            $tenant->shouldNotReceive('delete');
            $this->assertSame($tenant, $this->service->store($this->data()));
        } else {
            $tenant->shouldReceive('delete')->once()->andReturnTrue();
            try {
                $this->service->store($this->data());
                $this->fail('Expected provisioning failure.');
            } catch (RuntimeException $actual) {
                $this->assertSame($exception, $actual);
            }
        }

        $this->assertFalse($this->tenancy->initialized);
    }

    public function test_update_preserves_immutable_company_fields_and_unrelated_tenant_data(): void
    {
        $tenant = $this->tenant();

        $this->lifecycle($tenant, 2, true);
        $this->transaction();
        $this->tenants->shouldReceive('findWithDomains')->twice()->with('acme')->andReturn($tenant);
        $this->roles->shouldReceive('findActiveById')->once()->with(7)->andReturn(new Role);
        $this->companies->shouldReceive('saveCurrentCompany')->once()->with(['name' => 'Acme', 'role_id' => 7])->andReturn(new Company);

        $tenant->shouldReceive('save')->once()->andReturnUsing(function () use ($tenant): bool {
            $this->assertFalse($this->tenancy->initialized);
            $this->assertSame(7, $tenant->role_id);
            $this->assertSame(['name' => 'Acme', 'other' => 'preserved'], $tenant->data);

            return true;
        });

        $this->companies->shouldReceive('getCurrentCompany')->once()->andReturnNull();
        $this->users->shouldNotReceive('create');

        $data = $this->data();
        $data['id'] = 'attempted-change';

        $this->assertSame($tenant, $this->service->update('acme', $data));
        $this->assertSame('acme', $tenant->id);
        $this->assertFalse($this->tenancy->initialized);
    }

    public function test_update_rejects_inactive_role_before_switching_tenant(): void
    {
        $tenant = $this->tenant();

        $this->tenants->shouldReceive('findWithDomains')->once()->with('acme')->andReturn($tenant);
        $this->roles->shouldReceive('findActiveById')->once()->with(7)->andReturnNull();
        $this->tenancy->shouldNotReceive('initialize');

        $tenant->shouldNotReceive('save');

        $this->expectExceptionMessage('O perfil de acesso informado não está disponível.');
        $this->service->update('acme', $this->data());
    }

    public function test_update_ends_tenancy_and_does_not_save_central_data_when_company_save_fails(): void
    {
        $tenant = $this->tenant();

        $this->lifecycle($tenant);
        $this->transaction();
        $this->tenants->shouldReceive('findWithDomains')->once()->with('acme')->andReturn($tenant);
        $this->roles->shouldReceive('findActiveById')->once()->with(7)->andReturn(new Role);

        $exception = new RuntimeException('save failed');
        $this->companies->shouldReceive('saveCurrentCompany')->once()->andThrow($exception);
        $tenant->shouldNotReceive('save');

        try {
            $this->service->update('acme', $this->data());
            $this->fail('Expected company save failure.');
        } catch (RuntimeException $actual) {
            $this->assertSame($exception, $actual);
            $this->assertFalse($this->tenancy->initialized);
        }
    }
}
