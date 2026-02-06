<?php

declare(strict_types=1);

namespace Pixielity\Discovery\Tests;

use Illuminate\Foundation\Application;
use Mockery;
use Orchestra\Testbench\TestCase as Orchestra;
use Override;
use Pixielity\Discovery\Tests\Concerns\LoadsApplicationWithAttributes;

/**
 * Base Test Case for Discovery Package Tests.
 *
 * This abstract class provides the foundation for all Discovery package tests.
 * It extends Orchestra Testbench which provides a Laravel application instance
 * specifically designed for package testing.
 *
 * ## Key Features
 *
 * ### 1. Container Attribute Support
 *
 * This test case properly configures Laravel's container to support PHP attributes
 * like #[Bind], #[Singleton], #[Scoped], and #[Config]. This is critical because
 * the Discovery package uses attribute-based dependency injection instead of
 * traditional service providers.
 *
 * ### 2. Composer Attribute Collector Integration
 *
 * The test case loads the composer-attribute-collector's generated attributes file
 * early in the bootstrap process, ensuring all PHP attributes in the codebase are
 * available for discovery and container binding.
 *
 * ### 3. Test Isolation
 *
 * Each test gets a fresh Laravel application instance with:
 * - In-memory SQLite database (fast, no cleanup needed)
 * - Disabled caching (tests don't depend on cached state)
 * - Clean container state (no shared instances between tests)
 *
 * ## Why Orchestra Testbench?
 *
 * Orchestra Testbench provides a minimal Laravel application specifically designed
 * for package testing. It:
 * - Boots a real Laravel application with all core services
 * - Provides helpers for testing packages in isolation
 * - Supports service provider registration and configuration
 * - Handles application lifecycle (boot, shutdown, cleanup)
 *
 * ## Container Attributes vs Service Providers
 *
 * Traditional Laravel packages use service providers to register bindings:
 *
 * ```php
 * public function register() {
 *     $this->app->singleton(MyInterface::class, MyClass::class);
 * }
 * ```
 *
 * Modern Laravel (11+) supports container attributes for cleaner code:
 *
 * ```php
 * #[Singleton]
 * #[Bind(MyClass::class)]
 * interface MyInterface {}
 * ```
 *
 * This package uses container attributes, which requires special setup in tests
 * (provided by the LoadsApplicationWithAttributes trait).
 *
 * ## Usage Example
 *
 * ```php
 * namespace Pixielity\Discovery\Tests\Unit;
 *
 * use Pixielity\Discovery\Tests\TestCase;
 *
 * class MyTest extends TestCase
 * {
 *     public function test_something()
 *     {
 *         // The application is already set up with attribute support
 *         $instance = $this->app->make(SomeInterface::class);
 *
 *         $this->assertInstanceOf(ConcreteClass::class, $instance);
 *     }
 * }
 * ```
 *
 * ## Extending This Test Case
 *
 * When creating new test classes:
 *
 * 1. **Unit Tests**: Extend this class directly
 *    - Test individual classes in isolation
 *    - Mock dependencies as needed
 *    - Focus on single responsibility
 *
 * 2. **Feature Tests**: Extend this class and add integration setup
 *    - Test multiple components working together
 *    - Use real implementations when possible
 *    - Test realistic scenarios
 *
 * 3. **Custom Setup**: Override `setUp()` for test-specific configuration
 *    ```php
 *    protected function setUp(): void
 *    {
 *        parent::setUp(); // Always call parent first!
 *
 *        // Your custom setup here
 *        $this->app['config']->set('discovery.custom', 'value');
 *    }
 *    ```
 *
 * ## Important Notes
 *
 * ### Service Provider Registration
 *
 * This test case intentionally does NOT register the DiscoveryServiceProvider
 * because the package uses container attributes for binding. The service provider
 * exists for backwards compatibility and explicit binding scenarios, but tests
 * rely on attribute-based binding to ensure that mechanism works correctly.
 *
 * If you need to test with the service provider, override `getPackageProviders()`:
 *
 * ```php
 * protected function getPackageProviders($app): array
 * {
 *     return [
 *         \Pixielity\Discovery\Providers\DiscoveryServiceProvider::class,
 *     ];
 * }
 * ```
 *
 * ### Mockery Cleanup
 *
 * The `tearDown()` method calls `Mockery::close()` to clean up any mocks created
 * during tests. This prevents mock expectations from leaking between tests and
 * ensures proper cleanup of Mockery's internal state.
 *
 * ### Configuration
 *
 * Test configuration is set in `getEnvironmentSetUp()` (provided by the trait).
 * This includes:
 * - Database configuration (SQLite in-memory)
 * - Debug mode (disabled)
 * - Discovery-specific settings (cache disabled)
 *
 * ## Troubleshooting
 *
 * ### "Target [Interface] is not instantiable"
 *
 * This error means the container can't resolve an interface. Check:
 * 1. Does the interface have #[Bind(ConcreteClass::class)]?
 * 2. Does the concrete class have #[Singleton] or #[Scoped]?
 * 3. Is the attributes file being loaded? (check vendor/attributes.php exists)
 * 4. Is the environment resolver set? (LoadsApplicationWithAttributes does this)
 *
 * ### "Class not found" errors
 *
 * Run `composer dump-autoload` to regenerate the autoloader and attributes file.
 *
 * ### Tests pass individually but fail when run together
 *
 * This indicates state leaking between tests. Check:
 * 1. Are you calling `parent::setUp()` in custom setUp methods?
 * 2. Are you calling `parent::tearDown()` in custom tearDown methods?
 * 3. Are you modifying global state (static properties, singletons)?
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 * @see     Orchestra
 * @see     LoadsApplicationWithAttributes
 */
