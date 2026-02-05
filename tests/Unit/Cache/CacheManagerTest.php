<?php declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Unit\Cache;

use Pixielity\Discovery\Cache\CacheManager;
use Pixielity\Discovery\Tests\TestCase;
use Override;

/**
 * CacheManager Unit Tests.
 *
 * Tests the caching functionality for discovery results.
 * The CacheManager is responsible for storing and retrieving
 * discovery results to improve performance on subsequent runs.
 *
 * ## Key Features Tested:
 * - Cache storage and retrieval
 * - Cache invalidation
 * - Cache directory management
 * - Configuration handling
 * - Error handling
 *
 * @covers \Pixielity\Discovery\Cache\CacheManager
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
class CacheManagerTest extends TestCase
{
    /**
     * The cache manager instance being tested.
     *
     * @var CacheManager
     */
    protected CacheManager $cacheManager;

    /**
     * The temporary cache directory path.
     *
     * @var string
     */
    protected string $cachePath;

    /**
     * Setup the test environment.
     *
     * Creates a temporary cache directory and configures
     * the cache manager for testing.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create a temporary cache directory for testing
        $this->cachePath = sys_get_temp_dir() . '/discovery_test_cache';

        // Configure the cache manager
        config()->set('discovery.cache.path', $this->cachePath);
        config()->set('discovery.cache.enabled', true);

        // Resolve the cache manager from the container
        $this->cacheManager = resolve(CacheManager::class);
    }

    /**
     * Tear down the test environment.
     *
     * Cleans up the temporary cache directory after each test.
     */
    #[Override]
    protected function tearDown(): void
    {
        // Clean up the cache directory if it exists
        if (is_dir($this->cachePath)) {
            // Delete all cache files
            array_map(unlink(...), glob("{$this->cachePath}/*") ?: []);
            // Remove the directory
            rmdir($this->cachePath);
        }

        parent::tearDown();
    }

    /**
     * Test can store and retrieve cache.
     *
     * This test verifies that the cache manager can successfully
     * store data and retrieve it later.
     *
     * ## Scenario:
     * 1. Store data with a specific key
     * 2. Retrieve data using the same key
     * 3. Verify retrieved data matches stored data
     *
     * ## Assertions:
     * - Retrieved data equals stored data
     * - Data structure is preserved
     */
    public function test_can_store_and_retrieve_cache(): void
    {
        // Arrange: Create test data to cache
        $data = ['test' => 'value', 'number' => 123];

        // Act: Store the data in cache
        $this->cacheManager->put('test_key', $data);

        // Act: Retrieve the data from cache
        $retrieved = $this->cacheManager->get('test_key');

        // Assert: Retrieved data should match stored data
        $this->assertEquals($data, $retrieved);
    }

    /**
     * Test returns null for missing cache.
     *
     * This test verifies that the cache manager returns null
     * when attempting to retrieve a non-existent cache entry.
     *
     * ## Scenario:
     * 1. Attempt to retrieve cache with non-existent key
     * 2. Verify null is returned
     *
     * ## Assertions:
     * - Result is null
     * - No exceptions are thrown
     */
    public function test_returns_null_for_missing_cache(): void
    {
        // Act: Attempt to retrieve non-existent cache
        $result = $this->cacheManager->get('non_existent_key');

        // Assert: Should return null
        $this->assertNull($result);
    }

    /**
     * Test can clear specific cache.
     *
     * This test verifies that the cache manager can clear
     * a specific cache entry without affecting others.
     *
     * ## Scenario:
     * 1. Store multiple cache entries
     * 2. Clear one specific entry
     * 3. Verify only that entry is cleared
     *
     * ## Assertions:
     * - Cleared entry returns null
     * - Other entries remain intact
     */
    public function test_can_clear_specific_cache(): void
    {
        // Arrange: Store multiple cache entries
        $this->cacheManager->put('key1', ['data' => 1]);
        $this->cacheManager->put('key2', ['data' => 2]);

        // Act: Clear only the first cache entry
        $this->cacheManager->clear('key1');

        // Assert: First entry should be cleared
        $this->assertNull($this->cacheManager->get('key1'));

        // Assert: Second entry should still exist
        $this->assertNotNull($this->cacheManager->get('key2'));
    }

    /**
     * Test can clear all caches.
     *
     * This test verifies that the cache manager can clear
     * all cache entries at once.
     *
     * ## Scenario:
     * 1. Store multiple cache entries
     * 2. Clear all caches
     * 3. Verify all entries are cleared
     *
     * ## Assertions:
     * - All cache entries return null
     * - Cache directory is cleaned
     */
    public function test_can_clear_all_caches(): void
    {
        // Arrange: Store multiple cache entries
        $this->cacheManager->put('key1', ['data' => 1]);
        $this->cacheManager->put('key2', ['data' => 2]);

        // Act: Clear all cache entries
        $this->cacheManager->clear();

        // Assert: All entries should be cleared
        $this->assertNull($this->cacheManager->get('key1'));
        $this->assertNull($this->cacheManager->get('key2'));
    }

    /**
     * Test respects cache enabled config.
     *
     * This test verifies that the cache manager respects
     * the cache.enabled configuration setting.
     *
     * ## Scenario:
     * 1. Disable caching in configuration
     * 2. Attempt to store and retrieve cache
     * 3. Verify caching is bypassed
     *
     * ## Assertions:
     * - Cache is not stored when disabled
     * - Retrieval returns null
     */
    public function test_respects_cache_enabled_config(): void
    {
        // Arrange: Disable caching
        config()->set('discovery.cache.enabled', false);

        // Arrange: Create a new cache manager with disabled config
        $cacheManager = resolve(CacheManager::class);

        // Act: Attempt to store cache
        $cacheManager->put('test', ['data' => 'value']);

        // Act: Attempt to retrieve cache
        $result = $cacheManager->get('test');

        // Assert: Should return null (caching is disabled)
        $this->assertNull($result);
    }

    /**
     * Test creates cache directory if not exists.
     *
     * This test verifies that the cache manager automatically
     * creates the cache directory if it doesn't exist.
     *
     * ## Scenario:
     * 1. Configure a non-existent cache directory
     * 2. Store cache data
     * 3. Verify directory is created
     *
     * ## Assertions:
     * - Cache directory is created
     * - Cache data is stored successfully
     */
    public function test_creates_cache_directory_if_not_exists(): void
    {
        // Arrange: Define a new cache path that doesn't exist
        $newPath = sys_get_temp_dir() . '/discovery_new_cache';

        // Arrange: Ensure the directory doesn't exist
        if (is_dir($newPath)) {
            rmdir($newPath);
        }

        // Arrange: Configure the new cache path
        config()->set('discovery.cache.path', $newPath);

        // Arrange: Create a new cache manager
        $cacheManager = resolve(CacheManager::class);

        // Act: Store cache data (should create directory)
        $cacheManager->put('test', ['data' => 'value']);

        // Assert: Directory should be created
        $this->assertDirectoryExists($newPath);

        // Cleanup: Remove the test directory
        array_map(unlink(...), glob("{$newPath}/*") ?: []);
        rmdir($newPath);
    }

    /**
     * Test handles invalid cache data gracefully.
     *
     * This test verifies that the cache manager handles
     * corrupted or invalid cache files gracefully.
     *
     * ## Scenario:
     * 1. Create an invalid cache file manually
     * 2. Attempt to retrieve the cache
     * 3. Verify graceful handling
     *
     * ## Assertions:
     * - Invalid cache returns null
     * - No exceptions are thrown
     */
    public function test_handles_invalid_cache_data_gracefully(): void
    {
        // Arrange: Ensure cache directory exists
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0777, true);
        }

        // Arrange: Create an invalid cache file
        file_put_contents("{$this->cachePath}/invalid.php", '<?php return "invalid";');

        // Act: Attempt to retrieve the invalid cache
        $result = $this->cacheManager->get('invalid');

        // Assert: Should return null (invalid data)
        $this->assertNull($result);
    }

    /**
     * Test cache key sanitization.
     *
     * This test verifies that the cache manager properly
     * sanitizes cache keys to create valid file names.
     *
     * ## Scenario:
     * 1. Use a cache key with special characters
     * 2. Store and retrieve cache
     * 3. Verify key sanitization works
     *
     * ## Assertions:
     * - Cache is stored successfully
     * - Cache can be retrieved with original key
     * - Special characters are handled
     */
    public function test_cache_key_sanitization(): void
    {
        // Arrange: Create a key with special characters
        $key = 'test/key:with*special?chars';
        $data = ['test' => 'value'];

        // Act: Store cache with special key
        $this->cacheManager->put($key, $data);

        // Act: Retrieve cache with original key
        $retrieved = $this->cacheManager->get($key);

        // Assert: Data should be retrieved successfully
        $this->assertEquals($data, $retrieved);
    }
}
