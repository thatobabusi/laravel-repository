<?php

namespace Thatobabusi\LaravelRepositoryPattern\Eloquent;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Thatobabusi\LaravelRepositoryPattern\Contracts\CriteriaInterface;
use Thatobabusi\LaravelRepositoryPattern\Contracts\RepositoryCriteriaInterface;
use Thatobabusi\LaravelRepositoryPattern\Contracts\RepositoryInterface;
use Thatobabusi\LaravelRepositoryPattern\Exceptions\RepositoryException;

abstract class BaseRepository implements RepositoryInterface, RepositoryCriteriaInterface
{
    protected Container $app;

    protected Model|\Illuminate\Database\Eloquent\Builder $model;

    protected ?Closure $scopeQuery = null;

    protected Collection $criteria;

    protected bool $skipCriteria = false;

    protected array $fieldSearchable = [];

    public function __construct(Container $app)
    {
        $this->app = $app;
        $this->criteria = new Collection();
        $this->makeModel();
        $this->boot();
    }

    abstract public function model(): string;

    public function boot(): void {}

    // ── Model reset ────────────────────────────────────────────────────────

    public function makeModel(): Model|\Illuminate\Database\Eloquent\Builder
    {
        $model = $this->app->make($this->model());

        if (! $model instanceof Model) {
            throw new RepositoryException(
                "Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model"
            );
        }

        return $this->model = $model;
    }

    protected function resetModel(): void
    {
        $this->makeModel();
    }

    // ── Scope & criteria ───────────────────────────────────────────────────

    public function scopeQuery(Closure $scope): static
    {
        $this->scopeQuery = $scope;

        return $this;
    }

    public function resetScope(): static
    {
        $this->scopeQuery = null;

        return $this;
    }

    public function pushCriteria(mixed $criteria): static
    {
        if (is_string($criteria)) {
            $criteria = new $criteria();
        }

        if (! $criteria instanceof CriteriaInterface) {
            throw new RepositoryException(
                "Class ".get_class($criteria)." must be an instance of ".CriteriaInterface::class
            );
        }

        $this->criteria->push($criteria);

        return $this;
    }

    public function popCriteria(mixed $criteria): static
    {
        $this->criteria = $this->criteria->reject(function ($item) use ($criteria) {
            if (is_string($criteria)) {
                return get_class($item) === $criteria;
            }

            return $item === $criteria || get_class($item) === get_class($criteria);
        });

        return $this;
    }

    public function getCriteria(): Collection
    {
        return $this->criteria;
    }

    public function getByCriteria(CriteriaInterface $criteria): mixed
    {
        $this->model = $criteria->apply($this->model, $this);
        $results = $this->model->get();
        $this->resetModel();

        return $results;
    }

    public function skipCriteria(bool $status = true): static
    {
        $this->skipCriteria = $status;

        return $this;
    }

    public function resetCriteria(): static
    {
        $this->criteria = new Collection();

        return $this;
    }

    protected function applyCriteria(): static
    {
        if ($this->skipCriteria) {
            return $this;
        }

        foreach ($this->getCriteria() as $criteria) {
            $this->model = $criteria->apply($this->model, $this);
        }

        return $this;
    }

    protected function applyScope(): static
    {
        if ($this->scopeQuery !== null) {
            $callback = $this->scopeQuery;
            $this->model = $callback($this->model);
        }

        return $this;
    }

    // ── Query builder helpers ──────────────────────────────────────────────

    public function orderBy(string $column, string $direction = 'asc'): static
    {
        $this->model = $this->model->orderBy($column, $direction);

        return $this;
    }

    public function with(array|string $relations): static
    {
        $this->model = $this->model->with($relations);

        return $this;
    }

    public function withCount(array|string $relations): static
    {
        $this->model = $this->model->withCount($relations);

        return $this;
    }

    public function has(string $relation): static
    {
        $this->model = $this->model->has($relation);

        return $this;
    }

    public function whereHas(string $relation, Closure $closure): static
    {
        $this->model = $this->model->whereHas($relation, $closure);

        return $this;
    }

    public function hidden(array $fields): static
    {
        $this->model->setHidden($fields);

        return $this;
    }

    public function visible(array $fields): static
    {
        $this->model->setVisible($fields);

        return $this;
    }

    // ── Read operations ────────────────────────────────────────────────────

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function all(array $columns = ['*']): mixed
    {
        $this->applyCriteria()->applyScope();
        $results = $this->model->get($columns);
        $this->resetModel();
        $this->resetScope();

        return $results;
    }

    public function lists(string $value, ?string $key = null): mixed
    {
        return $this->pluck($value, $key);
    }

