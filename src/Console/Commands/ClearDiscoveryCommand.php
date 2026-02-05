<?php

namespace Pixielity\Discovery\Console\Commands;

use Illuminate\Console\Command;
use Pixielity\Discovery\Concerns\InteractsWithPrompts;
use Pixielity\Discovery\Contracts\CacheManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * ClearDiscoveryCommand - Clears discovery caches.
 *
 * Removes all cached discovery results or a specific cache by key.
 * This is useful during development when classes are added/removed,
 * or during deployment to ensure fresh discovery.
 */
#[AsCommand(
    name: 'discovery:clear',
    description: 'Clear discovery caches'
)]
class ClearDiscoveryCommand extends Command
{
    use InteractsWithPrompts;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discovery:clear
                            {key? : Specific cache key to clear}
                            {--all : Clear all caches without confirmation}';

    /**
     * Execute the console command.
     *
     * @param  CacheManagerInterface $cacheManager The cache manager
     * @return int                   Command exit code
     */
    public function handle(CacheManagerInterface $cacheManager): int
    {
        // Display header
        $this->header('Clear Discovery Cache', '🗑️');

        $key = $this->argument('key');

        if ($key) {
            // Clear specific cache key
            $this->note("Clearing discovery cache: <fg=cyan>{$key}</>");
            $this->newLine();

            $this->spinner(
                fn () => $cacheManager->clear($key),
                "Clearing cache '{$key}'..."
            );

            $this->newLine();
            $this->success("Discovery cache '<fg=cyan>{$key}</>' cleared successfully!");
        } else {
            // Clear all caches
            $this->caution('This will clear all discovery caches.');
            $this->newLine();

            if (! $this->option('all') && ! $this->askConfirmation('Are you sure you want to continue?', false)) {
                $this->note('Operation cancelled.');

                return self::SUCCESS;
            }

            $this->newLine();
            $this->spinner(
                fn () => $cacheManager->clear(),
                'Clearing all discovery caches...'
            );

            $this->newLine();
            $this->success('All discovery caches cleared successfully!');
        }

        // Display helpful tip
        $this->newLine();
        $this->divider('Tip');
        $this->informational('💡 Run <fg=cyan>discovery:cache</> to rebuild the cache.');

        return self::SUCCESS;
    }
}
