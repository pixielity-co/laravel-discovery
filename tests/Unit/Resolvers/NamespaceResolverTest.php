<?php declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Unit\Resolvers;

use Pixielity\Discovery\Resolvers\NamespaceResolver;
use Pixielity\Discovery\Tests\TestCase;
use Symfony\Component\Finder\SplFileInfo;

/**
 * NamespaceResolver Unit Tests.
 *
 * Tests namespace resolution from file paths for monorepo structures.
 * The NamespaceResolver converts file system paths to fully qualified
 * PHP class names, supporting various monorepo patterns.
 *
 * ## Key Features Tested:
 * - Package namespace resolution
 * - Module namespace resolution
 * - App directory resolution
 * - Custom pattern support
 * - Nested namespace handling
 * - Error handling
 *
 * @covers \Pixielity\Discovery\Resolvers\NamespaceResolver
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
class NamespaceResolverTest extends TestCase
{
    /**
     * The namespace resolver instance being tested.
     *
     * @var NamespaceResolver
     */
    protected NamespaceResolver $resolver;

    /**
     * Setup the test environment.
     *
     * Initializes the namespace resolver before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create the namespace resolver instance
        $this->resolver = new NamespaceResolver();
    }

    /**
     * Test resolves namespace from file path.
     *
     * This test verifies that the resolver can extract a fully
     * qualified class name from a file path.
     *
     * ## Scenario:
     * - Provide a file path with standard structure
     * - Resolve the namespace
     * - Verify correct class name is returned
     *
     * ## Assertions:
     * - Namespace is correctly resolved
     * - Class name matches expected format
     */
    public function test_resolves_namespace_from_file_path(): void
    {
        // Arrange: Create a mock file path for a test class
        $filePath = __DIR__ . '/../../Fixtures/Classes/Cards/DashboardCard.php';

        // Arrange: Create SplFileInfo instance
        $file = new SplFileInfo($filePath, '', '');

        // Act: Resolve the namespace from the file
        $className = $this->resolver->resolveFromFile($file);

        // Assert: Should resolve to the correct class name
        $this->assertNotNull($className);
        $this->assertStringContainsString('Pixielity', $className);
        $this->assertStringContainsString('DashboardCard', $className);
    }

    /**
     * Test handles monorepo packages.
     *
     * This test verifies that the resolver correctly handles
     * the packages directory structure in a monorepo.
     *
     * ## Scenario:
     * - Provide a file path in packages directory
     * - Resolve the namespace
     * - Verify package name is included in namespace
     *
     * ## Assertions:
     * - Package name is extracted
     * - Namespace follows Pixielity\{Package}\{Class} pattern
     */
    public function test_handles_monorepo_packages(): void
    {
        // Arrange: Create a mock file path in packages directory
        // Pattern: packages/{Package}/src/{Namespace}/{Class}.php
        $filePath = __DIR__ . '/../../Fixtures/Classes/Services/TestService.php';

        // Arrange: Create SplFileInfo instance
        $file = new SplFileInfo($filePath, '', '');

        // Act: Resolve the namespace
        $className = $this->resolver->resolveFromFile($file);

        // Assert: Should resolve with package structure
        $this->assertNotNull($className);
        $this->assertStringContainsString('Discovery', $className);
        $this->assertStringContainsString('TestService', $className);
    }

    /**
     * Test handles monorepo modules.
     *
     * This test verifies that the resolver correctly handles
     * the modules directory structure in a monorepo.
     *
     * ## Scenario:
     * - Provide a file path in modules directory
     * - Resolve the namespace
     * - Verify module name is included in namespace
     *
     * ## Assertions:
     * - Module name is extracted
     * - Namespace follows Modules\{Module}\{Class} pattern
     */
    public function test_handles_monorepo_modules(): void
    {
        // Arrange: Create a temporary file to simulate modules structure
        $tempDir = sys_get_temp_dir() . '/test_modules';
        $moduleDir = $tempDir . '/modules/TestModule/src';

        // Arrange: Create directory structure
        if (!is_dir($moduleDir)) {
            mkdir($moduleDir, 0777, true);
        }

        // Arrange: Create a test file
        $testFile = $moduleDir . '/TestClass.php';
        file_put_contents($testFile, '<?php namespace Modules\TestModule; class TestClass {}');

        // Arrange: Create SplFileInfo instance
        $file = new SplFileInfo($testFile, '', '');

        // Act: Resolve the namespace
        $className = $this->resolver->resolveFromFile($file);

        // Assert: Should resolve with module structure
        $this->assertNotNull($className);
        $this->assertStringContainsString('Modules', $className);
        $this->assertStringContainsString('TestModule', $className);
        $this->assertStringContainsString('TestClass', $className);

        // Cleanup: Remove temporary files
        unlink($testFile);
        rmdir($moduleDir);
        rmdir(dirname($moduleDir));
        rmdir($tempDir);
    }

    /**
     * Test handles app directory.
     *
     * This test verifies that the resolver correctly handles
     * the app directory structure (Laravel standard).
     *
     * ## Scenario:
     * - Provide a file path in app directory
     * - Resolve the namespace
     * - Verify App namespace is used
     *
     * ## Assertions:
     * - App namespace is used
     * - Subdirectories are converted to namespace segments
     */
    public function test_handles_app_directory(): void
    {
        // Arrange: Create a temporary file to simulate app structure
        $tempDir = sys_get_temp_dir() . '/test_app';
        $appDir = $tempDir . '/app/Http/Controllers';

        // Arrange: Create directory structure
        if (!is_dir($appDir)) {
            mkdir($appDir, 0777, true);
        }

        // Arrange: Create a test file
        $testFile = $appDir . '/UserController.php';
        file_put_contents($testFile, '<?php namespace App\Http\Controllers; class UserController {}');

        // Arrange: Create SplFileInfo instance
        $file = new SplFileInfo($testFile, '', '');

        // Act: Resolve the namespace
        $className = $this->resolver->resolveFromFile($file);

        // Assert: Should resolve with App namespace
        $this->assertNotNull($className);
        $this->assertStringStartsWith('App\\', $className);
        $this->assertStringContainsString('Http', $className);
        $this->assertStringContainsString('Controllers', $className);
        $this->assertStringContainsString('UserController', $className);

        // Cleanup: Remove temporary files
        unlink($testFile);
        rmdir($appDir);
        rmdir(dirname($appDir));
        rmdir(dirname(dirname($appDir)));
        rmdir($tempDir);
    }

    /**
     * Test handles custom namespace patterns.
     *
     * This test verifies that the resolver can use custom
     * namespace patterns with placeholders.
     *
     * ## Scenario:
     * - Provide a custom namespace pattern
     * - Resolve namespace using the pattern
     * - Verify pattern placeholders are replaced
     *
     * ## Assertions:
     * - Custom pattern is applied
     * - Placeholders are replaced correctly
     * - Resulting namespace is valid
     */
    public function test_handles_custom_namespace_patterns(): void
    {
        // Arrange: Create a temporary file in packages structure
        $tempDir = sys_get_temp_dir() . '/test_custom';
        $packageDir = $tempDir . '/packages/MyPackage/src/Services';

        // Arrange: Create directory structure
        if (!is_dir($packageDir)) {
            mkdir($packageDir, 0777, true);
        }

        // Arrange: Create a test file
        $testFile = $packageDir . '/MyService.php';
        file_put_contents($testFile, '<?php namespace Custom\MyPackage\Services; class MyService {}');

        // Arrange: Create SplFileInfo instance
        $file = new SplFileInfo($testFile, '', '');

        // Arrange: Define custom pattern
        $customPattern = 'Custom\{package}\{namespace}\{class}';

        // Act: Resolve namespace with custom pattern
        $className = $this->resolver->resolveFromFile($file, $customPattern);

        // Assert: Should use custom pattern
        $this->assertNotNull($className);
        $this->assertStringStartsWith('Custom\\', $className);
        $this->assertStringContainsString('MyPackage', $className);
        $this->assertStringContainsString('Services', $className);
        $this->assertStringContainsString('MyService', $className);

        // Cleanup: Remove temporary files
        unlink($testFile);
        rmdir($packageDir);
        rmdir(dirname($packageDir));
        rmdir(dirname(dirname($packageDir)));
        rmdir(dirname(dirname(dirname($packageDir))));
        rmdir($tempDir);
    }

    /**
     * Test handles invalid file paths.
     *
     * This test verifies that the resolver handles gracefully
     * when given invalid or non-standard file paths.
     *
     * ## Scenario:
     * - Provide a file path that doesn't match any pattern
     * - Attempt to resolve namespace
     * - Verify null is returned
     *
     * ## Assertions:
     * - Returns null for invalid paths
     * - No exceptions are thrown
     */
    public function test_handles_invalid_file_paths(): void
    {
        // Arrange: Create a temporary file in non-standard location
        $tempDir = sys_get_temp_dir() . '/test_invalid';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        // Arrange: Create a test file in non-standard location
        $testFile = $tempDir . '/RandomClass.php';
        file_put_contents($testFile, '<?php class RandomClass {}');

        // Arrange: Create SplFileInfo instance
        $file = new SplFileInfo($testFile, '', '');

        // Act: Attempt to resolve namespace
        $className = $this->resolver->resolveFromFile($file);

        // Assert: Should return null for non-standard paths
        $this->assertNull($className);

        // Cleanup: Remove temporary files
        unlink($testFile);
        rmdir($tempDir);
    }
}
