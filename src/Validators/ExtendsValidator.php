<?php

namespace Pixielity\Discovery\Validators;

use Pixielity\Discovery\Contracts\ValidatorInterface;
use Pixielity\Discovery\Support\Reflection;
use Throwable;

/**
 * ExtendsValidator - Validates that a class extends a parent class.
 *
 * Checks if a class is a subclass of the specified parent class.
 */
class ExtendsValidator implements ValidatorInterface
{
    /**
     * Create a new ExtendsValidator instance.
     *
     * @param string $parentClass Fully qualified parent class name
     */
    public function __construct(
        protected string $parentClass
    ) {}

    /**
     * Validate that a class extends the parent class.
     *
     * @param  string $class Fully qualified class name
     * @return bool   True if extends parent, false otherwise
     */
    public function validate(string $class): bool
    {
        try {
            return Reflection::isSubclassOf($class, $this->parentClass);
        } catch (Throwable) {
            return false;
        }
    }
}