abstract class TestCase extends Orchestra
{
    // This trait provides:
    // - Composer attribute collector integration
    // - Environment resolver setup for #[Bind] attributes
    // - Discovery-specific configuration
    use LoadsApplicationWithAttributes;

    /**
     * Setup the test environment.
     *
     * This method is called before each test method runs. It:
     * 1. Calls the parent setUp to bootstrap the Laravel application
     * 2. Provides a hook for test-specific setup in child classes
     *
     * ## When to Override
     *
     * Override this method when you need to:
     * - Set up test-specific configuration
     * - Create test data or fixtures
     * - Initialize mocks or stubs
     * - Register additional bindings
     *
     * ## Important
     *
     * ALWAYS call `parent::setUp()` first in your override:
     *
     * ```php
     * protected function setUp(): void
     * {
     *     parent::setUp(); // Required!
     *
     *     // Your setup code here
     * }
     * ```
     *
     * Forgetting to call parent::setUp() will result in:
     * - No Laravel application instance
     * - Container not configured
     * - Attributes not loaded
     * - Tests failing with cryptic errors
     */
    protected function setUp(): void
    {
        // Bootstrap the Laravel application with attribute support
        // This is provided by Orchestra Testbench and our trait
        parent::setUp();

        // Child classes can add their own setup here by overriding this method
        // and calling parent::setUp() first
    }

    /**
     * Tear down the test environment.
     *
     * This method is called after each test method completes. It:
     * 1. Closes all Mockery mocks to prevent leaks
     * 2. Calls the parent tearDown to clean up the application
     *
     * ## Why Close Mockery?
     *
     * Mockery maintains internal state about mock expectations. If not cleaned up:
     * - Mock expectations can leak between tests
     * - Memory usage increases over time
     * - Unexpected mock verification errors can occur
     *
     * Calling `Mockery::close()` ensures:
     * - All mock expectations are verified
     * - Mock state is reset
     * - Memory is freed
     *
     * ## When to Override
     *
     * Override this method when you need to:
     * - Clean up test-specific resources
     * - Reset global state
     * - Close connections or files
     * - Remove temporary data
     *
     * ## Important
     *
     * ALWAYS call `parent::tearDown()` last in your override:
     *
     * ```php
     * protected function tearDown(): void
     * {
     *     // Your cleanup code here
     *
     *     parent::tearDown(); // Required!
     * }
     * ```
     *
     * Calling parent::tearDown() first would destroy the application before
     * your cleanup code runs, potentially causing errors.
     */
    #[Override]
    protected function tearDown(): void
    {
        // Close all Mockery mocks and verify expectations
        // This must be called before parent::tearDown() to ensure mocks
        // are cleaned up while the application is still available
        Mockery::close();

        // Clean up the Laravel application and Orchestra Testbench state
        // This destroys the application instance and resets the container
        parent::tearDown();
    }

    /**
     * Get package providers.
     *
     * This method tells Orchestra Testbench which service providers to register
     * for the test application. By default, we return an empty array because
     * this package uses container attributes (#[Bind], #[Singleton]) instead of
     * traditional service provider registration.
     *
     * ## Why No Service Provider?
     *
     * The Discovery package uses Laravel 11's container attributes for dependency
     * injection. This means bindings are defined directly on interfaces and classes
     * using PHP attributes, rather than in a service provider's register() method.
     *
     * Example:
     * ```php
     * #[Singleton]
     * #[Bind(CacheManager::class)]
     * interface CacheManagerInterface {}
     * ```
     *
     * This approach:
     * - Keeps binding definitions close to the code they affect
     * - Reduces boilerplate in service providers
     * - Makes dependencies more discoverable
     * - Leverages PHP 8's attribute system
     *
     * ## When to Override
     *
     * Override this method if you need to:
     * - Test the package WITH the service provider registered
     * - Test backwards compatibility scenarios
     * - Register additional test-specific providers
     * - Test provider boot/register logic
     *
     * Example override:
     * ```php
     * protected function getPackageProviders($app): array
     * {
     *     return [
     *         \Pixielity\Discovery\Providers\DiscoveryServiceProvider::class,
     *         \Your\Package\TestServiceProvider::class,
     *     ];
     * }
     * ```
     *
     * ## Service Provider Still Exists
     *
     * The DiscoveryServiceProvider class still exists in the package for:
     * - Backwards compatibility with Laravel 10 and earlier
     * - Explicit binding scenarios where attributes aren't desired
     * - Package discovery via Laravel's auto-discovery mechanism
     *
     * But for testing, we rely on attribute-based binding to ensure that
     * mechanism works correctly in real-world usage.
     *
     * @param  Application              $app The Laravel application instance
     * @return array<int, class-string> Array of service provider class names
     */
    protected function getPackageProviders($app): array
    {
        // Suppress unused parameter warning - Orchestra Testbench requires this signature
        unset($app);

        // Return empty array - we use container attributes instead of service providers
        // The LoadsApplicationWithAttributes trait sets up the environment resolver
        // which enables #[Bind] attributes to work correctly
        return [];
    }
}
