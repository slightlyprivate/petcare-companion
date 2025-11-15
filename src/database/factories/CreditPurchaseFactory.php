<?php

namespace Database\Factories;

use App\Models\CreditBundle;
use App\Models\CreditPurchase;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CreditPurchase>
 */
class CreditPurchaseFactory extends Factory
{
    protected $model = CreditPurchase::class;

    public function definition(): array
    {
        /** @var User $user */
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $bundle = CreditBundle::factory()->create();

        return [
            'id' => Str::uuid()->toString(),
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'credit_bundle_id' => $bundle->id,
            'credits' => $bundle->credits,
            'amount_cents' => $bundle->price_cents,
            'stripe_session_id' => null,
            'stripe_charge_id' => null,
            'status' => 'pending',
            'completed_at' => null,
        ];
    }
}
