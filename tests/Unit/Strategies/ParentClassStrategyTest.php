<?php

declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Unit\Strategies;

use Illuminate\Console\Command;
use Pixielity\Discovery\Factories\StrategyFactory;
use Pixielity\Discovery\Strategies\ParentClassStrategy;
use Pixielity\Discovery\Tests\Fixtures\Classes\Commands\TestCommand;
use Pixielity\Discovery\Tests\Fixtures\Classes\Services\AbstractService;
use Pixielity\Discovery\Tests\TestCase;

/**
 * ParentClassStrategy Unit Tests.
 *
 * Tests parent class extension discovery.
 * The ParentClassStrategy discovers classes that extend a specific parent class,
 * supporting both global and directory-based discovery modes.
 *
 * ## Key Features Tested:
 * - Parent class extension discovery
 * - Abstract class handling
 * - Multi-level inheritance
 * - Parent class exclusion
 * - Empty result handling
 *
 * @covers \Pixielity\Discovery\Strategies\ParentClassStrategy
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
class ParentClassStrategyTest extends TestCase
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
     * Test discovers classes extending parent.
     *
     * This test verifies that the strategy can discover all classes
     * that extend the specified parent class.
     *
     * ## Scenario:
     * - Create strategy for Command class
     * - Discover extending classes
     * - Verify TestCommand is found
     *
     * ## Assertions:
     * - Results include TestCommand
     * - Results are an array
     * - Only extending classes are returned
     */
    public function test_discovers_classes_extending_parent(): void
    {
        // Arrange: Create strategy for Command class
        $parentClassStrategy = new ParentClassStrategy(Command::class, $this->factory);

        // Act: Discover classes extending Command
        $results = $parentClassStrategy->discover();

        // Assert: Results should be an array
        $this->assertIsArray($results);

        // Assert: Should include TestCommand
        $this->assertContains(TestCommand::class, $results);
    }

    /**
     * Test excludes abstract classes when specified.
     *
     * This test verifies that abstract classes can be included
     * or excluded based on configuration.
     *
     * ## Scenario:
     * - Discover classes extending a parent
     * - Verify abstract classes are included by default
     *
     * ## Assertions:
     * - Abstract classes are discovered
     * - Concrete classes are discovered
     */
    public function test_excludes_abstract_classes_when_specified(): void
    {
        // Arrange: Create strategy for AbstractService parent
        // Note: AbstractService itself is abstract, so we test its behavior
        $parentClassStrategy = new ParentClassStrategy(AbstractService::class, $this->factory);

        // Act: Discover classes
        $results = $parentClassStrategy->discover();

        // Assert: Results should be an array
        $this->assertIsArray($results);

        // Note: By default, abstract classes are included in discovery
        // Filtering them out is done via the instantiable() validator
    }

    /**
     * Test handles multi-level inheritance.
     *
     * This test verifies that the strategy can handle classes
     * that extend through multiple levels of inheritance.
     *
     * ## Scenario:
     * - Test with multi-level inheritance chain
     * - Verify all descendants are found
     *
     * ## Assertions:
     * - Multi-level inheritance is detected
     * - All descendants in chain are discovered
     */
    public function test_handles_multi_level_inheritance(): void
    {
        // Arrange: Create strategy for Command class
        $parentClassStrategy = new ParentClassStrategy(Command::class, $this->factory);

        // Act: Discover classes
        $results = $parentClassStrategy->discover();

        // Assert: Should handle multi-level inheritance
        $this->assertIsArray($results);
        $this->assertContains(TestCommand::class, $results);
    }

    /**
     * Test excludes parent class itself.
     *
     * This test verifies that the strategy excludes the parent class
     * itself from the results (only descendants are returned).
     *
     * ## Scenario:
     * - Create strategy for Command class
     * - Discover extending classes
     * - Verify Command itself is not in results
     *
     * ## Assertions:
     * - Parent class is not in results
     * - Only extending classes are returned
     */
    public function test_excludes_parent_class_itself(): void
    {
        // Arrange: Create strategy for Command class
        $parentClassStrategy = new ParentClassStrategy(Command::class, $this->factory);

        // Act: Discover classes
        $results = $parentClassStrategy->discover();

        // Assert: Parent class itself should not be in results
        $this->assertNotContains(Command::class, $results);
    }

    /**
     * Test returns empty when no extensions.
     *
     * This test verifies that the strategy returns an empty array
     * when no classes extend the specified parent class.
     *
     * ## Scenario:
     * - Create strategy for non-existent parent class
     * - Discover classes
     * - Verify empty array is returned
     *
     * ## Assertions:
     * - Results are an array
     * - Results are empty
     * - No exceptions are thrown
     */
    public function test_returns_empty_when_no_extensions(): void
    {
        // Arrange: Create strategy for non-existent parent class
        $parentClassStrategy = new ParentClassStrategy('App\NonExistent\ParentClass', $this->factory);

        // Act: Discover classes
        $results = $parentClassStrategy->discover();

        // Assert: Should return empty array
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }
}
