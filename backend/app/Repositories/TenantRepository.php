<?php

namespace App\Repositories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TenantRepository extends BaseRepository
{
    protected array $searchFields = [
        'id' => 'like',
    ];

    protected array $filterFields = [
        'id',
        'active',
    ];

    protected array $dateFilterFields = [
        'created_at',
        'updated_at',
    ];

    protected string $defaultDateFilterField = 'created_at';

    public function model(): string
    {
        return Tenant::class;
    }

    public function search(Request $request): Builder
    {
        return parent::search($request)
            ->with('domains');
    }

    public function findWithDomains(string $id): Tenant
    {
        return $this->query()
            ->with('domains')
            ->findOrFail($id);
    }
}
