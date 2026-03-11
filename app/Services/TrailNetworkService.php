<?php

namespace App\Services;

use App\Models\TrailFeature;
use App\Models\TrailNetwork;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class TrailNetworkService extends BaseService
{
    /**
     * Get all trail networks with trail counts.
     */
    public function getNetworks(): Collection
    {
        if ($this->isMobile()) {
            return $this->toObjectCollection($this->apiGet('/trail-networks'));
        }

        return TrailNetwork::withCount('trails')
            ->orderBy('network_name')
            ->get();
    }

    /**
     * Get a single trail network with its trails by slug.
     */
    public function getNetworkDetail(string $slug): mixed
    {
        if ($this->isMobile()) {
            return $this->toObject($this->apiGet("/trail-networks/{$slug}"));
        }

        $network = TrailNetwork::where('slug', $slug)
            ->with(['trails' => function ($query) {
                $query->select('id', 'trail_network_id', 'name', 'description', 'difficulty_level', 'distance_km', 'elevation_gain_m', 'estimated_time_hours', 'trail_type', 'route_coordinates', 'status')
                    ->with(['trailMedia' => function ($q) {
                        $q->where('media_type', 'photo')
                            ->where(function ($q2) {
                                $q2->where('is_featured', true)
                                    ->orWhereRaw('id IN (SELECT MIN(id) FROM trail_media WHERE trail_id = trail_media.trail_id AND media_type = "photo")');
                            })
                            ->orderBy('is_featured', 'desc')
                            ->limit(1);
                    }, 'features.media'])
                    ->where(function ($q) {
                        $q->where('status', 'active')
                            ->orWhere('status', 'seasonal');
                    });
            }])
            ->firstOrFail();

        $network->trails->each(function ($trail) {
            $media = $trail->trailMedia->first();
            $trail->preview_photo = $media ? asset('storage/'.$media->storage_path) : null;

            $trail->photos = $trail->trailMedia->map(function ($media) {
                return [
                    'url' => asset('storage/'.$media->storage_path),
                    'caption' => $media->caption,
                ];
            })->toArray();

            if ($trail->features) {
                $trail->features->each(function ($feature) {
                    if (is_array($feature->coordinates) && count($feature->coordinates) >= 2) {
                        $feature->coordinates = [
                            (float) $feature->coordinates[0],
                            (float) $feature->coordinates[1],
                        ];
                    }

                    if ($feature->media) {
                        $feature->media->each(function ($media) {
                            if ($media->media_type === 'photo') {
                                $media->url = asset('storage/'.$media->storage_path);
                            } elseif ($media->media_type === 'video_url') {
                                $media->url = $media->video_url;
                            } elseif ($media->media_type === 'video') {
                                $media->url = asset('storage/'.$media->storage_path);
                                $media->video_url = asset('storage/'.$media->storage_path);
                            }
                        });
                    }
                });
            }
        });

        return $network;
    }

    /**
     * Get trail highlights/features for the map.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getHighlights(): array
    {
        if ($this->isMobile()) {
            return $this->apiGet('/highlights');
        }

        return TrailFeature::with(['media', 'trail:id,name'])
            ->get()
            ->map(function ($feature) {
                $coordinates = null;
                if (is_array($feature->coordinates) && count($feature->coordinates) >= 2) {
                    $coordinates = [
                        (float) $feature->coordinates[0],
                        (float) $feature->coordinates[1],
                    ];
                }

                $media = $feature->media->map(function ($m) {
                    $mediaData = [
                        'id' => $m->id,
                        'media_type' => $m->media_type,
                        'caption' => $m->caption,
                    ];

                    if ($m->media_type === 'photo') {
                        $mediaData['url'] = Storage::url($m->storage_path);
                    } elseif ($m->media_type === 'video_url') {
                        $mediaData['url'] = $m->video_url;
                        $mediaData['video_url'] = $m->video_url;
                    } elseif ($m->media_type === 'video') {
                        $mediaData['url'] = Storage::url($m->storage_path);
                        $mediaData['video_url'] = Storage::url($m->storage_path);
                    }

                    return $mediaData;
                });

                return [
                    'id' => $feature->id,
                    'name' => $feature->name,
                    'description' => $feature->description,
                    'type' => $feature->feature_type,
                    'feature_type' => $feature->feature_type,
                    'coordinates' => $coordinates,
                    'icon' => $feature->icon,
                    'color' => $feature->color,
                    'media' => $media,
                    'trail' => [
                        'id' => $feature->trail->id,
                        'name' => $feature->trail->name,
                    ],
                ];
            })->toArray();
    }

    /**
     * Get always-visible trail networks (for map).
     */
    public function getVisibleNetworks(): Collection
    {
        if ($this->isMobile()) {
            return $this->toObjectCollection($this->apiGet('/trail-networks'));
        }

        return TrailNetwork::where('is_always_visible', true)->get();
    }
}
