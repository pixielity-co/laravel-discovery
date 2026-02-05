<?php declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Fixtures\Classes\Services;

use Pixielity\Discovery\Tests\Fixtures\Classes\ServiceInterface;

/**
 * Abstract Service Base Class.
 *
 * A test fixture representing an abstract service that implements
 * the ServiceInterface. This class is used to test that abstract
 * classes are properly excluded when using the `instantiable()` validator.
 *
 * ## Characteristics:
 * - Abstract class (cannot be instantiated)
 * - Implements ServiceInterface
 * - Has abstract execute() method
 *
 * ## Test Scenarios:
 * - Abstract class detection
 * - Instantiability validation (should fail)
 * - Interface implementation (should pass)
 * - Filtering out non-instantiable classes
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
abstract class AbstractService implements ServiceInterface
{
    /**
     * Execute the service operation.
     *
     * This abstract method must be implemented by concrete subclasses.
     * It defines the contract for service execution.
     *
     * @return mixed The result of the service execution
     */
    abstract public function execute(): mixed;
}
