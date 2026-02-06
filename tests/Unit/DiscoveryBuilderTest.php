<?php

declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Unit;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Pixielity\Discovery\DiscoveryBuilder;
use Pixielity\Discovery\DiscoveryManager;
use Pixielity\Discovery\Tests\Fixtures\Attributes\TestAttribute;
use Pixielity\Discovery\Tests\Fixtures\Classes\ServiceInterface;
use Pixielity\Discovery\Tests\TestCase;

/**
 * DiscoveryBuilder Unit Tests.
 *
 * Tests the fluent builder interface for discovery operations.
 * The DiscoveryBuilder provides a chainable API for configuring
 * discovery strategies, filters, validators, and caching.
 *
 * ## Key Features Tested:
 * - Filter addition (where, filter)
 * - Validator addition (instantiable, extending, implementing)
 * - Caching configuration
 * - Method chaining
 * - Discovery execution
 *
 * @covers \Pixielity\Discovery\DiscoveryBuilder
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
class DiscoveryBuilderTest extends TestCase
{
    /**
     * The discovery manager instance.
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
     * Test where adds property filter.
     *
     * This test verifies that the where() method adds a property filter
     * to the discovery builder and returns the builder for chaining.
     *
     * ## Scenario:
     * - Create a discovery builder
     * - Call where() to add a property filter
     * - Verify builder is returned for chaining
     *
     * ## Assertions:
     * - Returns DiscoveryBuilder instance
     * - Returns same builder instance (fluent interface)
     */
    public function test_where_adds_property_filter(): void
    {
        // Arrange: Create a discovery builder with attribute strategy
        $discoveryBuilder = $this->manager->attribute(TestAttribute::class);

        // Act: Add a property filter using where()
        $result = $discoveryBuilder->where('enabled', true);

        // Assert: Should return the same builder instance
        $this->assertInstanceOf(DiscoveryBuilder::class, $result);
        $this->assertSame($discoveryBuilder, $result);
    }

    /**
     * Test filter adds callback filter.
     *
     * This test verifies that the filter() method adds a callback filter
     * to the discovery builder and returns the builder for chaining.
     *
     * ## Scenario:
     * - Create a discovery builder
     * - Call filter() with a callback
     * - Verify builder is returned for chaining
     *
     * ## Assertions:
     * - Returns DiscoveryBuilder instance
     * - Returns same builder instance (fluent interface)
     */
    public function test_filter_adds_callback_filter(): void
    {
        // Arrange: Create a discovery builder with attribute strategy
        $discoveryBuilder = $this->manager->attribute(TestAttribute::class);

        // Act: Add a callback filter using filter()
        $result = $discoveryBuilder->filter(fn ($class): bool => true);

        // Assert: Should return the same builder instance
        $this->assertInstanceOf(DiscoveryBuilder::class, $result);
        $this->assertSame($discoveryBuilder, $result);
    }

    /**
     * Test instantiable adds validator.
     *
     * This test verifies that the instantiable() method adds an
     * instantiability validator to the discovery builder.
     *
     * ## Scenario:
     * - Create a discovery builder
     * - Call instantiable() to add validator
     * - Verify builder is returned for chaining
     *
     * ## Assertions:
     * - Returns DiscoveryBuilder instance
     * - Returns same builder instance (fluent interface)
     */
    public function test_instantiable_adds_validator(): void
    {
        // Arrange: Create a discovery builder with directory strategy
        $discoveryBuilder = $this->manager->directories(__DIR__ . '/../Fixtures');

        // Act: Add instantiable validator
        $result = $discoveryBuilder->instantiable();

        // Assert: Should return the same builder instance
        $this->assertInstanceOf(DiscoveryBuilder::class, $result);
        $this->assertSame($discoveryBuilder, $result);
    }

    /**
     * Test extending adds validator.
     *
     * This test verifies that the extending() method adds a parent class
     * validator to the discovery builder.
     *
     * ## Scenario:
     * - Create a discovery builder
     * - Call extending() with a parent class
     * - Verify builder is returned for chaining
     *
     * ## Assertions:
     * - Returns DiscoveryBuilder instance
     * - Returns same builder instance (fluent interface)
     */
    public function test_extends_adds_validator(): void
    {
        // Arrange: Create a discovery builder with directory strategy
        $discoveryBuilder = $this->manager->directories(__DIR__ . '/../Fixtures');

        // Act: Add extending validator
        $result = $discoveryBuilder->extending(Command::class);

        // Assert: Should return the same builder instance
        $this->assertInstanceOf(DiscoveryBuilder::class, $result);
        $this->assertSame($discoveryBuilder, $result);
    }

    /**
     * Test implementing adds validator.
     *
     * This test verifies that the implementing() method adds an interface
     * validator to the discovery builder.
     *
     * ## Scenario:
     * - Create a discovery builder
     * - Call implementing() with an interface
     * - Verify builder is returned for chaining
     *
     * ## Assertions:
     * - Returns DiscoveryBuilder instance
     * - Returns same builder instance (fluent interface)
     */
    public function test_implements_adds_validator(): void
    {
        // Arrange: Create a discovery builder with directory strategy
        $discoveryBuilder = $this->manager->directories(__DIR__ . '/../Fixtures');

        // Act: Add implementing validator
        $result = $discoveryBuilder->implementing(ServiceInterface::class);

        // Assert: Should return the same builder instance
        $this->assertInstanceOf(DiscoveryBuilder::class, $result);
        $this->assertSame($discoveryBuilder, $result);
    }

    /**
     * Test cached enables caching.
     *
     * This test verifies that the cached() method enables caching
     * for the discovery results.
     *
     * ## Scenario:
     * - Create a discovery builder
     * - Call cached() with a cache key
     * - Verify builder is returned for chaining
     *
     * ## Assertions:
     * - Returns DiscoveryBuilder instance
     * - Returns same builder instance (fluent interface)
     */
    public function test_cached_enables_caching(): void
    {
        // Arrange: Create a discovery builder with attribute strategy
        $discoveryBuilder = $this->manager->attribute(TestAttribute::class);

        // Act: Enable caching with a cache key
        $result = $discoveryBuilder->cached('test_cache_key');

        // Assert: Should return the same builder instance
        $this->assertInstanceOf(DiscoveryBuilder::class, $result);
        $this->assertSame($discoveryBuilder, $result);
    }

    /**
     * Test get executes discovery.
     *
     * This test verifies that the get() method executes the discovery
     * process and returns the results.
     *
     * ## Scenario:
     * - Create a discovery builder
     * - Call get() to execute discovery
     * - Verify results are returned
     *
     * ## Assertions:
     * - Returns an array
     * - Discovery is executed
     */
    public function test_get_executes_discovery(): void
    {
        // Arrange: Create a discovery builder with directory strategy
        $discoveryBuilder = $this->manager->directories(__DIR__ . '/../Fixtures/Classes/Cards');

        // Act: Execute discovery
        $results = $discoveryBuilder->get();

        // Assert: Should return a Collection of results
        $this->assertInstanceOf(Collection::class, $results);
        $this->assertNotEmpty($results);
    }

    /**
     * Test chaining methods returns self.
     *
     * This test verifies that all builder methods return the same
     * builder instance, enabling fluent method chaining.
     *
     * ## Scenario:
     * - Create a discovery builder
     * - Chain multiple methods together
     * - Verify same instance is returned throughout
     *
     * ## Assertions:
     * - All methods return DiscoveryBuilder
     * - Same instance is returned (fluent interface)
     * - Methods can be chained together
     */
    public function test_chaining_methods_returns_self(): void
    {
        // Arrange: Create a discovery builder with attribute strategy
        $discoveryBuilder = $this->manager->attribute(TestAttribute::class);

        // Act: Chain multiple methods together
        $result = $discoveryBuilder
            ->where('enabled', true)
            ->filter(fn ($class): bool => true)
            ->instantiable()
            ->cached('test');

        // Assert: Should return the same builder instance
        $this->assertInstanceOf(DiscoveryBuilder::class, $result);
        $this->assertSame($discoveryBuilder, $result);
    }
}
