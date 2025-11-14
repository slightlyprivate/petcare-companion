<?php

namespace App\Console\Commands;

use App\Jobs\DeleteExpiredExportsJob;
use Illuminate\Console\Command;

/**
 * Artisan command to clean up expired user data exports.
 *
 * Can be scheduled in `routes/console.php` or run manually:
 *   php artisan exports:cleanup
 */
class CleanupExpiredExportsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exports:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired user data exports and remove associated files';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting cleanup of expired user data exports...');

        try {
            // Dispatch the job synchronously
            dispatch_sync(new DeleteExpiredExportsJob);

            $this->info('✓ Expired exports cleanup completed successfully.');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('✗ Failed to cleanup expired exports: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
