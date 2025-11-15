<?php

namespace App\Console\Commands;

use App\Models\CreditPurchase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Scan for pending credit purchases older than X minutes and log warnings.
 *
 * Usage:
 *   php artisan credits:scan-stale --minutes=30
 */
class ScanStaleCreditPurchasesCommand extends Command
{
    protected $signature = 'credits:scan-stale {--minutes=30 : Threshold in minutes for stale pending purchases}';

    protected $description = 'Log warnings for stale pending credit purchases older than the threshold';

    public function handle(): int
    {
        $minutes = (int) $this->option('minutes');

        $cutoff = now()->subMinutes($minutes);
        $stale = CreditPurchase::where('status', 'pending')
            ->where('created_at', '<', $cutoff)
            ->get();

        if ($stale->isEmpty()) {
            $this->info("No stale pending credit purchases older than {$minutes} minutes.");

            return self::SUCCESS;
        }

        $this->warn("Found {$stale->count()} stale pending credit purchases older than {$minutes} minutes.");

        foreach ($stale as $purchase) {
            Log::warning('Stale pending credit purchase detected', [
                'purchase_id' => $purchase->id,
                'user_id' => $purchase->user_id,
                'wallet_id' => $purchase->wallet_id,
                'credits' => $purchase->credits,
                'amount_cents' => $purchase->amount_cents,
                'created_at' => $purchase->created_at?->toIso8601String(),
                'stripe_session_id' => $purchase->stripe_session_id,
            ]);
        }

        return self::SUCCESS;
    }
}
