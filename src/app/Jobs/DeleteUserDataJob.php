<?php

namespace App\Jobs;

use App\Mail\Auth\UserDataDeletionInitiated;
use App\Mail\Auth\UserDataDeletionNotification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Job for deleting user data for GDPR "Right to Erasure" compliance.
 */
class DeleteUserDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private User $user) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $userEmail = $this->user->email;

            // Send initiation confirmation email before deletion
            try {
                Mail::send(new UserDataDeletionInitiated($userEmail));
            } catch (\Throwable $e) {
                Log::warning('Error sending data deletion initiation email', [
                    'email' => $userEmail,
                    'error' => $e->getMessage(),
                ]);
                // Continue even if email fails - the deletion should proceed
            }

            // Hard delete user's appointments through pets
            $this->user->pets()->each(fn ($pet) => $pet->appointments()->forceDelete());

            // Hard delete user's pets
            $this->user->pets()->forceDelete();

            // Hard delete user's gifts
            $this->user->gifts()->forceDelete();

            // Hard delete user notification preferences
            $this->user->notificationPreference()?->forceDelete();

            // Anonymize sensitive data before final deletion
            $this->anonymizeUser();

            // Hard delete the user account
            $this->user->forceDelete();

            // Send completion confirmation email to the user's former email address
            try {
                Mail::send(new UserDataDeletionNotification($userEmail));
            } catch (\Throwable $e) {
                Log::warning('Error sending data deletion confirmation email', [
                    'email' => $userEmail,
                    'error' => $e->getMessage(),
                ]);
                // Continue even if email fails - the account was still deleted
            }
        } catch (\Exception $e) {
            Log::error('Error deleting user data', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Anonymize user data before hard deletion.
     */
    private function anonymizeUser(): void
    {
        $this->user->update([
            'email' => 'deleted-'.$this->user->id.'@deleted.local',
        ]);
    }
}
