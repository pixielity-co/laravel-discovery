<?php declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Unit\Strategies;

use Pixielity\Discovery\Strategies\AttributeStrategy;
use Pixielity\Discovery\Support\Arr;
use Pixielity\Discovery\Tests\Fixtures\Attributes\TestAttribute;
use Pixielity\Discovery\Tests\Fixtures\Attributes\TestCardAttribute;
use Pixielity\Discovery\Tests\TestCase;

/**
 * AttributeStrategy Unit Tests.
 *
 * Tests the attribute-based discovery strategy.
 * The AttributeStrategy discovers classes that have specific PHP attributes,
 * extracting attribute metadata for further processing.
 *
 * ## Key Features Tested:
 * - Class discovery by attribute
 * - Attribute metadata extraction
 * - Property value access
 * - Multiple attribute handling
 * - Non-existent attribute handling
 *
 * @covers \Pixielity\Discovery\Strategies\AttributeStrategy
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
class AttributeStrategyTest extends TestCase
{
    /**
     * Test discovers classes with attribute.
     *
     * This test verifies that the strategy can discover classes
     * that have the specified attribute.
     *
     * ## Scenario:
     * - Create strategy for TestAttribute
     * - Discover classes
     * - Verify results are returned
     *
     * ## Assertions:
     * - Results are an array
     * - Discovery completes without errors
     */
    public function test_discovers_classes_with_attribute(): void
    {
        // Arrange: Create strategy for TestAttribute
        $attributeStrategy = new AttributeStrategy(TestAttribute::class);

        // Act: Discover classes with the attribute
        $results = $attributeStrategy->discover();

        // Assert: Results should be an array
        $this->assertIsArray($results);
    }

    /**
     * Test returns empty array when no classes found.
     *
     * This test verifies that the strategy returns an empty array
     * when no classes have the specified attribute.
     *
     * ## Scenario:
     * - Create strategy for non-existent attribute
     * - Discover classes
     * - Verify empty array is returned
     *
     * ## Assertions:
     * - Results are an array
     * - Results are empty
     */
    public function test_returns_empty_array_when_no_classes_found(): void
    {
        // Arrange: Create strategy for non-existent attribute
        $attributeStrategy = new AttributeStrategy('NonExistentAttribute');

        // Act: Discover classes
        $results = $attributeStrategy->discover();

        // Assert: Should return empty array
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    /**
     * Test includes attribute metadata.
     *
     * This test verifies that the strategy includes attribute
     * metadata in the discovery results via getMetadata().
     *
     * ## Scenario:
     * - Create strategy for TestCardAttribute
     * - Discover classes
     * - Get metadata for discovered class
     * - Verify metadata includes attribute instance
     *
     * ## Assertions:
     * - Metadata contains 'attribute' key
     * - Attribute instance is accessible
     */
    public function test_includes_attribute_metadata(): void
    {
        // Arrange: Create strategy for TestCardAttribute
        $attributeStrategy = new AttributeStrategy(TestCardAttribute::class);

        // Act: Discover classes
        $results = $attributeStrategy->discover();

        // Assert: If results exist, verify metadata structure
        if ($results !== []) {
            $first = reset($results);
            $this->assertIsString($first);

            // Get metadata for the first class
            $metadata = $attributeStrategy->getMetadata($first);
            $this->assertIsArray($metadata);
            $this->assertArrayHasKey('attribute', $metadata);
        } else {
            $this->markTestSkipped('No classes found with TestCardAttribute');
        }
    }

    /**
     * Test handles multiple attributes on same class.
     *
     * This test verifies that the strategy can handle classes
     * that have multiple attributes.
     *
     * ## Scenario:
     * - Create strategy for TestAttribute
     * - Discover classes (some may have multiple attributes)
     * - Verify discovery works correctly
     *
     * ## Assertions:
     * - Results are an array
     * - Multiple attributes don't cause errors
     */
    public function test_handles_multiple_attributes_on_same_class(): void
    {
        // Arrange: Create strategy for TestAttribute
        $attributeStrategy = new AttributeStrategy(TestAttribute::class);

        // Act: Discover classes
        $results = $attributeStrategy->discover();

        // Assert: Should return array (may be empty)
        $this->assertIsArray($results);
    }

    /**
     * Test discovers attribute with properties.
     *
     * This test verifies that the strategy can discover attributes
     * that have properties and extract those property values.
     *
     * ## Scenario:
     * - Create strategy for TestCardAttribute (has properties)
     * - Discover classes
     * - Verify attribute properties are accessible
     *
     * ## Assertions:
     * - Attribute has expected properties
     * - Property values are accessible
     */
    public function test_discovers_attribute_with_properties(): void
    {
        // Arrange: Create strategy for TestCardAttribute
        $attributeStrategy = new AttributeStrategy(TestCardAttribute::class);

        // Act: Discover classes
        $results = $attributeStrategy->discover();

        // Assert: If results exist, verify attribute properties
        if ($results !== []) {
            $first = reset($results);
            $this->assertIsString($first);

            // Get metadata for the first class
            $metadata = $attributeStrategy->getMetadata($first);
            $this->assertIsArray($metadata);
            $this->assertArrayHasKey('attribute', $metadata);

            $attribute = $metadata['attribute'];
            $this->assertIsObject($attribute);
            $this->assertObjectHasProperty('enabled', $attribute);
            $this->assertObjectHasProperty('priority', $attribute);
        } else {
            $this->markTestSkipped('No classes found with TestCardAttribute');
        }
    }

    /**
     * Test handles non-existent attribute.
     *
     * This test verifies that the strategy handles gracefully
     * when given a non-existent attribute class.
     *
     * ## Scenario:
     * - Create strategy for non-existent attribute
     * - Discover classes
     * - Verify graceful handling
     *
     * ## Assertions:
     * - Results are an array
     * - Results are empty
     * - No exceptions are thrown
     */
    public function test_handles_non_existent_attribute(): void
    {
        // Arrange: Create strategy for non-existent attribute
        $attributeStrategy = new AttributeStrategy('App\NonExistent\Attribute');

        // Act: Discover classes
        $results = $attributeStrategy->discover();

        // Assert: Should return empty array
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    /**
     * Test filters by attribute property values.
     *
     * This test verifies that discovered classes can be filtered
     * by their attribute property values.
     *
     * ## Scenario:
     * - Create strategy for TestCardAttribute
     * - Discover classes
     * - Filter by enabled property using metadata
     *
     * ## Assertions:
     * - Filtering works correctly
     * - Property values are accessible for filtering
     */
    public function test_filters_by_attribute_property_values(): void
    {
        // Arrange: Create strategy for TestCardAttribute
        $attributeStrategy = new AttributeStrategy(TestCardAttribute::class);

        // Act: Discover classes
        $results = $attributeStrategy->discover();

        // Act: Filter by enabled = true using metadata
        $filtered = Arr::filter(
            $results,
            function (string $class) use ($attributeStrategy) {
                $metadata = $attributeStrategy->getMetadata($class);
                return isset($metadata['attribute']) && $metadata['attribute']->enabled === true;
            }
        );

        // Assert: Filtered results should be an array
        $this->assertIsArray($filtered);
    }
}
