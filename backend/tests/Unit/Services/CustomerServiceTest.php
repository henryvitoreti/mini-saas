<?php

namespace Tests\Unit\Services;

use App\Models\Customer;
use App\Repositories\CustomerRepository;
use App\Services\CustomerService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\UnitTestCase;

class CustomerServiceTest extends UnitTestCase
{
    private $repository;

    private CustomerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(CustomerRepository::class);
        $this->service = new CustomerService($this->repository);
    }

    public function test_index_preserves_request_and_paginator(): void
    {
        $request = new Request(['search' => 'Ana']);
        $paginator = new LengthAwarePaginator([], 0, 15);

        $this->repository->expects($this->once())->method('paginateSearch')->with($this->identicalTo($request))->willReturn($paginator);
        $this->assertSame($paginator, $this->service->index($request));
    }

    public function test_search_preserves_request_and_collection(): void
    {
        $request = new Request(['search' => 'Ana']);
        $items = new Collection([new Customer]);

        $this->repository->expects($this->once())->method('getSearch')->with($this->identicalTo($request))->willReturn($items);
        $this->assertSame($items, $this->service->search($request));
    }

    public function test_options_formats_and_reindexes_customers(): void
    {
        $customer = new Customer;
        $customer->setRawAttributes(['id' => 7, 'name' => 'Ana', 'document' => '52998224725', 'phone' => '11987654321', 'birth_date' => '1990-05-15']);
        $request = new Request;

        $this->repository->expects($this->once())->method('getOptions')->with($request)->willReturn(new Collection([8 => $customer]));
        $this->assertSame([['value' => 7, 'label' => 'Ana', 'row' => [
            'id' => 7, 'name' => 'Ana', 'phone' => '(11) 9 8765-4321', 'birth_date' => '15/05/1990', 'document' => '529.982.247-25',
        ]]], $this->service->options($request));
    }

    public function test_options_returns_empty_array(): void
    {
        $request = new Request;

        $this->repository->expects($this->once())->method('getOptions')->with($request)->willReturn(new Collection);
        $this->assertSame([], $this->service->options($request));
    }

    public function test_show_returns_customer(): void
    {
        $customer = new Customer;

        $this->repository->expects($this->once())->method('findOrFail')->with(7)->willReturn($customer);
        $this->assertSame($customer, $this->service->show(7));
    }

    public function test_show_propagates_missing_customer(): void
    {
        $exception = new ModelNotFoundException;

        $this->repository->expects($this->once())->method('findOrFail')->with(7)->willThrowException($exception);
        $this->expectExceptionObject($exception);
        $this->service->show(7);
    }

    public function test_store_passes_data_to_repository(): void
    {
        $customer = new Customer;
        $data = ['name' => 'Ana', 'document' => '52998224725', 'type' => 'individual'];

        $this->repository->expects($this->once())->method('create')->with($data)->willReturn($customer);
        $this->assertSame($customer, $this->service->store($data));
    }

    public static function found(): array
    {
        return [[true], [false]];
    }

    #[DataProvider('found')]
    public function test_update_removes_immutable_fields(bool $found): void
    {
        $result = $found ? new Customer : null;

        $this->repository->expects($this->once())->method('update')->with(7, ['name' => 'Ana'])->willReturn($result);
        $this->assertSame($result, $this->service->update(7, ['name' => 'Ana', 'document' => 'changed', 'type' => 'company']));
    }

    #[DataProvider('found')]
    public function test_delete_preserves_repository_result(bool $found): void
    {
        $this->repository->expects($this->once())->method('delete')->with(7)->willReturn($found);
        $this->assertSame($found, $this->service->delete(7));
    }
}
