<?php

namespace Fulers\Discovery\Contracts;

use Fulers\Discovery\DiscoveryBuilder;
use Fulers\Discovery\DiscoveryManager;
use Illuminate\Container\Attributes\Bind;
use Illuminate\Container\Attributes\Singleton;
use Symfony\Component\Finder\Finder;

/**
 * DiscoveryManagerInterface - Contract for the main discovery service.
 *
 * Defines the interface for discovering classes using various strategies.
 * Implementations should provide methods for attribute-based, directory-based,
 * interface-based, and parent class-based discovery.
 *
 * ## Container Binding:
 * - #[Bind]: Automatically binds this interface to DiscoveryManager implementation
 * - #[Singleton]: Shared instance across the application lifecycle
 *
 * The #[Singleton] attribute ensures a single discovery manager instance is used
 * throughout the application, maintaining consistent discovery behavior and cache state.
 */
#[Singleton]
#[Bind(DiscoveryManager::class)]
interface DiscoveryManagerInterface
{
    /**
     * Discover classes by attribute.
     *
     * Finds all classes decorated with the specified PHP attribute.
     *
     * @param  string           $attributeClass Fully qualified attribute class name
     * @return DiscoveryBuilder Fluent builder for chaining filters and options
     */
    public function attribute(string $attributeClass): DiscoveryBuilder;

    /**
     * Discover classes in directories.
     *
     * Scans the specified directories for PHP files and resolves them to
     * fully qualified class names. Supports glob patterns.
     *
     * @param  string|array<string> $directories Directory path(s) to scan
     * @return DiscoveryBuilder     Fluent builder for chaining filters and options
     */
    public function directories(string|array $directories): DiscoveryBuilder;

    /**
     * Discover classes implementing an interface.
     *
     * Finds all classes that implement the specified interface.
     *
     * @param  string           $interface Fully qualified interface name
     * @return DiscoveryBuilder Fluent builder for chaining filters and options
     */
    public function implementing(string $interface): DiscoveryBuilder;

    /**
     * Discover classes extending a parent class.
     *
     * Finds all classes that extend the specified parent class.
     *
     * @param  string           $parentClass Fully qualified parent class name
     * @return DiscoveryBuilder Fluent builder for chaining filters and options
     */
    public function extending(string $parentClass): DiscoveryBuilder;

    /**
     * Clear discovery caches.
     *
     * Clears cached discovery results.
     *
     * @param string|null $key Specific cache key to clear, or null to clear all
     */
    public function clearCache(?string $key = null): void;

    /**
     * Discover methods decorated with an attribute.
     *
     * Finds all methods decorated with the specified PHP attribute.
     *
     * @param  string           $attributeClass Fully qualified attribute class name
     * @return DiscoveryBuilder Fluent builder for chaining filters and options
     */
    public function methods(string $attributeClass): DiscoveryBuilder;

    /**
     * Discover properties decorated with an attribute.
     *
     * Finds all properties decorated with the specified PHP attribute.
     *
     * @param  string           $attributeClass Fully qualified attribute class name
     * @return DiscoveryBuilder Fluent builder for chaining filters and options
     */
    public function properties(string $attributeClass): DiscoveryBuilder;

    /**
     * Get a Symfony Finder instance for advanced file discovery.
     *
     * Provides direct access to Symfony Finder as an escape hatch for
     * advanced use cases not covered by standard discovery methods.
     *
     * @param  string|array<string> $directories Directory path(s) to search
     * @return Finder               Finder instance for advanced filtering
     */
    public function finder(string|array $directories): Finder;
}
