<?php

namespace App\Support;

class GeoDistance
{
    private const EARTH_RADIUS_KM = 6371.0;

    /**
     * Great-circle (straight-line) distance between two [lat, lng] points.
     *
     * @param  array{0: float, 1: float}  $from
     * @param  array{0: float, 1: float}  $to
     */
    public static function kilometersBetween(array $from, array $to): float
    {
        [$lat1, $lng1] = $from;
        [$lat2, $lng2] = $to;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return self::EARTH_RADIUS_KM * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
