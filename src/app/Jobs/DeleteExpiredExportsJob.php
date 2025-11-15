<?php

namespace App\Jobs;

use App\Models\UserExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Job for deleting expired user data exports.
 *
 * Runs nightly to clean up ZIP files and database records for exports
 * that have passed their 7-day expiration window.
 */
class DeleteExpiredExportsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Find all expired exports
            $expiredExports = UserExport::expired()->get();

            $deletedCount = 0;
            $failedCount = 0;

            foreach ($expiredExports as $export) {
                try {
                    // Delete file from storage if it exists
                    if (Storage::disk('local')->exists($export->file_path)) {
                        Storage::disk('local')->delete($export->file_path);
                    }

                    // Delete the database record
                    $export->delete();
                    $deletedCount++;
                } catch (\Exception $e) {
                    Log::warning('Failed to delete expired export', [
                        'export_id' => $export->id,
                        'user_id' => $export->user_id,
                        'file_path' => $export->file_path,
                        'error' => $e->getMessage(),
                    ]);
                    $failedCount++;
                }
            }

            Log::info('Expired exports cleanup completed', [
                'deleted_count' => $deletedCount,
                'failed_count' => $failedCount,
                'total_expired' => $expiredExports->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error running expired exports cleanup job', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
