<?php

namespace App\Http\Controllers\Donation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Donation\ExportDonationReceiptRequest;
use App\Models\Donation;
use App\Services\Donation\DonationService;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * Controller for managing donations.
 *
 * @group Donations
 */
class DonationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private DonationService $donationService) {}

    /**
     * Export a donation receipt.
     */
    public function exportReceipt(ExportDonationReceiptRequest $request, string $id): \Illuminate\Http\Response
    {
        $donation = Donation::findOrFail($id);

        // Check if user owns this donation
        if ($donation->user_id !== $request->user()->id) {
            throw new AuthorizationException('Unauthorized to access this donation');
        }

        $pdfContent = $this->donationService->exportReceiptAsFile($donation);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="receipt_'.$donation->id.'.pdf"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }
}
