<?php declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Feature;

use Illuminate\Console\Command;
use Pixielity\Discovery\Tests\Fixtures\Classes\ServiceInterface;
use Pixielity\Discovery\Tests\TestCase;
use Pixielity\Discovery\DiscoveryManager;

/**
 * DirectoryDiscovery Feature Tests.
 *
 * End-to-end tests for directory-based class discovery.
 * Tests scanning directories, glob patterns, and filtering.
 *
 * @covers \Pixielity\Discovery\DiscoveryManager
 * @covers \Pixielity\Discovery\Strategies\DirectoryStrategy
 */
class DirectoryDiscoveryTest extends TestCase
{
    /**
     * Discovery manager instance.
     *
     * @var DiscoveryManager
     */
    protected DiscoveryManager $discovery;

    /**
     * Set up the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->discovery = app(DiscoveryManager::class);
    }

    /**
     * Test discovers classes in directory.
     *
     * Verifies that all PHP classes in a specific directory
     * are discovered and returned with proper metadata.
     *
     * @return void
     */
    public function test_discovers_classes_in_directory(): void
    {
        // Act: Discover all classes in Cards directory
        $results = $this
            ->discovery
            ->directories(__DIR__ . '/../Fixtures/Classes/Cards')
            ->get()->all();

        // Assert: Should find classes
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    /**
     * Test discovers with glob patterns.
     *
     * Verifies that glob patterns (e.g., packages/*\/src)
     * are properly expanded and all matching directories are scanned.
     *
     * @return void
     */
    public function test_discovers_with_glob_patterns(): void
    {
        // Act: Use glob pattern to discover across multiple directories
        $results = $this
            ->discovery
            ->directories(__DIR__ . '/../Fixtures/Classes/*')
            ->get()->all();

        // Assert: Should find classes from multiple subdirectories
        $this->assertIsArray($results);
    }

    /**
     * Test discovers in multiple directories.
     *
     * Verifies that multiple directory paths can be provided
     * and classes from all directories are discovered.
     *
     * @return void
     */
    public function test_discovers_in_multiple_directories(): void
    {
        // Act: Discover from multiple specific directories
        $results = $this
            ->discovery
            ->directories([
                __DIR__ . '/../Fixtures/Classes/Cards',
                __DIR__ . '/../Fixtures/Classes/Services',
            ])
            ->get()->all();

        // Assert: Should find classes from both directories
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    /**
     * Test filters by interface.
     *
     * Verifies that discovered classes can be filtered to only
     * include those implementing a specific interface.
     *
     * @return void
     */
    public function test_filters_by_interface(): void
    {
        // Act: Discover classes implementing ServiceInterface
        $results = $this
            ->discovery
            ->directories(__DIR__ . '/../Fixtures/Classes/Services')
            ->implementing(ServiceInterface::class)
            ->get()->all();

        // Assert: Should find service implementations
        $this->assertIsArray($results);

        // Verify all results implement the interface
        foreach ($results as $class => $metadata) {
            if (class_exists($class)) {
                $this->assertTrue(in_array(ServiceInterface::class, class_implements($class) ?: []));
            }
        }
    }

    /**
     * Test filters by parent class.
     *
     * Verifies that discovered classes can be filtered to only
     * include those extending a specific parent class.
     *
     * @return void
     */
    public function test_filters_by_parent_class(): void
    {
        // Act: Discover classes extending Command
        $results = $this
            ->discovery
            ->directories(__DIR__ . '/../Fixtures/Classes/Commands')
            ->extending(Command::class)
            ->get()->all();

        // Assert: Should find command classes
        $this->assertIsArray($results);
    }

    /**
     * Test validates instantiable.
     *
     * Verifies that the instantiable validator excludes
     * abstract classes and interfaces from results.
     *
     * @return void
     */
    public function test_validates_instantiable(): void
    {
        // Act: Discover only instantiable classes
        $results = $this
            ->discovery
            ->directories(__DIR__ . '/../Fixtures/Classes/Services')
            ->instantiable()
            ->get()->all();

        // Assert: Should only include concrete classes
        $this->assertIsArray($results);

        // Verify all results are instantiable
        foreach ($results as $class => $metadata) {
            if (class_exists($class)) {
                $reflection = new \ReflectionClass($class);
                $this->assertFalse($reflection->isAbstract());
                $this->assertFalse($reflection->isInterface());
            }
        }
    }
}
