<?php

declare(strict_types=1);

namespace Pixielity\Discovery\Concerns;

use BackedEnum;
use DateTimeInterface;

use function filter_var;

use Illuminate\Support\Str;

use function is_array;
use function is_bool;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;

use Laravel\Prompts\Progress;

use function Laravel\Prompts\progress;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\table;
use function Laravel\Prompts\warning;

use Pixielity\Discovery\Support\Reflection;
use Throwable;

/**
 * Has Command Helpers Trait.
 *
 * Provides utility methods for console commands including:
 * - Formatted output helpers (header, failure, caution, etc.)
 * - Validation helpers (email, required arguments)
 * - Display helpers (summary, listing, divider, etc.)
 * - Exception handling
 *
 * This trait works alongside InteractsWithPrompts to provide a complete
 * set of command utilities.
 *
 * ## Example Usage:
 * ```php
 * use Pixielity\Discovery\Concerns\InteractsWithPrompts;
 *
 * class MyCommand extends Command
 * {
 *     use InteractsWithPrompts, HasCommandHelpers;
 * use Illuminate\Support\Str;
 *
 *     public function handle(): int
 *     {
 *         $this->header('My Command');
 *         $this->summary(['key' => 'value']);
 *         $this->completed('Done!');
 *         return self::SUCCESS;
 *     }
 * }
 * ```
 */
trait InteractsWithPrompts
{
    /**
     * Show an intro message.
     *
     * @param string $message Intro message
     */
    protected function intro(string $message): void
    {
        $this->line('');
        $this->line("<fg=cyan;options=bold>{$message}</>");
        $this->line('');
    }

    /**
     * Show an outro message.
     *
     * @param string $message Outro message
     */
    protected function outro(string $message): void
    {
        $this->line('');
        $this->line("<fg=green;options=bold>{$message}</>");
        $this->line('');
    }

    /**
     * Show a formatted header for the command.
     *
     * @param string $title Header title
     * @param string $icon  Optional emoji icon
     */
    protected function header(string $title, string $icon = '🚀'): void
    {
        $this->intro(Str::format('%s %s', $icon, $title));
        $this->newLine();
    }

    /**
     * Show a failure/error message.
     *
     * @param string $message Error message
     * @param string $icon    Optional emoji icon
     */
    protected function failure(string $message, string $icon = '❌'): void
    {
        error(Str::format('%s %s', $icon, $message));
    }

    /**
     * Show a success message.
     *
     * @param string $message Success message
     * @param string $icon    Optional emoji icon
     */
    protected function success(string $message, string $icon = '✅'): void
    {
        $this->info(Str::format('%s %s', $icon, $message));
    }

    /**
     * Show a warning/caution message.
     *
     * @param string $message Warning message
     * @param string $icon    Optional emoji icon
     */
    protected function caution(string $message, string $icon = '⚠️'): void
    {
        warning(Str::format('%s %s', $icon, $message));
    }

    /**
     * Show an informational message.
     *
     * @param string $message Info message
     * @param string $icon    Optional emoji icon
     */
    protected function informational(string $message, string $icon = 'ℹ️'): void
    {
        info(Str::format('%s %s', $icon, $message));
    }

    /**
     * Show a note message.
     *
     * @param string $message Note message
     * @param string $icon    Optional emoji icon
     */
    protected function note(string $message, string $icon = '📝'): void
    {
        note(Str::format('<fg=blue>%s %s</>', $icon, $message));
    }

    /**
     * Display a formatted table.
     *
     * @param array<string>              $headers Table headers
     * @param array<array<string|mixed>> $rows    Table rows
     */
    protected function displayTable(array $headers, array $rows): void
    {
        table($headers, $rows);
    }

    /**
     * Run a callback with a spinner.
     *
     * @template T
     *
     * @param  callable(): T $callback Callback to execute
     * @param  string        $message  Spinner message
     * @return T             Result of the callback
     */
    protected function spinner(callable $callback, string $message = 'Processing...'): mixed
    {
        return spin($callback, $message);
    }

    /**
     * Create a progress bar for manual control.
     *
     * @param  int                                           $total Total steps
     * @param  string                                        $label Progress bar label
     * @return Progress<array<int, mixed>>|array<int, mixed>
     */
    protected function createProgress(int $total, string $label = 'Processing...'): Progress|array
    {
        /** @var Progress<array<int, mixed>> $progress */
        $progress = progress(
            label: $label,
            steps: $total
        );

        return $progress;
    }

