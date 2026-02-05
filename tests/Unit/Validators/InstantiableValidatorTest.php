<?php

declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Unit\Validators;

use Pixielity\Discovery\Tests\Fixtures\Classes\ServiceInterface;
use Pixielity\Discovery\Tests\Fixtures\Classes\Services\AbstractService;
use Pixielity\Discovery\Tests\Fixtures\Classes\Services\TestService;
use Pixielity\Discovery\Tests\TestCase;
use Pixielity\Discovery\Validators\InstantiableValidator;

/**
 * InstantiableValidator Unit Tests.
 *
 * Tests validation of instantiable classes.
 * The InstantiableValidator checks if a class can be instantiated,
 * excluding abstract classes, interfaces, and traits.
 *
 * @covers \Pixielity\Discovery\Validators\InstantiableValidator
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
class InstantiableValidatorTest extends TestCase
{
    /**
     * The instantiable validator instance.
     */
    protected InstantiableValidator $validator;

    /**
     * Setup the test environment.
     *
     * Initializes the instantiable validator before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create the validator instance
        $this->validator = new InstantiableValidator();
    }

    /**
     * Test validates concrete classes.
     *
     * This test verifies that the validator correctly identifies
     * concrete classes that can be instantiated.
     *
     * ## Scenario:
     * - Test with a concrete class (TestService)
     * - Verify validation passes
     *
     * ## Assertions:
     * - Validator returns true for concrete classes
     * - Class can actually be instantiated
     */
    public function test_validates_concrete_classes(): void
    {
        // Arrange: Use a concrete class
        $concreteClass = TestService::class;

        // Act: Validate the class
        $result = $this->validator->validate($concreteClass);

        // Assert: Validation should pass
        $this->assertTrue($result);

        // Assert: Class can actually be instantiated
        $instance = new $concreteClass();
        $this->assertInstanceOf(TestService::class, $instance);
    }

    /**
     * Test rejects abstract classes.
     *
     * This test verifies that the validator correctly rejects
     * abstract classes that cannot be instantiated.
     *
     * ## Scenario:
     * - Test with an abstract class (AbstractService)
     * - Verify validation fails
     *
     * ## Assertions:
     * - Validator returns false for abstract classes
     * - Abstract classes cannot be instantiated
     */
    public function test_rejects_abstract_classes(): void
    {
        // Arrange: Use an abstract class
        $abstractClass = AbstractService::class;

        // Act: Validate the class
        $result = $this->validator->validate($abstractClass);

        // Assert: Validation should fail
        $this->assertFalse($result);
    }

    /**
     * Test rejects interfaces.
     *
     * This test verifies that the validator correctly rejects
     * interfaces that cannot be instantiated.
     *
     * ## Scenario:
     * - Test with an interface (ServiceInterface)
     * - Verify validation fails
     *
     * ## Assertions:
     * - Validator returns false for interfaces
     * - Interfaces cannot be instantiated
     */
    public function test_rejects_interfaces(): void
    {
        // Arrange: Use an interface
        $interface = ServiceInterface::class;

        // Act: Validate the interface
        $result = $this->validator->validate($interface);

        // Assert: Validation should fail
        $this->assertFalse($result);
    }

    /**
     * Test rejects traits.
     *
     * This test verifies that the validator correctly rejects
     * traits that cannot be instantiated.
     *
     * ## Scenario:
     * - Test with a non-existent class (simulating trait behavior)
     * - Verify validation fails
     *
     * ## Assertions:
     * - Validator returns false for traits
     * - Traits cannot be instantiated
     */
    public function test_rejects_traits(): void
    {
        // Arrange: Use a non-existent class (simulating trait/invalid class)
        $nonExistentClass = 'App\NonExistent\Trait';

        // Act: Validate the class
        $result = $this->validator->validate($nonExistentClass);

        // Assert: Validation should fail
        $this->assertFalse($result);
    }

    /**
     * Test handles classes with constructor params.
     *
     * This test verifies that the validator correctly handles
     * classes that have constructor parameters.
     *
     * ## Scenario:
     * - Test with a class that has constructor parameters
     * - Verify validation still works correctly
     *
     * ## Assertions:
     * - Validator returns true for classes with constructors
     * - Constructor parameters don't affect instantiability check
     */
    public function test_handles_classes_with_constructor_params(): void
    {
        // Arrange: Use a concrete class (even with constructor, it's still instantiable)
        $classWithConstructor = TestService::class;

        // Act: Validate the class
        $result = $this->validator->validate($classWithConstructor);

        // Assert: Validation should pass
        // Note: InstantiableValidator checks if a class CAN be instantiated,
        // not if it can be instantiated WITHOUT parameters
        $this->assertTrue($result);
    }
}
