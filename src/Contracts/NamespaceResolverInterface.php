<?php

namespace Fulers\Discovery\Contracts;

use Fulers\Discovery\Resolvers\NamespaceResolver;
use Illuminate\Container\Attributes\Bind;
use Illuminate\Container\Attributes\Singleton;
use Symfony\Component\Finder\SplFileInfo;

/**
 * NamespaceResolverInterface - Contract for namespace resolution.
 *
 * Defines the interface for converting file paths to fully qualified class names.
 * Implementations should handle monorepo structures and custom namespace patterns.
 *
 * ## Container Binding:
 * - #[Bind]: Automatically binds this interface to NamespaceResolver implementation
 * - #[Singleton]: Shared instance across the application lifecycle
 *
 * The #[Singleton] attribute ensures a single namespace resolver instance is used
 * throughout the application, maintaining consistent namespace resolution logic.
 */
#[Singleton]
#[Bind(NamespaceResolver::class)]
interface NamespaceResolverInterface
{
    /**
     * Resolve class name from file.
     *
     * Converts a file path to a fully qualified class name using either
     * a custom pattern or default monorepo patterns.
     *
     * @param  SplFileInfo $file    File info object
     * @param  string|null $pattern Custom namespace pattern with placeholders
     * @return string|null Fully qualified class name, or null if resolution fails
     */
    public function resolveFromFile(SplFileInfo $file, ?string $pattern = null): ?string;
}
