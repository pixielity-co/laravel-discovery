<?php

namespace Pixielity\Discovery\Strategies;

use olvlvl\ComposerAttributeCollector\Attributes;
use Pixielity\Discovery\Contracts\DiscoveryStrategyInterface;
use Pixielity\Discovery\Support\Reflection;
use RuntimeException;
use Throwable;

/**
 * MethodStrategy - Discovers methods decorated with attributes.
 *
 * This strategy finds all methods that are decorated with a specified PHP attribute
 * using the composer-attribute-collector package. It's particularly useful for
 * discovering route handlers, event listeners, or any method-level functionality
 * that uses attributes.
 *
 * ## Features:
 * - Fast discovery using Composer's attribute collector
 * - Returns method information including class and method name
 * - Supports filtering by attribute properties
 * - Cached results for improved performance
 *
 * ## Usage Examples:
 *
 * ```php
 * // Discover all methods with Route attribute
 * $routes = Discovery::methods(Route::class)
 *     ->where('method', 'GET')
 *     ->get();
 *
 * // Discover event listeners
 * $listeners = Discovery::methods(ListensTo::class)
 *     ->cached('event-listeners')
 *     ->register(function ($method, $metadata) {
 *         Event::listen($metadata['event'], [$method['class'], $method['method']]);
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
class MethodStrategy implements DiscoveryStrategyInterface
{
    /**
     * Create a new MethodStrategy instance.
     *
     * @param class-string $attributeClass Fully qualified attribute class name to search for
     */
    public function __construct(
        /**
         * The fully qualified attribute class name to search for.
         *
         * All discovered methods must be decorated with this attribute.
         */
        protected string $attributeClass
    ) {}

    /**
     * Discover methods decorated with the attribute.
     *
     * Uses the composer-attribute-collector package to find all methods
     * decorated with the specified attribute. Returns an array of method
     * identifiers in the format "ClassName::methodName".
     *
     * The composer-attribute-collector package must be installed and the
     * attribute must be registered in composer.json for this to work.
     *
     * @return array<int, string> Array of method identifiers (ClassName::methodName)
     *
     * @throws RuntimeException If composer-attribute-collector is not installed
     */
    public function discover(): array
    {
        // Check if composer-attribute-collector is available
        throw_unless(Reflection::exists(Attributes::class), RuntimeException::class, 'The composer-attribute-collector package is required for method discovery. '
            . 'Install it with: composer require olvlvl/composer-attribute-collector');

        try {
            // Get all methods decorated with this attribute
            $targetMethods = Attributes::findTargetMethods($this->attributeClass);

            // Convert to method identifier format (ClassName::methodName)
            $methods = [];
            foreach ($targetMethods as $targetMethod) {
                $methods[] = $targetMethod->class . '::' . $targetMethod->name;
            }

            return $methods;
        } catch (Throwable) {
            // If attribute is not registered or other error, return empty array
            return [];
        }
    }

    /**
     * Get metadata for a discovered method.
     *
     * Returns detailed information about the method including:
     * - method: The method identifier (ClassName::methodName)
     * - class: The fully qualified class name
     * - name: The method name
     * - attribute: The attribute class name
     * - instance: The attribute instance with all its properties
     *
     * @param  string               $method Method identifier (ClassName::methodName)
     * @return array<string, mixed> Metadata array
     */
    public function getMetadata(string $method): array
    {
        // Parse method identifier
        [$class, $methodName] = explode('::', $method, 2);

        $metadata = [
            'method' => $method,
            'class' => $class,
            'name' => $methodName,
            'attribute' => null,
            'attributeClass' => $this->attributeClass,
        ];

        // Try to get attribute instance
        try {
            if (Reflection::exists(Attributes::class)) {
                $targetMethods = Attributes::findTargetMethods($this->attributeClass);

                foreach ($targetMethods as $targetMethod) {
                    if ($targetMethod->class === $class && $targetMethod->name === $methodName) {
                        $metadata['attribute'] = $targetMethod->attribute;

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
     * @return string Cache key in format 'method:{md5_hash}'
     */
    public function getCacheKey(): string
    {
        return 'method:' . md5($this->attributeClass);
    }
}
