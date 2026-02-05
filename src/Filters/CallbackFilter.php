<?php

namespace Fulers\Discovery\Filters;

use Fulers\Discovery\Contracts\DiscoveryStrategyInterface;
use Fulers\Discovery\Contracts\FilterInterface;
use Fulers\Support\Arr;

/**
 * CallbackFilter - Filters classes using a custom callback.
 *
 * Allows flexible filtering logic through user-defined callbacks.
 */
class CallbackFilter implements FilterInterface
{
    /**
     * Create a new CallbackFilter instance.
     *
     * @param callable $callback Filter callback
     */
    public function __construct(
        protected $callback
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
        return Arr::values(Arr::filter($classes, function (string $class) use ($discoveryStrategy) {
            $metadata = $discoveryStrategy->getMetadata($class);

            return ($this->callback)($class, $metadata);
        }));
    }
}
