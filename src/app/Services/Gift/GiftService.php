<?php

namespace App\Services\Gift;

use App\Exceptions\Receipt\ReceiptMetadataException;
use App\Models\Gift;
use App\Services\Receipt\ReceiptMetadataValidator;
use Barryvdh\DomPDF\Facade\Pdf;
use Stripe\Stripe;

/**
 * Service for managing gift operations.
 */
class GiftService
{
    private ReceiptMetadataValidator $validator;

    public function __construct()
    {
        $this->validator = new ReceiptMetadataValidator;
    }

    /**
     * Generate receipt data for a gift.
     *
     * @throws ReceiptMetadataException if critical metadata is missing
     */
    public function generateReceiptData(Gift $gift): array
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $chargeId = $gift->stripe_charge_id;
        $metadata = $gift->stripe_metadata ?? [];

        $receiptData = [
            'gift' => $gift,
            'chargeId' => $chargeId,
            'metadata' => $metadata,
            'costInCredits' => $gift->cost_in_credits,
            'receiptDate' => now()->format('Y-m-d H:i:s'),
            'donorEmail' => $gift->user->email,
            'petName' => $gift->pet->name,
            'petSpecies' => $gift->pet->species,
            'petBreed' => $gift->pet->breed ?? 'Not specified',
            'petOwner' => $gift->pet->owner_name,
            'status' => ucfirst($gift->status),
            'completedAt' => $gift->completed_at?->format('Y-m-d H:i:s'),
        ];

        // Validate metadata before returning
        $this->validator->validate($receiptData, $gift->id, $chargeId);

        return $receiptData;
    }

    /**
     * Export receipt as PDF content.
     *
     *
     * @return string PDF binary content
     *
     * @throws ReceiptMetadataException if metadata validation fails
     */
    public function exportReceiptAsFile(Gift $gift): string
    {
        $data = $this->generateReceiptData($gift);

        // Generate PDF from HTML view using DomPDF
        $pdf = Pdf::loadView('receipts.gift-receipt', $data);

        return $pdf->output();
    }
}
