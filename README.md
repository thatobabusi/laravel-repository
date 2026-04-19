# Laravel Repository Pattern

A Laravel 13-compatible repository pattern implementation with Eloquent, criteria-based query filtering, and Artisan generators.

[![Tests](https://github.com/thatobabusi/laravel-repository-pattern/actions/workflows/tests.yml/badge.svg)](https://github.com/thatobabusi/laravel-repository-pattern/actions)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)

---

## Requirements

| Dependency | Version |
|---|---|
| PHP | ^8.2 |
| Laravel | ^11 \| ^12 \| ^13 |

---

## Installation

```bash
composer require thatobabusi/laravel-repository-pattern
```

The service provider is auto-discovered. To publish the config file:

```bash
php artisan vendor:publish --tag=repository-config
```

---

## Quickstart

### 1. Create a repository

```bash
php artisan make:repository User
```

This generates `app/Repositories/UserRepository.php`:

```php
namespace App\Repositories;

use Thatobabusi\LaravelRepositoryPattern\Eloquent\BaseRepository;
use App\Models\User;

class UserRepository extends BaseRepository
{
    protected array $fieldSearchable = [
        'name'  => 'like',
        'email' => '=',
    ];

    public function model(): string
    {
        return User::class;
    }
}
```

### 2. Bind in a service provider

```php
use App\Repositories\UserRepository;
use Thatobabusi\LaravelRepositoryPattern\Contracts\RepositoryInterface;

$this->app->bind(RepositoryInterface::class, UserRepository::class);
```

### 3. Use in a controller

```php
use App\Repositories\UserRepository;

class UserController extends Controller
{
    public function __construct(private UserRepository $users) {}

    public function index()
    {
        return $this->users->paginate(25);
    }

    public function store(Request $request)
    {
        return $this->users->create($request->validated());
    }
}
```

---

## API Reference

### Read operations

| Method | Description |
|---|---|
| `all(columns)` | Retrieve all records |
| `paginate(limit, columns)` | Paginate results |
| `simplePaginate(limit, columns)` | Simple pagination (no total count) |
| `find(id, columns)` | Find by primary key (throws `ModelNotFoundException`) |
| `findByField(field, value, columns)` | Find all records where field = value |
| `findWhere(where, columns)` | Find with multiple conditions |
| `findWhereIn(field, values, columns)` | `WHERE field IN (...)` |
| `findWhereNotIn(field, values, columns)` | `WHERE field NOT IN (...)` |
| `findWhereBetween(field, values, columns)` | `WHERE field BETWEEN ? AND ?` |
| `pluck(value, key)` | Pluck a column |
| `lists(value, key)` | Alias for `pluck()` |
| `firstOrNew(attributes)` | First matching model or new unsaved instance |
| `firstOrCreate(attributes)` | First matching model or create and save |

### Write operations

| Method | Description |
|---|---|
| `create(attributes)` | Create and persist a new record |
| `update(attributes, id)` | Update record by primary key |
| `updateOrCreate(attributes, values)` | Update or create |
| `delete(id)` | Delete by primary key |
| `deleteWhere(where)` | Delete all records matching conditions |
| `sync(id, relation, attributes)` | Sync a many-to-many relation |
| `syncWithoutDetaching(id, relation, attributes)` | Sync without detaching |

### Query modifiers (chainable)

| Method | Description |
|---|---|
| `orderBy(column, direction)` | Apply ORDER BY |
| `with(relations)` | Eager-load relations |
| `withCount(relations)` | Eager-load relation counts |
| `has(relation)` | Filter by relation existence |
| `whereHas(relation, closure)` | Filter by relation with constraint |
| `hidden(fields)` | Hide attributes from results |
| `visible(fields)` | Restrict visible attributes |
| `scopeQuery(closure)` | Apply an arbitrary query scope |
| `resetScope()` | Remove the applied scope |

---

## Criteria

Criteria are reusable, composable query modifiers.

### Generate a criteria class

```bash
php artisan make:criteria ActiveUsers
```

```php
namespace App\Criteria;

use Thatobabusi\LaravelRepositoryPattern\Contracts\CriteriaInterface;
use Thatobabusi\LaravelRepositoryPattern\Contracts\RepositoryInterface;

class ActiveUsersCriteria implements CriteriaInterface
{
    public function apply(mixed $model, RepositoryInterface $repository): mixed
    {
        return $model->where('active', true);
    }
}
```

### Apply criteria

```php
$this->users
    ->pushCriteria(new ActiveUsersCriteria())
    ->pushCriteria(new RecentlyCreatedCriteria())
    ->paginate(25);
```

### Criteria management

```php
$repo->skipCriteria(true);       // bypass all criteria
$repo->popCriteria($criteria);   // remove a specific criteria
$repo->getCriteria();            // get the criteria collection
$repo->resetCriteria();          // clear all criteria
$repo->getByCriteria($criteria); // apply a single criteria and get results
```

---

## RequestCriteria — HTTP-Driven Filtering

`RequestCriteria` translates incoming HTTP query parameters directly into Eloquent query constraints. Inject it once and your endpoint gains sorting, searching, filtering, and eager loading — all driven by URL parameters.

```php
use Thatobabusi\LaravelRepositoryPattern\Criteria\RequestCriteria;

class UserController extends Controller
{
    public function index(Request $request, UserRepository $users)
    {
        $users->pushCriteria(new RequestCriteria($request));

        return $users->paginate(25);
    }
}
```

### Supported parameters

| Parameter | Example | Effect |
|---|---|---|
| `search` | `search=john` | `WHERE name LIKE '%john%'` (uses `fieldSearchable`) |
| `search` | `search=email:john@example.com` | Field-specific search |
| `searchFields` | `searchFields=name:like;email:=` | Override conditions per field |
| `searchJoin` | `searchJoin=and` | `AND` instead of `OR` between search fields |
| `filter` | `filter=id;name;email` | `SELECT id, name, email` |
| `orderBy` | `orderBy=name` | `ORDER BY name ASC` |
| `sortedBy` | `sortedBy=desc` | `ORDER BY name DESC` |
| `with` | `with=posts;profile` | Eager-load `posts` and `profile` |
| `withCount` | `withCount=posts` | Load `posts_count` |

### Configuring searchable fields

In your repository, declare which fields are searchable and with what condition:

```php
protected array $fieldSearchable = [
    'name'  => 'like',
    'email' => '=',
    'age'   => 'between',
    'tags'  => 'in',
];
```

### Customising parameter names

Override the query-string parameter names via `.env` or `config/repository.php` to avoid conflicts:

```env
REPOSITORY_CRITERIA_SEARCH=q
REPOSITORY_CRITERIA_ORDER_BY=sort
REPOSITORY_CRITERIA_SORTED_BY=direction
```

---

## Configuration

```php
// config/repository.php

return [
    'pagination' => [
        'limit' => env('REPOSITORY_PAGINATION_LIMIT', 15),
    ],

    'criteria' => [
        'params' => [
            'search'       => env('REPOSITORY_CRITERIA_SEARCH', 'search'),
            'searchFields' => env('REPOSITORY_CRITERIA_SEARCH_FIELDS', 'searchFields'),
            'filter'       => env('REPOSITORY_CRITERIA_FILTER', 'filter'),
            'orderBy'      => env('REPOSITORY_CRITERIA_ORDER_BY', 'orderBy'),
            'sortedBy'     => env('REPOSITORY_CRITERIA_SORTED_BY', 'sortedBy'),
            'with'         => env('REPOSITORY_CRITERIA_WITH', 'with'),
            'withCount'    => env('REPOSITORY_CRITERIA_WITH_COUNT', 'withCount'),
            'searchJoin'   => env('REPOSITORY_CRITERIA_SEARCH_JOIN', 'searchJoin'),
        ],
    ],

    'generator' => [
        'basePath'      => app_path(),
        'rootNamespace' => 'App\\',
        'paths'         => [
            'repositories' => 'Repositories',
            'criteria'     => 'Criteria',
        ],
    ],
];
```

---

## Testing

```bash
composer test
```

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

---

## License

MIT — see [LICENSE](LICENSE).
