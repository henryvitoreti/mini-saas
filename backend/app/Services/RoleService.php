<?php

namespace App\Services;

use App\Models\Role;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class RoleService
{
    public function __construct(
        private readonly RoleRepository $repository,
        private readonly PermissionRepository $permissionRepository,
    ) {}

    public function index(Request $request): LengthAwarePaginator
    {
        return $this->repository->paginateSearch($request);
    }

    public function search(Request $request): Collection
    {
        return $this->repository->getSearch($request);
    }

    public function options(Request $request): array
    {
        return $this->repository->getOptions($request);
    }

    public function show(int $id): ?Role
    {
        return $this->repository->findWithPermissions($id);
    }

    /**
     * @throws Throwable
     */
    public function store(array $data): Role
    {
        return DB::connection($this->centralConnection())->transaction(function () use ($data): Role {
            $roleData = Arr::only($data, ['name', 'slug', 'description', 'is_active']);
            $roleData['can_modify'] = true;

            $role = $this->repository->create($roleData);

            if (empty($role)) {
                throw new RuntimeException('Não foi possível cadastrar o perfil.');
            }

            $this->syncPermissions($role, $data['permissions'] ?? []);

            return $role->load('permissions');
        });
    }

    /**
     * @throws Throwable
     */
    public function update(int $id, array $data): Role|false|null
    {
        return DB::connection($this->centralConnection())->transaction(function () use ($id, $data): Role|false|null {
            $role = $this->repository->find($id);

            if (empty($role)) {
                return null;
            }

            if (!$role->can_modify) {
                return false;
            }

            $role->update(Arr::only($data, ['name', 'slug', 'description', 'is_active']));
            $this->syncPermissions($role, $data['permissions'] ?? []);

            return $role->refresh()->load('permissions');
        });
    }

    /**
     * @return array{success: bool, message: string|null}
     * @throws Throwable
     */
    public function delete(int $id): array
    {
        $role = $this->repository->find($id);

        if (empty($role)) {
            $response = [
                'success' => false,
                'message' => 'Perfil não encontrado.',
            ];
        } elseif (!$role->can_modify) {
            $response = [
                'success' => false,
                'message' => 'Este perfil é protegido e não pode ser excluído.',
            ];
        } elseif ($role->tenants()->exists() > 0) {
            $response = [
                'success' => false,
                'message' => 'Este perfil está vinculado a um domínio e não pode ser excluído.',
            ];
        } else {
            $response = DB::connection($this->centralConnection())->transaction(function () use ($role): array {
                $role->permissions()->detach();

                return [
                    'success' => (bool) $role->delete(),
                    'message' => null,
                ];
            });
        }

        return $response;
    }

    /**
     * @param  array<int, array{id: int, is_active: bool, show_locked_routes: bool}>  $permissions
     */
    private function syncPermissions(Role $role, array $permissions): void
    {
        $syncData = [];
        $basePermissionIds = $this->permissionRepository->getBasePermissionIds();
        $basePermissionLookup = array_fill_keys($basePermissionIds, true);

        foreach ($permissions as $permission) {
            $permissionId = (int) $permission['id'];
            $isBase = isset($basePermissionLookup[$permissionId]);
            $isActive = $isBase || (bool) $permission['is_active'];

            $syncData[$permissionId] = [
                'is_active' => $isActive,
                'show_locked_routes' => ! $isBase
                    && ! $isActive
                    && (bool)$permission['show_locked_routes'],
            ];
        }

        foreach ($basePermissionIds as $permissionId) {
            $syncData[$permissionId] = [
                'is_active' => true,
                'show_locked_routes' => false,
            ];
        }

        $role->permissions()->sync($syncData);
    }

    private function centralConnection(): ?string
    {
        return $this->repository->getModel()->getConnectionName();
    }
}
