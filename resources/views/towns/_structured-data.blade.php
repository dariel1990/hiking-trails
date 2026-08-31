{{--
    Schema.org markup for a town landing page.

    Emits a breadcrumb trail, the town itself as a tourist destination, and an
    ItemList of its trails so search engines can surface individual trails.
    Expects $town, $hikingTrails, $fishingLakes, $businesses, $pageDescription.
--}}
@php
    $graph = [
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Towns', 'item' => route('towns.index')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $town->name, 'item' => route('towns.show', $town)],
            ],
        ],
        array_filter([
            '@type' => 'TouristDestination',
            'name' => "{$town->name}, {$town->province}",
            'description' => $pageDescription,
            'url' => route('towns.show', $town),
            'image' => $heroUrl ?? null,
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) $town->latitude,
                'longitude' => (float) $town->longitude,
            ],
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => $town->name,
                'addressRegion' => $town->province_code,
                'addressCountry' => 'CA',
            ],
        ]),
    ];

    $trailItems = $hikingTrails->concat($fishingLakes)->values();

    if ($trailItems->isNotEmpty()) {
        $graph[] = [
            '@type' => 'ItemList',
            'name' => "Trails and lakes near {$town->name}, BC",
            'numberOfItems' => $trailItems->count(),
            'itemListElement' => $trailItems->map(fn ($trail, $index) => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'item' => array_filter([
                    '@type' => 'TouristAttraction',
                    'name' => $trail->name,
                    'url' => route('trails.show', $trail->id),
                    'description' => $trail->description ? Str::limit(strip_tags($trail->description), 200) : null,
                    'geo' => $trail->start_latitude === null ? null : [
                        '@type' => 'GeoCoordinates',
                        'latitude' => (float) $trail->start_latitude,
                        'longitude' => (float) $trail->start_longitude,
                    ],
                ]),
            ])->all(),
        ];
    }

    foreach ($businesses as $business) {
        $graph[] = array_filter([
            '@type' => 'LocalBusiness',
            'name' => $business->name,
            'url' => route('businesses.public.show', $business->slug),
            'description' => $business->description ? Str::limit(strip_tags($business->description), 200) : null,
            'address' => $business->address ? [
                '@type' => 'PostalAddress',
                'streetAddress' => $business->address,
                'addressRegion' => 'BC',
                'addressCountry' => 'CA',
            ] : null,
            'telephone' => $business->phone,
            'priceRange' => $business->price_range,
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) $business->latitude,
                'longitude' => (float) $business->longitude,
            ],
        ]);
    }

    /**
     * Encoded inside this @php block on purpose: an "@context" key written in
     * plain Blade markup is compiled as the @context directive instead.
     */
    $structuredData = json_encode(
        ['@context' => 'https://schema.org', '@graph' => $graph],
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );
@endphp
<script type="application/ld+json">
{!! $structuredData !!}
</script>
