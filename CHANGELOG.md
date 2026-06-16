# Changelog

All notable changes to `thatobabusi/laravel-repository` are documented here.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).
This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Added
- Documentation index, FAQ, troubleshooting, contributing, and version compatibility guides.

### Changed
- Reworked the README and internal documentation links to match the package documentation standards used by `laravel-ddd`.

## [1.0.0] - 2026-04-18

### Added
- `RepositoryInterface` - full contract covering all CRUD, query, and scope operations
- `CriteriaInterface` - single `apply()` contract for reusable query modifiers
- `RepositoryCriteriaInterface` - criteria management: push, pop, get, skip, reset
- `BaseRepository` - abstract Eloquent implementation of both interfaces
- `RequestCriteria` - HTTP-driven filtering via `search`, `searchFields`, `filter`, `orderBy`, `sortedBy`, `with`, `withCount`, `searchJoin` query parameters
- `make:repository {name}` Artisan command with stub generation and sub-directory support
- `make:criteria {name}` Artisan command with automatic `Criteria` suffix handling
- `config/repository.php` - configurable pagination limit and criteria parameter names
- Full test suite: 44 tests across Unit (BaseRepository, RequestCriteria) and Feature (Artisan commands)
- `.env.example` documenting all supported environment variables
- Laravel package auto-discovery via `extra.laravel.providers`

### Fixed
- Field-specific search syntax (`field:value`) no longer leaks `%%` LIKE wildcards into unrelated fields
- `paginate()` respects the explicitly passed `$limit` argument over the config default

[Unreleased]: https://github.com/thatobabusi/laravel-repository/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/thatobabusi/laravel-repository/releases/tag/v1.0.0
