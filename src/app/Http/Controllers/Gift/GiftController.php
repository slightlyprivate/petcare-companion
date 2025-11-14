<?php

namespace App\Http\Controllers\Gift;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gift\ExportGiftReceiptRequest;
use App\Models\Gift;
use App\Services\Gift\GiftService;
use Illuminate\Auth\Access\AuthorizationException;

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
     */
    public function exportReceipt(ExportGiftReceiptRequest $request, Gift $gift): \Illuminate\Http\Response
    {
        // Check if user owns this gift
        if ($gift->user_id !== $request->user()->id) {
            throw new AuthorizationException;
        }

        $pdfContent = $this->giftService->exportReceiptAsFile($gift);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="receipt_'.$gift->id.'.pdf"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }
}
