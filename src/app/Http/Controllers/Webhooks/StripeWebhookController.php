<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Wehook\Stripe\StripeWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controller for handling Stripe webhooks.
 *
 * @group Webhooks
 */
class StripeWebhookController extends Controller
{
    /** @var StripeWebhookService */
    private $stripeWebhookService;

    public function __construct(StripeWebhookService $stripeWebhookService)
    {
        $this->stripeWebhookService = $stripeWebhookService;
    }

    /**
     * Handle incoming Stripe webhook.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $this->stripeWebhookService->handle(
                $payload,
                $sigHeader
            );
        } catch (\Exception $e) {
            Log::error('Error processing Stripe webhook', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 400);
        }

        return response()->json(['status' => 'success']);
    }
}
