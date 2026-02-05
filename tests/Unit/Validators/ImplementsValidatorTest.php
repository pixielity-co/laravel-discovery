<?php

namespace Fulers\Discovery\Tests\Unit\Validators;

use ArrayObject;
use Countable;
use Fulers\Discovery\Validators\ImplementsValidator;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * ImplementsValidatorTest - Tests for ImplementsValidator class.
 *
 * @covers \Fulers\Discovery\Validators\ImplementsValidator
 */
class ImplementsValidatorTest extends TestCase
{
    /**
     * Test that validator can be instantiated.
     */
    public function test_can_instantiate(): void
    {
        $implementsValidator = new ImplementsValidator(Countable::class);

        $this->assertInstanceOf(ImplementsValidator::class, $implementsValidator);
    }

    /**
     * Test that validate returns true for implementing class.
     */
    public function test_validate_returns_true_for_implementing_class(): void
    {
        $implementsValidator = new ImplementsValidator(Countable::class);
        $result = $implementsValidator->validate(ArrayObject::class);

        $this->assertTrue($result);
    }

    /**
     * Test that validate returns false for non-implementing class.
     */
    public function test_validate_returns_false_for_non_implementing_class(): void
    {
        $implementsValidator = new ImplementsValidator(Countable::class);
        $result = $implementsValidator->validate(stdClass::class);

        $this->assertFalse($result);
    }

    /**
     * Test that validate returns false for interface itself.
     */
    public function test_validate_returns_false_for_interface_itself(): void
    {
        $implementsValidator = new ImplementsValidator(Countable::class);
        $result = $implementsValidator->validate(Countable::class);

        $this->assertFalse($result);
    }

    /**
     * Test that validate returns false for non-existent class.
     */
    public function test_validate_returns_false_for_non_existent_class(): void
    {
        $implementsValidator = new ImplementsValidator(Countable::class);
        $result = $implementsValidator->validate('NonExistentClass');

        $this->assertFalse($result);
    }

    /**
     * Test that validate handles exceptions gracefully.
     */
    public function test_validate_handles_exceptions_gracefully(): void
    {
        $implementsValidator = new ImplementsValidator('NonExistentInterface');
        $result = $implementsValidator->validate(stdClass::class);

        $this->assertFalse($result);
    }
}