    /**
     * Handle exceptions with user-friendly output.
     *
     * @param  Throwable $throwable Exception to handle
     * @param  string    $context   Context message
     * @return int       Command exit code (FAILURE)
     */
    protected function exception(Throwable $throwable, string $context = 'An error occurred'): int
    {
        $this->failure(Str::format('%s: %s', $context, $throwable->getMessage()));

        if ($this->output->isVerbose()) {
            $this->newLine();
            $this->line('<fg=red>Stack trace:</>');
            $this->line($throwable->getTraceAsString());
        }

        return self::FAILURE;
    }

    /**
     * Validate required argument.
     *
     * @param  string      $name         Argument name
     * @param  string      $errorMessage Error message if validation fails
     * @return string|null Argument value or null if validation fails
     */
    protected function validateRequiredArgument(string $name, string $errorMessage = 'is required'): ?string
    {
        /** @var string|null $value */
        $value = $this->argument($name);

        if (empty($value)) {
            $this->failure(Str::format('%s %s', $name, $errorMessage));

            return null;
        }

        return $value;
    }

    /**
     * Validate email format.
     *
     * @param  string $email Email to validate
     * @return bool   True if valid, false otherwise
     */
    protected function validateEmail(string $email): bool
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->failure("Invalid email format: {$email}");

            return false;
        }

        return true;
    }

    /**
     * Show a summary table with key-value pairs.
     *
     * @param array<string, mixed> $data    Data to display
     * @param string               $title   Optional title
     * @param array<string>        $headers Optional custom headers
     */
    protected function summary(array $data, string $title = 'Summary', array $headers = ['Property', 'Value']): void
    {
        if ($title !== '' && $title !== '0') {
            $this->note($title);
            $this->newLine();
        }

        $rows = [];
        foreach ($data as $key => $value) {
            $rows[] = [$key, $this->formatValue($value)];
        }

        $this->displayTable($headers, $rows);
    }

    /**
     * Format a value for display.
     *
     * @param  mixed  $value Value to format
     * @return string Formatted value
     */
    protected function formatValue(mixed $value): string
    {
        if ($value === null) {
            return '<fg=gray>null</>';
        }

        if (is_bool($value)) {
            return $value ? '<fg=green>Yes</>' : '<fg=red>No</>';
        }

        if (is_array($value)) {
            return implode(', ', $value);
        }

        if (Reflection::implements($value, DateTimeInterface::class)) {
            return $value->format('Y-m-d H:i:s');
        }

        if (Reflection::implements($value, BackedEnum::class)) {
            return (string) $value->value;
        }

        return (string) $value;
    }

    /**
     * Ask for confirmation with a default value.
     *
     * @param  string $question Question to ask
     * @param  bool   $default  Default value
     * @return bool   User's answer
     */
    protected function askConfirmation(string $question, bool $default = false): bool
    {
        return confirm($question, $default);
    }

    /**
     * Display command completion message.
     *
     * @param string       $message Completion message
     * @param array<mixed> $stats   Optional statistics to display
     */
    protected function completed(string $message = 'Operation completed successfully!', array $stats = []): void
    {
        $this->newLine();
        $this->outro("🎉 {$message}");

        if ($stats !== []) {
            $this->newLine();
            $this->summary($stats, 'Statistics');
        }
    }

    /**
     * Display a step in a multi-step process.
     *
     * @param int    $current Current step number
     * @param int    $total   Total number of steps
     * @param string $message Step message
     */
    protected function step(int $current, int $total, string $message): void
    {
        $this->line(Str::format('  <fg=cyan>[%d/%d]</> %s', $current, $total, $message));
    }

    /**
     * Display a list of items.
     *
     * @param array<string> $items Items to display
     * @param string        $icon  Optional icon for each item
     */
    protected function listing(array $items, string $icon = '•'): void
    {
        foreach ($items as $item) {
            $this->line(Str::format('  %s %s', $icon, $item));
        }
    }

    /**
     * Display a section divider.
     *
     * @param string $title Optional section title
     */
    protected function divider(string $title = ''): void
    {
        $this->newLine();
        if ($title !== '' && $title !== '0') {
            $this->line(Str::format('<fg=gray>━━━ %s ━━━</>', $title));
        } else {
            $this->line('<fg=gray>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</>');
        }

        $this->newLine();
    }
}
