<?php

namespace Fulers\Discovery\Tests\Unit;

use Fulers\Discovery\Contracts\CacheManagerInterface;
use Fulers\Discovery\Contracts\DiscoveryStrategyInterface;
use Fulers\Discovery\DiscoveryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * DiscoveryBuilderTest - Tests for DiscoveryBuilder class.
 *
 * @covers \Fulers\Discovery\DiscoveryBuilder
 */
class DiscoveryBuilderTest extends TestCase
{
    private MockObject $cacheManager;

    private MockObject $strategy;

    private DiscoveryBuilder $discoveryBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheManager = $this->createMock(CacheManagerInterface::class);
        $this->strategy = $this->createMock(DiscoveryStrategyInterface::class);
        $this->discoveryBuilder = new DiscoveryBuilder($this->cacheManager);
    }

    /**
     * Test that builder can be instantiated.
     */
    public function test_can_instantiate(): void
    {
        $this->assertInstanceOf(DiscoveryBuilder::class, $this->discoveryBuilder);
    }

    /**
     * Test that setStrategy returns fluent interface.
     */
    public function test_set_strategy_returns_fluent_interface(): void
    {
        $discoveryBuilder = $this->discoveryBuilder->setStrategy($this->strategy);

        $this->assertSame($this->discoveryBuilder, $discoveryBuilder);
    }

    /**
     * Test that where returns fluent interface.
     */
    public function test_where_returns_fluent_interface(): void
    {
        $this->discoveryBuilder->setStrategy($this->strategy);
        $discoveryBuilder = $this->discoveryBuilder->where('property', 'value');

        $this->assertSame($this->discoveryBuilder, $discoveryBuilder);
    }

    /**
     * Test that filter returns fluent interface.
     */
    public function test_filter_returns_fluent_interface(): void
    {
        $this->discoveryBuilder->setStrategy($this->strategy);
        $discoveryBuilder = $this->discoveryBuilder->filter(fn ($class): true => true);

        $this->assertSame($this->discoveryBuilder, $discoveryBuilder);
    }

    /**
     * Test that extending returns fluent interface.
     */
    public function test_extending_returns_fluent_interface(): void
    {
        $this->discoveryBuilder->setStrategy($this->strategy);
        $discoveryBuilder = $this->discoveryBuilder->extending('ParentClass');

        $this->assertSame($this->discoveryBuilder, $discoveryBuilder);
    }

    /**
     * Test that implementing returns fluent interface.
     */
    public function test_implementing_returns_fluent_interface(): void
    {
        $this->discoveryBuilder->setStrategy($this->strategy);
        $discoveryBuilder = $this->discoveryBuilder->implementing('InterfaceName');

        $this->assertSame($this->discoveryBuilder, $discoveryBuilder);
    }

    /**
     * Test that instantiable returns fluent interface.
     */
    public function test_instantiable_returns_fluent_interface(): void
    {
        $this->discoveryBuilder->setStrategy($this->strategy);
        $discoveryBuilder = $this->discoveryBuilder->instantiable();

        $this->assertSame($this->discoveryBuilder, $discoveryBuilder);
    }

    /**
     * Test that cached returns fluent interface.
     */
    public function test_cached_returns_fluent_interface(): void
    {
        $this->strategy->method('getCacheKey')->willReturn('test-key');
        $this->discoveryBuilder->setStrategy($this->strategy);
        $discoveryBuilder = $this->discoveryBuilder->cached();

        $this->assertSame($this->discoveryBuilder, $discoveryBuilder);
    }

    /**
     * Test that get returns array of classes.
     */
    public function test_get_returns_array_of_classes(): void
    {
        $classes = ['Class1', 'Class2', 'Class3'];
        $this->strategy->method('discover')->willReturn($classes);
        $this->cacheManager->method('get')->willReturn(null);

        $this->discoveryBuilder->setStrategy($this->strategy);
        $result = $this->discoveryBuilder->get();

        $this->assertIsArray($result);
        $this->assertEquals($classes, $result);
    }

    /**
     * Test that get uses cache when available.
     */
    public function test_get_uses_cache_when_available(): void
    {
        $cachedClasses = ['CachedClass1', 'CachedClass2'];
        $this->strategy->method('getCacheKey')->willReturn('test-key');
        $this->cacheManager->method('get')->willReturn($cachedClasses);

        $this->discoveryBuilder->setStrategy($this->strategy);
        $result = $this->discoveryBuilder->cached()->get();

        $this->assertEquals($cachedClasses, $result);
    }

    /**
     * Test that get stores in cache when caching is enabled.
     */
    public function test_get_stores_in_cache_when_caching_enabled(): void
    {
        $classes = ['Class1', 'Class2'];
        $this->strategy->method('discover')->willReturn($classes);
        $this->strategy->method('getCacheKey')->willReturn('test-key');
        $this->cacheManager->method('get')->willReturn(null);
        $this
            ->cacheManager
            ->expects($this->once())
            ->method('put')
            ->with('test-key', $classes);

        $this->discoveryBuilder->setStrategy($this->strategy);
        $this->discoveryBuilder->cached()->get();
    }

    /**
     * Test that toArray returns associative array.
     */
    public function test_to_array_returns_associative_array(): void
    {
        $classes = ['Class1', 'Class2'];
        $this->strategy->method('discover')->willReturn($classes);
        $this->strategy->method('getMetadata')->willReturnCallback(
            fn ($class): array => ['class' => $class, 'metadata' => 'test']
        );
        $this->cacheManager->method('get')->willReturn(null);

        $this->discoveryBuilder->setStrategy($this->strategy);
        $result = $this->discoveryBuilder->toArray();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('Class1', $result);
        $this->assertArrayHasKey('Class2', $result);
    }

    /**
     * Test that register executes callback for each class.
     */
    public function test_register_executes_callback_for_each_class(): void
    {
        $classes = ['Class1', 'Class2'];
        $this->strategy->method('discover')->willReturn($classes);
        $this->strategy->method('getMetadata')->willReturnCallback(
            fn ($class): array => ['class' => $class]
        );
        $this->cacheManager->method('get')->willReturn(null);

        $called = [];
        $this->discoveryBuilder->setStrategy($this->strategy);
        $result = $this->discoveryBuilder->register(function ($class, $metadata) use (&$called): void {
            $called[] = $class;
        });

        $this->assertEquals($classes, $called);
        $this->assertEquals($classes, $result);
    }

    /**
     * Test that cached with custom key uses that key.
     */
    public function test_cached_with_custom_key_uses_that_key(): void
    {
        $classes = ['Class1'];
        $this->strategy->method('discover')->willReturn($classes);
        $this->cacheManager->method('get')->willReturn(null);
        $this
            ->cacheManager
            ->expects($this->once())
            ->method('put')
            ->with('custom-key', $classes);

        $this->discoveryBuilder->setStrategy($this->strategy);
        $this->discoveryBuilder->cached('custom-key')->get();
    }
}
