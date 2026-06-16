<?php

namespace Laravel\Repository\Contracts;

interface RepositoryInterface
{
    public function all(array $columns = ['*']): mixed;

    public function lists(string $value, ?string $key = null): mixed;

    public function pluck(string $value, ?string $key = null): mixed;

    public function sync(int|string $id, string $relation, mixed $attributes, bool $detaching = true): mixed;

    public function syncWithoutDetaching(int|string $id, string $relation, mixed $attributes): mixed;

    public function paginate(int $limit = 15, array $columns = ['*'], string $method = 'paginate'): mixed;

    public function simplePaginate(int $limit = 15, array $columns = ['*']): mixed;

    public function find(int|string $id, array $columns = ['*']): mixed;

    public function findByField(string $field, mixed $value, array $columns = ['*']): mixed;

    public function findWhere(array $where, array $columns = ['*']): mixed;

    public function findWhereIn(string $field, array $values, array $columns = ['*']): mixed;

    public function findWhereNotIn(string $field, array $values, array $columns = ['*']): mixed;

    public function findWhereBetween(string $field, array $values, array $columns = ['*']): mixed;

    public function create(array $attributes): mixed;

    public function update(array $attributes, int|string $id): mixed;

    public function updateOrCreate(array $attributes, array $values = []): mixed;

    public function delete(int|string $id): mixed;

    public function deleteWhere(array $where): mixed;

    public function orderBy(string $column, string $direction = 'asc'): static;

    public function with(array|string $relations): static;

    public function withCount(array|string $relations): static;

    public function has(string $relation): static;

    public function whereHas(string $relation, \Closure $closure): static;

    public function hidden(array $fields): static;

    public function visible(array $fields): static;

    public function scopeQuery(\Closure $scope): static;

    public function resetScope(): static;

    public function getFieldsSearchable(): array;

    public function firstOrNew(array $attributes = []): mixed;

    public function firstOrCreate(array $attributes = []): mixed;
}
