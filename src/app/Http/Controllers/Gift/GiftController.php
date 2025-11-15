<?php

namespace App\Http\Controllers\Gift;

use App\Exceptions\Receipt\ReceiptMetadataException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Gift\ExportGiftReceiptRequest;
use App\Http\Requests\Gift\StoreWalletGiftRequest;
use App\Models\Gift;
use App\Models\Pet;
use App\Services\Gift\GiftService;
use App\Services\Pet\PetGiftService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
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
    public function __construct(private GiftService $giftService, private PetGiftService $petGiftService) {}

    /**
     * Create a gift using wallet credits.
     */
    public function store(StoreWalletGiftRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();
        /** @var Pet $pet */
        $pet = Pet::findOrFail($data['pet_id']);

        $result = $this->petGiftService->createGift($data, $user, $pet);

        return response()->json($result, 201);
    }

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
