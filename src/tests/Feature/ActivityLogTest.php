<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Gift;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that User model creation is logged.
     */
    public function test_user_creation_is_logged(): void
    {
        Activity::query()->delete();

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'role' => 'user',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'event' => 'created',
        ]);

        $activity = Activity::latest()->first();
        $this->assertNotNull($activity);
        $this->assertEquals('created', $activity->event);
        $this->assertArrayHasKey('email', $activity->properties['attributes']);
    }

    /**
     * Test that User model updates are logged.
     */
    public function test_user_update_is_logged(): void
    {
        $user = User::factory()->create([
            'email' => 'original@example.com',
        ]);

        Activity::query()->delete();

        $user->update(['role' => 'admin']);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'event' => 'updated',
        ]);

        $activity = Activity::latest()->first();
        $this->assertNotNull($activity);
        $this->assertEquals('updated', $activity->event);
    }

    /**
     * Test that Pet model creation is logged.
     */
    public function test_pet_creation_is_logged(): void
    {
        $user = User::factory()->create();
        Activity::query()->delete();

        $pet = Pet::factory()->create([
            'user_id' => $user->id,
            'name' => 'Fluffy',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Pet::class,
            'subject_id' => $pet->id,
            'event' => 'created',
        ]);

        $activity = Activity::latest()->first();
        $this->assertNotNull($activity);
        $this->assertEquals('created', $activity->event);
        $this->assertArrayHasKey('name', $activity->properties['attributes']);
    }

    /**
     * Test that Pet model updates are logged.
     */
    public function test_pet_update_is_logged(): void
    {
        $pet = Pet::factory()->create(['name' => 'Fluffy']);
        Activity::query()->delete();

        $pet->update(['name' => 'Fido']);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Pet::class,
            'subject_id' => $pet->id,
            'event' => 'updated',
        ]);

        $activity = Activity::latest()->first();
        $this->assertNotNull($activity);
        $this->assertEquals('updated', $activity->event);
        $properties = $activity->properties;
        $this->assertArrayHasKey('old', $properties);
        $this->assertArrayHasKey('attributes', $properties);
    }

    /**
     * Test that Pet model deletion is logged.
     */
    public function test_pet_deletion_is_logged(): void
    {
        $pet = Pet::factory()->create();
        $petId = $pet->id;
        Activity::query()->delete();

        $pet->delete();

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Pet::class,
            'subject_id' => $petId,
            'event' => 'deleted',
        ]);

        $activity = Activity::latest()->first();
        $this->assertNotNull($activity);
        $this->assertEquals('deleted', $activity->event);
    }

    /**
     * Test that Appointment model creation is logged.
     */
    public function test_appointment_creation_is_logged(): void
    {
        $pet = Pet::factory()->create();
        Activity::query()->delete();

        $appointment = Appointment::factory()->create([
            'pet_id' => $pet->id,
            'title' => 'Vet Checkup',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Appointment::class,
            'subject_id' => $appointment->id,
            'event' => 'created',
        ]);

        $activity = Activity::latest()->first();
        $this->assertNotNull($activity);
        $this->assertEquals('created', $activity->event);
        $this->assertArrayHasKey('title', $activity->properties['attributes']);
    }

    /**
     * Test that Appointment model updates are logged.
     */
    public function test_appointment_update_is_logged(): void
    {
        $appointment = Appointment::factory()->create(['title' => 'Vet Checkup']);
        Activity::query()->delete();

        $appointment->update(['title' => 'Updated Vet Checkup']);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Appointment::class,
            'subject_id' => $appointment->id,
            'event' => 'updated',
        ]);

        $activity = Activity::latest()->first();
        $this->assertNotNull($activity);
        $this->assertEquals('updated', $activity->event);
    }

    /**
     * Test that Appointment model deletion is logged.
     */
    public function test_appointment_deletion_is_logged(): void
    {
        $appointment = Appointment::factory()->create();
        $appointmentId = $appointment->id;
        Activity::query()->delete();

        $appointment->delete();

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Appointment::class,
            'subject_id' => $appointmentId,
            'event' => 'deleted',
        ]);

        $activity = Activity::latest()->first();
        $this->assertNotNull($activity);
        $this->assertEquals('deleted', $activity->event);
    }

    /**
     * Test that Gift model creation is logged.
     */
    public function test_gift_creation_is_logged(): void
    {
        $user = User::factory()->create();
        $pet = Pet::factory()->create();
        Activity::query()->delete();

        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
        ]);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Gift::class,
            'subject_id' => $gift->id,
            'event' => 'created',
        ]);

        $activity = Activity::latest()->first();
        $this->assertNotNull($activity);
        $this->assertEquals('created', $activity->event);
    }

    /**
     * Test that Gift status updates are logged.
     */
    public function test_gift_status_update_is_logged(): void
    {
        $gift = Gift::factory()->create(['status' => 'pending']);
        Activity::query()->delete();

        $gift->markAsPaid();

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Gift::class,
            'subject_id' => $gift->id,
            'event' => 'updated',
        ]);

        $activity = Activity::latest()->first();
        $this->assertNotNull($activity);
        $this->assertEquals('updated', $activity->event);
        $properties = $activity->properties;
        $this->assertArrayHasKey('old', $properties);
        $this->assertEquals('pending', $properties['old']['status']);
        $this->assertEquals('paid', $properties['attributes']['status']);
    }

    /**
     * Test that activity logs contain proper attributes for Pet.
     */
    public function test_pet_activity_log_contains_expected_fields(): void
    {
        $user = User::factory()->create();
        Activity::query()->delete();

        $pet = Pet::factory()->create([
            'user_id' => $user->id,
            'name' => 'Buddy',
            'species' => 'dog',
            'breed' => 'golden_retriever',
            'is_public' => true,
        ]);

        $activity = Activity::where('subject_type', Pet::class)->latest()->first();
        $this->assertNotNull($activity);

        $attributes = $activity->properties['attributes'];
        $this->assertArrayHasKey('name', $attributes);
        $this->assertArrayHasKey('species', $attributes);
        $this->assertArrayHasKey('breed', $attributes);
        $this->assertArrayHasKey('is_public', $attributes);
        $this->assertEquals('Buddy', $attributes['name']);
        $this->assertTrue($attributes['is_public']);
    }

    /**
     * Test that activity logs contain proper attributes for Appointment.
     */
    public function test_appointment_activity_log_contains_expected_fields(): void
    {
        $pet = Pet::factory()->create();
        Activity::query()->delete();

        $appointment = Appointment::factory()->create([
            'pet_id' => $pet->id,
            'title' => 'Grooming',
            'notes' => 'Winter coat trim',
        ]);

        $activity = Activity::where('subject_type', Appointment::class)->latest()->first();
        $this->assertNotNull($activity);

        $attributes = $activity->properties['attributes'];
        $this->assertArrayHasKey('pet_id', $attributes);
        $this->assertArrayHasKey('title', $attributes);
        $this->assertArrayHasKey('notes', $attributes);
        $this->assertEquals('Grooming', $attributes['title']);
    }

    /**
     * Test that activity logs track all user-triggered events.
     */
    public function test_multiple_operations_create_separate_logs(): void
    {
        $pet = Pet::factory()->create();
        Activity::query()->delete();

        // Create
        $pet = Pet::factory()->create();
        // Update
        $pet->update(['name' => 'Updated']);
        // Another update
        $pet->update(['is_public' => true]);

        $activities = Activity::where('subject_type', Pet::class)->get();
        $this->assertCount(3, $activities);

        $events = $activities->pluck('event')->all();
        $this->assertContains('created', $events);
        $this->assertContains('updated', $events);
    }
}
