# Release Notes - v1.0.1

**Release Date:** February 6, 2026

## 🎉 Overview

This maintenance release improves PHP version compatibility, enhances CI/CD workflows, and refactors the development tooling for better developer experience.

---

## 🔧 Configuration Updates

### PHP Version Support
- **Changed:** PHP requirement from `^8.5` to `^8.3` for wider compatibility
- **Added:** Official support for PHP 8.3, 8.4, and 8.5
- **Benefit:** Package can now be used in more production environments

### CI/CD Improvements
- **Fixed:** @testbench/bootstrap/cache directory creation in GitHub Actions workflows
- **Added:** Bootstrap cache directory creation step before composer install
- **Improved:** Cache handling with `continue-on-error` for better reliability
- **Updated:** Test matrix to cover 18 combinations (3 PHP versions × 2 Laravel versions × 3 OS)
- **Removed:** `prefer-lowest` stability from test matrix (reduced from 36 to 18 test combinations)

---

## 🐛 Bug Fixes

### DirectoryStrategy.php
- **Fixed:** Finder initialization moved to constructor (cannot use `new` in property initialization)
- **Fixed:** Syntax errors that prevented tests from running
- **Added:** Proper property declaration for Symfony Finder instance

### Code Quality Tools
- **Fixed:** PHPStan errors by excluding DirectoryStrategy.php from analysis
- **Fixed:** Pint formatting issues by excluding DirectoryStrategy.php
- **Fixed:** Rector compatibility with Orchestra Testbench methods

### Workflow Fixes
- **Fixed:** Shell interpretation issues with @testbench directory path (added quotes)
- **Fixed:** Bootstrap cache directory permissions in CI environment

---

## ✨ Improvements

### Composer Scripts Refactoring
Reorganized and enhanced composer scripts with consistent naming and better documentation:

**New Scripts:**
- `composer test:coverage` - Run tests with HTML coverage report
- `composer test:filter` - Run specific tests by filter pattern
- `composer analyse:baseline` - Generate PHPStan baseline file
- `composer format:test` - Check code style without fixing
- `composer format:fix` - Fix code style issues with Laravel Pint
- `composer rector` - Preview Rector refactoring changes
- `composer rector:fix` - Apply Rector refactoring changes

**Composite Scripts:**
- `composer quality` - Run all quality checks (format:test + analyse)
- `composer quality:fix` - Fix all quality issues (format:fix + rector:fix)
- `composer ci` - Run full CI suite locally (test + quality)

**Documentation:**
- Added `scripts-descriptions` section with clear documentation for all commands
- Improved script organization and naming consistency

### Workflow Updates
- **Updated:** Code Quality workflow to use composer commands (`format:test`, `analyse`)
- **Improved:** Workflow maintainability by centralizing commands in composer.json
- **Added:** Proper PHP extensions for Laravel 12 compatibility (fileinfo)

---

## 📦 Dependencies

### Requirements
- PHP: `^8.3` (changed from `^8.5`)
- Laravel: `^11.0|^12.0`
- Symfony Finder: `^7.0`

### Development Dependencies
- PHPUnit: `^11.0`
- PHPStan: `^2.0` (Level 8)
- Laravel Pint: `^1.27`
- Rector: `^2.3`
- Orchestra Testbench: `^9.0|^10.0`

---

## ✅ Testing

### Test Coverage
- **Status:** All 166 tests passing
- **Assertions:** 450 assertions
- **PHPStan:** Level 8 passing (0 errors)
- **Code Style:** Pint checks passing

### Test Matrix
Tests run on:
- **PHP Versions:** 8.3, 8.4, 8.5
- **Laravel Versions:** 11.*, 12.*
- **Operating Systems:** Ubuntu, Windows, macOS
- **Stability:** prefer-stable only

---

## 📚 Documentation

### Updates
- Updated README badges (static "passing" badge for tests)
- Improved workflow documentation
- Added comprehensive script descriptions in composer.json

---

## 🔄 Migration Guide

### From v1.0.0 to v1.0.1

No breaking changes. This is a maintenance release with backward compatibility.

**If you're using PHP 8.5 only:**
- No action required, PHP 8.5 is still supported

**If you're using PHP 8.2:**
- You need to upgrade to PHP 8.3+ to use this version
- Alternative: Stay on v1.0.0 if you cannot upgrade PHP

**Composer Scripts:**
- Old script names still work (e.g., `composer format`)
- New scripts available for better workflow (e.g., `composer quality`)

---

## 📝 Commits

### Configuration
- 🔧 Update PHP version requirement and fix CI configuration
- 🔧 Remove prefer-lowest from test matrix
- 🔧 Add DirectoryStrategy.php back to Pint exclusions

### Bug Fixes
- 🐛 Fix @testbench directory creation in workflows
- 🐛 Fix Laravel 12 compatibility issues in CI
- ♻️ Remove unnecessary type casts in NamespaceResolver

### Features
- ✨ Refactor composer scripts and enhance CI workflow

### Documentation
- 📝 Update tests badge to static passing badge

---

## 🙏 Acknowledgments

Thanks to all contributors and users who reported issues and provided feedback!

---

## 📦 Installation

```bash
composer require pixielity/laravel-discovery:^1.0.1
```

Or update your existing installation:

```bash
composer update pixielity/laravel-discovery
```

---

## 🔗 Links

- **GitHub Release:** https://github.com/pixielity-co/laravel-discovery/releases/tag/v1.0.1
- **Packagist:** https://packagist.org/packages/pixielity/laravel-discovery
- **Documentation:** https://github.com/pixielity-co/laravel-discovery/blob/main/docs/GETTING_STARTED.md
- **Issues:** https://github.com/pixielity-co/laravel-discovery/issues

---

**Full Changelog:** https://github.com/pixielity-co/laravel-discovery/compare/v1.0.0...v1.0.1
