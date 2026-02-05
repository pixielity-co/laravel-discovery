<?php

namespace Fulers\Discovery\Tests\Unit\Strategies;

use Attribute;
use Fulers\Discovery\Strategies\AttributeStrategy;
use Olvlvl\ComposerAttributeCollector\Attributes;
use Override;
use PHPUnit\Framework\TestCase;

/**
 * AttributeStrategyTest - Tests for AttributeStrategy class.
 *
 * @covers \Fulers\Discovery\Strategies\AttributeStrategy
 */
class AttributeStrategyTest extends TestCase
{
    /**
     * Test that strategy can be instantiated with attribute class.
     */
    public function test_can_instantiate_with_attribute_class(): void
    {
        $attributeStrategy = new AttributeStrategy(Attribute::class);

        $this->assertInstanceOf(AttributeStrategy::class, $attributeStrategy);
    }

    /**
     * Test that discover returns empty array when composer-attribute-collector is not available.
     */
    public function test_discover_returns_empty_array_when_collector_not_available(): void
    {
        if (class_exists(Attributes::class)) {
            $this->markTestSkipped('composer-attribute-collector is installed');
        }

        $attributeStrategy = new AttributeStrategy(Attribute::class);
        $result = $attributeStrategy->discover();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test that discover returns array of classes when collector is available.
     */
    public function test_discover_returns_classes_when_collector_available(): void
    {
        if (! class_exists(Attributes::class)) {
            $this->markTestSkipped('composer-attribute-collector is not installed');
        }

        $attributeStrategy = new AttributeStrategy(Attribute::class);
        $result = $attributeStrategy->discover();

        $this->assertIsArray($result);
        // Result may be empty if no classes have the attribute
    }

    /**
     * Test that getMetadata returns correct structure.
     */
    public function test_get_metadata_returns_correct_structure(): void
    {
        $attributeStrategy = new AttributeStrategy(Attribute::class);
        $metadata = $attributeStrategy->getMetadata('TestClass');

        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('class', $metadata);
        $this->assertArrayHasKey('attribute', $metadata);
        $this->assertEquals('TestClass', $metadata['class']);
        $this->assertNull($metadata['attribute']);  // No target found
    }

    /**
     * Test that getMetadata includes attribute instance when available.
     */
    public function test_get_metadata_includes_instance_when_available(): void
    {
        if (! class_exists(Attributes::class)) {
            $this->markTestSkipped('composer-attribute-collector is not installed');
        }

        $attributeStrategy = new AttributeStrategy(Attribute::class);
        $classes = $attributeStrategy->discover();

        if ($classes === []) {
            $this->markTestSkipped('No classes found with attribute');
        }

        $metadata = $attributeStrategy->getMetadata($classes[0]);

        $this->assertArrayHasKey('instance', $metadata);
    }

    /**
     * Test that getCacheKey returns consistent key.
     */
    public function test_get_cache_key_returns_consistent_key(): void
    {
        $attributeStrategy = new AttributeStrategy(Attribute::class);
        $key1 = $attributeStrategy->getCacheKey();
        $key2 = $attributeStrategy->getCacheKey();

        $this->assertEquals($key1, $key2);
        $this->assertStringStartsWith('attribute:', $key1);
    }

    /**
     * Test that getCacheKey is unique per attribute class.
     */
    public function test_get_cache_key_is_unique_per_attribute(): void
    {
        $strategy1 = new AttributeStrategy(Attribute::class);
        $strategy2 = new AttributeStrategy(Override::class);

        $key1 = $strategy1->getCacheKey();
        $key2 = $strategy2->getCacheKey();

        $this->assertNotEquals($key1, $key2);
    }

    /**
     * Test that discover handles exceptions gracefully.
     */
    public function test_discover_handles_exceptions_gracefully(): void
    {
        $attributeStrategy = new AttributeStrategy('NonExistentAttribute');
        $result = $attributeStrategy->discover();

        $this->assertIsArray($result);
        // Should not throw exception
    }

    /**
     * Test that getMetadata handles exceptions gracefully.
     */
    public function test_get_metadata_handles_exceptions_gracefully(): void
    {
        $attributeStrategy = new AttributeStrategy('NonExistentAttribute');
        $metadata = $attributeStrategy->getMetadata('NonExistentClass');

        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('class', $metadata);
        $this->assertArrayHasKey('attribute', $metadata);
        // Should not throw exception
    }
}
