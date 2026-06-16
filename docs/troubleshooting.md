# Troubleshooting

## Installation & Discovery

### Service provider is not loading

Laravel should auto-discover `Laravel\Repository\LaravelRepositoryServiceProvider`.

If commands are missing, refresh package discovery:

```bash
composer dump-autoload
php artisan package:discover
```

### Config changes are not taking effect

Publish the config and clear Laravel's cached config:

```bash
php artisan vendor:publish --tag=repository-config
php artisan config:clear
```

In production, rebuild the config cache after changes:

```bash
php artisan config:cache
```

## Generator Issues

### `make:repository` says the repository already exists

The generator does not overwrite existing files. Rename or remove the existing class manually, then run the command again.

### Generated classes use the wrong namespace

Check the generator settings in `config/repository.php`:

```php
'generator' => [
    'basePath' => app_path(),
    'rootNamespace' => 'App\\',
    'paths' => [
        'repositories' => 'Repositories',
        'criteria' => 'Criteria',
    ],
],
```

After changing the namespace or output path, run:

```bash
composer dump-autoload
```

## Repository Issues

### `Class ... must be an instance of Illuminate\Database\Eloquent\Model`

Your repository's `model()` method must return an Eloquent model class:

```php
public function model(): string
{
    return User::class;
}
```

### `ModelNotFoundException` from `find()` or `update()`

`find()` and `update()` use `findOrFail()`. Catch `ModelNotFoundException` or use a custom repository method if a nullable result is preferred.

### Query modifiers leak between calls

Terminal read methods reset the model after execution. If you are seeing unexpected state, check for a long-lived repository instance with criteria pushed in a previous operation. Use `resetCriteria()` or `skipCriteria()` when needed.

## Criteria Issues

### `pushCriteria()` throws `RepositoryException`

Only instances of `CriteriaInterface` can be pushed:

```php
use Laravel\Repository\Contracts\CriteriaInterface;

class ActiveUsersCriteria implements CriteriaInterface
{
    public function apply(mixed $model, RepositoryInterface $repository): mixed
    {
        return $model->where('active', true);
    }
}
```

### Criteria are not affecting results

Make sure the criteria is pushed before the terminal call:

```php
$users->pushCriteria(new ActiveUsersCriteria())->paginate(25);
```

Methods such as `all()`, `paginate()`, `find()`, and `findWhere()` execute the query immediately.

## RequestCriteria Issues

### `search` returns no results

`RequestCriteria` only searches fields declared in `$fieldSearchable`:

```php
protected array $fieldSearchable = [
    'name' => 'like',
    'email' => '=',
];
```

### `searchFields` appears to ignore a field

`searchFields` can only override fields already allowed by `$fieldSearchable`. This is intentional so clients cannot search arbitrary columns.

### Sorting direction is always `asc`

Only `desc` is accepted as a descending value. Any other value falls back to `asc`:

```text
GET /users?orderBy=name&sortedBy=desc
```

### Custom query parameter names are not working

Check `.env`, `config/repository.php`, and Laravel's config cache:

```env
REPOSITORY_CRITERIA_SEARCH=q
REPOSITORY_CRITERIA_ORDER_BY=sort
REPOSITORY_CRITERIA_SORTED_BY=direction
```

Then run:

```bash
php artisan config:clear
```
