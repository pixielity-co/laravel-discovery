<?php

namespace Fulers\Discovery\Tests\Unit\Strategies;

use Attribute;
use Fulers\Discovery\Strategies\PropertyStrategy;
use Olvlvl\ComposerAttributeCollector\Attributes;
use Override;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * PropertyStrategyTest - Tests for PropertyStrategy class.
 *
 * @covers \Fulers\Discovery\Strategies\PropertyStrategy
 */
class PropertyStrategyTest extends TestCase
{
    /**
     * Test that strategy can be instantiated with attribute class.
     */
    public function test_can_instantiate_with_attribute_class(): void
    {
        $propertyStrategy = new PropertyStrategy(Attribute::class);

        $this->assertInstanceOf(PropertyStrategy::class, $propertyStrategy);
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

        $propertyStrategy = new PropertyStrategy(Attribute::class);
        $propertyStrategy->discover();
    }

    /**
     * Test that discover returns array of property identifiers.
     */
    public function test_discover_returns_property_identifiers(): void
    {
        if (! class_exists(Attributes::class)) {
            $this->markTestSkipped('composer-attribute-collector is not installed');
        }

        $propertyStrategy = new PropertyStrategy(Attribute::class);
        $result = $propertyStrategy->discover();

        $this->assertIsArray($result);

        foreach ($result as $property) {
            $this->assertIsString($property);
            $this->assertStringContainsString('::', $property);
            $this->assertStringContainsString('$', $property);
        }
    }

    /**
     * Test that property identifiers are in correct format.
     */
    public function test_property_identifiers_have_correct_format(): void
    {
        if (! class_exists(Attributes::class)) {
            $this->markTestSkipped('composer-attribute-collector is not installed');
        }

        $propertyStrategy = new PropertyStrategy(Attribute::class);
        $result = $propertyStrategy->discover();

        if ($result === []) {
            $this->markTestSkipped('No properties found with attribute');
        }

        $property = $result[0];
        $parts = explode('::', $property);

        $this->assertCount(2, $parts);
        $this->assertNotEmpty($parts[0]);  // Class name
        $this->assertStringStartsWith('$', $parts[1]);  // Property name with $
    }

    /**
     * Test that getMetadata returns correct structure.
     */
    public function test_get_metadata_returns_correct_structure(): void
    {
        $propertyStrategy = new PropertyStrategy(Attribute::class);
        $metadata = $propertyStrategy->getMetadata('TestClass::$testProperty');

        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('property', $metadata);
        $this->assertArrayHasKey('class', $metadata);
        $this->assertArrayHasKey('name', $metadata);
        $this->assertArrayHasKey('attribute', $metadata);
        $this->assertEquals('TestClass::$testProperty', $metadata['property']);
        $this->assertEquals('TestClass', $metadata['class']);
        $this->assertEquals('testProperty', $metadata['name']);  // Without $
        $this->assertEquals(Attribute::class, $metadata['attribute']);
    }

    /**
     * Test that getMetadata strips dollar sign from property name.
     */
    public function test_get_metadata_strips_dollar_sign_from_property_name(): void
    {
        $propertyStrategy = new PropertyStrategy(Attribute::class);

        $metadata1 = $propertyStrategy->getMetadata('TestClass::$property');
        $metadata2 = $propertyStrategy->getMetadata('TestClass::property');

        $this->assertEquals('property', $metadata1['name']);
        $this->assertEquals('property', $metadata2['name']);
    }

    /**
     * Test that getMetadata includes attribute instance when available.
     */
    public function test_get_metadata_includes_instance_when_available(): void
    {
        if (! class_exists(Attributes::class)) {
            $this->markTestSkipped('composer-attribute-collector is not installed');
        }

        $propertyStrategy = new PropertyStrategy(Attribute::class);
        $properties = $propertyStrategy->discover();

        if ($properties === []) {
            $this->markTestSkipped('No properties found with attribute');
        }

        $metadata = $propertyStrategy->getMetadata($properties[0]);

        $this->assertArrayHasKey('instance', $metadata);
    }

    /**
     * Test that getCacheKey returns consistent key.
     */
    public function test_get_cache_key_returns_consistent_key(): void
    {
        $propertyStrategy = new PropertyStrategy(Attribute::class);
        $key1 = $propertyStrategy->getCacheKey();
        $key2 = $propertyStrategy->getCacheKey();

        $this->assertEquals($key1, $key2);
        $this->assertStringStartsWith('property:', $key1);
    }

    /**
     * Test that getCacheKey is unique per attribute class.
     */
    public function test_get_cache_key_is_unique_per_attribute(): void
    {
        $strategy1 = new PropertyStrategy(Attribute::class);
        $strategy2 = new PropertyStrategy(Override::class);

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

        $propertyStrategy = new PropertyStrategy('NonExistentAttribute');
        $result = $propertyStrategy->discover();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test that getMetadata handles exceptions gracefully.
     */
    public function test_get_metadata_handles_exceptions_gracefully(): void
    {
        $propertyStrategy = new PropertyStrategy('NonExistentAttribute');
        $metadata = $propertyStrategy->getMetadata('NonExistentClass::$nonExistentProperty');

        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('property', $metadata);
        $this->assertArrayHasKey('class', $metadata);
        $this->assertArrayHasKey('name', $metadata);
        $this->assertArrayHasKey('attribute', $metadata);
    }

    /**
     * Test that getMetadata parses property identifier correctly.
     */
    public function test_get_metadata_parses_property_identifier_correctly(): void
    {
        $propertyStrategy = new PropertyStrategy(Attribute::class);

        $metadata = $propertyStrategy->getMetadata('App\Models\User::$email');

        $this->assertEquals('App\Models\User', $metadata['class']);
        $this->assertEquals('email', $metadata['name']);
    }
}
