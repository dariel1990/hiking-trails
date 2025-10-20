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
        if (empty($coordinates) || count($coordinates) < 2) {
            Log::error('Invalid coordinates for elevation profile');
            return null;
        }

        try {
            // Limit to 100 points to avoid API rate limits
            if (count($coordinates) > 100) {
                $step = max(1, floor(count($coordinates) / 100));
                $sampledCoordinates = [];
                for ($i = 0; $i < count($coordinates); $i += $step) {
                    $sampledCoordinates[] = $coordinates[$i];
                }
                // Always include the last point
                if (end($sampledCoordinates) !== end($coordinates)) {
                    $sampledCoordinates[] = end($coordinates);
                }
                $coordinates = $sampledCoordinates;
            }

            // Format locations for OpenTopoData API (lat,lng format)
            $locations = implode('|', array_map(function($coord) {
                return $coord[0] . ',' . $coord[1];
            }, $coordinates));

            Log::info('Fetching elevation data for ' . count($coordinates) . ' points');

            // Call OpenTopoData API (free, no API key required)
            $response = Http::timeout(30)->get('https://api.opentopodata.org/v1/aster30m', [
                'locations' => $locations
            ]);

            if ($response->successful() && isset($response->json()['results'])) {
                $results = $response->json()['results'];
                
                Log::info('Elevation data received: ' . count($results) . ' results');

                // Add elevation to coordinates
                $coordinatesWithElevation = array_map(function($coord, $result) {
                    return [
                        (float) $coord[0], // latitude
                        (float) $coord[1], // longitude
                        (float) ($result['elevation'] ?? 0) // elevation in meters
                    ];
                }, $coordinates, $results);

                return [
                    'geometry' => [
                        'type' => 'LineString',
                        'coordinates' => $coordinatesWithElevation
                    ],
                    'properties' => [
                        'samples' => count($coordinatesWithElevation)
                    ]
                ];
            }

            Log::error('Failed to fetch elevation data from API');
            return null;

        } catch (\Exception $e) {
            Log::error('Elevation API error: ' . $e->getMessage());
            return null;
        }
    }
}