<?php

namespace App\Http\Controllers;

use App\Models\TrailNetwork;
use Illuminate\Http\Request;

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
                    }])
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
        });
        
        return view('trail-networks.show', compact('network'));
    }
}