    public function pluck(string $value, ?string $key = null): mixed
    {
        $this->applyCriteria();
        $results = $this->model->pluck($value, $key);
        $this->resetModel();

        return $results;
    }

    public function sync(int|string $id, string $relation, mixed $attributes, bool $detaching = true): mixed
    {
        $model = $this->find($id);

        return $model->{$relation}()->sync($attributes, $detaching);
    }

    public function syncWithoutDetaching(int|string $id, string $relation, mixed $attributes): mixed
    {
        return $this->sync($id, $relation, $attributes, false);
    }

    public function paginate(int $limit = 0, array $columns = ['*'], string $method = 'paginate'): mixed
    {
        $limit = $limit > 0 ? $limit : (int) config('repository.pagination.limit', 15);

        $this->applyCriteria()->applyScope();
        $results = $this->model->{$method}($limit, $columns);
        $this->resetModel();
        $this->resetScope();

        return $results;
    }

    public function simplePaginate(int $limit = 15, array $columns = ['*']): mixed
    {
        return $this->paginate($limit, $columns, 'simplePaginate');
    }

    public function find(int|string $id, array $columns = ['*']): mixed
    {
        $this->applyCriteria()->applyScope();
        $model = $this->model->findOrFail($id, $columns);
        $this->resetModel();

        return $model;
    }

    public function findByField(string $field, mixed $value, array $columns = ['*']): mixed
    {
        $this->applyCriteria()->applyScope();
        $results = $this->model->where($field, '=', $value)->get($columns);
        $this->resetModel();
        $this->resetScope();

        return $results;
    }

    public function findWhere(array $where, array $columns = ['*']): mixed
    {
        $this->applyCriteria()->applyScope();

        foreach ($where as $field => $value) {
            if (is_array($value)) {
                [$field, $condition, $val] = $value;
                $this->model = $this->model->where($field, $condition, $val);
            } else {
                $this->model = $this->model->where($field, '=', $value);
            }
        }

        $results = $this->model->get($columns);
        $this->resetModel();
        $this->resetScope();

        return $results;
    }

    public function findWhereIn(string $field, array $values, array $columns = ['*']): mixed
    {
        $this->applyCriteria()->applyScope();
        $results = $this->model->whereIn($field, $values)->get($columns);
        $this->resetModel();
        $this->resetScope();

        return $results;
    }

    public function findWhereNotIn(string $field, array $values, array $columns = ['*']): mixed
    {
        $this->applyCriteria()->applyScope();
        $results = $this->model->whereNotIn($field, $values)->get($columns);
        $this->resetModel();
        $this->resetScope();

        return $results;
    }

    public function findWhereBetween(string $field, array $values, array $columns = ['*']): mixed
    {
        $this->applyCriteria()->applyScope();
        $results = $this->model->whereBetween($field, $values)->get($columns);
        $this->resetModel();
        $this->resetScope();

        return $results;
    }

    public function firstOrNew(array $attributes = []): mixed
    {
        $this->applyCriteria()->applyScope();
        $model = $this->model->firstOrNew($attributes);
        $this->resetModel();

        return $model;
    }

    public function firstOrCreate(array $attributes = []): mixed
    {
        $this->applyCriteria()->applyScope();
        $model = $this->model->firstOrCreate($attributes);
        $this->resetModel();

        return $model;
    }

    // ── Write operations ───────────────────────────────────────────────────

    public function create(array $attributes): mixed
    {
        $model = $this->model->newInstance($attributes);
        $model->save();
        $this->resetModel();

        return $model;
    }

    public function update(array $attributes, int|string $id): mixed
    {
        $this->applyScope();
        $model = $this->model->findOrFail($id);
        $model->fill($attributes)->save();
        $this->resetModel();

        return $model;
    }

    public function updateOrCreate(array $attributes, array $values = []): mixed
    {
        $this->applyScope();
        $model = $this->model->updateOrCreate($attributes, $values);
        $this->resetModel();

        return $model;
    }

    public function delete(int|string $id): mixed
    {
        $this->applyScope();
        $model = $this->find($id);
        $this->resetModel();

        return $model->delete();
    }

    public function deleteWhere(array $where): mixed
    {
        $this->applyScope();

        foreach ($where as $field => $value) {
            if (is_array($value)) {
                [$field, $condition, $val] = $value;
                $this->model = $this->model->where($field, $condition, $val);
            } else {
                $this->model = $this->model->where($field, '=', $value);
            }
        }

        $deleted = $this->model->delete();
        $this->resetModel();

        return $deleted;
    }
}
