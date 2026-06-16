# Version Compatibility

## Supported Versions

| Laravel | Package | PHP | Status |
|---|---|---|---|
| 11.x - 13.x | 1.x | ^8.2 | Current |

## Composer Requirements

The package requires:

```json
{
    "php": "^8.2",
    "illuminate/support": "^11|^12|^13",
    "illuminate/database": "^11|^12|^13",
    "illuminate/console": "^11|^12|^13"
}
```

## Test Matrix

The current development dependency range is:

| Dependency | Version |
|---|---|
| Orchestra Testbench | ^9 or ^10 |
| PHPUnit | ^11 |

## Upgrade Notes

### From pre-1.0 development versions

1. Confirm your app runs PHP 8.2 or newer.
2. Confirm your app uses Laravel 11, 12, or 13.
3. Publish the current config if you need overrides:

```bash
php artisan vendor:publish --tag=repository-config
```

4. Review custom repositories for the current `model(): string` method signature.
5. Run your test suite and any API endpoint tests that use `RequestCriteria`.

## Support Policy

The package follows semantic versioning. Bug fixes should target the current major version, while breaking changes should wait for the next major version.
