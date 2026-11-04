<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\TrailFeature;
use App\Models\TrailMedia;
use App\Models\TrailNetwork;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TrailNetworkController extends Controller
{
    /**
     * Display a listing of all trail networks
     */
    public function index(Request $request)
    {
        $type = $request->get('type');

        $query = TrailNetwork::active()->withCount('trails')->orderBy('network_name');

        if ($type === 'ski') {
            $query->whereIn('type', ['nordic_skiing', 'downhill_skiing']);
        } elseif ($type === 'mountain_biking') {
            $query->where('type', 'mountain_biking');
        }

        $networks = $query->get();

        return view('trail-networks.index', compact('networks', 'type'));
    }

    /**
     * Display the specified trail network with its map
     */
    public function show($slug)
    {
        $network = TrailNetwork::where('slug', $slug)
            ->active()
            ->with(['trails' => function ($query) {
                $query->select('id', 'trail_network_id', 'name', 'description', 'difficulty_level', 'distance_km', 'elevation_gain_m', 'estimated_time_hours', 'trail_type', 'route_coordinates', 'start_coordinates', 'status')
                    ->with(['media', 'generalMedia', 'features.media'])
                    ->where(function ($q) {
                        $q->where('status', 'active')
                            ->orWhere('status', 'seasonal');
                    });
            }])
            ->firstOrFail();

        // Same image/video setup as the /maps trail panel: featured photo as hero,
        // trail-level (non-highlight) photos and videos as the gallery.
        $network->trails->each(function ($trail) {
            $photoMedia = $trail->media->where('media_type', 'photo');
            $featuredMedia = $photoMedia->firstWhere('is_featured', true) ?? $photoMedia->first();

            $trail->preview_photo = $featuredMedia ? Storage::url($featuredMedia->storage_path) : null;

            $gallery = $this->formatTrailGalleryMedia($trail->generalMedia);
            $trail->photos = $gallery['photos'];
            $trail->videos = $gallery['videos'];

            $trail->unsetRelation('media');
            $trail->unsetRelation('generalMedia');

            // Format features/highlights data
            if ($trail->features) {
                $trail->features->each(function ($feature) {
                    // Normalize coordinates
                    if (is_array($feature->coordinates) && count($feature->coordinates) >= 2) {
                        $feature->coordinates = [
                            (float) $feature->coordinates[0],
                            (float) $feature->coordinates[1],
                        ];
                    }

                    // Format media for each feature
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

        $mapboxToken = config('services.mapbox.access_token');

        $facilities = Facility::where('is_active', true)
            ->where('trail_network_id', $network->id)
            ->orderBy('facility_type')
            ->orderBy('name')
            ->get(['id', 'name', 'facility_type', 'latitude', 'longitude', 'description', 'icon', 'icon_image'])
            ->map(function ($facility) {
                $data = $facility->toArray();
                $data['icon_image_url'] = $facility->icon_image ? asset('storage/'.$facility->icon_image) : null;

                return $data;
            });

        return view('trail-networks.show', compact('network', 'mapboxToken', 'facilities'));
    }

    /**
     * Split a trail's general media into the photo and video lists used by the map gallery.
     *
     * @param  Collection<int, TrailMedia>  $media
     * @return array{
     *     photos: list<array{id: int, url: string, caption: ?string, is_featured: bool}>,
     *     videos: list<array{id: int, media_type: string, url: ?string, video_url: ?string, thumbnail_url: ?string, embed_url: ?string, caption: ?string, is_featured: bool, video_provider: ?string}>
     * }
     */
    private function formatTrailGalleryMedia(Collection $media): array
    {
        $photos = $media->where('media_type', 'photo')
            ->map(fn (TrailMedia $item) => [
                'id' => $item->id,
                'url' => Storage::url($item->storage_path),
                'caption' => $item->caption,
                'is_featured' => (bool) $item->is_featured,
            ])->values()->all();

        $videos = $media->filter(fn (TrailMedia $item) => in_array($item->media_type, ['video', 'video_url']))
            ->map(fn (TrailMedia $item) => [
                'id' => $item->id,
                'media_type' => $item->media_type,
                'url' => $item->media_type === 'video_url'
                    ? $item->video_url
                    : Storage::url($item->storage_path),
                'video_url' => $item->video_url,
                'thumbnail_url' => $item->thumbnail_url,
                'embed_url' => $item->embed_url,
                'caption' => $item->caption,
                'is_featured' => (bool) $item->is_featured,
                'video_provider' => $item->video_provider,
            ])->values()->all();

        return ['photos' => $photos, 'videos' => $videos];
    }

    public function trailHighlights()
    {
        $highlights = TrailFeature::with(['media', 'trail:id,name'])
            ->get()
            ->map(function ($feature) {
                // Normalize coordinates — stored as {lat,lng} object or [lat,lng] array
                $coordinates = null;
                $raw = $feature->coordinates;
                if (is_array($raw)) {
                    if (isset($raw['lat'], $raw['lng'])) {
                        $coordinates = [(float) $raw['lat'], (float) $raw['lng']];
                    } elseif (isset($raw[0], $raw[1])) {
                        $coordinates = [(float) $raw[0], (float) $raw[1]];
                    }
                }

                // Format media
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
                    'icon_image_url' => $feature->icon_image_url,
                    'color' => $feature->color,
                    'media' => $media,
                    'trail' => [
                        'id' => $feature->trail->id,
                        'name' => $feature->trail->name,
                    ],
                ];
            });

        return response()->json($highlights);
    }
}
