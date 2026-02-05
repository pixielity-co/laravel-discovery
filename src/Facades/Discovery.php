<?php

namespace Fulers\Discovery\Facades;

use Fulers\Discovery\Contracts\DiscoveryManagerInterface;
use Fulers\Discovery\DiscoveryBuilder;
use Illuminate\Support\Facades\Facade;
use Symfony\Component\Finder\Finder;

/**
 * Discovery Facade.
 *
 * Provides a static interface to the DiscoveryManager service.
 *
 * @method static DiscoveryBuilder attribute(string $attributeClass)      Discover classes by attribute
 * @method static DiscoveryBuilder directories(string|array $directories) Discover classes in directories
 * @method static DiscoveryBuilder implementing(string $interface)        Discover classes implementing an interface
 * @method static DiscoveryBuilder extending(string $parentClass)         Discover classes extending a parent class
 * @method static DiscoveryBuilder methods(string $attributeClass)        Discover methods decorated with an attribute
 * @method static DiscoveryBuilder properties(string $attributeClass)     Discover properties decorated with an attribute
 * @method static Finder           finder((string|array) $directories)    Get Symfony Finder for advanced file discovery
 * @method static void             clearCache(?string $key = null)        Clear discovery caches
 *
 * @see \Fulers\Discovery\DiscoveryManager
 */
class Discovery extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * Returns the container binding key that this facade resolves to.
     * Uses the interface binding for better testability and flexibility.
     */
    protected static function getFacadeAccessor(): string
    {
        return DiscoveryManagerInterface::class;
    }
}
