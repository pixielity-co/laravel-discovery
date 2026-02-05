<?php

namespace Fulers\Discovery\Factories;

use Fulers\Discovery\Contracts\DiscoveryStrategyInterface;
use Fulers\Discovery\Contracts\NamespaceResolverInterface;
use Fulers\Discovery\Contracts\StrategyFactoryInterface;
use Fulers\Discovery\Strategies\AttributeStrategy;
use Fulers\Discovery\Strategies\DirectoryStrategy;
use Fulers\Discovery\Strategies\InterfaceStrategy;
use Fulers\Discovery\Strategies\MethodStrategy;
use Fulers\Discovery\Strategies\ParentClassStrategy;
use Fulers\Discovery\Strategies\PropertyStrategy;
use Illuminate\Contracts\Foundation\Application;

/**
 * StrategyFactory - Creates discovery strategy instances with proper dependencies.
 *
 * This factory encapsulates the creation logic for all discovery strategies,
 * ensuring they receive their required dependencies through constructor injection.
 */
class StrategyFactory implements StrategyFactoryInterface
{
    /**
     * Create a new StrategyFactory instance.
     *
     * @param NamespaceResolverInterface $namespaceResolver Namespace resolver
     * @param Application                $app               Laravel application instance
     */
    public function __construct(
        protected NamespaceResolverInterface $namespaceResolver,
        protected Application $app
    ) {}

    /**
     * Create an attribute discovery strategy.
     *
     * @param  string                     $attributeClass Fully qualified attribute class name
     * @return DiscoveryStrategyInterface The created strategy instance
     */
    public function createAttributeStrategy(string $attributeClass): DiscoveryStrategyInterface
    {
        return new AttributeStrategy($attributeClass);
    }

    /**
     * Create a directory discovery strategy.
     *
     * @param  string|array<string>       $directories Directory path(s) to scan
     * @return DiscoveryStrategyInterface The created strategy instance
     */
    public function createDirectoryStrategy(string|array $directories): DiscoveryStrategyInterface
    {
        return new DirectoryStrategy(
            $directories,
            $this->namespaceResolver,
            $this->app
        );
    }

    /**
     * Create an interface discovery strategy.
     *
     * Creates a strategy that discovers classes implementing a specific interface.
     * The strategy is injected with this factory instance to enable directory
     * scanning capabilities via the directories() method.
     *
     * @param  string                     $interface Fully qualified interface name
     * @return DiscoveryStrategyInterface The created strategy instance
     */
    public function createInterfaceStrategy(string $interface): DiscoveryStrategyInterface
    {
        return new InterfaceStrategy($interface, $this);
    }

    /**
     * Create a parent class discovery strategy.
     *
     * Creates a strategy that discovers classes extending a specific parent class.
     * The strategy is injected with this factory instance to enable directory
     * scanning capabilities via the directories() method.
     *
     * @param  string                     $parentClass Fully qualified parent class name
     * @return DiscoveryStrategyInterface The created strategy instance
     */
    public function createParentClassStrategy(string $parentClass): DiscoveryStrategyInterface
    {
        return new ParentClassStrategy($parentClass, $this);
    }

    /**
     * Create a method discovery strategy.
     *
     * Creates a strategy that discovers methods decorated with a specific attribute.
     * Uses the composer-attribute-collector package for fast attribute-based discovery.
     *
     * @param  string                     $attributeClass Fully qualified attribute class name
     * @return DiscoveryStrategyInterface The created strategy instance
     */
    public function createMethodStrategy(string $attributeClass): DiscoveryStrategyInterface
    {
        return new MethodStrategy($attributeClass);
    }

    /**
     * Create a property discovery strategy.
     *
     * Creates a strategy that discovers properties decorated with a specific attribute.
     * Uses the composer-attribute-collector package for fast attribute-based discovery.
     *
     * @param  string                     $attributeClass Fully qualified attribute class name
     * @return DiscoveryStrategyInterface The created strategy instance
     */
    public function createPropertyStrategy(string $attributeClass): DiscoveryStrategyInterface
    {
        return new PropertyStrategy($attributeClass);
    }
}
