<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trail;

class TrailController extends Controller
{
    /**
     * Show homepage with featured trails
     */
    public function home()
    {
        $featuredTrails = collect([
            (object) [
                'id' => 1,
                'name' => 'Grouse Grind',
                'description' => 'Vancouver\'s most famous hiking trail, known as "Nature\'s Stairmaster". A challenging uphill climb with rewarding city views.',
                'difficulty_level' => 4,
                'distance_km' => 2.9,
                'elevation_gain_m' => 853,
                'estimated_time_hours' => 1.5,
                'start_coordinates' => [49.3486, -123.1073],
                'featured_image' => null,
                'location' => 'North Vancouver, BC'
            ],
            (object) [
                'id' => 2,
                'name' => 'Lynn Canyon Park',
                'description' => 'Beautiful forest trails with suspension bridge, waterfalls, and swimming holes. Perfect for families.',
                'difficulty_level' => 2,
                'distance_km' => 3.2,
                'elevation_gain_m' => 150,
                'estimated_time_hours' => 2,
                'start_coordinates' => [49.3430, -123.0198],
                'featured_image' => null,
                'location' => 'North Vancouver, BC'
            ],
            (object) [
                'id' => 3,
                'name' => 'Quarry Rock',
                'description' => 'Scenic hike to a stunning viewpoint overlooking Deep Cove and Indian Arm.',
                'difficulty_level' => 2,
                'distance_km' => 3.8,
                'elevation_gain_m' => 100,
                'estimated_time_hours' => 2,
                'start_coordinates' => [49.3292, -122.9477],
                'featured_image' => null,
                'location' => 'Deep Cove, BC'
            ]
        ]);

        $stats = [
            'total_trails' => 150,
            'total_distance' => 2340,
            'total_elevation' => 45600,
            'locations_count' => 25
        ];

        return view('home', compact('featuredTrails', 'stats'));
    }

    /**
     * Display trail listing page
     */
    public function index(Request $request)
    {
        // Sample trails with search and filtering
        $trails = collect([
            (object) [
                'id' => 1,
                'name' => 'Grouse Grind',
                'description' => 'Vancouver\'s most famous hiking trail',
                'difficulty_level' => 4,
                'distance_km' => 2.9,
                'elevation_gain_m' => 853,
                'estimated_time_hours' => 1.5,
                'location' => 'North Vancouver, BC'
            ],
            (object) [
                'id' => 2,
                'name' => 'Lynn Canyon Park',
                'description' => 'Beautiful forest trails with suspension bridge',
                'difficulty_level' => 2,
                'distance_km' => 3.2,
                'elevation_gain_m' => 150,
                'estimated_time_hours' => 2,
                'location' => 'North Vancouver, BC'
            ],
            (object) [
                'id' => 3,
                'name' => 'Quarry Rock',
                'description' => 'Scenic hike to stunning viewpoint',
                'difficulty_level' => 2,
                'distance_km' => 3.8,
                'elevation_gain_m' => 100,
                'estimated_time_hours' => 2,
                'location' => 'Deep Cove, BC'
            ]
        ]);

        // Apply search filter
        if ($request->search) {
            $trails = $trails->filter(function ($trail) use ($request) {
                return stripos($trail->name, $request->search) !== false ||
                       stripos($trail->description, $request->search) !== false;
            });
        }

        // Apply difficulty filter
        if ($request->difficulty) {
            $trails = $trails->filter(function ($trail) use ($request) {
                return $trail->difficulty_level == $request->difficulty;
            });
        }

        return view('trails.index', compact('trails'));
    }

    /**
     * Show trail detail page
     */
    public function show($id)
    {
        // Sample trail data
        $trail = (object) [
            'id' => $id,
            'name' => 'Sample Trail ' . $id,
            'description' => 'This is a detailed description of the sample trail with information about terrain, highlights, and what to expect.',
            'difficulty_level' => 3,
            'distance_km' => 5.2,
            'elevation_gain_m' => 300,
            'estimated_time_hours' => 2.5,
            'trail_type' => 'loop',
            'start_coordinates' => [49.2827, -122.7927],
            'end_coordinates' => [49.2827, -122.7927],
            'route_coordinates' => [],
            'status' => 'active',
            'location' => 'Sample Location, BC',
            'best_seasons' => ['Spring', 'Summer', 'Fall'],
            'features' => ['Waterfalls', 'Wildlife', 'Scenic Views'],
            'photos' => []
        ];

        return view('trails.show', compact('trail'));
    }

    /**
     * Show map page
     */
    public function map()
    {
        return view('map');
    }

    /**
     * API endpoint for trail data
     */
    public function apiIndex(Request $request)
    {
        $query = Trail::with(['activities', 'photos', 'features'])
                    ->active();

        // Season filter
        if ($request->season) {
            $query->whereHas('seasonalData', function($q) use ($request) {
                $q->where('season', $request->season)
                ->where('recommended', true);
            });
        }

        // Activity filter
        if ($request->filters) {
            $activities = explode(',', $request->filters);
            $query->whereHas('activities', function($q) use ($activities) {
                $q->whereIn('slug', $activities);
            });
        }

        $trails = $query->get()->map(function($trail) use ($request) {
            $seasonalData = $request->season ? 
                $trail->getSeasonalData($request->season) : null;

            return [
                'id' => $trail->id,
                'name' => $trail->name,
                'coordinates' => $trail->start_coordinates,
                'difficulty' => $trail->difficulty_level,
                'distance' => $trail->distance_km,
                'activities' => $trail->activities->map(function($activity) {
                    return [
                        'type' => $activity->slug,
                        'name' => $activity->name,
                        'icon' => $activity->icon,
                        'color' => $activity->color
                    ];
                }),
                'seasonal_info' => $seasonalData ? [
                    'conditions' => $seasonalData->trail_conditions,
                    'notes' => $seasonalData->seasonal_notes,
                    'recommended' => $seasonalData->recommended
                ] : null,
                'preview_photo' => $trail->featuredPhoto?->url,
            ];
        });

        return response()->json($trails);
    }
}