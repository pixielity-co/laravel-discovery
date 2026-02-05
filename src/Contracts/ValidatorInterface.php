<?php

namespace Pixielity\Discovery\Contracts;

/**
 * Validator Interface.
 *
 * Defines the contract for validating discovered classes.
 */
interface ValidatorInterface
{
    /**
     * Validate a class.
     *
     * @param  string $class Fully qualified class name
     * @return bool   True if valid, false otherwise
     */
    public function validate(string $class): bool;
}
