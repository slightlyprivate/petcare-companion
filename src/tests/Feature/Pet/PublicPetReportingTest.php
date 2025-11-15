<?php

namespace Tests\Feature\Pet;

use App\Models\Gift;
use App\Models\GiftType;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for public pet reporting endpoints.
 *
 * @group Pets
 */
class PublicPetReportingTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function public_pet_report_includes_gift_summaries_by_type(): void
    {
        // Create a public pet
        $user = User::factory()->create();
        $pet = Pet::factory()->create(['user_id' => $user->id, 'is_public' => true]);

        // Create gift types
        $toyType = GiftType::factory()->create([
            'name' => 'Toy',
            'icon_emoji' => '🧸',
            'color_code' => '#FF6B6B',
        ]);
        $treatType = GiftType::factory()->create([
            'name' => 'Treat',
            'icon_emoji' => '🍖',
            'color_code' => '#FFA500',
        ]);

        // Create gifts of different types (1 credit = 20 cents)
        Gift::factory()->count(2)->create([
            'pet_id' => $pet->id,
            'gift_type_id' => $toyType->id,
            'cost_in_credits' => 50,
            'status' => 'paid',
        ]);

        Gift::factory()->count(3)->create([
            'pet_id' => $pet->id,
            'gift_type_id' => $treatType->id,
            'cost_in_credits' => 30,
            'status' => 'paid',
        ]);

        // Fetch the report
        $response = $this->getJson(route('public.pet-reports.show', $pet->id));

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => $pet->name,
                    'gift_count' => 5,
                ],
            ]);

        // Verify gift summaries
        $data = $response->json('data');
        $this->assertArrayHasKey('gift_summaries_by_type', $data);
        $this->assertCount(2, $data['gift_summaries_by_type']);

        // Check toy summary (2 gifts * 50 credits each = 100 credits = $20.00)
        $toySummary = collect($data['gift_summaries_by_type'])->firstWhere('gift_type_name', 'Toy');
        $this->assertEquals(2, $toySummary['count']);
        $this->assertEquals(20.0, $toySummary['total_value']);

        // Check treat summary (3 gifts * 30 credits each = 90 credits = $18.00)
        $treatSummary = collect($data['gift_summaries_by_type'])->firstWhere('gift_type_name', 'Treat');
        $this->assertEquals(3, $treatSummary['count']);
        $this->assertEquals(18.0, $treatSummary['total_value']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function public_pet_report_includes_transaction_audit_trail(): void
    {
        // Create a public pet
        $user = User::factory()->create();
        $pet = Pet::factory()->create(['user_id' => $user->id, 'is_public' => true]);

        // Create gifts with specific timestamps
        $gift1 = Gift::factory()->create([
            'pet_id' => $pet->id,
            'cost_in_credits' => 50,
            'status' => 'paid',
            'completed_at' => now()->subHours(2),
        ]);

        $gift2 = Gift::factory()->create([
            'pet_id' => $pet->id,
            'cost_in_credits' => 30,
            'status' => 'paid',
            'completed_at' => now()->subHours(1),
        ]);

        // Fetch the report
        $response = $this->getJson(route('public.pet-reports.show', $pet->id));

        $response->assertStatus(200);

        // Verify audit trail
        $data = $response->json('data');
        $this->assertArrayHasKey('transaction_audit_trail', $data);
        $this->assertCount(2, $data['transaction_audit_trail']);

        // Most recent transactions should be first (sorted by timestamp descending)
        // gift2 (30 credits) was completed 1 hour ago (more recent than gift1)
        $this->assertEquals(30, $data['transaction_audit_trail'][0]['amount_credits']);
        $this->assertEquals(50, $data['transaction_audit_trail'][1]['amount_credits']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function audit_trail_shows_correct_transaction_details(): void
    {
        $user = User::factory()->create();
        $pet = Pet::factory()->create(['user_id' => $user->id, 'is_public' => true, 'name' => 'Fluffy']);

        Gift::factory()->create([
            'pet_id' => $pet->id,
            'cost_in_credits' => 75,
            'status' => 'paid',
            'completed_at' => now(),
        ]);

        $response = $this->getJson(route('public.pet-reports.show', $pet->id));

        $response->assertStatus(200);

        $auditEntry = $response->json('data.transaction_audit_trail.0');
        $this->assertEquals('deduction', $auditEntry['type']);
        $this->assertEquals('Gift Sent', $auditEntry['type_label']);
        $this->assertStringContainsString('Fluffy', $auditEntry['reason']);
        $this->assertEquals(75, $auditEntry['amount_credits']);
        $this->assertEquals(1500, $auditEntry['amount_cents']); // 75 * 20 cents
        $this->assertEquals(15.0, $auditEntry['amount_dollars']);
        $this->assertEquals('Gift', $auditEntry['related_type']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function directory_pet_includes_gift_type_distribution(): void
    {
        $user = User::factory()->create();
        $pet = Pet::factory()->create(['user_id' => $user->id, 'is_public' => true]);

        $giftType = GiftType::factory()->create([
            'name' => 'Toy',
            'icon_emoji' => '🧸',
        ]);

        Gift::factory()->count(3)->create([
            'pet_id' => $pet->id,
            'gift_type_id' => $giftType->id,
            'status' => 'paid',
        ]);

        $response = $this->getJson(route('public.pets.index'));

        $response->assertStatus(200);

        $petData = collect($response->json('data'))->firstWhere('id', $pet->id);
        $this->assertArrayHasKey('gift_types', $petData);
        $this->assertCount(1, $petData['gift_types']);
        $this->assertEquals('Toy', $petData['gift_types'][0]['gift_type_name']);
        $this->assertEquals(3, $petData['gift_types'][0]['count']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function directory_pet_includes_report_url(): void
    {
        $user = User::factory()->create();
        $pet = Pet::factory()->create(['user_id' => $user->id, 'is_public' => true]);

        $response = $this->getJson(route('public.pets.index'));

        $response->assertStatus(200);

        $petData = collect($response->json('data'))->firstWhere('id', $pet->id);
        $this->assertArrayHasKey('report_url', $petData);
        $this->assertStringContainsString('pet-reports', $petData['report_url']);
        $this->assertStringContainsString($pet->id, $petData['report_url']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function pet_report_only_includes_paid_gifts(): void
    {
        $user = User::factory()->create();
        $pet = Pet::factory()->create(['user_id' => $user->id, 'is_public' => true]);

        Gift::factory()->create([
            'pet_id' => $pet->id,
            'cost_in_credits' => 50,
            'status' => 'paid',
        ]);

        Gift::factory()->create([
            'pet_id' => $pet->id,
            'cost_in_credits' => 30,
            'status' => 'pending',
        ]);

        Gift::factory()->create([
            'pet_id' => $pet->id,
            'cost_in_credits' => 20,
            'status' => 'failed',
        ]);

        $response = $this->getJson(route('public.pet-reports.show', $pet->id));

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'gift_count' => 1,
            ],
        ]);

        $data = $response->json('data');
        $this->assertCount(1, $data['transaction_audit_trail']);
        $this->assertEquals(50, $data['transaction_audit_trail'][0]['amount_credits']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cannot_view_report_for_private_pet(): void
    {
        $user = User::factory()->create();
        $pet = Pet::factory()->create(['user_id' => $user->id, 'is_public' => false]);

        $response = $this->getJson(route('public.pet-reports.show', $pet->id));

        $response->assertStatus(404);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function nonexistent_pet_returns_404(): void
    {
        $response = $this->getJson(route('public.pet-reports.show', 'nonexistent-id'));

        $response->assertStatus(404);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function gift_summary_calculates_average_value(): void
    {
        $user = User::factory()->create();
        $pet = Pet::factory()->create(['user_id' => $user->id, 'is_public' => true]);

        $giftType = GiftType::factory()->create();

        // Create gifts: 100 + 200 + 150 = 450 credits total, 3 gifts
        // 450 credits = $90 (450 * 20 cents = 9000 cents = $90)
        // average = $30 per gift (900 cents / 3)
        Gift::factory()->create([
            'pet_id' => $pet->id,
            'gift_type_id' => $giftType->id,
            'cost_in_credits' => 100,
            'status' => 'paid',
        ]);
        Gift::factory()->create([
            'pet_id' => $pet->id,
            'gift_type_id' => $giftType->id,
            'cost_in_credits' => 200,
            'status' => 'paid',
        ]);
        Gift::factory()->create([
            'pet_id' => $pet->id,
            'gift_type_id' => $giftType->id,
            'cost_in_credits' => 150,
            'status' => 'paid',
        ]);

        $response = $this->getJson(route('public.pet-reports.show', $pet->id));

        $response->assertStatus(200);

        $summary = $response->json('data.gift_summaries_by_type.0');
        $this->assertEquals(3, $summary['count']);
        $this->assertEquals(90.0, $summary['total_value']);
        $this->assertEquals(30.0, $summary['average_value']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function empty_pet_shows_empty_summaries(): void
    {
        $user = User::factory()->create();
        $pet = Pet::factory()->create(['user_id' => $user->id, 'is_public' => true]);

        $response = $this->getJson(route('public.pet-reports.show', $pet->id));

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'gift_count' => 0,
                ],
            ]);

        $data = $response->json('data');
        $this->assertEmpty($data['gift_summaries_by_type']);
        $this->assertEmpty($data['transaction_audit_trail']);
    }
}
