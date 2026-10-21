<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Constants\RequestAttribute;
use App\Http\Controllers\Api\TenantController;
use App\Http\Requests\TenantRequest;
use App\Models\Tenant;
use App\Services\TenantService;
use Error;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Unit\UnitTestCase;

class TenantControllerTest extends UnitTestCase
{
    private $service;

    private TenantController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bindRequest();
        $this->service = $this->createMock(TenantService::class);
        $this->controller = new TenantController($this->service);
    }

    private function tenant(): Tenant
    {
        $tenant = new Tenant;
        $tenant->setRawAttributes(['id' => 'acme', 'active' => true, 'role_id' => 7, 'data' => '{"name":"Acme"}']);
        $tenant->setRelation('domains', new Collection);

        return $tenant;
    }

    public static function lists(): array
    {
        return [[false, false], [true, false], [false, true]];
    }

    #[DataProvider('lists')]
    public function test_index_serializes_paginated_tenants(bool $details, bool $empty): void
    {
        $request = $this->bindRequest(['with_details' => $details ? 'true' : 'false']);
        $this->service->expects($this->once())->method('index')->with($request)
            ->willReturn(new LengthAwarePaginator(new Collection($empty ? [] : [$this->tenant()]), $empty ? 0 : 1, 15, 1));

        $response = $this->controller->index($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(! $details, $request->attributes->get(RequestAttribute::USE_SIMPLE_RESOURCE_RESPONSE));

        $data = $response->getData(true)['data'];

        $this->assertSame($empty ? 0 : 1, $data['total']);
        $this->assertCount($empty ? 0 : 1, $data['items']);

        if (! $empty) {
            $this->assertSame('acme', $data['items'][0]['id']);
            $this->assertSame($details, array_key_exists('role_id', $data['items'][0]));
        }
    }

    public function test_show_returns_detailed_tenant(): void
    {
        $this->service->expects($this->once())->method('show')->with('acme')->willReturn($this->tenant());

        $response = $this->controller->show('acme');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('acme', $response->getData(true)['data']['id']);
        $this->assertSame(7, $response->getData(true)['data']['role_id']);
    }

    public function test_show_propagates_failure(): void
    {
        $exception = new RuntimeException('missing');

        $this->service->expects($this->once())->method('show')->with('missing')->willThrowException($exception);
        $this->expectExceptionObject($exception);
        $this->controller->show('missing');
    }

    public static function writes(): array
    {
        return [['store', 'success'], ['update', 'success'], ['store', 'business'], ['update', 'business'],
            ['store', 'unexpected'], ['update', 'unexpected']];
    }

    #[DataProvider('writes')]
    public function test_write_outcomes(string $method, string $outcome): void
    {
        $data = ['id' => 'acme', 'company' => ['name' => 'Acme']];
        $request = $method === 'update' ? $this->validatedRequest(TenantRequest::class, $data)
            : TenantRequest::create('/api/tenants', 'POST', $data);

        $this->app->instance('request', $request);

        $expectation = $this->service->expects($this->once())->method($method)
            ->with(...($method === 'store' ? [$data] : ['acme', $data]));

        if ($outcome === 'success') {
            $expectation->willReturn($this->tenant());
        } else {
            $expectation->willThrowException($outcome === 'business' ? new RuntimeException('Domínio em uso.') : new Error('private detail'));
        }

        $response = $method === 'store' ? $this->controller->store($request) : $this->controller->update($request, 'acme');

        $this->assertSame($outcome === 'success' ? ($method === 'store' ? 201 : 200) : 400, $response->getStatusCode());

        $body = $response->getData(true);

        if ($outcome === 'success') {
            $this->assertSame('acme', $body['data']['id']);
        } else {
            $this->assertSame($outcome === 'business' ? 'Domínio em uso.' : ($method === 'store'
                ? 'Não foi possível criar o domínio.' : 'Não foi possível atualizar o domínio.'), $body['message']);
        }
    }
}
