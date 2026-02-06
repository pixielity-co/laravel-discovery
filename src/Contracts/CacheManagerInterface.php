<?php

namespace Pixielity\Discovery\Contracts;

use Illuminate\Container\Attributes\Bind;
use Illuminate\Container\Attributes\Singleton;
use Pixielity\Discovery\Cache\CacheManager;

/**
 * CacheManagerInterface - Contract for discovery cache management.
 *
 * Defines the interface for caching discovery results. Implementations should
 * provide file-based or other persistent storage mechanisms for discovered
 * class names to improve performance.
 *
 * ## Container Binding:
 * - #[Bind]: Automatically binds this interface to CacheManager implementation
 * - #[Singleton]: Shared instance across the application lifecycle
 *
 * The #[Singleton] attribute ensures a single cache manager instance is used
 * throughout the application, maintaining consistent cache state and avoiding
 * duplicate cache operations.
 */
#[Singleton]
#[Bind(CacheManager::class)]
interface CacheManagerInterface
{
    /**
     * Get cached discovery results.
     *
     * Retrieves previously cached discovery results for the given key. If caching
     * is disabled or the cache doesn't exist, returns null.
     *
     * @param  string             $key The cache key to retrieve
     * @return array<string>|null Array of discovered class names, or null if not cached
     */
    public function get(string $key): ?array;

    /**
     * Store discovery results in cache.
     *
     * Persists the discovery results for future retrieval. The implementation
     * should handle cache directory creation and file locking as needed.
     *
     * @param string        $key  The cache key
     * @param array<string> $data Array of discovered class names to cache
     */
    public function put(string $key, array $data): void;

    /**
     * Clear cache.
     *
     * Removes cached discovery results. If a specific key is provided, only that
     * cache is cleared. If no key is provided, all caches are cleared.
     *
     * @param string|null $key Specific cache key to clear, or null to clear all caches
     */
    public function clear(?string $key = null): void;
}
