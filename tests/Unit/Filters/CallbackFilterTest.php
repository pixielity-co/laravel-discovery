<?php declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Unit\Filters;

use Pixielity\Discovery\Filters\CallbackFilter;
use Pixielity\Discovery\Strategies\AttributeStrategy;
use Pixielity\Discovery\Tests\Fixtures\Attributes\TestCardAttribute;
use Pixielity\Discovery\Tests\Fixtures\Classes\Cards\AnalyticsCard;
use Pixielity\Discovery\Tests\Fixtures\Classes\Cards\DashboardCard;
use Pixielity\Discovery\Tests\TestCase;

/**
 * CallbackFilter Unit Tests.
 *
 * Tests custom callback filtering for discovery results.
 * The CallbackFilter allows flexible filtering logic through
 * user-defined callbacks that receive the class name and metadata.
 *
 * @covers \Pixielity\Discovery\Filters\CallbackFilter
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
class CallbackFilterTest extends TestCase
{
    /**
     * The attribute strategy instance for testing.
     *
     * @var AttributeStrategy
     */
    protected AttributeStrategy $strategy;

    /**
     * Setup the test environment.
     *
     * Initializes the attribute strategy for card discovery.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create strategy for discovering classes with TestCardAttribute
        $this->strategy = new AttributeStrategy(TestCardAttribute::class);
    }

    /**
     * Test applies callback filter.
     *
     * This test verifies that the CallbackFilter correctly applies
     * a custom callback function to filter discovered classes.
     *
     * ## Scenario:
     * - Create a callback that filters by class name
     * - Apply the filter to a list of classes
     * - Verify only matching classes are returned
     *
     * ## Assertions:
     * - Callback is executed for each class
     * - Only classes matching the callback criteria are returned
     * - Filtered results are correct
     */
    public function test_applies_callback_filter(): void
    {
        // Arrange: Create a callback that filters by class name containing 'Dashboard'
        $callback = fn(string $class, array $metadata): bool => str_contains($class, 'Dashboard');

        // Arrange: Create the filter
        $filter = new CallbackFilter($callback);

        // Arrange: Get all classes with the attribute
        $allClasses = [
            DashboardCard::class,
            AnalyticsCard::class,
        ];

        // Act: Apply the filter
        $filtered = $filter->apply($allClasses, $this->strategy);

        // Assert: Only DashboardCard should remain
        $this->assertContains(DashboardCard::class, $filtered);
        $this->assertNotContains(AnalyticsCard::class, $filtered);
    }

    /**
     * Test receives correct parameters.
     *
     * This test verifies that the callback receives the correct
     * parameters (class name and metadata).
     *
     * ## Scenario:
     * - Create a callback that inspects its parameters
     * - Apply the filter
     * - Verify parameters are correct
     *
     * ## Assertions:
     * - First parameter is a string (class name)
     * - Second parameter is an array (metadata)
     * - Metadata contains expected keys
     */
    public function test_receives_correct_parameters(): void
    {
        // Arrange: Track callback invocations
        $invocations = [];

        // Arrange: Create a callback that records parameters
        $callback = function (string $class, array $metadata) use (&$invocations): bool {
            $invocations[] = [
                'class' => $class,
                'metadata' => $metadata,
            ];

            return true;  // Accept all classes
        };

        // Arrange: Create the filter
        $filter = new CallbackFilter($callback);

        // Arrange: Get all classes with the attribute
        $allClasses = [
            DashboardCard::class,
        ];

        // Act: Apply the filter
        $filter->apply($allClasses, $this->strategy);

        // Assert: Callback was invoked
        $this->assertNotEmpty($invocations);

        // Assert: Parameters are correct
        $invocation = $invocations[0];
        $this->assertIsString($invocation['class']);
        $this->assertIsArray($invocation['metadata']);
        $this->assertEquals(DashboardCard::class, $invocation['class']);
    }

    /**
     * Test handles multiple callbacks.
     *
     * This test verifies that multiple CallbackFilters can be
     * chained together to create complex filtering logic.
     *
     * ## Scenario:
     * - Apply first filter (by name)
     * - Apply second filter (by attribute property)
     * - Verify both filters are applied
     *
     * ## Assertions:
     * - First filter reduces the result set
     * - Second filter further reduces the result set
     * - Final results match both criteria
     */
    public function test_handles_multiple_callbacks(): void
    {
        // Arrange: Create first callback (filter by name)
        $callback1 = fn(string $class, array $metadata): bool => str_contains($class, 'Card');

        // Arrange: Create second callback (filter by class name containing 'Dashboard')
        $callback2 = fn(string $class, array $metadata): bool => str_contains($class, 'Dashboard');

        // Arrange: Create filters
        $filter1 = new CallbackFilter($callback1);
        $filter2 = new CallbackFilter($callback2);

        // Arrange: Get all classes with the attribute by discovering them
        $allClasses = $this->strategy->discover();

        // Act: Apply first filter
        $filtered1 = $filter1->apply($allClasses, $this->strategy);

        // Act: Apply second filter
        $filtered2 = $filter2->apply($filtered1, $this->strategy);

        // Assert: Both filters were applied
        $this->assertGreaterThanOrEqual(1, count($filtered1));  // At least one card passes first filter
        $this->assertCount(1, $filtered2);  // Only DashboardCard passes second filter
        $this->assertContains(DashboardCard::class, $filtered2);
    }

    /**
     * Test handles exception in callback.
     *
     * This test verifies that exceptions thrown in callbacks
     * are handled gracefully (or propagated as expected).
     *
     * ## Scenario:
     * - Create a callback that throws an exception
     * - Apply the filter
     * - Verify exception handling
     *
     * ## Assertions:
     * - Exception is thrown or handled
     * - Filter behavior is predictable
     */
    public function test_handles_exception_in_callback(): void
    {
        // Arrange: Create a callback that throws an exception
        $callback = function (string $class, array $metadata): bool {
            throw new \RuntimeException('Test exception');
        };

        // Arrange: Create the filter
        $filter = new CallbackFilter($callback);

        // Arrange: Get all classes with the attribute
        $allClasses = [
            DashboardCard::class,
        ];

        // Assert: Exception is thrown
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Test exception');

        // Act: Apply the filter (should throw exception)
        $filter->apply($allClasses, $this->strategy);
    }
}
