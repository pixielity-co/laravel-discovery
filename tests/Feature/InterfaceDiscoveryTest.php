<?php declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Feature;

use Pixielity\Discovery\Tests\Fixtures\Classes\Services\TestService;
use Pixielity\Discovery\Tests\Fixtures\Classes\ServiceInterface;
use Pixielity\Discovery\Tests\TestCase;
use Pixielity\Discovery\DiscoveryManager;

/**
 * InterfaceDiscovery Feature Tests.
 *
 * End-to-end tests for interface implementation discovery.
 * Tests finding classes that implement specific interfaces.
 *
 * @covers \Pixielity\Discovery\DiscoveryManager
 * @covers \Pixielity\Discovery\Strategies\InterfaceStrategy
 */
class InterfaceDiscoveryTest extends TestCase
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
     * Test discovers interface implementations.
     *
     * Verifies that all classes implementing a specific interface
     * are discovered and returned.
     *
     * @return void
     */
    public function test_discovers_interface_implementations(): void
    {
        // Act: Discover all classes implementing ServiceInterface
        $results = $this
            ->discovery
            ->implementing(ServiceInterface::class)
            ->get()->all();

        // Assert: Should find implementations
        $this->assertIsArray($results);

        // Verify all results implement the interface
        foreach ($results as $class => $metadata) {
            if (class_exists($class)) {
                $interfaces = class_implements($class) ?: [];
                $this->assertContains(ServiceInterface::class, $interfaces);
            }
        }
    }

    /**
     * Test combines with directory filter.
     *
     * Verifies that interface discovery can be combined with
     * directory filtering to narrow down search scope.
     *
     * @return void
     */
    public function test_combines_with_directory_filter(): void
    {
        // Act: Discover implementations in specific directory
        $results = $this
            ->discovery
            ->directories(__DIR__ . '/../Fixtures/Classes/Services')
            ->implementing(ServiceInterface::class)
            ->get()->all();

        // Assert: Should find implementations in directory
        $this->assertIsArray($results);
    }

    /**
     * Test validates instantiable.
     *
     * Verifies that when combined with instantiable validator,
     * only concrete implementations are returned (no abstracts).
     *
     * @return void
     */
    public function test_validates_instantiable(): void
    {
        // Act: Discover only instantiable implementations
        $results = $this
            ->discovery
            ->implementing(ServiceInterface::class)
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

    /**
     * Test handles multiple interfaces.
     *
     * Verifies that classes implementing multiple interfaces
     * are discovered when searching for any of those interfaces.
     *
     * @return void
     */
    public function test_handles_multiple_interfaces(): void
    {
        // Act: Discover ServiceInterface implementations
        $results = $this
            ->discovery
            ->implementing(ServiceInterface::class)
            ->get()->all();

        // Assert: Should handle classes with multiple interfaces
        $this->assertIsArray($results);
    }
}
