<?php

namespace Pixielity\Discovery\Contracts;

/**
 * DiscoveryStrategyInterface Interface.
 *
 * Defines the contract for all discovery strategies.
 * Each strategy implements a different method of discovering classes.
 */
interface DiscoveryStrategyInterface
{
    /**
     * Discover classes using this strategy.
     *
     * @return array<string> Array of fully qualified class names
     */
    public function discover(): array;

    /**
     * Get metadata for a discovered class.
     *
     * @param  string               $class Fully qualified class name
     * @return array<string, mixed> Metadata about the class
     */
    public function getMetadata(string $class): array;

    /**
     * Get cache key for this strategy.
     *
     * @return string Unique cache key
     */
    public function getCacheKey(): string;
}
