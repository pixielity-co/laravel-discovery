# Contributing to Discovery Package

Thank you for considering contributing to the Discovery package! This document provides guidelines for contributing.

## Development Setup

1. Clone the repository
2. Install dependencies:

```bash
composer install
```

3. Run tests:

```bash
composer test
```

## Coding Standards

- Follow PSR-12 coding standards
- Use strict types: `declare(strict_types=1);`
- Add PHPDoc blocks for all public methods
- Use type hints for all parameters and return types
- Write descriptive commit messages

## Testing

- Write tests for all new features
- Ensure all tests pass before submitting PR
- Aim for high test coverage (>80%)

```bash
# Run tests
composer test

# Run tests with coverage
composer test-coverage

# Run static analysis
composer analyse
```

## Pull Request Process

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/my-feature`
3. Make your changes
4. Write/update tests
5. Ensure tests pass
6. Commit your changes: `git commit -am 'Add new feature'`
7. Push to the branch: `git push origin feature/my-feature`
8. Create a Pull Request

## Code Review

All submissions require review. We use GitHub pull requests for this purpose.

## Reporting Issues

- Use GitHub Issues
- Provide clear description
- Include code examples
- Specify PHP/Laravel versions
- Include stack traces for errors

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
