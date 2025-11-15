<?php

namespace App\Services\Receipt;

use App\Exceptions\Receipt\ReceiptMetadataException;

/**
 * Validator for receipt metadata completeness and reliability.
 *
 * Ensures all required fields are present before PDF rendering to prevent
 * incomplete or missing data in receipts.
 *
 * @group Services
 */
class ReceiptMetadataValidator
{
    /**
     * Fields required for a complete receipt.
     */
    private const REQUIRED_FIELDS = [
        'chargeId',
        'metadata',
        'costInCredits' => 'cost data',
        'receiptDate' => 'receipt date',
        'donorEmail' => 'donor email',
        'petName' => 'pet name',
        'petSpecies' => 'pet species',
        'status' => 'payment status',
    ];

    /**
     * Critical metadata fields from Stripe that must be present for a valid receipt.
     */
    private const CRITICAL_METADATA_FIELDS = [
        'amount' => 'transaction amount',
        'currency' => 'currency code',
        'payment_method' => 'payment method type',
    ];

    /**
     * Validate receipt data before PDF rendering.
     *
     * @throws ReceiptMetadataException if critical fields are missing
     */
    public function validate(array $receiptData, ?string $transactionId = null, ?string $chargeId = null): void
    {
        $missingFields = [];

        // Check required fields
        foreach (self::REQUIRED_FIELDS as $field => $description) {
            // Handle both numeric and associative array keys
            $key = is_string($field) ? $field : $description;
            $fieldName = is_string($field) ? $field : $description;

            if (! isset($receiptData[$fieldName]) || $receiptData[$fieldName] === '') {
                $missingFields[] = $fieldName;
            }
        }

        // Check critical metadata fields
        $metadata = $receiptData['metadata'] ?? [];
        foreach (self::CRITICAL_METADATA_FIELDS as $field => $description) {
            if (empty($metadata[$field])) {
                $missingFields[] = "metadata.{$field}";
            }
        }

        // If critical data is missing, throw exception
        if (! empty($missingFields)) {
            throw new ReceiptMetadataException(
                'Receipt metadata incomplete: '.implode(', ', $missingFields),
                $missingFields,
                $transactionId,
                $chargeId
            );
        }
    }

    /**
     * Check if metadata is sufficient (non-critical fields may be missing).
     *
     * Returns false if critical payment details are absent.
     */
    public function isSufficient(array $metadata): bool
    {
        // Must have at least charge ID and payment method
        return ! empty($metadata['amount']) &&
            ! empty($metadata['currency']) &&
            ! empty($metadata['payment_method']);
    }

    /**
     * Get list of critical fields that are missing.
     */
    public function getMissingCriticalFields(array $metadata): array
    {
        $missing = [];

        foreach (self::CRITICAL_METADATA_FIELDS as $field => $description) {
            if (empty($metadata[$field])) {
                $missing[$field] = $description;
            }
        }

        return $missing;
    }
}
