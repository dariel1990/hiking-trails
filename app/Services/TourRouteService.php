<?php

namespace App\Services;

use App\Models\Tour;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Builds a street-following route through a tour's stops (Mapbox Directions),
 * for tours whose admin hasn't saved a route of their own. Results are cached by
 * the stops' coordinates, so editing a stop automatically produces a fresh route.
 */
class TourRouteService
{
    /**
     * Mapbox Directions accepts at most 25 coordinates per request.
     */
    public const MAX_WAYPOINTS = 25;

    private const CACHE_TTL_SECONDS = 60 * 60 * 24 * 30;

    /**
     * After a failed lookup, wait this long before asking Mapbox again.
     */
    private const FAILURE_TTL_SECONDS = 60 * 10;

    /**
     * @return array{coordinates: list<array{0: float, 1: float}>, distance_km: float, profile: 'walking'|'driving', legs: list<array{distance_km: float, minutes: int}>}|null
     */
    public function forTour(Tour $tour): ?array
    {
        $waypoints = array_values($tour->locatedStopCoordinates());

        if (count($waypoints) < 2 || count($waypoints) > self::MAX_WAYPOINTS) {
            return null;
        }

        $profile = $tour->isWalkable() ? 'walking' : 'driving';
        $cacheKey = 'tour-route:v1:'.sha1($profile.json_encode($waypoints));

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached ?: null;
        }

        $route = $this->fetch($waypoints, $profile);

        Cache::put($cacheKey, $route ?? false, $route ? self::CACHE_TTL_SECONDS : self::FAILURE_TTL_SECONDS);

        return $route;
    }

    /**
     * @param  list<array{0: float, 1: float}>  $waypoints  [lat, lng]
     * @param  'walking'|'driving'  $profile
     * @return array{coordinates: list<array{0: float, 1: float}>, distance_km: float, profile: 'walking'|'driving', legs: list<array{distance_km: float, minutes: int}>}|null
     */
    private function fetch(array $waypoints, string $profile): ?array
    {
        $token = config('services.mapbox.access_token');

        if (blank($token)) {
            return null;
        }

        $path = implode(';', array_map(fn (array $point) => $point[1].','.$point[0], $waypoints));

        try {
            $response = Http::timeout(8)->get("https://api.mapbox.com/directions/v5/mapbox/{$profile}/{$path}", [
                'geometries' => 'geojson',
                'overview' => 'full',
                'access_token' => $token,
            ]);
        } catch (Throwable $e) {
            Log::warning('Tour route lookup failed', ['error' => $e->getMessage()]);

            return null;
        }

        $route = $response->json('routes.0');

        if (! $response->successful() || $response->json('code') !== 'Ok' || empty($route['geometry']['coordinates'])) {
            Log::warning('Tour route lookup returned no route', ['status' => $response->status(), 'code' => $response->json('code')]);

            return null;
        }

        return [
            'coordinates' => array_map(
                fn (array $point) => [(float) $point[0], (float) $point[1]],
                $route['geometry']['coordinates'],
            ),
            'distance_km' => round(($route['distance'] ?? 0) / 1000, 2),
            'profile' => $profile,
            'legs' => array_map(fn (array $leg) => [
                'distance_km' => ($leg['distance'] ?? 0) / 1000,
                'minutes' => max(1, (int) round(($leg['duration'] ?? 0) / 60)),
            ], $route['legs'] ?? []),
        ];
    }
}
