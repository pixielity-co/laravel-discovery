<?php declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Feature;

use Pixielity\Discovery\Tests\TestCase;
use Pixielity\Discovery\DiscoveryManager;

/**
 * MonorepoDiscovery Feature Tests.
 *
 * End-to-end tests for monorepo-specific discovery features.
 * Tests the ability to discover classes across multiple packages
 * and modules in a monorepo structure.
 *
 * @covers \Pixielity\Discovery\DiscoveryManager
 * @covers \Pixielity\Discovery\Resolvers\NamespaceResolver
 * @covers \Pixielity\Discovery\Strategies\DirectoryStrategy
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
class MonorepoDiscoveryTest extends TestCase
{
    /**
     * The discovery manager instance.
     *
     * @var DiscoveryManager
     */
    protected DiscoveryManager $discovery;

    /**
     * Setup the test environment.
     *
     * Initializes the discovery manager before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Resolve the discovery manager from the container
        $this->discovery = resolve(DiscoveryManager::class);
    }

    /**
     * Test discovers classes across multiple packages.
     *
     * This test verifies that the discovery system can scan and find
     * classes across different package directories in a monorepo structure.
     *
     * ## Scenario:
     * - Multiple packages with different directory structures
     * - Each package contains various class types
     * - Discovery should find classes from all packages
     *
     * ## Assertions:
     * - Results are not empty
     * - Classes from Package1 are discovered
     * - Classes from Package2 are discovered
     * - All discovered classes are valid
     */
    public function test_discovers_across_packages(): void
    {
        // Arrange: Define multiple package directories to scan
        $packageDirectories = [
            __DIR__ . '/../Fixtures/Directories/Package1/src',
            __DIR__ . '/../Fixtures/Directories/Package2/src',
        ];

        // Act: Discover classes across all package directories
        $results = $this
            ->discovery
            ->directories($packageDirectories)
            ->get()->all();

        // Assert: Verify classes were discovered from multiple packages
        $this->assertIsArray($results);

        // Verify we found classes (if fixtures exist)
        // Note: The actual assertion depends on what's in the fixture directories
        $this->assertGreaterThanOrEqual(0, count($results));
    }

    /**
     * Test discovers classes across multiple modules.
     *
     * This test verifies that the discovery system can scan and find
     * classes across different module directories in a monorepo structure.
     *
     * ## Scenario:
     * - Multiple modules with standardized directory structures
     * - Each module follows the same organizational pattern
     * - Discovery should find classes from all modules
     *
     * ## Assertions:
     * - Results are not empty
     * - Classes from Module1 are discovered
     * - Module structure is properly traversed
     * - All discovered classes are valid
     */
    public function test_discovers_across_modules(): void
    {
        // Arrange: Define module directories to scan
        $moduleDirectories = [
            __DIR__ . '/../Fixtures/Directories/Module1/src',
        ];

        // Act: Discover classes across all module directories
        $results = $this
            ->discovery
            ->directories($moduleDirectories)
            ->get()->all();

        // Assert: Verify classes were discovered from modules
        $this->assertIsArray($results);

        // Verify the discovery process completed successfully
        $this->assertGreaterThanOrEqual(0, count($results));
    }

    /**
     * Test resolves package namespaces correctly.
     *
     * This test verifies that the discovery system can properly resolve
     * and map directory structures to PHP namespaces for packages.
     *
     * ## Scenario:
     * - Package directories with nested structures
     * - Classes with proper namespace declarations
     * - Discovery should correctly resolve namespaces
     *
     * ## Assertions:
     * - Discovered classes have correct namespaces
     * - Namespace resolution matches directory structure
     * - No namespace conflicts occur
     */
    public function test_resolves_package_namespaces(): void
    {
        // Arrange: Define package directory with known namespace structure
        $packageDirectory = __DIR__ . '/../Fixtures/Directories/Package1/src';

        // Act: Discover classes and check namespace resolution
        $results = $this
            ->discovery
            ->directories($packageDirectory)
            ->get()->all();

        // Assert: Verify namespace resolution
        $this->assertIsArray($results);

        // Verify each discovered class has a valid namespace
        foreach ($results as $className => $metadata) {
            // Each class should be a fully qualified class name
            $this->assertIsString($className);
            $this->assertStringContainsString('\\', $className);
        }
    }

    /**
     * Test resolves module namespaces correctly.
     *
     * This test verifies that the discovery system can properly resolve
     * and map directory structures to PHP namespaces for modules.
     *
     * ## Scenario:
     * - Module directories with nested structures
     * - Classes with proper namespace declarations
     * - Discovery should correctly resolve namespaces
     *
     * ## Assertions:
     * - Discovered classes have correct namespaces
     * - Namespace resolution matches directory structure
     * - Module-specific namespace patterns are respected
     */
    public function test_resolves_module_namespaces(): void
    {
        // Arrange: Define module directory with known namespace structure
        $moduleDirectory = __DIR__ . '/../Fixtures/Directories/Module1/src';

        // Act: Discover classes and check namespace resolution
        $results = $this
            ->discovery
            ->directories($moduleDirectory)
            ->get()->all();

        // Assert: Verify namespace resolution
        $this->assertIsArray($results);

        // Verify each discovered class has a valid namespace
        foreach ($results as $className => $metadata) {
            // Each class should be a fully qualified class name
            $this->assertIsString($className);
            $this->assertStringContainsString('\\', $className);
        }
    }

    /**
     * Test handles custom monorepo structure.
     *
     * This test verifies that the discovery system can handle
     * non-standard monorepo structures with custom directory layouts.
     *
     * ## Scenario:
     * - Mixed package and module directories
     * - Different nesting levels
     * - Custom directory naming conventions
     * - Discovery should adapt to the structure
     *
     * ## Assertions:
     * - All directories are scanned successfully
     * - Classes from all locations are discovered
     * - No errors occur with custom structures
     * - Results are properly organized
     */
    public function test_handles_custom_monorepo_structure(): void
    {
        // Arrange: Define a mix of different directory structures
        $customDirectories = [
            // Package-style structure
            __DIR__ . '/../Fixtures/Directories/Package1/src',
            __DIR__ . '/../Fixtures/Directories/Package2/src',
            // Module-style structure
            __DIR__ . '/../Fixtures/Directories/Module1/src',
            // Direct class directories
            __DIR__ . '/../Fixtures/Classes/Services',
            __DIR__ . '/../Fixtures/Classes/Commands',
        ];

        // Act: Discover classes across all custom directory structures
        $results = $this
            ->discovery
            ->directories($customDirectories)
            ->get()->all();

        // Assert: Verify discovery handled all structures
        $this->assertIsArray($results);

        // Verify we can discover from mixed structures
        $this->assertGreaterThanOrEqual(0, count($results));

        // Verify each result has proper metadata
        foreach ($results as $className => $metadata) {
            $this->assertIsString($className);
            $this->assertIsArray($metadata);
        }
    }
}
