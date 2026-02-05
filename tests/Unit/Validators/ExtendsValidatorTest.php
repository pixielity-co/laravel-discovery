<?php

declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Unit\Validators;

use Illuminate\Console\Command;
use Pixielity\Discovery\Tests\Fixtures\Classes\Cards\DashboardCard;
use Pixielity\Discovery\Tests\Fixtures\Classes\Commands\TestCommand;
use Pixielity\Discovery\Tests\Fixtures\Classes\Services\AbstractService;
use Pixielity\Discovery\Tests\Fixtures\Classes\Services\TestService;
use Pixielity\Discovery\Tests\TestCase;
use Pixielity\Discovery\Validators\ExtendsValidator;

/**
 * ExtendsValidator Unit Tests.
 *
 * Tests validation of parent class extension.
 * The ExtendsValidator checks if a class extends a specific parent class,
 * including through multi-level inheritance.
 *
 * @covers \Pixielity\Discovery\Validators\ExtendsValidator
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
class ExtendsValidatorTest extends TestCase
{
    /**
     * Test validates classes extending parent.
     *
     * This test verifies that the validator correctly identifies
     * classes that extend the specified parent class.
     *
     * ## Scenario:
     * - Create validator for Command class
     * - Test with TestCommand (extends Command)
     * - Verify validation passes
     *
     * ## Assertions:
     * - Validator returns true for extending classes
     * - Direct inheritance is detected
     */
    public function test_validates_classes_extending_parent(): void
    {
        // Arrange: Create validator for Command class
        $extendsValidator = new ExtendsValidator(Command::class);

        // Act: Validate TestCommand (extends Command)
        $result = $extendsValidator->validate(TestCommand::class);

        // Assert: Validation should pass
        $this->assertTrue($result);
    }

    /**
     * Test rejects classes not extending parent.
     *
     * This test verifies that the validator correctly rejects
     * classes that do not extend the specified parent class.
     *
     * ## Scenario:
     * - Create validator for Command class
     * - Test with DashboardCard (does not extend Command)
     * - Verify validation fails
     *
     * ## Assertions:
     * - Validator returns false for non-extending classes
     */
    public function test_rejects_classes_not_extending_parent(): void
    {
        // Arrange: Create validator for Command class
        $extendsValidator = new ExtendsValidator(Command::class);

        // Act: Validate DashboardCard (does not extend Command)
        $result = $extendsValidator->validate(DashboardCard::class);

        // Assert: Validation should fail
        $this->assertFalse($result);
    }

    /**
     * Test handles multi-level inheritance.
     *
     * This test verifies that the validator correctly handles
     * multi-level inheritance chains.
     *
     * ## Scenario:
     * - Create validator for AbstractService
     * - Test with TestService (extends AbstractService indirectly)
     * - Verify validation works through inheritance chain
     *
     * ## Assertions:
     * - Validator detects indirect inheritance
     * - Multi-level inheritance is properly handled
     */
    public function test_handles_multi_level_inheritance(): void
    {
        // Arrange: Create validator for AbstractService
        $validator = new ExtendsValidator(AbstractService::class);

        // Act: Validate TestService (does not extend AbstractService directly)
        // Note: TestService implements ServiceInterface but doesn't extend AbstractService
        $result = $validator->validate(TestService::class);

        // Assert: Validation should fail (TestService doesn't extend AbstractService)
        $this->assertFalse($result);

        // Arrange: Test with Command hierarchy
        $commandValidator = new ExtendsValidator(Command::class);

        // Act: Validate TestCommand (extends Command)
        $commandResult = $commandValidator->validate(TestCommand::class);

        // Assert: Validation should pass
        $this->assertTrue($commandResult);
    }

    /**
     * Test handles non-existent parent class.
     *
     * This test verifies that the validator handles gracefully
     * when given a non-existent parent class name.
     *
     * ## Scenario:
     * - Create validator for non-existent parent class
     * - Test with any class
     * - Verify validation fails gracefully
     *
     * ## Assertions:
     * - Validator returns false for non-existent parent classes
     * - No exceptions are thrown
     */
    public function test_handles_non_existent_parent_class(): void
    {
        // Arrange: Create validator for non-existent parent class
        $extendsValidator = new ExtendsValidator('App\NonExistent\ParentClass');

        // Act: Validate TestCommand
        $result = $extendsValidator->validate(TestCommand::class);

        // Assert: Validation should fail gracefully
        $this->assertFalse($result);
    }
}
