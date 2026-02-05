<?php

namespace Pixielity\Discovery\Strategies;

use Olvlvl\ComposerAttributeCollector\Attributes;
use olvlvl\ComposerAttributeCollector\TargetClass;
use Pixielity\Discovery\Contracts\DiscoveryStrategyInterface;
use Pixielity\Discovery\Support\Arr;
use Pixielity\Discovery\Support\Reflection;
use Throwable;

/**
 * AttributeStrategy - Discovers classes by PHP attributes.
 *
 * Uses the composer-attribute-collector package for efficient attribute-based
 * discovery with built-in caching.
 */
class AttributeStrategy implements DiscoveryStrategyInterface
{
    /**
     * Discovered targets.
     *
     * @var array<\Olvlvl\ComposerAttributeCollector\TargetClass>
     */
    protected array $targets = [];

    /**
     * Create a new AttributeStrategy instance.
     *
     * @param string $attributeClass Fully qualified attribute class name
     */
    public function __construct(
        protected string $attributeClass
    ) {}

    /**
     * Discover classes with the specified attribute.
     *
     * @return array<string> Array of class names
     */
    public function discover(): array
    {
        // Check if composer-attribute-collector is available
        if (!Reflection::exists(Attributes::class)) {
            return [];
        }

        try {
            $this->targets = Attributes::findTargetClasses($this->attributeClass);

            return Arr::mapValues(
                fn(TargetClass $targetClass): string => $targetClass->name,
                $this->targets
            );
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Get metadata for a discovered class.
     *
     * @param  string               $class Class name
     * @return array<string, mixed> Metadata including attribute instance
     */
    public function getMetadata(string $class): array
    {
        $target = collect($this->targets)->firstWhere('name', $class);

        return [
            'attribute' => $target?->attribute,
            'class' => $class,
        ];
    }

    /**
     * Get cache key for this strategy.
     *
     * @return string Cache key
     */
    public function getCacheKey(): string
    {
        // Attributes are already cached by composer-attribute-collector
        return 'attribute:' . md5($this->attributeClass);
    }
}
