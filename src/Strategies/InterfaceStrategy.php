<?php

namespace Pixielity\Discovery\Strategies;

use Pixielity\Discovery\Contracts\DiscoveryStrategyInterface;
use Pixielity\Discovery\Contracts\StrategyFactoryInterface;
use Pixielity\Discovery\Support\Arr;
use Pixielity\Discovery\Support\Reflection;
use RuntimeException;
use Throwable;

/**
 * InterfaceStrategy - Discovers classes implementing an interface.
 *
 * This strategy finds all classes that implement a specified interface. It can operate
 * in two modes:
 *
 * 1. **Global Mode**: Scans all declared classes in memory using `get_declared_classes()`
 * 2. **Directory Mode**: Scans specific directories for better performance and precision
 *
 * The directory mode is recommended for production use as it's more efficient and doesn't
 * rely on classes being already loaded into memory.
 *
 * ## Usage Examples:
 *
 * ```php
 * // Global mode - scans all declared classes
 * $strategy = new InterfaceStrategy(HealthCheckInterface::class);
 * $classes = $strategy->discover();
 *
 * // Directory mode - scans specific directories
 * $strategy = new InterfaceStrategy(HealthCheckInterface::class, $strategyFactory);
 * $strategy->directories('packages/*\/src/Health');
 * $classes = $strategy->discover();
 * ```
 *
 * ## Performance Considerations:
 * - Global mode: Fast but requires classes to be autoloaded
 * - Directory mode: Slower initial scan but more reliable and doesn't require autoloading
 */
class InterfaceStrategy implements DiscoveryStrategyInterface
{
    /**
     * Optional directory strategy for scanning specific directories.
     *
     * When set, this strategy will scan directories instead of using
     * get_declared_classes(). This provides better performance and
     * doesn't require classes to be autoloaded.
     */
    protected ?DirectoryStrategy $directoryStrategy = null;

    /**
     * Create a new InterfaceStrategy instance.
     *
     * The strategy factory is optional. If not provided, the directories()
     * method will not be available and the strategy will only work in global mode.
     *
     * @param string                        $interface       Fully qualified interface name to search for
     * @param StrategyFactoryInterface|null $strategyFactory Optional factory for creating directory strategies
     */
    public function __construct(
        protected string $interface,
        protected ?StrategyFactoryInterface $strategyFactory = null
    ) {}

    /**
     * Set directories to scan for classes implementing the interface.
     *
     * This method switches the strategy from global mode to directory mode.
     * Instead of scanning all declared classes, it will only scan the specified
     * directories for PHP files and check if they implement the interface.
     *
     * Supports glob patterns for monorepo structures:
     * - `packages/*\/src` - Scans all package src directories
     * - `modules/*\/Health` - Scans Health directories in all modules
     *
     * @param string|array<string> $directories Directory path(s) to scan, supports glob patterns
     *
     * @throws RuntimeException If strategy factory was not provided in constructor
     */
    public function directories(string|array $directories): void
    {
        throw_unless(Reflection::implements($this->strategyFactory, StrategyFactoryInterface::class), RuntimeException::class, 'Cannot use directories() without a StrategyFactory. '
            . 'Provide StrategyFactory in constructor or use global discovery mode.');

        // Create DirectoryStrategy through the factory with proper dependency injection
        $this->directoryStrategy = $this->strategyFactory->createDirectoryStrategy($directories);
    }

    /**
     * Discover classes implementing the interface.
     *
     * Executes the discovery process using either global mode or directory mode:
     *
     * **Global Mode** (default):
     * - Uses `get_declared_classes()` to get all loaded classes
     * - Filters to only classes implementing the interface
     * - Fast but requires classes to be autoloaded
     *
     * **Directory Mode** (when directories() was called):
     * - Scans specified directories for PHP files
     * - Resolves file paths to class names
     * - Filters to only classes implementing the interface
     * - More reliable and doesn't require autoloading
     *
     * @return array<int, class-string> Array of fully qualified class names implementing the interface
     */
    public function discover(): array
    {
        // Get classes from either directory scan or global scope
        $classes = $this->directoryStrategy?->discover() ?? get_declared_classes();

        // Filter to only classes implementing the interface
        return Arr::values(Arr::filter($classes, function (string $class): bool {
            try {
                // isSubclassOf returns true for both interfaces and parent classes
                return Reflection::isSubclassOf($class, $this->interface);
            } catch (Throwable) {
                // Class doesn't exist or can't be loaded
                return false;
            }
        }));
    }

    /**
     * Get metadata for a discovered class.
     *
     * Returns information about the discovered class including the interface
     * it implements. This metadata can be used for registration, documentation,
     * or debugging purposes.
     *
     * @param  class-string         $class Fully qualified class name
     * @return array<string, mixed> Metadata array with 'class' and 'interface' keys
     */
    public function getMetadata(string $class): array
    {
        return [
            'class' => $class,
            'interface' => $this->interface,
        ];
    }

    /**
     * Get cache key for this strategy.
     *
     * Generates a unique cache key based on the interface name. The key is
     * MD5 hashed to ensure it's filesystem-safe and consistent length.
     *
     * @return string Cache key in format 'interface:{md5_hash}'
     */
    public function getCacheKey(): string
    {
        return 'interface:' . md5($this->interface);
    }
}
