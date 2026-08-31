@extends('layouts.public')

@php
    $pageTitle = $town->seo_title ?: "Hiking Trails in {$town->name}, BC — Trails, Lakes & Things to Do";
    $pageDescription = $town->meta_description ?: Str::limit(
        strip_tags($town->intro) ?: "Discover {$hikingTrailsCount} hiking trails and {$fishingLakesCount} fishing lakes around {$town->name}, British Columbia. Maps, difficulty, distance and local businesses.",
        155
    );
    $heroUrl = $town->hero_image ? asset('storage/'.$town->hero_image) : null;
    $hasAnything = $hikingTrails->isNotEmpty() || $fishingLakes->isNotEmpty();
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageDescription)
@section('canonical', route('towns.show', $town))
@unless($shouldIndex)
    @section('robots', 'noindex,follow')
@endunless

@push('meta')
    @include('partials.seo-meta', [
        'title' => $pageTitle,
        'description' => $pageDescription,
        'image' => $heroUrl,
        'url' => route('towns.show', $town),
    ])
    @include('towns._structured-data')
@endpush

@push('styles')
<link href="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.css" rel="stylesheet">
<style>
    .town-map { height: 30rem; }
    @media (max-width: 1023px) { .town-map { height: 22rem; } }

    .tour-card-img { transition: transform 0.5s ease; }
    .tour-card:hover .tour-card-img { transform: scale(1.06); }
    .tour-card { transition: box-shadow 0.2s ease, transform 0.2s ease; }
    .tour-card:hover { transform: translateY(-3px); box-shadow: 0 20px 40px rgba(25, 54, 52, 0.14); }

    .town-intro p { margin-bottom: 1rem; }
    .town-intro p:last-child { margin-bottom: 0; }

    /* Editorial link rows for the nearby-town list — deliberately not another
       row of three identical cards. */
    .town-link-row { transition: background-color 0.25s ease, padding-left 0.25s ease; }
    .town-link-row:hover { background-color: #f5f8f7; padding-left: 1rem; }
    .town-link-row:hover .town-link-arrow { transform: translateX(0.35rem); }
    .town-link-arrow { transition: transform 0.25s ease; }
</style>
@endpush

@section('content')

{{-- Hero --}}
<section class="relative flex items-center justify-center hero-gradient overflow-hidden">
    @if($heroUrl)
        <img src="{{ $heroUrl }}"
             alt="Landscape near {{ $town->name }}, British Columbia"
             class="absolute inset-0 w-full h-full object-cover opacity-40 z-10">
    @endif
    <div class="absolute inset-0 bg-pattern-trees opacity-20 z-15"></div>
    <div class="absolute inset-0 bg-black bg-opacity-40 z-20"></div>

    <div class="relative z-30 text-center text-white max-w-6xl mx-auto px-4 flex flex-col justify-center py-20">

        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="mb-8 fade-in">
            <ol class="flex items-center justify-center gap-2 text-sm text-white/70">
                <li><a href="{{ route('home') }}" class="hover:text-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70 rounded">Home</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('towns.index') }}" class="hover:text-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70 rounded">Towns</a></li>
                <li aria-hidden="true">/</li>
                <li class="text-white font-medium" aria-current="page">{{ $town->name }}</li>
            </ol>
        </nav>

        {{-- Badge --}}
        <div class="mb-8 fade-in">
            <span class="inline-flex items-center px-6 py-3 bg-white/25 backdrop-blur-sm rounded-full text-white text-sm font-semibold border border-white/30 shadow-lg">
                📍 {{ $town->name }}, {{ $town->province }}
            </span>
        </div>

        {{-- Headline --}}
        <div class="slide-in-up mb-8">
            <h1 class="text-5xl md:text-7xl font-bold leading-tight text-balance">
                <span class="text-white text-shadow-lg">Hiking Trails in</span><br>
                <span class="bg-gradient-to-r from-emerald-300 via-sand-200 to-accent-300 bg-clip-text text-transparent">
                    {{ $town->name }}
                </span>
            </h1>
        </div>

        {{-- Subtitle --}}
        <div class="slide-in-up mb-12" style="animation-delay: 0.2s;">
            <p class="text-xl md:text-2xl text-white leading-relaxed max-w-3xl mx-auto text-shadow-md text-pretty">
                @if($town->tagline)
                    {{ $town->tagline }}
                @else
                    Trails, fishing lakes and local businesses around {{ $town->name }}, British Columbia.
                @endif
            </p>
        </div>

        @include('partials.app-promo-banner')

        {{-- Stats panel, reusing the glass treatment from the trails search bar --}}
        <div class="w-full max-w-4xl mx-auto scale-in" style="animation-delay: 0.4s;">
            <div class="bg-white/20 backdrop-blur-md rounded-2xl p-6 shadow-2xl border border-white/30">
                <dl class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <dd class="text-3xl md:text-4xl font-bold text-white tabular-nums">{{ $hikingTrailsCount }}</dd>
                        <dt class="text-xs uppercase tracking-[0.14em] text-white/70 mt-1">Trails</dt>
                    </div>
                    <div class="text-center">
                        <dd class="text-3xl md:text-4xl font-bold text-white tabular-nums">{{ $fishingLakesCount }}</dd>
                        <dt class="text-xs uppercase tracking-[0.14em] text-white/70 mt-1">Fishing lakes</dt>
                    </div>
                    <div class="text-center">
                        <dd class="text-3xl md:text-4xl font-bold text-white tabular-nums">{{ number_format($totalDistanceKm) }}</dd>
                        <dt class="text-xs uppercase tracking-[0.14em] text-white/70 mt-1">km mapped</dt>
                    </div>
                    <div class="text-center">
                        <dd class="text-3xl md:text-4xl font-bold text-white tabular-nums">{{ $tours->count() }}</dd>
                        <dt class="text-xs uppercase tracking-[0.14em] text-white/70 mt-1">Tours</dt>
                    </div>
                </dl>

                <div class="flex flex-col sm:flex-row gap-3 justify-center mt-6 pt-6 border-t border-white/20">
                    <a href="{{ route('map', ['town' => $town->slug]) }}" class="btn-primary">
                        Open the full map
                    </a>
                    @if($town->website_url)
                        <a href="{{ $town->website_url }}" target="_blank" rel="noopener"
                           class="bg-transparent border-2 border-white text-white hover:bg-white hover:text-forest-700 font-semibold py-3 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 active:scale-95 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-transparent">
                            Visit {{ $town->name }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

{{-- About + map, deliberately asymmetric rather than a centred block --}}
<section class="section bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid lg:grid-cols-12 gap-10 lg:gap-14 items-start">

            <div class="lg:col-span-5 lg:sticky lg:top-28">
                <div class="flex items-center gap-3 mb-6">
                    <span class="text-2xl" aria-hidden="true">🏔</span>
                    <h2 class="text-2xl font-bold text-gray-800">About {{ $town->name }}</h2>
                </div>

                @if($town->intro)
                    {{-- Blank lines in the admin textarea become real paragraphs,
                         so the copy is semantic rather than a run of <br> tags. --}}
                    <div class="town-intro text-gray-600 leading-relaxed max-w-[65ch] text-pretty">
                        @foreach(preg_split('/\R{2,}/', trim($town->intro)) as $paragraph)
                            <p>{!! nl2br(e(trim($paragraph))) !!}</p>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-600 leading-relaxed max-w-[65ch] text-pretty">
                        {{ $town->name }} sits in {{ $town->province }}, and everything mapped here is within
                        about {{ $town->radius_km }} km of town — trailheads, fishing lakes, facilities and the
                        businesses you'll want on the way out or back.
                    </p>
                @endif

                <a href="{{ route('map', ['town' => $town->slug]) }}"
                   class="group inline-flex items-center gap-1.5 mt-6 text-sm font-semibold text-emerald-600 hover:text-emerald-700 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 rounded">
                    Explore everything on the interactive map
                    <svg class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            <div class="lg:col-span-7">
                <div id="town-map"
                     class="town-map w-full rounded-2xl overflow-hidden shadow-xl ring-1 ring-forest-100 bg-forest-50"
                     role="application"
                     aria-label="Map of trails and facilities near {{ $town->name }}">
                    {{-- Skeleton, replaced once Mapbox paints --}}
                    <div id="town-map-skeleton" class="w-full h-full animate-pulse bg-gradient-to-br from-forest-50 via-sand-100 to-forest-100"></div>
                </div>
                <p class="mt-3 text-xs text-gray-500">
                    <span class="inline-block w-2 h-2 rounded-full bg-forest-600 align-middle" aria-hidden="true"></span> Trailheads
                    <span class="inline-block w-2 h-2 rounded-full bg-blue-500 align-middle ml-3" aria-hidden="true"></span> Fishing lakes
                    <span class="inline-block w-2 h-2 rounded-full bg-accent-500 align-middle ml-3" aria-hidden="true"></span> Facilities
                </p>
            </div>
        </div>
    </div>
</section>

{{-- Trails and lakes --}}
<section class="section bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">

        @if($hasAnything)
            <div class="text-center mb-12">
                <h2 class="section-title text-forest-600 text-balance">Where to hike near {{ $town->name }}</h2>
                <p class="section-subtitle text-pretty">
                    Every route here is mapped, graded and kept current. Distances and elevation come from
                    recorded GPS tracks, not estimates.
                </p>
            </div>
        @endif

        {{-- Hiking trails --}}
        <div class="flex items-center gap-3 mb-6">
            <span class="text-2xl" aria-hidden="true">🥾</span>
            <h3 class="text-2xl font-bold text-gray-800">Hiking trails</h3>
            <span class="text-sm text-gray-500 font-medium tabular-nums">({{ $hikingTrailsCount }})</span>
            <div class="flex-1 h-px bg-gray-200"></div>
            @if($hikingTrailsCount > $hikingTrails->count())
                <a href="{{ route('trails.index', ['town' => $town->slug]) }}"
                   class="text-sm font-semibold text-emerald-600 hover:text-emerald-700 transition-colors whitespace-nowrap focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 rounded">
                    See all {{ $hikingTrailsCount }}
                </a>
            @endif
        </div>

        @if($hikingTrails->isEmpty())
            <div class="text-center py-16 mb-14">
                <div class="max-w-md mx-auto">
                    <div class="w-24 h-24 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                    </div>
                    <h4 class="text-2xl font-bold text-gray-900 mb-4">No trails mapped here yet</h4>
                    <p class="text-gray-600 mb-8 text-pretty">
                        We're still recording tracks around {{ $town->name }}. In the meantime, the towns
                        below have routes ready to walk.
                    </p>
                    <a href="{{ route('towns.index') }}" class="btn-primary">Browse other towns</a>
                </div>
            </div>
        @else
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-14">
                @include('trails._cards', ['trails' => $hikingTrails, 'type' => 'hiking'])
            </div>
        @endif

        {{-- Fishing lakes --}}
        @if($fishingLakes->isNotEmpty())
            <div class="flex items-center gap-3 mb-6">
                <span class="text-2xl" aria-hidden="true">🎣</span>
                <h3 class="text-2xl font-bold text-gray-800">Fishing lakes</h3>
                <span class="text-sm text-gray-500 font-medium tabular-nums">({{ $fishingLakesCount }})</span>
                <div class="flex-1 h-px bg-gray-200"></div>
                @if($fishingLakesCount > $fishingLakes->count())
                    <a href="{{ route('fishing-lakes.index', ['town' => $town->slug]) }}"
                       class="text-sm font-semibold text-emerald-600 hover:text-emerald-700 transition-colors whitespace-nowrap focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 rounded">
                        See all {{ $fishingLakesCount }}
                    </a>
                @endif
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @include('trails._cards', ['trails' => $fishingLakes, 'type' => 'lakes'])
            </div>
        @endif
    </div>
</section>

{{-- Tours and businesses --}}
@if($tours->isNotEmpty() || $businesses->isNotEmpty())
<section class="section bg-white">
    <div class="max-w-7xl mx-auto px-4">

        @if($tours->isNotEmpty())
            <div class="flex items-center gap-3 mb-6">
                <span class="text-2xl" aria-hidden="true">🚗</span>
                <h3 class="text-2xl font-bold text-gray-800">Self-guided tours</h3>
                <span class="text-sm text-gray-500 font-medium tabular-nums">({{ $tours->count() }})</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 {{ $businesses->isNotEmpty() ? 'mb-14' : '' }}">
                @foreach($tours as $tour)
                    @include('tours._card', ['tour' => $tour])
                @endforeach
            </div>
        @endif

        @if($businesses->isNotEmpty())
            <div class="flex items-center gap-3 mb-6">
                <span class="text-2xl" aria-hidden="true">☕</span>
                <h3 class="text-2xl font-bold text-gray-800">Eat, stay and gear up</h3>
                <div class="flex-1 h-px bg-gray-200"></div>
                <a href="{{ route('businesses.public.index') }}"
                   class="text-sm font-semibold text-emerald-600 hover:text-emerald-700 transition-colors whitespace-nowrap focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 rounded">
                    All businesses
                </a>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($businesses as $business)
                    @include('businesses._card', ['business' => $business])
                @endforeach
            </div>
        @endif
    </div>
</section>
@endif

{{-- Events and nearby towns --}}
@if($events->isNotEmpty() || $nearbyTowns->isNotEmpty())
<section class="section bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid lg:grid-cols-12 gap-10 lg:gap-16 items-start">

            @if($events->isNotEmpty())
                <div class="{{ $nearbyTowns->isNotEmpty() ? 'lg:col-span-7' : 'lg:col-span-12' }}">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="text-2xl" aria-hidden="true">📅</span>
                        <h3 class="text-2xl font-bold text-gray-800">What's on in {{ $town->name }}</h3>
                        <div class="flex-1 h-px bg-gray-200"></div>
                    </div>

                    <ul class="space-y-3">
                        @foreach($events as $event)
                            <li>
                                <a href="{{ route('events.show', $event) }}"
                                   class="group flex items-start gap-5 bg-white rounded-xl border-l-4 border-accent-500 p-5 shadow-sm hover:shadow-lg transition-all duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent-500 focus-visible:ring-offset-2">
                                    <time class="flex-shrink-0 text-center leading-none pt-0.5" datetime="{{ $event->event_date?->toDateString() }}">
                                        <span class="block text-xs font-semibold uppercase tracking-wide text-accent-600">{{ $event->event_date?->format('M') }}</span>
                                        <span class="block text-2xl font-bold text-gray-900 tabular-nums">{{ $event->event_date?->format('j') }}</span>
                                    </time>
                                    <span class="min-w-0">
                                        <span class="block font-bold text-gray-900 group-hover:text-accent-600 transition-colors">{{ $event->title }}</span>
                                        @if($event->venue)
                                            <span class="block text-sm text-gray-500 mt-0.5">{{ $event->venue }}</span>
                                        @endif
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($nearbyTowns->isNotEmpty())
                <div class="{{ $events->isNotEmpty() ? 'lg:col-span-5' : 'lg:col-span-12' }}">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="text-2xl" aria-hidden="true">🧭</span>
                        <h3 class="text-2xl font-bold text-gray-800">Explore nearby</h3>
                        <div class="flex-1 h-px bg-gray-200"></div>
                    </div>

                    <ul class="divide-y divide-gray-200 border-y border-gray-200">
                        @foreach($nearbyTowns as $nearby)
                            <li>
                                <a href="{{ route('towns.show', $nearby) }}"
                                   class="town-link-row group flex items-center justify-between gap-4 py-5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 rounded">
                                    <span class="min-w-0">
                                        <span class="block text-lg font-bold text-gray-900 group-hover:text-forest-700 transition-colors">
                                            {{ $nearby->name }}, {{ $nearby->province_code }}
                                        </span>
                                        @if($nearby->tagline)
                                            <span class="block text-sm text-gray-500 mt-0.5 line-clamp-1">{{ $nearby->tagline }}</span>
                                        @endif
                                    </span>
                                    <svg class="town-link-arrow w-5 h-5 flex-shrink-0 text-gray-400 group-hover:text-forest-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</section>
@endif

{{-- Closing call to action --}}
<section class="section cta-section">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-4xl font-bold text-white mb-6 text-balance">Planning a trip to {{ $town->name }}?</h2>
        <p class="text-xl text-emerald-100 mb-8 text-pretty">
            Every route, lake and facility around town on one map — with offline access in the app when
            the cell signal runs out.
        </p>
        <div class="flex flex-col md:flex-row gap-4 justify-center">
            <a href="{{ route('map', ['town' => $town->slug]) }}"
               class="bg-transparent border-2 border-white text-white hover:bg-white hover:text-forest-700 font-semibold py-4 px-10 rounded-xl transition-all duration-300 text-lg shadow-xl hover:scale-105 active:scale-95">
                View {{ $town->name }} on the map
            </a>
            <a href="{{ route('towns.index') }}"
               class="bg-white text-forest-600 hover:bg-forest-50 font-semibold py-4 px-10 rounded-xl transition-all duration-300 text-lg shadow-xl hover:scale-105 active:scale-95">
                Browse all towns
            </a>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script src="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.js"></script>
<script>
(function () {
    const markers = @json($mapMarkers);
    const token = @json($mapboxToken);
    const skeleton = document.getElementById('town-map-skeleton');

    if (!token || !window.mapboxgl) {
        return;
    }

    mapboxgl.accessToken = token;

    // Stored coordinates are [lat, lng]; Mapbox wants [lng, lat].
    const toLngLat = (coords) => [coords[1], coords[0]];

    const map = new mapboxgl.Map({
        container: 'town-map',
        style: 'mapbox://styles/mapbox/outdoors-v12',
        center: toLngLat(markers.center),
        zoom: markers.zoom,
        cooperativeGestures: true
    });

    map.addControl(new mapboxgl.NavigationControl(), 'top-right');
    map.addControl(new mapboxgl.FullscreenControl(), 'top-right');

    map.on('load', function () {
        if (skeleton) {
            skeleton.remove();
        }

        const bounds = new mapboxgl.LngLatBounds();
        bounds.extend(toLngLat(markers.center));

        markers.trails.forEach(function (trail) {
            const lngLat = toLngLat(trail.coordinates);
            const isLake = trail.type === 'fishing_lake';

            const el = document.createElement('button');
            el.type = 'button';
            el.setAttribute('aria-label', trail.name);
            el.style.cssText = 'width:30px;height:30px;border-radius:50%;display:flex;align-items:center;'
                + 'justify-content:center;font-size:14px;cursor:pointer;padding:0;'
                + 'box-shadow:0 2px 8px rgba(25,54,52,.35);transition:transform .2s ease;'
                + 'background:' + (isLake ? '#3b82f6' : '#2C5F5D') + ';border:2px solid #fff;';
            el.textContent = isLake ? '\u{1F41F}' : '\u{1F97E}';
            el.addEventListener('mouseenter', () => { el.style.transform = 'scale(1.15)'; });
            el.addEventListener('mouseleave', () => { el.style.transform = 'scale(1)'; });

            const popup = new mapboxgl.Popup({ offset: 18 }).setHTML(
                '<div style="font-weight:700;margin-bottom:4px;">' + trail.name.replace(/</g, '&lt;') + '</div>'
                + '<a href="' + trail.url + '" style="color:#2C5F5D;font-size:12px;font-weight:600;">View details &rarr;</a>'
            );

            new mapboxgl.Marker({ element: el }).setLngLat(lngLat).setPopup(popup).addTo(map);
            bounds.extend(lngLat);
        });

        markers.facilities.forEach(function (facility) {
            const lngLat = toLngLat(facility.coordinates);
            const el = document.createElement('div');
            el.style.cssText = 'width:14px;height:14px;border-radius:50%;background:#E87B35;'
                + 'border:2px solid #fff;box-shadow:0 1px 5px rgba(25,54,52,.35);';

            new mapboxgl.Marker({ element: el })
                .setLngLat(lngLat)
                .setPopup(new mapboxgl.Popup({ offset: 12 }).setText(facility.name))
                .addTo(map);
            bounds.extend(lngLat);
        });

        if (markers.trails.length || markers.facilities.length) {
            map.fitBounds(bounds, { padding: 60, maxZoom: 12, duration: 0 });
        }
    });
})();
</script>
@endpush
