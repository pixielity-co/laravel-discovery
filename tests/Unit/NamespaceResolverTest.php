<?php

namespace Fulers\Discovery\Tests\Unit;

use Exception;
use Fulers\Discovery\Resolvers\NamespaceResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;

/**
 * NamespaceResolverTest - Tests for NamespaceResolver class.
 *
 * @covers \Fulers\Discovery\Resolvers\NamespaceResolver
 */
class NamespaceResolverTest extends TestCase
{
    private NamespaceResolver $namespaceResolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->namespaceResolver = new NamespaceResolver();
    }

    /**
     * Test that resolver can be instantiated.
     */
    public function test_can_instantiate(): void
    {
        $this->assertInstanceOf(NamespaceResolver::class, $this->namespaceResolver);
    }

    /**
     * Test that resolveFromFile returns null for invalid file.
     */
    public function test_resolve_from_file_returns_null_for_invalid_file(): void
    {
        $file = $this->createMock(SplFileInfo::class);
        $file->method('getRealPath')->willReturn('/invalid/path.php');

        $result = $this->namespaceResolver->resolveFromFile($file);

        $this->assertNull($result);
    }

    /**
     * Test that resolveFromFile handles package structure.
     */
    public function test_resolve_from_file_handles_package_structure(): void
    {
        $file = $this->createMock(SplFileInfo::class);
        $file->method('getRealPath')->willReturn('/base/packages/Auth/src/Models/User.php');

        $result = $this->namespaceResolver->resolveFromFile($file);

        $this->assertIsString($result);
        $this->assertEquals('Fulers\Auth\Models\User', $result);
    }

    /**
     * Test that resolveFromFile handles module structure.
     */
    public function test_resolve_from_file_handles_module_structure(): void
    {
        $file = $this->createMock(SplFileInfo::class);
        $file->method('getRealPath')->willReturn('/base/modules/Blog/src/Controllers/PostController.php');

        $result = $this->namespaceResolver->resolveFromFile($file);

        $this->assertIsString($result);
        $this->assertEquals('Modules\Blog\Controllers\PostController', $result);
    }

    /**
     * Test that resolveFromFile handles app structure.
     */
    public function test_resolve_from_file_handles_app_structure(): void
    {
        $file = $this->createMock(SplFileInfo::class);
        $file->method('getRealPath')->willReturn('/base/app/Http/Controllers/HomeController.php');

        $result = $this->namespaceResolver->resolveFromFile($file);

        $this->assertIsString($result);
        $this->assertEquals('App\Http\Controllers\HomeController', $result);
    }

    /**
     * Test that resolveFromFile uses custom pattern.
     */
    public function test_resolve_from_file_uses_custom_pattern(): void
    {
        $file = $this->createMock(SplFileInfo::class);
        $file->method('getRealPath')->willReturn('/base/packages/Auth/src/Models/User.php');

        $pattern = 'Custom\{package}\{namespace}\{class}';
        $result = $this->namespaceResolver->resolveFromFile($file, $pattern);

        $this->assertIsString($result);
        $this->assertEquals('Custom\Auth\Models\User', $result);
    }

    /**
     * Test that resolveFromFile handles exceptions gracefully.
     */
    public function test_resolve_from_file_handles_exceptions_gracefully(): void
    {
        $file = $this->createMock(SplFileInfo::class);
        $file->method('getRelativePathname')->willThrowException(new Exception('Test exception'));

        $result = $this->namespaceResolver->resolveFromFile($file);

        $this->assertNull($result);
    }
}
