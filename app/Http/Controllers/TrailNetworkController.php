<?php

namespace App\Http\Controllers;

use App\Models\TrailNetwork;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\TrailFeature;

class TrailNetworkController extends Controller
{
    /**
     * Display a listing of all trail networks
     */
    public function index()
    {
        $networks = TrailNetwork::withCount('trails')
            ->orderBy('network_name')
            ->get();
        
        return view('trail-networks.index', compact('networks'));
    }

    /**
     * Display the specified trail network with its map
     */
    public function show($slug)
    {
        $network = TrailNetwork::where('slug', $slug)
            ->with(['trails' => function($query) {
                $query->select('id', 'trail_network_id', 'name', 'description', 'difficulty_level', 'distance_km', 'elevation_gain_m', 'estimated_time_hours', 'trail_type', 'route_coordinates', 'status')
                    ->with(['trailMedia' => function($q) {
                        $q->where('media_type', 'photo')
                            ->where(function($q2) {
                                $q2->where('is_featured', true)
                                ->orWhereRaw('id IN (SELECT MIN(id) FROM trail_media WHERE trail_id = trail_media.trail_id AND media_type = "photo")');
                            })
                            ->orderBy('is_featured', 'desc')
                            ->limit(1);
                    },  'features.media' ])
                    ->where(function($q) {
                        $q->where('status', 'active')
                            ->orWhere('status', 'seasonal');
                    });
            }])
            ->firstOrFail();
        
        // Add preview_photo and photos to each trail
        $network->trails->each(function($trail) {
            $media = $trail->trailMedia->first();
            
            if ($media) {
                // Build full URL for the image
                $trail->preview_photo = asset('storage/' . $media->storage_path);
            } else {
                $trail->preview_photo = null;
            }
            
            // Also add photos array for compatibility
            $trail->photos = $trail->trailMedia->map(function($media) {
                return [
                    'url' => asset('storage/' . $media->storage_path),
                    'caption' => $media->caption
                ];
            })->toArray();

            // Format features/highlights data
            if ($trail->features) {
                $trail->features->each(function($feature) {
                    // Normalize coordinates
                    if (is_array($feature->coordinates) && count($feature->coordinates) >= 2) {
                        $feature->coordinates = [
                            (float) $feature->coordinates[0],
                            (float) $feature->coordinates[1]
                        ];
                    }
                    
                    // Format media for each feature
                    if ($feature->media) {
                        $feature->media->each(function($media) {
                            if ($media->media_type === 'photo') {
                                $media->url = asset('storage/' . $media->storage_path);
                            } elseif ($media->media_type === 'video_url') {
                                $media->url = $media->video_url;
                            } elseif ($media->media_type === 'video') {
                                $media->url = asset('storage/' . $media->storage_path);
                                $media->video_url = asset('storage/' . $media->storage_path);
                            }
                        });
                    }
                });
            }
        });
        
        return view('trail-networks.show', compact('network'));
    }

    public function trailHighlights()
    {
        $highlights = TrailFeature::with(['media', 'trail:id,name'])
            ->get()
            ->map(function($feature) {
                // Normalize coordinates
                $coordinates = null;
                if (is_array($feature->coordinates) && count($feature->coordinates) >= 2) {
                    $coordinates = [
                        (float) $feature->coordinates[0],
                        (float) $feature->coordinates[1]
                    ];
                }
                
                // Format media
                $media = $feature->media->map(function($m) {
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
                        'name' => $feature->trail->name
                    ]
                ];
            });
        
        return response()->json($highlights);
    }
}