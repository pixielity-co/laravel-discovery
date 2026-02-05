# Contributing to Laravel Discovery

Thank you for considering contributing to Laravel Discovery! This document outlines the process for contributing to this project.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Making Changes](#making-changes)
- [Testing](#testing)
- [Code Quality](#code-quality)
- [Submitting Changes](#submitting-changes)
- [Release Process](#release-process)

## Code of Conduct

This project adheres to a code of conduct. By participating, you are expected to uphold this code. Please report unacceptable behavior to dev@pixielity.com.

## Getting Started

1. **Fork the repository** on GitHub
2. **Clone your fork** locally
3. **Create a branch** for your changes
4. **Make your changes** following our guidelines
5. **Test your changes** thoroughly
6. **Submit a pull request**

## Development Setup

### Prerequisites

- PHP 8.3 or higher
- Composer 2.0 or higher
- Git

### Installation

```bash
# Clone your fork
git clone https://github.com/YOUR_USERNAME/laravel-discovery.git
cd laravel-discovery

# Install dependencies
composer install

# Run tests to ensure everything works
composer test
```

### Project Structure

```
laravel-discovery/
├── src/                    # Source code
│   ├── Cache/             # Caching system
│   ├── Strategies/        # Discovery strategies
│   ├── Filters/           # Filtering logic
│   ├── Validators/        # Validation logic
│   └── ...
├── tests/                 # Test suite
│   ├── Unit/             # Unit tests
│   ├── Feature/          # Feature tests
│   └── Fixtures/         # Test fixtures
├── docs/                  # Documentation
└── config/               # Configuration files
```

## Making Changes

### Branching Strategy

- `main` - Stable production code
- `develop` - Development branch
- `feature/*` - New features
- `bugfix/*` - Bug fixes
- `hotfix/*` - Critical production fixes

### Branch Naming

```bash
# Features
git checkout -b feature/add-new-strategy

# Bug fixes
git checkout -b bugfix/fix-cache-issue

# Hotfixes
git checkout -b hotfix/security-patch
```

### Commit Messages

Follow the [Conventional Commits](https://www.conventionalcommits.org/) specification:

```
<type>(<scope>): <subject>

<body>

<footer>
```

**Types:**

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks
- `perf`: Performance improvements

**Examples:**

```bash
feat(cache): add Redis cache driver support

Add support for Redis as a cache backend alongside the existing
file-based cache system.

Closes #123
```

```bash
fix(strategies): resolve namespace resolution in monorepos

Fix issue where namespace resolution failed for deeply nested
monorepo structures.

Fixes #456
```

## Testing

### Running Tests

```bash
# Run all tests
composer test

# Run specific test suite
vendor/bin/phpunit --testsuite=Unit
vendor/bin/phpunit --testsuite=Feature

# Run with coverage
composer test-coverage
```

### Writing Tests

- Write tests for all new features
- Ensure existing tests pass
- Aim for high test coverage (>80%)
- Use descriptive test names

**Example:**

```php
<?php

namespace Pixielity\Discovery\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Pixielity\Discovery\DiscoveryBuilder;

class DiscoveryBuilderTest extends TestCase
{
    /** @test */
    public function it_can_discover_classes_by_attribute(): void
    {
        $builder = new DiscoveryBuilder();

        $result = $builder->attribute(MyAttribute::class)->get();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }
}
```

## Code Quality

### Code Style

We use Laravel Pint for code formatting:

```bash
# Check code style
composer format:check

# Fix code style
composer format
```

### Static Analysis

We use PHPStan at level 8:

```bash
# Run static analysis
composer analyse
```

### Quality Checklist

Before submitting a PR, ensure:

- [ ] Code follows PSR-12 standards
- [ ] All tests pass
- [ ] PHPStan analysis passes (level 8)
- [ ] Code is properly documented
- [ ] No unnecessary dependencies added
- [ ] Backward compatibility maintained (unless breaking change)

## Submitting Changes

### Pull Request Process

1. **Update your branch** with the latest changes from `develop`

```bash
git checkout develop
git pull upstream develop
git checkout your-branch
git rebase develop
```

2. **Push your changes** to your fork

```bash
git push origin your-branch
```

3. **Create a Pull Request** on GitHub
    - Use a clear, descriptive title
    - Fill out the PR template completely
    - Link related issues
    - Add screenshots if applicable

4. **Address review feedback**
    - Respond to all comments
    - Make requested changes
    - Push updates to your branch

5. **Wait for approval**
    - At least one maintainer approval required
    - All CI checks must pass

### PR Requirements

- [ ] PR title follows conventional commits format
- [ ] Description clearly explains the changes
- [ ] Tests added/updated for changes
- [ ] Documentation updated if needed
- [ ] CHANGELOG.md updated (for significant changes)
- [ ] No merge conflicts
- [ ] All CI checks passing

## Release Process

Releases are managed by maintainers:

1. Update `CHANGELOG.md`
2. Update version in `composer.json`
3. Create a git tag: `v1.2.3`
4. Push tag to trigger release workflow
5. GitHub Actions creates the release
6. Packagist is automatically updated

### Versioning

We follow [Semantic Versioning](https://semver.org/):

- **MAJOR** version for incompatible API changes
- **MINOR** version for backward-compatible functionality
- **PATCH** version for backward-compatible bug fixes

## Questions?

- **Documentation**: Check the [docs](./docs) folder
- **Issues**: Search [existing issues](https://github.com/pixielity-co/laravel-discovery/issues)
- **Discussions**: Start a [discussion](https://github.com/pixielity-co/laravel-discovery/discussions)
- **Email**: dev@pixielity.com

## Recognition

Contributors will be recognized in:

- `CHANGELOG.md` for their contributions
- GitHub contributors page
- Release notes

Thank you for contributing! 🎉
