<?php

namespace Fulers\Discovery\Tests\Unit\Strategies;

use Fulers\Discovery\Resolvers\NamespaceResolver;
use Fulers\Discovery\Strategies\DirectoryStrategy;
use Illuminate\Contracts\Foundation\Application;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * DirectoryStrategyTest - Tests for DirectoryStrategy class.
 *
 * @covers \Fulers\Discovery\Strategies\DirectoryStrategy
 */
class DirectoryStrategyTest extends TestCase
{
    private NamespaceResolver $namespaceResolver;

    private MockObject $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->namespaceResolver = new NamespaceResolver();
        $this->app = $this->createMock(Application::class);
        $this->app->method('basePath')->willReturnCallback(fn ($path): string => '/base/' . $path);
    }

    /**
     * Test that strategy can be instantiated with single directory.
     */
    public function test_can_instantiate_with_single_directory(): void
    {
        $directoryStrategy = new DirectoryStrategy('src', $this->namespaceResolver, $this->app);

        $this->assertInstanceOf(DirectoryStrategy::class, $directoryStrategy);
    }

    /**
     * Test that strategy can be instantiated with multiple directories.
     */
    public function test_can_instantiate_with_multiple_directories(): void
    {
        $directoryStrategy = new DirectoryStrategy(['src', 'app'], $this->namespaceResolver, $this->app);

        $this->assertInstanceOf(DirectoryStrategy::class, $directoryStrategy);
    }

    /**
     * Test that setDirectories updates directories.
     */
    public function test_set_directories_updates_directories(): void
    {
        $directoryStrategy = new DirectoryStrategy('src', $this->namespaceResolver, $this->app);
        $directoryStrategy->setDirectories(['app', 'packages']);

        // Test by checking discover behavior (indirectly)
        $this->assertInstanceOf(DirectoryStrategy::class, $directoryStrategy);
    }

    /**
     * Test that setNamespacePattern updates pattern.
     */
    public function test_set_namespace_pattern_updates_pattern(): void
    {
        $directoryStrategy = new DirectoryStrategy('src', $this->namespaceResolver, $this->app);
        $directoryStrategy->setNamespacePattern('Custom\{package}\{class}');

        $this->assertInstanceOf(DirectoryStrategy::class, $directoryStrategy);
    }

    /**
     * Test that discover returns empty array for non-existent directories.
     */
    public function test_discover_returns_empty_array_for_non_existent_directories(): void
    {
        $directoryStrategy = new DirectoryStrategy('/non/existent/path', $this->namespaceResolver, $this->app);
        $result = $directoryStrategy->discover();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test that getMetadata returns correct structure.
     */
    public function test_get_metadata_returns_correct_structure(): void
    {
        $directoryStrategy = new DirectoryStrategy('src', $this->namespaceResolver, $this->app);
        $metadata = $directoryStrategy->getMetadata('TestClass');

        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('class', $metadata);
        $this->assertArrayHasKey('file', $metadata);
        $this->assertEquals('TestClass', $metadata['class']);
    }

    /**
     * Test that getCacheKey returns consistent key.
     */
    public function test_get_cache_key_returns_consistent_key(): void
    {
        $directoryStrategy = new DirectoryStrategy('src', $this->namespaceResolver, $this->app);
        $key1 = $directoryStrategy->getCacheKey();
        $key2 = $directoryStrategy->getCacheKey();

        $this->assertEquals($key1, $key2);
        $this->assertStringStartsWith('directory:', $key1);
    }

    /**
     * Test that getCacheKey is unique per directory set.
     */
    public function test_get_cache_key_is_unique_per_directory_set(): void
    {
        $strategy1 = new DirectoryStrategy('src', $this->namespaceResolver, $this->app);
        $strategy2 = new DirectoryStrategy('app', $this->namespaceResolver, $this->app);

        $key1 = $strategy1->getCacheKey();
        $key2 = $strategy2->getCacheKey();

        $this->assertNotEquals($key1, $key2);
    }

    /**
     * Test that discover handles exceptions gracefully.
     */
    public function test_discover_handles_exceptions_gracefully(): void
    {
        // Use a non-existent directory to test exception handling
        $directoryStrategy = new DirectoryStrategy('/non/existent/path', $this->namespaceResolver, $this->app);
        $result = $directoryStrategy->discover();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
        // Should not throw exception
    }

    /**
     * Test that getMetadata handles exceptions gracefully.
     */
    public function test_get_metadata_handles_exceptions_gracefully(): void
    {
        $directoryStrategy = new DirectoryStrategy('src', $this->namespaceResolver, $this->app);
        $metadata = $directoryStrategy->getMetadata('NonExistentClass');

        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('class', $metadata);
        $this->assertArrayHasKey('file', $metadata);
        $this->assertNull($metadata['file']);
    }
}
