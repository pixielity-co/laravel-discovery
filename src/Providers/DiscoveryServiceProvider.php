<?php declare(strict_types=1);

namespace Pixielity\Discovery\Providers;

use Illuminate\Support\ServiceProvider;
use Pixielity\Discovery\Cache\CacheManager;
use Pixielity\Discovery\Contracts\CacheManagerInterface;
use Pixielity\Discovery\Contracts\DiscoveryManagerInterface;
use Pixielity\Discovery\Contracts\NamespaceResolverInterface;
use Pixielity\Discovery\Contracts\StrategyFactoryInterface;
use Pixielity\Discovery\Factories\StrategyFactory;
use Pixielity\Discovery\Resolvers\NamespaceResolver;
use Pixielity\Discovery\DiscoveryManager;

/**
 * DiscoveryServiceProvider - Registers discovery services in the container.
 *
 * This service provider handles the registration and bootstrapping of all
 * discovery-related services. It binds interfaces to their implementations
 * and ensures proper dependency injection throughout the package.
 *
 * ## Registered Services:
 * - NamespaceResolverInterface → NamespaceResolver (Singleton)
 * - CacheManagerInterface → CacheManager (Singleton)
 * - StrategyFactoryInterface → StrategyFactory (Singleton)
 * - DiscoveryManagerInterface → DiscoveryManager (Singleton)
 *
 * ## Usage:
 * This provider is automatically registered when the package is installed
 * in a Laravel application. No manual registration is required.
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
class DiscoveryServiceProvider extends ServiceProvider
{
    /**
     * Register discovery services.
     *
     * This method binds all interfaces to their concrete implementations
     * in the service container. All services are registered as singletons
     * to ensure consistent state and avoid duplicate instances.
     *
     * The binding order is important:
     * 1. NamespaceResolver - No dependencies
     * 2. CacheManager - No dependencies
     * 3. StrategyFactory - Depends on NamespaceResolver
     * 4. DiscoveryManager - Depends on CacheManager and StrategyFactory
     */
    public function register(): void
    {
        // Bind NamespaceResolver as singleton
        // This service has no dependencies and is used by DirectoryStrategy
        $this->app->singleton(
            NamespaceResolverInterface::class,
            NamespaceResolver::class
        );

        // Bind CacheManager as singleton
        // This service has no dependencies and is used by DiscoveryBuilder
        $this->app->singleton(
            CacheManagerInterface::class,
            CacheManager::class
        );

        // Bind StrategyFactory as singleton
        // This service depends on NamespaceResolver and Application
        $this->app->singleton(
            StrategyFactoryInterface::class,
            StrategyFactory::class
        );

        // Bind DiscoveryManager as singleton
        // This is the main service that coordinates all discovery operations
        // It depends on CacheManager and StrategyFactory
        $this->app->singleton(
            DiscoveryManagerInterface::class,
            DiscoveryManager::class
        );
    }

    /**
     * Bootstrap discovery services.
     *
     * This method is called after all services have been registered.
     * Currently, no bootstrapping logic is required, but this method
     * is kept for future extensions.
     */
    public function boot(): void
    {
        // No bootstrapping required at this time
        // This method is kept for future extensions such as:
        // - Publishing configuration files
        // - Registering console commands
        // - Loading views or translations
    }

    /**
     * Get the services provided by the provider.
     *
     * This method returns an array of service identifiers that this
     * provider registers. Laravel uses this for deferred service loading
     * and to determine which providers to load for specific services.
     *
     * @return array<int, string> Array of service identifiers
     */
    public function provides(): array
    {
        return [
            NamespaceResolverInterface::class,
            CacheManagerInterface::class,
            StrategyFactoryInterface::class,
            DiscoveryManagerInterface::class,
        ];
    }
}
