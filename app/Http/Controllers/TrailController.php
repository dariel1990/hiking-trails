<?php

namespace App\Http\Controllers;

use App\Models\ActivityType;
use App\Models\Trail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TrailController extends Controller
{
    /**
     * Show homepage with featured trails
     */
    public function home()
    {
        $mediaWith = ['trailMedia' => function ($q) {
            $q->where('media_type', 'photo')
                ->where(function ($q2) {
                    $q2->where('is_featured', true)->orWhere('sort_order', 0);
                });
        }];

        // Per location type: up to 3, prioritising featured then filling with active non-featured
        $featuredTrails = collect();

        foreach (['trail', 'fishing_lake'] as $type) {
            $featured = Trail::where('location_type', $type)
                ->where('is_featured', true)
                ->where('status', 'active')
                ->with($mediaWith)
                ->take(3)
                ->get();

            $needed = 3 - $featured->count();

            if ($needed > 0) {
                $fillerIds = $featured->pluck('id')->all();
                $filler = Trail::where('location_type', $type)
                    ->where('status', 'active')
                    ->whereNotIn('id', $fillerIds)
                    ->with($mediaWith)
                    ->take($needed)
                    ->get();
                $featured = $featured->merge($filler);
            }

            $featuredTrails = $featuredTrails->merge($featured);
        }

        $stats = [
            'total_trails' => Trail::count(),
            'total_distance' => Trail::sum('distance_km'),
            'total_elevation' => Trail::sum('elevation_gain_m'),
            'locations_count' => Trail::distinct('location')->count('location'),
        ];

        $activities = ActivityType::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('home', compact('featuredTrails', 'stats', 'activities'));
    }

    /**
     * Display trail listing page
     */
    public function index(Request $request)
    {
        $query = Trail::query();

        // Apply search filter
        if ($request->search) {
            $query->where(function ($q) use ($request) {
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
            switch ($request->distance) {
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

        // Apply activity filter
        if ($request->activity) {
            $query->whereHas('activities', function ($q) use ($request) {
                $q->where('slug', $request->activity);
            });
        }

        // Apply season filter
        if ($request->season) {
            $query->whereJsonContains('best_seasons', ucfirst($request->season));
        }

        $mediaWith = ['trailMedia' => function ($q) {
            $q->where('media_type', 'photo')
                ->where(function ($q2) {
                    $q2->where('is_featured', true)->orWhere('sort_order', 0);
                });
        }];

        $hikingTrails = (clone $query)
            ->where('location_type', 'trail')
            ->with($mediaWith)
            ->orderBy('is_featured', 'desc')
            ->orderBy('name')
            ->paginate(9, ['*'], 'hiking_page');

        $fishingLakes = (clone $query)
            ->where('location_type', 'fishing_lake')
            ->with($mediaWith)
            ->orderBy('is_featured', 'desc')
            ->orderBy('name')
            ->paginate(9, ['*'], 'lake_page');

        // Fetch all active activities for the filter dropdown
        $activities = ActivityType::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('trails.index', compact('hikingTrails', 'fishingLakes', 'activities'));
    }

    /**
     * Show trail detail page
     */
    public function show($id)
    {
        $trail = Trail::with([
            'media',
            'features.media',
            'highlights.media',
            'trailNetwork',
        ])->findOrFail($id);

        // Increment view count
        $trail->increment('view_count');

        // Get only general trail media (excludes feature-linked media)
        $generalMedia = $trail->generalMedia;

        return view('trails.show', compact('trail', 'generalMedia'));
    }

    /**
     * Show map page
     */
    public function map()
    {
        // Fetch all active activities for the filters
        $activities = ActivityType::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('map', compact('activities'));
    }

    /**
     * API endpoint for trail data
     */
    public function apiIndex(Request $request)
    {
        $season = $request->get('season');

        $query = Trail::with([
            'media' => function ($query) {
                $query->where('media_type', 'photo')->orderBy('sort_order');
            },
            'features.media',
            'generalMedia',
            'activities' => function ($q) {
                $q->where('is_active', true);
            },
            'seasonalData',
            'trailNetwork',
        ]);

        // Include active/seasonal trails + always-visible network trails
        $alwaysVisibleNetworkIds = \App\Models\TrailNetwork::where('is_always_visible', true)->pluck('id');
        $query->where(function ($q) use ($alwaysVisibleNetworkIds) {
            $q->whereIn('status', ['active', 'seasonal']);
            if ($alwaysVisibleNetworkIds->isNotEmpty()) {
                $q->orWhereIn('trail_network_id', $alwaysVisibleNetworkIds);
            }
        });

        // Filter by season when provided — fishing lakes are always included regardless of season
        if ($season) {
            $query->where(function ($q) use ($season) {
                $q->where('location_type', 'fishing_lake')
                    ->orWhereHas('activities', function ($q2) use ($season) {
                        $q2->where(function ($q3) use ($season) {
                            $q3->where('season_applicable', $season)
                                ->orWhere('season_applicable', 'both');
                        })->where('is_active', true);
                    });
            });
        }

        // Apply difficulty filter
        if ($request->difficulty) {
            $query->where('difficulty_level', $request->difficulty);
        }

        // Apply distance filter
        if ($request->distance) {
            switch ($request->distance) {
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
                    $query->where('distance_km', '>', 20);
                    break;
            }
        }

        $trails = $query->get()->map(function ($trail) {
            // Normalize coordinates to ensure they're [lat, lng] format
            $startCoords = $trail->start_coordinates;
            if (is_array($startCoords) && count($startCoords) === 2) {
                $coordinates = [
                    (float) $startCoords[0],
                    (float) $startCoords[1],
                ];
            } else {
                $coordinates = null;
            }

            // Normalize route coordinates
            $routeCoords = null;
            if ($trail->route_coordinates && is_array($trail->route_coordinates)) {
                $routeCoords = array_map(function ($coord) {
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
            if (! $featuredMedia) {
                $featuredMedia = $trail->media->where('media_type', 'photo')->first();
            }

            // Get all photos from trail_media (eager-loaded)
            $photos = $trail->generalMedia->map(function ($media) {
                return [
                    'id' => $media->id,
                    'url' => Storage::url($media->storage_path),
                    'caption' => $media->caption,
                    'is_featured' => $media->is_featured,
                ];
            });

            // Map activities to the format expected by frontend
            $activities = $trail->activities->map(function ($activity) {
                return [
                    'type' => $activity->slug,
                    'name' => $activity->name,
                    'icon' => $activity->icon,
                    'color' => $activity->color,
                ];
            });

            // Map features/highlights with their media
            $highlights = $trail->features->map(function ($feature) {
                // Normalize feature coordinates
                $featureCoords = null;
                if (is_array($feature->coordinates) && count($feature->coordinates) >= 2
                    && isset($feature->coordinates[0], $feature->coordinates[1])) {
                    $featureCoords = [
                        (float) $feature->coordinates[0],
                        (float) $feature->coordinates[1],
                    ];
                }

                // Get primary media from the eager-loaded collection
                $primaryMedia = $feature->media->where('is_primary', true)->first();

                // Map ALL media items for this feature
                $allMedia = $feature->media->map(function ($media) {
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
                'description' => $trail->description ? \Illuminate\Support\Str::limit(strip_tags($trail->description), 150) : null,
                'location' => $trail->location,
                'location_type' => $trail->location_type,
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
                'fishing_location' => $trail->fishing_location,
                'fishing_distance_from_town' => $trail->fishing_distance_from_town,
                'fish_species' => $trail->fish_species,
                'best_fishing_season' => $trail->best_fishing_season,
                'best_fishing_time' => $trail->best_fishing_time,
                'view_count' => $trail->view_count,
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

        if ($month >= 3 && $month <= 5) {
            return 'spring';
        }
        if ($month >= 6 && $month <= 8) {
            return 'summer';
        }
        if ($month >= 9 && $month <= 11) {
            return 'fall';
        }

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
            'highlights' => $trail->highlights->map(function ($h) {
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
