<?php

namespace Fulers\Discovery\Tests\Unit;

use Fulers\Discovery\Cache\CacheManager;
use Fulers\Discovery\Contracts\StrategyFactoryInterface;
use Fulers\Discovery\DiscoveryBuilder;
use Fulers\Discovery\DiscoveryManager;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the DiscoveryManager service.
 *
 * These tests verify that the DiscoveryManager correctly instantiates
 * DiscoveryBuilder instances with the appropriate strategies.
 */
class DiscoveryTest extends TestCase
{
    /**
     * Test that byAttribute() returns a DiscoveryBuilder instance.
     *
     * @test
     */
    public function it_returns_discovery_builder_from_by_attribute(): void
    {
        $this->markTestSkipped('DiscoveryBuilder and AttributeStrategy not yet implemented');

        $cacheManager = $this->createMock(CacheManager::class);
        $discoveryManager = new DiscoveryManager($cacheManager);

        $discoveryBuilder = $discoveryManager->byAttribute('SomeAttribute');

        $this->assertInstanceOf(DiscoveryBuilder::class, $discoveryBuilder);
    }

    /**
     * Test that inDirectories() returns a DiscoveryBuilder instance.
     *
     * @test
     */
    public function it_returns_discovery_builder_from_in_directories(): void
    {
        $this->markTestSkipped('DiscoveryBuilder and DirectoryStrategy not yet implemented');

        $cacheManager = $this->createMock(CacheManager::class);
        $discoveryManager = new DiscoveryManager($cacheManager);

        $discoveryBuilder = $discoveryManager->inDirectories('some/path');

        $this->assertInstanceOf(DiscoveryBuilder::class, $discoveryBuilder);
    }

    /**
     * Test that inDirectories() accepts an array of directories.
     *
     * @test
     */
    public function it_accepts_array_of_directories(): void
    {
        $this->markTestSkipped('DiscoveryBuilder and DirectoryStrategy not yet implemented');

        $cacheManager = $this->createMock(CacheManager::class);
        $discoveryManager = new DiscoveryManager($cacheManager);

        $discoveryBuilder = $discoveryManager->inDirectories(['path1', 'path2']);

        $this->assertInstanceOf(DiscoveryBuilder::class, $discoveryBuilder);
    }

    /**
     * Test that implementing() returns a DiscoveryBuilder instance.
     *
     * @test
     */
    public function it_returns_discovery_builder_from_implementing(): void
    {
        $this->markTestSkipped('DiscoveryBuilder and InterfaceStrategy not yet implemented');

        $cacheManager = $this->createMock(CacheManager::class);
        $discoveryManager = new DiscoveryManager($cacheManager);

        $discoveryBuilder = $discoveryManager->implementing('SomeInterface');

        $this->assertInstanceOf(DiscoveryBuilder::class, $discoveryBuilder);
    }

    /**
     * Test that extending() returns a DiscoveryBuilder instance.
     *
     * @test
     */
    public function it_returns_discovery_builder_from_extending(): void
    {
        $this->markTestSkipped('DiscoveryBuilder and ParentClassStrategy not yet implemented');

        $cacheManager = $this->createMock(CacheManager::class);
        $discoveryManager = new DiscoveryManager($cacheManager);

        $discoveryBuilder = $discoveryManager->extending('SomeParentClass');

        $this->assertInstanceOf(DiscoveryBuilder::class, $discoveryBuilder);
    }

    /**
     * Test that clearCache() calls CacheManager::clear().
     *
     * @test
     */
    public function it_clears_cache_without_key(): void
    {
        $discoveryBuilder = $this->createMock(DiscoveryBuilder::class);
        $cacheManager = $this->createMock(CacheManager::class);
        $strategyFactory = $this->createMock(StrategyFactoryInterface::class);

        $cacheManager
            ->expects($this->once())
            ->method('clear')
            ->with(null);

        $discoveryManager = new DiscoveryManager($discoveryBuilder, $cacheManager, $strategyFactory);
        $discoveryManager->clearCache();
    }

    /**
     * Test that clearCache() accepts a specific cache key.
     *
     * @test
     */
    public function it_clears_cache_with_specific_key(): void
    {
        $discoveryBuilder = $this->createMock(DiscoveryBuilder::class);
        $cacheManager = $this->createMock(CacheManager::class);
        $strategyFactory = $this->createMock(StrategyFactoryInterface::class);

        $cacheManager
            ->expects($this->once())
            ->method('clear')
            ->with('some-key');

        $discoveryManager = new DiscoveryManager($discoveryBuilder, $cacheManager, $strategyFactory);
        $discoveryManager->clearCache('some-key');
    }
}
