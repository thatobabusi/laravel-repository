# Scope Query

`scopeQuery()` lets you apply a one-off anonymous query constraint without creating a full criteria class. It's useful for single-use filters that don't need to be reused or composed.

---

## Basic usage

```php
$this->users
    ->scopeQuery(fn ($q) => $q->where('verified', true)->where('active', true))
    ->paginate(25);
```

The closure receives the current Eloquent builder and must return it (modified or not).

---

## When to use `scopeQuery` vs criteria

| | `scopeQuery` | Criteria class |
|---|---|---|
| Reusable across repositories | — | ✓ |
| Composable / stackable | — | ✓ |
| Constructor arguments | ✓ (closure captures) | ✓ |
| One-off inline constraint | ✓ | — |
| Readable in complex query chains | — | ✓ |

Use `scopeQuery` for quick, one-off filters inline in a controller or service. Create a criteria class when the constraint is reused, complex, or needs a meaningful name.

---

## Combining with criteria

`scopeQuery` and the criteria stack work independently. Both are applied before the terminal query, in this order:

1. All pushed criteria (via `applyCriteria()`)
2. The scope closure (via `applyScope()`)

```php
$this->users
    ->pushCriteria(new ActiveUsersCriteria())   // applied first
    ->scopeQuery(fn ($q) => $q->whereNotNull('verified_at'))  // applied second
    ->paginate(25);
```

---

## Clearing a scope

`scopeQuery` is reset automatically after each terminal call (`all()`, `paginate()`, `find()`, etc.). To clear it manually before the call:

```php
$this->users->resetScope()->all();
```

---

## Closure captures

Since `scopeQuery` accepts any closure, you can capture variables from the surrounding context:

```php
$minAge = $request->integer('min_age', 18);

$this->users
    ->scopeQuery(fn ($q) => $q->where('age', '>=', $minAge))
    ->paginate(25);
```
