<?php

namespace App\Services;

use App\Models\Trail;
use phpGPX\phpGPX;
use phpGPX\Models\GpxFile;
use phpGPX\Models\Point;
use Illuminate\Support\Facades\Log;
use Exception;

class GpxService
{
    /**
     * Parse a GPX file and return the GPX object
     *
     * @param string $filePath
     * @return GpxFile|null
     * @throws Exception
     */
    public function parseGpxFile(string $filePath): ?GpxFile
    {
        try {
            if (!file_exists($filePath)) {
                throw new Exception("GPX file not found: {$filePath}");
            }

            $gpx = new phpGPX();
            $gpxFile = $gpx->load($filePath);

            if (!$gpxFile) {
                throw new Exception("Failed to parse GPX file");
            }

            return $gpxFile;
        } catch (Exception $e) {
            Log::error('GPX Parse Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Calculate total distance from track points using Haversine formula
     *
     * @param array $trackPoints Array of [lat, lon] or Point objects
     * @return float Distance in kilometers
     */
    public function calculateDistance(array $trackPoints): float
    {
        if (count($trackPoints) < 2) {
            return 0.0;
        }

        $totalDistance = 0.0;

        for ($i = 1; $i < count($trackPoints); $i++) {
            $point1 = $this->extractCoordinates($trackPoints[$i - 1]);
            $point2 = $this->extractCoordinates($trackPoints[$i]);

            $totalDistance += $this->haversineDistance(
                $point1['lat'],
                $point1['lon'],
                $point2['lat'],
                $point2['lon']
            );
        }

        return round($totalDistance, 2);
    }

    /**
     * Calculate cumulative elevation gain from track points
     *
     * @param array $trackPoints Array of Point objects with elevation data
     * @return int Elevation gain in meters
     */
    public function calculateElevation(array $trackPoints): int
    {
        if (count($trackPoints) < 2) {
            return 0;
        }

        $totalGain = 0.0;

        for ($i = 1; $i < count($trackPoints); $i++) {
            $elevation1 = $this->extractElevation($trackPoints[$i - 1]);
            $elevation2 = $this->extractElevation($trackPoints[$i]);

            // Only count positive elevation changes (going up)
            if ($elevation2 !== null && $elevation1 !== null) {
                $change = $elevation2 - $elevation1;
                if ($change > 0) {
                    $totalGain += $change;
                }
            }
        }

        return (int) round($totalGain);
    }

    /**
     * Estimate hiking time using Naismith's Rule
     *
     * @param float $distance Distance in kilometers
     * @param int $elevation Elevation gain in meters
     * @param float $difficulty Difficulty factor (1.0 to 5.0)
     * @return float Estimated time in hours
     */
    public function estimateTime(float $distance, int $elevation, float $difficulty = 3.0): float
    {
        // Base time: 5 km per hour on flat terrain
        $baseTime = $distance / 5.0;

        // Add time for elevation: 1 hour per 600 meters of elevation gain
        $elevationTime = $elevation / 600.0;

        // Calculate total time
        $totalTime = $baseTime + $elevationTime;

        // Apply difficulty factor
        // Easy trails (1.0-2.0): 0.8x - 0.9x
        // Moderate trails (2.1-3.5): 1.0x
        // Hard trails (3.6-5.0): 1.1x - 1.5x
        $difficultyMultiplier = $this->getDifficultyMultiplier($difficulty);
        $totalTime *= $difficultyMultiplier;

        return round($totalTime, 2);
    }

    /**
     * Extract route coordinates from track points and simplify if needed
     *
     * @param array $trackPoints
     * @return array Array of [lat, lng] coordinates
     */
    public function extractRouteCoordinates(array $trackPoints): array
    {
        $coordinates = [];

        foreach ($trackPoints as $point) {
            $coord = $this->extractCoordinates($point);
            $coordinates[] = [$coord['lat'], $coord['lon']];
        }

        // Simplify if too many points (>500)
        if (count($coordinates) > 500) {
            $coordinates = $this->simplifyRoute($coordinates, 500);
        }

        return $coordinates;
    }

    /**
     * Store GPX data and calculated values in the trail record
     *
     * @param Trail $trail
     * @param GpxFile $gpxObject
     * @param array $calculatedValues ['distance' => float, 'elevation' => int, 'time' => float]
     * @return Trail
     */
    public function storeGpxData(Trail $trail, GpxFile $gpxObject, array $calculatedValues): Trail
    {
        // Extract track points for storage
        $trackPoints = $this->getAllTrackPoints($gpxObject);
        $coordinates = $this->extractRouteCoordinates($trackPoints);

        // Prepare GPX raw data for storage
        $gpxRawData = [
            'metadata' => [
                'name' => $gpxObject->metadata->name ?? null,
                'description' => $gpxObject->metadata->description ?? null,
                'author' => $gpxObject->metadata->author ?? null,
                'time' => $gpxObject->metadata->time ? $gpxObject->metadata->time->format('Y-m-d H:i:s') : null,
            ],
            'stats' => [
                'total_points' => count($trackPoints),
                'simplified_points' => count($coordinates),
            ],
        ];

        // Update trail with GPX data
        $trail->update([
            'gpx_raw_data' => $gpxRawData,
            'gpx_calculated_distance' => $calculatedValues['distance'],
            'gpx_calculated_elevation' => $calculatedValues['elevation'],
            'gpx_calculated_time' => $calculatedValues['time'],
            'route_coordinates' => $coordinates,
            'data_source' => 'gpx',
            'gpx_uploaded_at' => now(),
            
            // Also update the main fields with calculated values
            'distance_km' => $calculatedValues['distance'],
            'elevation_gain_m' => $calculatedValues['elevation'],
            'estimated_time_hours' => $calculatedValues['time'],
            
            // Set start and end coordinates if not already set
            'start_coordinates' => $trail->start_coordinates ?? $coordinates[0] ?? null,
            'end_coordinates' => $trail->end_coordinates ?? $coordinates[count($coordinates) - 1] ?? null,
        ]);

        return $trail->fresh();
    }

    /**
     * Calculate all values from a GPX file
     *
     * @param string $filePath
     * @param float $difficulty
     * @return array ['distance' => float, 'elevation' => int, 'time' => float, 'coordinates' => array]
     * @throws Exception
     */
    public function calculateAllFromGpx(string $filePath, float $difficulty = 3.0): array
    {
        $gpxFile = $this->parseGpxFile($filePath);
        $trackPoints = $this->getAllTrackPoints($gpxFile);

        if (empty($trackPoints)) {
            throw new Exception("No track points found in GPX file");
        }

        $distance = $this->calculateDistance($trackPoints);
        $elevation = $this->calculateElevation($trackPoints);
        $time = $this->estimateTime($distance, $elevation, $difficulty);
        $coordinates = $this->extractRouteCoordinates($trackPoints);

        return [
            'distance' => $distance,
            'elevation' => $elevation,
            'time' => $time,
            'coordinates' => $coordinates,
            'point_count' => count($trackPoints),
            'gpx_file' => $gpxFile,
        ];
    }

    // ============================================
    // PRIVATE HELPER METHODS
    // ============================================

    /**
     * Get all track points from a GPX file
     *
     * @param GpxFile $gpxFile
     * @return array
     */
    private function getAllTrackPoints(GpxFile $gpxFile): array
    {
        $allPoints = [];

        foreach ($gpxFile->tracks as $track) {
            foreach ($track->segments as $segment) {
                foreach ($segment->points as $point) {
                    $allPoints[] = $point;
                }
            }
        }

        return $allPoints;
    }

    /**
     * Extract coordinates from a Point object or array
     *
     * @param mixed $point
     * @return array ['lat' => float, 'lon' => float]
     */
    private function extractCoordinates($point): array
    {
        if ($point instanceof Point) {
            return [
                'lat' => $point->latitude,
                'lon' => $point->longitude,
            ];
        }

        if (is_array($point)) {
            return [
                'lat' => $point[0] ?? $point['lat'] ?? 0,
                'lon' => $point[1] ?? $point['lon'] ?? $point['lng'] ?? 0,
            ];
        }

        return ['lat' => 0, 'lon' => 0];
    }

    /**
     * Extract elevation from a Point object
     *
     * @param mixed $point
     * @return float|null
     */
    private function extractElevation($point): ?float
    {
        if ($point instanceof Point) {
            return $point->elevation;
        }

        if (is_array($point) && isset($point['elevation'])) {
            return (float) $point['elevation'];
        }

        return null;
    }

    /**
     * Calculate distance between two points using Haversine formula
     *
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float Distance in kilometers
     */
    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get difficulty multiplier based on trail difficulty
     *
     * @param float $difficulty
     * @return float
     */
    private function getDifficultyMultiplier(float $difficulty): float
    {
        if ($difficulty <= 2.0) {
            // Easy trails: 0.85x
            return 0.85;
        } elseif ($difficulty <= 3.5) {
            // Moderate trails: 1.0x
            return 1.0;
        } else {
            // Hard trails: 1.2x - 1.5x
            return 1.0 + (($difficulty - 3.5) * 0.33);
        }
    }

    /**
     * Simplify route by keeping every Nth point to reduce to target count
     *
     * @param array $coordinates
     * @param int $targetCount
     * @return array
     */
    private function simplifyRoute(array $coordinates, int $targetCount): array
    {
        $total = count($coordinates);
        
        if ($total <= $targetCount) {
            return $coordinates;
        }

        $simplified = [];
        $step = $total / $targetCount;

        // Always include first point
        $simplified[] = $coordinates[0];

        // Sample points at regular intervals
        for ($i = 1; $i < $targetCount - 1; $i++) {
            $index = (int) round($i * $step);
            if ($index < $total) {
                $simplified[] = $coordinates[$index];
            }
        }

        // Always include last point
        $simplified[] = $coordinates[$total - 1];

        return $simplified;
    }

    /**
     * Calculate statistics from GPX file (for preview/debugging)
     *
     * @param string $filePath
     * @return array
     */
    public function getGpxStatistics(string $filePath): array
    {
        try {
            $gpxFile = $this->parseGpxFile($filePath);
            $trackPoints = $this->getAllTrackPoints($gpxFile);

            $elevations = array_filter(array_map(function ($point) {
                return $this->extractElevation($point);
            }, $trackPoints));

            return [
                'total_points' => count($trackPoints),
                'has_elevation' => !empty($elevations),
                'min_elevation' => !empty($elevations) ? min($elevations) : null,
                'max_elevation' => !empty($elevations) ? max($elevations) : null,
                'track_count' => count($gpxFile->tracks),
                'metadata' => [
                    'name' => $gpxFile->metadata->name ?? null,
                    'description' => $gpxFile->metadata->description ?? null,
                ],
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get GPX file info without full processing (quick preview)
     */
    public function getGpxInfo($filePath)
    {
        if (!file_exists($filePath)) {
            throw new Exception('GPX file not found: ' . $filePath);
        }

        $gpx = new \phpGPX\phpGPX();
        $file = $gpx->load($filePath);

        $tracks = $file->tracks;
        if (empty($tracks)) {
            throw new Exception('No tracks found in GPX file');
        }

        $totalPoints = 0;
        foreach ($tracks as $track) {
            foreach ($track->segments as $segment) {
                $totalPoints += count($segment->points);
            }
        }

        return [
            'track_count' => count($tracks),
            'point_count' => $totalPoints,
            'estimated_processing_time' => $totalPoints > 1000 ? 'high' : ($totalPoints > 500 ? 'medium' : 'low')
        ];
    }
}