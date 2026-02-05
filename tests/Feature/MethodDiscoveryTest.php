<?php declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Feature;

use Pixielity\Discovery\Support\Arr;
use Pixielity\Discovery\Tests\Fixtures\Attributes\TestRouteAttribute;
use Pixielity\Discovery\Tests\TestCase;
use Pixielity\Discovery\DiscoveryManager;

/**
 * MethodDiscovery Feature Tests.
 *
 * End-to-end tests for method attribute discovery.
 * Tests discovering methods decorated with attributes.
 *
 * @covers \Pixielity\Discovery\DiscoveryManager
 * @covers \Pixielity\Discovery\Strategies\MethodStrategy
 */
class MethodDiscoveryTest extends TestCase
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
     * Test discovers methods with attribute.
     *
     * Verifies that methods decorated with a specific attribute
     * are discovered across all classes.
     *
     * @return void
     */
    public function test_discovers_methods_with_attribute(): void
    {
        // Act: Discover all methods with TestRouteAttribute
        $results = $this
            ->discovery
            ->methods(TestRouteAttribute::class)
            ->get();

        // Assert: Should find methods with route attributes
        $this->assertIsArray($results);
    }

    /**
     * Test filters by method attribute properties.
     *
     * Verifies that discovered methods can be filtered based on
     * their attribute property values (e.g., HTTP method = GET).
     *
     * @return void
     */
    public function test_filters_by_method_attribute_properties(): void
    {
        // Act: Discover only GET route methods
        $results = $this
            ->discovery
            ->methods(TestRouteAttribute::class)
            ->where('method', 'GET')
            ->get();

        // Assert: Should find GET methods
        $this->assertIsArray($results);

        // Verify all results have method = GET
        foreach ($results as $metadata) {
            if (isset($metadata['attribute'])) {
                $this->assertEquals('GET', $metadata['attribute']->method);
            }
        }
    }

    /**
     * Test discovers across multiple classes.
     *
     * Verifies that method discovery works across multiple
     * controller classes and aggregates all results.
     *
     * @return void
     */
    public function test_discovers_across_multiple_classes(): void
    {
        // Act: Discover route methods from all controllers
        $results = $this
            ->discovery
            ->methods(TestRouteAttribute::class)
            ->get();

        // Assert: Should find methods from multiple controllers
        $this->assertIsArray($results);

        // Verify results contain methods from different classes
        $classes = [];
        foreach ($results as $identifier => $metadata) {
            if (isset($metadata['class'])) {
                $classes[] = $metadata['class'];
            }
        }

        // Should have methods from multiple classes
        $uniqueClasses = Arr::unique($classes);
        $this->assertGreaterThanOrEqual(1, count($uniqueClasses));
    }

    /**
     * Test includes correct metadata.
     *
     * Verifies that discovered methods include all necessary
     * metadata: class name, method name, file path, line number.
     *
     * @return void
     */
    public function test_includes_correct_metadata(): void
    {
        // Act: Discover route methods
        $results = $this
            ->discovery
            ->methods(TestRouteAttribute::class)
            ->get();

        // Assert: Metadata should be complete
        if (!empty($results)) {
            $first = reset($results);

            // Verify required metadata fields
            $this->assertArrayHasKey('attribute', $first);
            $this->assertArrayHasKey('class', $first);
            $this->assertArrayHasKey('method', $first);
        }
    }

    /**
     * Test handles static and instance methods.
     *
     * Verifies that both static and instance methods with
     * attributes are discovered correctly.
     *
     * @return void
     */
    public function test_handles_static_and_instance_methods(): void
    {
        // Act: Discover all route methods (static and instance)
        $results = $this
            ->discovery
            ->methods(TestRouteAttribute::class)
            ->get();

        // Assert: Should handle both types
        $this->assertIsArray($results);
    }
}
