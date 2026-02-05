<?php

namespace Fulers\Discovery\Strategies;

use Fulers\Discovery\Contracts\DiscoveryStrategyInterface;
use Fulers\Discovery\Contracts\StrategyFactoryInterface;
use Fulers\Support\Arr;
use Fulers\Support\Reflection;
use RuntimeException;
use Throwable;

/**
 * ParentClassStrategy - Discovers classes extending a parent class.
 *
 * This strategy finds all classes that extend a specified parent class. It can operate
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
 * $strategy = new ParentClassStrategy(Command::class);
 * $classes = $strategy->discover();
 *
 * // Directory mode - scans specific directories
 * $strategy = new ParentClassStrategy(Command::class, $strategyFactory);
 * $strategy->directories('packages/*\/src/Console/Commands');
 * $classes = $strategy->discover();
 * ```
 *
 * ## Performance Considerations:
 * - Global mode: Fast but requires classes to be autoloaded
 * - Directory mode: Slower initial scan but more reliable and doesn't require autoloading
 */
class ParentClassStrategy implements DiscoveryStrategyInterface
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
     * Create a new ParentClassStrategy instance.
     *
     * The strategy factory is optional. If not provided, the directories()
     * method will not be available and the strategy will only work in global mode.
     *
     * @param string                        $parentClass     Fully qualified parent class name to search for
     * @param StrategyFactoryInterface|null $strategyFactory Optional factory for creating directory strategies
     */
    public function __construct(
        protected string $parentClass,
        protected ?StrategyFactoryInterface $strategyFactory = null
    ) {}

    /**
     * Set directories to scan for classes extending the parent class.
     *
     * This method switches the strategy from global mode to directory mode.
     * Instead of scanning all declared classes, it will only scan the specified
     * directories for PHP files and check if they extend the parent class.
     *
     * Supports glob patterns for monorepo structures:
     * - `packages/*\/src` - Scans all package src directories
     * - `modules/*\/Commands` - Scans Commands directories in all modules
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
     * Discover classes extending the parent class.
     *
     * Executes the discovery process using either global mode or directory mode:
     *
     * **Global Mode** (default):
     * - Uses `get_declared_classes()` to get all loaded classes
     * - Filters to only classes extending the parent class
     * - Fast but requires classes to be autoloaded
     *
     * **Directory Mode** (when directories() was called):
     * - Scans specified directories for PHP files
     * - Resolves file paths to class names
     * - Filters to only classes extending the parent class
     * - More reliable and doesn't require autoloading
     *
     * @return array<int, class-string> Array of fully qualified class names extending the parent class
     */
    public function discover(): array
    {
        // Get classes from either directory scan or global scope
        $classes = $this->directoryStrategy?->discover() ?? get_declared_classes();

        // Filter to only classes extending the parent class
        return Arr::values(Arr::filter($classes, function (string $class): bool {
            try {
                // isSubclassOf returns true for both interfaces and parent classes
                return Reflection::isSubclassOf($class, $this->parentClass);
            } catch (Throwable) {
                // Class doesn't exist or can't be loaded
                return false;
            }
        }));
    }

    /**
     * Get metadata for a discovered class.
     *
     * Returns information about the discovered class including the parent class
     * it extends. This metadata can be used for registration, documentation,
     * or debugging purposes.
     *
     * @param  class-string         $class Fully qualified class name
     * @return array<string, mixed> Metadata array with 'class' and 'parent' keys
     */
    public function getMetadata(string $class): array
    {
        return [
            'class' => $class,
            'parent' => $this->parentClass,
        ];
    }

    /**
     * Get cache key for this strategy.
     *
     * Generates a unique cache key based on the parent class name. The key is
     * MD5 hashed to ensure it's filesystem-safe and consistent length.
     *
     * @return string Cache key in format 'parent:{md5_hash}'
     */
    public function getCacheKey(): string
    {
        return 'parent:' . md5($this->parentClass);
    }
}
