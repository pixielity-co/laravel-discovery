<?php

namespace Fulers\Discovery\Tests\Unit\Strategies;

use Attribute;
use Fulers\Discovery\Strategies\MethodStrategy;
use Olvlvl\ComposerAttributeCollector\Attributes;
use Override;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * MethodStrategyTest - Tests for MethodStrategy class.
 *
 * @covers \Fulers\Discovery\Strategies\MethodStrategy
 */
class MethodStrategyTest extends TestCase
{
    /**
     * Test that strategy can be instantiated with attribute class.
     */
    public function test_can_instantiate_with_attribute_class(): void
    {
        $methodStrategy = new MethodStrategy(Attribute::class);

        $this->assertInstanceOf(MethodStrategy::class, $methodStrategy);
    }

    /**
     * Test that discover throws exception when composer-attribute-collector is not available.
     */
    public function test_discover_throws_exception_when_collector_not_available(): void
    {
        if (class_exists(Attributes::class)) {
            $this->markTestSkipped('composer-attribute-collector is installed');
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('composer-attribute-collector package is required');

        $methodStrategy = new MethodStrategy(Attribute::class);
        $methodStrategy->discover();
    }

    /**
     * Test that discover returns array of method identifiers.
     */
    public function test_discover_returns_method_identifiers(): void
    {
        if (! class_exists(Attributes::class)) {
            $this->markTestSkipped('composer-attribute-collector is not installed');
        }

        $methodStrategy = new MethodStrategy(Attribute::class);
        $result = $methodStrategy->discover();

        $this->assertIsArray($result);

        foreach ($result as $method) {
            $this->assertIsString($method);
            $this->assertStringContainsString('::', $method);
        }
    }

    /**
     * Test that method identifiers are in correct format.
     */
    public function test_method_identifiers_have_correct_format(): void
    {
        if (! class_exists(Attributes::class)) {
            $this->markTestSkipped('composer-attribute-collector is not installed');
        }

        $methodStrategy = new MethodStrategy(Attribute::class);
        $result = $methodStrategy->discover();

        if ($result === []) {
            $this->markTestSkipped('No methods found with attribute');
        }

        $method = $result[0];
        $parts = explode('::', $method);

        $this->assertCount(2, $parts);
        $this->assertNotEmpty($parts[0]);  // Class name
        $this->assertNotEmpty($parts[1]);  // Method name
    }

    /**
     * Test that getMetadata returns correct structure.
     */
    public function test_get_metadata_returns_correct_structure(): void
    {
        $methodStrategy = new MethodStrategy(Attribute::class);
        $metadata = $methodStrategy->getMetadata('TestClass::testMethod');

        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('method', $metadata);
        $this->assertArrayHasKey('class', $metadata);
        $this->assertArrayHasKey('name', $metadata);
        $this->assertArrayHasKey('attribute', $metadata);
        $this->assertEquals('TestClass::testMethod', $metadata['method']);
        $this->assertEquals('TestClass', $metadata['class']);
        $this->assertEquals('testMethod', $metadata['name']);
        $this->assertEquals(Attribute::class, $metadata['attribute']);
    }

    /**
     * Test that getMetadata includes attribute instance when available.
     */
    public function test_get_metadata_includes_instance_when_available(): void
    {
        if (! class_exists(Attributes::class)) {
            $this->markTestSkipped('composer-attribute-collector is not installed');
        }

        $methodStrategy = new MethodStrategy(Attribute::class);
        $methods = $methodStrategy->discover();

        if ($methods === []) {
            $this->markTestSkipped('No methods found with attribute');
        }

        $metadata = $methodStrategy->getMetadata($methods[0]);

        $this->assertArrayHasKey('instance', $metadata);
    }

    /**
     * Test that getCacheKey returns consistent key.
     */
    public function test_get_cache_key_returns_consistent_key(): void
    {
        $methodStrategy = new MethodStrategy(Attribute::class);
        $key1 = $methodStrategy->getCacheKey();
        $key2 = $methodStrategy->getCacheKey();

        $this->assertEquals($key1, $key2);
        $this->assertStringStartsWith('method:', $key1);
    }

    /**
     * Test that getCacheKey is unique per attribute class.
     */
    public function test_get_cache_key_is_unique_per_attribute(): void
    {
        $strategy1 = new MethodStrategy(Attribute::class);
        $strategy2 = new MethodStrategy(Override::class);

        $key1 = $strategy1->getCacheKey();
        $key2 = $strategy2->getCacheKey();

        $this->assertNotEquals($key1, $key2);
    }

    /**
     * Test that discover handles exceptions gracefully.
     */
    public function test_discover_handles_exceptions_gracefully(): void
    {
        if (! class_exists(Attributes::class)) {
            $this->markTestSkipped('composer-attribute-collector is not installed');
        }

        $methodStrategy = new MethodStrategy('NonExistentAttribute');
        $result = $methodStrategy->discover();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test that getMetadata handles exceptions gracefully.
     */
    public function test_get_metadata_handles_exceptions_gracefully(): void
    {
        $methodStrategy = new MethodStrategy('NonExistentAttribute');
        $metadata = $methodStrategy->getMetadata('NonExistentClass::nonExistentMethod');

        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('method', $metadata);
        $this->assertArrayHasKey('class', $metadata);
        $this->assertArrayHasKey('name', $metadata);
        $this->assertArrayHasKey('attribute', $metadata);
    }

    /**
     * Test that getMetadata parses method identifier correctly.
     */
    public function test_get_metadata_parses_method_identifier_correctly(): void
    {
        $methodStrategy = new MethodStrategy(Attribute::class);

        $metadata = $methodStrategy->getMetadata('App\Controllers\UserController::index');

        $this->assertEquals('App\Controllers\UserController', $metadata['class']);
        $this->assertEquals('index', $metadata['name']);
    }
}
