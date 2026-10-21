<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Http\Controllers\Api\PermissionController;
use App\Models\Permission;
use App\Services\PermissionService;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\UnitTestCase;

class PermissionControllerTest extends UnitTestCase
{
    public static function results(): array
    {
        return ['empty' => [false], 'populated' => [true]];
    }

    #[DataProvider('results')]
    public function test_search_serializes_permissions(bool $populated): void
    {
        $request = $this->bindRequest(['search' => 'customers']);

        $permission = new Permission;
        $permission->setRawAttributes(['id' => 7, 'name' => 'Customers', 'slug' => 'customers', 'is_base' => true]);
        $permission->setRelation('pivot', null);

        $service = $this->createMock(PermissionService::class);
        $service->expects($this->once())->method('search')->with($this->identicalTo($request))
            ->willReturn(new Collection($populated ? [$permission] : []));

        $response = (new PermissionController($service))->search($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['message' => 'Operação realizada com sucesso.', 'data' => $populated ? [[
            'id' => 7, 'name' => 'Customers', 'slug' => 'customers', 'base_front_url' => null,
            'base_api_url' => null, 'group' => null, 'is_base' => true,
            'show_locked_routes' => null, 'is_active' => null,
        ]] : []], $response->getData(true));
    }
}
