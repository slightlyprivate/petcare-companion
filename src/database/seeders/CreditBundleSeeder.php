<?php

namespace Database\Seeders;

use App\Models\CreditBundle;
use Illuminate\Database\Seeder;

/**
 * Seeder class for populating the credit_bundles table with default bundles.
 */
class CreditBundleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bundles = [
            [
                'name' => 'Starter Pack',
                'credits' => 50,
                'price_cents' => 1000, // $10.00 = 10 * 5 credits per dollar
            ],
            [
                'name' => 'Popular Bundle',
                'credits' => 100,
                'price_cents' => 2000, // $20.00
            ],
            [
                'name' => 'Premium Pack',
                'credits' => 250,
                'price_cents' => 5000, // $50.00
            ],
            [
                'name' => 'Ultimate Bundle',
                'credits' => 500,
                'price_cents' => 10000, // $100.00
            ],
        ];

        foreach ($bundles as $bundle) {
            CreditBundle::firstOrCreate(
                ['credits' => $bundle['credits']],
                $bundle
            );
        }
    }
}
