<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trail;
use App\Models\ActivityType;

class ConsolidatedTrailSeeder extends Seeder
{
    public function run(): void
    {
        echo "Starting trail seeding...\n";
        
        // Get all activity types
        $hiking = ActivityType::where('slug', 'hiking')->first();
        $fishing = ActivityType::where('slug', 'fishing')->first();
        $camping = ActivityType::where('slug', 'camping')->first();
        $viewpoint = ActivityType::where('slug', 'viewpoint')->first();
        $snowshoeing = ActivityType::where('slug', 'snowshoeing')->first();
        $iceFishing = ActivityType::where('slug', 'ice-fishing')->first();
        $crossCountry = ActivityType::where('slug', 'cross-country-skiing')->first();
        $downhill = ActivityType::where('slug', 'downhill-skiing')->first();

        $trails = [
            // === SUMMER HIKING TRAILS (20 trails) ===
            [
                'trail' => [
                    'name' => 'Angels Landing Trail',
                    'description' => 'One of the most famous and thrilling hikes in America, featuring chains for the final ascent to spectacular panoramic views of Zion Canyon.',
                    'location' => 'Springdale, Utah',
                    'difficulty_level' => 5.0,
                    'distance_km' => 8.7,
                    'elevation_gain_m' => 453,
                    'estimated_time_hours' => 4.0,
                    'trail_type' => 'out-and-back',
                    'start_coordinates' => [37.26910, -112.94705],
                    'end_coordinates' => [37.26904, -112.94765],
                    'route_coordinates' => [
                        [37.26910, -112.94705],
                        [37.26850, -112.94750],
                        [37.26904, -112.94765]
                    ],
                    'status' => 'active',
                    'best_seasons' => ['Spring', 'Summer', 'Fall'],
                    'is_featured' => true,
                ],
                'activities' => [$hiking, $viewpoint]
            ],
            [
                'trail' => [
                    'name' => 'Half Dome Trail',
                    'description' => 'Yosemite\'s most iconic hike featuring cables for the final ascent up the granite face.',
                    'location' => 'Yosemite Valley, California',
                    'difficulty_level' => 5.0,
                    'distance_km' => 22.5,
                    'elevation_gain_m' => 1463,
                    'estimated_time_hours' => 12.0,
                    'trail_type' => 'out-and-back',
                    'start_coordinates' => [37.73123, -119.55456],
                    'end_coordinates' => [37.74612, -119.53234],
                    'route_coordinates' => [
                        [37.73123, -119.55456],
                        [37.73500, -119.53000],
                        [37.74612, -119.53234]
                    ],
                    'status' => 'active',
                    'best_seasons' => ['Summer', 'Fall'],
                    'is_featured' => true,
                ],
                'activities' => [$hiking, $viewpoint]
            ],
            [
                'trail' => [
                    'name' => 'Old Rag Mountain Loop',
                    'description' => 'Virginia\'s most popular hike featuring a thrilling rock scramble to panoramic summit views.',
                    'location' => 'Syria, Virginia',
                    'difficulty_level' => 5.0,
                    'distance_km' => 14.5,
                    'elevation_gain_m' => 785,
                    'estimated_time_hours' => 7.0,
                    'trail_type' => 'loop',
                    'start_coordinates' => [38.55156, -78.29245],
                    'end_coordinates' => [38.55156, -78.29245],
                    'route_coordinates' => [
                        [38.55156, -78.29245],
                        [38.56789, -78.30456],
                        [38.55156, -78.29245]
                    ],
                    'status' => 'active',
                    'best_seasons' => ['Spring', 'Fall'],
                    'is_featured' => true,
                ],
                'activities' => [$hiking, $viewpoint, $camping]
            ],
            [
                'trail' => [
                    'name' => 'Grouse Grind',
                    'description' => 'Steep, challenging hike up Grouse Mountain with rewarding city views.',
                    'location' => 'North Vancouver, BC',
                    'difficulty_level' => 4.0,
                    'distance_km' => 2.9,
                    'elevation_gain_m' => 853,
                    'estimated_time_hours' => 1.5,
                    'trail_type' => 'out-and-back',
                    'start_coordinates' => [49.3486, -123.1073],
                    'end_coordinates' => [49.3782, -123.0811],
                    'route_coordinates' => [
                        [49.3486, -123.1073],
                        [49.3650, -123.0950],
                        [49.3782, -123.0811]
                    ],
                    'status' => 'active',
                    'best_seasons' => ['Spring', 'Summer', 'Fall'],
                    'is_featured' => true,
                ],
                'activities' => [$hiking, $viewpoint]
            ],
            [
                'trail' => [
                    'name' => 'Emerald Lake Trail',
                    'description' => 'Beautiful alpine lake trail featuring three pristine lakes with stunning mountain reflections.',
                    'location' => 'Estes Park, Colorado',
                    'difficulty_level' => 3.0,
                    'distance_km' => 5.6,
                    'elevation_gain_m' => 200,
                    'estimated_time_hours' => 3.0,
                    'trail_type' => 'out-and-back',
                    'start_coordinates' => [40.31089, -105.64450],
                    'end_coordinates' => [40.31150, -105.64680],
                    'route_coordinates' => [
                        [40.31089, -105.64450],
                        [40.31120, -105.64590],
                        [40.31150, -105.64680]
                    ],
                    'status' => 'active',
                    'best_seasons' => ['Summer', 'Fall'],
                    'is_featured' => true,
                ],
                'activities' => [$hiking, $viewpoint, $fishing]
            ],
            [
                'trail' => [
                    'name' => 'Delicate Arch Trail',
                    'description' => 'Iconic trail to Utah\'s most famous natural arch featured on the state license plate.',
                    'location' => 'Moab, Utah',
                    'difficulty_level' => 3.0,
                    'distance_km' => 4.8,
                    'elevation_gain_m' => 146,
                    'estimated_time_hours' => 2.5,
                    'trail_type' => 'out-and-back',
                    'start_coordinates' => [38.73661, -109.52005],
                    'end_coordinates' => [38.74395, -109.49926],
                    'route_coordinates' => [
                        [38.73661, -109.52005],
                        [38.74200, -109.50500],
                        [38.74395, -109.49926]
                    ],
                    'status' => 'active',
                    'best_seasons' => ['Spring', 'Fall'],
                    'is_featured' => true,
                ],
                'activities' => [$hiking, $viewpoint]
            ],
            [
                'trail' => [
                    'name' => 'Avalanche Lake Trail',
                    'description' => 'Glacier National Park gem featuring ancient cedars and pristine alpine lake.',
                    'location' => 'Lake McDonald, Montana',
                    'difficulty_level' => 3.0,
                    'distance_km' => 7.2,
                    'elevation_gain_m' => 150,
                    'estimated_time_hours' => 3.5,
                    'trail_type' => 'out-and-back',
                    'start_coordinates' => [48.67234, -113.81567],
                    'end_coordinates' => [48.68456, -113.83789],
                    'route_coordinates' => [
                        [48.67234, -113.81567],
                        [48.68000, -113.83000],
                        [48.68456, -113.83789]
                    ],
                    'status' => 'active',
                    'best_seasons' => ['Summer', 'Fall'],
                    'is_featured' => false,
                ],
                'activities' => [$hiking, $viewpoint, $camping]
            ],
            [
                'trail' => [
                    'name' => 'Chimney Tops Trail',
                    'description' => 'Steep, rocky climb to twin peaks offering panoramic Smoky Mountain views.',
                    'location' => 'Gatlinburg, Tennessee',
                    'difficulty_level' => 4.0,
                    'distance_km' => 3.2,
                    'elevation_gain_m' => 335,
                    'estimated_time_hours' => 2.5,
                    'trail_type' => 'out-and-back',
                    'start_coordinates' => [35.63456, -83.49123],
                    'end_coordinates' => [35.64789, -83.49567],
                    'route_coordinates' => [
                        [35.63456, -83.49123],
                        [35.64200, -83.49400],
                        [35.64789, -83.49567]
                    ],
                    'status' => 'active',
                    'best_seasons' => ['Spring', 'Summer', 'Fall'],
                    'is_featured' => false,
                ],
                'activities' => [$hiking, $viewpoint]
            ],
            [
                'trail' => [
                    'name' => 'Sky Pond Trail',
                    'description' => 'Spectacular alpine lake beneath dramatic cliff faces in Rocky Mountain National Park.',
                    'location' => 'Estes Park, Colorado',
                    'difficulty_level' => 4.0,
                    'distance_km' => 13.8,
                    'elevation_gain_m' => 524,
                    'estimated_time_hours' => 6.0,
                    'trail_type' => 'out-and-back',
                    'start_coordinates' => [40.31089, -105.64450],
                    'end_coordinates' => [40.29567, -105.66789],
                    'route_coordinates' => [
                        [40.31089, -105.64450],
                        [40.30000, -105.65800],
                        [40.29567, -105.66789]
                    ],
                    'status' => 'active',
                    'best_seasons' => ['Summer', 'Fall'],
                    'is_featured' => false,
                ],
                'activities' => [$hiking, $viewpoint]
            ],
            [
                'trail' => [
                    'name' => 'Grinnell Glacier Trail',
                    'description' => 'Stunning Glacier National Park hike to an active glacier with wildflower meadows.',
                    'location' => 'Babb, Montana',
                    'difficulty_level' => 5.0,
                    'distance_km' => 17.7,
                    'elevation_gain_m' => 512,
                    'estimated_time_hours' => 7.0,
                    'trail_type' => 'out-and-back',
                    'start_coordinates' => [48.69123, -113.65456],
                    'end_coordinates' => [48.75234, -113.72789],
                    'route_coordinates' => [
                        [48.69123, -113.65456],
                        [48.73000, -113.70000],
                        [48.75234, -113.72789]
                    ],
                    'status' => 'active',
                    'best_seasons' => ['Summer', 'Fall'],
                    'is_featured' => false,
                ],
                'activities' => [$hiking, $viewpoint]
            ],
            [
                'trail' => [
                    'name' => 'Quarry Rock',
                    'description' => 'Popular hike to panoramic viewpoint overlooking Deep Cove and Indian Arm.',
                    'location' => 'Deep Cove, BC',
                    'difficulty_level' => 2.0,
                    'distance_km' => 3.8,
                    'elevation_gain_m' => 100,
                    'estimated_time_hours' => 2.0,
                    'trail_type' => 'out-and-back',
                    'start_coordinates' => [49.3292, -122.9477],
                    'end_coordinates' => [49.3425, -122.9521],
                    'route_coordinates' => [
                        [49.3292, -122.9477],
                        [49.3350, -122.9510],
                        [49.3425, -122.9521]
                    ],
                    'status' => 'active',
                    'best_seasons' => ['Spring', 'Summer', 'Fall'],
                    'is_featured' => true,
                ],
                'activities' => [$hiking, $viewpoint]
            ],
            [
                'trail' => [
                    'name' => 'Joffre Lakes',
                    'description' => 'Stunning turquoise alpine lakes with glacier views.',
                    'location' => 'Pemberton, BC',
                    'difficulty_level' => 3.0,
                    'distance_km' => 10.5,
                    'elevation_gain_m' => 400,
                    'estimated_time_hours' => 4.5,
                    'trail_type' => 'out-and-back',
                    'start_coordinates' => [50.3723, -122.9654],
                    'end_coordinates' => [50.3820, -122.9750],
                    'route_coordinates' => [
                        [50.3723, -122.9654],
                        [50.3780, -122.9710],
                        [50.3820, -122.9750]
                    ],
                    'status' => 'active',
                    'best_seasons' => ['Summer', 'Fall'],
                    'is_featured' => true,
                ],
                'activities' => [$hiking, $viewpoint, $camping]
            ],
            [
                'trail' => [
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
                        [49.3430, -123.0198],
                        [49.3460, -123.0165],
                        [49.3430, -123.0198]
                    ],
                    'status' => 'active',
                    'best_seasons' => ['Spring', 'Summer', 'Fall'],
                    'is_featured' => false,
                ],
                'activities' => [$hiking, $viewpoint]
            ],
            [
                'trail' => [
                    'name' => 'Tunnel Bluffs',
                    'description' => 'Spectacular viewpoint hike overlooking Howe Sound.',
                    'location' => 'Lions Bay, BC',
                    'difficulty_level' => 3.5,
                    'distance_km' => 8.0,
                    'elevation_gain_m' => 600,
                    'estimated_time_hours' => 4.0,
                    'trail_type' => 'out-and-back',
                    'start_coordinates' => [49.4567, -123.2345],
                    'end_coordinates' => [49.4678, -123.2456],
                    'route_coordinates' => [
                        [49.4567, -123.2345],
                        [49.4635, -123.2410],
                        [49.4678, -123.2456]
                    ],
                    'status' => 'active',
                    'best_seasons' => ['Spring', 'Summer', 'Fall'],
                    'is_featured' => false,
                ],
                'activities' => [$hiking, $viewpoint]
            ],
            [
                'trail' => [
                    'name' => 'Lighthouse Park',
                    'description' => 'Coastal forest trail to historic lighthouse with ocean views.',
                    'location' => 'West Vancouver, BC',
                    'difficulty_level' => 2.0,
                    'distance_km' => 5.0,
                    'elevation_gain_m' => 100,
                    'estimated_time_hours' => 2.5,
                    'trail_type' => 'loop',
                    'start_coordinates' => [49.3327, -123.2619],
                    'end_coordinates' => [49.3327, -123.2619],
                    'route_coordinates' => [
                        [49.3327, -123.2619],
                        [49.3345, -123.2654],
                        [49.3327, -123.2619]
                    ],
                    'status' => 'active',
                    'best_seasons' => ['Spring', 'Summer', 'Fall', 'Winter'],
                    'is_featured' => false,
                ],
                'activities' => [$hiking, $viewpoint]
            ],
            [
                'trail' => [
                    'name' => 'Capilano River Trail',
                    'description' => 'Easy riverside walk through old-growth forest.',
                    'location' => 'North Vancouver, BC',
                    'difficulty_level' => 1.5,
                    'distance_km' => 7.0,
                    'elevation_gain_m' => 50,
                    'estimated_time_hours' => 2.5,
                    'trail_type' => 'out-and-back',
                    'start_coordinates' => [49.3421, -123.1156],
                    'end_coordinates' => [49.3756, -123.1234],
                    'route_coordinates' => [
                        [49.3421, -123.1156],
                        [49.3580, -123.1210],
                        [49.3756, -123.1234]
                    ],
                    'status' => 'active',
                    'best_seasons' => ['Spring', 'Summer', 'Fall'],
                    'is_featured' => false,
                ],
                'activities' => [$hiking, $fishing]
            ],
            [
                'trail' => [
                    'name' => 'Chief Peak First Peak',
                    'description' => 'Iconic granite monolith climb with spectacular Howe Sound views.',
                    'location' => 'Squamish, BC',
                    'difficulty_level' => 4.0,
                    'distance_km' => 11.0,
                    'elevation_gain_m' => 600,
                    'estimated_time_hours' => 5.0,
                    'trail_type' => 'out-and-back',
                    'start_coordinates' => [49.6823, -123.1456],
                    'end_coordinates' => [49.6934, -123.1567],
                    'route_coordinates' => [
                        [49.6823, -123.1456],
                        [49.6880, -123.1520],
                        [49.6934, -123.1567]
                    ],
                    'status' => 'active',
                    'best_seasons' => ['Spring', 'Summer', 'Fall'],
                    'is_featured' => true,
                ],
                'activities' => [$hiking, $viewpoint]
            ],
            [
                'trail' => [
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
                        [49.3789, -123.0456],
                        [49.3810, -123.0500],
                        [49.3789, -123.0456]
                    ],
                    'status' => 'active',
                    'best_seasons' => ['Spring', 'Summer', 'Fall'],
                    'is_featured' => false,
                ],
                'activities' => [$hiking, $fishing]
            ],
            [
                'trail' => [
                    'name' => 'Mount Seymour Trail',
                    'description' => 'Alpine hike with 360-degree views of mountains and city.',
                    'location' => 'North Vancouver, BC',
                    'difficulty_level' => 3.0,
                    'distance_km' => 7.0,
                    'elevation_gain_m' => 450,
                    'estimated_time_hours' => 3.5,
                    'trail_type' => 'out-and-back',
                    'start_coordinates' => [49.3678, -122.9456],
                    'end_coordinates' => [49.3834, -122.9678],
                    'route_coordinates' => [
                        [49.3678, -122.9456],
                        [49.3780, -122.9580],
                        [49.3834, -122.9678]
                    ],
                    'status' => 'active',
                    'best_seasons' => ['Summer', 'Fall'],
                    'is_featured' => false,
                ],
                'activities' => [$hiking, $viewpoint]
            ],
            [
                'trail' => [
                    'name' => 'Garibaldi Lake',
                    'description' => 'Stunning turquoise glacial lake surrounded by peaks.',
                    'location' => 'Squamish, BC',
                    'difficulty_level' => 4.0,
                    'distance_km' => 18.0,
                    'elevation_gain_m' => 850,
                    'estimated_time_hours' => 6.0,
                    'trail_type' => 'out-and-back',
                    'start_coordinates' => [49.9456, -123.0987],
                    'end_coordinates' => [49.9567, -123.0456],
                    'route_coordinates' => [
                        [49.9456, -123.0987],
                        [49.9500, -123.0700],
                        [49.9567, -123.0456]
                    ],
                    'status' => 'active',
                    'best_seasons' => ['Summer', 'Fall'],
                    'is_featured' => true,
                ],
                'activities' => [$hiking, $viewpoint, $camping]
            ],

