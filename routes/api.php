<?php

use App\Http\Controllers\TrailController;
use App\Http\Controllers\TrailNetworkController;
use App\Models\Business;
use App\Models\Facility;
use App\Services\RouteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API Routes for map data
Route::get('/trails', [TrailController::class, 'apiIndex']);
Route::get('/trails/{trail}', [TrailController::class, 'apiShow']);
// Trail Networks API
Route::get('/trail-networks', function () {
    $networks = \App\Models\TrailNetwork::with(['trails' => function ($q) {
        $q->select('id', 'trail_network_id', 'name', 'difficulty_level', 'distance_km', 'status')
            ->whereIn('status', ['active', 'seasonal'])
            ->orderBy('difficulty_level');
    }])
        ->get()
        ->filter(fn ($n) => $n->trails->isNotEmpty())
        ->values()
        ->map(fn ($network) => [
            'id'          => $network->id,
            'name'        => $network->network_name,
            'slug'        => $network->slug,
            'type'        => $network->type,
            'season'      => $network->season,
            'icon'        => $network->icon,
            'image'       => $network->image ? asset('storage/'.$network->image) : null,
            'description' => $network->description,
            'latitude'    => $network->latitude  ? (float) $network->latitude  : null,
            'longitude'   => $network->longitude ? (float) $network->longitude : null,
            'website_url' => $network->website_url,
            'trail_count' => $network->trails->count(),
            'trails'      => $network->trails->map(fn ($t) => [
                'id'         => $t->id,
                'name'       => $t->name,
                'difficulty' => $t->difficulty_level,
                'distance'   => $t->distance_km,
            ])->values(),
        ]);

    return response()->json($networks);
});

Route::post('/calculate-route', function (Request $request) {
    $request->validate([
        'start_lat' => 'required|numeric',
        'start_lng' => 'required|numeric',
        'end_lat' => 'required|numeric',
        'end_lng' => 'required|numeric',
    ]);

    // Add debugging
    \Log::info('Route calculation request:', $request->all());

    $routeService = new RouteService;
    $route = $routeService->calculateRoute(
        $request->start_lat,
        $request->start_lng,
        $request->end_lat,
        $request->end_lng
    );

    if (! $route) {
        \Log::error('Route calculation failed for coordinates', $request->all());

        return response()->json([
            'error' => 'Unable to calculate route',
            'debug' => [
                'api_key_set' => ! empty(config('services.openrouteservice.api_key')),
                'coordinates' => $request->all(),
            ],
        ], 400);
    }

    \Log::info('Route calculation successful');

    return response()->json($route);
});
Route::post('/elevation-profile', function (Request $request) {
    $request->validate([
        'coordinates' => 'required|array',
        'coordinates.*' => 'required|array|min:2',
    ]);

    $routeService = new RouteService;
    $elevation = $routeService->getElevationProfile($request->coordinates);

    if (! $elevation) {
        return response()->json(['error' => 'Unable to get elevation profile'], 400);
    }

    return response()->json($elevation);
});

Route::get('/facilities', function () {
    $facilities = Facility::where('is_active', true)
        ->with(['media' => function ($query) {
            $query->orderBy('sort_order')->orderBy('created_at');
        }])
        ->get();

    return $facilities->map(function ($facility) {
        return [
            'id' => $facility->id,
            'name' => $facility->name,
            'facility_type' => $facility->facility_type,
            'facility_type_label' => $facility->facility_type_label,
            'description' => $facility->description,
            'latitude' => $facility->latitude,
            'longitude' => $facility->longitude,
            'icon' => $facility->icon,
            'media_count' => $facility->media_count,
            'media' => $facility->media->map(function ($media) {
                // For photos: use file_path directly with asset() like admin blade
                $photoUrl = $media->file_path ? asset('storage/'.$media->file_path) : ($media->url ?? null);

                // For videos: use url field directly
                $videoUrl = $media->url ?? null;

                return [
                    'id' => $media->id,
                    'media_type' => $media->media_type,
                    'url' => $media->media_type === 'photo' ? $photoUrl : $videoUrl,
                    'thumbnail_url' => $media->media_type === 'photo' ? $photoUrl : $media->thumbnail_url,
                    'embed_url' => $media->embed_url,
                    'caption' => $media->caption,
                    'is_primary' => $media->is_primary,
                    'video_provider' => $media->video_provider,
                ];
            }),
        ];
    });
});

Route::get('/highlights', [TrailNetworkController::class, 'trailHighlights']);

Route::get('/businesses', function () {
    $type = request('type');

    $businesses = Business::active()
        ->with(['media' => function ($query) {
            $query->where('is_primary', true)->orderBy('sort_order');
        }])
        ->when($type, fn ($q) => $q->where('business_type', $type))
        ->orderBy('name')
        ->get();

    return $businesses->map(function (Business $business) {
        $primaryMedia = $business->media->first();
        $photoUrl = $primaryMedia && $primaryMedia->file_path
            ? asset('storage/'.$primaryMedia->file_path)
            : null;

        return [
            'id' => $business->id,
            'name' => $business->name,
            'slug' => $business->slug,
            'business_type' => $business->business_type,
            'business_type_label' => $business->business_type_label,
            'description' => $business->description,
            'tagline' => $business->tagline,
            'address' => $business->address,
            'latitude' => $business->latitude,
            'longitude' => $business->longitude,
            'phone' => $business->phone,
            'email' => $business->email,
            'website' => $business->website,
            'price_range' => $business->price_range,
            'is_seasonal' => $business->is_seasonal,
            'season_open' => $business->season_open,
            'icon' => $business->icon,
            'is_featured' => $business->is_featured,
            'photo_url' => $photoUrl,
        ];
    });
});
