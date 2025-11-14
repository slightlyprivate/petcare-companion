<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\User;
use App\Notifications\Pet\PetCreatedNotification;
use App\Notifications\Pet\PetDeletedNotification;
use App\Notifications\Pet\PetUpdatedNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature tests for the Pet API endpoints.
 */
class PetApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_list_pets()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pets = Pet::factory(3)->for($user)->create();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/pets');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'species',
                        'breed',
                        'birth_date',
                        'owner_name',
                        'age',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta',
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    #[Test]
    public function it_can_filter_pets_by_species()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        Pet::factory()->for($user)->create(['species' => 'Dog']);
        Pet::factory()->for($user)->create(['species' => 'Cat']);
        Pet::factory()->for($user)->create(['species' => 'Dog']);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/pets?species=Dog');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));

        $response->assertJson(
            fn (AssertableJson $json) => $json->where('data.0.species', 'Dog')
                ->where('data.1.species', 'Dog')
                ->etc()
        );
    }

    #[Test]
    public function it_can_filter_pets_by_owner_name()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        Pet::factory()->for($user)->create(['owner_name' => 'John Smith']);
        Pet::factory()->for($user)->create(['owner_name' => 'Jane Doe']);
        Pet::factory()->for($user)->create(['owner_name' => 'John Johnson']);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/pets?owner_name=John');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    #[Test]
    public function it_can_filter_pets_by_name()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        Pet::factory()->for($user)->create(['name' => 'Buddy']);
        Pet::factory()->for($user)->create(['name' => 'Bella']);
        Pet::factory()->for($user)->create(['name' => 'Buddy Jr']);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/pets?name=Buddy');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    #[Test]
    public function it_can_sort_pets()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        Pet::factory()->for($user)->create(['name' => 'Zebra']);
        Pet::factory()->for($user)->create(['name' => 'Alpha']);
        Pet::factory()->for($user)->create(['name' => 'Beta']);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/pets?sort_by=name&sort_direction=asc');

        $response->assertStatus(200);
        $names = collect($response->json('data'))->pluck('name')->toArray();
        $this->assertEquals(['Alpha', 'Beta', 'Zebra'], $names);
    }

    #[Test]
    public function it_paginates_pets()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        Pet::factory(25)->for($user)->create();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/pets?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(25, $response->json('meta.total'));
        $this->assertEquals(3, $response->json('meta.last_page'));
    }

    #[Test]
    public function it_can_create_a_pet()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $petData = [
            'name' => 'Buddy',
            'species' => 'Dog',
            'breed' => 'Golden Retriever',
            'birth_date' => '2020-05-15',
            'owner_name' => 'John Smith',
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/pets', $petData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'species',
                    'breed',
                    'birth_date',
                    'owner_name',
                    'age',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson(
                fn (AssertableJson $json) => $json->where('data.name', 'Buddy')
                    ->where('data.species', 'Dog')
                    ->where('data.breed', 'Golden Retriever')
                    ->where('data.birth_date', '2020-05-15')
                    ->where('data.owner_name', 'John Smith')
                    ->etc()
            );

        $this->assertDatabaseHas('pets', [
            'name' => 'Buddy',
            'species' => 'Dog',
            'breed' => 'Golden Retriever',
            'owner_name' => 'John Smith',
        ]);
    }

    #[Test]
    public function it_can_create_a_pet_without_optional_fields()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $petData = [
            'name' => 'Buddy',
            'species' => 'Dog',
            'owner_name' => 'John Smith',
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/pets', $petData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('pets', [
            'name' => 'Buddy',
            'species' => 'Dog',
            'owner_name' => 'John Smith',
            'breed' => null,
            'birth_date' => null,
        ]);
    }

    #[Test]
    public function it_validates_required_fields_when_creating_pet()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/pets', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'species', 'owner_name']);
    }

    #[Test]
    public function it_validates_birth_date_format_when_creating_pet()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $petData = [
            'name' => 'Buddy',
            'species' => 'Dog',
            'owner_name' => 'John Smith',
            'birth_date' => 'invalid-date',
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/pets', $petData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['birth_date']);
    }

    #[Test]
    public function it_validates_birth_date_not_in_future()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $futureDate = Carbon::tomorrow()->format('Y-m-d');

        $petData = [
            'name' => 'Buddy',
            'species' => 'Dog',
            'owner_name' => 'John Smith',
            'birth_date' => $futureDate,
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/pets', $petData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['birth_date']);
    }

    #[Test]
    public function it_validates_field_length_limits()
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $petData = [
            'name' => str_repeat('a', 256), // Too long
            'species' => str_repeat('b', 101), // Too long
            'breed' => str_repeat('c', 101), // Too long
            'owner_name' => str_repeat('d', 256), // Too long
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/pets', $petData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'species', 'breed', 'owner_name']);
    }

    #[Test]
    public function it_denies_access_to_pets_owned_by_other_users()
    {
        /** @var Authenticatable $owner */
        $owner = User::factory()->create();

        /** @var Authenticatable $otherUser */
        $otherUser = User::factory()->create();

        $pet = Pet::factory()->for($owner)->create();

        $response = $this->actingAs($otherUser, 'sanctum')->getJson("/api/pets/{$pet->id}");

        $response->assertForbidden();
    }

    #[Test]
    public function administrators_can_view_any_pet()
    {
        /** @var Authenticatable $admin */
        $admin = User::factory()->administrator()->create();

        /** @var Authenticatable $owner */
        $owner = User::factory()->create();

        $pet = Pet::factory()->for($owner)->create();

        $response = $this->actingAs($admin, 'sanctum')->getJson("/api/pets/{$pet->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $pet->id);
    }

    #[Test]
    public function administrators_can_update_pets_owned_by_other_users()
    {
        /** @var Authenticatable $admin */
        $admin = User::factory()->administrator()->create();

        /** @var Authenticatable $owner */
        $owner = User::factory()->create();

        $pet = Pet::factory()->for($owner)->create([
            'name' => 'Original',
            'species' => 'Dog',
            'owner_name' => 'Owner Name',
        ]);

        $updatePayload = [
            'name' => 'Updated Name',
            'species' => 'Dog',
            'owner_name' => 'Owner Name',
        ];

        $response = $this->actingAs($admin, 'sanctum')->putJson("/api/pets/{$pet->id}", $updatePayload);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');

        $this->assertDatabaseHas('pets', [
            'id' => $pet->id,
            'name' => 'Updated Name',
        ]);
    }

    #[Test]
    public function it_sends_notification_when_creating_a_pet()
    {
        Notification::fake();

        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $petData = [
            'name' => 'Buddy',
            'species' => 'Dog',
            'breed' => 'Golden Retriever',
            'birth_date' => '2020-05-15',
            'owner_name' => 'John Smith',
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/pets', $petData);

        $response->assertStatus(201);

        Notification::assertSentTo($user, PetCreatedNotification::class);
    }

    #[Test]
    public function it_respects_pet_create_notification_preference()
    {
        Notification::fake();

        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $user->notificationPreference()->create(['pet_create_notifications' => false]);

        $petData = [
            'name' => 'Buddy',
            'species' => 'Dog',
            'breed' => 'Golden Retriever',
            'birth_date' => '2020-05-15',
            'owner_name' => 'John Smith',
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/pets', $petData);

        $response->assertStatus(201);

        Notification::assertNotSentTo($user, PetCreatedNotification::class);
    }

    #[Test]
    public function it_sends_notification_when_deleting_a_pet()
    {
        Notification::fake();

        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->for($user)->create();

        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/pets/{$pet->id}");

        $response->assertNoContent();

        Notification::assertSentTo($user, PetDeletedNotification::class);
    }

    #[Test]
    public function it_respects_pet_delete_notification_preference()
    {
        Notification::fake();

        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $user->notificationPreference()->create(['pet_delete_notifications' => false]);
        $pet = Pet::factory()->for($user)->create();

        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/pets/{$pet->id}");

        $response->assertNoContent();

        Notification::assertNotSentTo($user, PetDeletedNotification::class);
    }

    #[Test]
    public function it_sends_notification_when_updating_a_pet_with_changes()
    {
        Notification::fake();

        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->for($user)->create([
            'name' => 'Original',
            'species' => 'Dog',
            'owner_name' => 'Owner Name',
        ]);

        $updatePayload = [
            'name' => 'Updated Name',
            'species' => 'Dog',
            'owner_name' => 'Owner Name',
        ];

        $response = $this->actingAs($user, 'sanctum')->putJson("/api/pets/{$pet->id}", $updatePayload);

        $response->assertOk();

        Notification::assertSentTo($user, PetUpdatedNotification::class, function (PetUpdatedNotification $notification) {
            return isset($notification->changes['name']) && $notification->changes['name'] === 'Updated Name';
        });
    }

    #[Test]
    public function it_does_not_send_notification_when_updating_pet_without_changes()
    {
        Notification::fake();

        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->for($user)->create([
            'name' => 'Original',
            'species' => 'Dog',
            'owner_name' => 'Owner Name',
        ]);

        $updatePayload = [
            'name' => 'Original',
            'species' => 'Dog',
            'owner_name' => 'Owner Name',
        ];

        $response = $this->actingAs($user, 'sanctum')->putJson("/api/pets/{$pet->id}", $updatePayload);

        $response->assertOk();

        Notification::assertNotSentTo($user, PetUpdatedNotification::class);
    }

    #[Test]
    public function it_respects_pet_update_notification_preference()
    {
        Notification::fake();

        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $user->notificationPreference()->create(['pet_update_notifications' => false]);
        $pet = Pet::factory()->for($user)->create([
            'name' => 'Original',
            'species' => 'Dog',
            'owner_name' => 'Owner Name',
        ]);

        $updatePayload = [
            'name' => 'Updated Name',
            'species' => 'Dog',
            'owner_name' => 'Owner Name',
        ];

        $response = $this->actingAs($user, 'sanctum')->putJson("/api/pets/{$pet->id}", $updatePayload);

        $response->assertOk();

        Notification::assertNotSentTo($user, PetUpdatedNotification::class);
    }
}
