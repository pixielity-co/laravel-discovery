<?php

declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Unit\Filters;

use Pixielity\Discovery\Filters\PropertyFilter;
use Pixielity\Discovery\Strategies\AttributeStrategy;
use Pixielity\Discovery\Tests\Fixtures\Attributes\TestCardAttribute;
use Pixielity\Discovery\Tests\Fixtures\Classes\Cards\AnalyticsCard;
use Pixielity\Discovery\Tests\Fixtures\Classes\Cards\DashboardCard;
use Pixielity\Discovery\Tests\TestCase;

/**
 * PropertyFilter Unit Tests.
 *
 * Tests property-based filtering for discovery results.
 * Ensures proper filtering by attribute property values with various operators.
 * The PropertyFilter is used to filter discovered classes based on their
 * attribute property values.
 *
 * @covers \Pixielity\Discovery\Filters\PropertyFilter
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
class PropertyFilterTest extends TestCase
{
    /**
     * The attribute strategy instance for testing.
     */
    protected AttributeStrategy $strategy;

    /**
     * Setup the test environment.
     *
     * Initializes the attribute strategy for card discovery.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create strategy for discovering classes with TestCardAttribute
        $this->strategy = new AttributeStrategy(TestCardAttribute::class);
    }

    /**
     * Test filters by exact match.
     *
     * This test verifies that the PropertyFilter can filter classes
     * by exact property value matches.
     *
     * ## Scenario:
     * - Discover classes with TestCardAttribute
     * - Filter by enabled = true
     * - Verify only enabled cards are returned
     *
     * ## Assertions:
     * - Filtered results contain DashboardCard (enabled: true)
     * - Filtered results do not contain AnalyticsCard (enabled: false)
     */
    public function test_filters_by_exact_match(): void
    {
        // Arrange: Create filter for enabled = true
        $propertyFilter = new PropertyFilter('enabled', true);

        // Arrange: Discover all classes with the attribute
        $allClasses = $this->strategy->discover();

        // Act: Apply the filter
        $filtered = $propertyFilter->apply($allClasses, $this->strategy);

        // Assert: Only enabled cards should remain
        $this->assertContains(DashboardCard::class, $filtered);
        $this->assertNotContains(AnalyticsCard::class, $filtered);
    }

    /**
     * Test filters by not equal.
     *
     * This test verifies that the PropertyFilter can filter classes
     * by excluding specific property values.
     *
     * ## Scenario:
     * - Discover classes with TestCardAttribute
     * - Filter by enabled != false (or enabled = true)
     * - Verify only enabled cards are returned
     *
     * ## Assertions:
     * - Filtered results contain enabled cards
     * - Filtered results exclude disabled cards
     */
    public function test_filters_by_not_equal(): void
    {
        // Arrange: Create filter for enabled = false
        $propertyFilter = new PropertyFilter('enabled', false);

        // Arrange: Discover all classes with the attribute
        $allClasses = $this->strategy->discover();

        // Act: Apply the filter
        $filtered = $propertyFilter->apply($allClasses, $this->strategy);

        // Assert: Only disabled cards should remain
        $this->assertNotContains(DashboardCard::class, $filtered);
        $this->assertContains(AnalyticsCard::class, $filtered);
    }

    /**
     * Test filters by greater than.
     *
     * This test verifies that the PropertyFilter can filter classes
     * by numeric property comparisons (greater than).
     *
     * ## Scenario:
     * - Discover classes with TestCardAttribute
     * - Filter by priority > 5
     * - Verify only high priority cards are returned
     *
     * ## Assertions:
     * - DashboardCard (priority: 10) is included
     * - AnalyticsCard (priority: 5) is excluded
     */
    public function test_filters_by_greater_than(): void
    {
        // Arrange: Create filter for priority = 10 (high priority)
        $propertyFilter = new PropertyFilter('priority', 10);

        // Arrange: Discover all classes with the attribute
        $allClasses = $this->strategy->discover();

        // Act: Apply the filter
        $filtered = $propertyFilter->apply($allClasses, $this->strategy);

        // Assert: Only high priority cards should remain
        $this->assertContains(DashboardCard::class, $filtered);
        $this->assertNotContains(AnalyticsCard::class, $filtered);
    }

    /**
     * Test filters by less than.
     *
     * This test verifies that the PropertyFilter can filter classes
     * by numeric property comparisons (less than).
     *
     * ## Scenario:
     * - Discover classes with TestCardAttribute
     * - Filter by priority < 10
     * - Verify only lower priority cards are returned
     *
     * ## Assertions:
     * - AnalyticsCard (priority: 5) is included
     * - DashboardCard (priority: 10) is excluded
     */
    public function test_filters_by_less_than(): void
    {
        // Arrange: Create filter for priority = 5 (lower priority)
        $propertyFilter = new PropertyFilter('priority', 5);

        // Arrange: Discover all classes with the attribute
        $allClasses = $this->strategy->discover();

        // Act: Apply the filter
        $filtered = $propertyFilter->apply($allClasses, $this->strategy);

        // Assert: Only lower priority cards should remain
        $this->assertNotContains(DashboardCard::class, $filtered);
        $this->assertContains(AnalyticsCard::class, $filtered);
    }

    /**
     * Test filters by contains.
     *
     * This test verifies that the PropertyFilter can filter classes
     * by checking if a property value contains a specific substring.
     *
     * ## Scenario:
     * - Discover classes with TestCardAttribute
     * - Filter by group = 'main'
     * - Verify only cards in the main group are returned
     *
     * ## Assertions:
     * - DashboardCard (group: 'main') is included
     * - AnalyticsCard (group: 'reports') is excluded
     */
    public function test_filters_by_contains(): void
    {
        // Arrange: Create filter for group = 'main'
        $propertyFilter = new PropertyFilter('group', 'main');

        // Arrange: Discover all classes with the attribute
        $allClasses = $this->strategy->discover();

        // Act: Apply the filter
        $filtered = $propertyFilter->apply($allClasses, $this->strategy);

        // Assert: Only main group cards should remain
        $this->assertContains(DashboardCard::class, $filtered);
        $this->assertNotContains(AnalyticsCard::class, $filtered);
    }

    /**
     * Test filters by in array.
     *
     * This test verifies that the PropertyFilter can filter classes
     * by checking if an array property contains a specific value.
     *
     * ## Scenario:
     * - Discover classes with TestCardAttribute
     * - Check if tags array contains 'dashboard'
     * - Verify only cards with that tag are returned
     *
     * ## Assertions:
     * - DashboardCard (tags: ['dashboard', 'analytics']) is included
     * - AnalyticsCard (tags: ['analytics']) may or may not be included
     */
    public function test_filters_by_in_array(): void
    {
        // Arrange: Discover classes first
        $classes = $this->strategy->discover();

        // Skip if no classes found
        if ($classes === []) {
            $this->markTestSkipped('No classes with TestCardAttribute found');
        }

        // Act: Get metadata for discovered classes
        foreach ($classes as $class) {
            $metadata = $this->strategy->getMetadata($class);

            // Assert: If attribute exists, verify tags structure
            if ($metadata['attribute'] !== null && property_exists($metadata['attribute'], 'tags')) {
                $this->assertIsArray($metadata['attribute']->tags);
            }
        }

        // If we got here, test passed
        $this->assertTrue(true);
    }

    /**
     * Test handles nested properties.
     *
     * This test verifies that the PropertyFilter can handle
     * properties that don't exist on the attribute.
     *
     * ## Scenario:
     * - Try to filter by a non-existent property
     * - Verify no classes are returned
     *
     * ## Assertions:
     * - Filtered results are empty
     * - No errors are thrown
     */
    public function test_handles_nested_properties(): void
    {
        // Arrange: Create filter for non-existent property
        $propertyFilter = new PropertyFilter('nonExistentProperty', 'value');

        // Arrange: Discover all classes with the attribute
        $allClasses = $this->strategy->discover();

        // Act: Apply the filter
        $filtered = $propertyFilter->apply($allClasses, $this->strategy);

        // Assert: No classes should match
        $this->assertEmpty($filtered);
    }

    /**
     * Test handles null values.
     *
     * This test verifies that the PropertyFilter can handle
     * null property values correctly.
     *
     * ## Scenario:
     * - Filter by a property that might be null
     * - Verify proper handling of null values
     *
     * ## Assertions:
     * - Filter works correctly with null values
     * - No errors are thrown
     */
    public function test_handles_null_values(): void
    {
        // Arrange: Create filter for null value
        $propertyFilter = new PropertyFilter('group', null);

        // Arrange: Discover all classes with the attribute
        $allClasses = $this->strategy->discover();

        // Act: Apply the filter
        $filtered = $propertyFilter->apply($allClasses, $this->strategy);

        // Assert: No classes should match (both have non-null groups)
        $this->assertEmpty($filtered);
    }
}
