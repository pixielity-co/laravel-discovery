<?php

namespace Fulers\Discovery\Validators;

use Fulers\Discovery\Contracts\ValidatorInterface;
use Fulers\Support\Reflection;
use Throwable;

/**
 * InstantiableValidator - Validates that a class can be instantiated.
 *
 * Checks if a class is concrete (not abstract or interface) and can be instantiated.
 * Uses the Framework's Reflection utility for consistent reflection operations.
 */
class InstantiableValidator implements ValidatorInterface
{
    /**
     * Validate that a class is instantiable.
     *
     * Uses the Framework's Reflection utility to check if a class can be instantiated.
     * This ensures consistent reflection behavior across the application.
     *
     * @param  string $class Fully qualified class name
     * @return bool   True if instantiable, false otherwise
     */
    public function validate(string $class): bool
    {
        try {
            return Reflection::isInstantiable($class);
        } catch (Throwable) {
            return false;
        }
    }
}
