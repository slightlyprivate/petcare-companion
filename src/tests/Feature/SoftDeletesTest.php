<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Donation;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SoftDeletesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Pet $pet;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->pet = Pet::factory()->for($this->user)->create();
    }

    #[Test]
    public function pet_can_be_soft_deleted(): void
    {
        $this->assertFalse($this->pet->trashed());

        $response = $this->actingAs($this->user, 'sanctum')
            ->delete("/api/pets/{$this->pet->id}");

        $response->assertStatus(204);
        $this->assertTrue($this->pet->refresh()->trashed());
    }

    #[Test]
    public function soft_deleted_pet_is_not_returned_in_index(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->delete("/api/pets/{$this->pet->id}");

        $response = $this->actingAs($this->user, 'sanctum')
            ->get('/api/pets');

        $response->assertStatus(200);
        $this->assertEmpty($response->json('data'));
    }

    #[Test]
    public function soft_deleted_pet_can_be_restored(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->delete("/api/pets/{$this->pet->id}");

        $this->assertTrue($this->pet->refresh()->trashed());

        $response = $this->actingAs($this->user, 'sanctum')
            ->post("/api/pets/{$this->pet->id}/restore");

        $response->assertStatus(200);
        $this->assertFalse($this->pet->refresh()->trashed());
        $this->assertEquals(__('pets.restore.success'), $response->json('message'));
    }

    #[Test]
    public function user_cannot_restore_another_users_pet(): void
    {
        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->delete("/api/pets/{$this->pet->id}");

        $response = $this->actingAs($anotherUser, 'sanctum')
            ->post("/api/pets/{$this->pet->id}/restore");

        $response->assertStatus(403);
    }

    #[Test]
    public function restoring_non_deleted_pet_returns_not_deleted_message(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->post("/api/pets/{$this->pet->id}/restore");

        $response->assertStatus(200);
        $this->assertEquals('Pet is not deleted', $response->json('message'));
        $this->assertFalse($this->pet->refresh()->trashed());
    }

    #[Test]
    public function appointment_can_be_soft_deleted(): void
    {
        $appointment = Appointment::factory()->for($this->pet)->create();

        $this->assertFalse($appointment->trashed());

        $response = $this->actingAs($this->user, 'sanctum')
            ->delete("/api/appointments/{$appointment->id}");

        $response->assertStatus(204);
        $this->assertTrue($appointment->refresh()->trashed());
    }

    #[Test]
    public function soft_deleted_appointment_is_not_returned_in_index(): void
    {
        $appointment = Appointment::factory()->for($this->pet)->create();

        $this->actingAs($this->user, 'sanctum')
            ->delete("/api/appointments/{$appointment->id}");

        $response = $this->actingAs($this->user, 'sanctum')
            ->get("/api/pets/{$this->pet->id}/appointments");

        $response->assertStatus(200);
        $this->assertEmpty($response->json('data'));
    }

    #[Test]
    public function donation_can_be_soft_deleted(): void
    {
        $donation = Donation::factory()
            ->for($this->user)
            ->for($this->pet)
            ->create();

        $this->assertFalse($donation->trashed());

        // Note: We don't have a direct delete endpoint for donations,
        // so we test the soft delete at the model level
        $donation->delete();

        $this->assertTrue($donation->refresh()->trashed());
    }

    #[Test]
    public function soft_deleted_donation_can_be_restored(): void
    {
        $donation = Donation::factory()
            ->for($this->user)
            ->for($this->pet)
            ->create();

        $donation->delete();
        $this->assertTrue($donation->refresh()->trashed());

        $donation->restore();

        $this->assertFalse($donation->refresh()->trashed());
    }

    #[Test]
    public function deleted_at_column_is_present(): void
    {
        $this->assertTrue($this->pet->refresh()->trashed() === false);

        $this->pet->delete();

        $this->assertNotNull($this->pet->refresh()->deleted_at);
    }
}
