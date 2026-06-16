# Installation

---

## Requirements

| Requirement | Version |
|---|---|
| PHP | ^8.2 |
| Laravel | ^11 \| ^12 \| ^13 |

---

## 1. Install via Composer

```bash
composer require thatobabusi/laravel-repository
```

Laravel auto-discovers `LaravelRepositoryServiceProvider` - no manual registration is needed.

---

## 2. Publish the config (optional)

```bash
php artisan vendor:publish --tag=repository-config
```

Creates `config/repository.php` where you can change:

- The default pagination limit
- Query-string parameter names used by `RequestCriteria`
- Generator output paths and root namespace

The package merges its own defaults, so publishing is only required if you want to override something. See [Configuration Options](configuration/options.md) for the full reference.

---

## 3. Create your first repository

```bash
php artisan make:repository User
```

This generates `app/Repositories/UserRepository.php` with a stub ready to fill in:

```php
namespace App\Repositories;

use Laravel\Repository\Eloquent\BaseRepository;
use App\Models\User;

class UserRepository extends BaseRepository
{
    protected array $fieldSearchable = [
        //
    ];

    public function model(): string
    {
        return User::class;
    }
}
```

See [Getting Started](usage/getting-started.md) to continue from here.

---

## Environment variables

All config keys have `.env` overrides:

```env
REPOSITORY_PAGINATION_LIMIT=15

REPOSITORY_CRITERIA_SEARCH=search
REPOSITORY_CRITERIA_SEARCH_FIELDS=searchFields
REPOSITORY_CRITERIA_FILTER=filter
REPOSITORY_CRITERIA_ORDER_BY=orderBy
REPOSITORY_CRITERIA_SORTED_BY=sortedBy
REPOSITORY_CRITERIA_WITH=with
REPOSITORY_CRITERIA_WITH_COUNT=withCount
REPOSITORY_CRITERIA_SEARCH_JOIN=searchJoin
```
