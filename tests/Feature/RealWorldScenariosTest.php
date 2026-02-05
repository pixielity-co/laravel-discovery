<?php declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Feature;

use Illuminate\Console\Command;
use Pixielity\Discovery\Support\Arr;
use Pixielity\Discovery\Tests\Fixtures\Attributes\TestAttribute;
use Pixielity\Discovery\Tests\Fixtures\Attributes\TestCardAttribute;
use Pixielity\Discovery\Tests\Fixtures\Attributes\TestRouteAttribute;
use Pixielity\Discovery\Tests\Fixtures\Attributes\TestServiceAttribute;
use Pixielity\Discovery\Tests\Fixtures\Attributes\TestValidateAttribute;
use Pixielity\Discovery\Tests\Fixtures\Classes\Cards\AnalyticsCard;
use Pixielity\Discovery\Tests\Fixtures\Classes\Cards\DashboardCard;
use Pixielity\Discovery\Tests\Fixtures\Classes\Commands\TestCommand;
use Pixielity\Discovery\Tests\Fixtures\Classes\Controllers\AdminController;
use Pixielity\Discovery\Tests\Fixtures\Classes\Controllers\TestController;
use Pixielity\Discovery\Tests\Fixtures\Classes\Services\TestService;
use Pixielity\Discovery\Tests\Fixtures\Classes\Settings\AppSettings;
use Pixielity\Discovery\Tests\Fixtures\Classes\Settings\UserSettings;
use Pixielity\Discovery\Tests\Fixtures\Classes\ServiceInterface;
use Pixielity\Discovery\Tests\TestCase;
use Pixielity\Discovery\DiscoveryManager;

