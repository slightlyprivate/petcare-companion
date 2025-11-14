<?php

namespace App\Http\Controllers\Gift;

use App\Exceptions\Receipt\ReceiptMetadataException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Gift\ExportGiftReceiptRequest;
use App\Models\Gift;
use App\Services\Gift\GiftService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Log;

/**
 * Controller for managing gifts.
 *
 * @authenticated
 *
 * @group Gifts
 */
class GiftController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private GiftService $giftService) {}

    /**
     * Export a gift receipt.
     *
     * Returns a PDF receipt for a completed gift. Fails with 422 if metadata is incomplete.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function exportReceipt(ExportGiftReceiptRequest $request, Gift $gift)
    {
        // Check if user owns this gift
        if ($gift->user_id !== $request->user()->id) {
            throw new AuthorizationException;
        }

        try {
            $pdfContent = $this->giftService->exportReceiptAsFile($gift);

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="receipt_'.$gift->id.'.pdf"',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
            ]);
        } catch (ReceiptMetadataException $e) {
            Log::warning('Receipt export failed due to incomplete metadata', [
                'gift_id' => $gift->id,
                'user_id' => $request->user()->id,
                'missing_fields' => $e->getMissingFields(),
                'charge_id' => $e->getChargeId(),
            ]);

            return response()->json([
                'message' => 'Receipt data is incomplete. Please contact support.',
                'error' => 'incomplete_metadata',
                'missing_fields' => $e->getMissingFields(),
            ], 422);
        }
    }
}
