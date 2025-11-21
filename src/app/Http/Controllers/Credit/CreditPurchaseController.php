<?php

namespace App\Http\Controllers\Credit;

use App\Http\Controllers\Controller;
use App\Http\Requests\Credit\StoreCreditPurchaseRequest;
use App\Http\Resources\Credit\CreditPurchaseResource;
use App\Models\CreditBundle;
use App\Models\CreditPurchase;
use App\Services\Credit\CreditPurchaseService;
use Illuminate\Support\Facades\Log;

/**
 * Controller for managing credit purchases.
 *
 * @authenticated
 *
 * @group Credits
 */
class CreditPurchaseController extends Controller
{
    public function __construct(private CreditPurchaseService $creditPurchaseService) {}

    /**
     * List the authenticated user's credit purchases (most recent first).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $this->authorize('viewAny', CreditPurchase::class);
        $user = request()->user();

        $purchases = CreditPurchase::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        return response()->json([
            'data' => CreditPurchaseResource::collection($purchases->items()),
            'meta' => [
                'current_page' => $purchases->currentPage(),
                'last_page' => $purchases->lastPage(),
                'per_page' => $purchases->perPage(),
                'total' => $purchases->total(),
            ],
        ]);
    }

    /**
     * Initiate a credit purchase and create a Stripe checkout session.
     *
     * Returns a checkout URL that the client should redirect to for payment processing.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreCreditPurchaseRequest $request)
    {
        $this->authorize('create', CreditPurchase::class);
        $validated = $request->validated();
        $bundle = CreditBundle::findOrFail($validated['credit_bundle_id']);

        try {
            $result = $this->creditPurchaseService->createCheckoutSession(
                $request->user(),
                $bundle,
                $validated['return_url']
            );

            return response()->json([
                'purchase' => new CreditPurchaseResource($result['purchase']),
                'checkout_url' => $result['checkout_url'],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create credit purchase checkout session', [
                'user_id' => $request->user()->id,
                'bundle_id' => $validated['credit_bundle_id'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => __('credits.created.failure'),
                'error' => 'checkout_creation_failed',
            ], 500);
        }
    }

    /**
     * Retrieve details of a specific credit purchase.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(CreditPurchase $creditPurchase)
    {
        // Authorize that the user owns this purchase
        $this->authorize('view', $creditPurchase);

        return response()->json([
            'purchase' => new CreditPurchaseResource($creditPurchase),
        ]);
    }
}
