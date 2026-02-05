<?php

namespace Fulers\Discovery\Providers;

use Fulers\Support\ServiceProvider;
use Override;

/**
 * Discovery Service Provider.
 *
 * Registers the Discovery package services, configuration, and commands.
 *
 * ## Container Bindings:
 * All interface-to-implementation bindings are handled automatically via
 * Laravel 12 container attributes (#[Bind], #[Singleton]) on the interfaces.
 * No manual registration needed.
 *
 * Registered interfaces:
 * - DiscoveryManagerInterface → DiscoveryManager (Singleton)
 * - CacheManagerInterface → CacheManager (Singleton)
 * - NamespaceResolverInterface → NamespaceResolver (Singleton)
 * - StrategyFactoryInterface → StrategyFactory (Singleton)
 */
class DiscoveryServiceProvider extends ServiceProvider
{
    /**
     * The module name.
     *
     * Used for:
     * - Identifying the module in logs and error messages
     * - Namespacing views: `view('discovery::view_name')`
     * - Namespacing translations: `trans('discovery::message')`
     * - Namespacing config: `config('discovery.config_name')`
     */
    protected string $moduleName = 'Discovery';

    /**
     * The module namespace.
     *
     * Used for:
     * - Auto-discovering commands in `Fulers\Discovery\Console\Commands\`
     * - Auto-discovering controllers in `Fulers\Discovery\Controllers\`
     * - Resolving class names for dependency injection
     */
    protected string $moduleNamespace = 'Fulers\Discovery';

    /**
     * Bootstrap any application services.
     *
     * This method is called after all service providers have been registered.
     *
     * ## What Happens Here:
     * 1. Parent boot() loads module resources (migrations, routes, etc.)
     * 2. Auto-discovers discovery classes from configured paths
     * 3. Registers discovered classes using container tags
     * 4. Merges with manually registered discovery
     */
    #[Override]
    public function boot(): void
    {
        // Call parent boot first to load all module resources
        parent::boot();
    }

    /**
     * Register any application services.
     *
     * This method is called during the registration phase, before boot().
     * Use this to bind services into the container.
     */
    #[Override]
    public function register(): void
    {
        // Call parent register for base functionality
        parent::register();
    }
}
