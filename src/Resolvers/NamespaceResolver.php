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
     * Resolve using monorepo patterns and Composer autoload.
     *
     * Check patterns from most specific to least specific to avoid false matches.
     *
     * @param  string      $path File path
     * @return string|null Resolved class name
     */
    protected function resolveMonorepoPattern(string $path): ?string
    {
        // First, try to resolve using Composer's autoload map
        $composerResolved = $this->resolveFromComposerAutoload($path);
        if ($composerResolved !== null) {
            return $composerResolved;
        }

        // Pattern: packages/{Package}/tests/{Namespace}/{Class}.php (check before generic tests pattern)
        if (preg_match('#/packages/([^/]+)/tests/(.+)\.php$#', $path, $matches)) {
            $package = (string) $matches[1];
            $relativePath = (string) $matches[2];
            $namespace = Str::replace('/', '\\', $relativePath);

            return "Pixielity\\{$package}\\Tests\\{$namespace}";
        }

        // Pattern: packages/{Package}/src/{Namespace}/{Class}.php
        if (preg_match('#/packages/([^/]+)/src/(.+)\.php$#', $path, $matches)) {
            $package = (string) $matches[1];
            $relativePath = (string) $matches[2];
            $namespace = Str::replace('/', '\\', $relativePath);

            return "Pixielity\\{$package}\\{$namespace}";
        }

        // Pattern: modules/{Module}/src/{Namespace}/{Class}.php
        if (preg_match('#/modules/([^/]+)/src/(.+)\.php$#', $path, $matches)) {
            $module = (string) $matches[1];
            $relativePath = (string) $matches[2];
            $namespace = Str::replace('/', '\\', $relativePath);

            return "Modules\\{$module}\\{$namespace}";
        }

        // Pattern: app/{Namespace}/{Class}.php
        if (preg_match('#/app/(.+)\.php$#', $path, $matches)) {
            $relativePath = (string) $matches[1];
            $namespace = Str::replace('/', '\\', $relativePath);

            return "App\\{$namespace}";
        }

        return null;
    }

    /**
     * Resolve namespace from Composer's autoload configuration.
     *
     * @param  string      $path File path
     * @return string|null Resolved class name
     */
    protected function resolveFromComposerAutoload(string $path): ?string
    {
        // Get Composer's autoload classmap
        $autoloadFiles = [
            dirname(__DIR__, 2) . '/vendor/autoload.php',
            dirname(__DIR__, 4) . '/autoload.php', // For when this package is installed as a dependency
        ];

        foreach ($autoloadFiles as $autoloadFile) {
            if (file_exists($autoloadFile)) {
                $loader = require $autoloadFile;
                // Ensure $loader is an object with getPrefixesPsr4 method
                if (is_object($loader) && method_exists($loader, 'getPrefixesPsr4')) {
                    $prefixes = $loader->getPrefixesPsr4();

                    // Normalize path for comparison
                    $normalizedPath = str_replace('\\', '/', realpath($path));

                    // Sort by path length (longest first) to match most specific namespace
                    $sortedPrefixes = [];
                    foreach ($prefixes as $namespace => $paths) {
                        foreach ($paths as $prefixPath) {
                            $normalizedPrefixPath = str_replace('\\', '/', realpath($prefixPath));
                            if ($normalizedPrefixPath) {
                                $sortedPrefixes[$namespace] = $normalizedPrefixPath;
                            }
                        }
                    }

                    // Sort by path length descending
                    uasort($sortedPrefixes, function ($a, $b) {
                        return strlen($b) - strlen($a);
                    });

                    // Find matching namespace
                    foreach ($sortedPrefixes as $namespace => $prefixPath) {
                        if (str_starts_with($normalizedPath, $prefixPath)) {
                            // Calculate relative path
                            $relativePath = substr($normalizedPath, strlen($prefixPath) + 1);
                            $relativePath = str_replace('.php', '', $relativePath);
                            $relativeNamespace = str_replace('/', '\\', $relativePath);

                            return rtrim($namespace, '\\') . '\\' . $relativeNamespace;
                        }
                    }
                }

                break;
            }
        }

        return null;
    }
}
