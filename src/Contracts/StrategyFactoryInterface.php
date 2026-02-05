<?php

namespace Fulers\Discovery\Contracts;

use Fulers\Discovery\Factories\StrategyFactory;
use Illuminate\Container\Attributes\Bind;
use Illuminate\Container\Attributes\Scoped;

/**
 * StrategyFactoryInterface - Contract for creating discovery strategies.
 *
 * Defines the interface for factory classes that create discovery strategy instances.
 * This allows proper dependency injection instead of manual container resolution.
 *
 * ## Container Binding:
 * - #[Bind]: Automatically binds this interface to StrategyFactory implementation
 * - #[Singleton]: Shared instance across the application lifecycle
 *
 * The #[Singleton] attribute ensures a single strategy factory instance is used
 * throughout the application, maintaining consistent strategy creation.
 */
#[Scoped]
#[Bind(StrategyFactory::class)]
interface StrategyFactoryInterface
{
    /**
     * Create an attribute discovery strategy.
     *
     * @param  string                     $attributeClass Fully qualified attribute class name
     * @return DiscoveryStrategyInterface The created strategy instance
     */
    public function createAttributeStrategy(string $attributeClass): DiscoveryStrategyInterface;

    /**
     * Create a directory discovery strategy.
     *
     * @param  string|array<string>       $directories Directory path(s) to scan
     * @return DiscoveryStrategyInterface The created strategy instance
     */
    public function createDirectoryStrategy(string|array $directories): DiscoveryStrategyInterface;

    /**
     * Create an interface discovery strategy.
     *
     * @param  string                     $interface Fully qualified interface name
     * @return DiscoveryStrategyInterface The created strategy instance
     */
    public function createInterfaceStrategy(string $interface): DiscoveryStrategyInterface;

    /**
     * Create a parent class discovery strategy.
     *
     * @param  string                     $parentClass Fully qualified parent class name
     * @return DiscoveryStrategyInterface The created strategy instance
     */
    public function createParentClassStrategy(string $parentClass): DiscoveryStrategyInterface;

    /**
     * Create a method discovery strategy.
     *
     * @param  string                     $attributeClass Fully qualified attribute class name
     * @return DiscoveryStrategyInterface The created strategy instance
     */
    public function createMethodStrategy(string $attributeClass): DiscoveryStrategyInterface;

    /**
     * Create a property discovery strategy.
     *
     * @param  string                     $attributeClass Fully qualified attribute class name
     * @return DiscoveryStrategyInterface The created strategy instance
     */
    public function createPropertyStrategy(string $attributeClass): DiscoveryStrategyInterface;
}
