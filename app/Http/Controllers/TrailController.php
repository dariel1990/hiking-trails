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
        $featuredTrails = Trail::where('is_featured', true)
            ->with(['media' => function($query) {
                $query->where('is_featured', true)->orWhere('sort_order', 0);
            }])
            ->take(6)
            ->get();

        // If no featured trails, get some random ones
        if ($featuredTrails->isEmpty()) {
            $featuredTrails = Trail::inRandomOrder()->take(6)->get();
        }

        $stats = [
            'total_trails' => Trail::count(),
            'total_distance' => Trail::sum('distance_km'),
            'total_elevation' => Trail::sum('elevation_gain_m'),
            'locations_count' => Trail::distinct('location')->count('location'),
        ];

        return view('home', compact('featuredTrails', 'stats'));
    }

    /**
     * Display trail listing page
     */
    public function index(Request $request)
    {
        $query = Trail::query();

        // Apply search filter
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%")
                  ->orWhere('location', 'like', "%{$request->search}%");
            });
        }

        // Apply difficulty filter
        if ($request->difficulty) {
            $query->where('difficulty_level', $request->difficulty);
        }

        // Apply distance filter
        if ($request->distance) {
            switch($request->distance) {
                case '0-5':
                    $query->where('distance_km', '<=', 5);
                    break;
                case '5-10':
                    $query->whereBetween('distance_km', [5, 10]);
                    break;
                case '10-20':
                    $query->whereBetween('distance_km', [10, 20]);
                    break;
                case '20+':
                    $query->where('distance_km', '>', 20);
                    break;
            }
        }

        $trails = $query->orderBy('is_featured', 'desc')
                       ->orderBy('name')
                       ->paginate(12);

        return view('trails.index', compact('trails'));
    }

    /**
     * Show trail detail page
     */
    public function show($id)
    {
        $trail = Trail::with(['media', 'features', 'highlights'])->findOrFail($id);
        
        // Increment view count
        $trail->increment('view_count');

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
    // public function apiIndex(Request $request)
    // {
    //     $query = Trail::with(['featuredPhoto', 'highlights']);
    //     $query->where('status', 'active');

    //     // Apply difficulty filter
    //     if ($request->difficulty) {
    //         $query->where('difficulty_level', $request->difficulty);
    //     }

    //     // Apply distance filter
    //     if ($request->distance) {
    //         switch($request->distance) {
    //             case '0-5':
    //                 $query->where('distance_km', '<=', 5);
    //                 break;
    //             case '5-10':
    //                 $query->whereBetween('distance_km', [5, 10]);
    //                 break;
    //             case '10-20':
    //                 $query->whereBetween('distance_km', [10, 20]);
    //                 break;
    //             case '20+':
    //                 $query->where('distance_km', '>', 20);
    //                 break;
    //         }
    //     }

    //     $trails = $query->get()->map(function($trail) {
    //         // Normalize coordinates to ensure they're [lat, lng] format
    //         $startCoords = $trail->start_coordinates;
    //         if (is_array($startCoords) && count($startCoords) === 2) {
    //             $coordinates = [
    //                 (float) $startCoords[0],
    //                 (float) $startCoords[1]
    //             ];
    //         } else {
    //             $coordinates = null;
    //         }

    //         // Normalize route coordinates
    //         $routeCoords = null;
    //         if ($trail->route_coordinates && is_array($trail->route_coordinates)) {
    //             $routeCoords = array_map(function($coord) {
    //                 if (is_array($coord) && count($coord) >= 2) {
    //                     return [(float) $coord[0], (float) $coord[1]];
    //                 }
    //                 return null;
    //             }, $trail->route_coordinates);
    //             // Remove any null values
    //             $routeCoords = array_filter($routeCoords);
    //             $routeCoords = array_values($routeCoords); // Re-index array
    //         }

    //         return [
    //             'id' => $trail->id,
    //             'name' => $trail->name,
    //             'description' => substr($trail->description, 0, 150) . '...',
    //             'location' => $trail->location,
    //             'coordinates' => $coordinates,
    //             'difficulty' => $trail->difficulty_level,
    //             'distance' => $trail->distance_km,
    //             'elevation_gain' => $trail->elevation_gain_m,
    //             'estimated_time' => $trail->estimated_time_hours,
    //             'trail_type' => $trail->trail_type,
    //             'status' => $trail->status,
    //             'route_coordinates' => $routeCoords,
    //             'preview_photo' => $trail->featuredPhoto ? $trail->featuredPhoto->url : null,
    //             'highlights' => $trail->highlights->map(function($h) {
    //                 // Normalize highlight coordinates too
    //                 $highlightCoords = null;
    //                 if (is_array($h->coordinates) && count($h->coordinates) >= 2) {
    //                     $highlightCoords = [
    //                         (float) $h->coordinates[0],
    //                         (float) $h->coordinates[1]
    //                     ];
    //                 }
                    
    //                 return [
    //                     'id' => $h->id,
    //                     'name' => $h->name,
    //                     'description' => $h->description,
    //                     'type' => $h->type,
    //                     'coordinates' => $highlightCoords,
    //                     'icon' => $h->display_icon,
    //                     'color' => $h->color,
    //                 ];
    //             }),
    //             'activities' => [
    //                 [
    //                     'type' => 'hiking',
    //                     'name' => 'Hiking',
    //                     'icon' => '🥾',
    //                     'color' => '#10B981'
    //                 ]
    //             ],
    //             'seasonal_info' => [
    //                 'recommended' => true,
    //                 'notes' => null
    //             ],
    //         ];
    //     });

    //     return response()->json($trails);
    // }

    /**
     * API endpoint for trail data
     */
    public function apiIndex(Request $request)
    {
        $season = $request->get('season', 'summer');
        
        $query = Trail::with([
            'media', 
            'highlights',
            'activities' => function($q) use ($season) {
                // Filter activities by season
                $q->where(function($query) use ($season) {
                    $query->where('season_applicable', $season)
                        ->orWhere('season_applicable', 'both');
                })->where('is_active', true);
            }
        ]);
        
        $query->whereIn('status', ['active', 'seasonal']);

        $query->whereHas('activities', function($q) use ($season) {
            $q->where(function($query) use ($season) {
                $query->where('season_applicable', $season)
                    ->orWhere('season_applicable', 'both');
            })->where('is_active', true);
        });

        // Apply difficulty filter
        if ($request->difficulty) {
            $query->where('difficulty_level', $request->difficulty);
        }

        // Apply distance filter
        if ($request->distance) {
            switch($request->distance) {
                case '0-5':
                    $query->where('distance_km', '<=', 5);
                    break;
                case '5-10':
                    $query->whereBetween('distance_km', [5, 10]);
                    break;
                case '10-20':
                    $query->whereBetween('distance_km', [10, 20]);
                    break;
                case '20+':
                    $query->where('distance_km', '>', 20);
                    break;
            }
        }

        $trails = $query->get()->map(function($trail) use ($season) {
            // Normalize coordinates to ensure they're [lat, lng] format
            $startCoords = $trail->start_coordinates;
            if (is_array($startCoords) && count($startCoords) === 2) {
                $coordinates = [
                    (float) $startCoords[0],
                    (float) $startCoords[1]
                ];
            } else {
                $coordinates = null;
            }

            // Normalize route coordinates
            $routeCoords = null;
            if ($trail->route_coordinates && is_array($trail->route_coordinates)) {
                $routeCoords = array_map(function($coord) {
                    if (is_array($coord) && count($coord) >= 2) {
                        return [(float) $coord[0], (float) $coord[1]];
                    }
                    return null;
                }, $trail->route_coordinates);
                // Remove any null values
                $routeCoords = array_filter($routeCoords);
                $routeCoords = array_values($routeCoords); // Re-index array
            }

            // Get seasonal info for this trail
            $seasonalData = $trail->getSeasonalData($season);
            
            // Map activities to the format expected by frontend
            $activities = $trail->activities->map(function($activity) {
                return [
                    'type' => $activity->slug,
                    'name' => $activity->name,
                    'icon' => $activity->icon,
                    'color' => $activity->color
                ];
            });
            
            return [
                'id' => $trail->id,
                'name' => $trail->name,
                'description' => substr($trail->description, 0, 150) . '...',
                'location' => $trail->location,
                'coordinates' => $coordinates,
                'difficulty' => $trail->difficulty_level,
                'distance' => $trail->distance_km,
                'elevation_gain' => $trail->elevation_gain_m,
                'estimated_time' => $trail->estimated_time_hours,
                'trail_type' => $trail->trail_type,
                'status' => $trail->status,
                'route_coordinates' => $routeCoords,
                'preview_photo' => $trail->featuredPhoto ? $trail->featuredPhoto->url : null,
                'highlights' => $trail->highlights->map(function($h) {
                    // Normalize highlight coordinates too
                    $highlightCoords = null;
                    if (is_array($h->coordinates) && count($h->coordinates) >= 2) {
                        $highlightCoords = [
                            (float) $h->coordinates[0],
                            (float) $h->coordinates[1]
                        ];
                    }
                    
                    return [
                        'id' => $h->id,
                        'name' => $h->name,
                        'description' => $h->description,
                        'type' => $h->type,
                        'coordinates' => $highlightCoords,
                        'icon' => $h->display_icon,
                        'color' => $h->color,
                    ];
                }),
                'activities' => $activities,
                'seasonal_info' => [
                    'recommended' => $seasonalData ? $seasonalData->recommended : true,
                    'notes' => $seasonalData ? $seasonalData->seasonal_notes : null,
                    'conditions' => $seasonalData ? $seasonalData->trail_conditions : null,
                ],
            ];
        });

        return response()->json($trails);
    }

    /**
     * API endpoint for single trail
     */
    public function apiShow($id)
    {
        $trail = Trail::findOrFail($id);
        
        return response()->json([
            'id' => $trail->id,
            'name' => $trail->name,
            'description' => $trail->description,
            'location' => $trail->location,
            'start_coordinates' => $trail->start_coordinates,
            'end_coordinates' => $trail->end_coordinates,
            'route_coordinates' => $trail->route_coordinates,
            'difficulty_level' => $trail->difficulty_level,
            'distance_km' => $trail->distance_km,
            'elevation_gain_m' => $trail->elevation_gain_m,
            'estimated_time_hours' => $trail->estimated_time_hours,
            'trail_type' => $trail->trail_type,
            'status' => $trail->status,
            'best_seasons' => $trail->best_seasons,
            'directions' => $trail->directions,
            'parking_info' => $trail->parking_info,
            'safety_notes' => $trail->safety_notes,
            'features' => [], // Will be populated when features are added
            'photos' => [], // Will be populated when photos are added
            'highlights' => $trail->highlights->map(function($h) {
                return [
                    'id' => $h->id,
                    'name' => $h->name,
                    'description' => $h->description,
                    'type' => $h->type,
                    'coordinates' => $h->coordinates,
                    'icon' => $h->display_icon,
                    'color' => $h->color,
                ];
            }),
        ]);
    }
}