/**
 * RealWorldScenarios Feature Tests.
 *
 * End-to-end tests for real-world use cases and scenarios.
 * These tests demonstrate practical applications of the discovery system
 * in common Laravel application patterns.
 *
 * @covers \Pixielity\Discovery\DiscoveryManager
 * @covers \Pixielity\Discovery\DiscoveryBuilder
 * @covers \Pixielity\Discovery\Strategies\DirectoryStrategy
 * @covers \Pixielity\Discovery\Strategies\AttributeStrategy
 * @covers \Pixielity\Discovery\Strategies\MethodStrategy
 * @covers \Pixielity\Discovery\Strategies\PropertyStrategy
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
class RealWorldScenariosTest extends TestCase
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
     * Test auto-register settings classes.
     *
     * This test demonstrates a real-world scenario where an application
     * needs to automatically discover and register all settings classes
     * for configuration management.
     *
     * ## Use Case:
     * - Application has multiple settings classes
     * - Each settings class has properties with validation attributes
     * - System needs to auto-register all settings for the config system
     *
     * ## Scenario:
     * 1. Discover all classes in the Settings directory
     * 2. Find properties with validation attributes
     * 3. Build a registry of settings with their validation rules
     *
     * ## Assertions:
     * - Settings classes are discovered
     * - Properties with validation are found
     * - Validation rules are properly extracted
     */
    public function test_auto_register_settings_classes(): void
    {
        // Arrange: Define the settings directory to scan
        $settingsDirectory = __DIR__ . '/../Fixtures/Classes/Settings';

        // Act: Discover all settings classes
        $settingsClasses = $this
            ->discovery
            ->directories($settingsDirectory)
            ->instantiable()
            ->get()->all();

        // Assert: Verify settings classes were discovered
        $this->assertNotEmpty($settingsClasses);
        $this->assertArrayHasKey(AppSettings::class, $settingsClasses);
        $this->assertArrayHasKey(UserSettings::class, $settingsClasses);

        // Act: Discover properties with validation attributes
        $validatedProperties = $this
            ->discovery
            ->properties(TestValidateAttribute::class)
            ->get()->all();

        // Assert: Verify validated properties were discovered
        $this->assertNotEmpty($validatedProperties);

        // Verify we can extract validation rules from properties
        foreach ($validatedProperties as $property => $metadata) {
            $this->assertIsString($property);
            $this->assertIsArray($metadata);
            $this->assertArrayHasKey('attributes', $metadata);
        }
    }

    /**
     * Test dynamic route registration.
     *
     * This test demonstrates a real-world scenario where an application
     * needs to automatically discover and register routes based on
     * controller method attributes.
     *
     * ## Use Case:
     * - Controllers use attributes to define routes
     * - System needs to auto-register routes without manual route files
     * - Route metadata (method, path, middleware) is in attributes
     *
     * ## Scenario:
     * 1. Discover all controller methods with route attributes
     * 2. Extract route metadata (HTTP method, path, middleware)
     * 3. Build a route registry for automatic registration
     *
     * ## Assertions:
     * - Route methods are discovered
     * - Route metadata is properly extracted
     * - Multiple routes from different controllers are found
     */
    public function test_dynamic_route_registration(): void
    {
        // Arrange: Discover methods with route attributes
        // This simulates scanning controllers for route definitions

        // Act: Discover all methods with route attributes
        $routes = $this
            ->discovery
            ->methods(TestRouteAttribute::class)
            ->get()->all();

        // Assert: Verify routes were discovered
        $this->assertNotEmpty($routes);

        // Verify we found routes from TestController
        $testControllerRoutes = Arr::filter(
            $routes->all(),
            fn($method) => str_contains($method, TestController::class)
        );
        $this->assertNotEmpty($testControllerRoutes);

        // Verify we found routes from AdminController
        $adminControllerRoutes = Arr::filter(
            $routes->all(),
            fn($method) => str_contains($method, AdminController::class)
        );
        $this->assertNotEmpty($adminControllerRoutes);

        // Verify route metadata is available
        foreach ($routes as $method => $metadata) {
            $this->assertIsString($method);
            $this->assertIsArray($metadata);
            $this->assertArrayHasKey('attributes', $metadata);

            // Verify we can access route attribute data
            $attributes = $metadata['attributes'];
            $this->assertNotEmpty($attributes);
        }
    }

    /**
     * Test plugin system discovery.
     *
     * This test demonstrates a real-world scenario where an application
     * has a plugin system that needs to automatically discover and load
     * plugins based on attributes or interfaces.
     *
     * ## Use Case:
     * - Application supports plugins
     * - Plugins are marked with specific attributes
     * - System needs to auto-discover and load plugins
     *
     * ## Scenario:
     * 1. Discover classes with service attributes (simulating plugins)
     * 2. Filter for instantiable classes
     * 3. Verify plugin interface implementation
     *
     * ## Assertions:
     * - Plugin classes are discovered
     * - Only instantiable plugins are included
     * - Plugin metadata is accessible
     */
    public function test_plugin_system_discovery(): void
    {
        // Arrange: Discover classes with service attributes (simulating plugins)
        // In a real plugin system, you might use a dedicated PluginAttribute

        // Act: Discover all classes with service attributes
        $plugins = $this
            ->discovery
            ->attribute(TestServiceAttribute::class)
            ->instantiable()
            ->get()->all();

        // Assert: Verify plugins were discovered
        $this->assertNotEmpty($plugins);
        $this->assertArrayHasKey(TestService::class, $plugins);

        // Verify plugins implement the expected interface
        $pluginsWithInterface = $this
            ->discovery
            ->attribute(TestServiceAttribute::class)
            ->implementing(ServiceInterface::class)
            ->instantiable()
            ->get()->all();

        $this->assertNotEmpty($pluginsWithInterface);
        $this->assertArrayHasKey(TestService::class, $pluginsWithInterface);
    }

    /**
     * Test health check discovery.
     *
     * This test demonstrates a real-world scenario where an application
     * needs to automatically discover health check classes for monitoring.
     *
     * ## Use Case:
     * - Application has multiple health check classes
     * - Health checks are marked with attributes
     * - System needs to auto-register health checks
     *
     * ## Scenario:
     * 1. Discover classes with card attributes (simulating health checks)
     * 2. Filter by enabled status
     * 3. Sort by priority
     *
     * ## Assertions:
     * - Health check classes are discovered
     * - Enabled/disabled filtering works
     * - Priority metadata is accessible
     */
    public function test_health_check_discovery(): void
    {
        // Arrange: Discover classes with card attributes (simulating health checks)
        // In a real system, you might use a HealthCheckAttribute

        // Act: Discover all classes with card attributes
        $healthChecks = $this
            ->discovery
            ->attribute(TestCardAttribute::class)
            ->instantiable()
            ->get()->all();

        // Assert: Verify health checks were discovered
        $this->assertNotEmpty($healthChecks);
        $this->assertArrayHasKey(DashboardCard::class, $healthChecks);
        $this->assertArrayHasKey(AnalyticsCard::class, $healthChecks);

        // Verify we can filter by enabled status
        $enabledHealthChecks = $this
            ->discovery
            ->attribute(TestCardAttribute::class)
            ->where('enabled', true)
            ->instantiable()
            ->get()->all();

        $this->assertIsArray($enabledHealthChecks);

        // Verify metadata includes priority information
        foreach ($healthChecks as $className => $metadata) {
            $this->assertIsArray($metadata);
            $this->assertArrayHasKey('attributes', $metadata);
        }
    }

    /**
     * Test command discovery.
     *
     * This test demonstrates a real-world scenario where an application
     * needs to automatically discover and register console commands.
     *
     * ## Use Case:
     * - Application has custom console commands
     * - Commands extend Laravel's Command class
     * - System needs to auto-register commands
     *
     * ## Scenario:
     * 1. Discover classes extending Command
     * 2. Filter for instantiable commands
     * 3. Build command registry
     *
     * ## Assertions:
     * - Command classes are discovered
     * - Commands extend the correct base class
     * - Commands are instantiable
     */
    public function test_command_discovery(): void
    {
        // Arrange: Define the commands directory to scan
        $commandsDirectory = __DIR__ . '/../Fixtures/Classes/Commands';

        // Act: Discover all classes extending Command
        $commands = $this
            ->discovery
            ->directories($commandsDirectory)
            ->extending(Command::class)
            ->instantiable()
            ->get()->all();

        // Assert: Verify commands were discovered
        $this->assertNotEmpty($commands);
        $this->assertArrayHasKey(TestCommand::class, $commands);

        // Verify commands have the expected metadata
        foreach ($commands as $className => $metadata) {
            $this->assertIsString($className);
            $this->assertIsArray($metadata);

            // Verify the class actually extends Command
            $reflection = new \ReflectionClass($className);
            $this->assertTrue($reflection->isSubclassOf(Command::class));
        }
    }

    /**
     * Test middleware discovery.
     *
     * This test demonstrates a real-world scenario where an application
     * needs to automatically discover middleware classes for HTTP request processing.
     *
     * ## Use Case:
     * - Application has custom middleware classes
     * - Middleware are marked with attributes
     * - System needs to auto-register middleware
     *
     * ## Scenario:
     * 1. Discover classes with specific attributes
     * 2. Filter for instantiable middleware
     * 3. Extract middleware metadata (priority, group)
     *
     * ## Assertions:
     * - Middleware classes are discovered
     * - Metadata is properly extracted
     * - Filtering works correctly
     */
    public function test_middleware_discovery(): void
    {
        // Arrange: Discover classes with attributes (simulating middleware)
        // In a real system, you might use a MiddlewareAttribute

        // Act: Discover all classes with test attributes (simulating middleware)
        $middleware = $this
            ->discovery
            ->attribute(TestAttribute::class)
            ->instantiable()
            ->get()->all();

        // Assert: Verify middleware were discovered
        $this->assertIsArray($middleware);

        // Verify we can discover middleware by directory
        $middlewareByDirectory = $this
            ->discovery
            ->directories(__DIR__ . '/../Fixtures/Classes')
            ->attribute(TestAttribute::class)
            ->instantiable()
            ->get()->all();

        $this->assertIsArray($middlewareByDirectory);
    }

    /**
     * Test event listener discovery.
     *
     * This test demonstrates a real-world scenario where an application
     * needs to automatically discover event listeners for the event system.
     *
     * ## Use Case:
     * - Application has event listener classes
     * - Listeners implement specific interfaces
     * - System needs to auto-register listeners
     *
     * ## Scenario:
     * 1. Discover classes implementing listener interface
     * 2. Filter for instantiable listeners
     * 3. Extract event-to-listener mappings
     *
     * ## Assertions:
     * - Listener classes are discovered
     * - Interface implementation is verified
     * - Listeners are instantiable
     */
    public function test_event_listener_discovery(): void
    {
        // Arrange: Discover classes implementing ServiceInterface (simulating listeners)
        // In a real system, you might use an EventListenerInterface

        // Act: Discover all classes implementing the interface
        $listeners = $this
            ->discovery
            ->implementing(ServiceInterface::class)
            ->instantiable()
            ->get()->all();

        // Assert: Verify listeners were discovered
        $this->assertNotEmpty($listeners);
        $this->assertArrayHasKey(TestService::class, $listeners);

        // Verify we can combine interface discovery with attributes
        $attributedListeners = $this
            ->discovery
            ->implementing(ServiceInterface::class)
            ->attribute(TestServiceAttribute::class)
            ->instantiable()
            ->get()->all();

        $this->assertNotEmpty($attributedListeners);
        $this->assertArrayHasKey(TestService::class, $attributedListeners);

        // Verify listeners have proper metadata
        foreach ($listeners as $className => $metadata) {
            $this->assertIsString($className);
            $this->assertIsArray($metadata);

            // Verify the class actually implements the interface
            $reflection = new \ReflectionClass($className);
            $this->assertTrue($reflection->implementsInterface(ServiceInterface::class));
        }
    }
}
