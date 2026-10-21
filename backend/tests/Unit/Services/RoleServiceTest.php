<?php

namespace Tests\Unit\Services;

use App\Models\Role;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use App\Services\RoleService;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Unit\UnitTestCase;

class RoleServiceTest extends UnitTestCase
{
    private $repository;

    private $permissions;

    private RoleService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(RoleRepository::class);
        $this->permissions = Mockery::mock(PermissionRepository::class);
        $this->service = new RoleService($this->repository, $this->permissions);
    }

    private function transaction(): void
    {
        $model = Mockery::mock(Role::class)->makePartial();
        $model->shouldReceive('getConnectionName')->once()->andReturn('central');

        $this->repository->shouldReceive('getModel')->once()->andReturn($model);

        $connection = Mockery::mock(Connection::class);

        DB::shouldReceive('connection')->once()->with('central')->andReturn($connection);
        $connection->shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());
    }

    public static function reads(): array
    {
        return [['index', 'paginateSearch'], ['search', 'getSearch'], ['options', 'getOptions'], ['show', 'findWithPermissions']];
    }

    #[DataProvider('reads')]
    public function test_reads_delegate_to_repository(string $method, string $repositoryMethod): void
    {
        $input = $method === 'show' ? 7 : new Request(['search' => 'Admin']);
        $result = match ($method) {
            'index' => new LengthAwarePaginator([], 0, 15), 'search' => new Collection,
            'options' => [['value' => 7, 'label' => 'Admin']], 'show' => new Role,
        };

        $this->repository->shouldReceive($repositoryMethod)->once()->with($input)->andReturn($result);
        $this->assertSame($result, $this->service->$method($input));
    }

    public function test_show_returns_null_when_missing(): void
    {
        $this->repository->shouldReceive('findWithPermissions')->once()->with(7)->andReturnNull();
        $this->assertNull($this->service->show(7));
    }

    public static function writes(): array
    {
        return [['store'], ['update']];
    }

    #[DataProvider('writes')]
    public function test_writes_filter_fields_and_enforce_base_permissions(string $method): void
    {
        $this->transaction();

        $role = Mockery::mock(Role::class)->makePartial();
        $role->setRawAttributes(['can_modify' => true]);

        $data = ['name' => 'Admin', 'slug' => 'admin', 'can_modify' => false, 'ignored' => 'value', 'permissions' => [
            ['id' => '1', 'is_active' => false, 'show_locked_routes' => true],
            ['id' => '3', 'is_active' => false, 'show_locked_routes' => true],
            ['id' => 4, 'is_active' => true, 'show_locked_routes' => true],
            ['id' => 5, 'is_active' => false, 'show_locked_routes' => false],
        ]];

        if ($method === 'store') {
            $this->repository->shouldReceive('create')->once()->with(['name' => 'Admin', 'slug' => 'admin', 'can_modify' => true])->andReturn($role);
        } else {
            $this->repository->shouldReceive('find')->once()->with(7)->andReturn($role);
            $role->shouldReceive('update')->once()->with(['name' => 'Admin', 'slug' => 'admin'])->andReturnTrue();
            $role->shouldReceive('refresh')->once()->andReturnSelf();
        }

        $this->permissions->shouldReceive('getBasePermissionIds')->once()->andReturn([1, 2]);

        $relation = Mockery::mock(BelongsToMany::class);
        $role->shouldReceive('permissions')->once()->andReturn($relation);
        $relation->shouldReceive('sync')->once()->with([
            1 => ['is_active' => true, 'show_locked_routes' => false],
            3 => ['is_active' => false, 'show_locked_routes' => true],
            4 => ['is_active' => true, 'show_locked_routes' => false],
            5 => ['is_active' => false, 'show_locked_routes' => false],
            2 => ['is_active' => true, 'show_locked_routes' => false],
        ]);

        $role->shouldReceive('load')->once()->with('permissions')->andReturnSelf();
        $result = $method === 'store' ? $this->service->store($data) : $this->service->update(7, $data);
        $this->assertSame($role, $result);
    }

    public function test_store_adds_base_permissions_when_permissions_are_omitted(): void
    {
        $this->transaction();

        $role = Mockery::mock(Role::class)->makePartial();

        $this->repository->shouldReceive('create')->once()->with(['name' => 'Admin', 'can_modify' => true])->andReturn($role);
        $this->permissions->shouldReceive('getBasePermissionIds')->once()->andReturn([1]);

        $relation = Mockery::mock(BelongsToMany::class);
        $role->shouldReceive('permissions')->once()->andReturn($relation);
        $relation->shouldReceive('sync')->once()->with([1 => ['is_active' => true, 'show_locked_routes' => false]]);
        $role->shouldReceive('load')->once()->with('permissions')->andReturnSelf();

        $this->assertSame($role, $this->service->store(['name' => 'Admin']));
    }

    public function test_store_throws_when_repository_cannot_create_role(): void
    {
        $this->transaction();
        $this->repository->shouldReceive('create')->once()->andReturnNull();
        $this->expectExceptionMessage('Não foi possível cadastrar o perfil.');
        $this->expectException(RuntimeException::class);
        $this->service->store(['name' => 'Admin']);
    }

    public static function unavailable(): array
    {
        return [[true], [false]];
    }

    #[DataProvider('unavailable')]
    public function test_update_does_not_modify_missing_or_protected_roles(bool $missing): void
    {
        $this->transaction();

        $role = Mockery::mock(Role::class)->makePartial();
        $role->setRawAttributes(['can_modify' => false]);
        $role->shouldNotReceive('update');
        $role->shouldNotReceive('permissions');

        $this->repository->shouldReceive('find')->once()->with(7)->andReturn($missing ? null : $role);
        $this->assertSame($missing ? null : false, $this->service->update(7, ['name' => 'Changed']));
    }

    public static function blockedDeletions(): array
    {
        return [['missing', 'Perfil não encontrado.'], ['protected', 'Este perfil é protegido e não pode ser excluído.'],
            ['linked', 'Este perfil está vinculado a um domínio e não pode ser excluído.']];
    }

    #[DataProvider('blockedDeletions')]
    public function test_delete_rejects_unavailable_roles(string $state, string $message): void
    {
        $role = Mockery::mock(Role::class)->makePartial();
        $role->setRawAttributes(['can_modify' => $state !== 'protected']);
        $role->shouldNotReceive('delete');
        $role->shouldNotReceive('permissions');

        if ($state === 'linked') {
            $relation = Mockery::mock(HasMany::class);
            $role->shouldReceive('tenants')->once()->andReturn($relation);
            $relation->shouldReceive('exists')->once()->andReturnTrue();
        }

        $this->repository->shouldReceive('find')->once()->with(7)->andReturn($state === 'missing' ? null : $role);
        $this->assertSame(['success' => false, 'message' => $message], $this->service->delete(7));
    }

    #[DataProvider('unavailable')]
    public function test_delete_detaches_permissions_and_preserves_delete_result(bool $deleted): void
    {
        $this->transaction();

        $role = Mockery::mock(Role::class)->makePartial();
        $role->setRawAttributes(['can_modify' => true]);

        $this->repository->shouldReceive('find')->once()->with(7)->andReturn($role);

        $tenants = Mockery::mock(HasMany::class);
        $role->shouldReceive('tenants')->once()->andReturn($tenants);
        $tenants->shouldReceive('exists')->once()->andReturnFalse();
        $permissions = Mockery::mock(BelongsToMany::class);
        $role->shouldReceive('permissions')->once()->andReturn($permissions);
        $permissions->shouldReceive('detach')->once()->globally()->ordered();
        $role->shouldReceive('delete')->once()->globally()->ordered()->andReturn($deleted);

        $this->assertSame(['success' => $deleted, 'message' => null], $this->service->delete(7));
    }
}
