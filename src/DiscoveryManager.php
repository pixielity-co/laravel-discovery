<?php

namespace Fulers\Discovery;

use Fulers\Discovery\Contracts\CacheManagerInterface;
use Fulers\Discovery\Contracts\DiscoveryManagerInterface;
use Fulers\Discovery\Contracts\StrategyFactoryInterface;
use Fulers\Support\Arr;
use Fulers\Support\Str;
use Symfony\Component\Finder\Finder;

/**
 * DiscoveryManager - Main service class for class discovery operations.
 *
 * This class provides the core discovery functionality with proper dependency injection.
 * It acts as a factory for creating DiscoveryBuilder instances configured with different
 * discovery strategies (attribute, directory, interface, parent class).
 *
 * All strategies are created through the injected StrategyFactory, ensuring proper
 * dependency injection throughout the discovery process.
 *
 * The manager is registered as a singleton in the service container and should be
 * accessed through the Discovery facade for a cleaner API, or injected directly
 * into classes that need discovery functionality.
 *
 * Example usage:
 * ```php
 * // Via facade
 * Discovery::attribute(AsCard::class)->get();
 *
 * // Via dependency injection
 * public function __construct(DiscoveryManagerInterface $discovery) {
 *     $this->discovery = $discovery;
 * }
 * ```
 *
 * @see Facades\Discovery
 */
class DiscoveryManager implements DiscoveryManagerInterface
{
    /**
     * Create a new DiscoveryManager instance.
     *
     * This constructor uses dependency injection to receive all required services.
     * The Laravel service container automatically resolves and injects these
     * dependencies when the manager is instantiated.
     *
     * @param CacheManagerInterface    $cacheManager    Cache manager for discovery results
     * @param StrategyFactoryInterface $strategyFactory Factory for creating strategies
     */
    public function __construct(
        protected CacheManagerInterface $cacheManager,
        protected StrategyFactoryInterface $strategyFactory
    ) {}

    /**
     * Discover classes by attribute.
     *
     * Uses the composer-attribute-collector package to find all classes
     * decorated with the specified attribute. This is the most performant
     * discovery method as it leverages Composer's built-in caching.
     *
     * The attribute strategy is created through the injected StrategyFactory,
     * ensuring proper dependency injection. The builder is then returned for
     * further chaining of filters and options.
     *
     * @param  string           $attributeClass Fully qualified attribute class name
     * @return DiscoveryBuilder Fluent builder for chaining filters and options
     *
     * @example
     * ```php
     * // Discover all classes with the AsCard attribute
     * Discovery::attribute(AsCard::class)
     *     ->where('enabled', true)
     *     ->get();
     * ```
     */
    public function attribute(string $attributeClass): DiscoveryBuilder
    {
        // Create the AttributeStrategy through the factory with proper DI
        $discoveryStrategy = $this->strategyFactory->createAttributeStrategy($attributeClass);

        // Create a fresh DiscoveryBuilder instance and configure it
        return resolve(DiscoveryBuilder::class)->setStrategy($discoveryStrategy);
    }

    /**
     * Discover classes in directories.
     *
     * Scans the specified directories for PHP files and resolves them to
     * fully qualified class names. Supports glob patterns for monorepo
     * structures (e.g., 'packages/*src/Settings').
     *
     * The directory strategy is created through the injected StrategyFactory,
     * ensuring proper dependency injection. Glob patterns are automatically
     * expanded to match multiple directories.
     *
     * @param  string|array<string> $directories Directory path(s) to scan, supports glob patterns
     * @return DiscoveryBuilder     Fluent builder for chaining filters and options
     *
     * @example
     * ```php
     * // Discover settings classes across packages, modules, and app
     * Discovery::directories([
     *     'packages/*src/Settings',
     *     'modules/*src/Settings',
     *     'app/Settings',
     * ])
     * ->extending(Settings::class)
     * ->cached('settings')
     * ->get();
     * ```
     */
    public function directories(string|array $directories): DiscoveryBuilder
    {
        // Create the DirectoryStrategy through the factory with proper DI
        $discoveryStrategy = $this->strategyFactory->createDirectoryStrategy($directories);

        // Create a fresh DiscoveryBuilder instance and configure it
        return resolve(DiscoveryBuilder::class)->setStrategy($discoveryStrategy);
    }

    /**
     * Discover classes implementing an interface.
     *
     * Finds all classes that implement the specified interface. Can be combined
     * with directory scanning for better performance, otherwise scans all
     * declared classes in memory.
     *
     * The interface strategy is created through the injected StrategyFactory,
     * ensuring proper dependency injection.
     *
     * @param  string           $interface Fully qualified interface name
     * @return DiscoveryBuilder Fluent builder for chaining filters and options
     *
     * @example
     * ```php
     * // Discover all health check implementations
     * Discovery::implementing(HealthCheckInterface::class)
     *     ->directories('packages/*src/Health')
     *     ->instantiable()
     *     ->get();
     * ```
     */
    public function implementing(string $interface): DiscoveryBuilder
    {
        // Create the InterfaceStrategy through the factory with proper DI
        $discoveryStrategy = $this->strategyFactory->createInterfaceStrategy($interface);

        // Create a fresh DiscoveryBuilder instance and configure it
        return resolve(DiscoveryBuilder::class)->setStrategy($discoveryStrategy);
    }

