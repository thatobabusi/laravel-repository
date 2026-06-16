# Method Reference

All methods are defined in `RepositoryInterface` and implemented in `BaseRepository`. Methods marked **chainable** return `static` and can be chained before a terminal call. All other methods are terminal - they execute the query and reset the model.

---

## Read operations

### `all(array $columns = ['*']): mixed`

Retrieve all records as an Eloquent `Collection`.

```php
$users = $this->users->all();
$users = $this->users->all(['id', 'name', 'email']);
```

---

### `paginate(int $limit = 0, array $columns = ['*'], string $method = 'paginate'): mixed`

Return a `LengthAwarePaginator`. When `$limit` is `0` or omitted, uses `config('repository.pagination.limit', 15)`.

```php
$users = $this->users->paginate();       // default limit
$users = $this->users->paginate(25);     // 25 per page
$users = $this->users->paginate(25, ['id', 'name']);
```

---

### `simplePaginate(int $limit = 15, array $columns = ['*']): mixed`

Return a `Paginator` (no total count - better performance on large tables).

```php
$users = $this->users->simplePaginate(25);
```

---

### `find(int|string $id, array $columns = ['*']): mixed`

Find a record by primary key. Throws `ModelNotFoundException` if not found.

```php
$user = $this->users->find(1);
$user = $this->users->find($id, ['id', 'name']);
```

---

### `findByField(string $field, mixed $value, array $columns = ['*']): mixed`

Find all records where `$field = $value`. Returns a `Collection`.

```php
$users = $this->users->findByField('role', 'admin');
$users = $this->users->findByField('email', $email, ['id', 'name', 'email']);
```

---

### `findWhere(array $where, array $columns = ['*']): mixed`

Find records matching multiple conditions. Returns a `Collection`.

```php
// Simple equality
$users = $this->users->findWhere(['active' => true, 'role' => 'admin']);

// With operator
$users = $this->users->findWhere([
    ['age', '>=', 18],
    ['verified', '=', true],
]);
```

---

### `findWhereIn(string $field, array $values, array $columns = ['*']): mixed`

`WHERE field IN (...)`. Returns a `Collection`.

```php
$users = $this->users->findWhereIn('role', ['admin', 'editor']);
```

---

### `findWhereNotIn(string $field, array $values, array $columns = ['*']): mixed`

`WHERE field NOT IN (...)`. Returns a `Collection`.

```php
$users = $this->users->findWhereNotIn('status', ['banned', 'suspended']);
```

---

### `findWhereBetween(string $field, array $values, array $columns = ['*']): mixed`

`WHERE field BETWEEN ? AND ?`. Returns a `Collection`.

```php
$users = $this->users->findWhereBetween('age', [18, 65]);
```

---

### `firstOrNew(array $attributes = []): mixed`

Return the first matching model or a new unsaved instance.

```php
$user = $this->users->firstOrNew(['email' => 'john@example.com']);
```

---

### `firstOrCreate(array $attributes = []): mixed`

Return the first matching model or create and save a new one.

```php
$user = $this->users->firstOrCreate(['email' => 'john@example.com']);
```

---

### `pluck(string $value, ?string $key = null): mixed`

Pluck a column value (optionally keyed by another column).

```php
$emails = $this->users->pluck('email');
$map    = $this->users->pluck('email', 'id');  // [id => email]
```

### `lists(string $value, ?string $key = null): mixed`

Alias for `pluck()`.

---

## Write operations

### `create(array $attributes): mixed`

Create and persist a new model instance.

```php
$user = $this->users->create([
    'name'  => 'Jane Doe',
    'email' => 'jane@example.com',
]);
```

---

### `update(array $attributes, int|string $id): mixed`

Update a record by primary key. Throws `ModelNotFoundException` if not found.

```php
$user = $this->users->update(['name' => 'Jane Smith'], $id);
```

---

### `updateOrCreate(array $attributes, array $values = []): mixed`

Update matching records or create if none exist.

```php
$user = $this->users->updateOrCreate(
    ['email' => 'jane@example.com'],
    ['name'  => 'Jane Doe']
);
```

---

### `delete(int|string $id): mixed`

Delete a record by primary key.

```php
$this->users->delete($id);
```

---

### `deleteWhere(array $where): mixed`

Delete all records matching conditions. Accepts the same format as `findWhere()`.

```php
$this->users->deleteWhere(['active' => false]);
$this->users->deleteWhere([['last_login', '<', now()->subYear()]]);
```

---

### `sync(int|string $id, string $relation, mixed $attributes, bool $detaching = true): mixed`

Sync a many-to-many relation.

```php
$this->users->sync($userId, 'roles', [1, 2, 3]);
```

### `syncWithoutDetaching(int|string $id, string $relation, mixed $attributes): mixed`

Sync without removing existing pivot records.

```php
$this->users->syncWithoutDetaching($userId, 'roles', [4]);
```

---

## Chainable modifiers

These return `static` and must be followed by a terminal read method.

### `orderBy(string $column, string $direction = 'asc'): static`

```php
$this->users->orderBy('name')->all();
$this->users->orderBy('created_at', 'desc')->paginate(25);
```

### `with(array|string $relations): static`

```php
$this->users->with('posts')->all();
$this->users->with(['posts', 'profile'])->paginate(25);
```

### `withCount(array|string $relations): static`

```php
$this->users->withCount('posts')->all();
```

### `has(string $relation): static`

```php
$this->users->has('posts')->all();   // only users who have posts
```

### `whereHas(string $relation, Closure $closure): static`

```php
$this->users->whereHas('posts', fn ($q) => $q->where('published', true))->all();
```

### `hidden(array $fields): static`

Hide attributes from the result set.

```php
$this->users->hidden(['password', 'remember_token'])->all();
```

### `visible(array $fields): static`

Restrict which attributes appear in the result.

```php
$this->users->visible(['id', 'name', 'email'])->all();
```

### `scopeQuery(Closure $scope): static`

Apply an anonymous one-off query closure. See [Scope Query](../usage/scope-query.md).

```php
$this->users->scopeQuery(fn ($q) => $q->where('active', true))->all();
```

### `resetScope(): static`

Remove the applied scope closure without executing a query.

```php
$this->users->resetScope();
```

---

## Criteria methods

See [Criteria](../usage/criteria.md) for full examples.

| Method | Description |
|---|---|
| `pushCriteria(mixed $criteria): static` | Add a criteria to the stack |
| `popCriteria(mixed $criteria): static` | Remove a criteria from the stack |
| `getCriteria(): Collection` | Return the current criteria stack |
| `getByCriteria(CriteriaInterface $criteria): mixed` | Apply one criteria and get all results |
| `skipCriteria(bool $status = true): static` | Bypass all criteria for the next call |
| `resetCriteria(): static` | Clear the entire criteria stack |
| `getFieldsSearchable(): array` | Return the `$fieldSearchable` array |
