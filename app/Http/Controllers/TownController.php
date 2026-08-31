<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Facility;
use App\Models\Tour;
use App\Models\Town;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class TownController extends Controller
{
    /**
     * How long an assembled town page payload stays cached. Town content only
     * changes when an admin edits a trail or business, and those saves flush
     * the key, so this is really just a backstop.
     */
    private const CACHE_MINUTES = 15;

    /**
     * Landing pages carry the trail cards, so they are capped rather than
     * paginated; the "see all" links hand off to /trails?town=.
     */
    private const TRAIL_LIMIT = 12;

    private const LAKE_LIMIT = 6;

    /**
     * Hub page listing every town that has a landing page.
     */
    public function index(): View
    {
        $towns = Town::active()
            ->ordered()
            ->withCount([
                'trails as hiking_trails_count' => fn ($q) => $q->where('location_type', 'trail')->whereIn('status', ['active', 'seasonal']),
                'trails as fishing_lakes_count' => fn ($q) => $q->where('location_type', 'fishing_lake')->whereIn('status', ['active', 'seasonal']),
            ])
            ->get();

        return view('towns.index', compact('towns'));
    }

    /**
     * A single town's landing page.
     */
    public function show(Town $town): View
    {
        abort_unless($town->is_active, 404);

        $data = Cache::remember(
            self::cacheKey($town),
            now()->addMinutes(self::CACHE_MINUTES),
            fn () => $this->buildPayload($town)
        );

        return view('towns.show', $data + [
            'town' => $town,
            'mapboxToken' => config('services.mapbox.access_token'),
        ]);
    }

    public static function cacheKey(Town $town): string
    {
        return "town-page:{$town->slug}";
    }

    /**
     * Everything the landing page renders, gathered in one place so it can be
     * cached as a unit.
     *
     * @return array<string, mixed>
     */
    private function buildPayload(Town $town): array
    {
        $mediaWith = ['trailMedia' => function ($q) {
            $q->where('media_type', 'photo')
                ->where(function ($q2) {
                    $q2->where('is_featured', true)->orWhere('sort_order', 0);
                });
        }];

        $hikingTrails = $town->publishedTrails()
            ->with($mediaWith)
            ->orderByDesc('is_featured')
            ->orderByDesc('view_count')
            ->limit(self::TRAIL_LIMIT)
            ->get();

        $fishingLakes = $town->publishedFishingLakes()
            ->with($mediaWith)
            ->orderByDesc('is_featured')
            ->orderByDesc('view_count')
            ->limit(self::LAKE_LIMIT)
            ->get();

        return [
            'hikingTrails' => $hikingTrails,
            'fishingLakes' => $fishingLakes,
            'hikingTrailsCount' => $town->publishedTrails()->count(),
            'fishingLakesCount' => $town->publishedFishingLakes()->count(),
            'totalDistanceKm' => (float) $town->publishedTrails()->sum('distance_km'),
            'tours' => Tour::active()->inTown($town)->withCount('stops')->orderBy('sort_order')->orderBy('title')->get(),
            'businesses' => $town->nearbyBusinesses(6),
            'events' => $this->eventsNear($town),
            'mapMarkers' => $this->mapMarkers($town, $hikingTrails, $fishingLakes),
            'nearbyTowns' => $this->nearbyTowns($town),
            'shouldIndex' => $town->shouldIndex(),
        ];
    }

    /**
     * Upcoming events that mention the town. Event location and venue are
     * scraped free text, so this is a best-effort match rather than a join.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Event>
     */
    private function eventsNear(Town $town)
    {
        return Event::query()
            ->upcoming()
            ->where('is_active', true)
            ->where(function ($q) use ($town) {
                $q->where('location', 'like', "%{$town->name}%")
                    ->orWhere('venue', 'like', "%{$town->name}%");
            })
            ->orderBy('event_date')
            ->limit(3)
            ->get();
    }

    /**
     * Marker payload for the mini map, injected into the page so the map does
     * not need a second round trip. Coordinates stay [lat, lng] here; the view
     * swaps them to Mapbox's [lng, lat] order.
     *
     * @return array<string, mixed>
     */
    private function mapMarkers(Town $town, $hikingTrails, $fishingLakes): array
    {
        $trailMarkers = $hikingTrails->concat($fishingLakes)
            ->filter(fn ($trail) => $trail->start_latitude !== null)
            ->map(fn ($trail) => [
                'id' => $trail->id,
                'name' => $trail->name,
                'type' => $trail->location_type,
                'url' => route('trails.show', $trail->id),
                'coordinates' => [(float) $trail->start_latitude, (float) $trail->start_longitude],
            ])->values()->all();

        $facilityMarkers = Facility::query()
            ->where('is_active', true)
            ->inTown($town)
            ->limit(50)
            ->get()
            ->map(fn ($facility) => [
                'id' => $facility->id,
                'name' => $facility->name,
                'type' => $facility->facility_type,
                'coordinates' => [(float) $facility->latitude, (float) $facility->longitude],
            ])->values()->all();

        return [
            'center' => [(float) $town->latitude, (float) $town->longitude],
            'zoom' => $town->map_zoom,
            'trails' => $trailMarkers,
            'facilities' => $facilityMarkers,
        ];
    }

    /**
     * The three closest other towns, for cross-linking between landing pages.
     *
     * @return Collection<int, Town>
     */
    private function nearbyTowns(Town $town)
    {
        return Town::active()
            ->whereKeyNot($town->getKey())
            ->get()
            ->sortBy(fn (Town $other) => $town->distanceTo((float) $other->latitude, (float) $other->longitude))
            ->take(3)
            ->values();
    }
}
