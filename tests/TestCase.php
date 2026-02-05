<?php

declare(strict_types=1);

namespace Pixielity\Discovery\Tests;

use Illuminate\Foundation\Application;
use Mockery;
use Orchestra\Testbench\TestCase as Orchestra;
use Override;
use Pixielity\Discovery\Providers\DiscoveryServiceProvider;

/**
 * Base Test Case for ServiceProvider Package Tests.
 *
 * This abstract class provides the foundation for all ServiceProvider package tests.
 * It extends Orchestra Testbench which provides a Laravel application instance
 * specifically designed for package testing.
 *
 * ## Features:
 * - Automatic service provider registration
 * - In-memory SQLite database for fast tests
 * - Clean test environment setup
 * - Module facade mocking support
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
abstract class TestCase extends Orchestra
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Load the attributes file for composer-attribute-collector
        $attributesFile = __DIR__ . '/../vendor/attributes.php';
        if (file_exists($attributesFile)) {
            require_once $attributesFile;
        }
    }

    /**
     * Tear down the test environment.
     */
    #[Override]
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Get package providers.
     *
     * @param  Application              $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            DiscoveryServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set app debug mode
        $app['config']->set('app.debug', false);
    }
}
