<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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

            // Delete user's appointments through pets
            $this->user->pets()->each(fn ($pet) => $pet->appointments()->delete());

            // Delete user's pets
            $this->user->pets()->delete();

            // Delete user's donations
            $this->user->donations()->delete();

            // Delete user notification preferences
            $this->user->notificationPreference()->delete();

            // Delete the user account
            $this->user->delete();

            // Send confirmation email to the user's former email address
            try {
                Mail::send('emails.data-deletion-confirmation', [
                    'email' => $userEmail,
                ], function ($message) use ($userEmail) {
                    $message->to($userEmail)
                        ->subject('Your PetCare Companion Account Has Been Deleted');
                });
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Error sending data deletion email', [
                    'email' => $userEmail,
                    'error' => $e->getMessage(),
                ]);
                // Continue even if email fails - the account was still deleted
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error deleting user data', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
