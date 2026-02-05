<?php

namespace Fulers\Discovery;

use Fulers\Discovery\Contracts\CacheManagerInterface;
use Fulers\Discovery\Contracts\DiscoveryStrategyInterface;
use Fulers\Discovery\Filters\CallbackFilter;
use Fulers\Discovery\Filters\PropertyFilter;
use Fulers\Discovery\Strategies\DirectoryStrategy;
use Fulers\Discovery\Validators\ExtendsValidator;
use Fulers\Discovery\Validators\ImplementsValidator;
use Fulers\Discovery\Validators\InstantiableValidator;
use Fulers\Support\Arr;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionException;

/**
 * DiscoveryBuilder - Fluent builder for configuring discovery operations.
 *
 * This class provides a chainable interface for building complex discovery queries
 * with filters, validators, and caching. It follows the Builder pattern to allow
 * flexible configuration of discovery operations.
 *
 * ## Features:
 * - **Filtering**: Filter discovered classes by attribute properties or custom callbacks
 * - **Validation**: Validate classes (instantiable, extends, implements)
 * - **Caching**: Cache discovery results for improved performance
 * - **Registration**: Register discovered classes with custom callbacks
 *
 * ## Usage Examples:
 *
 * ```php
 * // Simple discovery with caching
 * $classes = Discovery::attribute(AsCard::class)
 *     ->cached('pulse-cards')
 *     ->get();
 *
 * // Complex discovery with filters and validators
 * $classes = Discovery::directories('packages/*\/src/Commands')
 *     ->extending(Command::class)
 *     ->instantiable()
 *     ->where('enabled', true)
 *     ->cached('commands')
 *     ->get();
 *
 * // Register discovered classes
 * Discovery::attribute(ServiceProvider::class)
 *     ->register(function ($class, $metadata) {
 *         app()->register($class);
 *     });
 * ```
 */
class DiscoveryBuilder
{
    /**
     * The discovery strategy to use for finding classes.
     *
     * This strategy determines how classes are discovered (by attribute,
     * directory, interface, or parent class).
     */
    protected DiscoveryStrategyInterface $strategy;

    /**
     * Array of filters to apply to discovered classes.
     *
     * Filters are applied before validators and can filter based on
     * attribute properties or custom logic.
     *
     * @var array<int, Contracts\FilterInterface>
     */
    protected array $filters = [];

    /**
     * Array of validators to apply to discovered classes.
     *
     * Validators check if classes meet specific criteria (instantiable,
     * extends a parent, implements an interface).
     *
     * @var array<int, Contracts\ValidatorInterface>
     */
    protected array $validators = [];

    /**
     * Optional cache key for storing discovery results.
     *
     * When set, discovery results will be cached and retrieved from cache
     * on subsequent calls.
     */
    protected ?string $cacheKey = null;

    /**
     * Create a new DiscoveryBuilder instance.
     *
     * The cache manager is injected to handle caching of discovery results.
     * This allows for improved performance by avoiding repeated filesystem scans.
     *
     * @param CacheManagerInterface $cacheManager Cache manager for discovery results
     */
    public function __construct(
        protected CacheManagerInterface $cacheManager
    ) {}

    /**
     * Set the discovery strategy.
     *
     * This method is typically called by the DiscoveryManager when creating
     * a builder instance. It sets the strategy that will be used to discover classes.
     *
     * @param  DiscoveryStrategyInterface $discoveryStrategy Discovery strategy to use
     * @return $this                      Fluent interface
     */
    public function setStrategy(DiscoveryStrategyInterface $discoveryStrategy): self
    {
        $this->strategy = $discoveryStrategy;

        return $this;
    }

    /**
     * Filter by attribute property value.
     *
     * Filters discovered classes based on a property value in their attribute.
     * Only works with attribute-based discovery strategies.
     *
     * @param  string $property Property name to check
     * @param  mixed  $value    Expected value
     * @return $this  Fluent interface
     *
     * @example
     * ```php
     * Discovery::attribute(AsCard::class)
     *     ->where('enabled', true)
     *     ->get();
     * ```
     */
    public function where(string $property, mixed $value): self
    {
        $this->filters[] = new PropertyFilter($property, $value);

        return $this;
    }

    /**
     * Filter with custom callback.
     *
     * Applies a custom filter function to discovered classes. The callback
     * receives the class name and should return true to keep the class.
     *
     * @param  callable(class-string): bool $callback Filter function
     * @return $this                        Fluent interface
     *
     * @example
     * ```php
     * Discovery::directories('packages/*\/src')
     *     ->filter(fn($class) => Str::contains($class, 'Service'))
     *     ->get();
     * ```
     */
    public function filter(callable $callback): self
    {
        $this->filters[] = new CallbackFilter($callback);

        return $this;
    }

