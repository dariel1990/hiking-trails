<?php

namespace Database\Seeders;

use App\Models\Town;
use Illuminate\Database\Seeder;

class TownSeeder extends Seeder
{
    /**
     * Seed the Bulkley Valley / Highway 16 towns that get a landing page.
     *
     * Radii are chosen so each town claims the trailheads locals would call
     * theirs without swallowing a neighbour's: Smithers and Telkwa sit only
     * 15 km apart, so both are kept deliberately tight.
     *
     * Colours tint that town's pins on the interactive map. They are
     * mid-saturation on purpose: a 32px dot has to stay distinguishable
     * against both the Outdoors and Satellite basemaps, and dark enough to
     * carry a white icon.
     *
     * @var list<array<string, mixed>>
     */
    private array $towns = [
        [
            'name' => 'Smithers',
            'color' => '#2C5F5D',
            'latitude' => 54.7824,
            'longitude' => -127.1686,
            'radius_km' => 35,
            'map_zoom' => 11,
            'sort_order' => 1,
            'tagline' => 'Alpine hikes, Hudson Bay Mountain and the heart of the Bulkley Valley.',
        ],
        [
            'name' => 'Telkwa',
            'color' => '#0E7490',
            'latitude' => 54.6939,
            'longitude' => -127.0522,
            'radius_km' => 25,
            'map_zoom' => 11,
            'sort_order' => 2,
            'tagline' => 'Riverside trails and quiet lakes at the confluence of the Bulkley and Telkwa.',
        ],
        [
            'name' => 'Houston',
            'color' => '#C2410C',
            'latitude' => 54.3986,
            'longitude' => -126.6470,
            'radius_km' => 40,
            'map_zoom' => 10,
            'sort_order' => 3,
            'tagline' => 'Waterfall tours, forest trails and world-class steelhead water.',
        ],
        [
            'name' => 'Hazelton',
            'color' => '#7E22CE',
            'latitude' => 55.2500,
            'longitude' => -127.5878,
            'radius_km' => 45,
            'map_zoom' => 10,
            'sort_order' => 4,
            'tagline' => 'Trails beneath the Rocher de Boule range, where the Skeena meets the Bulkley.',
        ],
        [
            'name' => 'Burns Lake',
            'color' => '#15803D',
            'latitude' => 54.2286,
            'longitude' => -125.7594,
            'radius_km' => 45,
            'map_zoom' => 10,
            'sort_order' => 5,
            'tagline' => 'Lakes District riding, paddling and hiking on the Highway 16 corridor.',
        ],
        [
            'name' => 'Stewart',
            'color' => '#B91C1C',
            'latitude' => 55.9386,
            'longitude' => -129.9903,
            'radius_km' => 50,
            'map_zoom' => 10,
            'sort_order' => 6,
            'tagline' => 'Glaciers, bear viewing and coastal mountain trails at the head of the Portland Canal.',
        ],
    ];

    public function run(): void
    {
        foreach ($this->towns as $town) {
            Town::updateOrCreate(
                ['slug' => str($town['name'])->slug()->append('-bc')->toString()],
                $town + [
                    'province' => 'British Columbia',
                    'province_code' => 'BC',
                    'is_active' => true,
                ]
            );
        }
    }
}
