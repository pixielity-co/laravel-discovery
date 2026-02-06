<?php

namespace Pixielity\Discovery\Cache;

use Illuminate\Container\Attributes\Config;
use Illuminate\Container\Attributes\Scoped;
use Illuminate\Support\Facades\File;
use Pixielity\Discovery\Contracts\CacheManagerInterface;
use Throwable;

/**
 * CacheManager - Manages discovery result caching.
 *
 * This class provides file-based caching for discovery operations to significantly
 * improve performance by avoiding repeated filesystem scans. It stores serialized
 * PHP arrays as executable PHP files for fast loading.
 *
 * The cache can be disabled in development environments and enabled in production
 * through configuration. Cache files are stored with MD5-hashed keys to ensure
 * unique filenames.
 */
#[Scoped]
class CacheManager implements CacheManagerInterface
{
    /**
     * Create a new CacheManager instance.
     *
     * This constructor uses dependency injection to receive configuration values.
     * The #[Config] attributes on properties indicate which configuration keys
     * should be injected.
     *
     * @param string $cachePath The directory path for cache files
     * @param bool   $enabled   Whether caching is enabled
     */
    public function __construct(
        #[Config('discovery.cache.path', null)]
        protected ?string $cachePath,
        #[Config('discovery.cache.enabled', true)]
        protected ?bool $enabled
    ) {
        // Ensure a default cache path is set
        if (! $cachePath) {
            $this->cachePath = base_path('bootstrap/cache/discovery');
        }
    }

    /**
     * Get cached discovery results.
     *
     * Retrieves previously cached discovery results for the given key. If caching
     * is disabled or the cache file doesn't exist, returns null.
     *
     * The cache file is a PHP file that returns an array, which is executed using
     * require for fast loading. Any errors during loading are caught and null is
     * returned, allowing the discovery process to proceed normally.
     *
     * @param  string             $key The cache key to retrieve (will be MD5 hashed for filename)
     * @return array<string>|null Array of discovered class names, or null if not cached
     */
    public function get(string $key): ?array
    {
        // If caching is disabled, always return null to force fresh discovery
        if (! $this->enabled) {
            return null;
        }

        // Get the full path to the cache file
        $file = $this->getCacheFile($key);

        // If the cache file doesn't exist, return null
        if (! File::exists($file)) {
            return null;
        }

        try {
            // Execute the PHP cache file and return its array
            // The cache file contains: <?php return [...];
            return require $file;
        } catch (Throwable) {
            // If there's any error loading the cache (corrupted file, etc.),
            // return null to trigger a fresh discovery
            return null;
        }
    }

    /**
     * Store discovery results in cache.
     *
     * Serializes the discovery results as a PHP array and writes it to a cache file.
     * The file is created as executable PHP code that returns the array, allowing
     * for fast loading via require().
     *
     * The cache directory is created automatically if it doesn't exist. File locking
     * (LOCK_EX) is used to prevent race conditions when multiple processes try to
     * write to the same cache file simultaneously.
     *
     * @param string        $key  The cache key (will be MD5 hashed for filename)
     * @param array<string> $data Array of discovered class names to cache
     */
    public function put(string $key, array $data): void
    {
        // If caching is disabled, do nothing
        if (! $this->enabled) {
            return;
        }

        try {
            // Get the full path to the cache file
            $file = $this->getCacheFile($key);

            // Get the directory path
            $dir = dirname($file);

            // Create the cache directory if it doesn't exist
            // 0755 permissions allow owner full access, group/others read+execute
            if (! File::isDirectory($dir) && ! File::makeDirectory($dir, 0755, true)) {
                logger()->warning("Failed to create discovery cache directory: {$dir}");

                return;
            }

            // Convert the array to executable PHP code
            // var_export creates a parseable string representation of the array
            $content = '<?php' . PHP_EOL . PHP_EOL;
            $content .= '// Discovery cache file' . PHP_EOL;
            $content .= '// Generated: ' . date('Y-m-d H:i:s') . PHP_EOL;
            $content .= '// Key: ' . $key . PHP_EOL;
            $content .= PHP_EOL;
            $content .= 'return ' . var_export($data, true) . ';';

            // Write the file with exclusive lock to prevent concurrent writes
            if (! File::put($file, $content, true)) {
                logger()->warning("Failed to write discovery cache file: {$file}");
            }
        } catch (Throwable $throwable) {
            // Log the error but don't throw - caching failures shouldn't break discovery
            logger()->error('Discovery cache write failed', [
                'key' => $key,
                'error' => $throwable->getMessage(),
            ]);
        }
    }

    /**
     * Clear cache.
     *
     * Removes cached discovery results. If a specific key is provided, only that
     * cache file is deleted. If no key is provided, all cache files in the cache
     * directory are removed.
     *
     * This is useful for:
     * - Development: Clear cache after adding new classes
     * - Deployment: Clear cache to force fresh discovery
     * - Testing: Ensure clean state between tests
     *
     * @param string|null $key Specific cache key to clear, or null to clear all caches
     */
    public function clear(?string $key = null): void
    {
        try {
            if ($key !== null) {
                // Clear a specific cache file
                $file = $this->getCacheFile($key);
                // Only attempt to delete if the file exists
                if (File::exists($file) && ! File::delete($file)) {
                    logger()->warning("Failed to delete discovery cache file: {$file}");
                }
            } elseif ($this->cachePath !== null && File::isDirectory($this->cachePath)) {
                // Clear all cache files in the cache directory
                // Find all PHP files in the cache directory
                $files = File::glob("{$this->cachePath}/*.php");
                // Delete each cache file
                foreach ($files as $file) {
                    // Double-check it's a file (not a directory)
                    if (! File::isFile($file)) {
                        continue;
                    }
                    if (File::delete($file)) {
                        continue;
                    }
                    logger()->warning("Failed to delete discovery cache file: {$file}");
                }
            }
        } catch (Throwable $throwable) {
            // Log the error but don't throw - cache clearing failures shouldn't break the application
            logger()->error('Discovery cache clear failed', [
                'key' => $key,
                'error' => $throwable->getMessage(),
            ]);
        }
    }

    /**
     * Get cache file path for key.
     *
     * Converts a cache key into a full filesystem path. The key is MD5 hashed
     * to create a safe filename that doesn't contain special characters.
     *
     * Example:
     * - Key: "settings-discovery"
     * - Hash: "5f4dcc3b5aa765d61d8327deb882cf99"
     * - Path: "/path/to/cache/5f4dcc3b5aa765d61d8327deb882cf99.php"
     *
     * @param  string $key The cache key to convert
     * @return string Full filesystem path to the cache file
     */
    protected function getCacheFile(string $key): string
    {
        // Hash the key to create a safe filename
        $hashedKey = md5($key);

        // Combine cache path with hashed filename
        return "{$this->cachePath}/{$hashedKey}.php";
    }
}
