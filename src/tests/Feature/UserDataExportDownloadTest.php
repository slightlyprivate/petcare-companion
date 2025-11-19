<?php

namespace Tests\Feature;

use App\Jobs\DeleteExpiredExportsJob;
use App\Jobs\ExportUserDataJob;
use App\Mail\UserDataExportNotification;
use App\Models\Gift;
use App\Models\Pet;
use App\Models\User;
use App\Models\UserExport;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Test suite for user data export download functionality.
 *
 * Tests signed URL generation, email delivery, download routes, and cleanup jobs.
 */
class UserDataExportDownloadTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that export job creates UserExport record with expiration.
     */
    public function test_export_job_creates_user_export_record(): void
    {
        Storage::fake('exports');

        Mail::fake();

        /** @var Authenticatable $user */
        $user = User::factory()->create();

        // Dispatch the export job
        (new ExportUserDataJob($user))->handle();

        // Verify UserExport record was created
        $this->assertDatabaseHas('user_exports', [
            'user_id' => $user->id,
        ]);

        // Verify export expires 7 days from now
        $export = UserExport::where('user_id', $user->id)->first();
        $this->assertNotNull($export);
        // Allow time drift but ensure roughly 7 days from now
        $this->assertTrue($export->expires_at->greaterThan(now()->addDays(6)));
        $this->assertTrue($export->expires_at->lessThan(now()->addDays(8)));
    }

    /**
     * Test that export file is stored in exports disk.
     */
    public function test_export_file_stored_in_storage(): void
    {
        Storage::fake('exports');

        Mail::fake();

        /** @var Authenticatable $user */
        $user = User::factory()->create();
        Pet::factory()->create(['user_id' => $user->id]);

        // Dispatch the export job
        (new ExportUserDataJob($user))->handle();

        // Verify file exists in storage
        $export = UserExport::where('user_id', $user->id)->first();
        $this->assertNotNull($export);
        Storage::disk('exports')->assertExists($export->file_path);

        // Verify file is a valid zip
        $content = Storage::disk('exports')->get($export->file_path);
        $this->assertStringStartsWith('PK', $content); // ZIP file signature
    }

    /**
     * Test that export email is sent with signed URL.
     */
    public function test_export_email_sent_with_signed_url(): void
    {
        Storage::fake('exports');

        Mail::fake();

        /** @var Authenticatable $user */
        $user = User::factory()->create();

        // Dispatch the export job
        (new ExportUserDataJob($user))->handle();

        // Verify email was sent
        Mail::assertSent(UserDataExportNotification::class, function (UserDataExportNotification $mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    /**
     * Test that authenticated user can download export via signed URL.
     */
    public function test_user_can_download_export_with_signed_url(): void
    {
        Storage::fake('exports');

        Mail::fake();

        /** @var Authenticatable $user */
        $user = User::factory()->create();

        // Dispatch the export job
        (new ExportUserDataJob($user))->handle();

        $export = UserExport::where('user_id', $user->id)->first();

        // Generate signed URL
        $signedUrl = URL::temporarySignedRoute(
            'user.data.exports.download',
            $export->expires_at,
            ['export' => $export->id]
        );

        // Download via signed URL while authenticated
        $response = $this->actingAs($user, 'sanctum')
            ->get($signedUrl);

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/zip');

        // Verify file is served as attachment
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
    }

    /**
     * Test that unauthenticated user cannot access download endpoint.
     */
    public function test_unauthenticated_user_cannot_download_export(): void
    {
        Storage::fake('exports');

        Mail::fake();

        /** @var Authenticatable $user */
        $user = User::factory()->create();

        // Dispatch the export job
        (new ExportUserDataJob($user))->handle();

        $export = UserExport::where('user_id', $user->id)->first();

        // Generate signed URL
        $signedUrl = URL::temporarySignedRoute(
            'user.data.exports.download',
            $export->expires_at,
            ['export' => $export->id]
        );

        // Try to download without authentication
        $response = $this->get($signedUrl);

        $response->assertStatus(401);
    }

    /**
     * Test that user cannot download another user's export.
     */
    public function test_user_cannot_download_another_users_export(): void
    {
        Storage::fake('exports');

        Mail::fake();

        /** @var Authenticatable $user1 */
        $user1 = User::factory()->create();
        /** @var Authenticatable $user2 */
        $user2 = User::factory()->create();

        // Dispatch the export job for user1
        (new ExportUserDataJob($user1))->handle();

        $export = UserExport::where('user_id', $user1->id)->first();

        // Generate signed URL
        $signedUrl = URL::temporarySignedRoute(
            'user.data.exports.download',
            $export->expires_at,
            ['export' => $export->id]
        );

        // Try to download as user2
        $response = $this->actingAs($user2, 'sanctum')
            ->get($signedUrl);

        $response->assertStatus(403);
    }

    /**
     * Test that expired exports cannot be downloaded.
     */
    public function test_expired_export_cannot_be_downloaded(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();

        // Create an expired export
        $export = UserExport::create([
            'user_id' => $user->id,
            'file_path' => 'exports/test.zip',
            'file_name' => 'test.zip',
            'expires_at' => now()->subDay(),
        ]);

        // Generate signed URL (even though it's expired)
        $signedUrl = URL::temporarySignedRoute(
            'user.data.exports.download',
            $export->expires_at,
            ['export' => $export->id]
        );

        // Try to download
        $response = $this->actingAs($user, 'sanctum')
            ->get($signedUrl);

        $response->assertStatus(410);
        $response->assertJson(['status' => 'expired']);
    }

    /**
     * Test that download marks export as downloaded.
     */
    public function test_export_marked_as_downloaded(): void
    {
        Storage::fake('exports');

        Mail::fake();

        /** @var Authenticatable $user */
        $user = User::factory()->create();

        // Dispatch the export job
        (new ExportUserDataJob($user))->handle();

        $export = UserExport::where('user_id', $user->id)->first();

        // Verify not yet downloaded
        $this->assertNull($export->downloaded_at);

        // Generate signed URL and download
        $signedUrl = URL::temporarySignedRoute(
            'user.data.exports.download',
            $export->expires_at,
            ['export' => $export->id]
        );

        $this->actingAs($user, 'sanctum')->get($signedUrl);

        // Verify marked as downloaded
        $export->refresh();
        $this->assertNotNull($export->downloaded_at);
    }

    /**
     * Test that cleanup job deletes expired exports.
     */
    public function test_cleanup_job_deletes_expired_exports(): void
    {
        Storage::fake('exports');

        Mail::fake();

        /** @var Authenticatable $user1 */
        $user1 = User::factory()->create();
        /** @var Authenticatable $user2 */
        $user2 = User::factory()->create();

        // Create unexpired export
        (new ExportUserDataJob($user1))->handle();

        // Create expired export manually
        $expiredExport = UserExport::create([
            'user_id' => $user2->id,
            'file_path' => 'exports/expired.zip',
            'file_name' => 'expired.zip',
            'expires_at' => now()->subDay(),
        ]);

        // Store a dummy file for the expired export
        Storage::disk('local')->put($expiredExport->file_path, 'dummy content');

        // Verify both exports exist
        $this->assertDatabaseCount('user_exports', 2);
        $this->assertTrue(Storage::disk('local')->exists($expiredExport->file_path));

        // Run cleanup job
        (new DeleteExpiredExportsJob)->handle();

        // Verify expired export is deleted
        $this->assertDatabaseMissing('user_exports', ['id' => $expiredExport->id]);
        $this->assertFalse(Storage::disk('local')->exists($expiredExport->file_path));

        // Verify unexpired export still exists
        $this->assertDatabaseHas('user_exports', ['user_id' => $user1->id]);
    }

    /**
     * Test that cleanup job handles missing files gracefully.
     */
    public function test_cleanup_job_handles_missing_files(): void
    {
        // Create expired export with non-existent file
        $expiredExport = UserExport::create([
            'user_id' => User::factory()->create()->id,
            'file_path' => 'exports/nonexistent.zip',
            'file_name' => 'nonexistent.zip',
            'expires_at' => now()->subDay(),
        ]);

        // Run cleanup job (should not throw error) and still delete DB record
        (new DeleteExpiredExportsJob)->handle();

        // Verify export record is deleted even if file doesn't exist
        $this->assertDatabaseMissing('user_exports', ['id' => $expiredExport->id]);
    }

    /**
     * Test that cleanup command can be run via artisan.
     */
    public function test_cleanup_command_runs_successfully(): void
    {
        Mail::fake();

        /** @var Authenticatable $user */
        $user = User::factory()->create();

        // Create expired export
        UserExport::create([
            'user_id' => $user->id,
            'file_path' => 'exports/expired.zip',
            'file_name' => 'expired.zip',
            'expires_at' => now()->subDay(),
        ]);

        // Run artisan command
        $this->artisan('exports:cleanup')
            ->assertSuccessful()
            ->expectsOutput('✓ Expired exports cleanup completed successfully.');

        // Verify export was deleted
        $this->assertDatabaseCount('user_exports', 0);
    }

    /**
     * Test that export with no data creates valid zip.
     */
    public function test_export_with_no_data_creates_valid_zip(): void
    {
        Storage::fake('exports');

        Mail::fake();

        /** @var Authenticatable $user */
        $user = User::factory()->create();

        // Dispatch the export job without any related data
        (new ExportUserDataJob($user))->handle();

        $export = UserExport::where('user_id', $user->id)->first();

        // Verify file is stored and is a valid zip
        $this->assertTrue(Storage::disk('exports')->exists($export->file_path));
        $content = Storage::disk('exports')->get($export->file_path);
        $this->assertStringStartsWith('PK', $content);
    }

    /**
     * Test that export includes all user data types.
     */
    public function test_export_includes_all_data_types(): void
    {
        Storage::fake('exports');

        Mail::fake();

        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create(['user_id' => $user->id]);
        Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
        ]);

        // Dispatch the export job
        (new ExportUserDataJob($user))->handle();

        $export = UserExport::where('user_id', $user->id)->first();

        // Get file content and verify structure
        $content = Storage::disk('exports')->get($export->file_path);

        // Create temporary file to extract
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tmpFile, $content);

        $zip = new \ZipArchive;
        $zip->open($tmpFile);

        // Verify all expected files are in the zip
        $this->assertTrue($zip->locateName('user.json') !== false);
        $this->assertTrue($zip->locateName('pets.json') !== false);
        $this->assertTrue($zip->locateName('gifts.json') !== false);
        $this->assertTrue($zip->locateName('appointments.json') !== false);

        $zip->close();
        unlink($tmpFile);
    }
}
