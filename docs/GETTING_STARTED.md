# Getting Started with Discovery

A step-by-step guide to get you up and running with the Discovery package.

## Table of Contents

- [Installation](#installation)
- [Basic Concepts](#basic-concepts)
- [Your First Discovery](#your-first-discovery)
- [Common Patterns](#common-patterns)
- [Next Steps](#next-steps)

## Installation

### Step 1: Install via Composer

```bash
composer require fulers/discovery
```

### Step 2: Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=discovery-config
```

This creates `config/discovery.php` where you can customize settings.

### Step 3: Verify Installation

```bash
php artisan tinker
```

```php
>>> Discovery::attribute(\Attribute::class)->get();
=> []  // Empty array is expected if no classes have this attribute
```

## Basic Concepts

### What is Discovery?

Discovery helps you find classes, methods, and properties in your application based on:

- PHP 8 Attributes
- Directory locations
- Interface implementations
- Parent class extensions

### Why Use Discovery?

**Without Discovery:**

```php
// Manual registration - tedious
$this->app->singleton(SettingA::class);
$this->app->singleton(SettingB::class);
$this->app->singleton(SettingC::class);
// ... 50 more settings
```

**With Discovery:**

```php
// Automatic discovery - clean
$settings = Discovery::attribute(AsSetting::class)->get();
foreach ($settings as $class => $metadata) {
    $this->app->singleton($class);
}
```

## Your First Discovery

### Step 1: Create an Attribute

```php
// app/Attributes/AsCard.php
<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class AsCard
{
    public function __construct(
        public string $title,
        public bool $enabled = true,
        public int $priority = 0,
    ) {}
}
```

### Step 2: Create a Class with the Attribute

```php
// app/Cards/DashboardCard.php
<?php

namespace App\Cards;

use App\Attributes\AsCard;

#[AsCard(title: 'Dashboard Overview', enabled: true, priority: 10)]
class DashboardCard
{
    public function render(): string
    {
        return 'Dashboard content...';
    }
}
```

### Step 3: Discover the Class

```php
use Pixielity\Discovery\Facades\Discovery;

$cards = Discovery::attribute(AsCard::class)->get();

foreach ($cards as $class => $metadata) {
    echo "Found: {$class}\n";
    echo "Title: {$metadata['attribute']->title}\n";
    echo "Priority: {$metadata['attribute']->priority}\n";
}
```

**Output:**

```
Found: App\Cards\DashboardCard
Title: Dashboard Overview
Priority: 10
```

### Step 4: Add Filtering

```php
// Only enabled cards with priority > 5
$cards = Discovery::attribute(AsCard::class)
    ->where('enabled', true)
    ->where('priority', '>', 5)
    ->get();
```

### Step 5: Add Caching

```php
// Cache for better performance
$cards = Discovery::attribute(AsCard::class)
    ->where('enabled', true)
    ->cached('dashboard-cards')
    ->get();
```

## Common Patterns

### Pattern 1: Auto-Registration

```php
// In a service provider
public function register(): void
{
    $services = Discovery::attribute(AsService::class)
        ->instantiable()
        ->cached('services')
        ->get();

    foreach ($services as $class => $metadata) {
        $this->app->singleton($class);
    }
}
```

### Pattern 2: Route Discovery

```php
// In RouteServiceProvider
public function boot(): void
{
    $routes = Discovery::methods(Route::class)
        ->cached('routes')
        ->get();

    foreach ($routes as $identifier => $metadata) {
        [$class, $method] = explode('::', $identifier);
        $route = $metadata['attribute'];

        Route::{strtolower($route->method)}($route->path, [$class, $method])
            ->name($route->name);
    }
}
```

### Pattern 3: Plugin System

```php
public function loadPlugins(): void
{
    $plugins = Discovery::attribute(Plugin::class)
        ->implementing(PluginInterface::class)
        ->where('enabled', true)
        ->instantiable()
        ->cached('plugins')
        ->get();

    foreach ($plugins as $class => $metadata) {
        $plugin = app($class);
        $plugin->boot();
    }
}
```

### Pattern 4: Health Checks

```php
public function runHealthChecks(): array
{
    $checks = Discovery::attribute(HealthCheck::class)
        ->implementing(HealthCheckInterface::class)
        ->instantiable()
        ->cached('health-checks')
        ->get();

    $results = [];
    foreach ($checks as $class => $metadata) {
        $check = app($class);
        $results[] = $check->check();
    }

    return $results;
}
```

## Next Steps

### Learn More

- [API Reference](API.md) - Complete API documentation
- [Advanced Usage](ADVANCED_USAGE.md) - Advanced patterns and techniques
- [Performance Guide](PERFORMANCE.md) - Optimization tips
- [Examples](../README.md#-real-world-examples) - Real-world use cases

### Best Practices

1. **Always cache in production**

    ```php
    ->cached('my-cache-key')
    ```

2. **Use specific discovery methods**

    ```php
    // ✅ Fast
    Discovery::attribute(AsCard::class)

    // ⚠️ Slower
    Discovery::directories('app')
    ```

3. **Filter early**

    ```php
    ->where('enabled', true)
    ->instantiable()
    ```

4. **Clear cache after deployment**
    ```bash
    php artisan cache:clear
    ```

### Common Issues

**Issue: Classes not discovered**

- Run `composer dump-autoload`
- Check attribute is properly defined
- Verify class is in autoload paths

**Issue: Slow performance**

- Enable caching with `->cached('key')`
- Use attribute discovery instead of directory scanning
- Scope directory scans to specific paths

**Issue: Cache not clearing**

- Run `php artisan cache:clear`
- Check cache path permissions
- Verify `DISCOVERY_CACHE_ENABLED` is set correctly

### Get Help

- 💬 [GitHub Discussions](https://github.com/fulers/discovery/discussions)
- 🐛 [Report Issues](https://github.com/fulers/discovery/issues)
- 📧 [Email Support](mailto:support@fulers.com)

---

**Ready to dive deeper?** Check out the [Advanced Usage Guide](ADVANCED_USAGE.md)!
