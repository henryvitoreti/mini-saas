<?php

namespace App\Repositories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

class PermissionRepository extends BaseRepository
{
    protected array $searchFields = [
        'name' => 'like',
        'slug' => 'like',
        'base_front_url' => 'like',
        'base_api_url' => 'like',
        'group' => 'like',
    ];

    protected array $filterFields = [
        'id',
        'name' => 'like',
        'slug',
        'base_front_url' => 'like',
        'base_api_url' => 'like',
        'group',
        'is_base',
    ];

    public function model(): string
    {
        return Permission::class;
    }

    /**
     * @return array<int, int>
     */
    public function getBasePermissionIds(): array
    {
        return $this->query()
            ->where('is_base', true)
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();
    }

    /**
     * @return Collection<int, Permission>
     */
    public function getByRoleId(int $roleId): Collection
    {
        return $this->query()
            ->join('permission_role', 'permission_role.permission_id', '=', 'permissions.id')
            ->where('permission_role.role_id', $roleId)
            ->orderBy('permissions.id')
            ->get([
                'permissions.id',
                'permissions.name',
                'permissions.slug',
                'permissions.base_front_url',
                'permissions.base_api_url',
                'permission_role.show_locked_routes',
                'permission_role.is_active',
            ]);
    }
}
