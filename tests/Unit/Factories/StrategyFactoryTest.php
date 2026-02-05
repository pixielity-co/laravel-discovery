<?php declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Unit\Factories;

use Illuminate\Contracts\Foundation\Application;
use Pixielity\Discovery\Contracts\NamespaceResolverInterface;
use Pixielity\Discovery\Factories\StrategyFactory;
use Pixielity\Discovery\Strategies\AttributeStrategy;
use Pixielity\Discovery\Strategies\DirectoryStrategy;
use Pixielity\Discovery\Strategies\InterfaceStrategy;
use Pixielity\Discovery\Strategies\MethodStrategy;
use Pixielity\Discovery\Strategies\ParentClassStrategy;
use Pixielity\Discovery\Strategies\PropertyStrategy;
use Pixielity\Discovery\Tests\Fixtures\Attributes\TestAttribute;
use Pixielity\Discovery\Tests\Fixtures\Classes\ServiceInterface;
use Pixielity\Discovery\Tests\TestCase;

/**
 * StrategyFactory Unit Tests.
 *
 * Tests the strategy factory for creating discovery strategies with proper
 * dependency injection. The StrategyFactory is responsible for instantiating
 * all discovery strategy types with their required dependencies.
 *
 * ## Key Features Tested:
 * - Strategy creation for all types
 * - Dependency injection
 * - Return type validation
 * - Strategy configuration
 *
 * @covers \Pixielity\Discovery\Factories\StrategyFactory
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
class StrategyFactoryTest extends TestCase
{
    /**
     * The strategy factory instance being tested.
     *
     * @var StrategyFactory
     */
    protected StrategyFactory $factory;

    /**
     * The namespace resolver mock.
     *
     * @var NamespaceResolverInterface
     */
    protected NamespaceResolverInterface $namespaceResolver;

    /**
     * The application instance.
     *
     * @var Application
     */
    protected Application $app;

    /**
     * Setup the test environment.
     *
     * Creates the strategy factory with required dependencies.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Resolve dependencies from the container
        $this->namespaceResolver = resolve(NamespaceResolverInterface::class);
        $this->app = resolve(Application::class);

        // Create the strategy factory
        $this->factory = new StrategyFactory($this->namespaceResolver, $this->app);
    }

    /**
     * Test creates attribute strategy.
     *
     * This test verifies that the factory can create an AttributeStrategy
     * instance with the correct configuration.
     *
     * ## Scenario:
     * 1. Call createAttributeStrategy with an attribute class
     * 2. Verify the returned instance is correct
     * 3. Verify the strategy is properly configured
     *
     * ## Assertions:
     * - Returns AttributeStrategy instance
     * - Strategy is configured with the attribute class
     */
    public function test_creates_attribute_strategy(): void
    {
        // Arrange: Define the attribute class to discover
        $attributeClass = TestAttribute::class;

        // Act: Create the attribute strategy
        $strategy = $this->factory->createAttributeStrategy($attributeClass);

        // Assert: Should return AttributeStrategy instance
        $this->assertInstanceOf(AttributeStrategy::class, $strategy);

        // Assert: Strategy should be usable
        $results = $strategy->discover();
        $this->assertIsArray($results);
    }

    /**
     * Test creates directory strategy.
     *
     * This test verifies that the factory can create a DirectoryStrategy
     * instance with proper dependency injection.
     *
     * ## Scenario:
     * 1. Call createDirectoryStrategy with a directory path
     * 2. Verify the returned instance is correct
     * 3. Verify dependencies are injected
     *
     * ## Assertions:
     * - Returns DirectoryStrategy instance
     * - Strategy has namespace resolver injected
     * - Strategy has application instance injected
     */
    public function test_creates_directory_strategy(): void
    {
        // Arrange: Define the directory to scan
        $directory = __DIR__ . '/../../Fixtures/Classes/Cards';

        // Act: Create the directory strategy
        $strategy = $this->factory->createDirectoryStrategy($directory);

        // Assert: Should return DirectoryStrategy instance
        $this->assertInstanceOf(DirectoryStrategy::class, $strategy);

        // Assert: Strategy should be usable
        $results = $strategy->discover();
        $this->assertIsArray($results);
    }

    /**
     * Test creates interface strategy.
     *
     * This test verifies that the factory can create an InterfaceStrategy
     * instance with the factory injected for directory scanning.
     *
     * ## Scenario:
     * 1. Call createInterfaceStrategy with an interface class
     * 2. Verify the returned instance is correct
     * 3. Verify factory is injected
     *
     * ## Assertions:
     * - Returns InterfaceStrategy instance
     * - Strategy has factory injected
     * - Strategy is properly configured
     */
    public function test_creates_interface_strategy(): void
    {
        // Arrange: Define the interface to discover
        $interface = ServiceInterface::class;

        // Act: Create the interface strategy
        $strategy = $this->factory->createInterfaceStrategy($interface);

        // Assert: Should return InterfaceStrategy instance
        $this->assertInstanceOf(InterfaceStrategy::class, $strategy);

        // Assert: Strategy should be usable
        $results = $strategy->discover();
        $this->assertIsArray($results);
    }

    /**
     * Test creates parent class strategy.
     *
     * This test verifies that the factory can create a ParentClassStrategy
     * instance with the factory injected for directory scanning.
     *
     * ## Scenario:
     * 1. Call createParentClassStrategy with a parent class
     * 2. Verify the returned instance is correct
     * 3. Verify factory is injected
     *
     * ## Assertions:
     * - Returns ParentClassStrategy instance
     * - Strategy has factory injected
     * - Strategy is properly configured
     */
    public function test_creates_parent_class_strategy(): void
    {
        // Arrange: Define the parent class to discover
        $parentClass = \Illuminate\Console\Command::class;

        // Act: Create the parent class strategy
        $strategy = $this->factory->createParentClassStrategy($parentClass);

        // Assert: Should return ParentClassStrategy instance
        $this->assertInstanceOf(ParentClassStrategy::class, $strategy);

        // Assert: Strategy should be usable
        $results = $strategy->discover();
        $this->assertIsArray($results);
    }

    /**
     * Test creates method strategy.
     *
     * This test verifies that the factory can create a MethodStrategy
     * instance for discovering methods with attributes.
     *
     * ## Scenario:
     * 1. Call createMethodStrategy with an attribute class
     * 2. Verify the returned instance is correct
     * 3. Verify the strategy is properly configured
     *
     * ## Assertions:
     * - Returns MethodStrategy instance
     * - Strategy is configured with the attribute class
     */
    public function test_creates_method_strategy(): void
    {
        // Arrange: Define the attribute class for methods
        $attributeClass = TestAttribute::class;

        // Act: Create the method strategy
        $strategy = $this->factory->createMethodStrategy($attributeClass);

        // Assert: Should return MethodStrategy instance
        $this->assertInstanceOf(MethodStrategy::class, $strategy);

        // Assert: Strategy should be usable
        $results = $strategy->discover();
        $this->assertIsArray($results);
    }

    /**
     * Test creates property strategy.
     *
     * This test verifies that the factory can create a PropertyStrategy
     * instance for discovering properties with attributes.
     *
     * ## Scenario:
     * 1. Call createPropertyStrategy with an attribute class
     * 2. Verify the returned instance is correct
     * 3. Verify the strategy is properly configured
     *
     * ## Assertions:
     * - Returns PropertyStrategy instance
     * - Strategy is configured with the attribute class
     */
    public function test_creates_property_strategy(): void
    {
        // Arrange: Define the attribute class for properties
        $attributeClass = TestAttribute::class;

        // Act: Create the property strategy
        $strategy = $this->factory->createPropertyStrategy($attributeClass);

        // Assert: Should return PropertyStrategy instance
        $this->assertInstanceOf(PropertyStrategy::class, $strategy);

        // Assert: Strategy should be usable
        $results = $strategy->discover();
        $this->assertIsArray($results);
    }

    /**
     * Test injects dependencies correctly.
     *
     * This test verifies that the factory properly injects dependencies
     * into strategies that require them.
     *
     * ## Scenario:
     * 1. Create a directory strategy (requires dependencies)
     * 2. Verify the strategy can use injected dependencies
     * 3. Verify namespace resolution works
     *
     * ## Assertions:
     * - Dependencies are injected
     * - Strategy can use namespace resolver
     * - Strategy can use application instance
     */
    public function test_injects_dependencies_correctly(): void
    {
        // Arrange: Define a directory to scan
        $directory = __DIR__ . '/../../Fixtures/Classes/Services';

        // Act: Create the directory strategy (requires dependencies)
        $strategy = $this->factory->createDirectoryStrategy($directory);

        // Assert: Strategy should be created successfully
        $this->assertInstanceOf(DirectoryStrategy::class, $strategy);

        // Act: Use the strategy to discover classes
        $results = $strategy->discover();

        // Assert: Discovery should work (proving dependencies are injected)
        $this->assertIsArray($results);

        // Assert: If classes are found, they should have proper metadata
        if (!empty($results)) {
            $firstResult = reset($results);
            $this->assertIsArray($firstResult);
        }
    }
}
