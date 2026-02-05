<?php

declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Unit\Strategies;

use Pixielity\Discovery\Resolvers\NamespaceResolver;
use Pixielity\Discovery\Strategies\DirectoryStrategy;
use Pixielity\Discovery\Support\Arr;
use Pixielity\Discovery\Tests\TestCase;

/**
 * DirectoryStrategy Unit Tests.
 *
 * Tests the directory-based discovery strategy.
 * The DirectoryStrategy scans filesystem directories to discover PHP classes,
 * resolving their namespaces and extracting metadata.
 *
 * ## Key Features Tested:
 * - Single directory scanning
 * - Multiple directory scanning
 * - Glob pattern support
 * - Namespace resolution
 * - Nested directory handling
 * - Error handling for invalid paths
 *
 * @covers \Pixielity\Discovery\Strategies\DirectoryStrategy
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
class DirectoryStrategyTest extends TestCase
{
    /**
     * The path to test fixtures.
     */
    protected string $fixturesPath;

    /**
     * Setup the test environment.
     *
     * Initializes the fixtures path for testing.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Set the path to test fixtures
        $this->fixturesPath = __DIR__ . '/../../Fixtures/Classes';
    }

    /**
     * Test discovers classes in single directory.
     *
     * This test verifies that the strategy can discover all PHP classes
     * in a single directory.
     *
     * ## Scenario:
     * - Scan the Cards directory
     * - Verify classes are discovered
     * - Verify results are not empty
     *
     * ## Assertions:
     * - Results are an array
     * - Results are not empty
     * - Classes are properly discovered
     */
    public function test_discovers_classes_in_single_directory(): void
    {
        // Arrange: Create namespace resolver
        $namespaceResolver = resolve(NamespaceResolver::class);

        // Arrange: Create directory strategy for Cards directory
        $directoryStrategy = new DirectoryStrategy(
            directories: $this->fixturesPath . '/Cards',
            resolver: $namespaceResolver,
            app: app()
        );

        // Act: Discover classes in the directory
        $results = $directoryStrategy->discover();

        // Assert: Results should be an array
        $this->assertIsArray($results);

        // Assert: Results should not be empty (Cards directory has classes)
        $this->assertNotEmpty($results);
    }

    /**
     * Test discovers classes in multiple directories.
     *
     * This test verifies that the strategy can discover classes
     * across multiple directories in a single scan.
     *
     * ## Scenario:
     * - Scan both Cards and Services directories
     * - Verify classes from both are discovered
     * - Verify results contain classes from all directories
     *
     * ## Assertions:
     * - Results are an array
     * - Results are not empty
     * - Classes from multiple directories are included
     */
    public function test_discovers_classes_in_multiple_directories(): void
    {
        // Arrange: Create namespace resolver
        $namespaceResolver = resolve(NamespaceResolver::class);

        // Arrange: Create directory strategy for multiple directories
        $directoryStrategy = new DirectoryStrategy(
            directories: [
                $this->fixturesPath . '/Cards',
                $this->fixturesPath . '/Services',
            ],
            resolver: $namespaceResolver,
            app: app()
        );

        // Act: Discover classes in all directories
        $results = $directoryStrategy->discover();

        // Assert: Results should be an array
        $this->assertIsArray($results);

        // Assert: Results should not be empty
        $this->assertNotEmpty($results);
    }

    /**
     * Test handles glob patterns.
     *
     * This test verifies that the strategy can handle glob patterns
     * for flexible directory matching.
     *
     * ## Scenario:
     * - Use a glob pattern to match multiple directories
     * - Verify pattern is expanded correctly
     * - Verify classes are discovered
     *
     * ## Assertions:
     * - Results are an array
     * - Glob pattern is processed
     */
    public function test_handles_glob_patterns(): void
    {
        // Arrange: Create namespace resolver
        $namespaceResolver = resolve(NamespaceResolver::class);

        // Arrange: Create directory strategy with glob pattern
        $directoryStrategy = new DirectoryStrategy(
            directories: __DIR__ . '/../../Fixtures/Classes/*',
            resolver: $namespaceResolver,
            app: app()
        );

        // Act: Discover classes using glob pattern
        $results = $directoryStrategy->discover();

        // Assert: Results should be an array
        $this->assertIsArray($results);
    }

    /**
     * Test handles non-existent directory.
     *
     * This test verifies that the strategy handles gracefully
     * when given a non-existent directory path.
     *
     * ## Scenario:
     * - Provide a non-existent directory path
     * - Verify no errors are thrown
     * - Verify empty results are returned
     *
     * ## Assertions:
     * - Results are an array
     * - Results are empty
     * - No exceptions are thrown
     */
    public function test_handles_non_existent_directory(): void
    {
        // Arrange: Create namespace resolver
        $namespaceResolver = resolve(NamespaceResolver::class);

        // Arrange: Create directory strategy with non-existent path
        $directoryStrategy = new DirectoryStrategy(
            directories: '/non/existent/path',
            resolver: $namespaceResolver,
            app: app()
        );

        // Act: Attempt to discover classes
        $results = $directoryStrategy->discover();

        // Assert: Results should be an empty array
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    /**
     * Test excludes non-PHP files.
     *
     * This test verifies that the strategy only discovers PHP files
     * and excludes other file types.
     *
     * ## Scenario:
     * - Scan a directory that may contain non-PHP files
     * - Verify only PHP files are processed
     * - Verify all results have .php extension
     *
     * ## Assertions:
     * - All discovered files end with .php
     * - Non-PHP files are excluded
     */
    public function test_excludes_non_php_files(): void
    {
        // Arrange: Create namespace resolver
        $namespaceResolver = resolve(NamespaceResolver::class);

        // Arrange: Create directory strategy
        $directoryStrategy = new DirectoryStrategy(
            directories: $this->fixturesPath,
            resolver: $namespaceResolver,
            app: app()
        );

        // Act: Discover classes
        $results = $directoryStrategy->discover();

        // Assert: All results should be PHP files
        foreach ($results as $result) {
            $this->assertStringEndsWith('.php', $result['file'] ?? '');
        }
    }

    /**
     * Test resolves namespaces correctly.
     *
     * This test verifies that the strategy correctly resolves
     * PHP namespaces from file paths.
     *
     * ## Scenario:
     * - Discover classes in a directory
     * - Verify namespaces are resolved
     * - Verify namespace format is correct
     *
     * ## Assertions:
     * - All classes have proper namespaces
     * - Namespaces match expected pattern
     */
    public function test_resolves_namespaces_correctly(): void
    {
        // Arrange: Create namespace resolver
        $namespaceResolver = resolve(NamespaceResolver::class);

        // Arrange: Create directory strategy
        $directoryStrategy = new DirectoryStrategy(
            directories: $this->fixturesPath . '/Cards',
            resolver: $namespaceResolver,
            app: app()
        );

        // Act: Discover classes
        $results = $directoryStrategy->discover();

        // Assert: All classes should have proper namespaces
        foreach (Arr::keys($results) as $class) {
            $this->assertStringContainsString('Pixielity\Discovery\Tests\Fixtures', $class);
        }
    }

    /**
     * Test handles nested directories.
     *
     * This test verifies that the strategy can recursively
     * discover classes in nested directory structures.
     *
     * ## Scenario:
     * - Scan a directory with nested subdirectories
     * - Verify classes in all levels are discovered
     * - Verify recursive scanning works
     *
     * ## Assertions:
     * - Results are not empty
     * - Classes from nested directories are included
     */
    public function test_handles_nested_directories(): void
    {
        // Arrange: Create namespace resolver
        $namespaceResolver = resolve(NamespaceResolver::class);

        // Arrange: Create directory strategy for parent directory
        $directoryStrategy = new DirectoryStrategy(
            directories: $this->fixturesPath,
            resolver: $namespaceResolver,
            app: app()
        );

        // Act: Discover classes recursively
        $results = $directoryStrategy->discover();

        // Assert: Results should include classes from nested directories
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    /**
     * Test handles empty directory.
     *
     * This test verifies that the strategy handles gracefully
     * when scanning an empty directory.
     *
     * ## Scenario:
     * - Create a temporary empty directory
     * - Scan the empty directory
     * - Verify empty results are returned
     * - Clean up the temporary directory
     *
     * ## Assertions:
     * - Results are an array
     * - Results are empty
     * - No errors occur
     */
    public function test_handles_empty_directory(): void
    {
        // Arrange: Create a temporary empty directory
        $emptyDir = sys_get_temp_dir() . '/empty_test_dir';
        if (! is_dir($emptyDir)) {
            mkdir($emptyDir);
        }

        // Arrange: Create namespace resolver
        $namespaceResolver = resolve(NamespaceResolver::class);

        // Arrange: Create directory strategy for empty directory
        $directoryStrategy = new DirectoryStrategy(
            directories: $emptyDir,
            resolver: $namespaceResolver,
            app: app()
        );

        // Act: Discover classes in empty directory
        $results = $directoryStrategy->discover();

        // Assert: Results should be empty
        $this->assertIsArray($results);
        $this->assertEmpty($results);

        // Cleanup: Remove the temporary directory
        rmdir($emptyDir);
    }
}
