<?php

namespace Pixielity\Discovery\Validators;

use Pixielity\Discovery\Contracts\ValidatorInterface;
use Throwable;

/**
 * ImplementsValidator - Validates that a class implements an interface.
 *
 * Checks if a class implements the specified interface.
 */
class ImplementsValidator implements ValidatorInterface
{
    /**
     * Create a new ImplementsValidator instance.
     *
     * @param string $interface Fully qualified interface name
     */
    public function __construct(
        protected string $interface
    ) {}

    /**
     * Validate that a class implements the interface.
     *
     * @param  string $class Fully qualified class name
     * @return bool   True if implements interface, false otherwise
     */
    public function validate(string $class): bool
    {
        try {
            return Reflection::isSubclassOf($class, $this->interface);
        } catch (Throwable) {
            return false;
        }
    }
}
