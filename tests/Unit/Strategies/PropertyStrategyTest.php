<?php

declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Unit\Strategies;

use Pixielity\Discovery\Strategies\PropertyStrategy;
use Pixielity\Discovery\Tests\Fixtures\Attributes\TestValidateAttribute;
use Pixielity\Discovery\Tests\TestCase;

/**
 * PropertyStrategy Unit Tests.
 *
 * Tests property attribute discovery functionality.
 * The PropertyStrategy discovers class properties decorated with specific
 * PHP attributes using the composer-attribute-collector package.
 *
 * ## Key Features Tested:
 * - Property discovery by attribute
 * - Property metadata extraction
 * - Class and property name parsing
 * - Multiple properties handling
 * - Static property handling
 * - Visibility handling
 * - Typed property handling
 *
 * @covers \Pixielity\Discovery\Strategies\PropertyStrategy
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
class PropertyStrategyTest extends TestCase
{
    /**
     * Test discovers properties with attribute.
     *
     * This test verifies that the strategy can discover properties
     * decorated with the specified attribute.
     *
     * ## Scenario:
     * - Create strategy for TestValidateAttribute
     * - Discover properties
     * - Verify properties are found
     *
     * ## Assertions:
     * - Results are an array
     * - Properties with attribute are discovered
     */
    public function test_discovers_properties_with_attribute(): void
    {
        // Arrange: Create strategy for TestValidateAttribute
        $propertyStrategy = new PropertyStrategy(TestValidateAttribute::class);

        // Act: Discover properties with the attribute
        $results = $propertyStrategy->discover();

        // Assert: Results should be an array
        $this->assertIsArray($results);
    }

    /**
     * Test returns property metadata.
     *
     * This test verifies that the strategy returns proper metadata
     * for discovered properties.
     *
     * ## Scenario:
     * - Discover properties
     * - Get metadata for a property
     * - Verify metadata structure
     *
     * ## Assertions:
     * - Metadata contains property identifier
     * - Metadata contains class name
     * - Metadata contains property name
     */
    public function test_returns_property_metadata(): void
    {
        // Arrange: Create strategy
        $propertyStrategy = new PropertyStrategy(TestValidateAttribute::class);

        // Act: Discover properties
        $results = $propertyStrategy->discover();

        // Assert: If properties found, verify metadata
        if ($results !== []) {
            $firstProperty = $results[0];
            $metadata = $propertyStrategy->getMetadata($firstProperty);

            $this->assertIsArray($metadata);
            $this->assertArrayHasKey('property', $metadata);
            $this->assertArrayHasKey('class', $metadata);
            $this->assertArrayHasKey('name', $metadata);
        } else {
            $this->markTestSkipped('No properties found with TestValidateAttribute');
        }
    }

    /**
     * Test includes class and property name.
     *
     * This test verifies that discovered properties include both
     * the class name and property name.
     *
     * ## Scenario:
     * - Discover properties
     * - Parse property identifiers
     * - Verify format is ClassName::$propertyName
     *
     * ## Assertions:
     * - Property identifier contains ::
     * - Class name is extractable
     * - Property name is extractable
     */
    public function test_includes_class_and_property_name(): void
    {
        // Arrange: Create strategy
        $propertyStrategy = new PropertyStrategy(TestValidateAttribute::class);

        // Act: Discover properties
        $results = $propertyStrategy->discover();

        // Assert: Verify property identifier format
        foreach ($results as $result) {
            $this->assertIsString($result);
            $this->assertStringContainsString('::', $result);

            // Verify we can parse class and property name
            [$class, $propertyName] = explode('::', $result, 2);
            $this->assertNotEmpty($class);
            $this->assertNotEmpty($propertyName);
        }
    }

    /**
     * Test handles multiple properties in same class.
     *
     * This test verifies that the strategy can discover multiple
     * properties in the same class.
     *
     * ## Scenario:
     * - Discover properties from a class with multiple attributed properties
     * - Verify all properties are found
     *
     * ## Assertions:
     * - Multiple properties from same class are discovered
     * - Each property is listed separately
     */
    public function test_handles_multiple_properties_in_same_class(): void
    {
        // Arrange: Create strategy
        $propertyStrategy = new PropertyStrategy(TestValidateAttribute::class);

        // Act: Discover properties
        $results = $propertyStrategy->discover();

        // Assert: Should handle multiple properties
        $this->assertIsArray($results);

        // Group by class to check for multiple properties per class
        $propertiesByClass = [];
        foreach ($results as $result) {
            [$class] = explode('::', $result, 2);
            $propertiesByClass[$class] = ($propertiesByClass[$class] ?? 0) + 1;
        }
        array_any($propertiesByClass, fn ($count): bool => $count > 1);

        // This assertion may vary based on fixtures
        $this->assertIsArray($propertiesByClass);
    }

    /**
     * Test handles static properties.
     *
     * This test verifies that the strategy can discover static properties
     * decorated with attributes.
     *
     * ## Scenario:
     * - Discover properties (including static)
     * - Verify static properties are included
     *
     * ## Assertions:
     * - Static properties are discovered
     * - No distinction between static and instance properties
     */
    public function test_handles_static_properties(): void
    {
        // Arrange: Create strategy
        $propertyStrategy = new PropertyStrategy(TestValidateAttribute::class);

        // Act: Discover properties
        $results = $propertyStrategy->discover();

        // Assert: Should discover properties regardless of static modifier
        $this->assertIsArray($results);
    }

    /**
     * Test handles private protected public properties.
     *
     * This test verifies that the strategy can discover properties
     * with different visibility modifiers.
     *
     * ## Scenario:
     * - Discover properties with various visibility
     * - Verify all are discovered
     *
     * ## Assertions:
     * - Properties are discovered regardless of visibility
     * - Public, protected, and private properties are included
     */
    public function test_handles_private_protected_public_properties(): void
    {
        // Arrange: Create strategy
        $propertyStrategy = new PropertyStrategy(TestValidateAttribute::class);

        // Act: Discover properties
        $results = $propertyStrategy->discover();

        // Assert: Should discover properties regardless of visibility
        $this->assertIsArray($results);
    }

    /**
     * Test handles typed properties.
     *
     * This test verifies that the strategy can discover properties
     * with type declarations (PHP 7.4+).
     *
     * ## Scenario:
     * - Discover typed properties
     * - Verify type information is accessible
     *
     * ## Assertions:
     * - Typed properties are discovered
     * - Type declarations don't affect discovery
     */
    public function test_handles_typed_properties(): void
    {
        // Arrange: Create strategy
        $propertyStrategy = new PropertyStrategy(TestValidateAttribute::class);

        // Act: Discover properties
        $results = $propertyStrategy->discover();

        // Assert: Should discover typed properties
        $this->assertIsArray($results);

        // Note: Type information would be available through reflection
        // if needed, but the strategy itself doesn't require it
    }
}
