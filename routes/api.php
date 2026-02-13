<?php

use App\Http\Controllers\TrailController;
use App\Http\Controllers\TrailNetworkController;
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
    return \App\Models\TrailNetwork::where('is_always_visible', true)->get();
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
        'coordinates.*' => 'required|array|size:2',
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
                $photoUrl = $media->file_path ? asset('storage/' . $media->file_path) : ($media->url ?? null);
                
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
