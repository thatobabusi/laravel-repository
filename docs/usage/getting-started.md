# Getting Started

This guide walks from a fresh install to a working repository wired into a controller.

---

## 1. Generate a repository

```bash
php artisan make:repository User
```

Creates `app/Repositories/UserRepository.php`:

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

The only required override is `model()` - return the fully-qualified class name of the Eloquent model the repository wraps.

### Sub-directory support

Generate into a namespace sub-folder using forward slashes:

```bash
php artisan make:repository Admin/User
# creates: app/Repositories/Admin/UserRepository.php
# namespace: App\Repositories\Admin
```

---

## 2. Declare searchable fields

Fill in `$fieldSearchable` to tell `RequestCriteria` which columns it may search and with what operator:

```php
protected array $fieldSearchable = [
    'name'  => 'like',   // WHERE name LIKE '%value%'
    'email' => '=',      // WHERE email = 'value'
    'age'   => 'between',
    'roles' => 'in',
];
```

Supported operators: `=`, `like`, `ilike`, `>`, `>=`, `<`, `<=`, `<>`, `in`, `between`.

---

## 3. Bind in a service provider

Bind the concrete repository to the interface in `AppServiceProvider` (or a dedicated `RepositoryServiceProvider`):

```php
use App\Repositories\UserRepository;
use Thatobabusi\LaravelRepositoryPattern\Contracts\RepositoryInterface;

public function register(): void
{
    // Bind the generic interface to your concrete class
    $this->app->bind(UserRepository::class, UserRepository::class);

    // Or bind to the interface and always inject UserRepository:
    // $this->app->bind(RepositoryInterface::class, UserRepository::class);
}
```

For most applications, binding each concrete class directly (without the interface) is the simplest approach and allows injecting multiple repositories per controller.

---

## 4. Inject into a controller

```php
use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private UserRepository $users
    ) {}

    public function index()
    {
        return $this->users->paginate(25);
    }

    public function show(int $id)
    {
        return $this->users->find($id);
    }

    public function store(Request $request)
    {
        return $this->users->create($request->validated());
    }

    public function update(Request $request, int $id)
    {
        return $this->users->update($request->validated(), $id);
    }

    public function destroy(int $id)
    {
        $this->users->delete($id);
        return response()->noContent();
    }
}
```

---

## 5. Add custom methods

`BaseRepository` covers standard CRUD. For anything domain-specific, add methods to your concrete class:

```php
class UserRepository extends BaseRepository
{
    public function model(): string
    {
        return User::class;
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function activeAdmins(): Collection
    {
        return $this->model
            ->where('active', true)
            ->where('role', 'admin')
            ->orderBy('name')
            ->get();
    }
}
```

---

## 6. Eager loading

Chain `with()` before any read call:

```php
$this->users->with(['posts', 'profile'])->paginate(25);
$this->users->with('roles')->find($id);
$this->users->withCount('posts')->all();
```

The model is reset after each terminal call (`all()`, `paginate()`, `find()`, etc.) so eager loads don't leak between requests.

---

## 7. Ordering

```php
$this->users->orderBy('name', 'asc')->paginate(25);
$this->users->orderBy('created_at', 'desc')->all();
```

---

## 8. Column selection

Pass a `$columns` array to any read method:

```php
$this->users->all(['id', 'name', 'email']);
$this->users->paginate(25, ['id', 'name']);
$this->users->find($id, ['id', 'email']);
```
