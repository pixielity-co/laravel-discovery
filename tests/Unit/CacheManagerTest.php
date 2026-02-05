<?php

namespace Fulers\Discovery\Tests\Unit;

use Fulers\Discovery\Cache\CacheManager;
use PHPUnit\Framework\TestCase;

/**
 * CacheManagerTest - Tests for CacheManager class.
 *
 * @covers \Fulers\Discovery\Cache\CacheManager
 */
class CacheManagerTest extends TestCase
{
    private string $cachePath;

    private CacheManager $cacheManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cachePath = sys_get_temp_dir() . '/discovery-test-' . uniqid();
        $this->cacheManager = new CacheManager($this->cachePath, true);
    }

    protected function tearDown(): void
    {
        // Clean up test cache files
        if (is_dir($this->cachePath)) {
            $files = glob($this->cachePath . '/*.php');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($this->cachePath);
        }

        parent::tearDown();
    }

    /**
     * Test that cache manager can be instantiated.
     */
    public function test_can_instantiate(): void
    {
        $this->assertInstanceOf(CacheManager::class, $this->cacheManager);
    }

    /**
     * Test that get returns null for non-existent cache.
     */
    public function test_get_returns_null_for_non_existent_cache(): void
    {
        $result = $this->cacheManager->get('non-existent-key');

        $this->assertNull($result);
    }

    /**
     * Test that put stores data and get retrieves it.
     */
    public function test_put_stores_data_and_get_retrieves_it(): void
    {
        $data = ['Class1', 'Class2', 'Class3'];

        $this->cacheManager->put('test-key', $data);
        $result = $this->cacheManager->get('test-key');

        $this->assertEquals($data, $result);
    }

    /**
     * Test that put overwrites existing cache.
     */
    public function test_put_overwrites_existing_cache(): void
    {
        $data1 = ['Class1', 'Class2'];
        $data2 = ['Class3', 'Class4'];

        $this->cacheManager->put('test-key', $data1);
        $this->cacheManager->put('test-key', $data2);

        $result = $this->cacheManager->get('test-key');

        $this->assertEquals($data2, $result);
    }

    /**
     * Test that clear removes specific cache.
     */
    public function test_clear_removes_specific_cache(): void
    {
        $this->cacheManager->put('test-key-1', ['Class1']);
        $this->cacheManager->put('test-key-2', ['Class2']);

        $this->cacheManager->clear('test-key-1');

        $this->assertNull($this->cacheManager->get('test-key-1'));
        $this->assertNotNull($this->cacheManager->get('test-key-2'));
    }

    /**
     * Test that clear without key removes all caches.
     */
    public function test_clear_without_key_removes_all_caches(): void
    {
        $this->cacheManager->put('test-key-1', ['Class1']);
        $this->cacheManager->put('test-key-2', ['Class2']);

        $this->cacheManager->clear();

        $this->assertNull($this->cacheManager->get('test-key-1'));
        $this->assertNull($this->cacheManager->get('test-key-2'));
    }

    /**
     * Test that caching can be disabled.
     */
    public function test_caching_can_be_disabled(): void
    {
        $cacheManager = new CacheManager($this->cachePath, false);

        $cacheManager->put('test-key', ['Class1']);

        $result = $cacheManager->get('test-key');

        $this->assertNull($result);
    }

    /**
     * Test that cache directory is created automatically.
     */
    public function test_cache_directory_is_created_automatically(): void
    {
        $newCachePath = sys_get_temp_dir() . '/discovery-new-' . uniqid();
        $cacheManager = new CacheManager($newCachePath, true);

        $cacheManager->put('test-key', ['Class1']);

        $this->assertDirectoryExists($newCachePath);

        // Cleanup
        $files = glob($newCachePath . '/*.php');
        foreach ($files as $file) {
            unlink($file);
        }
        rmdir($newCachePath);
    }

    /**
     * Test that cache files are valid PHP.
     */
    public function test_cache_files_are_valid_php(): void
    {
        $data = ['Class1', 'Class2'];
        $this->cacheManager->put('test-key', $data);

        $files = glob($this->cachePath . '/*.php');
        $this->assertNotEmpty($files);

        $cacheFile = $files[0];
        $this->assertFileExists($cacheFile);

        $content = file_get_contents($cacheFile);
        $this->assertStringStartsWith('<?php', $content);
    }

    /**
     * Test that get handles corrupted cache files gracefully.
     */
    public function test_get_handles_corrupted_cache_files_gracefully(): void
    {
        // Create a corrupted cache file
        if (! is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }

        $cacheFile = $this->cachePath . '/' . md5('test-key') . '.php';
        file_put_contents($cacheFile, '<?php invalid php syntax {{{');

        $result = $this->cacheManager->get('test-key');

        $this->assertNull($result);
    }

    /**
     * Test that clear handles non-existent cache gracefully.
     */
    public function test_clear_handles_non_existent_cache_gracefully(): void
    {
        $this->cacheManager->clear('non-existent-key');

        // Should not throw exception
        $this->assertTrue(true);
    }
}
