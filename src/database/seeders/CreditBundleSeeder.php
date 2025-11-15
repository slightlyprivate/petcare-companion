<?php

namespace Database\Seeders;

use App\Constants\CreditConstants;
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
            ],
            [
                'name' => 'Popular Bundle',
                'credits' => 100,
            ],
            [
                'name' => 'Premium Pack',
                'credits' => 250,
            ],
            [
                'name' => 'Ultimate Bundle',
                'credits' => 500,
            ],
        ];

        foreach ($bundles as $bundle) {
            // Compute price in cents using centralized conversion
            $bundle['price_cents'] = CreditConstants::toCents($bundle['credits']);

            CreditBundle::firstOrCreate(
                ['credits' => $bundle['credits']],
                $bundle
            );
        }
    }
}

