<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Tour;
use App\Models\Town;
use App\Models\Trail;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * "Explore Northern BC" — every town other than Smithers, framed as a road trip
 * out of Smithers rather than as a directory listing.
 */
class ExploreController extends Controller
{
    /**
     * Matches TownController: the payload only changes when an admin edits a
     * trail, business or tour, so this window is a backstop rather than the
     * real invalidation.
     */
    private const CACHE_MINUTES = 15;

    public const CACHE_KEY = 'explore-page';

    /**
     * The town every distance on this page is measured from.
     */
    private const ORIGIN_SLUG = 'smithers-bc';

    /**
     * Road distances run longer than the straight line, and increasingly so: a
     * hop down the Bulkley Valley follows Highway 16 almost exactly, while
     * Stewart is reached by driving west past Kitwanga and back up 37A. The
     * detour factor therefore grows with distance, from ~1.15 nearby to a
     * ceiling of 1.7, calibrated against the real highway distances.
     */
    private const DETOUR_BASE = 1.15;

    private const DETOUR_PER_KM = 1 / 500;

    private const DETOUR_MAX = 1.7;

    /**
     * Average northern-highway speed, plus a fixed allowance for getting out of
     * one town and into the next. Deliberately conservative: these are two-lane
     * highways with wildlife, weather and few passing lanes.
     */
    private const AVERAGE_SPEED_KMH = 80;

    private const TOWN_OVERHEAD_MINUTES = 5;

    /**
     * The page's spine. Every town falls into exactly one band by drive time,
     * and the filter chips, section headers and card badges all read from this
     * single definition.
     *
     * @var array<int, array{key: string, label: string, blurb: string, max_minutes: int|null}>
     */
    private const BANDS = [
        [
            'key' => 'day-trip',
            'label' => 'Day Trip',
            'blurb' => 'Under an hour from town. Leave after breakfast, be home for dinner.',
            'max_minutes' => 60,
        ],
        [
            'key' => 'half-day',
            'label' => 'Half Day',
            'blurb' => 'One to two and a half hours out. Worth an early start and a full tank.',
            'max_minutes' => 150,
        ],
        [
            'key' => 'weekend-escape',
            'label' => 'Weekend Escape',
            'blurb' => 'Far enough that you should book a bed. Pack for the whole weekend.',
            'max_minutes' => null,
        ],
    ];

    private const FEATURED_TRAIL_LIMIT = 8;

    private const LAKE_LIMIT = 8;

    public function index(): View
    {
        $data = Cache::remember(
            self::CACHE_KEY,
            now()->addMinutes(self::CACHE_MINUTES),
            fn () => $this->buildPayload()
        );

        return view('explore.index', $data + [
            'mapboxToken' => config('services.mapbox.access_token'),
        ]);
    }

