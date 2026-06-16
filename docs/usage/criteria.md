# Criteria

Criteria are small, single-responsibility classes that modify an Eloquent query builder. They implement `CriteriaInterface`, which has a single method: `apply()`. Stack as many criteria as you need - they run in push order before the terminal query executes.

---

## Creating a criteria class

```bash
php artisan make:criteria ActiveUsers
```

Creates `app/Criteria/ActiveUsersCriteria.php` (the `Criteria` suffix is added automatically if absent):

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

The `$model` argument is the current Eloquent builder (or model). Return the modified builder.

---

## Criteria with constructor arguments

Pass data into a criteria class via its constructor:

```php
class CreatedAfterCriteria implements CriteriaInterface
{
    public function __construct(private readonly Carbon $date) {}

    public function apply(mixed $model, RepositoryInterface $repository): mixed
    {
        return $model->where('created_at', '>=', $this->date);
    }
}
```

```php
$this->users->pushCriteria(new CreatedAfterCriteria(now()->subDays(30)))->all();
```

---

## Applying criteria

### Stack multiple criteria

```php
$this->users
    ->pushCriteria(new ActiveUsersCriteria())
    ->pushCriteria(new CreatedAfterCriteria(now()->subMonth()))
    ->pushCriteria(new RequestCriteria($request))
    ->paginate(25);
```

Criteria are applied in the order they were pushed.

### Apply a single criteria without stacking

```php
$results = $this->users->getByCriteria(new ActiveUsersCriteria());
```

`getByCriteria()` applies one criteria, fetches all results, then resets the model. The criteria stack is not affected.

---

## Managing the criteria stack

### Remove a specific criteria

```php
$this->users->popCriteria(ActiveUsersCriteria::class);
$this->users->popCriteria(new ActiveUsersCriteria());   // by instance also works
```

### Skip all criteria for one call

```php
$this->users->skipCriteria()->all();       // criteria bypassed
$this->users->skipCriteria(false)->all();  // re-enable
```

### Get the current stack

```php
$criteria = $this->users->getCriteria();  // returns Illuminate\Support\Collection
```

### Clear the stack

```php
$this->users->resetCriteria();
```

---

## Practical patterns

### Criteria defined in the `boot()` method

Apply criteria that should always run for a repository by pushing them in `boot()`:

```php
class UserRepository extends BaseRepository
{
    public function boot(): void
    {
        $this->pushCriteria(new ActiveUsersCriteria());
    }

    public function model(): string
    {
        return User::class;
    }
}
```

### Using the repository interface in criteria

The `$repository` argument in `apply()` gives access to `getFieldsSearchable()` and any public method on your repository. Use it to make criteria that adapt to the repository they're applied to:

```php
public function apply(mixed $model, RepositoryInterface $repository): mixed
{
    $searchable = $repository->getFieldsSearchable();
    // ... conditional logic based on searchable fields
    return $model;
}
```
