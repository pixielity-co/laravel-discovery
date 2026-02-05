# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.x     | :white_check_mark: |

## Reporting a Vulnerability

If you discover a security vulnerability within this package, please send an email to security@pixielity.com. All security vulnerabilities will be promptly addressed.

Please do not publicly disclose the issue until it has been addressed by the team.

## Security Considerations

### File System Access

The Discovery package scans the file system for PHP classes. Ensure that:

1. Discovery paths are properly configured and restricted
2. User input is never used directly in directory paths
3. Cache directory has appropriate permissions

### Class Loading

When using discovered classes:

1. Always validate class existence before instantiation
2. Use the `instantiable()` validator for classes you plan to instantiate
3. Be cautious with dynamic class instantiation
4. Validate interfaces/parent classes when required

### Cache Security

1. Cache files are stored in `bootstrap/cache/discovery` by default
2. Ensure this directory is not web-accessible
3. Cache files contain serialized data - protect from tampering
4. Clear cache after deploying security updates

## Best Practices

```php
// ✅ GOOD - Validate before use
$classes = Discovery::attribute(MyAttribute::class)
    ->instantiable()
    ->implementing(MyInterface::class)
    ->get();

// ❌ BAD - No validation
$classes = Discovery::directories($_GET['path'])->get();
```

## Disclosure Policy

We follow responsible disclosure practices and will:

1. Acknowledge receipt within 48 hours
2. Provide an estimated timeline for a fix
3. Notify you when the issue is resolved
4. Credit you in the security advisory (if desired)
