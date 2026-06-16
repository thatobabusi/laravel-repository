# Contracts

Three interfaces define the full public API. Type-hinting against these instead of concrete classes keeps your code decoupled from any specific repository implementation.

---

## `RepositoryInterface`

**Namespace:** `Laravel\Repository\Contracts\RepositoryInterface`

The primary contract. Covers all CRUD, query, and scope operations.

```php
interface RepositoryInterface
{
    // ── Read ────────────────────────────────────────────────────────────────
    public function all(array $columns = ['*']): mixed;
    public function lists(string $value, ?string $key = null): mixed;
    public function pluck(string $value, ?string $key = null): mixed;
    public function paginate(int $limit = 15, array $columns = ['*'], string $method = 'paginate'): mixed;
    public function simplePaginate(int $limit = 15, array $columns = ['*']): mixed;
    public function find(int|string $id, array $columns = ['*']): mixed;
    public function findByField(string $field, mixed $value, array $columns = ['*']): mixed;
    public function findWhere(array $where, array $columns = ['*']): mixed;
    public function findWhereIn(string $field, array $values, array $columns = ['*']): mixed;
    public function findWhereNotIn(string $field, array $values, array $columns = ['*']): mixed;
    public function findWhereBetween(string $field, array $values, array $columns = ['*']): mixed;
    public function firstOrNew(array $attributes = []): mixed;
    public function firstOrCreate(array $attributes = []): mixed;

    // ── Write ───────────────────────────────────────────────────────────────
    public function create(array $attributes): mixed;
    public function update(array $attributes, int|string $id): mixed;
    public function updateOrCreate(array $attributes, array $values = []): mixed;
    public function delete(int|string $id): mixed;
    public function deleteWhere(array $where): mixed;
    public function sync(int|string $id, string $relation, mixed $attributes, bool $detaching = true): mixed;
    public function syncWithoutDetaching(int|string $id, string $relation, mixed $attributes): mixed;

    // ── Chainable modifiers ─────────────────────────────────────────────────
    public function orderBy(string $column, string $direction = 'asc'): static;
    public function with(array|string $relations): static;
    public function withCount(array|string $relations): static;
    public function has(string $relation): static;
    public function whereHas(string $relation, \Closure $closure): static;
    public function hidden(array $fields): static;
    public function visible(array $fields): static;
    public function scopeQuery(\Closure $scope): static;
    public function resetScope(): static;

    // ── Meta ────────────────────────────────────────────────────────────────
    public function getFieldsSearchable(): array;
}
```

---

## `CriteriaInterface`

**Namespace:** `Laravel\Repository\Contracts\CriteriaInterface`

Implemented by every criteria class. The single `apply()` method receives the current Eloquent builder and must return it (modified or not).

```php
interface CriteriaInterface
{
    public function apply(mixed $model, RepositoryInterface $repository): mixed;
}
```

`$model` is either an Eloquent `Model` or an Eloquent `Builder` instance - it can be treated as a builder in all practical cases. `$repository` gives access to `getFieldsSearchable()` and any public repository method.

---

## `RepositoryCriteriaInterface`

**Namespace:** `Laravel\Repository\Contracts\RepositoryCriteriaInterface`

Manages the criteria stack on a repository.

```php
interface RepositoryCriteriaInterface
{
    public function pushCriteria(mixed $criteria): static;
    public function popCriteria(mixed $criteria): static;
    public function getCriteria(): Collection;
    public function getByCriteria(CriteriaInterface $criteria): mixed;
    public function skipCriteria(bool $status = true): static;
    public function resetCriteria(): static;
}
```

`BaseRepository` implements both `RepositoryInterface` and `RepositoryCriteriaInterface`.

---

## Binding to the interface

```php
// AppServiceProvider.php
use App\Repositories\UserRepository;
use Laravel\Repository\Contracts\RepositoryInterface;

$this->app->when(UserController::class)
    ->needs(RepositoryInterface::class)
    ->give(UserRepository::class);
```

Or using a dedicated `RepositoryServiceProvider`:

```php
$this->app->bind(UserRepository::class, UserRepository::class);
$this->app->bind(PostRepository::class, PostRepository::class);
```

---

## `RepositoryException`

**Namespace:** `Laravel\Repository\Exceptions\RepositoryException`

Thrown by `BaseRepository` when:

- `model()` returns a class that is not an Eloquent `Model`
- `pushCriteria()` receives an object that does not implement `CriteriaInterface`

```php
use Laravel\Repository\Exceptions\RepositoryException;

try {
    $this->users->pushCriteria(new \stdClass());
} catch (RepositoryException $e) {
    // handle
}
```
