<?php

namespace App\Support;

use App\Models\Facility;
use App\Models\TourStop;
use App\Models\Trail;
use App\Models\TrailFeature;
use Illuminate\Support\Collection;

/**
 * Shapes tour stops for the admin tour form's Alpine component: from saved stops,
 * or — after a failed save — from the submitted input, so the admin's edits survive.
 *
 * @phpstan-type FormStop array{
 *     trail_id: int|null, feature_id: int|null, facility_id: int|null,
 *     latitude: float|null, longitude: float|null, item_type: string,
 *     feature_type: string|null, facility_type_label: string|null, name: string,
 *     start_coordinates: array<int, float>|null, stop_label: string,
 *     estimated_visit_time: string, driving_notes: string, icon: string,
 *     icon_image: string, icon_image_url: string|null, description: string,
 *     existing_image: string, image_url: string|null,
 *     gallery: list<array{path: string, url: string}>, has_error: bool
 * }
 */
class TourStopFormData
{
    /**
     * @param  Collection<int, TourStop>  $stops
     * @return list<FormStop>
     */
    public static function fromStops(Collection $stops): array
    {
        return $stops->map(fn (TourStop $stop) => [
            'trail_id' => $stop->trail_id,
            'feature_id' => $stop->trail_feature_id,
            'facility_id' => $stop->facility_id,
            'latitude' => $stop->isPinned() ? $stop->latitude : null,
            'longitude' => $stop->isPinned() ? $stop->longitude : null,
            'item_type' => $stop->is_roaming ? 'roaming' : ($stop->isPinned() ? 'pinned' : ($stop->facility_id ? 'facility' : ($stop->trail_feature_id ? 'feature' : 'trail'))),
            'feature_type' => $stop->feature?->feature_type,
            'facility_type_label' => $stop->facility ? self::facilityTypeLabel($stop->facility) : null,
            'name' => $stop->is_roaming
                ? 'Roaming activity'
                : ($stop->isPinned()
                    ? 'Pinned on map'
                    : ($stop->feature?->name ?? ($stop->facility?->name ?? ($stop->trail?->name ?? 'Unknown')))),
            'start_coordinates' => $stop->is_roaming
                ? null
                : ($stop->isPinned()
                    ? [$stop->latitude, $stop->longitude]
                    : ($stop->feature?->coordinates
                        ?? ($stop->facility ? [(float) $stop->facility->latitude, (float) $stop->facility->longitude] : null)
                        ?? $stop->trail?->start_coordinates)),
            'stop_label' => $stop->stop_label ?? '',
            'estimated_visit_time' => $stop->estimated_visit_time ?? '',
            'driving_notes' => $stop->driving_notes ?? '',
            'icon' => $stop->icon ?? '',
            'icon_image' => $stop->icon_image ?? '',
            'icon_image_url' => $stop->icon_image_url,
            'description' => $stop->description ?? '',
            'existing_image' => $stop->image ?? '',
            'image_url' => $stop->image_url,
            'gallery' => self::galleryItems($stop->gallery ?? []),
            'has_error' => false,
        ])->values()->all();
    }

