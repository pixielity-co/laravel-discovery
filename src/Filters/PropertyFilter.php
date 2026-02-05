<?php

namespace Pixielity\Discovery\Filters;

use Pixielity\Discovery\Contracts\DiscoveryStrategyInterface;
use Pixielity\Discovery\Contracts\FilterInterface;
use Pixielity\Discovery\Support\Arr;

/**
 * PropertyFilter - Filters classes by attribute property values.
 *
 * Checks if a specific property on the class's attribute matches a value.
 */
class PropertyFilter implements FilterInterface
{
    /**
     * Create a new PropertyFilter instance.
     *
     * @param string $property Property name to check
     * @param mixed  $value    Expected value
     */
    public function __construct(
        protected string $property,
        protected mixed $value
    ) {}

    /**
     * Apply filter to discovered classes.
     *
     * @param  array<string>              $classes           Classes to filter
     * @param  DiscoveryStrategyInterface $discoveryStrategy Discovery strategy
     * @return array<string>              Filtered classes
     */
    public function apply(array $classes, DiscoveryStrategyInterface $discoveryStrategy): array
    {
        return Arr::values(Arr::filter($classes, function (string $class) use ($discoveryStrategy): bool {
            $metadata = $discoveryStrategy->getMetadata($class);
            $attribute = $metadata['attribute'] ?? null;

            if ($attribute === null) {
                return false;
            }

            return ($attribute->{$this->property} ?? null) === $this->value;
        }));
    }
}
