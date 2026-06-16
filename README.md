# Laravel Repository Toolkit

[![Latest Version on Packagist](https://img.shields.io/packagist/v/thatobabusi/laravel-repository.svg?style=flat-square)](https://packagist.org/packages/thatobabusi/laravel-repository)
[![Total Downloads](https://img.shields.io/packagist/dt/thatobabusi/laravel-repository.svg?style=flat-square)](https://packagist.org/packages/thatobabusi/laravel-repository)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

Laravel Repository is an Eloquent-backed repository toolkit for Laravel applications. It gives you a reusable `BaseRepository`, contracts for CRUD and criteria-based query composition, HTTP-driven filtering with `RequestCriteria`, and Artisan generators for repositories and criteria.

## Quick Start

```bash
composer require thatobabusi/laravel-repository
php artisan vendor:publish --tag=repository-config
php artisan make:repository User
```

The generated repository only needs to point at its Eloquent model:

```php
namespace App\Repositories;

use App\Models\User;
use Laravel\Repository\Eloquent\BaseRepository;

class UserRepository extends BaseRepository
{
    protected array $fieldSearchable = [
        'name' => 'like',
        'email' => '=',
    ];

    public function model(): string
    {
        return User::class;
    }
}
```

Use it from controllers, services, jobs, or actions:

```php
use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private UserRepository $users) {}

    public function index(Request $request)
    {
        return $this->users->paginate(25);
    }
}
```

See [Getting Started](docs/usage/getting-started.md) for the complete walkthrough.

## Documentation

Complete documentation is organized into focused guides:

### Getting Started
- [Installation & Setup](docs/installation.md) - Install the package, publish config, and create your first repository.
- [Configuration](docs/configuration/options.md) - Configure pagination, request parameter names, and generator paths.

### Using the Toolkit
- [Getting Started](docs/usage/getting-started.md) - Generate, bind, inject, and extend repositories.
- [Criteria](docs/usage/criteria.md) - Build reusable query modifiers and manage the criteria stack.
- [RequestCriteria](docs/usage/request-criteria.md) - Add URL-driven search, filter, sorting, eager loading, and counts.
- [Scope Query](docs/usage/scope-query.md) - Apply one-off anonymous query constraints.

### Reference
- [Method Reference](docs/reference/method-reference.md) - Every repository method with signatures and examples.
- [Contracts](docs/reference/contracts.md) - `RepositoryInterface`, `CriteriaInterface`, and `RepositoryCriteriaInterface`.
- [Artisan Commands](docs/reference/commands.md) - `make:repository` and `make:criteria`.

### Support
- [Version Compatibility](docs/version-compatibility.md) - Laravel and PHP support matrix.
- [Troubleshooting](docs/troubleshooting.md) - Common errors and fixes.
- [FAQ](docs/faq.md) - Short answers to common questions.
- [Contributing](docs/contributing.md) - How to contribute docs, tests, and package changes.
- [Changelog](CHANGELOG.md) - Release history.

## Key Features

- Eloquent-backed `BaseRepository` with common CRUD and query operations
- Criteria stack for reusable, composable query filters
- `RequestCriteria` for HTTP-driven search, filtering, ordering, eager loading, and relation counts
- Chainable query helpers: `orderBy`, `with`, `withCount`, `has`, `whereHas`, `hidden`, `visible`, and `scopeQuery`
- Artisan generators for repositories and criteria
- Configurable generator paths and root namespace
- Laravel package auto-discovery
- Laravel 11, 12, and 13 support

## Version Compatibility

| Laravel | Package | PHP | Status |
|---|---|---|---|
| 11.x - 13.x | 1.x | ^8.2 | Current |

See [Version Compatibility](docs/version-compatibility.md) for details.

## Example: HTTP-Driven Filtering

Push `RequestCriteria` in a controller and expose a flexible listing endpoint:

```php
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Laravel\Repository\Criteria\RequestCriteria;

class UserController extends Controller
{
    public function index(Request $request, UserRepository $users)
    {
        $users->pushCriteria(new RequestCriteria($request));

        return $users->paginate(25);
    }
}
```

Supported query parameters include:

```text
GET /users?search=john&orderBy=name&sortedBy=asc&filter=id;name;email&with=profile
```

See [RequestCriteria](docs/usage/request-criteria.md) for all supported parameters and security notes.

## Example: Custom Criteria

```bash
php artisan make:criteria ActiveUsers
```

```php
namespace App\Criteria;

use Laravel\Repository\Contracts\CriteriaInterface;
use Laravel\Repository\Contracts\RepositoryInterface;

class ActiveUsersCriteria implements CriteriaInterface
{
    public function apply(mixed $model, RepositoryInterface $repository): mixed
    {
        return $model->where('active', true);
    }
}
```

```php
$users = $this->users
    ->pushCriteria(new ActiveUsersCriteria())
    ->orderBy('name')
    ->paginate(25);
```

## Testing

```bash
composer test
```

## Credits

- [Thato Babusi](https://github.com/thatobabusi)
- [Prettus Laravel Repository](https://github.com/andersao/l5-repository) for the original community pattern many Laravel teams know
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
