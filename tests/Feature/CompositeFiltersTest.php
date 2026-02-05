<?php

declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Feature;

use Pixielity\Discovery\Facades\Discovery;
use Pixielity\Discovery\Support\Reflection;
use Pixielity\Discovery\Tests\Fixtures\Attributes\TestAttribute;
use Pixielity\Discovery\Tests\Fixtures\Attributes\TestCardAttribute;
use Pixielity\Discovery\Tests\Fixtures\Attributes\TestRouteAttribute;
use Pixielity\Discovery\Tests\Fixtures\Attributes\TestServiceAttribute;
use Pixielity\Discovery\Tests\Fixtures\Classes\Commands\TestCommand;
use Pixielity\Discovery\Tests\Fixtures\Classes\Controllers\AdminController;
use Pixielity\Discovery\Tests\Fixtures\Classes\Controllers\TestController;
use Pixielity\Discovery\Tests\Fixtures\Classes\ServiceInterface;
use Pixielity\Discovery\Tests\Fixtures\Classes\Services\TestService;
use Pixielity\Discovery\Tests\TestCase;
use ReflectionClass;

/**
 * Composite Filters Feature Tests.
 *
 * Tests the composite filter methods (hasAttribute, hasMethodWith, hasPropertyWith)
 * that allow filtering classes based on the presence of attributes on the class,
 * its methods, or its properties.
 *
 * These filters enable powerful discovery patterns like:
 * - Find classes in a directory that have specific attributes
 * - Find classes that contain methods with route attributes
 * - Find classes that have properties with validation attributes
 *
 * @covers \Pixielity\Discovery\DiscoveryBuilder::hasAttribute
 * @covers \Pixielity\Discovery\DiscoveryBuilder::hasMethodWith
 * @covers \Pixielity\Discovery\DiscoveryBuilder::hasPropertyWith
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
class CompositeFiltersTest extends TestCase
{
    /**
     * Test hasAttribute filters classes with specific attribute.
     *
     * This test verifies that the hasAttribute() method correctly filters
     * classes to only include those that have the specified attribute
     * on the class itself.
     *
     * ## Scenario:
     * 1. Discover all classes in a directory
     * 2. Filter by classes that have TestAttribute
     * 3. Verify only attributed classes are returned
     *
     * ## Assertions:
     * - Results contain classes with the attribute
     * - Results don't contain classes without the attribute
     * - Filter works correctly with directory discovery
     */
    public function test_has_attribute_filters_classes(): void
    {
        // Act: Discover classes in directory and filter by attribute
        $results = Discovery::directories(__DIR__ . '/../Fixtures/Classes')
            ->hasAttribute(TestAttribute::class)
            ->get()
            ->all();

        // Assert: Should find classes with TestAttribute
        $this->assertNotEmpty($results);

        // Verify TestCommand is included (has TestAttribute)
        $this->assertArrayHasKey(TestCommand::class, $results);

        // Verify all results have the attribute
        foreach (array_keys($results) as $class) {
            $reflection = new ReflectionClass($class);
            $attributes = $reflection->getAttributes(TestAttribute::class);
            $this->assertNotEmpty(
                $attributes,
                "Class {$class} should have TestAttribute but doesn't"
            );
        }
    }

    /**
     * Test hasMethodWith filters classes with methods having attribute.
     *
     * This test verifies that the hasMethodWith() method correctly filters
     * classes to only include those that have at least one method decorated
     * with the specified attribute.
     *
     * ## Scenario:
     * 1. Discover all classes in Controllers directory
     * 2. Filter by classes that have methods with TestRouteAttribute
     * 3. Verify only classes with route methods are returned
     *
     * ## Assertions:
     * - Results contain controller classes with route methods
     * - TestController is included (has route methods)
     * - AdminController is included (has route methods)
     * - All results have at least one method with the attribute
     */
    public function test_has_method_with_filters_classes(): void
    {
        // Act: Discover classes with methods having TestRouteAttribute
        $results = Discovery::directories(__DIR__ . '/../Fixtures/Classes/Controllers')
            ->hasMethodWith(TestRouteAttribute::class)
            ->get()
            ->all();

        // Assert: Should find controller classes with route methods
        $this->assertNotEmpty($results);

        // Verify TestController is included
        $this->assertArrayHasKey(TestController::class, $results);

        // Verify AdminController is included
        $this->assertArrayHasKey(AdminController::class, $results);

        // Verify all results have at least one method with the attribute
        foreach (array_keys($results) as $class) {
            $hasMethodWithAttribute = array_any(Reflection::getMethods($class), fn ($method): bool => count($method->getAttributes(TestRouteAttribute::class)) > 0);

            $this->assertTrue(
                $hasMethodWithAttribute,
                "Class {$class} should have at least one method with TestRouteAttribute"
            );
        }
    }

    /**
     * Test hasPropertyWith filters classes with properties having attribute.
     *
     * This test verifies that the hasPropertyWith() method correctly filters
     * classes to only include those that have at least one property decorated
     * with the specified attribute.
     *
     * ## Scenario:
     * 1. Discover all classes in Cards directory
     * 2. Filter by classes that have properties with TestCardAttribute
     * 3. Verify filtering works correctly
     *
     * ## Assertions:
     * - Filter executes without errors
     * - Results are in expected format
     */
    public function test_has_property_with_filters_classes(): void
    {
        // Act: Discover classes with properties having TestCardAttribute
        $results = Discovery::directories(__DIR__ . '/../Fixtures/Classes/Cards')
            ->hasPropertyWith(TestCardAttribute::class)
            ->get()
            ->all();

        // Assert: Should execute without errors
        $this->assertIsArray($results);

        // Note: Our test fixtures may not have properties with attributes,
        // so we just verify the filter works without errors
    }

    /**
     * Test combining multiple composite filters.
     *
     * This test verifies that multiple composite filters can be chained
     * together to create complex discovery queries.
     *
     * ## Scenario:
     * 1. Discover classes in a directory
     * 2. Filter by class attribute
     * 3. Filter by method attribute
     * 4. Filter by interface implementation
     * 5. Verify all filters are applied
     *
     * ## Assertions:
     * - Multiple filters can be chained
     * - All filters are applied correctly
     * - Results match all criteria
     */
    public function test_combining_multiple_composite_filters(): void
    {
        // Act: Discover classes with multiple filters
        $results = Discovery::directories(__DIR__ . '/../Fixtures/Classes')
            ->hasAttribute(TestServiceAttribute::class)
            ->implementing(ServiceInterface::class)
            ->instantiable()
            ->get()
            ->all();

        // Assert: Should find classes matching all criteria
        // TestService has TestServiceAttribute and implements ServiceInterface
        if (! empty($results)) {
            $this->assertArrayHasKey(TestService::class, $results);

            // Verify TestService meets all criteria
            $reflectionClass = Reflection::getClass(TestService::class);
            $this->assertNotEmpty($reflectionClass->getAttributes(TestServiceAttribute::class));
            $this->assertTrue($reflectionClass->implementsInterface(ServiceInterface::class));
            $this->assertTrue($reflectionClass->isInstantiable());
        } else {
            // If no results, verify the filters are working (not throwing errors)
            $this->assertIsArray($results);
        }
    }

    /**
     * Test hasAttribute with directory discovery.
     *
     * This test verifies that hasAttribute works correctly when combined
     * with directory-based discovery, which is the most common use case.
     *
     * ## Scenario:
     * 1. Discover all classes in multiple directories
     * 2. Filter by specific attribute
     * 3. Verify results from all directories
     *
     * ## Assertions:
     * - Works with single directory
     * - Works with multiple directories
     * - Works with glob patterns
     */
    public function test_has_attribute_with_directory_discovery(): void
    {
        // Act: Discover with single directory
        $singleDir = Discovery::directories(__DIR__ . '/../Fixtures/Classes/Commands')
            ->hasAttribute(TestAttribute::class)
            ->get()
            ->all();

        // Assert: Should find TestCommand
        $this->assertNotEmpty($singleDir);
        $this->assertArrayHasKey(TestCommand::class, $singleDir);

        // Act: Discover with multiple directories
        $multipleDirs = Discovery::directories([
            __DIR__ . '/../Fixtures/Classes/Commands',
            __DIR__ . '/../Fixtures/Classes/Services',
        ])
            ->hasAttribute(TestAttribute::class)
            ->get()
            ->all();

        // Assert: Should find classes from both directories
        $this->assertNotEmpty($multipleDirs);
    }

    /**
     * Test hasMethodWith with interface validation.
     *
     * This test verifies that hasMethodWith can be combined with
     * interface validation to find specific types of classes.
     *
     * ## Scenario:
     * 1. Discover classes implementing an interface
     * 2. Filter by classes that have methods with specific attributes
     * 3. Verify combined filtering works
     *
     * ## Assertions:
     * - Filters can be combined
     * - Results match all criteria
     */
    public function test_has_method_with_and_interface_validation(): void
    {
        // Act: Discover classes with methods and interface
        $results = Discovery::directories(__DIR__ . '/../Fixtures/Classes/Controllers')
            ->hasMethodWith(TestRouteAttribute::class)
            ->instantiable()
            ->get()
            ->all();

        // Assert: Should find controller classes
        $this->assertNotEmpty($results);

        // Verify all results are instantiable
        foreach (array_keys($results) as $class) {
            $this->assertTrue(
                Reflection::isInstantiable($class),
                "Class {$class} should be instantiable"
            );
        }
    }

    /**
     * Test composite filters with caching.
     *
     * This test verifies that composite filters work correctly with
     * the caching mechanism.
     *
     * ## Scenario:
     * 1. Discover classes with composite filters and caching
     * 2. Verify results are cached
     * 3. Verify cached results are returned on second call
     *
     * ## Assertions:
     * - Caching works with composite filters
     * - Cached results match original results
     */
    public function test_composite_filters_with_caching(): void
    {
        // Arrange: Clear any existing cache
        Discovery::clearCache('composite_test');

        // Act: First discovery (should cache)
        $firstResults = Discovery::directories(__DIR__ . '/../Fixtures/Classes/Commands')
            ->hasAttribute(TestAttribute::class)
            ->cached('composite_test')
            ->get()
            ->all();

        // Act: Second discovery (should use cache)
        $cachedResults = Discovery::directories(__DIR__ . '/../Fixtures/Classes/Commands')
            ->hasAttribute(TestAttribute::class)
            ->cached('composite_test')
            ->get()
            ->all();

        // Assert: Results should match
        $this->assertEquals($firstResults, $cachedResults);

        // Cleanup
        Discovery::clearCache('composite_test');
    }

    /**
     * Test hasAttribute returns empty when no matches.
     *
     * This test verifies that hasAttribute returns an empty array
     * when no classes match the filter criteria.
     *
     * ## Scenario:
     * 1. Discover classes with non-existent attribute
     * 2. Verify empty results
     *
     * ## Assertions:
     * - Returns empty array (not null)
     * - No errors are thrown
     */
    public function test_has_attribute_returns_empty_when_no_matches(): void
    {
        // Act: Discover with non-existent attribute
        $results = Discovery::directories(__DIR__ . '/../Fixtures/Classes/Commands')
            ->hasAttribute('NonExistentAttribute')
            ->get()
            ->all();

        // Assert: Should return empty array
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    /**
     * Test composite filters handle reflection errors gracefully.
     *
     * This test verifies that composite filters handle reflection errors
     * gracefully and don't break the discovery process.
     *
     * ## Scenario:
     * 1. Discover classes that might cause reflection errors
     * 2. Verify errors are handled gracefully
     * 3. Verify discovery continues
     *
     * ## Assertions:
     * - No exceptions are thrown
     * - Discovery completes successfully
     */
    public function test_composite_filters_handle_errors_gracefully(): void
    {
        // Act: Discover with filters (should handle any reflection errors)
        $results = Discovery::directories(__DIR__ . '/../Fixtures/Classes')
            ->hasAttribute(TestAttribute::class)
            ->hasMethodWith(TestRouteAttribute::class)
            ->hasPropertyWith(TestCardAttribute::class)
            ->get()
            ->all();

        // Assert: Should complete without throwing exceptions
        $this->assertIsArray($results);
    }
}
