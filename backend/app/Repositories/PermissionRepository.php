<?php

namespace App\Repositories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

class PermissionRepository extends BaseRepository
{
    public function model(): string
    {
        return Permission::class;
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
