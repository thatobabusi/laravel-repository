# RequestCriteria

`RequestCriteria` is a built-in criteria class that translates HTTP query parameters into Eloquent constraints. Push it onto any repository in a controller and your endpoint gains searching, filtering, ordering, and eager loading — all driven by URL parameters with no extra code.

---

## Basic usage

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

---

## Declaring searchable fields

`RequestCriteria` only searches the fields you explicitly declare in `$fieldSearchable`. This prevents arbitrary column enumeration from the URL.

```php
class UserRepository extends BaseRepository
{
    protected array $fieldSearchable = [
        'name'  => 'like',    // LIKE '%value%'
        'email' => '=',       // exact match
        'age'   => 'between', // BETWEEN ? AND ?
        'roles' => 'in',      // IN (...)
    ];

    public function model(): string { return User::class; }
}
```

Supported operators: `=`, `like`, `ilike`, `>`, `>=`, `<`, `<=`, `<>`, `in`, `between`.

---

## Query parameters

### `search` — full-text across all searchable fields

```
GET /users?search=john
```

Applies the declared operator for every field in `$fieldSearchable`, joined with `OR` by default:

```sql
WHERE (name LIKE '%john%' OR email = 'john')
```

### `search` — field-specific

Use `field:value` syntax to target a single field:

```
GET /users?search=email:john@example.com
```

Multiple field-specific terms separated by `;`:

```
GET /users?search=name:john;email:john@example.com
```

### `searchJoin` — change the boolean operator

```
GET /users?search=john&searchJoin=and
```

Changes `OR` to `AND` between search field clauses.

### `searchFields` — override operators per request

Override the declared operator for specific fields in a single request:

```
GET /users?search=john&searchFields=name:like;email:=
```

The `searchFields` parameter restricts the search to only the listed fields for that request.

### `orderBy` — sort results

```
GET /users?orderBy=name
GET /users?orderBy=name;created_at   (multiple columns)
```

### `sortedBy` — sort direction

```
GET /users?orderBy=name&sortedBy=desc
```

Values: `asc` (default) or `desc`.

### `filter` — select specific columns

```
GET /users?filter=id;name;email
```

Translates to `SELECT id, name, email FROM users`.

### `with` — eager-load relations

```
GET /users?with=posts;profile
```

Equivalent to `->with(['posts', 'profile'])`.

### `withCount` — eager-load relation counts

```
GET /users?withCount=posts;comments
```

Adds `posts_count` and `comments_count` to each result.

---

## Combined example

```
GET /users?search=john&searchJoin=and&orderBy=name&sortedBy=asc&filter=id;name;email&with=profile
```

Produces:

```sql
SELECT id, name, email FROM users
WHERE (name LIKE '%john%' AND email = 'john')
ORDER BY name ASC
```

With `profile` eager-loaded on each result.

---

## Customising parameter names

Rename any query parameter to avoid conflicts with your own URL schema. Set via `.env`:

```env
REPOSITORY_CRITERIA_SEARCH=q
REPOSITORY_CRITERIA_ORDER_BY=sort
REPOSITORY_CRITERIA_SORTED_BY=direction
REPOSITORY_CRITERIA_FILTER=fields
REPOSITORY_CRITERIA_WITH=include
REPOSITORY_CRITERIA_WITH_COUNT=count
REPOSITORY_CRITERIA_SEARCH_FIELDS=searchFields
REPOSITORY_CRITERIA_SEARCH_JOIN=searchJoin
```

Or publish the config and edit `config/repository.php` directly. See [Configuration → Options](../configuration/options).

---

## Security notes

- Only fields listed in `$fieldSearchable` are searched — arbitrary column names from the URL are ignored
- `filter` (column selection) does not expose data beyond what the query already returns; it only reduces which columns are fetched
- `with` only eager-loads relations; it does not grant access to data the model's relationship methods don't already define
- Operators (`like`, `=`, etc.) are defined server-side in `$fieldSearchable` — the client cannot inject arbitrary SQL operators
