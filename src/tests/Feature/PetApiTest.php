<?php

namespace Tests\Feature;

use App\Models\Pet;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class PetApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_pets()
    {
        $pets = Pet::factory(3)->create();

        $response = $this->getJson('/api/pets');

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

    /** @test */
    public function it_can_filter_pets_by_species()
    {
        Pet::factory()->create(['species' => 'Dog']);
        Pet::factory()->create(['species' => 'Cat']);
        Pet::factory()->create(['species' => 'Dog']);

        $response = $this->getJson('/api/pets?species=Dog');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));

        $response->assertJson(fn (AssertableJson $json) => $json->where('data.0.species', 'Dog')
            ->where('data.1.species', 'Dog')
            ->etc()
        );
    }

    /** @test */
    public function it_can_filter_pets_by_owner_name()
    {
        Pet::factory()->create(['owner_name' => 'John Smith']);
        Pet::factory()->create(['owner_name' => 'Jane Doe']);
        Pet::factory()->create(['owner_name' => 'John Johnson']);

        $response = $this->getJson('/api/pets?owner_name=John');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    /** @test */
    public function it_can_filter_pets_by_name()
    {
        Pet::factory()->create(['name' => 'Buddy']);
        Pet::factory()->create(['name' => 'Bella']);
        Pet::factory()->create(['name' => 'Buddy Jr']);

        $response = $this->getJson('/api/pets?name=Buddy');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    /** @test */
    public function it_can_sort_pets()
    {
        Pet::factory()->create(['name' => 'Zebra']);
        Pet::factory()->create(['name' => 'Alpha']);
        Pet::factory()->create(['name' => 'Beta']);

        $response = $this->getJson('/api/pets?sort_by=name&sort_direction=asc');

        $response->assertStatus(200);
        $names = collect($response->json('data'))->pluck('name')->toArray();
        $this->assertEquals(['Alpha', 'Beta', 'Zebra'], $names);
    }

    /** @test */
    public function it_paginates_pets()
    {
        Pet::factory(25)->create();

        $response = $this->getJson('/api/pets?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(25, $response->json('meta.total'));
        $this->assertEquals(3, $response->json('meta.last_page'));
    }

    /** @test */
    public function it_can_create_a_pet()
    {
        $petData = [
            'name' => 'Buddy',
            'species' => 'Dog',
            'breed' => 'Golden Retriever',
            'birth_date' => '2020-05-15',
            'owner_name' => 'John Smith',
        ];

        $response = $this->postJson('/api/pets', $petData);

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
            ->assertJson(fn (AssertableJson $json) => $json->where('data.name', 'Buddy')
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

    /** @test */
    public function it_can_create_a_pet_without_optional_fields()
    {
        $petData = [
            'name' => 'Buddy',
            'species' => 'Dog',
            'owner_name' => 'John Smith',
        ];

        $response = $this->postJson('/api/pets', $petData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('pets', [
            'name' => 'Buddy',
            'species' => 'Dog',
            'owner_name' => 'John Smith',
            'breed' => null,
            'birth_date' => null,
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_pet()
    {
        $response = $this->postJson('/api/pets', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'species', 'owner_name']);
    }

    /** @test */
    public function it_validates_birth_date_format_when_creating_pet()
    {
        $petData = [
            'name' => 'Buddy',
            'species' => 'Dog',
            'owner_name' => 'John Smith',
            'birth_date' => 'invalid-date',
        ];

        $response = $this->postJson('/api/pets', $petData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['birth_date']);
    }

    /** @test */
    public function it_validates_birth_date_not_in_future()
    {
        $futureDate = Carbon::tomorrow()->format('Y-m-d');

        $petData = [
            'name' => 'Buddy',
            'species' => 'Dog',
            'owner_name' => 'John Smith',
            'birth_date' => $futureDate,
        ];

        $response = $this->postJson('/api/pets', $petData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['birth_date']);
    }

    /** @test */
    public function it_validates_field_length_limits()
    {
        $petData = [
            'name' => str_repeat('a', 256), // Too long
            'species' => str_repeat('b', 101), // Too long
            'breed' => str_repeat('c', 101), // Too long
            'owner_name' => str_repeat('d', 256), // Too long
        ];

        $response = $this->postJson('/api/pets', $petData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'species', 'breed', 'owner_name']);
    }
}
