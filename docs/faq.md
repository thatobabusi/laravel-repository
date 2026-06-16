# Frequently Asked Questions

## Installation & Setup

**Q: Do I need to register the service provider manually?**
A: No. Laravel auto-discovers the package service provider from `composer.json`.

**Q: Do I need to publish the config file?**
A: No. The package merges default config automatically. Publish only when you want to customize pagination, request parameter names, or generator paths.

**Q: Which Laravel versions are supported?**
A: Laravel 11, 12, and 13 are supported by the 1.x package line.

## Repositories

**Q: Should I inject `RepositoryInterface` or a concrete repository?**
A: In most apps, inject the concrete repository (`UserRepository`) so multiple repositories can be used in the same class without contextual bindings. Use `RepositoryInterface` when you intentionally want contextual binding.

**Q: Can I add custom methods to a repository?**
A: Yes. Extend `BaseRepository` and add domain-specific methods to the concrete repository.

**Q: Does the repository replace Eloquent models?**
A: No. It wraps Eloquent query and persistence operations. Your models, relationships, casts, scopes, and events still work normally.

**Q: Why does `find()` throw instead of returning null?**
A: `find()` uses Eloquent's `findOrFail()` behavior. Add a custom repository method if your use case needs nullable lookup semantics.

## Criteria

**Q: When should I use criteria instead of `scopeQuery()`?**
A: Use criteria for reusable, named, composable filters. Use `scopeQuery()` for quick one-off constraints.

**Q: Can criteria accept constructor arguments?**
A: Yes. Criteria are regular PHP classes, so pass request data, dates, IDs, or value objects through the constructor.

**Q: Are criteria applied to write operations?**
A: Criteria are intended for read operations. Use explicit custom methods when write operations require constraints.

## RequestCriteria

**Q: Can users search any database column from the URL?**
A: No. Only fields declared in `$fieldSearchable` can be searched.

**Q: Can I rename `search`, `orderBy`, or other query parameters?**
A: Yes. Set the `REPOSITORY_CRITERIA_*` environment variables or publish and edit `config/repository.php`.

**Q: Can I eager-load relations from the URL?**
A: Yes, with `with=posts;profile`. The relation methods must already exist on the model.

## Generators

**Q: Can repositories be generated into subdirectories?**
A: Yes. Use forward slashes: `php artisan make:repository Admin/User`.

**Q: Can I change where generated files are created?**
A: Yes. Configure `generator.basePath`, `generator.rootNamespace`, and `generator.paths` in `config/repository.php`.

## Testing

**Q: How do I run the package tests?**
A: Run `composer test` from the package root.
