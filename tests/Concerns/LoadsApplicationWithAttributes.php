<?php

declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Concerns;

use Illuminate\Contracts\Foundation\Application;

/**
 * LoadsApplicationWithAttributes Trait.
 *
 * This trait provides functionality to properly bootstrap a Laravel application
 * in Orchestra Testbench with full support for container attributes like #[Bind],
 * #[Singleton], #[Scoped], and #[Config].
 *
 * ## Problem Statement
 *
 * Orchestra Testbench's custom LoadConfiguration bootstrap class doesn't call
 * `resolveEnvironmentUsing()` on the container, which is required for Laravel's
 * container to process #[Bind] attributes on interfaces. Without this, the
 * container will fail to resolve interfaces that use attribute-based binding.
 *
 * ## Solution
 *
 * This trait:
 * 1. Loads the composer-attribute-collector's generated attributes file early
 * 2. Sets up the environment resolver so #[Bind] attributes work correctly
 * 3. Configures discovery-specific settings for the test environment
 *
 * ## Technical Details
 *
 * Laravel's Container::getConcrete() method (line 981-983) checks if
 * `$this->environmentResolver === null` before processing #[Bind] attributes.
 * If null, it returns the abstract directly without checking for bindings,
 * causing "Target [Interface] is not instantiable" errors.
 *
 * The environment resolver is normally set by Laravel's
 * `Illuminate\Foundation\Bootstrap\LoadConfiguration::bootstrap()` method,
 * but Orchestra Testbench uses its own LoadConfiguration that doesn't include
 * this critical step.
 *
 * ## Usage
 *
 * ```php
 * use Orchestra\Testbench\TestCase as Orchestra;
 * use Pixielity\Discovery\Tests\Concerns\LoadsApplicationWithAttributes;
 *
 * class MyTest extends Orchestra
 * {
 *     use LoadsApplicationWithAttributes;
 *
 *     public function test_something()
 *     {
 *         // Container attributes now work correctly
 *         $instance = $this->app->make(SomeInterface::class);
 *     }
 * }
 * ```
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
trait LoadsApplicationWithAttributes
{
    /**
     * Setup the test environment before the application is created.
     *
     * This method is called by Orchestra Testbench before the Laravel application
     * is bootstrapped. It's the perfect place to:
     * - Load the composer-attribute-collector's attributes file
     * - Set up the environment resolver for container attribute binding
     *
     * ## Why Load Attributes Early?
     *
     * The composer-attribute-collector package generates a `vendor/attributes.php`
     * file that contains all discovered PHP attributes in the codebase. This file
     * must be loaded before any code tries to use those attributes, otherwise
     * the attribute collector won't know about them.
     *
     * ## Why Set Environment Resolver?
     *
     * Laravel's container uses the environment resolver to determine if a #[Bind]
     * attribute should be applied based on the current environment. The resolver
     * is a callback that receives an array of environment names and returns true
     * if the current environment matches.
     *
     * Our implementation checks:
     * 1. If '*' is in the environments array (wildcard = all environments)
     * 2. If the current app environment is in the environments array
     *
     * This matches Laravel's default behavior in production applications.
     *
     * @param  Application  $app  The Laravel application instance being created
     *
     * @return void
     */
    protected function resolveApplicationCore($app): void
    {
        // Call parent implementation to maintain Orchestra Testbench's setup
        parent::resolveApplicationCore($app);

        // Load the composer-attribute-collector's generated attributes file
        // This file contains all PHP attributes discovered in the codebase
        // and must be loaded before any attribute-based functionality is used
        $this->loadComposerAttributes();

        // Set up the environment resolver so Laravel's container can process
        // #[Bind], #[Singleton], and #[Scoped] attributes on interfaces
        $this->setupEnvironmentResolver($app);
    }

    /**
     * Load the composer-attribute-collector's attributes file.
     *
     * The composer-attribute-collector package scans the codebase for PHP attributes
     * and generates a `vendor/attributes.php` file containing all discovered attributes.
     * This file must be loaded (via require_once) before any code tries to use those
     * attributes.
     *
     * ## What's in the attributes file?
     *
     * The file contains calls to `olvlvl\ComposerAttributeCollector\Attributes::with()`
     * which registers all discovered attributes with the collector. This includes:
     * - Class attributes (like #[Bind], #[Singleton])
     * - Method attributes (like #[Route], #[AsCommand])
     * - Property attributes (like #[Config], #[Autowired])
     * - Parameter attributes
     *
     * ## Why require_once?
     *
     * We use require_once instead of require to prevent the file from being loaded
     * multiple times if multiple test classes use this trait. Loading it multiple
     * times would cause errors as the attributes would be registered twice.
     *
     * @return void
     */
    protected function loadComposerAttributes(): void
    {
        // Build the path to the attributes file
        // __DIR__ points to tests/Concerns/, so we go up two levels to reach the project root
        $attributesFile = dirname(__DIR__, 2) . '/vendor/attributes.php';

        // Only load if the file exists (it might not exist if composer hasn't generated it yet)
        if (file_exists($attributesFile)) {
            require_once $attributesFile;
        }
    }

    /**
     * Setup the environment resolver for container attribute binding.
     *
     * This method configures Laravel's container to properly process #[Bind] attributes
     * by setting an environment resolver callback. Without this, the container will
     * skip attribute processing and fail to resolve interfaces.
     *
     * ## How #[Bind] Attributes Work
     *
     * When you define an interface with a #[Bind] attribute:
     *
     * ```php
     * #[Singleton]
     * #[Bind(ConcreteClass::class)]
     * interface MyInterface {}
     * ```
     *
     * Laravel's container needs to:
     * 1. Detect that you're trying to resolve an interface
     * 2. Check if the interface has a #[Bind] attribute
     * 3. Verify the binding applies to the current environment
     * 4. Register the binding (singleton, scoped, or transient)
     * 5. Resolve the concrete class
     *
     * Step 3 requires an environment resolver to be set.
     *
     * ## Environment Matching Logic
     *
     * The #[Bind] attribute accepts an optional $environments parameter:
     *
     * ```php
     * #[Bind(ConcreteClass::class, environments: ['production', 'staging'])]
     * ```
     *
     * Our resolver checks:
     * - If '*' is in the array → binding applies to all environments
     * - If current environment is in the array → binding applies
     * - Otherwise → binding doesn't apply
     *
     * ## Why This Matters for Tests
     *
     * In tests, we typically want all bindings to apply regardless of environment,
     * so we check for the wildcard '*' first. This ensures test isolation and
     * predictable behavior.
     *
     * @param  Application  $app  The Laravel application instance
     *
     * @return void
     */
    protected function setupEnvironmentResolver(Application $app): void
    {
        // Set the environment resolver callback
        // This callback is called by the container when checking if a #[Bind]
        // attribute should be applied based on the current environment
        // @phpstan-ignore-next-line
        $app->resolveEnvironmentUsing(function (array $environments) use ($app): bool {
            // Check if wildcard '*' is present - this means "all environments"
            // This is the default value for #[Bind] attributes when no specific
            // environments are specified
            if (\in_array('*', $environments, true)) {
                return true;
            }

            // Check if the current application environment matches any of the
            // specified environments in the #[Bind] attribute
            // For example: #[Bind(Foo::class, environments: ['production'])]
            // would only apply when $app->environment() returns 'production'
            return \in_array($app->environment(), $environments, true);
        });
    }

    /**
     * Define environment setup for discovery package.
     *
     * This method configures the test environment with settings specific to
     * the discovery package. It's called by Orchestra Testbench after the
     * application is created but before tests run.
     *
     * ## Orchestra Testbench Best Practice
     *
     * According to Orchestra Testbench documentation, `defineEnvironment()` is the
     * preferred method for configuring the test environment. It uses a callback
     * pattern with `tap()` for cleaner, more readable configuration.
     *
     * See: https://packages.tools/testbench/the-basic/environment.html
     *
     * ## Configuration Strategy
     *
     * We set up:
     * 1. Database configuration (SQLite in-memory for fast tests)
     * 2. Debug mode (disabled to match production behavior)
     * 3. Discovery-specific configuration (cache paths, enabled state)
     *
     * ## Why Disable Caching in Tests?
     *
     * We disable discovery caching (`discovery.cache.enabled = false`) because:
     * - Tests should be isolated and not depend on cached state
     * - Cache invalidation between tests is complex and error-prone
     * - In-memory operations are fast enough for test scenarios
     * - We want to test the actual discovery logic, not cached results
     *
     * ## Cache Path Configuration
     *
     * Even though caching is disabled, we still set a cache path because:
     * - Some tests might explicitly enable caching to test cache functionality
     * - The CacheManager constructor requires these config values
     * - It prevents errors if code tries to access the config
     *
     * @param  Application  $app  The Laravel application instance
     *
     * @return void
     */
    protected function defineEnvironment($app): void
    {
        // Use tap() for cleaner configuration as recommended by Orchestra Testbench
        // This pattern allows chaining multiple set() calls in a readable way
        tap($app['config'], function ($config) {
            // Setup default database to use SQLite in-memory
            // This provides a fast, isolated database for each test run
            // without requiring any external database setup
            $config->set('database.default', 'testing');
            $config->set('database.connections.testing', [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]);

            // Disable debug mode to match production behavior
            // This ensures tests catch issues that would occur in production
            $config->set('app.debug', false);

            // Configure discovery cache path
            // Uses Laravel's standard cache location: storage/framework/cache/discovery
            // The storage_path() helper resolves to the test application's storage directory
            $config->set('discovery.cache.path', storage_path('framework/cache/discovery'));

            // Disable caching in tests for isolation and predictability
            // Each test should perform actual discovery, not rely on cached results
            // This ensures tests are independent and can run in any order
            $config->set('discovery.cache.enabled', false);
        });
    }
}
