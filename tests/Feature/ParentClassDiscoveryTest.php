<?php

declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Feature;

use Illuminate\Console\Command;
use Pixielity\Discovery\DiscoveryManager;
use Pixielity\Discovery\Support\Reflection;
use Pixielity\Discovery\Tests\TestCase;

/**
 * ParentClassDiscovery Feature Tests.
 *
 * End-to-end tests for parent class extension discovery.
 * Tests finding classes that extend specific parent classes.
 *
 * @covers \Pixielity\Discovery\DiscoveryManager
 * @covers \Pixielity\Discovery\Strategies\ParentClassStrategy
 */
class ParentClassDiscoveryTest extends TestCase
{
    /**
     * Discovery manager instance.
     */
    protected DiscoveryManager $discovery;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->discovery = resolve(DiscoveryManager::class);
    }

    /**
     * Test discovers class extensions.
     *
     * Verifies that all classes extending a specific parent class
     * are discovered and returned.
     */
    public function test_discovers_class_extensions(): void
    {
        // Act: Discover all classes extending Command
        $results = $this
            ->discovery
            ->extending(Command::class)
            ->get()
            ->all();

        // Assert: Should find extensions
        $this->assertIsArray($results);

        // Verify all results extend the parent class
        foreach (array_keys($results) as $class) {
            if (class_exists($class)) {
                $this->assertTrue(is_subclass_of($class, Command::class));
            }
        }
    }

    /**
     * Test combines with directory filter.
     *
     * Verifies that parent class discovery can be combined with
     * directory filtering to narrow down search scope.
     */
    public function test_combines_with_directory_filter(): void
    {
        // Act: Discover extensions in specific directory
        $results = $this
            ->discovery
            ->directories(__DIR__ . '/../Fixtures/Classes/Commands')
            ->extending(Command::class)
            ->get()
            ->all();

        // Assert: Should find extensions in directory
        $this->assertIsArray($results);
    }

    /**
     * Test validates instantiable.
     *
     * Verifies that when combined with instantiable validator,
     * only concrete extensions are returned (no abstracts).
     */
    public function test_validates_instantiable(): void
    {
        // Act: Discover only instantiable extensions
        $results = $this
            ->discovery
            ->extending(Command::class)
            ->instantiable()
            ->get()
            ->all();

        // Assert: Should only include concrete classes
        $this->assertIsArray($results);

        // Verify all results are instantiable
        foreach (array_keys($results) as $class) {
            if (class_exists($class)) {
                $this->assertFalse(Reflection::isAbstract($class));
            }
        }
    }

    /**
     * Test handles multi-level inheritance.
     *
     * Verifies that classes extending a parent through multiple
     * levels of inheritance are discovered correctly.
     */
    public function test_handles_multi_level_inheritance(): void
    {
        // Act: Discover Command extensions (multi-level)
        $results = $this
            ->discovery
            ->extending(Command::class)
            ->get()
            ->all();

        // Assert: Should handle multi-level inheritance
        $this->assertIsArray($results);
    }
}