    /**
     * Only include classes extending a parent.
     *
     * Validates that discovered classes extend the specified parent class.
     * This is useful for ensuring type safety when discovering classes.
     *
     * @param  class-string $parentClass Parent class name
     * @return $this        Fluent interface
     *
     * @example
     * ```php
     * Discovery::directories('packages/*\/src/Commands')
     *     ->extending(Command::class)
     *     ->get();
     * ```
     */
    public function extending(string $parentClass): self
    {
        $this->validators[] = new ExtendsValidator($parentClass);

        return $this;
    }

    /**
     * Only include classes implementing an interface.
     *
     * Validates that discovered classes implement the specified interface.
     * This is useful for ensuring type safety when discovering classes.
     *
     * @param  class-string $interface Interface name
     * @return $this        Fluent interface
     *
     * @example
     * ```php
     * Discovery::directories('packages/*\/src/Health')
     *     ->implementing(HealthCheckInterface::class)
     *     ->get();
     * ```
     */
    public function implementing(string $interface): self
    {
        $this->validators[] = new ImplementsValidator($interface);

        return $this;
    }

    /**
     * Only include classes implementing an interface (alias for implementing).
     *
     * Validates that discovered classes implement the specified interface.
     * This is a shorter alias for the implementing() method.
     *
     * @param  class-string $interface Interface name
     * @return $this        Fluent interface
     *
     * @example
     * ```php
     * Discovery::attribute(SentryContext::class)
     *     ->implements(SentryContextInterface::class)
     *     ->get();
     * ```
     */
    public function implements(string $interface): self
    {
        return $this->implementing($interface);
    }

    /**
     * Only include instantiable classes.
     *
     * Validates that discovered classes can be instantiated (not abstract or interface).
     * This is useful when you need to create instances of discovered classes.
     *
     * @return $this Fluent interface
     *
     * @example
     * ```php
     * Discovery::attribute(ServiceProvider::class)
     *     ->instantiable()
     *     ->get();
     * ```
     */
    public function instantiable(): self
    {
        $this->validators[] = new InstantiableValidator();

        return $this;
    }

    /**
     * Specify directories to scan (for directory strategy).
     *
     * This method only works when using a DirectoryStrategy. It allows
     * changing the directories to scan after the strategy is created.
     *
     * @param  string|array<string> $directories Directory path(s) to scan
     * @return $this                Fluent interface
     */
    public function directories(string|array $directories): self
    {
        if ($this->strategy instanceof DirectoryStrategy) {
            $this->strategy->setDirectories($directories);
        }

        return $this;
    }

    /**
     * Set custom namespace pattern.
     *
     * This method only works when using a DirectoryStrategy. It allows
     * customizing how file paths are converted to class names.
     *
     * @param  string $pattern Pattern with placeholders ({package}, {module}, {class}, {namespace})
     * @return $this  Fluent interface
     *
     * @example
     * ```php
     * Discovery::directories('packages/*\/src')
     *     ->withNamespacePattern('Fulers\{package}\{namespace}\{class}')
     *     ->get();
     * ```
     */
    public function withNamespacePattern(string $pattern): self
    {
        if ($this->strategy instanceof DirectoryStrategy) {
            $this->strategy->setNamespacePattern($pattern);
        }

        return $this;
    }

    /**
     * Enable caching with optional key.
     *
     * Enables caching of discovery results. If no key is provided, a key
     * is generated from the strategy's cache key.
     *
     * @param  string|null $key Cache key (auto-generated if null)
     * @return $this       Fluent interface
     *
     * @example
     * ```php
     * Discovery::attribute(AsCard::class)
     *     ->cached('pulse-cards')
     *     ->get();
     * ```
     */
    public function cached(?string $key = null): self
    {
        $this->cacheKey = $key ?? $this->strategy->getCacheKey();

        return $this;
    }

    /**
     * Register discovered classes with callback.
     *
     * Discovers classes and executes a callback for each one. The callback
     * receives the class name and metadata. Returns the array of discovered classes.
     *
     * @param  callable(class-string, array<string, mixed>): void $callback Registration function
     * @return array<int, class-string>                           Registered class names
     *
     * @example
     * ```php
     * Discovery::attribute(ServiceProvider::class)
     *     ->register(function ($class, $metadata) {
     *         app()->register($class);
     *     });
     * ```
     */
    public function register(callable $callback): array
    {
        $classes = $this->get();

        foreach ($classes as $class) {
            $callback($class, $this->strategy->getMetadata($class));
        }

        return $classes->all();
    }

