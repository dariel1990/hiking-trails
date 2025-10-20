<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trail;
use App\Models\ActivityType;

class TrailActivitySeeder extends Seeder
{
    public function run(): void
    {
        // First, let's see what we have
        echo "Checking activities...\n";
        $activities = ActivityType::all();
        echo "Found " . $activities->count() . " activities\n";
        foreach ($activities as $activity) {
            echo "  - {$activity->name} ({$activity->slug}) - {$activity->season_applicable}\n";
        }

        // Define which trails should have which activities
        $trailActivities = [
            // Snowshoeing trails
            'Dream Lake Snowshoe Trail' => ['snowshoeing', 'viewpoint'],
            'Lake Louise Frozen Waterfall Snowshoe' => ['snowshoeing', 'viewpoint'],
            'Jenny Lake Snowshoe Circuit' => ['snowshoeing', 'viewpoint'],
            'Yellowstone Winter Wildlife Snowshoe' => ['snowshoeing', 'viewpoint'],
            'Maroon Bells Snowshoe Loop' => ['snowshoeing', 'viewpoint'],
            'Cascade Canyon Snowshoe Adventure' => ['snowshoeing', 'viewpoint'],
            
            // Cross-country skiing trails
            'Emerald Lake Cross-Country Ski Loop' => ['cross-country-skiing', 'viewpoint'],
            'Palisades Tahoe Cross-Country Trails' => ['cross-country-skiing'],
            'Breckenridge Nordic Center Trails' => ['cross-country-skiing'],
            'Royal Gorge Cross-Country Ski Trails' => ['cross-country-skiing'],
            
            // Ice fishing
            'Ice Fishing at Hebgen Lake' => ['ice-fishing'],
            'Lake Minnewanka Ice Fishing Trail' => ['ice-fishing'],
            
            // Downhill/Backcountry skiing
            'Vail Back Bowls Backcountry Skiing' => ['downhill-skiing'],
            'Alta Ski Area Backcountry Access' => ['downhill-skiing'],
            'Jackson Hole Backcountry Powder Run' => ['downhill-skiing'],
        ];

        echo "\nAttaching activities to trails...\n";
        
        foreach ($trailActivities as $trailName => $activitySlugs) {
            $trail = Trail::where('name', $trailName)->first();
            
            if (!$trail) {
                echo "❌ Trail not found: {$trailName}\n";
                continue;
            }

            foreach ($activitySlugs as $activitySlug) {
                $activity = ActivityType::where('slug', $activitySlug)->first();
                
                if (!$activity) {
                    echo "❌ Activity not found: {$activitySlug}\n";
                    continue;
                }

                // Detach first to avoid duplicates
                $trail->activities()->detach($activity->id);
                
                // Attach activity to trail
                $trail->activities()->attach($activity->id);
                echo "✓ Attached {$activitySlug} to {$trailName}\n";
            }
        }

        echo "\nDone!\n";
    }
}