            // === WINTER TRAILS (10 trails) ===
            [
                'trail' => [
                    'name' => 'Dream Lake Snowshoe Trail',
                    'description' => 'Magical winter wonderland snowshoe adventure to frozen Dream Lake.',
                    'location' => 'Estes Park, Colorado',
                    'difficulty_level' => 3.0,
                    'distance_km' => 5.6,
                    'elevation_gain_m' => 200,
                    'estimated_time_hours' => 3.5,
                    'trail_type' => 'out-and-back',
                    'start_coordinates' => [40.31089, -105.64450],
                    'end_coordinates' => [40.31120, -105.64590],
                    'route_coordinates' => [
                        [40.31089, -105.64450],
                        [40.31100, -105.64500],
                        [40.31120, -105.64590]
                    ],
                    'status' => 'seasonal',
                    'best_seasons' => ['Winter'],
                    'is_featured' => true,
                ],
                'activities' => [$snowshoeing, $viewpoint]
            ],
            [
                'trail' => [
                    'name' => 'Lake Louise Frozen Waterfall Snowshoe',
                    'description' => 'Stunning winter trail showcasing frozen waterfalls and turquoise ice.',
                    'location' => 'Lake Louise, Alberta',
                    'difficulty_level' => 2.0,
                    'distance_km' => 5.2,
                    'elevation_gain_m' => 95,
                    'estimated_time_hours' => 2.5,
                    'trail_type' => 'out-and-back',
                    'start_coordinates' => [51.41722, -116.21389],
                    'end_coordinates' => [51.42234, -116.22567],
                    'route_coordinates' => [
                        [51.41722, -116.21389],
                        [51.42100, -116.22200],
                        [51.42234, -116.22567]
                    ],
                    'status' => 'seasonal',
                    'best_seasons' => ['Winter'],
                    'is_featured' => true,
                ],
                'activities' => [$snowshoeing, $viewpoint]
            ],
            [
                'trail' => [
                    'name' => 'Emerald Lake Cross-Country Ski Loop',
                    'description' => 'Scenic Nordic skiing trail circling the frozen emerald-colored lake.',
                    'location' => 'Field, British Columbia',
                    'difficulty_level' => 3.0,
                    'distance_km' => 5.0,
                    'elevation_gain_m' => 45,
                    'estimated_time_hours' => 2.0,
                    'trail_type' => 'loop',
                    'start_coordinates' => [51.44333, -116.54028],
                    'end_coordinates' => [51.44333, -116.54028],
                    'route_coordinates' => [
                        [51.44333, -116.54028],
                        [51.44600, -116.54400],
                        [51.44333, -116.54028]
                    ],
                    'status' => 'seasonal',
                    'best_seasons' => ['Winter'],
                    'is_featured' => true,
                ],
                'activities' => [$crossCountry, $viewpoint]
            ],
            [
                'trail' => [
                    'name' => 'Palisades Tahoe Cross-Country Trails',
                    'description' => 'Olympic-caliber cross-country skiing facility with groomed trails.',
                    'location' => 'Olympic Valley, California',
                    'difficulty_level' => 2.0,
                    'distance_km' => 8.0,
                    'elevation_gain_m' => 150,
                    'estimated_time_hours' => 2.5,
                    'trail_type' => 'loop',
                    'start_coordinates' => [39.19789, -120.23456],
                    'end_coordinates' => [39.19789, -120.23456],
                    'route_coordinates' => [
                        [39.19789, -120.23456],
                        [39.20300, -120.24200],
                        [39.19789, -120.23456]
                    ],
                    'status' => 'seasonal',
                    'best_seasons' => ['Winter'],
                    'is_featured' => false,
                ],
                'activities' => [$crossCountry]
            ],
            [
                'trail' => [
                    'name' => 'Ice Fishing at Hebgen Lake',
                    'description' => 'Premier ice fishing destination offering excellent trout fishing.',
                    'location' => 'West Yellowstone, Montana',
                    'difficulty_level' => 2.0,
                    'distance_km' => 2.0,
                    'elevation_gain_m' => 10,
                    'estimated_time_hours' => 4.0,
                    'trail_type' => 'out-and-back',
                    'start_coordinates' => [44.85234, -111.35678],
                    'end_coordinates' => [44.86456, -111.36789],
                    'route_coordinates' => [
                        [44.85234, -111.35678],
                        [44.86200, -111.36400],
                        [44.86456, -111.36789]
                    ],
                    'status' => 'seasonal',
                    'best_seasons' => ['Winter'],
                    'is_featured' => false,
                ],
                'activities' => [$iceFishing]
            ],
            [
                'trail' => [
                    'name' => 'Vail Back Bowls Backcountry Skiing',
                    'description' => 'World-renowned powder skiing in Vail\'s legendary back bowls.',
                    'location' => 'Vail, Colorado',
                    'difficulty_level' => 5.0,
                    'distance_km' => 6.5,
                    'elevation_gain_m' => 450,
                    'estimated_time_hours' => 5.0,
                    'trail_type' => 'point-to-point',
                    'start_coordinates' => [39.64023, -106.35456],
                    'end_coordinates' => [39.62789, -106.33234],
                    'route_coordinates' => [
                        [39.64023, -106.35456],
                        [39.63000, -106.34200],
                        [39.62789, -106.33234]
                    ],
                    'status' => 'seasonal',
                    'best_seasons' => ['Winter'],
                    'is_featured' => true,
                ],
                'activities' => [$downhill, $viewpoint]
            ],
            [
                'trail' => [
                    'name' => 'Jenny Lake Snowshoe Circuit',
                    'description' => 'Peaceful winter snowshoe around the frozen shores of iconic Jenny Lake.',
                    'location' => 'Moose, Wyoming',
                    'difficulty_level' => 3.0,
                    'distance_km' => 11.3,
                    'elevation_gain_m' => 120,
                    'estimated_time_hours' => 4.5,
                    'trail_type' => 'loop',
                    'start_coordinates' => [43.75234, -110.72456],
                    'end_coordinates' => [43.75234, -110.72456],
                    'route_coordinates' => [
                        [43.75234, -110.72456],
                        [43.76200, -110.73200],
                        [43.75234, -110.72456]
                    ],
                    'status' => 'seasonal',
                    'best_seasons' => ['Winter'],
                    'is_featured' => true,
                ],
                'activities' => [$snowshoeing, $viewpoint]
            ],
            [
                'trail' => [
                    'name' => 'Breckenridge Nordic Center Trails',
                    'description' => 'Extensive groomed cross-country ski trail network through aspen groves.',
                    'location' => 'Breckenridge, Colorado',
                    'difficulty_level' => 2.0,
                    'distance_km' => 12.0,
                    'elevation_gain_m' => 180,
                    'estimated_time_hours' => 3.0,
                    'trail_type' => 'loop',
                    'start_coordinates' => [39.48123, -106.04567],
                    'end_coordinates' => [39.48123, -106.04567],
                    'route_coordinates' => [
                        [39.48123, -106.04567],
                        [39.49000, -106.05500],
                        [39.48123, -106.04567]
                    ],
                    'status' => 'seasonal',
                    'best_seasons' => ['Winter'],
                    'is_featured' => false,
                ],
                'activities' => [$crossCountry]
            ],
            [
                'trail' => [
                    'name' => 'Jackson Hole Backcountry Powder Run',
                    'description' => 'Epic expert-only skiing accessing Jackson Hole\'s famous steep terrain.',
                    'location' => 'Teton Village, Wyoming',
                    'difficulty_level' => 5.0,
                    'distance_km' => 5.8,
                    'elevation_gain_m' => 480,
                    'estimated_time_hours' => 4.5,
                    'trail_type' => 'point-to-point',
                    'start_coordinates' => [43.58789, -110.82567],
                    'end_coordinates' => [43.57234, -110.81123],
                    'route_coordinates' => [
                        [43.58789, -110.82567],
                        [43.57800, -110.81800],
                        [43.57234, -110.81123]
                    ],
                    'status' => 'seasonal',
                    'best_seasons' => ['Winter'],
                    'is_featured' => true,
                ],
                'activities' => [$downhill, $viewpoint]
            ],
            [
                'trail' => [
                    'name' => 'Maroon Bells Snowshoe Loop',
                    'description' => 'Colorado\'s most photographed peaks covered in winter splendor.',
                    'location' => 'Aspen, Colorado',
                    'difficulty_level' => 3.0,
                    'distance_km' => 7.2,
                    'elevation_gain_m' => 145,
                    'estimated_time_hours' => 3.5,
                    'trail_type' => 'loop',
                    'start_coordinates' => [39.09123, -106.94567],
                    'end_coordinates' => [39.09123, -106.94567],
                    'route_coordinates' => [
                        [39.09123, -106.94567],
                        [39.09800, -106.95500],
                        [39.09123, -106.94567]
                    ],
                    'status' => 'seasonal',
                    'best_seasons' => ['Winter'],
                    'is_featured' => true,
                ],
                'activities' => [$snowshoeing, $viewpoint]
            ],
        ];

        // Create trails and attach activities
        foreach ($trails as $data) {
            $trail = Trail::create($data['trail']);
            
            // Attach activities
            foreach ($data['activities'] as $activity) {
                if ($activity) {
                    $trail->activities()->attach($activity->id);
                    echo "✓ Created: {$trail->name} with activity: {$activity->name}\n";
                }
            }
        }

        echo "\n=== Summary ===\n";
        echo "Total trails created: " . count($trails) . "\n";
        
        $summerTrails = Trail::whereHas('activities', function($q) {
            $q->where('season_applicable', 'summer')->orWhere('season_applicable', 'both');
        })->count();
        
        $winterTrails = Trail::whereHas('activities', function($q) {
            $q->where('season_applicable', 'winter');
        })->count();
        
        echo "Summer trails: {$summerTrails}\n";
        echo "Winter trails: {$winterTrails}\n";
        echo "Done!\n";
    }
}