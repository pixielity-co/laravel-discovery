# API Documentation

Complete API reference for the Discovery package.

## Table of Contents

- [DiscoveryManager](#discoverymanager)
- [DiscoveryBuilder](#discoverybuilder)
- [Strategies](#strategies)
- [Filters](#filters)
- [Validators](#validators)
- [Cache Manager](#cache-manager)

---

## DiscoveryManager

Main service class for discovery operations.

### Methods

#### `attribute(string $attributeClass): DiscoveryBuilder`

Discover classes by attribute.

**Parameters:**

- `$attributeClass` - Fully qualified attribute class name

**Returns:** `DiscoveryBuilder`

**Example:**

```php
Discovery::attribute(AsCard::class)->get();
```

---

#### `directories(string|array $directories): DiscoveryBuilder`

Discover classes in directories.

**Parameters:**

- `$directories` - Directory path(s), supports glob patterns

**Returns:** `DiscoveryBuilder`

**Example:**

```php
Discovery::directories('packages/*/src/Settings')->get();
```

---

#### `implementing(string $interface): DiscoveryBuilder`

Discover classes implementing an interface.

**Parameters:**

- `$interface` - Fully qualified interface name

**Returns:** `DiscoveryBuilder`

**Example:**

```php
Discovery::implementing(HealthCheckInterface::class)->get();
```

---

#### `extending(string $parentClass): DiscoveryBuilder`

Discover classes extending a parent class.

**Parameters:**

- `$parentClass` - Fully qualified parent class name

**Returns:** `DiscoveryBuilder`

**Example:**

```php
Discovery::extending(Command::class)->get();
```

---

#### `methods(string $attributeClass): DiscoveryBuilder`

Discover methods with an attribute.

**Parameters:**

- `$attributeClass` - Fully qualified attribute class name

**Returns:** `DiscoveryBuilder`

**Example:**

```php
Discovery::methods(Route::class)->get();
```

---

#### `properties(string $attributeClass): DiscoveryBuilder`

Discover properties with an attribute.

**Parameters:**

- `$attributeClass` - Fully qualified attribute class name

**Returns:** `DiscoveryBuilder`

**Example:**

```php
Discovery::properties(Validate::class)->get();
```

---

#### `clearCache(?string $key = null): void`

Clear discovery caches.

**Parameters:**

- `$key` - Specific cache key, or null to clear all

**Example:**

```php
Discovery::clearCache('settings');
```

---

#### `finder(string|array $directories): Finder`

Get Symfony Finder instance for advanced discovery.

**Parameters:**

- `$directories` - Directory path(s)

**Returns:** `Symfony\Component\Finder\Finder`

**Example:**

```php
$finder = Discovery::finder('packages/*/src')
    ->name('*.php')
    ->date('since yesterday');
```

---

## DiscoveryBuilder

Fluent builder for configuring discovery operations.

### Methods

#### `where(string $property, mixed $operator, mixed $value = null): self`

Filter by attribute property value.

**Parameters:**

- `$property` - Property name
- `$operator` - Comparison operator or value
- `$value` - Value to compare (optional)

**Operators:**

- `=`, `==` - Exact match
- `!=`, `<>` - Not equal
- `>`, `>=`, `<`, `<=` - Comparison
- `contains` - Array contains
- `in` - Value in array

**Example:**

```php
Discovery::attribute(AsCard::class)
    ->where('enabled', true)
    ->where('priority', '>', 5)
    ->get();
```

---

#### `filter(callable $callback): self`

Add custom filter callback.

**Parameters:**

- `$callback` - Filter function `fn($class, $metadata) => bool`

**Example:**

```php
Discovery::attribute(AsCard::class)
    ->filter(fn($class, $meta) => $meta['attribute']->priority > 5)
    ->get();
```

---

#### `instantiable(): self`

Filter to only instantiable classes.

**Example:**

```php
Discovery::implementing(ServiceInterface::class)
    ->instantiable()
    ->get();
```

---

#### `extends(string $parentClass): self`

Validate parent class.

**Parameters:**

- `$parentClass` - Fully qualified parent class name

**Example:**

```php
Discovery::directories('app/Commands')
    ->extends(Command::class)
    ->get();
```

---

#### `implements(string $interface): self`

Validate interface implementation.

**Parameters:**

- `$interface` - Fully qualified interface name

**Example:**

```php
Discovery::directories('app/Services')
    ->implements(ServiceInterface::class)
    ->get();
```

---

#### `cached(string $key): self`

Enable caching with key.

**Parameters:**

- `$key` - Cache key

**Example:**

```php
Discovery::attribute(AsCard::class)
    ->cached('cards')
    ->get();
```

---

#### `get(): array`

Execute discovery and return results.

**Returns:** `array<string, array>` - Associative array of class/method/property => metadata

**Example:**

```php
$results = Discovery::attribute(AsCard::class)->get();
// [
//     'App\Cards\DashboardCard' => [
//         'attribute' => AsCard instance,
//         'class' => 'App\Cards\DashboardCard',
//         ...
//     ]
// ]
```

---

## Strategies

### AttributeStrategy

Discovers classes by PHP attributes using composer-attribute-collector.

**Used by:** `Discovery::attribute()`

---

### DirectoryStrategy

Scans directories for PHP files and resolves to class names.

**Used by:** `Discovery::directories()`

---

### InterfaceStrategy

Finds classes implementing a specific interface.

**Used by:** `Discovery::implementing()`

---

### ParentClassStrategy

Finds classes extending a specific parent class.

**Used by:** `Discovery::extending()`

---

### MethodStrategy

Discovers methods with specific attributes.

**Used by:** `Discovery::methods()`

---

### PropertyStrategy

Discovers properties with specific attributes.

**Used by:** `Discovery::properties()`

---

## Filters

### PropertyFilter

Filters by attribute property values.

**Operators:**

- `=`, `==` - Exact match
- `!=`, `<>` - Not equal
- `>`, `>=`, `<`, `<=` - Comparison
- `contains` - Array contains value
- `in` - Value in array

---

### CallbackFilter

Custom filtering logic via callback function.

---

## Validators

### InstantiableValidator

Validates that a class can be instantiated (not abstract, not interface).

---

### ExtendsValidator

Validates that a class extends a specific parent class.

---

### ImplementsValidator

Validates that a class implements a specific interface.

---

## Cache Manager

Manages file-based caching of discovery results.

### Configuration

```php
'cache' => [
    'enabled' => env('DISCOVERY_CACHE_ENABLED', true),
    'path' => base_path('bootstrap/cache/discovery'),
    'ttl' => null,  // Forever
],
```

### Methods

#### `get(string $key): ?array`

Get cached discovery results.

---

#### `put(string $key, array $data): void`

Store discovery results in cache.

---

#### `clear(?string $key = null): void`

Clear cache (specific key or all).

---

## Return Format

All discovery methods return an associative array:

```php
[
    'identifier' => [
        'attribute' => AttributeInstance,  // The attribute instance
        'class' => 'Full\Class\Name',      // For class discovery
        'method' => 'methodName',          // For method discovery
        'property' => 'propertyName',      // For property discovery
        'file' => '/path/to/file.php',     // File path
        'line' => 42,                      // Line number (for methods/properties)
        // ... additional metadata
    ]
]
```

### Class Discovery

```php
$classes = Discovery::attribute(AsCard::class)->get();
// [
//     'App\Cards\DashboardCard' => [
//         'attribute' => AsCard { enabled: true, priority: 10 },
//         'class' => 'App\Cards\DashboardCard',
//         'file' => '/path/to/DashboardCard.php',
//     ]
// ]
```

### Method Discovery

```php
$methods = Discovery::methods(Route::class)->get();
// [
//     'App\Controllers\UserController::index' => [
//         'attribute' => Route { method: 'GET', path: '/users' },
//         'class' => 'App\Controllers\UserController',
//         'method' => 'index',
//         'file' => '/path/to/UserController.php',
//         'line' => 25,
//     ]
// ]
```

### Property Discovery

```php
$properties = Discovery::properties(Validate::class)->get();
// [
//     'App\Models\User::$email' => [
//         'attribute' => Validate { required: true, email: true },
//         'class' => 'App\Models\User',
//         'property' => 'email',
//         'file' => '/path/to/User.php',
//         'line' => 15,
//     ]
// ]
```
