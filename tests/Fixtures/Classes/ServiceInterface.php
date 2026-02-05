<?php declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Fixtures\Classes;

/**
 * Service Interface Contract.
 *
 * A simple interface used for testing interface-based discovery
 * and validation. Classes implementing this interface can be
 * discovered using the `implementing()` validator.
 *
 * ## Usage:
 * ```php
 * class MyService implements ServiceInterface {
 *     public function execute(): mixed {
 *         return 'result';
 *     }
 * }
 * ```
 *
 * ## Test Scenarios:
 * - Interface implementation discovery
 * - Interface validation
 * - Polymorphic service discovery
 * - Contract-based filtering
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
interface ServiceInterface
{
    /**
     * Execute the service operation.
     *
     * This method should contain the main logic of the service.
     * The return type is mixed to allow flexibility in test scenarios.
     *
     * @return mixed The result of the service execution
     */
    public function execute(): mixed;
}
