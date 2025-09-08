<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trail;

class TrailSeeder extends Seeder
{
    public function run()
    {
        $trails = [
            [
                'name' => 'Grouse Grind',
                'description' => 'Steep, challenging hike up Grouse Mountain with rewarding city views at the summit.',
                'location' => 'North Vancouver, BC',
                'difficulty_level' => 4.0,
                'distance_km' => 2.9,
                'elevation_gain_m' => 853,
                'estimated_time_hours' => 1.5,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [49.3486, -123.1073],
                'end_coordinates' => [49.3782, -123.0811],
                'route_coordinates' => [
                    [49.3486, -123.1073], // Trailhead parking
                    [49.3520, -123.1050], // First switchback
                    [49.3580, -123.1020], // Quarter mark
                    [49.3650, -123.0950], // Halfway checkpoint
                    [49.3720, -123.0880], // Three-quarter mark
                    [49.3782, -123.0811]  // Grouse Mountain summit
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Summer', 'Fall'],
                'is_featured' => true,
            ],
            [
                'name' => 'Lynn Canyon Loop',
                'description' => 'Scenic forest loop featuring suspension bridge and swimming holes.',
                'location' => 'North Vancouver, BC',
                'difficulty_level' => 2.0,
                'distance_km' => 3.2,
                'elevation_gain_m' => 150,
                'estimated_time_hours' => 2.0,
                'trail_type' => 'loop',
                'start_coordinates' => [49.3430, -123.0198],
                'end_coordinates' => [49.3430, -123.0198],
                'route_coordinates' => [
                    [49.3430, -123.0198], // Visitor center start
                    [49.3445, -123.0180], // Trail junction
                    [49.3460, -123.0165], // Suspension bridge
                    [49.3470, -123.0150], // Upper pools
                    [49.3475, -123.0190], // Loop trail east
                    [49.3450, -123.0210], // Return path
                    [49.3430, -123.0198]  // Back to start
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Summer', 'Fall'],
                'is_featured' => true,
            ],
            [
                'name' => 'Quarry Rock',
                'description' => 'Popular hike to panoramic viewpoint overlooking Deep Cove and Indian Arm.',
                'location' => 'Deep Cove, BC',
                'difficulty_level' => 2.5,
                'distance_km' => 3.8,
                'elevation_gain_m' => 100,
                'estimated_time_hours' => 2.0,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [49.3292, -122.9477],
                'end_coordinates' => [49.3425, -122.9521],
                'route_coordinates' => [
                    [49.3292, -122.9477], // Deep Cove village
                    [49.3310, -122.9490], // Trail entrance
                    [49.3350, -122.9510], // Forest section
                    [49.3380, -122.9515], // Steep section
                    [49.3410, -122.9518], // Ridge approach
                    [49.3425, -122.9521]  // Quarry Rock viewpoint
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Summer', 'Fall'],
                'is_featured' => true,
            ],
            [
                'name' => 'Chief Peak First Peak',
                'description' => 'Iconic granite monolith climb with spectacular Howe Sound views.',
                'location' => 'Squamish, BC',
                'difficulty_level' => 4.0,
                'distance_km' => 7.0,
                'elevation_gain_m' => 540,
                'estimated_time_hours' => 4.0,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [49.6823, -123.1456],
                'end_coordinates' => [49.6934, -123.1567],
                'route_coordinates' => [
                    [49.6823, -123.1456], // Stawamus Chief parking
                    [49.6840, -123.1470], // Trail start
                    [49.6860, -123.1490], // Forested switchbacks
                    [49.6880, -123.1520], // Chain section start
                    [49.6900, -123.1540], // Granite slabs
                    [49.6920, -123.1555], // Final ascent
                    [49.6934, -123.1567]  // First Peak summit
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Summer', 'Fall'],
                'is_featured' => true,
            ],
            [
                'name' => 'Lighthouse Park Loop',
                'description' => 'Coastal forest trail to historic lighthouse with ocean views.',
                'location' => 'West Vancouver, BC',
                'difficulty_level' => 2.0,
                'distance_km' => 2.5,
                'elevation_gain_m' => 80,
                'estimated_time_hours' => 1.5,
                'trail_type' => 'loop',
                'start_coordinates' => [49.3327, -123.2619],
                'end_coordinates' => [49.3327, -123.2619],
                'route_coordinates' => [
                    [49.3327, -123.2619], // Parking area
                    [49.3335, -123.2635], // Trail junction
                    [49.3340, -123.2650], // Lighthouse approach
                    [49.3345, -123.2654], // Point Atkinson Lighthouse
                    [49.3350, -123.2640], // Coastal viewpoints
                    [49.3340, -123.2620], // Return trail
                    [49.3327, -123.2619]  // Back to parking
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Summer', 'Fall', 'Winter'],
                'is_featured' => false,
            ],
            [
                'name' => 'Tunnel Bluffs',
                'description' => 'Spectacular viewpoint hike overlooking Howe Sound and Sea to Sky Highway.',
                'location' => 'Lions Bay, BC',
                'difficulty_level' => 3.5,
                'distance_km' => 5.6,
                'elevation_gain_m' => 460,
                'estimated_time_hours' => 3.0,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [49.4567, -123.2345],
                'end_coordinates' => [49.4678, -123.2456],
                'route_coordinates' => [
                    [49.4567, -123.2345], // Lions Bay village
                    [49.4580, -123.2360], // Residential area
                    [49.4610, -123.2380], // Forest entry
                    [49.4635, -123.2410], // Switchback section
                    [49.4660, -123.2435], // Steep climb
                    [49.4678, -123.2456]  // Tunnel Bluffs viewpoint
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Summer', 'Fall'],
                'is_featured' => false,
            ],
            [
                'name' => 'Capilano River Regional Park',
                'description' => 'Easy riverside walk through old-growth forest along Capilano River.',
                'location' => 'North Vancouver, BC',
                'difficulty_level' => 1.5,
                'distance_km' => 6.0,
                'elevation_gain_m' => 50,
                'estimated_time_hours' => 2.5,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [49.3421, -123.1156],
                'end_coordinates' => [49.3756, -123.1234],
                'route_coordinates' => [
                    [49.3421, -123.1156], // Capilano River parking
                    [49.3450, -123.1170], // Riverside trail
                    [49.3480, -123.1185], // First bridge crossing
                    [49.3520, -123.1200], // Deep forest section
                    [49.3580, -123.1210], // Salmon pools
                    [49.3650, -123.1220], // Upper canyon
                    [49.3756, -123.1234]  // Cleveland Dam
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Summer', 'Fall', 'Winter'],
                'is_featured' => false,
            ],
            [
                'name' => 'Rice Lake Loop',
                'description' => 'Flat, accessible trail around peaceful mountain lake.',
                'location' => 'North Vancouver, BC',
                'difficulty_level' => 1.0,
                'distance_km' => 2.4,
                'elevation_gain_m' => 20,
                'estimated_time_hours' => 1.0,
                'trail_type' => 'loop',
                'start_coordinates' => [49.3789, -123.0456],
                'end_coordinates' => [49.3789, -123.0456],
                'route_coordinates' => [
                    [49.3789, -123.0456], // Rice Lake parking
                    [49.3795, -123.0470], // Boardwalk start
                    [49.3805, -123.0485], // East shore
                    [49.3810, -123.0500], // Far end of lake
                    [49.3800, -123.0515], // West shore
                    [49.3790, -123.0500], // Wetland area
                    [49.3789, -123.0456]  // Back to start
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Summer', 'Fall', 'Winter'],
                'is_featured' => false,
            ],
            [
                'name' => 'Mount Seymour First Pump Peak',
                'description' => 'Alpine hike with 360-degree views of mountains and city.',
                'location' => 'North Vancouver, BC',
                'difficulty_level' => 3.0,
                'distance_km' => 4.5,
                'elevation_gain_m' => 350,
                'estimated_time_hours' => 2.5,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [49.3678, -122.9456],
                'end_coordinates' => [49.3834, -122.9678],
                'route_coordinates' => [
                    [49.3678, -122.9456], // Mount Seymour parking
                    [49.3700, -122.9480], // Ski area base
                    [49.3740, -122.9520], // Forest trail
                    [49.3780, -122.9580], // Boulder field
                    [49.3810, -122.9630], // Alpine zone
                    [49.3834, -122.9678]  // First Pump Peak
                ],
                'status' => 'active',
                'best_seasons' => ['Summer', 'Fall'],
                'is_featured' => false,
            ],
            [
                'name' => 'Joffre Lakes Lower Lake',
                'description' => 'Stunning turquoise alpine lake with glacier views.',
                'location' => 'Pemberton, BC',
                'difficulty_level' => 3.0,
                'distance_km' => 2.4,
                'elevation_gain_m' => 90,
                'estimated_time_hours' => 1.5,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [50.3723, -122.9654],
                'end_coordinates' => [50.3820, -122.9750],
                'route_coordinates' => [
                    [50.3723, -122.9654], // Joffre Lakes parking
                    [50.3740, -122.9670], // Trail start
                    [50.3760, -122.9690], // Creek crossing
                    [50.3780, -122.9710], // Forested section
                    [50.3800, -122.9730], // Lake approach
                    [50.3820, -122.9750]  // Lower Joffre Lake
                ],
                'status' => 'active',
                'best_seasons' => ['Summer', 'Fall'],
                'is_featured' => true,
            ],
        ];

        foreach ($trails as $trail) {
            Trail::create($trail);
        }
    }
}