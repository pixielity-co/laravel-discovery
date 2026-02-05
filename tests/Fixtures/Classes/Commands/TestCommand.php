<?php

declare(strict_types=1);

namespace Pixielity\Discovery\Tests\Fixtures\Classes\Commands;

use Illuminate\Console\Command;
use Pixielity\Discovery\Tests\Fixtures\Attributes\TestAttribute;

/**
 * Test Console Command.
 *
 * A test fixture representing a console command that extends
 * Laravel's Command class. This class is used to test parent
 * class discovery and the `extending()` validator.
 *
 * ## Characteristics:
 * - Extends Illuminate\Console\Command
 * - Marked with TestAttribute
 * - Instantiable (concrete class)
 *
 * ## Test Scenarios:
 * - Parent class discovery (extends Command)
 * - Attribute discovery on commands
 * - Command auto-registration
 * - Instantiability validation
 *
 * @author  Pixielity Development Team
 *
 * @since   1.0.0
 */
#[TestAttribute]
class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'A test command for discovery testing';

    /**
     * Execute the console command.
     *
     * This method contains the command logic. For testing purposes,
     * it simply returns a success exit code.
     *
     * @return int Command exit code (0 for success)
     */
    public function handle(): int
    {
        // Return success exit code for testing
        return 0;
    }
}
