<?php

namespace App\Support;

use App\Models\EventStop;
use App\Models\TourStop;
use Illuminate\Support\Collection;

/**
 * Builds the data the shared route map (resources/views/tours/_map-script)
 * draws: the route line, the stop markers and the theme. Tours and events
 * both produce it, so one map script serves both pages and their embeds.
 */
class RouteMapPayload
{
    /**
     * @param  Collection<int, TourStop|EventStop>  $stops  Each stop provides toMapStop()
     * @param  list<array{0: float, 1: float}>|null  $route  [[lng, lat], ...]
     * @return array{stops: list<array<string, mixed>>, route: list<array{0: float, 1: float}>|null, iconImage: string|null, iconEmoji: string, isChristmas: bool, showPopups: bool}
     */
    public static function make(
        Collection $stops,
        ?array $route,
        ?string $iconImage,
        string $iconEmoji,
        bool $isChristmas,
        bool $showPopups,
    ): array {
        // Roaming activities have no map pin, so marker numbers count only the other stops.
        $stopNumber = 0;

        return [
            'stops' => $stops->values()
                ->map(function ($stop, int $index) use (&$stopNumber) {
                    return $stop->toMapStop($index, $stop->is_roaming ? null : ++$stopNumber);
                })
                ->all(),
            'route' => $route ?: null,
            'iconImage' => $iconImage,
            'iconEmoji' => $iconEmoji,
            'isChristmas' => $isChristmas,
            'showPopups' => $showPopups,
        ];
    }
}
