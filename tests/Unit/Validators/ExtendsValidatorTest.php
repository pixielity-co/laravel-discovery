<?php

namespace Fulers\Discovery\Tests\Unit\Validators;

use Exception;
use Fulers\Discovery\Validators\ExtendsValidator;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

/**
 * ExtendsValidatorTest - Tests for ExtendsValidator class.
 *
 * @covers \Fulers\Discovery\Validators\ExtendsValidator
 */
class ExtendsValidatorTest extends TestCase
{
    /**
     * Test that validator can be instantiated.
     */
    public function test_can_instantiate(): void
    {
        $extendsValidator = new ExtendsValidator(Exception::class);

        $this->assertInstanceOf(ExtendsValidator::class, $extendsValidator);
    }

    /**
     * Test that validate returns true for subclass.
     */
    public function test_validate_returns_true_for_subclass(): void
    {
        $extendsValidator = new ExtendsValidator(Exception::class);
        $result = $extendsValidator->validate(RuntimeException::class);

        $this->assertTrue($result);
    }

    /**
     * Test that validate returns false for non-subclass.
     */
    public function test_validate_returns_false_for_non_subclass(): void
    {
        $extendsValidator = new ExtendsValidator(Exception::class);
        $result = $extendsValidator->validate(stdClass::class);

        $this->assertFalse($result);
    }

    /**
     * Test that validate returns false for parent class itself.
     */
    public function test_validate_returns_false_for_parent_class_itself(): void
    {
        $extendsValidator = new ExtendsValidator(Exception::class);
        $result = $extendsValidator->validate(Exception::class);

        $this->assertFalse($result);
    }

    /**
     * Test that validate returns false for non-existent class.
     */
    public function test_validate_returns_false_for_non_existent_class(): void
    {
        $extendsValidator = new ExtendsValidator(Exception::class);
        $result = $extendsValidator->validate('NonExistentClass');

        $this->assertFalse($result);
    }

    /**
     * Test that validate handles exceptions gracefully.
     */
    public function test_validate_handles_exceptions_gracefully(): void
    {
        $extendsValidator = new ExtendsValidator('NonExistentParent');
        $result = $extendsValidator->validate(stdClass::class);

        $this->assertFalse($result);
    }
}
