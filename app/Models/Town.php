<?php

namespace App\Models;

use App\Observers\TownContentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[ObservedBy(TownContentObserver::class)]
class Town extends Model
{
    use HasFactory;

    /**
     * Mean radius of the Earth in kilometres, used by the equirectangular
     * distance approximation. At Bulkley Valley latitudes the error against a
     * full Haversine is well under a metre over the radii we query.
     */
    public const EARTH_RADIUS_KM = 6371.0;

    protected $fillable = [
        'name',
        'slug',
        'province',
        'province_code',
        'latitude',
        'longitude',
        'radius_km',
        'map_zoom',
        'tagline',
        'intro',
        'seo_title',
        'meta_description',
        'hero_image',
        'website_url',
        'sort_order',
        'is_active',
        'is_indexable',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'radius_km' => 'integer',
            'map_zoom' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'is_indexable' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function trails(): HasMany
    {
        return $this->hasMany(Trail::class);
    }

    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class);
    }

    public function facilities(): HasMany
    {
        return $this->hasMany(Facility::class);
    }

    public function tours(): HasMany
    {
        return $this->hasMany(Tour::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Hiking trails inside this town that are visible on the public site.
     */
    public function publishedTrails(): Builder
    {
        return $this->trails()->getQuery()
            ->where('location_type', 'trail')
            ->whereIn('status', ['active', 'seasonal']);
    }

    /**
     * Fishing lakes inside this town that are visible on the public site.
     */
    public function publishedFishingLakes(): Builder
    {
        return $this->trails()->getQuery()
            ->where('location_type', 'fishing_lake')
            ->whereIn('status', ['active', 'seasonal']);
    }

    /**
     * A Mapbox Static Images URL showing this town's terrain.
     *
     * Towns rarely have a hero photo, and a flat placeholder block reads as
     * broken. The static map is real, recognisable content that differs per
     * town and matches the rest of the site. Attribution is suppressed here
     * and shown once per section instead, as Mapbox's terms require.
     *
     * Large decorative panels can pass $retina = false: the image sits under a
     * heavy scrim and a contour overlay, so the extra pixels only cost bytes.
     */
    public function staticMapUrl(int $width = 600, int $height = 400, ?int $zoom = null, bool $retina = true): ?string
    {
        $token = config('services.mapbox.access_token');

        if (! $token) {
            return null;
        }

        return sprintf(
            'https://api.mapbox.com/styles/v1/mapbox/outdoors-v12/static/%F,%F,%d,0,40/%dx%d%s?logo=false&attribution=false&access_token=%s',
            (float) $this->longitude,
            (float) $this->latitude,
            $zoom ?? max(1, $this->map_zoom - 2),
            $width,
            $height,
            $retina ? '@2x' : '',
            $token
        );
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->name}, {$this->province_code}";
    }

    /**
     * Whether search engines should index this town's landing page.
     *
     * An explicit admin choice always wins. Left unset, a town is only indexed
     * once it actually has trails to show, so an empty page never gets
     * submitted to Google as thin content.
     */
    public function shouldIndex(): bool
    {
        if ($this->is_indexable !== null) {
            return $this->is_indexable;
        }

        return $this->publishedTrails()->exists();
    }

    /**
     * Businesses to show on the town page.
     *
     * Prefers businesses assigned to this town. When the town has none of its
     * own (most of the directory is Smithers-based), it falls back to the
     * nearest active businesses, but only those close enough to be genuinely
     * useful to a visitor.
     *
     * @return Collection<int, Business>
     */
    public function nearbyBusinesses(int $limit = 6): Collection
    {
        $own = $this->businesses()->where('is_active', true)
            ->orderByDesc('is_featured')
            ->orderBy('name')
            ->limit($limit)
            ->get();

        if ($own->isNotEmpty()) {
            return $own;
        }

        return Business::query()
            ->where('is_active', true)
            ->select('*')
            ->selectRaw($this->distanceExpression('latitude', 'longitude').' as distance_km')
            /**
             * whereRaw rather than having, because SQLite rejects HAVING on a
             * non-aggregate query. The radius is formatted into the SQL rather
             * than bound: PDO binds floats as TEXT, and SQLite sorts any TEXT
             * above every REAL, which would make the comparison always true.
             */
            ->whereRaw(sprintf('%s <= %F', $this->distanceExpression('latitude', 'longitude'), $this->fallbackRadiusKm()))
            ->orderByRaw($this->distanceExpression('latitude', 'longitude'))
            ->limit($limit)
            ->get();
    }

    /**
     * How far out the business fallback is allowed to reach. Double the town's
     * own radius keeps a neighbouring town's directory in play while still
     * showing nothing at all for a genuinely remote town such as Stewart.
     */
    public function fallbackRadiusKm(): float
    {
        return $this->radius_km * 2;
    }

    /**
     * SQL expression giving the distance in kilometres from this town's centre
     * to the given latitude/longitude columns.
     */
    public function distanceExpression(string $latColumn, string $lngColumn): string
    {
        $lat = (float) $this->latitude;
        $lng = (float) $this->longitude;
        $kmPerDegree = deg2rad(1) * self::EARTH_RADIUS_KM;
        $lngScale = cos(deg2rad($lat));

        return sprintf(
            '(%F * SQRT(POW(%s - %F, 2) + POW((%s - %F) * %F, 2)))',
            $kmPerDegree,
            $latColumn,
            $lat,
            $lngColumn,
            $lng,
            $lngScale
        );
    }

    /**
     * Great-circle-ish distance in kilometres from this town's centre.
     */
    public function distanceTo(float $latitude, float $longitude): float
    {
        $lat = (float) $this->latitude;
        $lng = (float) $this->longitude;
        $kmPerDegree = deg2rad(1) * self::EARTH_RADIUS_KM;

        $dLat = $latitude - $lat;
        $dLng = ($longitude - $lng) * cos(deg2rad($lat));

        return $kmPerDegree * sqrt($dLat ** 2 + $dLng ** 2);
    }

    protected static function booted(): void
    {
        static::creating(function (Town $town) {
            if (empty($town->slug)) {
                $base = Str::slug($town->name.' '.$town->province_code);
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $base.'-'.++$i;
                }
                $town->slug = $slug;
            }
        });
    }
}
