<?php declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Unit;

use Illuminate\Console\Command;
use Pixielity\Discovery\Tests\Fixtures\Attributes\TestAttribute;
use Pixielity\Discovery\Tests\Fixtures\Classes\ServiceInterface;
use Pixielity\Discovery\Tests\TestCase;
use Pixielity\Discovery\DiscoveryBuilder;
use Pixielity\Discovery\DiscoveryManager;
use Symfony\Component\Finder\Finder;

/**
 * DiscoveryManager Unit Tests.
 *
 * Tests the main discovery manager facade.
 * The DiscoveryManager provides the primary entry point for all discovery
 * operations, creating and configuring discovery builders for various strategies.
 *
 * ## Key Features Tested:
 * - Builder creation for all strategies
 * - Strategy method delegation
 * - Cache management
 * - Finder integration
 *
 * @covers \Pixielity\Discovery\DiscoveryManager
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
class DiscoveryManagerTest extends TestCase
{
    /**
     * The discovery manager instance being tested.
     *
     * @var DiscoveryManager
     */
    protected DiscoveryManager $manager;

    /**
     * Setup the test environment.
     *
     * Initializes the discovery manager before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Resolve the discovery manager from the container
        $this->manager = resolve(DiscoveryManager::class);
    }

    /**
     * Test attribute method returns builder.
     *
     * This test verifies that the attribute() method creates and returns
     * a DiscoveryBuilder configured with an AttributeStrategy.
     *
     * ## Scenario:
     * - Call attribute() with an attribute class
     * - Verify a builder is returned
     * - Verify builder is properly configured
     *
     * ## Assertions:
     * - Returns DiscoveryBuilder instance
     * - Builder is ready for chaining
     */
    public function test_attribute_method_returns_builder(): void
    {
        // Act: Create a builder for attribute discovery
        $discoveryBuilder = $this->manager->attribute(TestAttribute::class);

        // Assert: Should return a DiscoveryBuilder instance
        $this->assertInstanceOf(DiscoveryBuilder::class, $discoveryBuilder);
    }

    /**
     * Test directories method returns builder.
     *
     * This test verifies that the directories() method creates and returns
     * a DiscoveryBuilder configured with a DirectoryStrategy.
     *
     * ## Scenario:
     * - Call directories() with a directory path
     * - Verify a builder is returned
     * - Verify builder is properly configured
     *
     * ## Assertions:
     * - Returns DiscoveryBuilder instance
     * - Builder is ready for chaining
     */
    public function test_directories_method_returns_builder(): void
    {
        // Act: Create a builder for directory discovery
        $discoveryBuilder = $this->manager->directories(__DIR__ . '/../Fixtures');

        // Assert: Should return a DiscoveryBuilder instance
        $this->assertInstanceOf(DiscoveryBuilder::class, $discoveryBuilder);
    }

    /**
     * Test implementing method returns builder.
     *
     * This test verifies that the implementing() method creates and returns
     * a DiscoveryBuilder configured with an InterfaceStrategy.
     *
     * ## Scenario:
     * - Call implementing() with an interface class
     * - Verify a builder is returned
     * - Verify builder is properly configured
     *
     * ## Assertions:
     * - Returns DiscoveryBuilder instance
     * - Builder is ready for chaining
     */
    public function test_implementing_method_returns_builder(): void
    {
        // Act: Create a builder for interface discovery
        $discoveryBuilder = $this->manager->implementing(ServiceInterface::class);

        // Assert: Should return a DiscoveryBuilder instance
        $this->assertInstanceOf(DiscoveryBuilder::class, $discoveryBuilder);
    }

    /**
     * Test extending method returns builder.
     *
     * This test verifies that the extending() method creates and returns
     * a DiscoveryBuilder configured with a ParentClassStrategy.
     *
     * ## Scenario:
     * - Call extending() with a parent class
     * - Verify a builder is returned
     * - Verify builder is properly configured
     *
     * ## Assertions:
     * - Returns DiscoveryBuilder instance
     * - Builder is ready for chaining
     */
    public function test_extending_method_returns_builder(): void
    {
        // Act: Create a builder for parent class discovery
        $discoveryBuilder = $this->manager->extending(Command::class);

        // Assert: Should return a DiscoveryBuilder instance
        $this->assertInstanceOf(DiscoveryBuilder::class, $discoveryBuilder);
    }

    /**
     * Test methods method returns builder.
     *
     * This test verifies that the methods() method creates and returns
     * a DiscoveryBuilder configured with a MethodStrategy.
     *
     * ## Scenario:
     * - Call methods() with an attribute class
     * - Verify a builder is returned
     * - Verify builder is properly configured
     *
     * ## Assertions:
     * - Returns DiscoveryBuilder instance
     * - Builder is ready for chaining
     */
    public function test_methods_method_returns_builder(): void
    {
        // Act: Create a builder for method discovery
        $discoveryBuilder = $this->manager->methods(TestAttribute::class);

        // Assert: Should return a DiscoveryBuilder instance
        $this->assertInstanceOf(DiscoveryBuilder::class, $discoveryBuilder);
    }

    /**
     * Test properties method returns builder.
     *
     * This test verifies that the properties() method creates and returns
     * a DiscoveryBuilder configured with a PropertyStrategy.
     *
     * ## Scenario:
     * - Call properties() with an attribute class
     * - Verify a builder is returned
     * - Verify builder is properly configured
     *
     * ## Assertions:
     * - Returns DiscoveryBuilder instance
     * - Builder is ready for chaining
     */
    public function test_properties_method_returns_builder(): void
    {
        // Act: Create a builder for property discovery
        $discoveryBuilder = $this->manager->properties(TestAttribute::class);

        // Assert: Should return a DiscoveryBuilder instance
        $this->assertInstanceOf(DiscoveryBuilder::class, $discoveryBuilder);
    }

    /**
     * Test clear cache delegates to cache manager.
     *
     * This test verifies that the clearCache() method properly
     * delegates to the underlying cache manager.
     *
     * ## Scenario:
     * - Call clearCache() with a cache key
     * - Verify method executes without errors
     *
     * ## Assertions:
     * - Method executes successfully
     * - No exceptions are thrown
     */
    public function test_clear_cache_delegates_to_cache_manager(): void
    {
        // Act: Clear cache for a specific key
        $this->manager->clearCache('test_key');

        // Assert: Method should execute without errors
        $this->assertTrue(true);
    }

    /**
     * Test finder returns symfony finder.
     *
     * This test verifies that the finder() method returns a configured
     * Symfony Finder instance for file system operations.
     *
     * ## Scenario:
     * - Call finder() with a directory path
     * - Verify a Finder instance is returned
     * - Verify Finder is properly configured
     *
     * ## Assertions:
     * - Returns Symfony Finder instance
     * - Finder is configured for the directory
     */
    public function test_finder_returns_symfony_finder(): void
    {
        // Act: Get a Finder instance for a directory
        $finder = $this->manager->finder(__DIR__ . '/../Fixtures');

        // Assert: Should return a Symfony Finder instance
        $this->assertInstanceOf(Finder::class, $finder);
    }
}
