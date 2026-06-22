<?php

namespace App\Services;

use App\Models\Customer;
use App\Repositories\CustomerRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerService
{
    public function __construct(private readonly CustomerRepository $customerRepository)
    {}

    public function index(Request $request): LengthAwarePaginator
    {
        return $this->customerRepository->paginateSearch($request);
    }

    public function search(Request $request): Collection
    {
        return $this->customerRepository->getSearch($request);
    }

    public function options(Request $request): array
    {
        return $this->customerRepository->getOptions($request)
            ->map(fn (Customer $customer): array => [
                'value' => $customer->id,
                'label' => $customer->name,
                'row' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->formatted_phone,
                    'birth_date' => $customer->formatted_birth_date,
                    'document' => $customer->formatted_document,
                ],
            ])
            ->values()
            ->all();
    }

    public function show(int $id): Customer
    {
        return $this->customerRepository->findOrFail($id);
    }

    public function store(array $data): Customer
    {
        return $this->customerRepository->create($data);
    }

    public function update(int $id, array $data): Customer|null
    {
        unset($data['document']);

        return $this->customerRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->customerRepository->delete($id);
    }
}
