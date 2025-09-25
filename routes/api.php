<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\RouteService;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/calculate-route', function (Request $request) {
    $request->validate([
        'start_lat' => 'required|numeric',
        'start_lng' => 'required|numeric',
        'end_lat' => 'required|numeric',
        'end_lng' => 'required|numeric',
    ]);

    // Add debugging
    \Log::info('Route calculation request:', $request->all());
    
    $routeService = new RouteService();
    $route = $routeService->calculateRoute(
        $request->start_lat,
        $request->start_lng,
        $request->end_lat,
        $request->end_lng
    );

    if (!$route) {
        \Log::error('Route calculation failed for coordinates', $request->all());
        return response()->json([
            'error' => 'Unable to calculate route',
            'debug' => [
                'api_key_set' => !empty(config('services.openrouteservice.api_key')),
                'coordinates' => $request->all()
            ]
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

    $routeService = new RouteService();
    $elevation = $routeService->getElevationProfile($request->coordinates);

    if (!$elevation) {
        return response()->json(['error' => 'Unable to get elevation profile'], 400);
    }

    return response()->json($elevation);
});