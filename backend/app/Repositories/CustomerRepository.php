<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class CustomerRepository extends BaseRepository
{
    protected array $searchFields = [
        'name' => 'like',
        'document' => 'like',
        'email' => 'like',
        'phone' => 'like',
        'secondary_phone' => 'like',
    ];

    protected array $filterFields = [
        'id',
        'name' => 'like',
        'document',
        'email',
        'type',
        'phone',
        'secondary_phone',
        'zip_code',
        'street' => 'like',
        'number',
        'district' => 'like',
        'city' => 'like',
        'state',
        'is_active',
    ];

    protected array $dateFilterFields = [
        'birth_date',
        'created_at',
        'updated_at',
    ];

    protected string $defaultDateFilterField = 'created_at';

    public function model(): string
    {
        return Customer::class;
    }

    public function getOptions(Request $request): Collection
    {
        $query = $this->query()
            ->where('is_active', true);

        $this->applySearchFilter($query, $request);
        $this->applyRequestOrdering($query, $request);
        $this->applyRequestPage($query, $request);

        return $query->get(['id', 'name', 'document', 'phone', 'birth_date']);
    }
}
