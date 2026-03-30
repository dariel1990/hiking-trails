<?php

namespace App\Services;

use App\Models\Trail;
use Exception;
use Illuminate\Support\Facades\Log;
use phpGPX\Models\GpxFile;
use phpGPX\Models\Point;
use phpGPX\phpGPX;

class GpxService
{
    /**
     * Parse a GPX file and return the GPX object
     *
     * @throws Exception
     */
    public function parseGpxFile(string $filePath): ?GpxFile
    {
        try {
            if (! file_exists($filePath)) {
                throw new Exception("GPX file not found: {$filePath}");
            }

            $gpx = new phpGPX;
            $gpxFile = $gpx->load($filePath);

            if (! $gpxFile) {
                throw new Exception('Failed to parse GPX file');
            }

            return $gpxFile;
        } catch (Exception $e) {
            Log::error('GPX Parse Error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Calculate total distance from track points using Haversine formula
     *
     * @param  array  $trackPoints  Array of [lat, lon] or Point objects
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
     * @param  array  $trackPoints  Array of Point objects with elevation data
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
     * @param  float  $distance  Distance in kilometers
     * @param  int  $elevation  Elevation gain in meters
     * @param  float  $difficulty  Difficulty factor (1.0 to 5.0)
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
     * @return array Array of [lat, lng] coordinates
     */
    public function extractRouteCoordinates(array $trackPoints): array
    {
        $coordinates = [];

        foreach ($trackPoints as $point) {
            $coord = $this->extractCoordinates($point);
            $coordinates[] = [$coord['lat'], $coord['lon']];
        }

        // Remove repeated passes through the same area (circling/looping cleanup)
        $coordinates = $this->removeLoopingSegments($coordinates);

        // RDP simplification to cap point count
        if (count($coordinates) > 500) {
            $coordinates = $this->simplifyRoute($coordinates, 500);
        }

        return $coordinates;
    }

    /**
     * Store GPX data and calculated values in the trail record
     *
     * @param  array  $calculatedValues  ['distance' => float, 'elevation' => int, 'time' => float]
     */
    public function storeGpxData(Trail $trail, GpxFile $gpxObject, array $calculatedValues): Trail
    {
        // Use the display coordinates already computed in calculateAllFromGpx
        // (outbound half only for out-and-back trails)
        $coordinates = $calculatedValues['coordinates'];
        $isOutAndBack = $calculatedValues['is_out_and_back'] ?? false;
        $turnaroundPoint = $calculatedValues['turnaround_point'] ?? null;

        $trackPoints = $this->getAllTrackPoints($gpxObject);

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
                'is_out_and_back' => $isOutAndBack,
            ],
        ];

        // For out-and-back, end_coordinates is the turnaround point, not GPS track end
        $endCoordinates = $isOutAndBack && $turnaroundPoint !== null
            ? $turnaroundPoint
            : ($coordinates[count($coordinates) - 1] ?? null);

        $trail->update([
            'gpx_raw_data' => $gpxRawData,
            'gpx_calculated_distance' => $calculatedValues['distance'],
            'gpx_calculated_elevation' => $calculatedValues['elevation'],
            'gpx_calculated_time' => $calculatedValues['time'],
            'route_coordinates' => $coordinates,
            'data_source' => 'gpx',
            'gpx_uploaded_at' => now(),

            'distance_km' => $calculatedValues['distance'],
            'elevation_gain_m' => $calculatedValues['elevation'],
            'estimated_time_hours' => $calculatedValues['time'],

            'start_coordinates' => $trail->start_coordinates ?? $coordinates[0] ?? null,
            'end_coordinates' => $trail->end_coordinates ?? $endCoordinates,
        ]);

        return $trail->fresh();
    }

    /**
     * Calculate all values from a GPX file
     *
     * @return array ['distance' => float, 'elevation' => int, 'time' => float, 'coordinates' => array]
     *
     * @throws Exception
     */
    public function calculateAllFromGpx(string $filePath, float $difficulty = 3.0): array
    {
        $gpxFile = $this->parseGpxFile($filePath);
        $trackPoints = $this->getAllTrackPoints($gpxFile);

        if (empty($trackPoints)) {
            throw new Exception('No track points found in GPX file');
        }

        // Stats always use the full track (round-trip distance/elevation is correct)
        $distance = $this->calculateDistance($trackPoints);
        $elevation = $this->calculateElevation($trackPoints);
        $time = $this->estimateTime($distance, $elevation, $difficulty);

        // Extract raw coordinates (no deduplication yet) for accurate detection
        $rawCoordinates = [];
        foreach ($trackPoints as $point) {
            $coord = $this->extractCoordinates($point);
            $rawCoordinates[] = [$coord['lat'], $coord['lon']];
        }

        // Detect out-and-back on raw coordinates BEFORE any cleanup —
        // the circling pattern is what distinguishes a looping activity from a true out-and-back
        $isOutAndBack = $this->detectOutAndBack($rawCoordinates);
        $turnaroundPoint = null;

        // Now clean + simplify for display
        $allCoordinates = $this->extractRouteCoordinates($trackPoints);

        if ($isOutAndBack) {
            $outbound = $this->extractOutboundHalf($allCoordinates);
            $displayCoordinates = $outbound['coordinates'];
            $turnaroundPoint = $allCoordinates[$outbound['turnaround_index']];
        } else {
            $displayCoordinates = $allCoordinates;
        }

        return [
            'distance' => $distance,
            'elevation' => $elevation,
            'time' => $time,
            'coordinates' => $displayCoordinates,
            'is_out_and_back' => $isOutAndBack,
            'turnaround_point' => $turnaroundPoint,
            'point_count' => count($trackPoints),
            'gpx_file' => $gpxFile,
        ];
    }

    // ============================================
    // PRIVATE HELPER METHODS
    // ============================================

    /**
     * Get all track points from a GPX file
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
     * @param  mixed  $point
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
     * @param  mixed  $point
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
     * Simplify route using Ramer-Douglas-Peucker algorithm.
     * Keeps geometrically significant points (corners, direction changes)
     * rather than uniformly sampling, which produces a much cleaner result
     * especially for out-and-back trails.
     *
     * @param  array  $coordinates  Array of [lat, lng]
     * @param  int  $targetCount  Approximate max points (epsilon is auto-tuned)
     */
    private function simplifyRoute(array $coordinates, int $targetCount): array
    {
        $total = count($coordinates);

        if ($total <= $targetCount) {
            return $coordinates;
        }

        // Auto-tune epsilon: start with a small value and increase until we're
        // below the target point count.
        $epsilon = 0.00001;
        $result = $this->rdpSimplify($coordinates, $epsilon);

        while (count($result) > $targetCount && $epsilon < 1.0) {
            $epsilon *= 1.5;
            $result = $this->rdpSimplify($coordinates, $epsilon);
        }

        return $result;
    }

    /**
     * Remove repeated passes through the same area (circling/looping cleanup).
     * Divides space into ~80m grid cells and keeps at most 2 points per cell
     * (entry + exit), which eliminates ski-resort-style repeated runs and
     * GPS drift loops without affecting legitimate trail paths.
     *
     * @param  array  $coordinates  Array of [lat, lng]
     * @param  float  $cellMetres  Grid cell size in metres
     */
    public function removeLoopingSegments(array $coordinates, float $cellMetres = 80.0): array
    {
        if (count($coordinates) < 10) {
            return $coordinates;
        }

        $cellDeg = $cellMetres / 111000.0;

        $getCell = static function (array $pt) use ($cellDeg): string {
            return round($pt[0] / $cellDeg).','.round($pt[1] / $cellDeg);
        };

        $result = [$coordinates[0]];
        $cellVisits = []; // cell => count of times added to result

        $lastCell = $getCell($coordinates[0]);
        $cellVisits[$lastCell] = 1;

        foreach (array_slice($coordinates, 1) as $pt) {
            $cell = $getCell($pt);

            if ($cell !== $lastCell) {
                $visits = $cellVisits[$cell] ?? 0;

                // Allow up to 2 visits per cell (handles out-and-back retracing once)
                if ($visits < 2) {
                    $result[] = $pt;
                    $cellVisits[$cell] = $visits + 1;
                    $lastCell = $cell;
                }
                // If we've already been here twice, skip — it's circling
            }
            // Same cell as previous: skip intermediate GPS noise within the cell
        }

        // Always keep the last point
        $last = $coordinates[count($coordinates) - 1];
        if (end($result) !== $last) {
            $result[] = $last;
        }

        return $result;
    }

    /**
     * Ramer-Douglas-Peucker recursive simplification
     *
     * @param  array  $points  Array of [lat, lng]
     * @param  float  $epsilon  Tolerance in degrees
     */
    private function rdpSimplify(array $points, float $epsilon): array
    {
        $n = count($points);

        if ($n < 3) {
            return $points;
        }

        $maxDist = 0.0;
        $maxIndex = 0;

        $start = $points[0];
        $end = $points[$n - 1];

        for ($i = 1; $i < $n - 1; $i++) {
            $dist = $this->perpendicularDistance($points[$i], $start, $end);
            if ($dist > $maxDist) {
                $maxDist = $dist;
                $maxIndex = $i;
            }
        }

        if ($maxDist > $epsilon) {
            $left = $this->rdpSimplify(array_slice($points, 0, $maxIndex + 1), $epsilon);
            $right = $this->rdpSimplify(array_slice($points, $maxIndex), $epsilon);

            // Merge, avoiding duplicate at the split point
            return array_merge(array_slice($left, 0, -1), $right);
        }

        return [$start, $end];
    }

    /**
     * Perpendicular distance from a point to a line segment (in degrees, fast approximation)
     *
     * @param  array  $point  [lat, lng]
     * @param  array  $lineStart  [lat, lng]
     * @param  array  $lineEnd  [lat, lng]
     */
    private function perpendicularDistance(array $point, array $lineStart, array $lineEnd): float
    {
        $dx = $lineEnd[1] - $lineStart[1];
        $dy = $lineEnd[0] - $lineStart[0];

        if ($dx === 0.0 && $dy === 0.0) {
            // Start and end are the same point
            return sqrt(
                ($point[1] - $lineStart[1]) ** 2 +
                ($point[0] - $lineStart[0]) ** 2
            );
        }

        $t = (($point[1] - $lineStart[1]) * $dx + ($point[0] - $lineStart[0]) * $dy)
            / ($dx * $dx + $dy * $dy);

        $t = max(0.0, min(1.0, $t));

        $nearestX = $lineStart[1] + $t * $dx;
        $nearestY = $lineStart[0] + $t * $dy;

        return sqrt(($point[1] - $nearestX) ** 2 + ($point[0] - $nearestY) ** 2);
    }

    /**
     * Detect whether a track is an out-and-back trail.
     *
     * Three conditions must all pass:
     *   1. End point is within 150 m of start (GPS drift tolerance)
     *   2. Middle section (30–70%) has avg spread ≥ 150 m from its own centroid
     *      — excludes tight ski/circling activities (spread ~109 m)
     *   3. Return path average retrace distance < 120 m
     *      — return half of track closely follows the outbound half (loops don't)
     *
     * @param  array  $coordinates  Array of [lat, lng]
     */
    public function detectOutAndBack(array $coordinates): bool
    {
        $count = count($coordinates);

        if ($count < 10) {
            return false;
        }

        $start = $coordinates[0];
        $end = $coordinates[$count - 1];

        $startToEnd = $this->haversineDistance($start[0], $start[1], $end[0], $end[1]) * 1000;

        if ($startToEnd >= 150) {
            return false;
        }

        // Middle spread check: excludes tight ski/circling patterns
        $middleStart = (int) ($count * 0.3);
        $middleEnd = (int) ($count * 0.7);
        $middleSlice = array_slice($coordinates, $middleStart, $middleEnd - $middleStart);

        $centerLat = array_sum(array_column($middleSlice, 0)) / count($middleSlice);
        $centerLng = array_sum(array_column($middleSlice, 1)) / count($middleSlice);

        $avgDistFromCenter = 0.0;
        foreach ($middleSlice as $pt) {
            $avgDistFromCenter += $this->haversineDistance($centerLat, $centerLng, $pt[0], $pt[1]) * 1000;
        }
        $avgDistFromCenter /= count($middleSlice);

        if ($avgDistFromCenter < 150.0) {
            return false;
        }

        // Retrace check: the return path must closely follow the outbound path.
        // Sample both halves to keep this O(n) rather than O(n²).
        $halfIndex = (int) ($count / 2);
        $firstHalf = $coordinates;
        array_splice($firstHalf, $halfIndex);
        $secondHalf = array_slice($coordinates, $halfIndex);

        $sampleSize = 60;
        $step1 = max(1, (int) (count($firstHalf) / $sampleSize));
        $step2 = max(1, (int) (count($secondHalf) / $sampleSize));

        $sampledFirst = [];
        for ($i = 0; $i < count($firstHalf); $i += $step1) {
            $sampledFirst[] = $firstHalf[$i];
        }

        $totalRetrace = 0.0;
        $retraceCount = 0;
        for ($i = 0; $i < count($secondHalf); $i += $step2) {
            $pt = $secondHalf[$i];
            $minDist = PHP_FLOAT_MAX;
            foreach ($sampledFirst as $fp) {
                $d = $this->haversineDistance($pt[0], $pt[1], $fp[0], $fp[1]) * 1000;
                if ($d < $minDist) {
                    $minDist = $d;
                }
            }
            $totalRetrace += $minDist;
            $retraceCount++;
        }

        if ($retraceCount === 0) {
            return false;
        }

        $avgRetrace = $totalRetrace / $retraceCount;

        // Return path must stay within 130 m of the outbound path on average
        return $avgRetrace < 130.0;
    }

    /**
     * Return only the outbound half of the coordinates for an out-and-back trail.
     * The turnaround point is the point in the track that is farthest from the start.
     *
     * @param  array  $coordinates  Array of [lat, lng]
     * @return array ['coordinates' => array, 'turnaround_index' => int]
     */
    public function extractOutboundHalf(array $coordinates): array
    {
        $count = count($coordinates);
        $start = $coordinates[0];

        $maxDist = 0.0;
        $turnaroundIndex = (int) ($count / 2);

        // Search in the middle 60% of the track to avoid noise at start/end
        $searchStart = (int) ($count * 0.2);
        $searchEnd = (int) ($count * 0.8);

        for ($i = $searchStart; $i <= $searchEnd; $i++) {
            $dist = $this->haversineDistance($start[0], $start[1], $coordinates[$i][0], $coordinates[$i][1]);
            if ($dist > $maxDist) {
                $maxDist = $dist;
                $turnaroundIndex = $i;
            }
        }

        return [
            'coordinates' => array_slice($coordinates, 0, $turnaroundIndex + 1),
            'turnaround_index' => $turnaroundIndex,
        ];
    }

    /**
     * Extract a small set of key waypoints from a coordinate array for use
     * as draggable editor handles. Returns 8–12 structurally significant points:
     * start, evenly-spaced intermediates, and end.
     *
     * @param  array  $coordinates  Array of [lat, lng]
     * @param  int  $count  Target number of waypoints
     */
    public function extractKeyWaypoints(array $coordinates, int $count = 10): array
    {
        $total = count($coordinates);

        if ($total <= $count) {
            return $coordinates;
        }

        $waypoints = [$coordinates[0]];
        $step = ($total - 1) / ($count - 1);

        for ($i = 1; $i < $count - 1; $i++) {
            $waypoints[] = $coordinates[(int) round($i * $step)];
        }

        $waypoints[] = $coordinates[$total - 1];

        return $waypoints;
    }

    /**
     * Calculate statistics from GPX file (for preview/debugging)
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
                'has_elevation' => ! empty($elevations),
                'min_elevation' => ! empty($elevations) ? min($elevations) : null,
                'max_elevation' => ! empty($elevations) ? max($elevations) : null,
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
        if (! file_exists($filePath)) {
            throw new Exception('GPX file not found: '.$filePath);
        }

        $gpx = new \phpGPX\phpGPX;
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
            'estimated_processing_time' => $totalPoints > 1000 ? 'high' : ($totalPoints > 500 ? 'medium' : 'low'),
        ];
    }
}
