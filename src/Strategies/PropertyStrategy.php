<?php

namespace Pixielity\Discovery\Strategies;

use olvlvl\ComposerAttributeCollector\Attributes;
use Pixielity\Discovery\Contracts\DiscoveryStrategyInterface;
use Pixielity\Discovery\Support\Reflection;
use RuntimeException;
use Throwable;

/**
 * PropertyStrategy - Discovers properties decorated with attributes.
 *
 * This strategy finds all class properties that are decorated with a specified
 * PHP attribute using the composer-attribute-collector package. It's particularly
 * useful for discovering configuration properties, validation rules, or any
 * property-level metadata that uses attributes.
 *
 * ## Features:
 * - Fast discovery using Composer's attribute collector
 * - Returns property information including class and property name
 * - Supports filtering by attribute properties
 * - Cached results for improved performance
 *
 * ## Usage Examples:
 *
 * ```php
 * // Discover all properties with Validate attribute
 * $validations = Discovery::properties(Validate::class)
 *     ->where('required', true)
 *     ->get();
 *
 * // Discover configuration properties
 * $configs = Discovery::properties(Config::class)
 *     ->cached('config-properties')
 *     ->register(function ($property, $metadata) {
 *         // Register configuration
 *     });
 * ```
 *
 * ## Requirements:
 * - Requires `olvlvl/composer-attribute-collector` package
 * - Attributes must be registered in composer.json extra section
 *
 * ## Performance:
 * - Very fast as it uses Composer's cached attribute data
 * - No filesystem scanning required
 * - Recommended for production use
 */
class PropertyStrategy implements DiscoveryStrategyInterface
{
    /**
     * Create a new PropertyStrategy instance.
     *
     * @param class-string $attributeClass Fully qualified attribute class name to search for
     */
    public function __construct(
        /**
         * The fully qualified attribute class name to search for.
         *
         * All discovered properties must be decorated with this attribute.
         */
        protected string $attributeClass
    ) {}

    /**
     * Discover properties decorated with the attribute.
     *
     * Uses the composer-attribute-collector package to find all properties
     * decorated with the specified attribute. Returns an array of property
     * identifiers in the format "ClassName::$propertyName".
     *
     * The composer-attribute-collector package must be installed and the
     * attribute must be registered in composer.json for this to work.
     *
     * @return array<int, string> Array of property identifiers (ClassName::$propertyName)
     *
     * @throws RuntimeException If composer-attribute-collector is not installed
     */
    public function discover(): array
    {
        // Check if composer-attribute-collector is available
        throw_unless(Reflection::exists(Attributes::class), RuntimeException::class, 'The composer-attribute-collector package is required for property discovery. '
            . 'Install it with: composer require olvlvl/composer-attribute-collector');

        try {
            // Get all properties decorated with this attribute
            $targetProperties = Attributes::findTargetProperties($this->attributeClass);

            // Convert to property identifier format (ClassName::$propertyName)
            $properties = [];
            foreach ($targetProperties as $targetProperty) {
                $properties[] = $targetProperty->class . '::$' . $targetProperty->name;
            }

            return $properties;
        } catch (Throwable) {
            // If attribute is not registered or other error, return empty array
            return [];
        }
    }

    /**
     * Get metadata for a discovered property.
     *
     * Returns detailed information about the property including:
     * - property: The property identifier (ClassName::$propertyName)
     * - class: The fully qualified class name
     * - name: The property name (without $)
     * - attribute: The attribute class name
     * - instance: The attribute instance with all its properties
     *
     * @param  string               $property Property identifier (ClassName::$propertyName)
     * @return array<string, mixed> Metadata array
     */
    public function getMetadata(string $property): array
    {
        // Parse property identifier (remove $ prefix if present)
        [$class, $propertyName] = explode('::', $property, 2);
        $propertyName = ltrim($propertyName, '$');

        $metadata = [
            'property' => $property,
            'class' => $class,
            'name' => $propertyName,
            'attribute' => null,
            'attributeClass' => $this->attributeClass,
        ];

        // Try to get attribute instance
        try {
            if (Reflection::exists(Attributes::class)) {
                $targetProperties = Attributes::findTargetProperties($this->attributeClass);

                foreach ($targetProperties as $targetProperty) {
                    if ($targetProperty->class === $class && $targetProperty->name === $propertyName) {
                        $metadata['attribute'] = $targetProperty->attribute;

                        break;
                    }
                }
            }
        } catch (Throwable) {
            // Ignore errors when getting attribute instance
        }

        return $metadata;
    }

    /**
     * Get cache key for this strategy.
     *
     * Generates a unique cache key based on the attribute class name. The key
     * is MD5 hashed to ensure it's filesystem-safe and consistent length.
     *
     * @return string Cache key in format 'property:{md5_hash}'
     */
    public function getCacheKey(): string
    {
        return 'property:' . md5($this->attributeClass);
    }
}
