<?php

namespace Database\Seeders;

use App\Models\GiftType;
use Illuminate\Database\Seeder;

class GiftTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $giftTypes = [
            [
                'name' => 'Toy',
                'description' => 'Fun toys and playthings for your pet',
                'icon_emoji' => '🧸',
                'color_code' => '#FF6B6B',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Treat',
                'description' => 'Delicious snacks and treats',
                'icon_emoji' => '🍖',
                'color_code' => '#FFA500',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Accessory',
                'description' => 'Collars, leashes, and other accessories',
                'icon_emoji' => '🎀',
                'color_code' => '#FF69B4',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Grooming',
                'description' => 'Grooming supplies and care products',
                'icon_emoji' => '🛁',
                'color_code' => '#87CEEB',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Bedding',
                'description' => 'Comfortable beds and blankets',
                'icon_emoji' => '🛏️',
                'color_code' => '#DAA520',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Entertainment',
                'description' => 'Games and entertainment for mental stimulation',
                'icon_emoji' => '🎮',
                'color_code' => '#9370DB',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Health',
                'description' => 'Health supplements and wellness products',
                'icon_emoji' => '💊',
                'color_code' => '#50C878',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Other',
                'description' => 'Other gifts and surprises',
                'icon_emoji' => '🎁',
                'color_code' => '#FFD700',
                'sort_order' => 8,
                'is_active' => true,
            ],
        ];

        foreach ($giftTypes as $type) {
            GiftType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
