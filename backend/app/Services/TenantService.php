<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\Tenant;
use App\Repositories\CompanyRepository;
use App\Repositories\DomainRepository;
use App\Repositories\RoleRepository;
use App\Repositories\TenantRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedById;
use Throwable;

class TenantService
{
    public function __construct(
        private readonly TenantRepository $tenantRepository,
        private readonly RoleRepository $roleRepository,
        private readonly UserRepository $userRepository,
        private readonly CompanyRepository $companyRepository,
        private readonly DomainRepository $domainRepository
    ) {}

    public function index(Request $request): LengthAwarePaginator
    {
        return $this->tenantRepository->paginateSearch($request);
    }

    /**
     * @throws TenantCouldNotBeIdentifiedById
     */
    public function show(string $id): Tenant
    {
        $tenant = $this->tenantRepository->findWithDomains($id);
        $this->fillTenantCompanyData($tenant);

        return $tenant;
    }

    /**
     * @throws Throwable
     */
    public function store(array $data): Tenant
    {
        $tenant = null;

        try {
            $roleId = (int)$data['company']['role_id'];
            $this->ensureActiveRole($roleId);

            $tenant = $this->createTenant($data, $roleId);
            $this->createTenantInitialData($tenant, $data, $roleId);

            return $this->tenantRepository->findWithDomains($tenant->id);
        } catch (Throwable $throwable) {
            $tenant?->delete();
            throw $throwable;
        } finally {
            if (tenancy()->initialized) {
                tenancy()->end();
            }
        }
    }

    /**
     * @throws Throwable|TenantCouldNotBeIdentifiedById
     */
    public function update(string $id, array $data): Tenant
    {
        unset($data['id'], $data['user'], $data['company']['document']);

        $tenant = $this->tenantRepository->findWithDomains($id);
        $companyData = $data['company'] ?? [];

        if (!is_array($companyData)) {
            throw new RuntimeException('Os dados da empresa não foram informados corretamente.');
        }

        $roleId = (int)$companyData['role_id'];
        $this->ensureActiveRole($roleId);

        $this->updateTenantCompanyData($tenant, $companyData, $roleId);
        $this->updateTenantCentralData($tenant, $companyData, $roleId);

        return $this->show($tenant->id);
    }

    private function createTenant(array $data, int $roleId): Tenant
    {
        $slug = (string) $data['id'];
        $domain = $this->buildApiDomain($slug);

        if ($this->domainRepository->domainExists($domain)) {
            throw new RuntimeException('O domínio informado já está em uso.');
        }

        $tenant = $this->tenantRepository->create([
            'id' => $slug,
            'active' => true,
            'role_id' => $roleId,
            'data' => [
                'name' => $data['company']['name'],
            ],
        ]);

        if (!$tenant instanceof Tenant) {
            throw new RuntimeException('Não foi possível criar o tenant.');
        }

        $tenant->createDomain($domain);

        return $tenant;
    }

    /**
     * @throws Throwable|TenantCouldNotBeIdentifiedById
     */
    private function createTenantInitialData(Tenant $tenant, array $data, int $roleId): void
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        tenancy()->initialize($tenant);

        DB::transaction(function () use ($data, $roleId): void {
            $companyData = $data['company'];
            $companyData['role_id'] = $roleId;

            $this->companyRepository->saveCurrentCompany($companyData);

            $this->userRepository->create([
                'name' => $data['user']['name'],
                'email' => $data['user']['email'],
                'password' => $data['user']['password'],
                'is_active' => true,
            ]);
        });
    }

    /**
     * @throws Throwable|TenantCouldNotBeIdentifiedById
     */
    private function updateTenantCompanyData(Tenant $tenant, array $companyData, int $roleId): void
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        tenancy()->initialize($tenant);

        try {
            DB::transaction(function () use ($companyData, $roleId): void {
                $companyData['role_id'] = $roleId;
                $this->companyRepository->saveCurrentCompany($companyData);
            });
        } finally {
            if (tenancy()->initialized) {
                tenancy()->end();
            }
        }
    }

    private function updateTenantCentralData(Tenant $tenant, array $companyData, int $roleId): void
    {
        $tenantData = is_array($tenant->data) ? $tenant->data : [];
        $tenantData['name'] = $companyData['name'];

        $tenant->role_id = $roleId;
        $tenant->data = $tenantData;
        $tenant->save();
    }

    /**
     * @throws TenantCouldNotBeIdentifiedById
     */
    private function fillTenantCompanyData(Tenant $tenant): void
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        tenancy()->initialize($tenant);

        try {
            $company = $this->companyRepository->getCurrentCompany();

            if ($company === null) {
                return;
            }

            $tenant->setAttribute('company_data', $company->only([
                'name',
                'role_id',
                'document',
                'email',
                'phone',
                'secondary_phone',
                'zip_code',
                'street',
                'number',
                'complement',
                'district',
                'city',
                'state',
                'logo_path',
                'notes',
            ]));
        } finally {
            if (tenancy()->initialized) {
                tenancy()->end();
            }
        }
    }

    private function ensureActiveRole(int $roleId): void
    {
        $role = $this->roleRepository->findActiveById($roleId);

        if ($role === null) {
            throw new RuntimeException('O perfil de acesso informado não está disponível.');
        }
    }

    private function buildApiDomain(string $slug): string
    {
        return Domain::buildApiDomain($slug);
    }
}
