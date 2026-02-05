<?php

namespace Fulers\Discovery\Contracts;

/**
 * Filter Interface.
 *
 * Defines the contract for filtering discovered classes.
 */
interface FilterInterface
{
    /**
     * Apply filter to discovered classes.
     *
     * @param  array<string>              $classes           Classes to filter
     * @param  DiscoveryStrategyInterface $discoveryStrategy Discovery strategy for metadata access
     * @return array<string>              Filtered classes
     */
    public function apply(array $classes, DiscoveryStrategyInterface $discoveryStrategy): array;
}