    /**
     * Rebuild the stops list from submitted form input. Linked trails, features and
     * facilities are looked up for their names/coordinates; unknown links are dropped.
     *
     * @param  array<int|string, mixed>  $input
     * @param  Collection<int, Trail>  $trails
     * @param  Collection<int, TrailFeature>  $features
     * @param  Collection<int, Facility>  $facilities
     * @param  list<int>  $stopsWithErrors  Positions of stops that failed validation
     * @return list<FormStop>
     */
    public static function fromOldInput(array $input, Collection $trails, Collection $features, Collection $facilities, array $stopsWithErrors = []): array
    {
        $trails = $trails->keyBy('id');
        $features = $features->keyBy('id');
        $facilities = $facilities->keyBy('id');
        $stops = [];

        foreach (array_values($input) as $position => $stop) {
            if (! is_array($stop)) {
                continue;
            }

            $trail = $trails->get((int) ($stop['trail_id'] ?? 0));
            $feature = $features->get((int) ($stop['feature_id'] ?? 0));
            $facility = $facilities->get((int) ($stop['facility_id'] ?? 0));
            $isRoaming = filter_var($stop['is_roaming'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $latitude = is_numeric($stop['latitude'] ?? null) ? (float) $stop['latitude'] : null;
            $longitude = is_numeric($stop['longitude'] ?? null) ? (float) $stop['longitude'] : null;
            $isPinned = ! $trail && ! $facility && $latitude !== null && $longitude !== null;

            $itemType = match (true) {
                $facility !== null => 'facility',
                $feature !== null => 'feature',
                $trail !== null => 'trail',
                $isPinned => 'pinned',
                $isRoaming => 'roaming',
                default => null,
            };

            if ($itemType === null) {
                continue;
            }

            $iconImage = self::string($stop, 'icon_image');
            $existingImage = self::string($stop, 'existing_image');
            $removeImage = filter_var($stop['remove_image'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $stops[] = [
                'trail_id' => $trail?->id,
                'feature_id' => $feature?->id,
                'facility_id' => $facility?->id,
                'latitude' => $isPinned ? $latitude : null,
                'longitude' => $isPinned ? $longitude : null,
                'item_type' => $itemType,
                'feature_type' => $feature?->feature_type,
                'facility_type_label' => $facility ? self::facilityTypeLabel($facility) : null,
                'name' => match ($itemType) {
                    'roaming' => 'Roaming activity',
                    'pinned' => 'Pinned on map',
                    'facility' => $facility->name,
                    'feature' => $feature->name,
                    default => $trail->name,
                },
                'start_coordinates' => match ($itemType) {
                    'roaming' => null,
                    'pinned' => [$latitude, $longitude],
                    'facility' => [(float) $facility->latitude, (float) $facility->longitude],
                    'feature' => $feature->coordinates,
                    default => $trail->start_coordinates,
                },
                'stop_label' => self::string($stop, 'stop_label'),
                'estimated_visit_time' => self::string($stop, 'estimated_visit_time'),
                'driving_notes' => self::string($stop, 'driving_notes'),
                'icon' => self::string($stop, 'icon'),
                'icon_image' => $iconImage,
                'icon_image_url' => $iconImage !== '' ? asset('storage/'.$iconImage) : null,
                'description' => self::string($stop, 'description'),
                'existing_image' => $removeImage ? '' : $existingImage,
                'image_url' => ! $removeImage && $existingImage !== '' ? asset('storage/'.$existingImage) : null,
                'gallery' => self::galleryItems(array_filter((array) ($stop['existing_gallery'] ?? []), 'is_string')),
                'has_error' => in_array($position, $stopsWithErrors, true),
            ];
        }

        return $stops;
    }

    /**
     * Positions of stops that have validation errors (from keys like `stops.2.description`).
     *
     * @param  array<string, mixed>  $errorKeys
     * @return list<int>
     */
    public static function positionsWithErrors(array $errorKeys): array
    {
        return collect(array_keys($errorKeys))
            ->map(fn (string $key) => preg_match('/^stops\.(\d+)/', $key, $match) ? (int) $match[1] : null)
            ->filter(fn (?int $position) => $position !== null)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $paths
     * @return list<array{path: string, url: string}>
     */
    private static function galleryItems(array $paths): array
    {
        return collect($paths)
            ->map(fn (string $path) => ['path' => $path, 'url' => asset('storage/'.$path)])
            ->values()
            ->all();
    }

    private static function facilityTypeLabel(Facility $facility): string
    {
        return Facility::getFacilityTypes()[$facility->facility_type] ?? $facility->facility_type;
    }

    /**
     * @param  array<string, mixed>  $stop
     */
    private static function string(array $stop, string $key): string
    {
        return is_scalar($stop[$key] ?? null) ? (string) $stop[$key] : '';
    }
}
