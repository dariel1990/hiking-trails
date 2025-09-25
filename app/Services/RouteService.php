<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RouteService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openrouteservice.api_key');
        $this->baseUrl = config('services.openrouteservice.base_url');
    }

    /**
     * Calculate route between two points using foot-walking profile
     */
    public function calculateRoute($startLat, $startLng, $endLat, $endLng)
    {
        if (empty($this->apiKey)) {
            Log::error('OpenRouteService API key not configured');
            return null;
        }

        try {
            // Correct URL and headers for ORS API
            $url = 'https://api.openrouteservice.org/v2/directions/foot-walking/json';
            
            $payload = [
                'coordinates' => [
                    [(float)$startLng, (float)$startLat],  // ORS expects [lng, lat]
                    [(float)$endLng, (float)$endLat]
                ],
                'radiuses' => [-1, -1],  // Allow snapping to any distance
                'instructions' => false,  // We don't need turn-by-turn
                'geometry' => true
            ];

            Log::info('ORS Request:', ['url' => $url, 'payload' => $payload]);

            $response = Http::withHeaders([
                'Accept' => 'application/json, application/geo+json, application/gpx+xml, img/png; charset=utf-8',
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json; charset=utf-8'
            ])->timeout(10)->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('ORS Success:', ['routes_count' => count($data['routes'] ?? [])]);
                
                // Convert to GeoJSON format for consistency with frontend
                if (isset($data['routes'][0])) {
                    $route = $data['routes'][0];
                    return [
                        'type' => 'FeatureCollection',
                        'features' => [[
                            'type' => 'Feature',
                            'geometry' => $route['geometry'],
                            'properties' => [
                                'segments' => [[
                                    'distance' => $route['summary']['distance'] ?? 0,
                                    'duration' => $route['summary']['duration'] ?? 0
                                ]]
                            ]
                        ]]
                    ];
                }
            }

            Log::error('ORS API Error:', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('ORS Exception:', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function isValidCoordinate($lat, $lng)
    {
        return is_numeric($lat) && is_numeric($lng) && 
            $lat >= -90 && $lat <= 90 && 
            $lng >= -180 && $lng <= 180;
    }

    /**
     * Get elevation profile for a route
     */
    public function getElevationProfile($coordinates)
    {
        try {
            // Convert coordinates to the format ORS expects
            $locations = array_map(function($coord) {
                return [$coord[1], $coord[0]]; // Convert [lat, lng] to [lng, lat]
            }, $coordinates);

            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/elevation/line', [
                'format_in' => 'geojson',
                'format_out' => 'geojson',
                'geometry' => [
                    'coordinates' => $locations,
                    'type' => 'LineString'
                ]
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Elevation profile failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
}