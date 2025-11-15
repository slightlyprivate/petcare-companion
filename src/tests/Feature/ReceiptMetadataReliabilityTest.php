<?php

namespace Tests\Feature;

use App\Exceptions\Receipt\ReceiptMetadataException;
use App\Models\Gift;
use App\Models\Pet;
use App\Models\User;
use App\Services\Gift\GiftService;
use App\Services\Receipt\ReceiptMetadataValidator;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Test suite for receipt metadata reliability and validation.
 *
 * Tests metadata completeness checks, retry logic, and audit logging.
 */
class ReceiptMetadataReliabilityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that receipt export fails with incomplete metadata.
     */
    public function test_receipt_export_fails_with_incomplete_metadata(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        // Create gift with incomplete metadata (no payment method info)
        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'status' => 'paid',
            'stripe_metadata' => [
                'amount' => 1000,
                'currency' => 'usd',
                // Missing 'payment_method' - critical field
            ],
        ]);

        // Try to export receipt
        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/gifts/{$gift->id}/receipt");

        // Should fail with 422
        $response->assertStatus(422)
            ->assertJson(['error' => 'incomplete_metadata']);
    }

    /**
     * Test that receipt export succeeds with complete metadata.
     */
    public function test_receipt_export_succeeds_with_complete_metadata(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        // Create gift with complete metadata
        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'status' => 'paid',
            'stripe_charge_id' => 'ch_test_123',
            'stripe_metadata' => [
                'amount' => 1000,
                'currency' => 'usd',
                'payment_method' => 'card',
                'brand' => 'visa',
                'last4' => '4242',
            ],
        ]);

        // Export receipt
        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/gifts/{$gift->id}/receipt");

        // Should succeed
        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test that metadata validator catches missing amount.
     */
    public function test_validator_catches_missing_amount(): void
    {
        $validator = new ReceiptMetadataValidator;

        $metadata = [
            'currency' => 'usd',
            'payment_method' => 'card',
            // Missing 'amount'
        ];

        $this->assertFalse($validator->isSufficient($metadata));

        $missing = $validator->getMissingCriticalFields($metadata);
        $this->assertArrayHasKey('amount', $missing);
    }

    /**
     * Test that metadata validator catches missing currency.
     */
    public function test_validator_catches_missing_currency(): void
    {
        $validator = new ReceiptMetadataValidator;

        $metadata = [
            'amount' => 1000,
            'payment_method' => 'card',
            // Missing 'currency'
        ];

        $this->assertFalse($validator->isSufficient($metadata));

        $missing = $validator->getMissingCriticalFields($metadata);
        $this->assertArrayHasKey('currency', $missing);
    }

    /**
     * Test that metadata validator catches missing payment_method.
     */
    public function test_validator_catches_missing_payment_method(): void
    {
        $validator = new ReceiptMetadataValidator;

        $metadata = [
            'amount' => 1000,
            'currency' => 'usd',
            // Missing 'payment_method'
        ];

        $this->assertFalse($validator->isSufficient($metadata));

        $missing = $validator->getMissingCriticalFields($metadata);
        $this->assertArrayHasKey('payment_method', $missing);
    }

    /**
     * Test that GiftService validates receipt data on generation.
     */
    public function test_gift_service_validates_receipt_data(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'status' => 'paid',
            'stripe_metadata' => [], // Empty metadata
        ]);

        $service = new GiftService;

        // Should throw ReceiptMetadataException
        try {
            $service->generateReceiptData($gift);
            $this->fail('Expected ReceiptMetadataException to be thrown');
        } catch (ReceiptMetadataException $e) {
            $this->assertNotEmpty($e->getMissingFields());
            $this->assertEquals($gift->id, $e->getTransactionId());
        }
    }

    /**
     * Test that ReceiptMetadataException logs audit alert.
     */
    public function test_metadata_exception_logs_audit_alert(): void
    {
        Log::shouldReceive('alert')->once()->with(
            'Receipt metadata incomplete - manual audit required',
            \Mockery::on(function ($context) {
                return isset($context['transaction_id']) &&
                    isset($context['severity']) &&
                    $context['severity'] === 'HIGH' &&
                    isset($context['action_required']);
            })
        );

        $exception = new ReceiptMetadataException(
            'Missing critical fields',
            ['amount', 'payment_method'],
            'gift_123',
            'ch_test_123'
        );

        $this->assertNotNull($exception);
    }

    /**
     * Test that missing receipt data throws exception with specific fields.
     */
    public function test_exception_includes_missing_fields_in_details(): void
    {
        $missingFields = ['metadata.amount', 'metadata.payment_method'];
        $exception = new ReceiptMetadataException(
            'Incomplete metadata',
            $missingFields,
            'gift_456',
            'ch_test_456'
        );

        $this->assertEquals($missingFields, $exception->getMissingFields());
        $this->assertEquals('gift_456', $exception->getTransactionId());
        $this->assertEquals('ch_test_456', $exception->getChargeId());
    }

    /**
     * Test that receipt with no charge ID but other metadata fails validation.
     */
    public function test_receipt_fails_with_no_charge_id(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'status' => 'paid',
            'stripe_charge_id' => null, // No charge ID
            'stripe_metadata' => [
                'amount' => 1000,
                'currency' => 'usd',
                'payment_method' => 'card',
            ],
        ]);

        $service = new GiftService;

        // Should fail validation because chargeId is null
        try {
            $service->generateReceiptData($gift);
            $this->fail('Expected ReceiptMetadataException');
        } catch (ReceiptMetadataException $e) {
            $this->assertContains('chargeId', $e->getMissingFields());
        }
    }

    /**
     * Test that validator accepts sufficient metadata.
     */
    public function test_validator_accepts_sufficient_metadata(): void
    {
        $validator = new ReceiptMetadataValidator;

        $metadata = [
            'amount' => 1000,
            'currency' => 'usd',
            'payment_method' => 'card',
            'brand' => 'visa',
            'last4' => '4242',
        ];

        $this->assertTrue($validator->isSufficient($metadata));
        $this->assertEmpty($validator->getMissingCriticalFields($metadata));
    }

    /**
     * Test that GiftService can successfully validate complete receipt data.
     */
    public function test_gift_service_validates_complete_receipt_data(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'status' => 'paid',
            'stripe_charge_id' => 'ch_test_complete',
            'stripe_metadata' => [
                'amount' => 1000,
                'currency' => 'usd',
                'payment_method' => 'card',
                'brand' => 'visa',
                'last4' => '4242',
            ],
        ]);

        $service = new GiftService;

        // Should not throw - validation should pass
        $receiptData = $service->generateReceiptData($gift);

        $this->assertNotNull($receiptData);
        $this->assertEquals($gift->cost_in_credits, $receiptData['costInCredits']);
        $this->assertEquals('ch_test_complete', $receiptData['chargeId']);
    }
}
