<?php

namespace Database\Seeders;

use App\Models\Business;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BusinessSeeder extends Seeder
{
    public function run(): void
    {
        $businesses = [

            // ── Dining ───────────────────────────────────────────────────────
            [
                'name' => "Louise's Kitchen",
                'business_type' => 'restaurant',
                'tagline' => 'Your local spot for homestyle meals',
                'description' => "Family-owned and serving scratch-made breakfast and lunch favorites. Louise's Kitchen is your local spot for homestyle meals.",
                'address' => 'Smithers, BC',
                'latitude' => 54.7822,
                'longitude' => -127.1715,
                'facebook_url' => 'https://www.facebook.com/LouisesKitchenSmithers',
                'is_featured' => true,
            ],
            [
                'name' => 'Alpenhorn Bistro & Bar',
                'business_type' => 'bar',
                'tagline' => 'Where friends meet in a casual dining and pub environment',
                'description' => 'A place as unique as the people behind it where friends meet in a casual dining and pub environment.',
                'address' => 'Smithers, BC',
                'latitude' => 54.7810,
                'longitude' => -127.1702,
                'facebook_url' => 'https://www.facebook.com/AlpenhornBistro',
                'is_featured' => true,
            ],
            [
                'name' => 'WILDFIRE Restaurant',
                'business_type' => 'restaurant',
                'tagline' => 'House-made comfort food that warms your heart',
                'description' => 'Discover the awesome Smithers restaurant serving house-made comfort food that warms your heart and satisfies your cravings.',
                'address' => 'Smithers, BC',
                'latitude' => 54.7798,
                'longitude' => -127.1690,
                'facebook_url' => 'https://www.facebook.com/WildfireRestaurantSmithers',
                'is_featured' => true,
            ],
            [
                'name' => 'Blue Water Sushi',
                'business_type' => 'restaurant',
                'tagline' => 'Known as one of the best places to eat in town',
                'description' => 'Welcome to Blue Water Sushi, your go-to restaurant in Smithers, BC. Known as one of the best places to eat in town.',
                'address' => 'Smithers, BC',
                'latitude' => 54.7805,
                'longitude' => -127.1680,
                'facebook_url' => 'https://www.facebook.com/BlueWaterSushiSmithers',
                'is_featured' => true,
            ],

            // ── Accommodations ───────────────────────────────────────────────
            [
                'name' => 'Rocky Ridge Resort',
                'business_type' => 'accommodation',
                'tagline' => 'Rustic charm meets modern convenience',
                'description' => 'Escape to comfort at Rocky Ridge Resort, where rustic charm meets modern convenience.',
                'address' => 'Smithers, BC',
                'latitude' => 54.7750,
                'longitude' => -127.1850,
                'facebook_url' => 'https://www.facebook.com/RockyRidgeResortSmithers',
                'is_featured' => true,
            ],
            [
                'name' => 'Mainerz Air BnB',
                'business_type' => 'accommodation',
                'tagline' => 'Cozy downtown stays in Smithers, BC',
                'description' => 'Shop & Stay with Mainerz! Discover ladies\' fashion, home decor, and cozy downtown stays in Smithers, BC.',
                'address' => 'Smithers, BC',
                'latitude' => 54.7818,
                'longitude' => -127.1708,
                'facebook_url' => 'https://www.facebook.com/MainerzBoutique',
                'is_featured' => false,
            ],
            [
                'name' => 'Stork Nest Inn',
                'business_type' => 'accommodation',
                'tagline' => 'Family boutique inn minutes from downtown Smithers',
                'description' => 'We are a family owned and operated boutique inn only minutes walking distance from downtown Smithers BC.',
                'address' => 'Smithers, BC',
                'latitude' => 54.7830,
                'longitude' => -127.1725,
                'facebook_url' => 'https://www.facebook.com/StorkNestInn',
                'is_featured' => true,
            ],
            [
                'name' => 'Smithers Mountain Domes',
                'business_type' => 'accommodation',
                'tagline' => 'We love the beauty that nature has to offer',
                'description' => 'We love the beauty that nature has to offer here and are so excited to share it with others.',
                'address' => 'Smithers, BC',
                'latitude' => 54.7680,
                'longitude' => -127.2050,
                'is_featured' => true,
            ],
            [
                'name' => 'Lake Drop Inn',
                'business_type' => 'accommodation',
                'tagline' => 'Where luxury meets romance in the Murphy Suite',
                'description' => 'Escape to the enchanting Murphy Suite, where luxury meets romance. Wrap yourself in plush robes and unwind by the elegant wall-insert fireplace.',
                'address' => 'Smithers, BC',
                'latitude' => 54.7720,
                'longitude' => -127.1920,
                'is_featured' => false,
            ],
            [
                'name' => 'DEADWOODGONE',
                'business_type' => 'other',
                'tagline' => 'Farm to table. Quality and sustainability.',
                'description' => 'We are a small family business! Passion driven. Sovereign spirits. Farm to table. Quality and sustainability. A three year plan brought forward to fruition.',
                'address' => 'Smithers, BC',
                'latitude' => 54.7760,
                'longitude' => -127.1780,
                'is_featured' => false,
            ],

            // ── Shopping ─────────────────────────────────────────────────────
            [
                'name' => 'Mainerz Boutique',
                'business_type' => 'retail',
                'tagline' => 'Ladies\' fashion, home decor & cozy downtown stays',
                'description' => 'Shop & Stay with Mainerz! Discover ladies\' fashion, home decor, and cozy downtown stays in Smithers, BC.',
                'address' => 'Smithers, BC',
                'latitude' => 54.7817,
                'longitude' => -127.1706,
                'facebook_url' => 'https://www.facebook.com/MainerzBoutique',
                'is_featured' => true,
            ],
            [
                'name' => 'Salt Boutique',
                'business_type' => 'retail',
                'tagline' => 'Exceptional design thoughtfully chosen just for you',
                'description' => 'Surround yourself with beauty and explore the passion of exceptional design thoughtfully chosen just for you.',
                'address' => 'Smithers, BC',
                'latitude' => 54.7808,
                'longitude' => -127.1695,
                'facebook_url' => 'https://www.facebook.com/SaltBoutiqueSmithers',
                'is_featured' => true,
            ],
            [
                'name' => 'Out of Hand',
                'business_type' => 'retail',
                'tagline' => 'Beautiful · Local · Delicious',
                'description' => 'Local food and handmade goods from Northern BC.',
                'address' => 'Smithers, BC',
                'latitude' => 54.7813,
                'longitude' => -127.1700,
                'facebook_url' => 'https://www.facebook.com/OutOfHandSmithers',
                'is_featured' => false,
            ],
        ];

        foreach ($businesses as $data) {
            Business::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                array_merge($data, [
                    'slug' => Str::slug($data['name']),
                    'is_active' => true,
                    'is_featured' => $data['is_featured'] ?? false,
                    'price_range' => null,
                    'is_seasonal' => false,
                ])
            );
        }

        $this->command->info('✓ Seeded '.count($businesses).' businesses from Xplore Smithers partners.');
    }
}
