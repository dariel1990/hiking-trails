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
            'mapMarkers' => $this->mapMarkers($town),
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
     * Bootstrap payload for the mini map.
     *
     * Trails are not inlined: the page fetches /api/trails?town={slug} exactly
     * as the main interactive map does, so both maps share one source of truth
     * for icons, route geometry and activity data. Only the small, town-scoped
     * pieces the API cannot supply are passed here.
     *
     * Coordinates stay [lat, lng]; the view swaps them to Mapbox's [lng, lat].
     *
     * @return array<string, mixed>
     */
    private function mapMarkers(Town $town): array
    {
        $facilities = Facility::query()
            ->where('is_active', true)
            ->inTown($town)
            ->orderBy('name')
            ->get(['id', 'name', 'facility_type', 'latitude', 'longitude'])
            ->map(fn ($facility) => [
                'id' => $facility->id,
                'name' => $facility->name,
                'type' => $facility->facility_type,
                'coordinates' => [(float) $facility->latitude, (float) $facility->longitude],
            ])->values()->all();

        return [
            'center' => [(float) $town->latitude, (float) $town->longitude],
            'zoom' => $town->map_zoom,
            'townSlug' => $town->slug,
            // Built once so the view does not repeat a route() call per pin.
            'trailUrlTemplate' => route('trails.show', ['trail' => '__ID__']),
            'facilities' => $facilities,
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
