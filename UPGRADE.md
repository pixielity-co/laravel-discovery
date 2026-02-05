# Upgrade Guide

This document provides guidance for upgrading between major versions of the Discovery package.

## Upgrading to 1.0 from Pre-release

### Breaking Changes

None - this is the initial stable release.

### New Features

- Attribute-based discovery
- Directory scanning with glob patterns
- Interface and parent class discovery
- Method and property discovery
- File-based caching
- Monorepo support
- Full dependency injection

### Migration Steps

1. Update composer.json:

```bash
composer require fulers/discovery:^1.0
```

2. Publish configuration (optional):

```bash
php artisan vendor:publish --tag=discovery-config
```

3. Clear caches:

```bash
php artisan cache:clear
composer dump-autoload
```

## Future Upgrades

### Semantic Versioning

This package follows [Semantic Versioning](https://semver.org/):

- **Major versions** (2.0, 3.0) - Breaking changes
- **Minor versions** (1.1, 1.2) - New features, backward compatible
- **Patch versions** (1.0.1, 1.0.2) - Bug fixes, backward compatible

### Deprecation Policy

- Deprecated features will be marked with `@deprecated` in docblocks
- Deprecations will trigger `E_USER_DEPRECATED` notices
- Deprecated features will be removed in the next major version
- At least 6 months notice before removal

### Upgrade Process

1. Read the CHANGELOG.md for your target version
2. Check for breaking changes in this document
3. Update your composer.json
4. Run `composer update fulers/discovery`
5. Test your application thoroughly
6. Clear all caches

## Support Policy

| Version | PHP Version | Laravel Version | Support Until |
| ------- | ----------- | --------------- | ------------- |
| 1.x     | 8.3+        | 11.x, 12.x      | TBD           |

## Getting Help

- 📧 Email: support@fulers.com
- 🐛 Issues: [GitHub Issues](https://github.com/fulers/discovery/issues)
- 💬 Discussions: [GitHub Discussions](https://github.com/fulers/discovery/discussions)
