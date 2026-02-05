<?php declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Fixtures\Classes\Services;

use Pixielity\Discovery\Tests\Fixtures\Attributes\TestServiceAttribute;
use Pixielity\Discovery\Tests\Fixtures\Classes\ServiceInterface;

/**
 * Concrete Test Service.
 *
 * A test fixture representing a concrete service implementation
 * that can be instantiated and registered in the service container.
 * This class is used to test multiple discovery scenarios.
 *
 * ## Characteristics:
 * - Concrete class (can be instantiated)
 * - Implements ServiceInterface
 * - Marked with TestServiceAttribute
 * - Configured as singleton with alias
 *
 * ## Test Scenarios:
 * - Service discovery by attribute
 * - Interface implementation validation
 * - Instantiability validation (should pass)
 * - Singleton binding configuration
 * - Service alias extraction
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
#[TestServiceAttribute(singleton: true, alias: 'test.service')]
class TestService implements ServiceInterface
{
    /**
     * Execute the service operation.
     *
     * This method implements the ServiceInterface contract.
     * For testing purposes, it returns a simple string.
     *
     * @return string The result of the service execution
     */
    public function execute(): string
    {
        // Return a simple identifier for testing
        return 'executed';
    }
}
