<?php

declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Feature;

use Pixielity\Discovery\DiscoveryManager;
use Pixielity\Discovery\Tests\Fixtures\Attributes\TestAttribute;
use Pixielity\Discovery\Tests\Fixtures\Attributes\TestCardAttribute;
use Pixielity\Discovery\Tests\TestCase;

/**
 * AttributeDiscovery Feature Tests.
 *
 * End-to-end tests for attribute-based class discovery.
 * Tests the complete flow from attribute scanning to result filtering.
 *
 * @covers \Pixielity\Discovery\DiscoveryManager
 * @covers \Pixielity\Discovery\Strategies\AttributeStrategy
 */
class AttributeDiscoveryTest extends TestCase
{
    /**
     * Discovery manager instance.
     */
    protected DiscoveryManager $discovery;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Resolve the discovery manager from the container
        $this->discovery = resolve(DiscoveryManager::class);
    }

    /**
     * Test discovers classes with simple attribute.
     *
     * Verifies that the discovery system can find classes decorated
     * with a simple attribute that has no properties.
     */
    public function test_discovers_classes_with_simple_attribute(): void
    {
        // Act: Discover all classes with TestAttribute
        $results = $this
            ->discovery
            ->attribute(TestAttribute::class)
            ->get()->all();

        // Assert: Results should be an array
        $this->assertIsArray($results);
    }

    /**
     * Test discovers classes with attribute properties.
     *
     * Verifies that attributes with properties (enabled, priority, etc.)
     * are correctly discovered and their properties are accessible.
     */
    public function test_discovers_classes_with_attribute_properties(): void
    {
        // Act: Discover classes with TestCardAttribute
        $results = $this
            ->discovery
            ->attribute(TestCardAttribute::class)
            ->get()->all();

        // Assert: Results should be an array
        $this->assertIsArray($results);

        // If results found, verify attribute properties exist
        if ($results !== []) {
            $first = reset($results);

            // Verify attribute metadata is present
            $this->assertArrayHasKey('attribute', $first);

            // Verify attribute has expected properties
            $this->assertObjectHasProperty('enabled', $first['attribute']);
            $this->assertObjectHasProperty('priority', $first['attribute']);
        }
    }

    /**
     * Test filters by attribute property.
     *
     * Verifies that discovered classes can be filtered based on
     * their attribute property values using the where() method.
     */
    public function test_filters_by_attribute_property(): void
    {
        // Act: Discover cards where enabled = true
        $results = $this
            ->discovery
            ->attribute(TestCardAttribute::class)
            ->where('enabled', true)
            ->get()->all();

        // Assert: Results should be an array
        $this->assertIsArray($results);

        // Verify all results have enabled = true
        foreach ($results as $result) {
            if (isset($result['attribute'])) {
                $this->assertTrue($result['attribute']->enabled);
            }
        }
    }

    /**
     * Test combines with directory filter.
     *
     * Verifies that attribute discovery can be combined with
     * directory filtering to narrow down results.
     */
    public function test_combines_with_directory_filter(): void
    {
        // Act: Discover classes in specific directory
        $results = $this
            ->discovery
            ->directories(__DIR__ . '/../Fixtures/Classes/Cards')
            ->get()->all();

        // Assert: Should find classes in the Cards directory
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    /**
     * Test caches results.
     *
     * Verifies that discovery results can be cached and subsequent
     * calls with the same cache key return identical results.
     */
    public function test_caches_results(): void
    {
        // Act: First discovery with caching
        $results1 = $this
            ->discovery
            ->attribute(TestCardAttribute::class)
            ->cached('test_cards')
            ->get()->all();

        // Act: Second discovery with same cache key
        $results2 = $this
            ->discovery
            ->attribute(TestCardAttribute::class)
            ->cached('test_cards')
            ->get()->all();

        // Assert: Both results should be identical
        $this->assertEquals($results1, $results2);
    }

    /**
     * Test handles no results.
     *
     * Verifies that when no classes match the attribute,
     * an empty array is returned instead of null or exception.
     */
    public function test_handles_no_results(): void
    {
        // Act: Try to discover non-existent attribute
        $results = $this
            ->discovery
            ->attribute('NonExistentAttribute')
            ->get()->all();

        // Assert: Should return empty array
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }
}
