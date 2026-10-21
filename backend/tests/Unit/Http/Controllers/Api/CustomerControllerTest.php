<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Constants\RequestAttribute;
use App\Enums\PersonType;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    private CustomerService&MockObject $service;

    private CustomerController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->createMock(CustomerService::class);
        $this->controller = new CustomerController($this->service);
    }

    public static function resourceModes(): array
    {
        return [
            'simple by default' => [[], true],
            'explicitly simple' => [['with_details' => 'false'], true],
            'detailed' => [['with_details' => 'true'], false],
        ];
    }

    #[DataProvider('resourceModes')]
    public function test_index_returns_paginated_customers(array $query, bool $simple): void
    {
        $request = $this->bindRequest($query);
        $paginator = new LengthAwarePaginator(
            new Collection([$this->customer()]), 3, 1, 2,
            ['path' => '/api/customers']
        );

        $this->service->expects($this->once())->method('index')->with($this->identicalTo($request))
            ->willReturn($paginator);

        $response = $this->controller->index($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($simple, $request->attributes->get(RequestAttribute::USE_SIMPLE_RESOURCE_RESPONSE));
        $this->assertSame([
            'message' => 'Operação realizada com sucesso.',
            'data' => [
                'items' => [$this->expectedCustomer($simple)],
                'current_page' => 2,
                'last_page' => 3,
                'per_page' => 1,
                'total' => 3,
                'from' => 2,
                'to' => 2,
                'prev_page_url' => '/api/customers?page=1',
                'next_page_url' => '/api/customers?page=3',
            ],
        ], $response->getData(true));
    }

    public function test_index_returns_empty_pagination_when_no_customers_are_found(): void
    {
        $request = $this->bindRequest();
        $this->service->expects($this->once())->method('index')->with($request)
            ->willReturn(new LengthAwarePaginator(new Collection, 0, 15, 1));

        $response = $this->controller->index($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'items' => [],
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => 15,
            'total' => 0,
            'from' => null,
            'to' => null,
            'prev_page_url' => null,
            'next_page_url' => null,
        ], $response->getData(true)['data']);
    }

    #[DataProvider('resourceModes')]
    public function test_search_returns_customers_in_the_requested_resource_mode(array $query, bool $simple): void
    {
        $request = $this->bindRequest(['search' => 'Maria', ...$query]);
        $this->service->expects($this->once())->method('search')->with($this->identicalTo($request))
            ->willReturn(new Collection([$this->customer()]));

        $response = $this->controller->search($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($simple, $request->attributes->get(RequestAttribute::USE_SIMPLE_RESOURCE_RESPONSE));
        $this->assertSame([
            'message' => 'Operação realizada com sucesso.',
            'data' => [$this->expectedCustomer($simple)],
        ], $response->getData(true));
    }

    public function test_search_returns_an_empty_list_when_no_customers_are_found(): void
    {
        $request = $this->bindRequest(['search' => 'inexistente']);
        $this->service->expects($this->once())->method('search')->with($request)
            ->willReturn(new Collection);

        $response = $this->controller->search($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'message' => 'Operação realizada com sucesso.',
            'data' => [],
        ], $response->getData(true));
    }

    public static function customerOptions(): array
    {
        return [
            'available options' => [[[
                'value' => 7,
                'label' => 'Maria Silva',
                'row' => ['id' => 7, 'name' => 'Maria Silva', 'phone' => '(11) 9 8765-4321',
                    'birth_date' => '15/05/1990', 'document' => '529.982.247-25'],
            ]]],
            'no options' => [[]],
        ];
    }

    #[DataProvider('customerOptions')]
    public function test_options_returns_the_service_options(array $options): void
    {
        $request = $this->bindRequest(['search' => 'Maria']);
        $this->service->expects($this->once())->method('options')->with($this->identicalTo($request))
            ->willReturn($options);

        $response = $this->controller->options($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'message' => 'Operação realizada com sucesso.',
            'data' => ['options' => $options],
        ], $response->getData(true));
    }

    public function test_show_returns_the_detailed_customer(): void
    {
        $this->bindRequest();
        $this->service->expects($this->once())->method('show')->with(7)->willReturn($this->customer());

        $response = $this->controller->show(7);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'message' => 'Operação realizada com sucesso.',
            'data' => $this->expectedCustomer(),
        ], $response->getData(true));
    }

    public function test_show_propagates_the_model_not_found_exception(): void
    {
        $exception = (new ModelNotFoundException)->setModel(Customer::class, [7]);
        $this->service->expects($this->once())->method('show')->with(7)->willThrowException($exception);

        $this->expectExceptionObject($exception);

        $this->controller->show(7);
    }

    public function test_store_passes_only_validated_data_and_returns_the_created_customer(): void
    {
        $request = $this->validatedRequest();
        $this->service->expects($this->once())->method('store')->with($this->validatedData())
            ->willReturn($this->customer());

        $response = $this->controller->store($request);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame([
            'message' => 'Cliente cadastrado com sucesso.',
            'data' => $this->expectedCustomer(),
        ], $response->getData(true));
    }

    public function test_update_passes_the_id_and_validated_data_and_returns_the_updated_customer(): void
    {
        $request = $this->validatedRequest();
        $this->service->expects($this->once())->method('update')->with(7, $this->validatedData())
            ->willReturn($this->customer());

        $response = $this->controller->update($request, 7);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'message' => 'Cliente atualizado com sucesso.',
            'data' => $this->expectedCustomer(),
        ], $response->getData(true));
    }

    public function test_update_returns_not_found_when_the_customer_does_not_exist(): void
    {
        $request = $this->validatedRequest();
        $this->service->expects($this->once())->method('update')->with(7, $this->validatedData())
            ->willReturn(null);

        $response = $this->controller->update($request, 7);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame([
            'message' => 'Cliente não encontrado.',
            'errors' => [],
        ], $response->getData(true));
    }

    public function test_delete_returns_success_when_the_customer_is_deleted(): void
    {
        $this->service->expects($this->once())->method('delete')->with(7)->willReturn(true);

        $response = $this->controller->delete(7);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'message' => 'Cliente excluído com sucesso.',
            'data' => [],
        ], $response->getData(true));
    }

    public function test_delete_returns_not_found_when_the_customer_does_not_exist(): void
    {
        $this->service->expects($this->once())->method('delete')->with(7)->willReturn(false);

        $response = $this->controller->delete(7);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame([
            'message' => 'Cliente não encontrado.',
            'errors' => [],
        ], $response->getData(true));
    }

    private function bindRequest(array $query = []): Request
    {
        $request = Request::create('/api/customers', 'GET', $query);
        $this->app->instance('request', $request);

        return $request;
    }

    private function validatedRequest(): CustomerRequest
    {
        $request = Mockery::mock(CustomerRequest::class)->makePartial();
        $request->initialize();
        $request->merge([...$this->validatedData(), 'unvalidated_field' => 'ignored']);
        $request->shouldReceive('validated')->once()->withNoArgs()->andReturn($this->validatedData());
        $this->app->instance('request', $request);

        return $request;
    }

    private function validatedData(): array
    {
        $customer = $this->customer();

        return array_intersect_key($customer->getAttributes(), array_flip($customer->getFillable()));
    }

    private function customer(): Customer
    {
        $customer = new Customer;
        $customer->setRawAttributes([
            'id' => 7,
            'name' => 'Maria Silva',
            'document' => '52998224725',
            'email' => 'maria@example.com',
            'type' => PersonType::INDIVIDUAL->value,
            'birth_date' => '1990-05-15',
            'phone' => '11987654321',
            'secondary_phone' => '1134567890',
            'zip_code' => '01310100',
            'street' => 'Avenida Paulista',
            'number' => '1000',
            'complement' => 'Sala 10',
            'district' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP',
            'notes' => 'Cliente de teste',
            'is_active' => true,
            'created_at' => '2026-10-01 10:00:00',
            'updated_at' => '2026-10-02 11:30:00',
        ]);

        return $customer;
    }

    private function expectedCustomer(bool $simple = false): array
    {
        $data = [
            'id' => 7,
            'name' => 'Maria Silva',
            'document' => '529.982.247-25',
            'email' => 'maria@example.com',
            'type_label' => 'Pessoa Física',
            'birth_date' => '15/05/1990',
            'phone' => '(11) 9 8765-4321',
            'is_active' => true,
            'created_at' => '01/10/2026 10:00:00',
            'updated_at' => '02/10/2026 11:30:00',
        ];

        if ($simple) {
            return $data;
        }

        return [
            'id' => $data['id'],
            'name' => $data['name'],
            'document' => $data['document'],
            'document_raw' => '52998224725',
            'email' => $data['email'],
            'type' => 'individual',
            'type_label' => $data['type_label'],
            'birth_date' => $data['birth_date'],
            'birth_date_raw' => '1990-05-15',
            'phone' => $data['phone'],
            'phone_raw' => '11987654321',
            'secondary_phone' => '(11) 3456-7890',
            'secondary_phone_raw' => '1134567890',
            'zip_code' => '01310-100',
            'zip_code_raw' => '01310100',
            'street' => 'Avenida Paulista',
            'number' => '1000',
            'complement' => 'Sala 10',
            'district' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP',
            'notes' => 'Cliente de teste',
            'is_active' => $data['is_active'],
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at'],
        ];
    }
}
