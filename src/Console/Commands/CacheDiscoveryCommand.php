<?php

namespace Fulers\Discovery\Console\Commands;

use function count;

use Fulers\Discovery\Contracts\CacheManagerInterface;
use Fulers\Discovery\Contracts\DiscoveryManagerInterface;
use Fulers\Foundation\Console\Commands\BaseCommand;
use Illuminate\Container\Attributes\Config;
use Symfony\Component\Console\Attribute\AsCommand;
use Throwable;

/**
 * CacheDiscoveryCommand - Warms discovery caches for production.
 *
 * This command pre-caches discovery results for all configured discovery paths.
 * Running this command during deployment ensures that the first request doesn't
 * experience the performance penalty of initial discovery.
 *
 * The command reads configured discovery paths from config/discovery.php and
 * executes discovery for each path, storing the results in cache.
 */
#[AsCommand(
    name: 'discovery:cache',
    description: 'Cache discovery results for production'
)]
class CacheDiscoveryCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discovery:cache
                            {--force : Force cache refresh even if cache exists}';

    /**
     * Create a new command instance.
     *
     * @param array<string, array<string>> $discoveryPaths Configured discovery paths
     */
    public function __construct(
        /**
         * The configured discovery paths.
         */
        #[Config('discovery.paths')]
        protected array $discoveryPaths = []
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * Iterates through all configured discovery paths and caches the results.
     * Shows progress information and statistics about the caching operation.
     *
     * @param  DiscoveryManagerInterface $discoveryManager The discovery manager
     * @param  CacheManagerInterface     $cacheManager     The cache manager
     * @return int                       Command exit code (0 for success)
     */
    public function handle(
        DiscoveryManagerInterface $discoveryManager,
        CacheManagerInterface $cacheManager
    ): int {
        try {
            // Display header
            $this->header('Discovery Cache', '📦');

            // If no paths configured, show warning
            if ($this->discoveryPaths === []) {
                $this->caution('No discovery paths configured in config/discovery.php');
                $this->newLine();
                $this->info('💡 Add paths to the "paths" configuration key to enable cache warming.');

                return self::SUCCESS;
            }

            // Check if we should force refresh
            $force = $this->option('force');

            if ($force) {
                $this->note('Force flag detected - clearing existing caches...');
                $this->spinner(
                    fn () => $cacheManager->clear(),
                    'Clearing caches...'
                );
                $this->newLine();
            }

            // Track statistics
            $totalPaths = count($this->discoveryPaths);
            $totalClasses = 0;
            $cachedPaths = 0;
            $skippedPaths = 0;
            $failedPaths = 0;

            $this->info('Caching discovery results...');
            $this->newLine();

            // Create progress bar
            $progress = $this->createProgress($totalPaths, 'Processing discovery paths');

            // Process each configured discovery path
            foreach ($this->discoveryPaths as $name => $directories) {
                $progress->label("Processing: <fg=cyan>{$name}</>");

                // Create cache key from path name
                $cacheKey = "discovery-{$name}";

                try {
                    // Skip if cache exists and not forcing
                    if (! $force && $cacheManager->get($cacheKey) !== null) {
                        $skippedPaths++;
                        $progress->advance();

                        continue;
                    }

                    // Discover classes in the directories
                    $classes = $discoveryManager
                        ->directories($directories)
                        ->cached($cacheKey)
                        ->get();

                    $classCount = count($classes);
                    $totalClasses += $classCount;
                    $cachedPaths++;
                    $progress->advance();
                } catch (Throwable $e) {
                    // Log error but continue with other paths
                    $failedPaths++;
                    $progress->advance();

                    logger()->error('Discovery cache failed', [
                        'path' => $name,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $progress->finish();
            $this->newLine();

            // Display completion message with statistics
            $stats = [
                'Total Paths' => $totalPaths,
                'Cached' => $cachedPaths,
                'Skipped' => $skippedPaths,
                'Failed' => $failedPaths,
                'Classes Discovered' => $totalClasses,
            ];

            if ($cachedPaths > 0) {
                $this->completed('Discovery caching completed successfully!', $stats);
            } else {
                $this->caution('No discovery paths were cached.');
                $this->newLine();
                $this->summary($stats, 'Statistics');
            }

            return self::SUCCESS;
        } catch (Throwable $throwable) {
            return $this->exception($throwable, 'Discovery caching failed');
        }
    }
}
