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
    ];

    public function model(): string
    {
        return Role::class;
    }

    public function findActiveById(int $id): Role|null
    {
        return $this->query()
            ->where('is_active', true)
            ->find($id);
    }

    public function firstById(array $columns = ['*']): Role|null
    {
        return $this->query()
            ->orderBy('id')
            ->first($columns);
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
}
