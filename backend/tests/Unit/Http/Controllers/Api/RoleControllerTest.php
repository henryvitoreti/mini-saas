<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Constants\RequestAttribute;
use App\Http\Controllers\Api\RoleController;
use App\Http\Requests\RoleRequest;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Unit\UnitTestCase;

class RoleControllerTest extends UnitTestCase
{
    private $service;

    private RoleController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bindRequest();
        $this->service = $this->createMock(RoleService::class);
        $this->controller = new RoleController($this->service);
    }

    private function role(): Role
    {
        $role = new Role;
        $role->setRawAttributes(['id' => 7, 'name' => 'Admin', 'slug' => 'admin', 'is_active' => true, 'can_modify' => true]);
        $role->setRelation('permissions', new Collection);

        return $role;
    }

    public static function lists(): array
    {
        return [
            ['index', false, false], ['index', false, true], ['index', true, false],
            ['search', false, false], ['search', false, true], ['search', true, false],
        ];
    }

    #[DataProvider('lists')]
    public function test_lists_respect_resource_mode_and_empty_results(string $method, bool $details, bool $empty): void
    {
        $request = $this->bindRequest(['with_details' => $details ? 'true' : 'false']);
        $items = new Collection($empty ? [] : [$this->role()]);
        $result = $method === 'index' ? new LengthAwarePaginator($items, $items->count(), 15, 1) : $items;

        $this->service->expects($this->once())->method($method)->with($this->identicalTo($request))->willReturn($result);

        $response = $this->controller->$method($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(! $details, $request->attributes->get(RequestAttribute::USE_SIMPLE_RESOURCE_RESPONSE));

        $data = $response->getData(true)['data'];

        if ($method === 'index') {
            $this->assertSame($items->count(), $data['total']);
            $this->assertSame(15, $data['per_page']);
            $data = $data['items'];
        }

        $this->assertCount($empty ? 0 : 1, $data);

        if (! $empty) {
            $this->assertSame(7, $data[0]['id']);
            $this->assertSame('Admin', $data[0]['name']);
            $this->assertSame($details, array_key_exists('permissions', $data[0]));
        }
    }

    public function test_options_returns_service_options(): void
    {
        $request = $this->bindRequest(['search' => 'Admin']);
        $options = [['label' => 'Admin', 'value' => 7, 'disabled' => null]];

        $this->service->expects($this->once())->method('options')->with($request)->willReturn($options);
        $this->assertSame(['options' => $options], $this->controller->options($request)->getData(true)['data']);
    }

    public function test_show_returns_role_with_permissions(): void
    {
        $this->service->expects($this->once())->method('show')->with(7)->willReturn($this->role());

        $response = $this->controller->show(7);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(7, $response->getData(true)['data']['id']);
        $this->assertSame([], $response->getData(true)['data']['permissions']);
    }

    public function test_show_returns_not_found(): void
    {
        $this->service->expects($this->once())->method('show')->with(7)->willReturn(null);
        $this->assertSame(404, $this->controller->show(7)->getStatusCode());
    }

    public static function writes(): array
    {
        return [['store', 'success', 201], ['update', 'success', 200], ['update', 'missing', 404],
            ['update', 'protected', 422], ['store', 'exception', 500], ['update', 'exception', 500]];
    }

    #[DataProvider('writes')]
    public function test_write_outcomes(string $method, string $outcome, int $status): void
    {
        $data = ['name' => 'Admin'];
        $request = $this->validatedRequest(RoleRequest::class, $data);
        $expectation = $this->service->expects($this->once())->method($method)
            ->with(...($method === 'store' ? [$data] : [7, $data]));

        if ($outcome === 'exception') {
            $exception = new RuntimeException('failure');
            $expectation->willThrowException($exception);
            Log::shouldReceive('error')->once()->with($exception);
        } else {
            $expectation->willReturn(match ($outcome) {
                'missing' => null, 'protected' => false, default => $this->role()
            });
        }

        $response = $method === 'store' ? $this->controller->store($request) : $this->controller->update($request, 7);
        $this->assertSame($status, $response->getStatusCode());

        if ($outcome === 'success') {
            $this->assertSame(7, $response->getData(true)['data']['id']);
        } else {
            $this->assertSame([], $response->getData(true)['errors']);
        }
    }

    public static function deletions(): array
    {
        return [[true, null, 200, 'Perfil excluído com sucesso.'], [false, 'Protegido', 400, 'Protegido'],
            [false, null, 400, 'Ocorreu um erro ao tentar excluir o perfil.']];
    }

    #[DataProvider('deletions')]
    public function test_delete_outcomes(bool $success, ?string $message, int $status, string $expected): void
    {
        $this->service->expects($this->once())->method('delete')->with(7)->willReturn(compact('success', 'message'));

        $response = $this->controller->delete(7);

        $this->assertSame($status, $response->getStatusCode());
        $this->assertSame($expected, $response->getData(true)['message']);
    }

    public function test_delete_handles_exceptions(): void
    {
        $exception = new RuntimeException('failure');
        $this->service->expects($this->once())->method('delete')->with(7)->willThrowException($exception);

        Log::shouldReceive('error')->once()->with($exception);
        $this->assertSame(500, $this->controller->delete(7)->getStatusCode());
    }
}
