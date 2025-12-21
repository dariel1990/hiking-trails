<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trail;
use App\Models\ActivityType;
use App\Models\TrailNetwork;

class TrailNetworkActivitiesSeeder extends Seeder
{
    public function run()
    {
        // Get or create activity types
        $activities = [
            'cross-country-skiing' => [
                'name' => 'Cross-Country Skiing',
                'slug' => 'cross-country-skiing',
                'icon' => '⛷️',
                'color' => '#3B82F6',
                'season_applicable' => 'winter',
                'is_active' => true,
            ],
            'downhill-skiing' => [
                'name' => 'Downhill Skiing',
                'slug' => 'downhill-skiing',
                'icon' => '🎿',
                'color' => '#6366F1',
                'season_applicable' => 'winter',
                'is_active' => true,
            ],
            'snowshoeing' => [
                'name' => 'Snowshoeing',
                'slug' => 'snowshoeing',
                'icon' => '🥾',
                'color' => '#06B6D4',
                'season_applicable' => 'winter',
                'is_active' => true,
            ],
        ];

        $activityModels = [];
        foreach ($activities as $key => $activityData) {
            $activity = ActivityType::updateOrCreate(
                ['slug' => $activityData['slug']],
                $activityData
            );
            $activityModels[$key] = $activity;
            $this->command->info("Activity: {$activity->name} - ID: {$activity->id}");
        }

        // Get trail networks
        $nordicCentre = TrailNetwork::where('network_name', 'Bulkley Valley Nordic Centre')->first();
        $skiHill = TrailNetwork::where('network_name', 'LIKE', '%Hudson Bay%')->first();

        if (!$nordicCentre || !$skiHill) {
            $this->command->error('Trail networks not found!');
            return;
        }

        // Get Nordic Centre trails
        $nordicTrails = Trail::where('trail_network_id', $nordicCentre->id)->get();
        
        $this->command->info("\nAssigning activities to {$nordicTrails->count()} Nordic Centre trails...");
        
        foreach ($nordicTrails as $trail) {
            // All Nordic trails get cross-country skiing
            $trail->activities()->syncWithoutDetaching([
                $activityModels['cross-country-skiing']->id => [
                    'activity_notes' => 'Groomed cross-country ski trail',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);

            // Some trails also suitable for snowshoeing (easier trails)
            if (in_array($trail->difficulty_level, [1, 2])) {
                $trail->activities()->syncWithoutDetaching([
                    $activityModels['snowshoeing']->id => [
                        'activity_notes' => 'Suitable for snowshoeing',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]);
            }

            $this->command->info("  ✓ {$trail->name}");
        }

        // Get Ski Hill trails
        $skiHillTrails = Trail::where('trail_network_id', $skiHill->id)->get();
        
        $this->command->info("\nAssigning activities to {$skiHillTrails->count()} Ski Hill trails...");
        
        foreach ($skiHillTrails as $trail) {
            // All ski hill trails get downhill skiing
            $trail->activities()->syncWithoutDetaching([
                $activityModels['downhill-skiing']->id => [
                    'activity_notes' => 'Downhill ski run',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);

            $this->command->info("  ✓ {$trail->name}");
        }

        $this->command->info("\n✅ Activities assigned successfully!");
        $this->command->info("Nordic Centre trails: Cross-country skiing" . 
                           ($nordicTrails->where('difficulty_level', '<=', 2)->count() > 0 ? " + Snowshoeing (easy trails)" : ""));
        $this->command->info("Ski Hill trails: Downhill skiing");
    }
}