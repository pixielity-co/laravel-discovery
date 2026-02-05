<?php declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Unit\Validators;

use Pixielity\Discovery\Tests\Fixtures\Classes\Cards\DashboardCard;
use Pixielity\Discovery\Tests\Fixtures\Classes\Services\AbstractService;
use Pixielity\Discovery\Tests\Fixtures\Classes\Services\TestService;
use Pixielity\Discovery\Tests\Fixtures\Classes\ServiceInterface;
use Pixielity\Discovery\Tests\TestCase;
use Pixielity\Discovery\Validators\ImplementsValidator;

/**
 * ImplementsValidator Unit Tests.
 *
 * Tests validation of interface implementation.
 * The ImplementsValidator checks if a class implements a specific interface,
 * including through parent classes.
 *
 * @covers \Pixielity\Discovery\Validators\ImplementsValidator
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
class ImplementsValidatorTest extends TestCase
{
    /**
     * Test validates classes implementing interface.
     *
     * This test verifies that the validator correctly identifies
     * classes that implement the specified interface.
     *
     * ## Scenario:
     * - Create validator for ServiceInterface
     * - Test with TestService (implements ServiceInterface)
     * - Verify validation passes
     *
     * ## Assertions:
     * - Validator returns true for implementing classes
     * - Both concrete and abstract implementations are detected
     */
    public function test_validates_classes_implementing_interface(): void
    {
        // Arrange: Create validator for ServiceInterface
        $validator = new ImplementsValidator(ServiceInterface::class);

        // Act: Validate TestService (implements ServiceInterface)
        $result1 = $validator->validate(TestService::class);

        // Act: Validate AbstractService (also implements ServiceInterface)
        $result2 = $validator->validate(AbstractService::class);

        // Assert: Both should pass validation
        $this->assertTrue($result1);
        $this->assertTrue($result2);
    }

    /**
     * Test rejects classes not implementing interface.
     *
     * This test verifies that the validator correctly rejects
     * classes that do not implement the specified interface.
     *
     * ## Scenario:
     * - Create validator for ServiceInterface
     * - Test with DashboardCard (does not implement ServiceInterface)
     * - Verify validation fails
     *
     * ## Assertions:
     * - Validator returns false for non-implementing classes
     */
    public function test_rejects_classes_not_implementing_interface(): void
    {
        // Arrange: Create validator for ServiceInterface
        $validator = new ImplementsValidator(ServiceInterface::class);

        // Act: Validate DashboardCard (does not implement ServiceInterface)
        $result = $validator->validate(DashboardCard::class);

        // Assert: Validation should fail
        $this->assertFalse($result);
    }

    /**
     * Test handles multiple interfaces.
     *
     * This test verifies that the validator works correctly
     * when a class implements multiple interfaces.
     *
     * ## Scenario:
     * - Create validator for ServiceInterface
     * - Test with class that implements multiple interfaces
     * - Verify validation passes for the target interface
     *
     * ## Assertions:
     * - Validator correctly identifies one of multiple interfaces
     * - Multiple interface implementation doesn't affect validation
     */
    public function test_handles_multiple_interfaces(): void
    {
        // Arrange: Create validator for ServiceInterface
        $validator = new ImplementsValidator(ServiceInterface::class);

        // Act: Validate TestService (may implement multiple interfaces)
        $result = $validator->validate(TestService::class);

        // Assert: Validation should pass
        $this->assertTrue($result);
    }

    /**
     * Test handles non-existent interface.
     *
     * This test verifies that the validator handles gracefully
     * when given a non-existent interface name.
     *
     * ## Scenario:
     * - Create validator for non-existent interface
     * - Test with any class
     * - Verify validation fails gracefully
     *
     * ## Assertions:
     * - Validator returns false for non-existent interfaces
     * - No exceptions are thrown
     */
    public function test_handles_non_existent_interface(): void
    {
        // Arrange: Create validator for non-existent interface
        $validator = new ImplementsValidator('App\NonExistent\Interface');

        // Act: Validate TestService
        $result = $validator->validate(TestService::class);

        // Assert: Validation should fail gracefully
        $this->assertFalse($result);
    }
}
