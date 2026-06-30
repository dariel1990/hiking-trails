<?php

namespace Database\Seeders;

use App\Models\Tour;
use App\Models\TourStop;
use App\Models\Trail;
use Illuminate\Database\Seeder;

class HoustonWaterfallsSeeder extends Seeder
{
    /**
     * Placeholder coordinates for Houston BC waterfalls.
     * Admin should update exact pin locations using the map picker in the trail editor,
     * referencing the Visit Houston BC Field Guide.
     */
    public function run(): void
    {
        $waterfalls = [
            [
                'name' => 'Aitken Falls',
                'description' => 'A beautiful waterfall located in the forests surrounding Houston, BC. Known for its scenic cascade and accessible trail.',
                'start_coordinates' => [54.397, -126.673],
            ],
            [
                'name' => 'Buck Falls',
                'description' => 'A hidden gem waterfall near Houston, BC offering a rewarding hike through old-growth forest.',
                'start_coordinates' => [54.415, -126.658],
            ],
            [
                'name' => 'Equity Ice Falls',
                'description' => 'A spectacular ice formation in winter and a dramatic waterfall in summer. One of the most impressive falls in the area.',
                'start_coordinates' => [54.422, -126.641],
            ],
            [
                'name' => 'Dungate Falls',
                'description' => 'A picturesque waterfall along Dungate Creek, accessible via a short trail through the forest.',
                'start_coordinates' => [54.408, -126.620],
            ],
            [
                'name' => 'Byman Falls',
                'description' => 'A scenic waterfall set in the rolling terrain south of Houston, BC. Great for photography and short hikes.',
                'start_coordinates' => [54.392, -126.611],
            ],
            [
                'name' => 'Findlay Falls',
                'description' => 'A charming waterfall located along Findlay Creek near Houston, BC. A peaceful spot for nature walks.',
                'start_coordinates' => [54.380, -126.595],
            ],
        ];

        $trailIds = [];
        foreach ($waterfalls as $waterfall) {
            $trail = Trail::updateOrCreate(
                ['name' => $waterfall['name']],
                [
                    'description' => $waterfall['description'],
                    'location' => 'Houston, BC',
                    'location_type' => 'trail',
                    'geometry_type' => 'point',
                    'data_source' => 'manual',
                    'status' => 'active',
                    'difficulty_level' => 1.5,
                    'distance_km' => 2.0,
                    'elevation_gain_m' => 80,
                    'estimated_time_hours' => 1.0,
                    'trail_type' => 'out-and-back',
                    'start_coordinates' => $waterfall['start_coordinates'],
                    'best_seasons' => ['spring', 'summer', 'fall'],
                    'directions' => 'From Houston town centre, follow signs toward the trail. Refer to the Visit Houston BC Field Guide for detailed road directions.',
                    'parking_info' => 'Limited roadside parking available. Please respect private property.',
                    'safety_notes' => 'Waterfall areas can be slippery. Stay on marked trails and keep a safe distance from the water\'s edge.',
                ]
            );
            $trailIds[] = $trail->id;
        }

        // Create the Houston Waterfalls Tour
        $tour = Tour::updateOrCreate(
            ['slug' => 'houston-waterfalls-tour'],
            [
                'title' => 'Houston Waterfalls Tour',
                'tagline' => 'Six stunning waterfalls in one unforgettable drive',
                'description' => 'Discover the natural beauty surrounding Houston, BC on this self-guided waterfall tour. The Circle Tour takes you to six spectacular waterfalls — each with its own character and charm. Perfect for a half-day adventure, the route follows scenic back roads through old-growth forest and mountain terrain.',
                'tour_type' => 'waterfalls',
                'difficulty_summary' => 'Easy to Moderate',
                'duration_estimate' => 'Half day',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 0,
            ]
        );

        // Clear existing stops and recreate
        TourStop::where('tour_id', $tour->id)->delete();

        foreach ($trailIds as $order => $trailId) {
            TourStop::create([
                'tour_id' => $tour->id,
                'trail_id' => $trailId,
                'stop_order' => $order,
                'estimated_visit_time' => '1–2 hours',
            ]);
        }

        $this->command->info('Houston Waterfalls Tour seeded: 6 waterfall trails + 1 tour with 6 stops.');
        $this->command->warn('⚠  Waterfall GPS coordinates are placeholders. Update exact locations via Admin → Trails → [trail name] → Edit → Map Picker.');
    }
}
