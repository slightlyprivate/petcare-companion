<?php

namespace App\Services\Donation;

use App\Models\Donation;
use Barryvdh\DomPDF\Facade\Pdf;
use Stripe\Stripe;

/**
 * Service for managing donation operations.
 */
class DonationService
{
    /**
     * Generate receipt data for a donation.
     */
    public function generateReceiptData(Donation $donation): array
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $chargeId = $donation->stripe_charge_id;
        $metadata = $donation->stripe_metadata ?? [];

        return [
            'donation' => $donation,
            'chargeId' => $chargeId,
            'metadata' => $metadata,
            'amountDollars' => $donation->amount_cents / 100,
            'receiptDate' => now()->format('Y-m-d H:i:s'),
            'donorEmail' => $donation->user->email,
            'petName' => $donation->pet->name,
            'petSpecies' => $donation->pet->species,
            'petBreed' => $donation->pet->breed ?? 'Not specified',
            'petOwner' => $donation->pet->owner_name,
            'status' => ucfirst($donation->status),
            'completedAt' => $donation->completed_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Export receipt as PDF content.
     *
     * @return string PDF binary content
     */
    public function exportReceiptAsFile(Donation $donation): string
    {
        $data = $this->generateReceiptData($donation);

        // Generate PDF from HTML view using DomPDF
        $pdf = Pdf::loadView('receipts.donation-receipt', $data);

        return $pdf->output();
    }
}
