<?php

namespace App\Jobs;

use App\Mail\UserDataExportNotification;
use App\Models\Appointment;
use App\Models\User;
use App\Models\UserExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
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

            // Create zip file in memory and store using Storage::disk
            $zipContent = $this->generateZipContent($userData);
            $fileName = 'user_data_'.$this->user->id.'_'.now()->timestamp.'.zip';
            $filePath = 'exports/'.$fileName;

            // Store zip file using the local disk
            Storage::disk('local')->put($filePath, $zipContent);

            // Create UserExport record with 7-day expiration
            $expiresAt = now()->addDays(7);
            $userExport = UserExport::create([
                'user_id' => $this->user->id,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'expires_at' => $expiresAt,
            ]);

            // Generate signed temporary URL for download
            $downloadUrl = URL::temporarySignedRoute(
                'user.data.exports.download',
                $expiresAt,
                ['export' => $userExport->id]
            );

            // Send download link via email
            try {
                Mail::send(new UserDataExportNotification($this->user, $downloadUrl));
            } catch (\Throwable $e) {
                Log::warning('Error sending data export email', [
                    'user_id' => $this->user->id,
                    'export_id' => $userExport->id,
                    'error' => $e->getMessage(),
                ]);
                // Continue even if email fails - the file was still generated
            }
        } catch (\Exception $e) {
            Log::error('Error exporting user data', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate zip file content from user data.
     */
    private function generateZipContent(array $userData): string
    {
        $zip = new ZipArchive;
        $tmpFile = tempnam(sys_get_temp_dir(), 'export_');

        if ($zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Failed to create zip archive');
        }

        // Add JSON files for each data type
        $zip->addFromString('user.json', json_encode($userData['user'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $zip->addFromString('pets.json', json_encode($userData['pets'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $zip->addFromString('gifts.json', json_encode($userData['gifts'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $zip->addFromString('appointments.json', json_encode($userData['appointments'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        if ($zip->close() !== true) {
            throw new \Exception('Failed to close zip archive');
        }

        // Read the zip file content
        $content = file_get_contents($tmpFile);
        unlink($tmpFile);

        return $content;
    }
}
