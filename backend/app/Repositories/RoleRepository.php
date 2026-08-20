<?php

namespace App\Repositories;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleRepository extends BaseRepository
{
    protected array $searchFields = [
        'name' => 'like',
        'slug' => 'like',
    ];

    protected array $filterFields = [
        'id',
        'name' => 'like',
        'slug',
        'is_active',
        'can_modify',
    ];

    public function model(): string
    {
        return Role::class;
    }

    public function findActiveById(int $id): ?Role
    {
        return $this->query()
            ->where('is_active', true)
            ->find($id);
    }

    public function firstById(array $columns = ['*']): ?Role
    {
        return $this->query()
            ->orderBy('id')
            ->first($columns);
    }

    public function findWithPermissions(int $id): ?Role
    {
        return $this->query()
            ->with('permissions')
            ->find($id);
    }

    public function getOptions(Request $request): array
    {
        $query = $this->search($request);
        $query->selectRaw('name as label, id as value, NULL as disabled')
            ->where('is_active', true);

        if ($request->filled('sort_by')) {
            $query = $this->applyRequestOrdering($query, $request);
        } else {
            $query->orderBy('name');
        }

        $query = $this->applyRequestPage($query, $request);

        return $query->get()->toArray();
    }

    public function hasActivePermissionForApiUrls(int $id, array $baseApiUrls): bool
    {
        if ($baseApiUrls === []) {
            return false;
        }

        $role = $this->find($id);

        return $role?->permissions()
            ->whereIn('permissions.base_api_url', $baseApiUrls)
            ->wherePivot('is_active', true)
            ->exists() ?? false;
    }
}