    /**
     * Everything the page renders, gathered once so it caches as a unit.
     *
     * @return array<string, mixed>
     */
    private function buildPayload(): array
    {
        $origin = $this->origin();

        if (! $origin) {
            return $this->emptyPayload();
        }

        $destinations = $this->destinations($origin);

        /** @var list<int> $townIds */
        $townIds = $destinations->map(fn (array $destination): int => $destination['town']->id)->all();

        return [
            'origin' => $origin,
            'bands' => $this->groupIntoBands($destinations),
            'destinations' => $destinations,
            'stats' => $this->stats($destinations, $townIds),
            'featuredTrails' => $this->featuredTrails($townIds),
            'fishingLakes' => $this->fishingLakes($townIds),
            'businessesByTown' => $this->businessesByTown($destinations, $townIds),
            'tours' => $this->tours($townIds),
            'mapConfig' => $this->mapConfig($origin, $destinations),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyPayload(): array
    {
        return [
            'origin' => null,
            'bands' => collect(),
            'destinations' => collect(),
            'stats' => ['towns' => 0, 'trails' => 0, 'lakes' => 0, 'businesses' => 0],
            'featuredTrails' => new EloquentCollection,
            'fishingLakes' => new EloquentCollection,
            'businessesByTown' => collect(),
            'tours' => new EloquentCollection,
            'mapConfig' => null,
        ];
    }

    /**
     * Smithers, the town every distance here is measured from. Falls back to
     * the first ordered town so a renamed or re-slugged origin degrades into a
     * still-working page rather than a blank one.
     */
    private function origin(): ?Town
    {
        return Town::active()->where('slug', self::ORIGIN_SLUG)->first()
            ?? Town::active()->where('name', 'Smithers')->first()
            ?? Town::active()->ordered()->first();
    }

    /**
     * Every other town, decorated with its distance from the origin, its
     * content counts and a few named highlights. Ordered by drive time, which
     * is the order the page tells its story in.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function destinations(Town $origin): Collection
    {
        $towns = Town::active()
            ->ordered()
            ->whereKeyNot($origin->getKey())
            ->withCount([
                'trails as trails_count' => fn ($q) => $q->where('location_type', 'trail')->whereIn('status', ['active', 'seasonal']),
                'trails as lakes_count' => fn ($q) => $q->where('location_type', 'fishing_lake')->whereIn('status', ['active', 'seasonal']),
                'businesses as businesses_count' => fn ($q) => $q->where('is_active', true),
                'tours as tours_count' => fn ($q) => $q->where('is_active', true),
            ])
            ->get();

        return $towns
            ->map(function (Town $town) use ($origin): array {
                $straightKm = $origin->distanceTo((float) $town->latitude, (float) $town->longitude);
                $roadKm = $this->roadDistanceKm($straightKm);
                $minutes = $this->driveMinutes($roadKm);

                return [
                    'town' => $town,
                    'straight_km' => $straightKm,
                    'road_km' => (int) round($roadKm),
                    'drive_minutes' => $minutes,
                    'drive_label' => $this->driveLabel($minutes),
                    'band' => $this->bandKey($minutes),
                    'trails_count' => (int) $town->trails_count,
                    'lakes_count' => (int) $town->lakes_count,
                    'businesses_count' => (int) $town->businesses_count,
                    'tours_count' => (int) $town->tours_count,
                    'highlights' => $this->highlights($town),
                ];
            })
            ->sortBy('drive_minutes')
            ->values();
    }

    /**
     * Road kilometres implied by a straight-line distance.
     */
    private function roadDistanceKm(float $straightKm): float
    {
        $factor = min(self::DETOUR_MAX, self::DETOUR_BASE + ($straightKm * self::DETOUR_PER_KM));

        return $straightKm * $factor;
    }

    /**
     * Drive time in minutes, rounded to the nearest five so a badge never
     * implies more precision than a highway estimate can carry.
     */
    private function driveMinutes(float $roadKm): int
    {
        $minutes = ($roadKm / self::AVERAGE_SPEED_KMH) * 60 + self::TOWN_OVERHEAD_MINUTES;

        return max(5, (int) round($minutes / 5) * 5);
    }

    private function driveLabel(int $minutes): string
    {
        if ($minutes < 60) {
            return "{$minutes} min";
        }

        $hours = intdiv($minutes, 60);
        $remainder = $minutes % 60;

        return $remainder === 0 ? "{$hours} hr" : "{$hours} hr {$remainder} min";
    }

    private function bandKey(int $minutes): string
    {
        foreach (self::BANDS as $band) {
            if ($band['max_minutes'] === null || $minutes < $band['max_minutes']) {
                return $band['key'];
            }
        }

        return self::BANDS[array_key_last(self::BANDS)]['key'];
    }

    /**
     * The bands, each carrying the towns that fall into it. Empty bands are
     * kept so the filter chips stay in a stable order and can show a count.
     *
     * @param  Collection<int, array<string, mixed>>  $destinations
     * @return Collection<int, array<string, mixed>>
     */
    private function groupIntoBands(Collection $destinations): Collection
    {
        return collect(self::BANDS)->map(fn (array $band): array => $band + [
            'towns' => $destinations->where('band', $band['key'])->values(),
        ]);
    }

    /**
     * Two or three named things to actually go and see, so a card promises a
     * place rather than a number. Missing entries are dropped, which is what
     * keeps a town with nothing published from rendering empty rows.
     *
     * @return list<array{label: string, name: string, meta: string, url: string}>
     */
    private function highlights(Town $town): array
    {
        $trail = $town->publishedTrails()
            ->orderByDesc('is_featured')
            ->orderByDesc('view_count')
            ->first();

        $lake = $town->publishedFishingLakes()
            ->orderByDesc('is_featured')
            ->orderByDesc('view_count')
            ->first();

        $business = $town->businesses()
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->orderBy('name')
            ->first();

        return collect([
            $trail ? [
                'label' => 'Top trail',
                'name' => $trail->name,
                'meta' => $this->trailMeta($trail),
                'url' => route('trails.show', $trail->id),
            ] : null,
            $lake ? [
                'label' => 'Best lake',
                'name' => $lake->name,
                'meta' => $lake->best_fishing_season
                    ? 'Best in '.Str::lower($lake->best_fishing_season)
                    : 'Fishing lake',
                'url' => route('trails.show', $lake->id),
            ] : null,
            $business ? [
                'label' => 'Local stop',
                'name' => $business->name,
                'meta' => $business->tagline
                    ? Str::limit($business->tagline, 44)
                    : $business->business_type_label,
                'url' => route('businesses.public.show', $business->slug),
            ] : null,
        ])->filter()->values()->all();
    }

    private function trailMeta(Trail $trail): string
    {
        $parts = collect([
            $trail->distance_km ? number_format((float) $trail->distance_km, 1).' km' : null,
            $trail->difficulty_level ? $trail->difficulty_text : null,
        ])->filter()->join(' · ');

        return $parts !== '' ? $parts : 'Hiking trail';
    }

    /**
     * Live counts for the hero strip. Deliberately counts only what lies beyond
     * Smithers, because that is what the page is selling.
     *
     * @param  Collection<int, array<string, mixed>>  $destinations
     * @param  list<int>  $townIds
     * @return array<string, int>
     */
    private function stats(Collection $destinations, array $townIds): array
    {
        return [
            'towns' => $destinations->count(),
            'trails' => (int) $destinations->sum('trails_count'),
            'lakes' => (int) $destinations->sum('lakes_count'),
            'businesses' => Business::query()
                ->where('is_active', true)
                ->whereIn('town_id', $townIds)
                ->count(),
        ];
    }

    /**
     * @param  list<int>  $townIds
     * @return EloquentCollection<int, Trail>
     */
    private function featuredTrails(array $townIds): EloquentCollection
    {
        return Trail::query()
            ->where('location_type', 'trail')
            ->whereIn('status', ['active', 'seasonal'])
            ->whereIn('town_id', $townIds)
            ->with(['town', 'trailMedia' => $this->featuredPhotoConstraint()])
            ->orderByDesc('is_featured')
            ->orderByDesc('view_count')
            ->limit(self::FEATURED_TRAIL_LIMIT)
            ->get();
    }

    /**
     * @param  list<int>  $townIds
     * @return EloquentCollection<int, Trail>
     */
    private function fishingLakes(array $townIds): EloquentCollection
    {
        return Trail::query()
            ->where('location_type', 'fishing_lake')
            ->whereIn('status', ['active', 'seasonal'])
            ->whereIn('town_id', $townIds)
            ->with(['town', 'trailMedia' => $this->featuredPhotoConstraint()])
            ->orderByDesc('is_featured')
            ->orderByDesc('view_count')
            ->limit(self::LAKE_LIMIT)
            ->get();
    }

    /**
     * Only the featured photo (or the first one) is needed for a card, so the
     * eager load is narrowed rather than pulling every media row.
     */
    private function featuredPhotoConstraint(): callable
    {
        return function ($query): void {
            $query->where('media_type', 'photo')
                ->where(function ($inner): void {
                    $inner->where('is_featured', true)->orWhere('sort_order', 0);
                });
        };
    }

    /**
     * Active businesses in the destination towns, grouped by town so the
     * section reads as "while you are in Telkwa" rather than a flat directory.
     * Featured businesses lead within each town, and towns keep their
     * drive-time order.
     *
     * @param  Collection<int, array<string, mixed>>  $destinations
     * @param  list<int>  $townIds
     * @return Collection<int, array<string, mixed>>
     */
    private function businessesByTown(Collection $destinations, array $townIds): Collection
    {
        $businesses = Business::query()
            ->where('is_active', true)
            ->whereIn('town_id', $townIds)
            ->with('media')
            ->orderByDesc('is_featured')
            ->orderBy('name')
            ->get()
            ->groupBy('town_id');

        return $destinations
            ->filter(fn (array $destination): bool => $businesses->has($destination['town']->id))
            ->map(fn (array $destination): array => [
                'town' => $destination['town'],
                'drive_label' => $destination['drive_label'],
                'businesses' => $businesses->get($destination['town']->id),
            ])
            ->values();
    }

    /**
     * @param  list<int>  $townIds
     * @return EloquentCollection<int, Tour>
     */
    private function tours(array $townIds): EloquentCollection
    {
        return Tour::active()
            ->whereIn('town_id', $townIds)
            ->with('town')
            ->withCount('stops')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
    }

    /**
     * Bootstrap for the Mapbox teaser: the origin plus one marker per town in
     * its own colour. Coordinates stay [lat, lng] here to match the rest of the
     * app; the view swaps them to Mapbox's [lng, lat].
     *
     * @param  Collection<int, array<string, mixed>>  $destinations
     * @return array<string, mixed>
     */
    private function mapConfig(Town $origin, Collection $destinations): array
    {
        return [
            'origin' => [
                'name' => $origin->name,
                'color' => $origin->colorOrDefault(),
                'coordinates' => [(float) $origin->latitude, (float) $origin->longitude],
                'url' => route('towns.show', $origin),
            ],
            'towns' => $destinations->map(fn (array $destination): array => [
                'name' => $destination['town']->name,
                'color' => $destination['town']->colorOrDefault(),
                'coordinates' => [(float) $destination['town']->latitude, (float) $destination['town']->longitude],
                'drive' => $destination['drive_label'],
                'road_km' => $destination['road_km'],
                'url' => route('towns.show', $destination['town']),
            ])->all(),
        ];
    }
}
