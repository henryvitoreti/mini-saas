<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

abstract class BaseRepository extends Repository
{
    protected array $searchFields = [];

    protected array $filterFields = [
        'id',
    ];

    protected array $dateFilterFields = [];

    protected string $defaultDateFilterField = 'created_at';

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
        $query = $this->model->newQuery();

        $this->applySearchFilter($query, $request);
        $this->applyFieldFilters($query, $request);
        $this->applyDateFilters($query, $request);

        return $query;
    }

    public function paginateSearch(Request $request, array $columns = ['*']): LengthAwarePaginator
    {
        $query = $this->search($request);

        $this->applyRequestOrdering($query, $request);

        $limit = max((int) $request->input('limit', 15), 1);
        $page = max((int) $request->input('page', 1), 1);

        return $query->paginate(perPage: $limit, columns: $columns, page: $page);
    }

    public function getSearch(Request $request, array $columns = ['*']): Collection
    {
        $query = $this->search($request);

        $this->applyRequestOrdering($query, $request);

        return $query->get($columns);
    }

    public function getSearchByPage(Request $request, array $columns = ['*']): Collection
    {
        $query = $this->search($request);

        $this->applyRequestOrdering($query, $request);
        $this->applyRequestPage($query, $request);

        return $query->get($columns);
    }

    protected function applyRequestOrdering(Builder $query, Request $request): Builder
    {
        $sortBy = (string) $request->input('sort_by', $this->getPrimaryKey());
        $order = strtoupper((string) $request->input('order', 'DESC'));

        if (!in_array($order, ['ASC', 'DESC'], true)) {
            $order = 'DESC';
        }

        return $query->orderBy($sortBy, $order);
    }

    protected function applyRequestPage(Builder $query, Request $request): Builder
    {
        $limit = max((int) $request->input('limit', 15), 1);
        $page = max((int) $request->input('page', 1), 1);
        $offset = ($page - 1) * $limit;

        return $query->skip($offset)->limit($limit);
    }

    protected function applySearchFilter(Builder $query, Request $request): Builder
    {
        if (!$this->requestHasFilledField($request, 'search') || $this->searchFields === []) {
            return $query;
        }

        $search = $this->requestFieldValue($request, 'search');

        return $query->where(function (Builder $searchQuery) use ($search): void {
            foreach ($this->searchFields as $field => $operator) {
                [$field, $operator] = $this->normalizeConfiguredField($field, $operator);

                $this->applyFieldCondition($searchQuery, $field, $search, $operator, 'or');
            }
        });
    }

    protected function applyFieldFilters(Builder $query, Request $request): Builder
    {
        foreach ($this->filterFields as $field => $operator) {
            [$field, $operator] = $this->normalizeConfiguredField($field, $operator);

            if (!$this->requestHasFilledField($request, $field)) {
                continue;
            }

            $this->applyFieldCondition($query, $field, $this->requestFieldValue($request, $field), $operator);
        }

        return $query;
    }

    protected function applyDateFilters(Builder $query, Request $request): Builder
    {
        if ($this->dateFilterFields === []) {
            return $query;
        }

        $dateField = $this->requestHasFilledField($request, 'date_field')
            ? (string) $this->requestFieldValue($request, 'date_field')
            : $this->defaultDateFilterField;

        if (!in_array($dateField, $this->dateFilterFields, true)) {
            return $query;
        }

        if ($this->requestHasFilledField($request, 'start_date')) {
            $this->applyFieldCondition($query, $dateField, $this->requestFieldValue($request, 'start_date'), '>=');
        }

        if ($this->requestHasFilledField($request, 'end_date')) {
            $this->applyFieldCondition($query, $dateField, $this->requestFieldValue($request, 'end_date'), '<=');
        }

        return $query;
    }

    protected function applyFieldCondition(
        Builder $query,
        string $field,
        mixed $value,
        string $operator = '=',
        string $boolean = 'and'
    ): Builder {
        $value = $this->prepareFilterValue($operator, $value);

        if (!Str::contains($field, '.')) {
            return $boolean === 'or'
                ? $query->orWhere($field, $operator, $value)
                : $query->where($field, $operator, $value);
        }

        [$relation, $relationField] = explode('.', $field, 2);

        $callback = function (Builder $relationQuery) use ($relationField, $operator, $value): void {
            $relationQuery->where($relationField, $operator, $value);
        };

        return $boolean === 'or'
            ? $query->orWhereHas($relation, $callback)
            : $query->whereHas($relation, $callback);
    }

    protected function normalizeConfiguredField(int|string $field, string $operator): array
    {
        if (is_int($field)) {
            return [$operator, '='];
        }

        return [$field, $operator];
    }

    protected function prepareFilterValue(string $operator, mixed $value): mixed
    {
        if (in_array($operator, ['like', 'ilike'], true)) {
            return "%{$value}%";
        }

        return $value;
    }

    protected function requestHasFilledField(Request $request, string $field): bool
    {
        $value = $this->requestFieldValue($request, $field);

        return $value !== null && $value !== '' && $value !== [];
    }

    protected function requestFieldValue(Request $request, string $field): mixed
    {
        $query = $request->query->all();

        if (array_key_exists($field, $query)) {
            return $query[$field];
        }

        return $request->input($field);
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
