<?php

namespace Pixielity\Discovery\Strategies;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Pixielity\Discovery\Contracts\DiscoveryStrategyInterface;
use Pixielity\Discovery\Resolvers\NamespaceResolver;
use Pixielity\Discovery\Support\Arr;
use Pixielity\Discovery\Support\Reflection;
use Symfony\Component\Finder\Finder;
use Throwable;

/**
 * DirectoryStrategy - Discovers classes by scanning directories.
 *
 * This strategy scans specified directories for PHP files and resolves them to
 * fully qualified class names using namespace resolution. It's particularly useful
 * for monorepo structures where classes are organized across multiple packages.
 *
 * Features:
 * - Supports glob patterns (e.g., 'packages/*\/src/Settings')
 * - Automatic namespace resolution for packages, modules, and app directories
 * - Custom namespace patterns with placeholders
 * - Validates that discovered classes actually exist
 */
class DirectoryStrategy implements DiscoveryStrategyInterface
{
    /**
     * The directories to scan for PHP files.
     *
     * Can include glob patterns like 'packages/*\/src/Settings' which will be
     * expanded to match multiple directories.
     *
     * @var array<string>
     */
    protected array $directories;

    /**
     * Custom namespace pattern for resolving class names.
     *
     * If null, the default monorepo patterns will be used. When set, this pattern
     * is used to convert file paths to class names using placeholders like
     * {package}, {module}, {class}, and {namespace}.
     */
    protected ?string $namespacePattern = null;

    /**
     * Create a new DirectoryStrategy instance.
     *
     * This constructor uses dependency injection to receive all required services.
     * The directories parameter can be a single string or an array of directory paths.
     *
     * @param string|array<string> $directories Directory path(s) to scan
     * @param NamespaceResolver    $resolver    Namespace resolver instance
     * @param Application          $app         Laravel application instance
     */
    public function __construct(
        string|array $directories,
        protected NamespaceResolver $resolver,
        protected Application $app
    ) {
        // Normalize directories to always be an array
        $this->directories = is_array($directories) ? $directories : [$directories];
    }

    /**
     * Set directories to scan.
     *
     * Allows changing the directories after instantiation. Useful when the same
     * strategy instance needs to scan different directories.
     *
     * @param string|array<string> $directories Directory path(s) to scan
     */
    public function setDirectories(string|array $directories): void
    {
        // Normalize to array format
        $this->directories = is_array($directories) ? $directories : [$directories];
    }

    /**
     * Set custom namespace pattern.
     *
     * Overrides the default monorepo namespace resolution with a custom pattern.
     * The pattern can include placeholders:
     * - {package}: Package name from packages/* directory
     * - {module}: Module name from modules/* directory
     * - {class}: The class name (filename without .php)
     * - {namespace}: The remaining namespace path after src/
     *
     * Example: 'Pixielity\{package}\{namespace}\{class}'
     *
     * @param string $pattern Namespace pattern with placeholders
     */
    public function setNamespacePattern(string $pattern): void
    {
        $this->namespacePattern = $pattern;
    }

    /**
     * Discover classes in directories.
     *
     * Scans all specified directories for PHP files, resolves them to class names,
     * and validates that the classes actually exist. Glob patterns in directory
     * paths are automatically expanded.
     *
     * The discovery process:
     * 1. Expand glob patterns to get actual directory paths
     * 2. Use Symfony Finder to locate all .php files
     * 3. Resolve each file path to a fully qualified class name
     * 4. Validate that the class exists (using class_exists)
     * 5. Return array of discovered class names
     *
     * @return array<string> Array of fully qualified class names
     */
    public function discover(): array
    {
        $classes = [];

        // Expand glob patterns in directory paths
        // Example: 'packages/*\/src/Settings' becomes ['packages/Auth/src/Settings', 'packages/Users/src/Settings', ...]
        $expandedDirs = $this->expandDirectories($this->directories);

        // If no valid directories found, return empty array
        if ($expandedDirs === []) {
            return [];
        }

        // Create a Symfony Finder instance to scan for PHP files
        // Finder is more efficient than glob() for recursive directory scanning
        $finder = new Finder()
            ->files()  // Only find files, not directories
            ->name('*.php')  // Only PHP files
            ->in($expandedDirs);  // In the expanded directories

        // Process each discovered PHP file
        foreach ($finder as $file) {
            // Resolve the file path to a fully qualified class name
            // Uses the namespace resolver with optional custom pattern
            $class = $this->resolver->resolveFromFile(
                $file,
                $this->namespacePattern
            );

            // Only include the class if:
            // 1. Resolution was successful (not null)
            // 2. The class actually exists (can be autoloaded)
            if ($class !== null && Reflection::exists($class)) {
                $classes[] = $class;
            }
        }

        return $classes;
    }

    /**
     * Get metadata for a discovered class.
     *
     * Returns metadata including the file path where the class is defined.
     * Uses the Framework's Reflection utility for consistent reflection operations.
     *
     * @param  string               $class Class name
     * @return array<string, mixed> Metadata including file path
     */
    public function getMetadata(string $class): array
    {
        try {
            // Use Framework's Reflection utility instead of manual ReflectionClass
            $file = Reflection::getFileName($class);

            return [
                'class' => $class,
                'file' => $file !== false ? $file : null,
            ];
        } catch (Throwable) {
            // If reflection fails, return minimal metadata
            return [
                'class' => $class,
                'file' => null,
            ];
        }
    }

    /**
     * Get cache key for this strategy.
     *
     * @return string Cache key
     */
    public function getCacheKey(): string
    {
        return 'directory:' . md5(serialize($this->directories));
    }

    /**
     * Get the expanded directories being scanned.
     *
     * Returns the actual directory paths after expanding glob patterns.
     * Useful when you need the directory paths themselves rather than
     * the classes within them.
     *
     * @return array<string> Array of expanded directory paths
     */
    public function getDirectories(): array
    {
        return $this->expandDirectories($this->directories);
    }

    /**
     * Expand glob patterns in directories.
     *
     * Converts directory paths with glob patterns into actual directory paths.
     * Also handles relative paths by converting them to absolute paths using
     * the application's base path.
     *
     * Examples:
     * - 'packages/*\/src' → ['packages/Auth/src', 'packages/Users/src', ...]
     * - 'app/Services' → ['/full/path/to/app/Services']
     *
     * @param  array<string> $directories Directories with potential glob patterns
     * @return array<string> Expanded absolute directory paths
     */
    protected function expandDirectories(array $directories): array
    {
        $expanded = [];

        foreach ($directories as $directory) {
            // Convert relative paths to absolute paths using the app's base path
            // This ensures consistent path handling across different environments
            if (! Str::startsWith($directory, '/')) {
                $directory = $this->app->basePath($directory);
            }

            // Check if the directory contains glob patterns (* or ?)
            if (Str::contains($directory, '*')) {
                // Use glob() to expand the pattern
                // GLOB_ONLYDIR ensures we only get directories, not files
                $matches = File::glob($directory, GLOB_ONLYDIR);

                // Merge the matched directories into our result array
                $expanded = Arr::merge($expanded, $matches);
            } else {
                // No glob pattern, just add the directory as-is
                $expanded[] = $directory;
            }
        }

        // Filter out any paths that aren't actually directories
        // This handles cases where paths don't exist or are files
        return Arr::filter($expanded, fn ($path) => File::isDirectory($path));
    }
}
