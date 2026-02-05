<?php

namespace Fulers\Discovery\Tests\Unit\Strategies;

use Countable;
use Fulers\Discovery\Contracts\StrategyFactoryInterface;
use Fulers\Discovery\Strategies\DirectoryStrategy;
use Fulers\Discovery\Strategies\InterfaceStrategy;
use Iterator;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * InterfaceStrategyTest - Tests for InterfaceStrategy class.
 *
 * @covers \Fulers\Discovery\Strategies\InterfaceStrategy
 */
class InterfaceStrategyTest extends TestCase
{
    /**
     * Test that strategy can be instantiated with interface name.
     */
    public function test_can_instantiate_with_interface_name(): void
    {
        $interfaceStrategy = new InterfaceStrategy(Countable::class);

        $this->assertInstanceOf(InterfaceStrategy::class, $interfaceStrategy);
    }

    /**
     * Test that strategy can be instantiated with factory.
     */
    public function test_can_instantiate_with_factory(): void
    {
        $factory = $this->createMock(StrategyFactoryInterface::class);
        $interfaceStrategy = new InterfaceStrategy(Countable::class, $factory);

        $this->assertInstanceOf(InterfaceStrategy::class, $interfaceStrategy);
    }

    /**
     * Test that discover returns array of classes.
     */
    public function test_discover_returns_array_of_classes(): void
    {
        $interfaceStrategy = new InterfaceStrategy(Countable::class);
        $result = $interfaceStrategy->discover();

        $this->assertIsArray($result);
    }

    /**
     * Test that discover filters by interface.
     */
    public function test_discover_filters_by_interface(): void
    {
        $interfaceStrategy = new InterfaceStrategy(Countable::class);
        $result = $interfaceStrategy->discover();

        foreach ($result as $class) {
            $this->assertTrue(
                is_subclass_of($class, Countable::class),
                "Class {$class} does not implement Countable"
            );
        }
    }

    /**
     * Test that directories throws exception without factory.
     */
    public function test_directories_throws_exception_without_factory(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot use directories() without a StrategyFactory');

        $interfaceStrategy = new InterfaceStrategy(Countable::class);
        $interfaceStrategy->directories('src');
    }

    /**
     * Test that directories works with factory.
     */
    public function test_directories_works_with_factory(): void
    {
        $factory = $this->createMock(StrategyFactoryInterface::class);
        $directoryStrategy = $this->createMock(DirectoryStrategy::class);

        $factory
            ->expects($this->once())
            ->method('createDirectoryStrategy')
            ->with('src')
            ->willReturn($directoryStrategy);

        $interfaceStrategy = new InterfaceStrategy(Countable::class, $factory);
        $interfaceStrategy->directories('src');

        $this->assertTrue(true);
    }

    /**
     * Test that getMetadata returns correct structure.
     */
    public function test_get_metadata_returns_correct_structure(): void
    {
        $interfaceStrategy = new InterfaceStrategy(Countable::class);
        $metadata = $interfaceStrategy->getMetadata('TestClass');

        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('class', $metadata);
        $this->assertArrayHasKey('interface', $metadata);
        $this->assertEquals('TestClass', $metadata['class']);
        $this->assertEquals(Countable::class, $metadata['interface']);
    }

    /**
     * Test that getCacheKey returns consistent key.
     */
    public function test_get_cache_key_returns_consistent_key(): void
    {
        $interfaceStrategy = new InterfaceStrategy(Countable::class);
        $key1 = $interfaceStrategy->getCacheKey();
        $key2 = $interfaceStrategy->getCacheKey();

        $this->assertEquals($key1, $key2);
        $this->assertStringStartsWith('interface:', $key1);
    }

    /**
     * Test that getCacheKey is unique per interface.
     */
    public function test_get_cache_key_is_unique_per_interface(): void
    {
        $strategy1 = new InterfaceStrategy(Countable::class);
        $strategy2 = new InterfaceStrategy(Iterator::class);

        $key1 = $strategy1->getCacheKey();
        $key2 = $strategy2->getCacheKey();

        $this->assertNotEquals($key1, $key2);
    }

    /**
     * Test that discover handles exceptions gracefully.
     */
    public function test_discover_handles_exceptions_gracefully(): void
    {
        $interfaceStrategy = new InterfaceStrategy('NonExistentInterface');
        $result = $interfaceStrategy->discover();

        $this->assertIsArray($result);
        // Should not throw exception
    }
}
