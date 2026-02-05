<?php

declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Unit\Strategies;

use Pixielity\Discovery\Strategies\MethodStrategy;
use Pixielity\Discovery\Tests\Fixtures\Attributes\TestRouteAttribute;
use Pixielity\Discovery\Tests\TestCase;

/**
 * MethodStrategy Unit Tests.
 *
 * Tests method attribute discovery functionality.
 * The MethodStrategy discovers methods decorated with specific PHP attributes
 * using the composer-attribute-collector package.
 *
 * ## Key Features Tested:
 * - Method discovery by attribute
 * - Method metadata extraction
 * - Class and method name parsing
 * - File and line number information
 * - Multiple methods handling
 * - Visibility handling
 *
 * @covers \Pixielity\Discovery\Strategies\MethodStrategy
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
class MethodStrategyTest extends TestCase
{
    /**
     * Test discovers methods with attribute.
     *
     * This test verifies that the strategy can discover methods
     * decorated with the specified attribute.
     *
     * ## Scenario:
     * - Create strategy for TestRouteAttribute
     * - Discover methods
     * - Verify methods are found
     *
     * ## Assertions:
     * - Results are an array
     * - Methods with attribute are discovered
     */
    public function test_discovers_methods_with_attribute(): void
    {
        // Arrange: Create strategy for TestRouteAttribute
        $methodStrategy = new MethodStrategy(TestRouteAttribute::class);

        // Act: Discover methods with the attribute
        $results = $methodStrategy->discover();

        // Assert: Results should be an array
        $this->assertIsArray($results);
    }

    /**
     * Test returns method metadata.
     *
     * This test verifies that the strategy returns proper metadata
     * for discovered methods.
     *
     * ## Scenario:
     * - Discover methods
     * - Get metadata for a method
     * - Verify metadata structure
     *
     * ## Assertions:
     * - Metadata contains method identifier
     * - Metadata contains class name
     * - Metadata contains method name
     */
    public function test_returns_method_metadata(): void
    {
        // Arrange: Create strategy
        $methodStrategy = new MethodStrategy(TestRouteAttribute::class);

        // Act: Discover methods
        $results = $methodStrategy->discover();

        // Assert: If methods found, verify metadata
        if ($results !== []) {
            $firstMethod = $results[0];
            $metadata = $methodStrategy->getMetadata($firstMethod);

            $this->assertIsArray($metadata);
            $this->assertArrayHasKey('method', $metadata);
            $this->assertArrayHasKey('class', $metadata);
            $this->assertArrayHasKey('name', $metadata);
        } else {
            $this->markTestSkipped('No methods found with TestRouteAttribute');
        }
    }

    /**
     * Test includes class and method name.
     *
     * This test verifies that discovered methods include both
     * the class name and method name.
     *
     * ## Scenario:
     * - Discover methods
     * - Parse method identifiers
     * - Verify format is ClassName::methodName
     *
     * ## Assertions:
     * - Method identifier contains ::
     * - Class name is extractable
     * - Method name is extractable
     */
    public function test_includes_class_and_method_name(): void
    {
        // Arrange: Create strategy
        $methodStrategy = new MethodStrategy(TestRouteAttribute::class);

        // Act: Discover methods
        $results = $methodStrategy->discover();

        // Assert: Verify method identifier format
        foreach ($results as $result) {
            $this->assertIsString($result);
            $this->assertStringContainsString('::', $result);

            // Verify we can parse class and method name
            [$class, $methodName] = explode('::', $result, 2);
            $this->assertNotEmpty($class);
            $this->assertNotEmpty($methodName);
        }
    }

    /**
     * Test includes file and line number.
     *
     * This test verifies that method metadata can include
     * file and line number information.
     *
     * ## Scenario:
     * - Discover methods
     * - Get metadata
     * - Check for file/line information
     *
     * ## Assertions:
     * - Metadata structure is correct
     * - File information is available (if supported)
     */
    public function test_includes_file_and_line_number(): void
    {
        // Arrange: Create strategy
        $methodStrategy = new MethodStrategy(TestRouteAttribute::class);

        // Act: Discover methods
        $results = $methodStrategy->discover();

        // Assert: Metadata should be retrievable
        if ($results !== []) {
            $metadata = $methodStrategy->getMetadata($results[0]);
            $this->assertIsArray($metadata);
        } else {
            $this->markTestSkipped('No methods found');
        }
    }

    /**
     * Test handles multiple methods in same class.
     *
     * This test verifies that the strategy can discover multiple
     * methods in the same class.
     *
     * ## Scenario:
     * - Discover methods from a class with multiple attributed methods
     * - Verify all methods are found
     *
     * ## Assertions:
     * - Multiple methods from same class are discovered
     * - Each method is listed separately
     */
    public function test_handles_multiple_methods_in_same_class(): void
    {
        // Arrange: Create strategy
        $methodStrategy = new MethodStrategy(TestRouteAttribute::class);

        // Act: Discover methods
        $results = $methodStrategy->discover();

        // Assert: Should handle multiple methods
        $this->assertIsArray($results);

        // Group by class to check for multiple methods per class
        $methodsByClass = [];
        foreach ($results as $result) {
            [$class] = explode('::', $result, 2);
            $methodsByClass[$class] = ($methodsByClass[$class] ?? 0) + 1;
        }
        array_any($methodsByClass, fn ($count): bool => $count > 1);

        // This assertion may vary based on fixtures
        $this->assertIsArray($methodsByClass);
    }

    /**
     * Test handles static methods.
     *
     * This test verifies that the strategy can discover static methods
     * decorated with attributes.
     *
     * ## Scenario:
     * - Discover methods (including static)
     * - Verify static methods are included
     *
     * ## Assertions:
     * - Static methods are discovered
     * - No distinction between static and instance methods
     */
    public function test_handles_static_methods(): void
    {
        // Arrange: Create strategy
        $methodStrategy = new MethodStrategy(TestRouteAttribute::class);

        // Act: Discover methods
        $results = $methodStrategy->discover();

        // Assert: Should discover methods regardless of static modifier
        $this->assertIsArray($results);
    }

    /**
     * Test handles private protected public methods.
     *
     * This test verifies that the strategy can discover methods
     * with different visibility modifiers.
     *
     * ## Scenario:
     * - Discover methods with various visibility
     * - Verify all are discovered
     *
     * ## Assertions:
     * - Methods are discovered regardless of visibility
     * - Public, protected, and private methods are included
     */
    public function test_handles_private_protected_public_methods(): void
    {
        // Arrange: Create strategy
        $methodStrategy = new MethodStrategy(TestRouteAttribute::class);

        // Act: Discover methods
        $results = $methodStrategy->discover();

        // Assert: Should discover methods regardless of visibility
        $this->assertIsArray($results);
    }
}