    /**
     * Get discovered classes with metadata.
     *
     * Executes the discovery process and returns a collection of classes with metadata.
     * If caching is enabled, results are retrieved from cache or stored
     * in cache after discovery.
     *
     * @return Collection<class-string, array<string, mixed>> Collection of classes with metadata
     */
    public function get(): Collection
    {
        // Check cache first
        if ($this->cacheKey !== null) {
            $cached = $this->cacheManager->get($this->cacheKey);
            if ($cached !== null) {
                $classes = $this->applyFiltersAndValidators($cached);

                return $this->buildMetadataCollection($classes);
            }
        }

        // Discover classes
        $classes = $this->strategy->discover();

        // Apply filters and validators
        $classes = $this->applyFiltersAndValidators($classes);

        // Cache results
        if ($this->cacheKey !== null) {
            $this->cacheManager->put($this->cacheKey, $classes);
        }

        return $this->buildMetadataCollection($classes);
    }

    /**
     * Get only class names without metadata.
     *
     * Returns a simple collection of class names without metadata.
     * Useful when you only need the class names.
     *
     * @return Collection<int, class-string> Collection of fully qualified class names
     */
    public function classes(): Collection
    {
        return $this->get()->keys();
    }

    /**
     * Get file paths of discovered classes.
     *
     * Returns the file paths where the discovered classes are located,
     * instead of the class names. Useful when you need directory paths
     * for external tools (like Spatie's route-attributes).
     *
     * For directory-based discovery, this returns the unique directory paths.
     * For other strategies, this returns the file paths of the discovered classes.
     *
     * @return Collection<int, string> Collection of file/directory paths
     *
     * @example
     * ```php
     * // Get controller directories for route-attributes
     * $dirs = Discovery::directories([
     *     'packages/*\/src/Controllers',
     *     'modules/*\/src/Controllers',
     * ])->paths()->unique()->all();
     * ```
     */
    public function paths(): Collection
    {
        // Get discovered class names
        $classes = $this->classes();

        // If using DirectoryStrategy, return the directories being scanned
        if ($this->strategy instanceof DirectoryStrategy) {
            return collect($this->strategy->getDirectories())
                ->filter(fn ($dir): bool => is_dir($dir))
                ->values();
        }

        // For other strategies, convert class names to file paths
        $paths = collect();
        foreach ($classes as $class) {
            try {
                $reflection = new ReflectionClass($class);
                $filename = $reflection->getFileName();
                if ($filename !== false) {
                    $paths->push($filename);
                }
            } catch (ReflectionException) {
                // Skip classes that can't be reflected
                continue;
            }
        }

        return $paths;
    }

    /**
     * Convert to array format (for config compatibility).
     *
     * Returns discovered classes as an associative array where keys are
     * class names and values are metadata arrays. Useful for configuration
     * files or serialization.
     *
     * @return array<class-string, array<string, mixed>> Associative array of classes with metadata
     *
     * @example
     * ```php
     * $config = Discovery::attribute(AsCard::class)->toArray();
     * // ['App\Pulse\Cards\MyCard' => ['class' => '...', 'attribute' => '...']]
     * ```
     */
    public function toArray(): array
    {
        return $this->get()->all();
    }

    /**
     * Build metadata collection from class names.
     *
     * Internal method that builds a collection with metadata for each class.
     *
     * @param  array<int, class-string>                       $classes Array of class names
     * @return Collection<class-string, array<string, mixed>> Collection with metadata
     */
    protected function buildMetadataCollection(array $classes): Collection
    {
        $result = [];

        foreach ($classes as $class) {
            $metadata = $this->strategy->getMetadata($class);
            $result[$class] = $metadata;
        }

        return collect($result);
    }

    /**
     * Apply all filters and validators.
     *
     * Internal method that applies filters first, then validators to the
     * discovered classes. Filters can modify the list, while validators
     * only remove classes that don't meet criteria.
     *
     * @param  array<int, class-string> $classes Classes to filter
     * @return array<int, class-string> Filtered classes
     */
    protected function applyFiltersAndValidators(array $classes): array
    {
        // Apply filters
        foreach ($this->filters as $filter) {
            $classes = $filter->apply($classes, $this->strategy);
        }

        // Apply validators
        foreach ($this->validators as $validator) {
            $classes = Arr::filter($classes, $validator->validate(...));
        }

        return Arr::values($classes);
    }
}
