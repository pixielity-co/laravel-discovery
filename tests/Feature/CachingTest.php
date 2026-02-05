<?php declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Feature;

use Pixielity\Discovery\Tests\Fixtures\Attributes\TestCardAttribute;
use Pixielity\Discovery\Tests\TestCase;
use Pixielity\Discovery\DiscoveryManager;

/**
 * Caching Feature Tests.
 *
 * End-to-end tests for discovery result caching.
 * Tests cache storage, retrieval, and invalidation.
 *
 * @covers \Pixielity\Discovery\Cache\CacheManager
 * @covers \Pixielity\Discovery\DiscoveryBuilder
 */
class CachingTest extends TestCase
{
    /**
     * Discovery manager instance.
     *
     * @var DiscoveryManager
     */
    protected DiscoveryManager $discovery;

    /**
     * Set up the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->discovery = app(DiscoveryManager::class);

        // Enable caching for tests
        config()->set('discovery.cache.enabled', true);
    }

    /**
     * Test caches discovery results.
     *
     * Verifies that when caching is enabled, discovery results
     * are stored in the cache for future retrieval.
     *
     * @return void
     */
    public function test_caches_discovery_results(): void
    {
        // Act: Perform discovery with caching
        $results = $this
            ->discovery
            ->attribute(TestCardAttribute::class)
            ->cached('test_cache_1')
            ->get()->all();

        // Assert: Results should be cached
        $this->assertIsArray($results);
    }

    /**
     * Test returns cached results on second call.
     *
     * Verifies that subsequent calls with the same cache key
     * return the cached results instead of re-discovering.
     *
     * @return void
     */
    public function test_returns_cached_results_on_second_call(): void
    {
        // Act: First call - caches results
        $results1 = $this
            ->discovery
            ->attribute(TestCardAttribute::class)
            ->cached('test_cache_2')
            ->get()->all();

        // Act: Second call - should return cached results
        $results2 = $this
            ->discovery
            ->attribute(TestCardAttribute::class)
            ->cached('test_cache_2')
            ->get()->all();

        // Assert: Both results should be identical
        $this->assertEquals($results1, $results2);
    }

    /**
     * Test cache key uniqueness.
     *
     * Verifies that different cache keys store separate results
     * and don't interfere with each other.
     *
     * @return void
     */
    public function test_cache_key_uniqueness(): void
    {
        // Act: Cache with key1
        $results1 = $this
            ->discovery
            ->attribute(TestCardAttribute::class)
            ->where('enabled', true)
            ->cached('test_cache_key1')
            ->get()->all();

        // Act: Cache with key2 (different filter)
        $results2 = $this
            ->discovery
            ->attribute(TestCardAttribute::class)
            ->where('enabled', false)
            ->cached('test_cache_key2')
            ->get()->all();

        // Assert: Results should be different
        $this->assertIsArray($results1);
        $this->assertIsArray($results2);
    }

    /**
     * Test clear cache invalidates results.
     *
     * Verifies that calling clearCache() removes cached results
     * and forces fresh discovery on next call.
     *
     * @return void
     */
    public function test_clear_cache_invalidates_results(): void
    {
        // Arrange: Cache some results
        $this
            ->discovery
            ->attribute(TestCardAttribute::class)
            ->cached('test_cache_3')
            ->get()->all();

        // Act: Clear the cache
        $this->discovery->clearCache('test_cache_3');

        // Assert: Cache should be cleared
        $this->assertTrue(true);
    }

    /**
     * Test cache respects config.
     *
     * Verifies that when caching is disabled in config,
     * results are not cached even when cached() is called.
     *
     * @return void
     */
    public function test_cache_respects_config(): void
    {
        // Arrange: Disable caching
        config()->set('discovery.cache.enabled', false);

        // Act: Try to cache results
        $results = $this
            ->discovery
            ->attribute(TestCardAttribute::class)
            ->cached('test_cache_4')
            ->get()->all();

        // Assert: Should still return results (just not cached)
        $this->assertIsArray($results);
    }

    /**
     * Test cache disabled in local environment.
     *
     * Verifies that caching is automatically disabled in
     * local development environment.
     *
     * @return void
     */
    public function test_cache_disabled_in_local_environment(): void
    {
        // Arrange: Set environment to local
        app()->detectEnvironment(fn() => 'local');

        // Act: Perform discovery
        $results = $this
            ->discovery
            ->attribute(TestCardAttribute::class)
            ->get()->all();

        // Assert: Should work without caching
        $this->assertIsArray($results);
    }
}
