<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PetAvatarUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_upload_pet_avatar(): void
    {
        Storage::fake('public');

        /** @var User $owner */
        $owner = User::factory()->create();
        $pet = Pet::factory()->create(['user_id' => $owner->id]);

        $avatarFile = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $response = $this->actingAs($owner)
            ->postJson("/api/pets/{$pet->id}/avatar", [
                'avatar' => $avatarFile,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'avatar_url']);

        $pet->refresh();
        $this->assertNotNull($pet->avatar_path);

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        $disk->assertExists($pet->avatar_path);
    }

    public function test_avatar_upload_requires_authentication(): void
    {
        $pet = Pet::factory()->create();
        $avatarFile = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->postJson("/api/pets/{$pet->id}/avatar", [
            'avatar' => $avatarFile,
        ]);

        $response->assertStatus(401);
    }

    public function test_avatar_upload_requires_authorization(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create();
        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        $pet = Pet::factory()->create(['user_id' => $owner->id]);

        $avatarFile = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($otherUser)
            ->postJson("/api/pets/{$pet->id}/avatar", [
                'avatar' => $avatarFile,
            ]);

        $response->assertStatus(403);
    }

    public function test_avatar_upload_validates_file_type(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();
        $pet = Pet::factory()->create(['user_id' => $owner->id]);

        $invalidFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($owner)
            ->postJson("/api/pets/{$pet->id}/avatar", [
                'avatar' => $invalidFile,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    public function test_old_avatar_is_deleted_when_new_one_is_uploaded(): void
    {
        Storage::fake('public');

        /** @var User $owner */
        $owner = User::factory()->create();
        $pet = Pet::factory()->create(['user_id' => $owner->id]);

        // Upload first avatar
        $firstAvatar = UploadedFile::fake()->image('first.jpg');
        $this->actingAs($owner)
            ->postJson("/api/pets/{$pet->id}/avatar", ['avatar' => $firstAvatar]);

        $pet->refresh();
        $firstPath = $pet->avatar_path;

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        $disk->assertExists($firstPath);

        // Upload second avatar
        $secondAvatar = UploadedFile::fake()->image('second.jpg');
        $this->actingAs($owner)
            ->postJson("/api/pets/{$pet->id}/avatar", ['avatar' => $secondAvatar]);

        $pet->refresh();

        $disk->assertMissing($firstPath);
        $disk->assertExists($pet->avatar_path);
    }
}
