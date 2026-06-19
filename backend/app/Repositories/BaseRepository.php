<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

abstract class BaseRepository extends Repository
{
    public function last(string $column = 'id', array $columns = ['*']): Model|null
    {
        return $this->query()
            ->orderByDesc($column)
            ->first($columns);
    }

    public function nextId(string $column = 'id'): int
    {
        return ((int)$this->query()->max($column)) + 1;
    }

    public function getPrimaryKey(): string
    {
        return $this->model->getKeyName();
    }

    public function search(Request $request): Builder
    {
        $query = $this->query();

        //TODO implementar fluxo de filtro generico.

        return $query;
    }

    public function findBy(string $column, mixed $value, array $columns = ['*']): Model|null
    {
        return $this->query()
            ->where($column, $value)
            ->first($columns);
    }

    public function existsBy(string $column, mixed $value): bool
    {
        return $this->query()
            ->where($column, $value)
            ->exists();
    }
}
