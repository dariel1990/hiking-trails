<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FirstNationFacilitySeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            [
                'name' => 'Witset Canyon (Moricetown)',
                'description' => "Traditional Witsuwit'en fishing site on the Bulkley River where salmon have been harvested for thousands of years. Watch traditional dip-net fishing during the salmon run.",
                'latitude' => 55.0165000,
                'longitude' => -127.3330000,
            ],
            [
                'name' => 'Kyah Wiget Village Site',
                'description' => "One of the oldest continuously inhabited village sites in British Columbia, home of the Witsuwit'en people. Features totem poles and interpretive displays.",
                'latitude' => 55.0210000,
                'longitude' => -127.3390000,
            ],
            [
                'name' => "Office of the Wet'suwet'en",
                'description' => "Cultural and administrative centre of the Wet'suwet'en Nation, offering information about hereditary governance, territory, and cultural programs.",
                'latitude' => 54.7805000,
                'longitude' => -127.1690000,
            ],
            [
                'name' => 'Hagwilget Canyon Viewpoint',
                'description' => 'Dramatic canyon crossing on the Bulkley River at the Gitxsan village of Hagwilget, with views of the historic suspension bridge and traditional fishing sites.',
                'latitude' => 55.2560000,
                'longitude' => -127.5910000,
            ],
            [
                'name' => 'Driftwood Canyon Interpretive Site',
                'description' => "Interpretive site within Witsuwit'en territory sharing the cultural and natural history of the Driftwood Canyon area.",
                'latitude' => 54.8590000,
                'longitude' => -127.0350000,
            ],
        ];

        foreach ($facilities as $facility) {
            Facility::updateOrCreate(
                ['name' => $facility['name'], 'facility_type' => 'first_nation'],
                [
                    ...$facility,
                    'facility_type' => 'first_nation',
                    'icon' => '🪶',
                    'is_active' => true,
                ]
            );
        }
    }
}
