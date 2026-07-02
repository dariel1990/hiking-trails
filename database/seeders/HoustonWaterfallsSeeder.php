<?php

namespace Database\Seeders;

use App\Models\Tour;
use App\Models\TourStop;
use App\Models\Trail;
use Illuminate\Database\Seeder;

class HoustonWaterfallsSeeder extends Seeder
{
    public function run(): void
    {
        // These trails already exist in the database with correct GPS coordinates from GPX tracks.
        // We look them up by name rather than creating duplicates.
        $waterfallNames = [
            'Aitken Falls Trail',
            'Buck Creek Falls Trail',
            'Equity Ice Falls Trail',
            'Dungate Falls Trail',
            'Byman Falls Trail',
            'Findlay Falls Trail',
        ];

        $trailIds = Trail::whereIn('name', $waterfallNames)
            ->orderByRaw('FIELD(name, "'.implode('", "', $waterfallNames).'")')
            ->pluck('id')
            ->toArray();

        if (count($trailIds) !== count($waterfallNames)) {
            $this->command->warn('Warning: expected '.count($waterfallNames).' trails, found '.count($trailIds).'. Some trails may be missing.');
        }

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

        TourStop::where('tour_id', $tour->id)->delete();

        foreach ($trailIds as $order => $trailId) {
            TourStop::create([
                'tour_id' => $tour->id,
                'trail_id' => $trailId,
                'stop_order' => $order,
                'estimated_visit_time' => '1–2 hours',
            ]);
        }

        $this->command->info('Houston Waterfalls Tour seeded: linked '.count($trailIds).' existing trails.');
    }
}
