# 🎉 Laravel Discovery v1.0.0 - Initial Stable Release

A powerful class discovery system for Laravel with attribute-based scanning, directory traversal, and monorepo support.

## ✨ Features

- **Attribute-Based Discovery**: Scan classes by PHP 8 attributes
- **Directory Scanning**: Glob pattern support for flexible file discovery
- **Interface & Parent Class Discovery**: Find implementations and extensions
- **Method & Property Discovery**: Discover classes by method/property attributes
- **Fluent Builder API**: Intuitive chainable API with filters and validators
- **Smart Caching**: File-based caching system for optimal performance
- **Monorepo Support**: Advanced namespace resolution for monorepo architectures
- **Dependency Injection**: Full Laravel container integration
- **Laravel 11 & 12**: Compatible with the latest Laravel versions

## 📦 Installation

```bash
composer require pixielity/laravel-discovery
```

## 🎯 Quick Example

```php
use Pixielity\Discovery\Facades\Discovery;

// Discover classes with a specific attribute
$services = Discovery::in('app/Services')
    ->withAttribute(ServiceAttribute::class)
    ->cached()
    ->get();
```

## 📚 Documentation

- [Getting Started Guide](https://github.com/pixielity-co/laravel-discovery/blob/main/docs/GETTING_STARTED.md)
- [API Documentation](https://github.com/pixielity-co/laravel-discovery/blob/main/docs/API.md)
- [Contributing Guidelines](https://github.com/pixielity-co/laravel-discovery/blob/main/CONTRIBUTING.md)

## 🧪 Testing

Comprehensive test suite with 100% coverage of core functionality.

## 📝 Requirements

- PHP 8.3+
- Laravel 11.0+ or 12.0+

## 🙏 Credits

Built with ❤️ by the Pixielity Team

---

**Full Changelog**: https://github.com/pixielity-co/laravel-discovery/blob/main/CHANGELOG.md
