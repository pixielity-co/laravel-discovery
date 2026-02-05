<?php

namespace Fulers\Discovery\Tests\Unit\Strategies;

use Exception;
use Fulers\Discovery\Contracts\StrategyFactoryInterface;
use Fulers\Discovery\Strategies\DirectoryStrategy;
use Fulers\Discovery\Strategies\ParentClassStrategy;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * ParentClassStrategyTest - Tests for ParentClassStrategy class.
 *
 * @covers \Fulers\Discovery\Strategies\ParentClassStrategy
 */
class ParentClassStrategyTest extends TestCase
{
    /**
     * Test that strategy can be instantiated with parent class name.
     */
    public function test_can_instantiate_with_parent_class_name(): void
    {
        $parentClassStrategy = new ParentClassStrategy(Exception::class);

        $this->assertInstanceOf(ParentClassStrategy::class, $parentClassStrategy);
    }

    /**
     * Test that strategy can be instantiated with factory.
     */
    public function test_can_instantiate_with_factory(): void
    {
        $factory = $this->createMock(StrategyFactoryInterface::class);
        $parentClassStrategy = new ParentClassStrategy(Exception::class, $factory);

        $this->assertInstanceOf(ParentClassStrategy::class, $parentClassStrategy);
    }

    /**
     * Test that discover returns array of classes.
     */
    public function test_discover_returns_array_of_classes(): void
    {
        $parentClassStrategy = new ParentClassStrategy(Exception::class);
        $result = $parentClassStrategy->discover();

        $this->assertIsArray($result);
    }

    /**
     * Test that discover filters by parent class.
     */
    public function test_discover_filters_by_parent_class(): void
    {
        $parentClassStrategy = new ParentClassStrategy(Exception::class);
        $result = $parentClassStrategy->discover();

        foreach ($result as $class) {
            $this->assertTrue(
                is_subclass_of($class, Exception::class),
                "Class {$class} does not extend Exception"
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

        $parentClassStrategy = new ParentClassStrategy(Exception::class);
        $parentClassStrategy->directories('src');
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

        $parentClassStrategy = new ParentClassStrategy(Exception::class, $factory);
        $parentClassStrategy->directories('src');

        $this->assertTrue(true);
    }

    /**
     * Test that getMetadata returns correct structure.
     */
    public function test_get_metadata_returns_correct_structure(): void
    {
        $parentClassStrategy = new ParentClassStrategy(Exception::class);
        $metadata = $parentClassStrategy->getMetadata('TestClass');

        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('class', $metadata);
        $this->assertArrayHasKey('parent', $metadata);
        $this->assertEquals('TestClass', $metadata['class']);
        $this->assertEquals(Exception::class, $metadata['parent']);
    }

    /**
     * Test that getCacheKey returns consistent key.
     */
    public function test_get_cache_key_returns_consistent_key(): void
    {
        $parentClassStrategy = new ParentClassStrategy(Exception::class);
        $key1 = $parentClassStrategy->getCacheKey();
        $key2 = $parentClassStrategy->getCacheKey();

        $this->assertEquals($key1, $key2);
        $this->assertStringStartsWith('parent:', $key1);
    }

    /**
     * Test that getCacheKey is unique per parent class.
     */
    public function test_get_cache_key_is_unique_per_parent_class(): void
    {
        $strategy1 = new ParentClassStrategy(Exception::class);
        $strategy2 = new ParentClassStrategy(RuntimeException::class);

        $key1 = $strategy1->getCacheKey();
        $key2 = $strategy2->getCacheKey();

        $this->assertNotEquals($key1, $key2);
    }

    /**
     * Test that discover handles exceptions gracefully.
     */
    public function test_discover_handles_exceptions_gracefully(): void
    {
        $parentClassStrategy = new ParentClassStrategy('NonExistentClass');
        $result = $parentClassStrategy->discover();

        $this->assertIsArray($result);
        // Should not throw exception
    }
}
