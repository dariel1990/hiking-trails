<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trail;
use Illuminate\Support\Facades\Storage;

class TrailController extends Controller
{
    /**
     * Show homepage with featured trails
     */
    public function home()
    {
        $featuredTrails = Trail::where('is_featured', true)
            ->with(['trailMedia' => function($q) {
                // Only include photos for featured image display
                $q->where('media_type', 'photo')
                    ->where(function($q2) {
                        $q2->where('is_featured', true)->orWhere('sort_order', 0);
                    });
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

        $trails = $query->with(['trailMedia' => function($q) {
                            // Only include photos for featured image display
                            $q->where('media_type', 'photo')
                              ->where(function($q2) {
                                  $q2->where('is_featured', true)->orWhere('sort_order', 0);
                              });
                        }])
                       ->orderBy('is_featured', 'desc')
                       ->orderBy('name')
                       ->paginate(12);

        return view('trails.index', compact('trails'));
    }

    /**
     * Show trail detail page
     */
    public function show($id)
    {
        $trail = Trail::with([
            'media', 
            'features.media',
            'highlights.media'
        ])->findOrFail($id);
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
    public function apiIndex(Request $request)
    {
        $season = $request->get('season', $this->getCurrentSeason());
        
        $query = Trail::with([
            'media' => function($query) {
                $query->where('media_type', 'photo')->orderBy('sort_order');
            },
            'features.media',
            'activities' => function($q) use ($season) {
                // Filter activities by season
                $q->where(function($query) use ($season) {
                    $query->where('season_applicable', $season)
                        ->orWhere('season_applicable', 'both');
                })->where('is_active', true);
            },
            'seasonalData'
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
                case 'short':
                case '0-5':
                    $query->where('distance_km', '<', 5);
                    break;
                case 'medium':
                case '5-10':
                    $query->whereBetween('distance_km', [5, 10]);
                    break;
                case '10-20':
                    $query->whereBetween('distance_km', [10, 20]);
                    break;
                case 'long':
                case '20+':
                    $query->where('distance_km', '>', 15);
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

            // Get featured photo (only photos) or fallback to first photo
            $featuredMedia = $trail->media->where('is_featured', true)->where('media_type', 'photo')->first();
            if (!$featuredMedia) {
                $featuredMedia = $trail->media->where('media_type', 'photo')->first();
            }

            // Get all photos from trail_media
                $photos = $trail->media->map(function($media) {
                return [
                    'id' => $media->id,
                    'url' => Storage::url($media->storage_path),
                    'caption' => $media->caption,
                    'is_featured' => $media->is_featured,
                ];
            });

            // Get seasonal info for this trail
            $seasonalData = $trail->seasonalData()
                ->where('season', $season)
                ->first();
            
            // Map activities to the format expected by frontend
            $activities = $trail->activities->map(function($activity) {
                return [
                    'type' => $activity->slug,
                    'name' => $activity->name,
                    'icon' => $activity->icon,
                    'color' => $activity->color
                ];
            });

            // Map features/highlights with their media
            $highlights = $trail->features->map(function($feature) {
                // Normalize feature coordinates
                $featureCoords = null;
                if (is_array($feature->coordinates) && count($feature->coordinates) >= 2) {
                    $featureCoords = [
                        (float) $feature->coordinates[0],
                        (float) $feature->coordinates[1]
                    ];
                }
                
                // Get primary media from the eager-loaded collection
                $primaryMedia = $feature->media->where('is_primary', true)->first();
                
                // Map ALL media items for this feature
                $allMedia = $feature->media->map(function($media) {
                    $mediaData = [
                        'id' => $media->id,
                        'media_type' => $media->media_type,
                        'caption' => $media->caption,
                    ];
                    
                    // Handle different media types
                    if ($media->media_type === 'photo') {
                        $mediaData['url'] = Storage::url($media->storage_path);
                    } elseif ($media->media_type === 'video_url') {
                        $mediaData['video_url'] = $media->video_url;
                        $mediaData['url'] = $media->video_url;
                    } elseif ($media->media_type === 'video') {
                        $mediaData['url'] = Storage::url($media->storage_path);
                        $mediaData['video_url'] = Storage::url($media->storage_path);
                    }
                    
                    return $mediaData;
                });
                
                return [
                    'id' => $feature->id,
                    'name' => $feature->name,
                    'description' => $feature->description,
                    'type' => $feature->feature_type,
                    'feature_type' => $feature->feature_type,
                    'coordinates' => $featureCoords,
                    'photo_url' => $primaryMedia ? Storage::url($primaryMedia->storage_path) : null,
                    'media' => $allMedia,
                    'media_count' => $feature->media_count ?? 0,
                    'icon' => $feature->icon,
                    'color' => $feature->color,
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
                'preview_photo' => $featuredMedia ? Storage::url($featuredMedia->storage_path) : null,
                'photos' => $photos,
                'highlights' => $highlights,
                'activities' => $activities,
                'seasonal_info' => $seasonalData ? [
                    'season' => $seasonalData->season,
                    'recommended' => $seasonalData->recommended,
                    'notes' => $seasonalData->notes,
                    'conditions' => $seasonalData->conditions,
                ] : null,
            ];
        });

        return response()->json($trails);
    }

    /**
     * Get icon for feature type
     */
    private function getFeatureIcon($featureType)
    {
        $icons = [
            'waterfall' => '💧',
            'viewpoint' => '👁️',
            'wildlife' => '🦌',
            'bridge' => '🌉',
            'summit' => '⛰️',
            'lake' => '🏞️',
            'forest' => '🌲',
            'parking' => '🅿️',
            'restroom' => '🚻',
            'picnic' => '🍽️',
            'camping' => '⛺',
            'shelter' => '🏠',
            'other' => '📍',
        ];

        return $icons[$featureType] ?? '📍';
    }

    /**
     * Get color for feature type
     */
    private function getFeatureColor($featureType)
    {
        $colors = [
            'waterfall' => '#3B82F6',
            'viewpoint' => '#8B5CF6',
            'wildlife' => '#84CC16',
            'bridge' => '#F59E0B',
            'summit' => '#10B981',
            'lake' => '#06B6D4',
            'forest' => '#059669',
            'parking' => '#8B5CF6',
            'restroom' => '#EC4899',
            'picnic' => '#F97316',
            'camping' => '#EF4444',
            'shelter' => '#6B7280',
            'other' => '#6B7280',
        ];

        return $colors[$featureType] ?? '#6B7280';
    }

    /**
     * Get current season based on month
     */
    private function getCurrentSeason()
    {
        $month = now()->month;
        
        if ($month >= 3 && $month <= 5) return 'spring';
        if ($month >= 6 && $month <= 8) return 'summer';
        if ($month >= 9 && $month <= 11) return 'fall';
        return 'winter';
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