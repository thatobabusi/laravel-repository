# Artisan Commands

Two Artisan generators create repository and criteria classes from stubs, placing them in the correct directory and namespace automatically.

---

## `make:repository`

```
php artisan make:repository {name}
```

Creates a new repository class that extends `BaseRepository`.

### Argument

| Argument | Description |
|---|---|
| `name` | The model name. Supports forward-slash sub-paths for sub-directories. |

### Output paths

| Input | File created | Namespace |
|---|---|---|
| `User` | `app/Repositories/UserRepository.php` | `App\Repositories` |
| `Admin/User` | `app/Repositories/Admin/UserRepository.php` | `App\Repositories\Admin` |
| `Blog/Post` | `app/Repositories/Blog/PostRepository.php` | `App\Repositories\Blog` |

The `Repository` suffix is always appended to the class name.

### Generated stub

```php
namespace App\Repositories;

use Thatobabusi\LaravelRepositoryPattern\Eloquent\BaseRepository;
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

### Idempotency

Running the command twice for the same name returns an error and does not overwrite the existing file:

```
 ERROR  Repository already exists: UserRepository
```

### Customising the output path

Change the base path and namespace via `config/repository.php`:

```php
'generator' => [
    'basePath'      => base_path('src'),
    'rootNamespace' => 'Src\\',
    'paths' => [
        'repositories' => 'Infrastructure/Repositories',
    ],
],
```

See [Configuration → Options](../configuration/options) for the full reference.

---

## `make:criteria`

```
php artisan make:criteria {name}
```

Creates a new criteria class that implements `CriteriaInterface`.

### Argument

| Argument | Description |
|---|---|
| `name` | The criteria class name. The `Criteria` suffix is appended automatically if absent. |

### Output paths

| Input | File created | Class name |
|---|---|---|
| `ActiveUsers` | `app/Criteria/ActiveUsersCriteria.php` | `ActiveUsersCriteria` |
| `ActiveUsersCriteria` | `app/Criteria/ActiveUsersCriteria.php` | `ActiveUsersCriteria` (no double-suffix) |

### Generated stub

```php
namespace App\Criteria;

use Thatobabusi\LaravelRepositoryPattern\Contracts\CriteriaInterface;
use Thatobabusi\LaravelRepositoryPattern\Contracts\RepositoryInterface;

class ActiveUsersCriteria implements CriteriaInterface
{
    public function apply(mixed $model, RepositoryInterface $repository): mixed
    {
        return $model;
    }
}
```

Fill in the `apply()` body to modify the Eloquent builder and return it.

### Idempotency

Running the command twice for the same name returns an error without overwriting:

```
 ERROR  Criteria already exists: ActiveUsersCriteria
```

### Customising the output path

```php
'generator' => [
    'paths' => [
        'criteria' => 'Infrastructure/Criteria',
    ],
],
```
