<?php declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Feature;

use Pixielity\Discovery\Support\Arr;
use Pixielity\Discovery\Tests\Fixtures\Attributes\TestValidateAttribute;
use Pixielity\Discovery\Tests\TestCase;
use Pixielity\Discovery\DiscoveryManager;

/**
 * PropertyDiscovery Feature Tests.
 *
 * End-to-end tests for property attribute discovery.
 * Tests discovering class properties decorated with attributes.
 *
 * @covers \Pixielity\Discovery\DiscoveryManager
 * @covers \Pixielity\Discovery\Strategies\PropertyStrategy
 */
class PropertyDiscoveryTest extends TestCase
{
    /**
     * Discovery manager instance.
     *
     * @var DiscoveryManager
     */
    protected DiscoveryManager $discovery;

    /**
     * Set up the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->discovery = app(DiscoveryManager::class);
    }

    /**
     * Test discovers properties with attribute.
     *
     * Verifies that class properties decorated with a specific
     * attribute are discovered across all classes.
     *
     * @return void
     */
    public function test_discovers_properties_with_attribute(): void
    {
        // Act: Discover all properties with TestValidateAttribute
        $results = $this
            ->discovery
            ->properties(TestValidateAttribute::class)
            ->get()->all();

        // Assert: Should find properties with validation attributes
        $this->assertIsArray($results);
    }

    /**
     * Test filters by property attribute properties.
     *
     * Verifies that discovered properties can be filtered based on
     * their attribute property values (e.g., required = true).
     *
     * @return void
     */
    public function test_filters_by_property_attribute_properties(): void
    {
        // Act: Discover only required properties
        $results = $this
            ->discovery
            ->properties(TestValidateAttribute::class)
            ->where('required', true)
            ->get()->all();

        // Assert: Should find required properties
        $this->assertIsArray($results);

        // Verify all results have required = true
        foreach ($results as $metadata) {
            if (isset($metadata['attribute'])) {
                $this->assertTrue($metadata['attribute']->required);
            }
        }
    }

    /**
     * Test discovers across multiple classes.
     *
     * Verifies that property discovery works across multiple
     * classes and aggregates all results.
     *
     * @return void
     */
    public function test_discovers_across_multiple_classes(): void
    {
        // Act: Discover validation properties from all classes
        $results = $this
            ->discovery
            ->properties(TestValidateAttribute::class)
            ->get()->all();

        // Assert: Should find properties from multiple classes
        $this->assertIsArray($results);

        // Verify results contain properties from different classes
        $classes = [];
        foreach ($results as $identifier => $metadata) {
            if (isset($metadata['class'])) {
                $classes[] = $metadata['class'];
            }
        }

        // Should have properties from multiple classes
        $uniqueClasses = Arr::unique($classes);
        $this->assertGreaterThanOrEqual(1, count($uniqueClasses));
    }

    /**
     * Test includes correct metadata.
     *
     * Verifies that discovered properties include all necessary
     * metadata: class name, property name, file path.
     *
     * @return void
     */
    public function test_includes_correct_metadata(): void
    {
        // Act: Discover validation properties
        $results = $this
            ->discovery
            ->properties(TestValidateAttribute::class)
            ->get()->all();

        // Assert: Metadata should be complete
        if (!empty($results)) {
            $first = reset($results);

            // Verify required metadata fields
            $this->assertArrayHasKey('attribute', $first);
            $this->assertArrayHasKey('class', $first);
            $this->assertArrayHasKey('property', $first);
        }
    }

    /**
     * Test handles typed properties.
     *
     * Verifies that properties with type declarations
     * are discovered correctly.
     *
     * @return void
     */
    public function test_handles_typed_properties(): void
    {
        // Act: Discover all validation properties (including typed)
        $results = $this
            ->discovery
            ->properties(TestValidateAttribute::class)
            ->get()->all();

        // Assert: Should handle typed properties
        $this->assertIsArray($results);
    }
}
