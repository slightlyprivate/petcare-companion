<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use ZipArchive;

/**
 * Job for exporting user data for GDPR compliance.
 */
class ExportUserDataJob implements ShouldQueue
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
            // Gather user data
            $userData = [
                'user' => $this->user->only(['id', 'email', 'role', 'created_at', 'updated_at']),
                'pets' => $this->user->pets()->get()->map(fn ($pet) => $pet->only(['id', 'name', 'species', 'breed', 'owner_name', 'is_public', 'created_at', 'updated_at']))->toArray(),
                'gifts' => $this->user->gifts()->get()->map(fn ($gift) => $gift->only(['id', 'cost_in_credits', 'status', 'completed_at', 'created_at']))->toArray(),
                'appointments' => Appointment::whereHas('pet', fn ($q) => $q->where('user_id', $this->user->id))->get()->map(fn ($apt) => $apt->only(['id', 'pet_id', 'title', 'scheduled_at', 'notes', 'created_at']))->toArray(),
            ];

            // Create temporary zip file
            $zipPath = storage_path('app/exports/user_data_'.$this->user->id.'_'.now()->timestamp.'.zip');

            if (! file_exists(storage_path('app/exports'))) {
                mkdir(storage_path('app/exports'), 0755, true);
            }

            $zip = new ZipArchive;
            $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            // Add JSON files for each data type
            $zip->addFromString('user.json', json_encode($userData['user'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $zip->addFromString('pets.json', json_encode($userData['pets'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $zip->addFromString('gifts.json', json_encode($userData['gifts'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $zip->addFromString('appointments.json', json_encode($userData['appointments'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            // Close to ensure file is written
            $closeResult = $zip->close();

            if (! $closeResult) {
                throw new \Exception('Failed to close zip archive');
            }

            // Generate file reference for storage (in real implementation, would use signed URLs)
            $fileName = basename($zipPath);
            $downloadUrl = "File stored as: {$fileName}";

            // Send download link via email
            try {
                Mail::send('emails.data-export', [
                    'user' => $this->user,
                    'downloadUrl' => $downloadUrl,
                    'zipPath' => $zipPath,
                ], function ($message) {
                    $message->to($this->user->email)
                        ->subject('Your PetCare Companion Data Export');
                });
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Error sending data export email', [
                    'user_id' => $this->user->id,
                    'error' => $e->getMessage(),
                ]);
                // Continue even if email fails - the file was still generated
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error exporting user data', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
