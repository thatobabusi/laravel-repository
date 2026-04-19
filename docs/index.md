# Laravel Repository Pattern

A clean, Eloquent-backed implementation of the repository pattern for Laravel 11–13. Decouples your controllers and services from the database layer, adds reusable criteria-based query composition, and provides Artisan generators so new repositories take seconds to create.

---

## What it provides

| Component | Description |
|---|---|
| `RepositoryInterface` | Full contract for all CRUD, query, and scope operations |
| `CriteriaInterface` | Single-method contract for reusable query modifiers |
| `RepositoryCriteriaInterface` | Criteria management: push, pop, skip, reset |
| `BaseRepository` | Abstract Eloquent implementation — extend and override `model()` |
| `RequestCriteria` | Translates HTTP query parameters into Eloquent constraints |
| `make:repository` | Artisan generator — creates a typed repository from a stub |
| `make:criteria` | Artisan generator — creates a criteria class from a stub |

---

## Architecture overview

```
Controller / Service
        │
        ▼
  RepositoryInterface          ← what you type-hint
        │
        ▼
  BaseRepository               ← what your repository extends
  ├── applyCriteria()          ← runs all pushed criteria in order
  ├── applyScope()             ← applies the one-off scopeQuery closure
  └── [read / write methods]   ← delegates to Eloquent, resets model after each call
        │
        ▼
  Criteria (stack)             ← each modifies the Eloquent builder in turn
  ├── RequestCriteria          ← search, filter, orderBy, with, ...
  ├── ActiveUsersCriteria      ← your custom criteria
  └── ...
```

---

## Requirements

| Requirement | Version |
|---|---|
| PHP | ^8.2 |
| Laravel | ^11 \| ^12 \| ^13 |

---

## Sections

| Section | What it covers |
|---|---|
| [Installation](installation) | Composer require, publish config |
| [Usage → Getting Started](usage/getting-started) | Create a repository, bind it, inject and use it |
| [Usage → Criteria](usage/criteria) | Create criteria, push/pop/skip, one-off scopes |
| [Usage → RequestCriteria](usage/request-criteria) | HTTP-driven filtering, all query parameters |
| [Usage → Scope Query](usage/scope-query) | One-off anonymous query scopes |
| [Configuration → Options](configuration/options) | Full `config/repository.php` reference |
| [Reference → Method Reference](reference/method-reference) | All repository methods with signatures |
| [Reference → Contracts](reference/contracts) | All three interfaces |
| [Reference → Commands](reference/commands) | `make:repository` and `make:criteria` |
| [Changelog](changelog) | Version history |
| [License](license) | MIT licence |
