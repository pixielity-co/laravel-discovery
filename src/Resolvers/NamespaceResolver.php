<?php

namespace Pixielity\Discovery\Resolvers;

use Illuminate\Support\Str;
use Pixielity\Discovery\Contracts\NamespaceResolverInterface;
use Symfony\Component\Finder\SplFileInfo;

/**
 * NamespaceResolver - Resolves file paths to fully qualified class names.
 *
 * Handles monorepo structures with packages, modules, and app directories.
 * Supports custom namespace patterns with placeholders.
 */
class NamespaceResolver implements NamespaceResolverInterface
{
    /**
     * Resolve class name from file.
     *
     * @param  SplFileInfo $file    File info
     * @param  string|null $pattern Custom namespace pattern
     * @return string|null Fully qualified class name
     */
    public function resolveFromFile(SplFileInfo $file, ?string $pattern = null): ?string
    {
        $path = $file->getRealPath();

        // Use custom pattern if provided
        if ($pattern !== null) {
            return $this->resolveWithPattern($path, $pattern);
        }

        // Try monorepo patterns
        return $this->resolveMonorepoPattern($path);
    }

    /**
     * Resolve using custom pattern.
     *
     * Pattern placeholders:
     * - {package}: Package name
     * - {module}: Module name
     * - {class}: Class name
     * - {namespace}: Remaining namespace path
     *
     * @param  string      $path    File path
     * @param  string      $pattern Namespace pattern
     * @return string|null Resolved class name
     */
    protected function resolveWithPattern(string $path, string $pattern): ?string
    {
        // Extract package name
        if (preg_match('#/packages/([^/]+)/#', $path, $matches)) {
            $package = $matches[1];
            $pattern = Str::replace('{package}', $package, $pattern);
        }

        // Extract module name
        if (preg_match('#/modules/([^/]+)/#', $path, $matches)) {
            $module = $matches[1];
            $pattern = Str::replace('{module}', $module, $pattern);
        }

        // Extract class name
        $className = basename($path, '.php');
        $pattern = Str::replace('{class}', $className, $pattern);
        // Extract namespace path
        if (preg_match('#/src/(.+)/' . preg_quote($className, '#') . '\.php$#', $path, $matches)) {
            $namespace = Str::replace('/', '\\', $matches[1]);

            return Str::replace('{namespace}', $namespace, $pattern);
        }
        // No namespace path, remove placeholder
        $pattern = Str::replace('{namespace}\\', '', $pattern);

        return Str::replace('\{namespace}', '', $pattern);
    }

    /**
     * Resolve using monorepo patterns.
     *
     * @param  string      $path File path
     * @return string|null Resolved class name
     */
    protected function resolveMonorepoPattern(string $path): ?string
    {
        // Pattern: packages/{Package}/src/{Namespace}/{Class}.php
        if (preg_match('#/packages/([^/]+)/src/(.+)\.php$#', $path, $matches)) {
            $package = $matches[1];
            $relativePath = $matches[2];
            $namespace = Str::replace('/', '\\', $relativePath);

            return "Pixielity\\{$package}\\{$namespace}";
        }

        // Pattern: modules/{Module}/src/{Namespace}/{Class}.php
        if (preg_match('#/modules/([^/]+)/src/(.+)\.php$#', $path, $matches)) {
            $module = $matches[1];
            $relativePath = $matches[2];
            $namespace = Str::replace('/', '\\', $relativePath);

            return "Modules\\{$module}\\{$namespace}";
        }

        // Pattern: app/{Namespace}/{Class}.php
        if (preg_match('#/app/(.+)\.php$#', $path, $matches)) {
            $relativePath = $matches[1];
            $namespace = Str::replace('/', '\\', $relativePath);

            return "App\\{$namespace}";
        }

        // Pattern: packages/{Package}/tests/{Namespace}/{Class}.php
        if (preg_match('#/packages/([^/]+)/tests/(.+)\.php$#', $path, $matches)) {
            $package = $matches[1];
            $relativePath = $matches[2];
            $namespace = Str::replace('/', '\\', $relativePath);

            return "Pixielity\\{$package}\\Tests\\{$namespace}";
        }

        // Pattern: tests/{Namespace}/{Class}.php (standalone package)
        if (preg_match('#/tests/(.+)\.php$#', $path, $matches)) {
            $relativePath = $matches[1];
            $namespace = Str::replace('/', '\\', $relativePath);

            // Try to determine the base namespace from composer.json or use a default
            // For now, we'll check if this is in a package directory
            if (preg_match('#/packages/([^/]+)/#', $path, $packageMatches)) {
                $package = $packageMatches[1];

                return "Pixielity\\{$package}\\Tests\\{$namespace}";
            }

            // Fallback for standalone tests
            return "Tests\\{$namespace}";
        }

        return null;
    }
}
