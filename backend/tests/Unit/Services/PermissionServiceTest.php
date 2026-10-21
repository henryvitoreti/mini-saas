<?php

namespace Tests\Unit\Services;

use App\Models\Permission;
use App\Repositories\PermissionRepository;
use App\Services\PermissionService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PermissionServiceTest extends TestCase
{
    public static function results(): array
    {
        return [[true], [false]];
    }

    #[DataProvider('results')]
    public function test_search_passes_request_and_preserves_results(bool $empty): void
    {
        $request = new Request(['search' => 'customers']);
        $items = new Collection($empty ? [] : [new Permission]);

        $repository = $this->createMock(PermissionRepository::class);
        $repository->expects($this->once())->method('getSearch')->with($this->identicalTo($request))->willReturn($items);

        $this->assertSame($items, (new PermissionService($repository))->search($request));
    }
}