    /**
     * Discover classes extending a parent class.
     *
     * Finds all classes that extend the specified parent class. Can be combined
     * with directory scanning for better performance, otherwise scans all
     * declared classes in memory.
     *
     * The parent class strategy is created through the injected StrategyFactory,
     * ensuring proper dependency injection.
     *
     * @param  string           $parentClass Fully qualified parent class name
     * @return DiscoveryBuilder Fluent builder for chaining filters and options
     *
     * @example
     * ```php
     * // Discover all Artisan commands
     * Discovery::extending(Command::class)
     *     ->directories('packages/*src/Console/Commands')
     *     ->get();
     * ```
     */
    public function extending(string $parentClass): DiscoveryBuilder
    {
        // Create the ParentClassStrategy through the factory with proper DI
        $discoveryStrategy = $this->strategyFactory->createParentClassStrategy($parentClass);

        // Create a fresh DiscoveryBuilder instance and configure it
        return resolve(DiscoveryBuilder::class)->setStrategy($discoveryStrategy);
    }

    /**
     * Clear discovery caches.
     *
     * Clears cached discovery results. If a key is provided, only that specific
     * cache is cleared. Otherwise, all discovery caches are cleared.
     *
     * This is useful for:
     * - Development: Clear cache after adding new classes
     * - Deployment: Clear cache to force fresh discovery
     * - Testing: Ensure clean state between tests
     *
     * Note: This does not clear the composer-attribute-collector cache.
     * Use `composer dump-autoload` to rebuild attribute caches.
     *
     * @param string|null $key Specific cache key to clear, or null to clear all
     *
     * @example
     * ```php
     * // Clear specific cache
     * Discovery::clearCache('settings');
     *
     * // Clear all caches
     * Discovery::clearCache();
     * ```
     */
    public function clearCache(?string $key = null): void
    {
        // Delegate to the cache manager to handle the actual clearing
        $this->cacheManager->clear($key);
    }

    /**
     * Discover methods decorated with an attribute.
     *
     * Uses the composer-attribute-collector package to find all methods
     * decorated with the specified attribute. This is very fast as it uses
     * Composer's cached attribute data.
     *
     * The method strategy is created through the injected StrategyFactory,
     * ensuring proper dependency injection. The builder is then returned for
     * further chaining of filters and options.
     *
     * @param  string           $attributeClass Fully qualified attribute class name
     * @return DiscoveryBuilder Fluent builder for chaining filters and options
     *
     * @example
     * ```php
     * // Discover all methods with the Route attribute
     * Discovery::methods(Route::class)
     *     ->where('method', 'GET')
     *     ->cached('routes')
     *     ->get();
     * ```
     */
    public function methods(string $attributeClass): DiscoveryBuilder
    {
        // Create the MethodStrategy through the factory with proper DI
        $discoveryStrategy = $this->strategyFactory->createMethodStrategy($attributeClass);

        // Create a fresh DiscoveryBuilder instance and configure it
        return resolve(DiscoveryBuilder::class)->setStrategy($discoveryStrategy);
    }

    /**
     * Discover properties decorated with an attribute.
     *
     * Uses the composer-attribute-collector package to find all properties
     * decorated with the specified attribute. This is very fast as it uses
     * Composer's cached attribute data.
     *
     * The property strategy is created through the injected StrategyFactory,
     * ensuring proper dependency injection. The builder is then returned for
     * further chaining of filters and options.
     *
     * @param  string           $attributeClass Fully qualified attribute class name
     * @return DiscoveryBuilder Fluent builder for chaining filters and options
     *
     * @example
     * ```php
     * // Discover all properties with the Validate attribute
     * Discovery::properties(Validate::class)
     *     ->where('required', true)
     *     ->cached('validations')
     *     ->get();
     * ```
     */
    public function properties(string $attributeClass): DiscoveryBuilder
    {
        // Create the PropertyStrategy through the factory with proper DI
        $discoveryStrategy = $this->strategyFactory->createPropertyStrategy($attributeClass);

        // Create a fresh DiscoveryBuilder instance and configure it
        return resolve(DiscoveryBuilder::class)->setStrategy($discoveryStrategy);
    }

    /**
     * Get a Symfony Finder instance for advanced file discovery.
     *
     * This method provides direct access to Symfony Finder as an escape hatch
     * for advanced use cases that aren't covered by the standard discovery methods.
     *
     * Use this when you need:
     * - Custom file filtering beyond PHP classes
     * - Complex path patterns
     * - File content searching
     * - Date-based filtering
     * - Size-based filtering
     *
     * Note: When using finder(), you're responsible for:
     * - Converting file paths to class names
     * - Validating that classes exist
     * - Handling exceptions
     *
     * @param  string|array<string> $directories Directory path(s) to search
     * @return Finder               Finder instance for advanced filtering
     *
     * @example
     * ```php
     * // Find all PHP files modified in the last 24 hours
     * $finder = Discovery::finder('packages/*\/src')
     *     ->name('*.php')
     *     ->date('since yesterday')
     *     ->sortByName();
     *
     * foreach ($finder as $file) {
     *     // Custom processing...
     * }
     * ```
     * @example
     * ```php
     * // Find large configuration files
     * $finder = Discovery::finder(['config', 'packages/*\/config'])
     *     ->name('*.php')
     *     ->size('>= 10K')
     *     ->sortBySize();
     * ```
     */
    public function finder(string|array $directories): Finder
    {
        $directories = is_array($directories) ? $directories : [$directories];

        // Expand glob patterns and filter to existing directories
        $expandedDirs = [];
        foreach ($directories as $directory) {
            if (Str::contains($directory, '*')) {
                $matches = glob($directory, GLOB_ONLYDIR);
                $expandedDirs = Arr::merge($expandedDirs, $matches ?: []);
            } else {
                $expandedDirs[] = $directory;
            }
        }

        // Filter to only existing directories
        $expandedDirs = Arr::filter($expandedDirs, is_dir(...));

        // Create and return a new Finder instance
        $finder = new Finder();

        if ($expandedDirs !== []) {
            $finder->in($expandedDirs);
        }

        return $finder;
    }
}
