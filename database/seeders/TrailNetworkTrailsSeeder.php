<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trail;
use App\Models\TrailNetwork;

class TrailNetworkTrailsSeeder extends Seeder
{
    public function run()
    {
        // Create or get the trail networks
        $nordicCentre = TrailNetwork::firstOrCreate(
            ['slug' => 'bulkley-valley-nordic-centre'],
            [
                'network_name' => 'Bulkley Valley Nordic Centre',
                'type' => 'nordic_skiing',
                'latitude' => 54.8000,
                'longitude' => -127.2000,
                'address' => 'Smithers, BC',
                'website_url' => 'https://bvnordic.ca',
                'is_always_visible' => true,
                'description' => 'Cross-country ski trail network with multiple loops and difficulty levels.'
            ]
        );

        $skiHill = TrailNetwork::firstOrCreate(
            ['slug' => 'hudson-bay-mountain-ski-ride-smithers'],
            [
                'network_name' => 'Hudson Bay Mountain - Ski & Ride Smithers',
                'type' => 'downhill_skiing',
                'latitude' => 54.7667,
                'longitude' => -127.3167,
                'address' => 'Smithers, BC',
                'website_url' => 'https://hudsonbay.ski',
                'is_always_visible' => true,
                'description' => 'Downhill ski resort with varied terrain for all skill levels.'
            ]
        );

        $this->command->info('Trail networks ready: ' . $nordicCentre->network_name . ' and ' . $skiHill->network_name);

        // Delete existing trails for these networks to avoid duplicates
        Trail::where('trail_network_id', $nordicCentre->id)->delete();
        Trail::where('trail_network_id', $skiHill->id)->delete();

        // Nordic Centre Trails (from the map PDF)
        $nordicTrails = [
            [
                'name' => 'Upper Loring Trail',
                'description' => 'A scenic upper loop trail offering beautiful views of the Bulkley Valley. Perfect for intermediate skiers looking for a longer route.',
                'location' => 'Bulkley Valley Nordic Centre, Smithers, BC',
                'difficulty_level' => 3,
                'distance_km' => 2.8,
                'elevation_gain_m' => 120,
                'estimated_time_hours' => 1.5,
                'trail_type' => 'loop',
                'status' => 'active',
                'start_coordinates' => [54.8000, -127.2000],
                'route_coordinates' => [
                    [54.8000, -127.2000],
                    [54.8010, -127.1995],
                    [54.8020, -127.1985],
                    [54.8025, -127.1970],
                    [54.8022, -127.1955],
                    [54.8015, -127.1945],
                    [54.8005, -127.1950],
                    [54.7995, -127.1960],
                    [54.7990, -127.1975],
                    [54.7992, -127.1990],
                    [54.8000, -127.2000],
                ],
                'data_source' => 'manual',
            ],
            [
                'name' => 'Valley View',
                'description' => 'An intermediate trail with spectacular valley vistas. Features gentle climbs and fast descents through mixed forest.',
                'location' => 'Bulkley Valley Nordic Centre, Smithers, BC',
                'difficulty_level' => 3,
                'distance_km' => 1.9,
                'elevation_gain_m' => 85,
                'estimated_time_hours' => 1.0,
                'trail_type' => 'loop',
                'status' => 'active',
                'start_coordinates' => [54.7995, -127.1995],
                'route_coordinates' => [
                    [54.7995, -127.1995],
                    [54.8000, -127.1985],
                    [54.8005, -127.1975],
                    [54.8008, -127.1965],
                    [54.8005, -127.1955],
                    [54.7998, -127.1960],
                    [54.7992, -127.1970],
                    [54.7990, -127.1980],
                    [54.7995, -127.1995],
                ],
                'data_source' => 'manual',
            ],
            [
                'name' => 'Perimeter Loop',
                'description' => 'The main perimeter trail circling the Nordic Centre. Great for beginners with well-groomed tracks.',
                'location' => 'Bulkley Valley Nordic Centre, Smithers, BC',
                'difficulty_level' => 2,
                'distance_km' => 3.2,
                'elevation_gain_m' => 65,
                'estimated_time_hours' => 1.5,
                'trail_type' => 'loop',
                'status' => 'active',
                'start_coordinates' => [54.8005, -127.2005],
                'route_coordinates' => [
                    [54.8005, -127.2005],
                    [54.8015, -127.2000],
                    [54.8025, -127.1990],
                    [54.8030, -127.1975],
                    [54.8028, -127.1960],
                    [54.8020, -127.1950],
                    [54.8010, -127.1945],
                    [54.7998, -127.1950],
                    [54.7988, -127.1960],
                    [54.7980, -127.1975],
                    [54.7978, -127.1990],
                    [54.7985, -127.2005],
                    [54.7995, -127.2010],
                    [54.8005, -127.2005],
                ],
                'data_source' => 'manual',
            ],
            [
                'name' => 'Demo Forest Loop',
                'description' => 'Educational loop through demonstration forest showcasing sustainable forestry practices. Easy terrain suitable for all levels.',
                'location' => 'Bulkley Valley Nordic Centre, Smithers, BC',
                'difficulty_level' => 2,
                'distance_km' => 1.7,
                'elevation_gain_m' => 45,
                'estimated_time_hours' => 0.75,
                'trail_type' => 'loop',
                'status' => 'active',
                'start_coordinates' => [54.7990, -127.2010],
                'route_coordinates' => [
                    [54.7990, -127.2010],
                    [54.7995, -127.2005],
                    [54.8000, -127.1998],
                    [54.8003, -127.1990],
                    [54.8000, -127.1982],
                    [54.7993, -127.1985],
                    [54.7987, -127.1993],
                    [54.7985, -127.2003],
                    [54.7990, -127.2010],
                ],
                'data_source' => 'manual',
            ],
            [
                'name' => 'Wetzin\'kwa Trail',
                'description' => 'A challenging trail leading to the Wetzin\'kwa area. Features steep climbs and technical sections for experienced skiers.',
                'location' => 'Bulkley Valley Nordic Centre, Smithers, BC',
                'difficulty_level' => 4,
                'distance_km' => 2.6,
                'elevation_gain_m' => 175,
                'estimated_time_hours' => 2.0,
                'trail_type' => 'point-to-point',
                'status' => 'active',
                'start_coordinates' => [54.7985, -127.1990],
                'route_coordinates' => [
                    [54.7985, -127.1990],
                    [54.7980, -127.1980],
                    [54.7972, -127.1968],
                    [54.7965, -127.1955],
                    [54.7958, -127.1940],
                    [54.7950, -127.1925],
                    [54.7945, -127.1910],
                    [54.7940, -127.1895],
                    [54.7935, -127.1880],
                ],
                'data_source' => 'manual',
            ],
            [
                'name' => 'Hound Heaven',
                'description' => 'Dog-friendly designated trail with wide tracks. Perfect for skiing with your four-legged companions.',
                'location' => 'Bulkley Valley Nordic Centre, Smithers, BC',
                'difficulty_level' => 2,
                'distance_km' => 1.5,
                'elevation_gain_m' => 35,
                'estimated_time_hours' => 0.75,
                'trail_type' => 'loop',
                'status' => 'active',
                'start_coordinates' => [54.8010, -127.2015],
                'route_coordinates' => [
                    [54.8010, -127.2015],
                    [54.8015, -127.2010],
                    [54.8018, -127.2003],
                    [54.8020, -127.1995],
                    [54.8018, -127.1988],
                    [54.8013, -127.1985],
                    [54.8008, -127.1990],
                    [54.8005, -127.2000],
                    [54.8007, -127.2010],
                    [54.8010, -127.2015],
                ],
                'data_source' => 'manual',
            ],
            [
                'name' => 'Pine Mid Loop',
                'description' => 'Mid-level loop through dense pine forest. Classic-only sections with beautiful winter scenery.',
                'location' => 'Bulkley Valley Nordic Centre, Smithers, BC',
                'difficulty_level' => 3,
                'distance_km' => 2.4,
                'elevation_gain_m' => 95,
                'estimated_time_hours' => 1.25,
                'trail_type' => 'loop',
                'status' => 'active',
                'start_coordinates' => [54.7992, -127.2008],
                'route_coordinates' => [
                    [54.7992, -127.2008],
                    [54.7998, -127.2000],
                    [54.8005, -127.1993],
                    [54.8012, -127.1985],
                    [54.8015, -127.1975],
                    [54.8012, -127.1965],
                    [54.8005, -127.1962],
                    [54.7997, -127.1967],
                    [54.7990, -127.1975],
                    [54.7987, -127.1985],
                    [54.7988, -127.1995],
                    [54.7992, -127.2008],
                ],
                'data_source' => 'manual',
            ],
        ];

        // Ski Hill Trails (from the map image and Google Maps link)
        $skiHillTrails = [
            [
                'name' => 'Ptarmigan',
                'description' => 'Main intermediate run from the summit. Well-groomed with consistent pitch, perfect for cruising.',
                'location' => 'Hudson Bay Mountain, Smithers, BC',
                'difficulty_level' => 3,
                'distance_km' => 2.8,
                'elevation_gain_m' => 450,
                'estimated_time_hours' => 0.5,
                'trail_type' => 'point-to-point',
                'status' => 'seasonal',
                'start_coordinates' => [54.7667, -127.3167],
                'route_coordinates' => [
                    [54.7667, -127.3167],
                    [54.7665, -127.3160],
                    [54.7662, -127.3152],
                    [54.7658, -127.3145],
                    [54.7653, -127.3138],
                    [54.7648, -127.3132],
                    [54.7642, -127.3127],
                    [54.7635, -127.3123],
                    [54.7628, -127.3120],
                ],
                'data_source' => 'manual',
            ],
            [
                'name' => 'Chance',
                'description' => 'Challenging black diamond run with steep sections and moguls. For advanced skiers only.',
                'location' => 'Hudson Bay Mountain, Smithers, BC',
                'difficulty_level' => 4,
                'distance_km' => 1.9,
                'elevation_gain_m' => 380,
                'estimated_time_hours' => 0.4,
                'trail_type' => 'point-to-point',
                'status' => 'seasonal',
                'start_coordinates' => [54.7665, -127.3165],
                'route_coordinates' => [
                    [54.7665, -127.3165],
                    [54.7662, -127.3162],
                    [54.7658, -127.3158],
                    [54.7653, -127.3155],
                    [54.7647, -127.3152],
                    [54.7640, -127.3150],
                    [54.7633, -127.3149],
                ],
                'data_source' => 'manual',
            ],
            [
                'name' => 'Whorefrost',
                'description' => 'Intermediate gladed run through beautiful tree skiing. Watch for powder stashes on snowy days.',
                'location' => 'Hudson Bay Mountain, Smithers, BC',
                'difficulty_level' => 3,
                'distance_km' => 2.2,
                'elevation_gain_m' => 420,
                'estimated_time_hours' => 0.45,
                'trail_type' => 'point-to-point',
                'status' => 'seasonal',
                'start_coordinates' => [54.7670, -127.3170],
                'route_coordinates' => [
                    [54.7670, -127.3170],
                    [54.7667, -127.3165],
                    [54.7663, -127.3159],
                    [54.7658, -127.3153],
                    [54.7652, -127.3148],
                    [54.7646, -127.3144],
                    [54.7639, -127.3141],
                    [54.7632, -127.3139],
                ],
                'data_source' => 'manual',
            ],
            [
                'name' => 'Dazzle Drop',
                'description' => 'Beginner-friendly green run with gentle slopes. Great for learning and building confidence.',
                'location' => 'Hudson Bay Mountain, Smithers, BC',
                'difficulty_level' => 2,
                'distance_km' => 3.1,
                'elevation_gain_m' => 350,
                'estimated_time_hours' => 0.6,
                'trail_type' => 'point-to-point',
                'status' => 'seasonal',
                'start_coordinates' => [54.7672, -127.3172],
                'route_coordinates' => [
                    [54.7672, -127.3172],
                    [54.7670, -127.3168],
                    [54.7667, -127.3163],
                    [54.7664, -127.3157],
                    [54.7660, -127.3151],
                    [54.7656, -127.3145],
                    [54.7651, -127.3140],
                    [54.7646, -127.3136],
                    [54.7640, -127.3133],
                    [54.7634, -127.3131],
                    [54.7628, -127.3130],
                ],
                'data_source' => 'manual',
            ],
            [
                'name' => 'Gold Smoker',
                'description' => 'Intermediate run with varied terrain and natural features. Popular for its fun flow and rhythm.',
                'location' => 'Hudson Bay Mountain, Smithers, BC',
                'difficulty_level' => 3,
                'distance_km' => 2.5,
                'elevation_gain_m' => 405,
                'estimated_time_hours' => 0.5,
                'trail_type' => 'point-to-point',
                'status' => 'seasonal',
                'start_coordinates' => [54.7668, -127.3168],
                'route_coordinates' => [
                    [54.7668, -127.3168],
                    [54.7665, -127.3163],
                    [54.7661, -127.3157],
                    [54.7656, -127.3152],
                    [54.7651, -127.3147],
                    [54.7645, -127.3143],
                    [54.7639, -127.3140],
                    [54.7632, -127.3138],
                ],
                'data_source' => 'manual',
            ],
            [
                'name' => 'Wilma Triple Chair Line',
                'description' => 'Main corridor under the Wilma Triple chair. Wide intermediate terrain suitable for all levels.',
                'location' => 'Hudson Bay Mountain, Smithers, BC',
                'difficulty_level' => 2,
                'distance_km' => 2.0,
                'elevation_gain_m' => 320,
                'estimated_time_hours' => 0.4,
                'trail_type' => 'point-to-point',
                'status' => 'seasonal',
                'start_coordinates' => [54.7669, -127.3169],
                'route_coordinates' => [
                    [54.7669, -127.3169],
                    [54.7666, -127.3165],
                    [54.7663, -127.3160],
                    [54.7659, -127.3155],
                    [54.7654, -127.3150],
                    [54.7649, -127.3146],
                    [54.7643, -127.3143],
                    [54.7637, -127.3141],
                ],
                'data_source' => 'manual',
            ],
        ];

        // Create Nordic Centre trails
        $this->command->info('Creating Nordic Centre trails...');
        foreach ($nordicTrails as $trailData) {
            $trailData['trail_network_id'] = $nordicCentre->id;
            Trail::create($trailData);
        }

        // Create Ski Hill trails
        $this->command->info('Creating Ski Hill trails...');
        foreach ($skiHillTrails as $trailData) {
            $trailData['trail_network_id'] = $skiHill->id;
            Trail::create($trailData);
        }

        $this->command->info('Created ' . count($nordicTrails) . ' Nordic Centre trails');
        $this->command->info('Created ' . count($skiHillTrails) . ' Ski Hill trails');
        $this->command->info('Total: ' . (count($nordicTrails) + count($skiHillTrails)) . ' trails created');
    }
}