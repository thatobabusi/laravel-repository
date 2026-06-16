# Configuration Options

All configuration lives in `config/repository.php`. Publish it to override the defaults:

```bash
php artisan vendor:publish --tag=repository-config
```

The package merges its own defaults on every boot - you only need to publish and edit this file if you want to change something.

---

## Full reference

### `pagination.limit`

**Type:** `int` - **Default:** `env('REPOSITORY_PAGINATION_LIMIT', 15)`

The number of records returned per page when `paginate()` or `simplePaginate()` is called without an explicit `$limit` argument.

```php
'pagination' => [
    'limit' => env('REPOSITORY_PAGINATION_LIMIT', 15),
],
```

```env
REPOSITORY_PAGINATION_LIMIT=25
```

> **Note:** Passing `$limit` explicitly to `paginate(25)` always takes precedence over this config value.

---

### `criteria.params`

**Type:** `array`

Maps `RequestCriteria` URL parameter names to the keys it reads from `$request->get()`. Rename any parameter to avoid conflicts with your own query string conventions.

```php
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
```

| Key | Default param name | Purpose |
|---|---|---|
| `search` | `search` | Full-text or field-specific search term |
| `searchFields` | `searchFields` | Per-request operator overrides (`name:like;email:=`) |
| `filter` | `filter` | Column selection (`id;name;email`) |
| `orderBy` | `orderBy` | Sort column(s) |
| `sortedBy` | `sortedBy` | Sort direction (`asc` or `desc`) |
| `with` | `with` | Eager-load relations (`posts;profile`) |
| `withCount` | `withCount` | Eager-load relation counts |
| `searchJoin` | `searchJoin` | Boolean operator between search fields (`and` or `or`) |

**Example - use `q` instead of `search` and `sort` instead of `orderBy`:**

```env
REPOSITORY_CRITERIA_SEARCH=q
REPOSITORY_CRITERIA_ORDER_BY=sort
REPOSITORY_CRITERIA_SORTED_BY=direction
```

---

### `generator`

**Type:** `array`

Controls where `make:repository` and `make:criteria` put generated files and what namespace they use.

```php
'generator' => [
    'basePath'      => app_path(),       // absolute path to the root output directory
    'rootNamespace' => 'App\\',          // PSR-4 root namespace
    'paths'         => [
        'repositories' => 'Repositories', // relative to basePath
        'criteria'     => 'Criteria',      // relative to basePath
    ],
],
```

| Key | Default | Description |
|---|---|---|
| `basePath` | `app_path()` | Root directory for all generated classes |
| `rootNamespace` | `'App\\'` | Namespace prefix prepended to the path |
| `paths.repositories` | `'Repositories'` | Sub-directory for repository classes |
| `paths.criteria` | `'Criteria'` | Sub-directory for criteria classes |

**Example - generate into a `src/` DDD structure:**

```php
'generator' => [
    'basePath'      => base_path('src'),
    'rootNamespace' => 'Src\\',
    'paths'         => [
        'repositories' => 'Infrastructure/Repositories',
        'criteria'     => 'Infrastructure/Criteria',
    ],
],
```

```bash
php artisan make:repository User
# creates: src/Infrastructure/Repositories/UserRepository.php
# namespace: Src\Infrastructure\Repositories
```
