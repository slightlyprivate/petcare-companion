<?php

namespace App\Exceptions\Receipt;

use Exception;

/**
 * Exception thrown when receipt metadata is incomplete or invalid.
 *
 * This exception signals that a receipt cannot be safely rendered due to missing
 * payment details from Stripe. Finance and audit teams should be alerted to investigate.
 *
 * @group Exceptions
 */
class ReceiptMetadataException extends Exception
{
    /**
     * List of missing fields.
     */
    private array $missingFields = [];

    /**
     * The associated transaction ID (gift_id or donation_id).
     */
    private ?string $transactionId = null;

    /**
     * The Stripe charge ID for audit trail.
     */
    private ?string $chargeId = null;

    public function __construct(
        string $message,
        array $missingFields = [],
        ?string $transactionId = null,
        ?string $chargeId = null,
    ) {
        parent::__construct($message);

        $this->missingFields = $missingFields;
        $this->transactionId = $transactionId;
        $this->chargeId = $chargeId;

        // Log alert for finance/audit teams
        $this->logAuditAlert();
    }

    /**
     * Get the list of missing fields.
     */
    public function getMissingFields(): array
    {
        return $this->missingFields;
    }

    /**
     * Get the transaction ID.
     */
    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    /**
     * Get the Stripe charge ID.
     */
    public function getChargeId(): ?string
    {
        return $this->chargeId;
    }

    /**
     * Log an alert for finance/audit teams to investigate.
     */
    private function logAuditAlert(): void
    {
        \Illuminate\Support\Facades\Log::alert('Receipt metadata incomplete - manual audit required', [
            'exception' => self::class,
            'message' => $this->getMessage(),
            'transaction_id' => $this->transactionId,
            'charge_id' => $this->chargeId,
            'missing_fields' => $this->missingFields,
            'timestamp' => now()->toIso8601String(),
            'severity' => 'HIGH',
            'action_required' => 'Verify payment details in Stripe dashboard and retry receipt generation',
        ]);
    }
}
