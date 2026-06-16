# Contributing

Contributions are welcome. Keep changes focused, documented, and covered by tests when behavior changes.

## Local Setup

```bash
composer install
composer test
```

## Contribution Guidelines

- Keep pull requests scoped to one concern.
- Add or update tests for behavior changes.
- Update documentation when public APIs, configuration, or commands change.
- Follow the existing namespace, command, and stub conventions.
- Avoid unrelated formatting churn.

## Documentation Standards

Documentation should be:

- Task-oriented first, then reference-oriented.
- Accurate for Laravel 11, 12, and 13.
- Written with runnable examples.
- Linked from [Documentation Index](index.md) when it adds a new topic.
- Consistent with the README structure: quick start, key features, examples, support links.

## Testing

Run the test suite before submitting changes:

```bash
composer test
```

For generator changes, include tests that assert:

- The expected file path is created.
- The namespace is correct.
- Existing files are not overwritten.

For repository behavior changes, include tests that cover:

- Query results.
- Model reset behavior after terminal calls.
- Criteria and scope interactions when relevant.

## Release Notes

Update [CHANGELOG.md](../CHANGELOG.md) under `[Unreleased]` for user-facing changes.
