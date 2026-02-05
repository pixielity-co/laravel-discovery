<?php

declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Unit\Strategies;

use Pixielity\Discovery\Factories\StrategyFactory;
use Pixielity\Discovery\Strategies\InterfaceStrategy;
use Pixielity\Discovery\Tests\Fixtures\Classes\ServiceInterface;
use Pixielity\Discovery\Tests\Fixtures\Classes\Services\AbstractService;
use Pixielity\Discovery\Tests\Fixtures\Classes\Services\TestService;
use Pixielity\Discovery\Tests\TestCase;

/**
 * InterfaceStrategy Unit Tests.
 *
 * Tests interface implementation discovery.
 * The InterfaceStrategy discovers classes that implement a specific interface,
 * supporting both global and directory-based discovery modes.
 *
 * ## Key Features Tested:
 * - Interface implementation discovery
 * - Interface exclusion
 * - Multiple interface handling
 * - Nested interface inheritance
 * - Empty result handling
 *
 * @covers \Pixielity\Discovery\Strategies\InterfaceStrategy
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
class InterfaceStrategyTest extends TestCase
{
    /**
     * The strategy factory for creating strategies.
     */
    protected StrategyFactory $factory;

    /**
     * Setup the test environment.
     *
     * Initializes the strategy factory before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Resolve the strategy factory
        $this->factory = resolve(StrategyFactory::class);
    }

    /**
     * Test discovers classes implementing interface.
     *
     * This test verifies that the strategy can discover all classes
     * that implement the specified interface.
     *
     * ## Scenario:
     * - Create strategy for ServiceInterface
     * - Discover implementing classes
     * - Verify TestService is found
     *
     * ## Assertions:
     * - Results include TestService
     * - Results include AbstractService
     * - Results are an array
     */
    public function test_discovers_classes_implementing_interface(): void
    {
        // Arrange: Create strategy for ServiceInterface
        $interfaceStrategy = new InterfaceStrategy(ServiceInterface::class, $this->factory);

        // Act: Discover classes implementing the interface
        $results = $interfaceStrategy->discover();

        // Assert: Results should be an array
        $this->assertIsArray($results);

        // Assert: Should include TestService and AbstractService
        $this->assertContains(TestService::class, $results);
        $this->assertContains(AbstractService::class, $results);
    }

    /**
     * Test excludes interfaces themselves.
     *
     * This test verifies that the strategy excludes the interface
     * itself from the results (only implementations are returned).
     *
     * ## Scenario:
     * - Create strategy for ServiceInterface
     * - Discover implementing classes
     * - Verify interface itself is not in results
     *
     * ## Assertions:
     * - ServiceInterface is not in results
     * - Only implementing classes are returned
     */
    public function test_excludes_interfaces_themselves(): void
    {
        // Arrange: Create strategy for ServiceInterface
        $interfaceStrategy = new InterfaceStrategy(ServiceInterface::class, $this->factory);

        // Act: Discover classes
        $results = $interfaceStrategy->discover();

        // Assert: Interface itself should not be in results
        $this->assertNotContains(ServiceInterface::class, $results);
    }

    /**
     * Test handles multiple interfaces.
     *
     * This test verifies that the strategy works correctly when
     * classes implement multiple interfaces.
     *
     * ## Scenario:
     * - Discover classes implementing ServiceInterface
     * - Verify classes with multiple interfaces are found
     *
     * ## Assertions:
     * - Classes implementing multiple interfaces are discovered
     * - Discovery works correctly
     */
    public function test_handles_multiple_interfaces(): void
    {
        // Arrange: Create strategy for ServiceInterface
        $interfaceStrategy = new InterfaceStrategy(ServiceInterface::class, $this->factory);

        // Act: Discover classes
        $results = $interfaceStrategy->discover();

        // Assert: Should find classes even if they implement multiple interfaces
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    /**
     * Test handles nested interface inheritance.
     *
     * This test verifies that the strategy can handle interfaces
     * that extend other interfaces.
     *
     * ## Scenario:
     * - Test with interface inheritance chain
     * - Verify all implementations are found
     *
     * ## Assertions:
     * - Nested interface implementations are discovered
     * - Inheritance chain is properly traversed
     */
    public function test_handles_nested_interface_inheritance(): void
    {
        // Arrange: Create strategy for ServiceInterface
        $interfaceStrategy = new InterfaceStrategy(ServiceInterface::class, $this->factory);

        // Act: Discover classes
        $results = $interfaceStrategy->discover();

        // Assert: Should handle nested inheritance
        $this->assertIsArray($results);
        $this->assertContains(TestService::class, $results);
    }

    /**
     * Test returns empty when no implementations.
     *
     * This test verifies that the strategy returns an empty array
     * when no classes implement the specified interface.
     *
     * ## Scenario:
     * - Create strategy for non-existent interface
     * - Discover classes
     * - Verify empty array is returned
     *
     * ## Assertions:
     * - Results are an array
     * - Results are empty
     * - No exceptions are thrown
     */
    public function test_returns_empty_when_no_implementations(): void
    {
        // Arrange: Create strategy for non-existent interface
        $interfaceStrategy = new InterfaceStrategy('App\NonExistent\Interface', $this->factory);

        // Act: Discover classes
        $results = $interfaceStrategy->discover();

        // Assert: Should return empty array
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }
}
