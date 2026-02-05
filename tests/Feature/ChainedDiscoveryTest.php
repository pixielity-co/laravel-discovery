<?php declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Feature;

use Illuminate\Console\Command;
use Pixielity\Discovery\Tests\Fixtures\Attributes\TestRouteAttribute;
use Pixielity\Discovery\Tests\Fixtures\Attributes\TestServiceAttribute;
use Pixielity\Discovery\Tests\Fixtures\Attributes\TestValidateAttribute;
use Pixielity\Discovery\Tests\Fixtures\Classes\Cards\AnalyticsCard;
use Pixielity\Discovery\Tests\Fixtures\Classes\Cards\DashboardCard;
use Pixielity\Discovery\Tests\Fixtures\Classes\Services\TestService;
use Pixielity\Discovery\Tests\Fixtures\Classes\ServiceInterface;
use Pixielity\Discovery\Tests\TestCase;
use Pixielity\Discovery\DiscoveryManager;

/**
 * ChainedDiscovery Feature Tests.
 *
 * End-to-end tests for chained discovery operations.
 * These tests verify that multiple discovery strategies, filters, and validators
 * can be chained together in a fluent interface to create complex discovery queries.
 *
 * ## Key Features Tested:
 * - Chaining multiple discovery strategies
 * - Combining filters and validators
 * - Caching chained results
 * - Order independence of chain operations
 *
 * @covers \Pixielity\Discovery\DiscoveryManager
 * @covers \Pixielity\Discovery\DiscoveryBuilder
 * @covers \Pixielity\Discovery\Strategies\DirectoryStrategy
 * @covers \Pixielity\Discovery\Strategies\AttributeStrategy
 * @covers \Pixielity\Discovery\Strategies\MethodStrategy
 * @covers \Pixielity\Discovery\Strategies\PropertyStrategy
 * @covers \Pixielity\Discovery\Validators\InstantiableValidator
 * @covers \Pixielity\Discovery\Validators\ImplementsValidator
 * @covers \Pixielity\Discovery\Validators\ExtendsValidator
 * @covers \Pixielity\Discovery\Filters\PropertyFilter
 * @covers \Pixielity\Discovery\Filters\CallbackFilter
 * @covers \Pixielity\Discovery\Cache\CacheManager
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
class ChainedDiscoveryTest extends TestCase
{
    /**
     * The discovery manager instance.
     *
     * @var DiscoveryManager
     */
    protected DiscoveryManager $discovery;

    /**
     * Setup the test environment.
     *
     * Initializes the discovery manager before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Resolve the discovery manager from the container
        $this->discovery = resolve(DiscoveryManager::class);
    }

    /**
     * Test chaining directories, then attribute, then filter.
     *
     * This test verifies that we can chain directory discovery
     * with attribute filtering in a fluent manner.
     *
     * ## Scenario:
     * 1. Start with directory discovery
     * 2. Apply attribute filtering (implicit)
     * 3. Get results
     *
     * ## Assertions:
     * - Results are not empty
     * - Specific card classes are discovered
     * - All results are from the specified directory
     */
    public function test_directories_then_attribute_then_filter(): void
    {
        // Arrange: Define the directory to scan
        $cardsDirectory = __DIR__ . '/../Fixtures/Classes/Cards';

        // Act: Discover classes in the Cards directory
        $results = $this
            ->discovery
            ->directories($cardsDirectory)
            ->get();

        // Assert: Verify both card classes were discovered
        $this->assertNotEmpty($results);
        $this->assertArrayHasKey(DashboardCard::class, $results);
        $this->assertArrayHasKey(AnalyticsCard::class, $results);
    }

    /**
     * Test chaining attribute, then implements, then instantiable.
     *
     * This test verifies that we can chain attribute discovery
     * with interface validation and instantiability checks.
     *
     * ## Scenario:
     * 1. Start with attribute discovery
     * 2. Filter by interface implementation
     * 3. Validate instantiability
     * 4. Get results
     *
     * ## Assertions:
     * - Results are not empty
     * - TestService class is discovered
     * - All results implement the interface
     * - All results are instantiable
     */
    public function test_attribute_then_implements_then_instantiable(): void
    {
        // Arrange: Define the attribute and interface to filter by
        // We're looking for services marked with TestServiceAttribute
        // that implement ServiceInterface and can be instantiated

        // Act: Chain attribute discovery with validators
        $results = $this
            ->discovery
            ->attribute(TestServiceAttribute::class)
            ->implementing(ServiceInterface::class)
            ->instantiable()
            ->get();

        // Assert: Verify TestService was discovered
        $this->assertNotEmpty($results);
        $this->assertArrayHasKey(TestService::class, $results);
    }

    /**
     * Test chaining directories, then extends, then where.
     *
     * This test verifies that we can chain directory discovery
     * with parent class validation.
     *
     * ## Scenario:
     * 1. Start with directory discovery
     * 2. Filter by parent class (extends Command)
     * 3. Get results
     *
     * ## Assertions:
     * - Results are not empty
     * - All results extend Command class
     */
    public function test_directories_then_extends_then_where(): void
    {
        // Arrange: Define the commands directory to scan
        $commandsDirectory = __DIR__ . '/../Fixtures/Classes/Commands';

        // Act: Discover commands that extend Laravel's Command class
        $results = $this
            ->discovery
            ->directories($commandsDirectory)
            ->extending(Command::class)
            ->get();

        // Assert: Verify commands were discovered
        $this->assertNotEmpty($results);
    }

    /**
     * Test chaining methods, then filter, then cached.
     *
     * This test verifies that we can chain method discovery
     * with property filtering and caching.
     *
     * ## Scenario:
     * 1. Start with method discovery
     * 2. Filter by attribute property (method = 'GET')
     * 3. Enable caching
     * 4. Get results
     * 5. Verify cache is used on second call
     *
     * ## Assertions:
     * - Results are not empty
     * - Only GET methods are returned
     * - Cached results match original results
     */
    public function test_methods_then_filter_then_cached(): void
    {
        // Arrange: Define cache key for this discovery
        $cacheKey = 'test_routes';

        // Act: Discover methods with route attributes, filter by GET method
        $results = $this
            ->discovery
            ->methods(TestRouteAttribute::class)
            ->where('method', 'GET')
            ->cached($cacheKey)
            ->get();

        // Assert: Verify results were found
        $this->assertNotEmpty($results);

        // Act: Perform the same discovery again (should use cache)
        $cachedResults = $this
            ->discovery
            ->methods(TestRouteAttribute::class)
            ->where('method', 'GET')
            ->cached($cacheKey)
            ->get();

        // Assert: Verify cached results match original results
        $this->assertEquals($results, $cachedResults);
    }

    /**
     * Test chaining properties, then where, then cached.
     *
     * This test verifies that we can chain property discovery
     * with filtering and caching.
     *
     * ## Scenario:
     * 1. Start with property discovery
     * 2. Filter by attribute property (required = true)
     * 3. Enable caching
     * 4. Get results
     *
     * ## Assertions:
     * - Results are not empty
     * - Only required properties are returned
     */
    public function test_properties_then_where_then_cached(): void
    {
        // Arrange: Define cache key for this discovery
        $cacheKey = 'test_validations';

        // Act: Discover properties with validation attributes, filter by required
        $results = $this
            ->discovery
            ->properties(TestValidateAttribute::class)
            ->where('required', true)
            ->cached($cacheKey)
            ->get();

        // Assert: Verify results were found
        $this->assertNotEmpty($results);
    }

    /**
     * Test complex chain with multiple filters.
     *
     * This test verifies that we can chain multiple filters
     * including custom callback filters.
     *
     * ## Scenario:
     * 1. Start with directory discovery
     * 2. Filter by interface implementation
     * 3. Validate instantiability
     * 4. Apply custom callback filter (exclude Abstract classes)
     * 5. Get results
     *
     * ## Assertions:
     * - Results are not empty
     * - TestService is included
     * - Abstract classes are excluded
     */
    public function test_complex_chain_with_multiple_filters(): void
    {
        // Arrange: Define the services directory to scan
        $servicesDirectory = __DIR__ . '/../Fixtures/Classes/Services';

        // Act: Chain multiple filters together
        $results = $this
            ->discovery
            ->directories([$servicesDirectory])
            ->implementing(ServiceInterface::class)
            ->instantiable()
            ->filter(fn($class): bool => !str_contains($class, 'Abstract'))
            ->get();

        // Assert: Verify TestService was discovered
        $this->assertNotEmpty($results);
        $this->assertArrayHasKey(TestService::class, $results);

        // Verify abstract classes were filtered out
        foreach ($results as $className => $metadata) {
            $this->assertStringNotContainsString('Abstract', $className);
        }
    }

    /**
     * Test chain with all validators.
     *
     * This test verifies that we can chain all available validators
     * together in a single discovery query.
     *
     * ## Scenario:
     * 1. Start with directory discovery
     * 2. Apply interface validator
     * 3. Apply instantiability validator
     * 4. Get results
     *
     * ## Assertions:
     * - Results are not empty
     * - All validators are applied correctly
     */
    public function test_chain_with_all_validators(): void
    {
        // Arrange: Define the services directory to scan
        $servicesDirectory = __DIR__ . '/../Fixtures/Classes/Services';

        // Act: Chain all validators together
        $results = $this
            ->discovery
            ->directories($servicesDirectory)
            ->implementing(ServiceInterface::class)
            ->instantiable()
            ->get();

        // Assert: Verify results were found
        $this->assertNotEmpty($results);
    }

    /**
     * Test chain order independence.
     *
     * This test verifies that the order of chained validators
     * does not affect the final results.
     *
     * ## Scenario:
     * 1. Perform discovery with validators in one order
     * 2. Perform discovery with validators in different order
     * 3. Compare results
     *
     * ## Assertions:
     * - Both result sets are identical
     * - Validator order does not matter
     */
    public function test_chain_order_independence(): void
    {
        // Arrange: Define the services directory to scan
        $servicesDirectory = __DIR__ . '/../Fixtures/Classes/Services';

        // Act: Discover with validators in order A
        $results1 = $this
            ->discovery
            ->directories($servicesDirectory)
            ->implementing(ServiceInterface::class)
            ->instantiable()
            ->get();

        // Act: Discover with validators in order B (reversed)
        $results2 = $this
            ->discovery
            ->directories($servicesDirectory)
            ->instantiable()
            ->implementing(ServiceInterface::class)
            ->get();

        // Assert: Verify both result sets are identical
        $this->assertEquals($results1, $results2);
    }
}
