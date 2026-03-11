<?php

namespace App\Services;

use App\Models\ActivityType;
use App\Models\Trail;
use App\Models\TrailNetwork;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class TrailService extends BaseService
{
    /**
     * Get featured trails for the home page.
     *
     * @return array{featuredTrails: Collection, stats: array<string, mixed>, activities: Collection}
     */
    public function getFeaturedTrails(): array
    {
        if ($this->isMobile()) {
            $data = $this->apiGet('/featured-trails');

            $featuredTrails = $this->toObjectCollection($data['featured_trails'] ?? [])->map(function ($trail) {
                // Blade accesses ->trailMedia but API returns trail_media
                if (! isset($trail->trailMedia) && isset($trail->trail_media)) {
                    $trail->trailMedia = collect($trail->trail_media);
                } elseif (! isset($trail->trailMedia)) {
                    $trail->trailMedia = collect();
                }

                return $trail;
            });

            return [
                'featuredTrails' => $this->resolveMediaUrls($featuredTrails),
                'stats' => $data['stats'] ?? [],
                'activities' => $this->toObjectCollection($data['activities'] ?? []),
            ];
        }

        $featuredTrails = Trail::where('is_featured', true)
            ->with(['trailMedia' => function ($q) {
                $q->where('media_type', 'photo')
                    ->where(function ($q2) {
                        $q2->where('is_featured', true)->orWhere('sort_order', 0);
                    });
            }])
            ->take(6)
            ->get();

        if ($featuredTrails->isEmpty()) {
            $featuredTrails = Trail::inRandomOrder()->take(6)->get();
        }

        $stats = $this->getTrailStats();

        $activities = ActivityType::where('is_active', true)
            ->orderBy('name')
            ->get();

        return compact('featuredTrails', 'stats', 'activities');
    }

    /**
     * Get aggregate trail statistics.
     *
     * @return array<string, mixed>
     */
    public function getTrailStats(): array
    {
        if ($this->isMobile()) {
            return $this->apiGet('/trail-stats');
        }

        return [
            'total_trails' => Trail::count(),
            'total_distance' => Trail::sum('distance_km'),
            'total_elevation' => Trail::sum('elevation_gain_m'),
            'locations_count' => Trail::distinct('location')->count('location'),
        ];
    }

    /**
     * Get filtered trails for the listing page.
     *
     * @return array{trails: LengthAwarePaginator, activities: Collection}
     */
    public function getFilteredTrails(Request $request): array
    {
        if ($this->isMobile()) {
            $data = $this->apiGet('/trails', $request->only([
                'search', 'difficulty', 'distance', 'activity', 'season', 'page',
            ]));

            return [
                'trails' => $this->toObjectCollection($data),
                'activities' => $this->toObjectCollection($this->apiGet('/activities')),
            ];
        }

        $query = Trail::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%")
                    ->orWhere('location', 'like', "%{$request->search}%");
            });
        }

        if ($request->difficulty) {
            $query->where('difficulty_level', $request->difficulty);
        }

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

        if ($request->activity) {
            $query->whereHas('activities', function ($q) use ($request) {
                $q->where('slug', $request->activity);
            });
        }

        if ($request->season) {
            $query->whereJsonContains('best_seasons', ucfirst($request->season));
        }

        $trails = $query->with(['trailMedia' => function ($q) {
            $q->where('media_type', 'photo')
                ->where(function ($q2) {
                    $q2->where('is_featured', true)->orWhere('sort_order', 0);
                });
        }])
            ->orderBy('is_featured', 'desc')
            ->orderBy('name')
            ->paginate(12);

        $activities = ActivityType::where('is_active', true)
            ->orderBy('name')
            ->get();

        return compact('trails', 'activities');
    }

    /**
     * Get trails for the interactive map (API format).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getTrailsForMap(Request $request): array
    {
        if ($this->isMobile()) {
            $data = $this->apiGet('/trails', $request->only([
                'season', 'difficulty', 'distance',
            ]));

            return $this->resolveMediaUrls($data);
        }

        $season = $request->get('season', $this->getCurrentSeason());

        $query = Trail::with([
            'media' => function ($query) {
                $query->where('media_type', 'photo')->orderBy('sort_order');
            },
            'features.media',
            'activities' => function ($q) use ($season) {
                $q->where(function ($query) use ($season) {
                    $query->where('season_applicable', $season)
                        ->orWhere('season_applicable', 'both');
                })->where('is_active', true);
            },
            'seasonalData',
            'trailNetwork',
        ]);

        $query->whereIn('status', ['active', 'seasonal']);

        $alwaysVisibleNetworkIds = TrailNetwork::where('is_always_visible', true)->pluck('id');
        if ($alwaysVisibleNetworkIds->isNotEmpty()) {
            $query->orWhereIn('trail_network_id', $alwaysVisibleNetworkIds);
        }

        $query->whereHas('activities', function ($q) use ($season) {
            $q->where(function ($query) use ($season) {
                $query->where('season_applicable', $season)
                    ->orWhere('season_applicable', 'both');
            })->where('is_active', true);
        });

        if ($request->difficulty) {
            $query->where('difficulty_level', $request->difficulty);
        }

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
                    $query->where('distance_km', '>', 15);
                    break;
            }
        }

        return $query->get()->map(function ($trail) {
            return $this->transformTrailForMap($trail);
        })->toArray();
    }

    /**
     * Get a single trail detail for the show page.
     *
     * @return array{trail: Trail, generalMedia: Collection}
     */
    public function getTrailDetail(int $id): array
    {
        if ($this->isMobile()) {
            $data = $this->apiGet("/trail-detail/{$id}");

            // Hydrate into a real Trail model so Blade can use
            // methods (isFishingLake), accessors, and relationships
            $trail = (new Trail)->forceFill($data['trail'] ?? []);
            $trail->exists = true;

            // Hydrate relationships as collections of objects
            $trail->setRelation('media', $this->toObjectCollection($data['trail']['media'] ?? []));
            $trail->setRelation('highlights', $this->toObjectCollection($data['trail']['highlights'] ?? []));
            $trail->setRelation('trailNetwork', isset($data['trail']['trail_network'])
                ? $this->toObject($data['trail']['trail_network'])
                : null);

            // Parse dates so diffForHumans() works
            if (isset($data['trail']['updated_at'])) {
                $trail->updated_at = \Carbon\Carbon::parse($data['trail']['updated_at']);
            }
            if (isset($data['trail']['created_at'])) {
                $trail->created_at = \Carbon\Carbon::parse($data['trail']['created_at']);
            }

            $generalMedia = $this->toObjectCollection($data['general_media'] ?? []);

            return compact('trail', 'generalMedia');
        }

        $trail = Trail::with([
            'media',
            'features.media',
            'highlights.media',
            'trailNetwork',
        ])->findOrFail($id);

        $trail->increment('view_count');

        $generalMedia = $trail->generalMedia;

        return compact('trail', 'generalMedia');
    }

    /**
     * Get trail data for the API show endpoint.
     *
     * @return array<string, mixed>
     */
    public function getTrailForApi(int $id): array
    {
        if ($this->isMobile()) {
            return $this->apiGet("/trails/{$id}");
        }

        $trail = Trail::findOrFail($id);

        return [
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
            'features' => [],
            'photos' => [],
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
        ];
    }

    /**
     * Get active activities, optionally filtered by season.
     */
    public function getActiveActivities(): Collection
    {
        if ($this->isMobile()) {
            return $this->toObjectCollection($this->apiGet('/activities'));
        }

        return ActivityType::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get current season based on month.
     */
    public function getCurrentSeason(): string
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
     * Transform a trail model into the map API format.
     *
     * @return array<string, mixed>
     */
    private function transformTrailForMap(Trail $trail): array
    {
        $startCoords = $trail->start_coordinates;
        $coordinates = null;
        if (is_array($startCoords) && count($startCoords) === 2) {
            $coordinates = [(float) $startCoords[0], (float) $startCoords[1]];
        }

        $routeCoords = null;
        if ($trail->route_coordinates && is_array($trail->route_coordinates)) {
            $routeCoords = array_values(array_filter(array_map(function ($coord) {
                if (is_array($coord) && count($coord) >= 2) {
                    return [(float) $coord[0], (float) $coord[1]];
                }

                return null;
            }, $trail->route_coordinates)));
        }

        $featuredMedia = $trail->media->where('is_featured', true)->where('media_type', 'photo')->first();
        if (! $featuredMedia) {
            $featuredMedia = $trail->media->where('media_type', 'photo')->first();
        }

        $photos = $trail->generalMedia->map(function ($media) {
            return [
                'id' => $media->id,
                'url' => Storage::url($media->storage_path),
                'caption' => $media->caption,
                'is_featured' => $media->is_featured,
            ];
        });

        $activities = $trail->activities->map(function ($activity) {
            return [
                'type' => $activity->slug,
                'name' => $activity->name,
                'icon' => $activity->icon,
                'color' => $activity->color,
            ];
        });

        $highlights = $trail->features->map(function ($feature) {
            $featureCoords = null;
            if (is_array($feature->coordinates) && count($feature->coordinates) >= 2) {
                $featureCoords = [(float) $feature->coordinates[0], (float) $feature->coordinates[1]];
            }

            $primaryMedia = $feature->media->where('is_primary', true)->first();

            $allMedia = $feature->media->map(function ($media) {
                $mediaData = [
                    'id' => $media->id,
                    'media_type' => $media->media_type,
                    'caption' => $media->caption,
                ];

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
            'description' => substr($trail->description, 0, 150).'...',
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
    }
}
