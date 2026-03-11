<?php

use App\Http\Controllers\TrailController;
use App\Http\Controllers\TrailNetworkController;
use App\Services\ActivityService;
use App\Services\FacilityService;
use App\Services\RouteService;
use App\Services\TrailNetworkService;
use App\Services\TrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API Routes for map data
Route::get('/trails', [TrailController::class, 'apiIndex']);
Route::get('/trails/{trail}', [TrailController::class, 'apiShow']);

// Trail Networks API
Route::get('/trail-networks', function (TrailNetworkService $service) {
    return $service->getVisibleNetworks();
});

Route::get('/trail-networks/{slug}', function (string $slug, TrailNetworkService $service) {
    return response()->json($service->getNetworkDetail($slug));
});

Route::post('/calculate-route', function (Request $request) {
    $request->validate([
        'start_lat' => 'required|numeric',
        'start_lng' => 'required|numeric',
        'end_lat' => 'required|numeric',
        'end_lng' => 'required|numeric',
    ]);

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

Route::get('/facilities', function (FacilityService $service) {
    return response()->json($service->getActiveFacilities());
});

Route::get('/highlights', [TrailNetworkController::class, 'trailHighlights']);

// New endpoints for mobile
Route::get('/featured-trails', function (TrailService $service) {
    $data = $service->getFeaturedTrails();

    $trails = $data['featuredTrails']->map(function ($trail) {
        $trailArray = $trail->toArray();
        $trailArray['featured_media_url'] = $trail->featured_media_url;
        $trailArray['trail_media'] = $trail->trailMedia?->toArray() ?? [];

        return $trailArray;
    });

    return response()->json([
        'featured_trails' => $trails,
        'stats' => $data['stats'],
        'activities' => $data['activities'],
    ]);
});

Route::get('/trail-detail/{id}', function (int $id) {
    $trail = \App\Models\Trail::with([
        'media',
        'features.media',
        'highlights.media',
        'trailNetwork',
    ])->findOrFail($id);

    $generalMedia = $trail->generalMedia->map(function ($media) {
        $item = $media->toArray();
        $item['storage_path_url'] = asset('storage/'.$media->storage_path);

        return $item;
    });

    return response()->json([
        'trail' => $trail->append(['featured_media_url', 'difficulty_text']),
        'general_media' => $generalMedia,
    ]);
});

Route::get('/trail-stats', function (TrailService $service) {
    return response()->json($service->getTrailStats());
});

Route::get('/activities', function (ActivityService $service) {
    return response()->json($service->getActiveActivities());
});
