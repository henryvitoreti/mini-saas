<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

abstract class Repository
{
    protected Model $model;

    public function __construct()
    {
        $this->makeModel();
    }

    abstract public function model(): string;

    protected function makeModel(): void
    {
        $modelClass = $this->model();

        $model = app($modelClass);

        if (!$model instanceof Model) {
            throw new RuntimeException("A classe {$modelClass} deve ser uma instância de Illuminate\Database\Eloquent\Model.");
        }

        $this->model = $model;
    }

    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    public function newQuery(): Builder
    {
        return $this->query();
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function setModel(string $modelClass): static
    {
        $model = app($modelClass);

        if (!$model instanceof Model) {
            throw new RuntimeException("A classe {$modelClass} deve ser uma instância de Illuminate\Database\Eloquent\Model.");
        }

        $this->model = $model;

        return $this;
    }

    public function find(int|string $id, array $columns = ['*']): Model|null
    {
        return $this->query()->find($id, $columns);
    }

    public function findOrFail(int|string $id, array $columns = ['*']): Model
    {
        return $this->query()->findOrFail($id, $columns);
    }

    public function first(array $columns = ['*']): Model|null
    {
        return $this->query()->first($columns);
    }

    public function create(array $data): Model
    {
        return $this->query()->create($data);
    }

    public function firstOrCreate(array $attributes, array $values = []): Model
    {
        return $this->query()->firstOrCreate($attributes, $values);
    }

    public function update(int|string $id, array $data): Model|null
    {
        $entity = $this->find($id);

        if ($entity === null) {
            return null;
        }

        $entity->update($data);

        return $entity->refresh();
    }

    public function updateOrCreate(array $attributes, array $values = []): Model
    {
        return $this->query()->updateOrCreate($attributes, $values);
    }

    public function delete(int|string $id): bool
    {
        $entity = $this->find($id);

        if ($entity === null) {
            return false;
        }

        return (bool)$entity->delete();
    }

    public function forceDelete(int|string $id): bool
    {
        if (!in_array(SoftDeletes::class, class_uses_recursive($this->model), true)) {
            throw new RuntimeException('O model informado não suporta exclusão permanente. Verifique se ele usa SoftDeletes.');
        }

        $entity = $this->query()->withTrashed()->find($id);

        if ($entity === null) {
            return false;
        }

        return (bool)$entity->forceDelete();
    }

    public function __call(string $method, array $parameters): mixed
    {
        return $this->query()->{$method}(...$parameters);
    }
}
