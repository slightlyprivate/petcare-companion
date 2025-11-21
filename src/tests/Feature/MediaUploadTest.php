<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_upload_media(): void
    {
        Storage::fake('public');
        /** @var User $user */
        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('activity.jpg');

        $response = $this->actingAs($user)->postJson('/api/uploads', [
            'file' => $file,
            'context' => 'activities',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['path', 'url', 'disk', 'context', 'original_name', 'mime_type'],
            ]);

        $storedPath = $response->json('data.path');
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        $disk->assertExists($storedPath);
    }

    public function test_upload_validation_rejects_invalid_payloads(): void
    {
        Storage::fake('public');
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/uploads', [
            'file' => UploadedFile::fake()->create('clip.mov', 20480, 'video/quicktime'),
            'context' => 'not-valid',
        ]);

        $response->assertStatus(422);
    }
}
