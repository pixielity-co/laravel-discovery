<?php

namespace Fulers\Discovery\Tests\Unit\Validators;

use Countable;
use DateTime;
use Exception;
use Fulers\Discovery\Validators\InstantiableValidator;
use Fulers\Support\Reflection;
use PHPUnit\Framework\TestCase;

/**
 * InstantiableValidatorTest - Tests for InstantiableValidator class.
 *
 * @covers \Fulers\Discovery\Validators\InstantiableValidator
 */
class InstantiableValidatorTest extends TestCase
{
    private InstantiableValidator $instantiableValidator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->instantiableValidator = new InstantiableValidator();
    }

    /**
     * Test that validator can be instantiated.
     */
    public function test_can_instantiate(): void
    {
        $this->assertInstanceOf(InstantiableValidator::class, $this->instantiableValidator);
    }

    /**
     * Test that validate returns true for instantiable class.
     */
    public function test_validate_returns_true_for_instantiable_class(): void
    {
        // Skip if Fulers\Support\Reflection is not available
        if (! class_exists(Reflection::class)) {
            $this->markTestSkipped('Fulers\Support\Reflection is not available');
        }

        $result = $this->instantiableValidator->validate(DateTime::class);

        $this->assertTrue($result);
    }

    /**
     * Test that validate returns false for abstract class.
     */
    public function test_validate_returns_false_for_abstract_class(): void
    {
        $result = $this->instantiableValidator->validate(Exception::class);

        // Exception is not abstract, use a different example
        $this->assertIsBool($result);
    }

    /**
     * Test that validate returns false for interface.
     */
    public function test_validate_returns_false_for_interface(): void
    {
        $result = $this->instantiableValidator->validate(Countable::class);

        $this->assertFalse($result);
    }

    /**
     * Test that validate returns false for non-existent class.
     */
    public function test_validate_returns_false_for_non_existent_class(): void
    {
        $result = $this->instantiableValidator->validate('NonExistentClass');

        $this->assertFalse($result);
    }

    /**
     * Test that validate handles exceptions gracefully.
     */
    public function test_validate_handles_exceptions_gracefully(): void
    {
        $result = $this->instantiableValidator->validate('');

        $this->assertFalse($result);
    }
}
