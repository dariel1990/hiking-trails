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

    /* Marker treatment copied from the main interactive map so a trail looks
       the same wherever it is plotted. */
    .selectable-marker-el.selected {
        transform: scale(1.35);
        box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.55), 0 4px 14px rgba(0, 0, 0, 0.55) !important;
    }

    /* Layer switcher — segmented control, top left of the map. */
    .town-layer-switch {
        display: flex;
        gap: 2px;
        padding: 3px;
        margin: 10px;
        border-radius: 9px;
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(6px);
        box-shadow: 0 2px 10px rgba(25, 54, 52, 0.28);
    }

    .town-layer-switch button {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 11px;
        border: 0;
        border-radius: 7px;
        background: transparent;
        color: #4b5563;
        font: 600 12px/1 inherit;
        cursor: pointer;
        transition: background 0.2s ease, color 0.2s ease;
    }

    .town-layer-switch button:hover { background: rgba(44, 95, 93, 0.09); color: #193634; }
    .town-layer-switch button[aria-pressed="true"] { background: #2C5F5D; color: #fff; }
    .town-layer-switch button:focus-visible { outline: 2px solid #2C5F5D; outline-offset: 2px; }

    /* Mapbox pads and rounds its popup by default, which fights an
       edge-to-edge photo. Reset the shell and let the card own its own inset. */
    #town-map .mapboxgl-popup-content {
        padding: 0;
        border-radius: 0.75rem;
        overflow: hidden;
        box-shadow: 0 12px 32px -8px rgba(25, 54, 52, 0.35);
    }

    #town-map .mapboxgl-popup-close-button {
        top: 4px;
        right: 6px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        color: #fff;
        font-size: 17px;
        line-height: 1;
        background: rgba(25, 54, 52, 0.55);
        transition: background 0.2s ease;
    }

    #town-map .mapboxgl-popup-close-button:hover {
        background: rgba(25, 54, 52, 0.85);
    }

    #town-map .town-popup { width: 236px; }

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
/**
 * Town mini map.
 *
 * Deliberately mirrors the main interactive map: same /api/trails source, same
 * marker element, same icon precedence, and the same three route layers that
 * reveal a trail's line only once it is selected. Keeping the two in step means
 * a trail looks identical wherever a visitor meets it.
 */
(function () {
    const config = @json($mapMarkers);
    const token = @json($mapboxToken);
    const hikingBootIcon = @json(asset('images/hiking-boot.png'));
    const xploreLogo = @json(asset('images/xplore-smithers-logo.png'));
    const skeleton = document.getElementById('town-map-skeleton');

    if (!token || !window.mapboxgl) {
        return;
    }

    const MARKER_COLOR = '#1B3935';
    const ROUTE_COLOR = '#22c55e';

    mapboxgl.accessToken = token;

    // Stored coordinates are [lat, lng]; Mapbox wants [lng, lat].
    const toLngLat = (coords) => [coords[1], coords[0]];

    const sanitize = (coords) => {
        if (!coords) {
            return null;
        }

        if (Array.isArray(coords) && coords.length >= 2 && typeof coords[0] === 'number' && typeof coords[1] === 'number') {
            return [coords[0], coords[1]];
        }

        if (Array.isArray(coords) && Array.isArray(coords[0])) {
            return sanitize(coords[0]);
        }

        return null;
    };

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    })[character]);

    const MAP_STYLES = {
        standard: 'mapbox://styles/mapbox/standard',
        satellite: 'mapbox://styles/mapbox/satellite-streets-v12'
    };

    let currentStyle = 'standard';

    const map = new mapboxgl.Map({
        container: 'town-map',
        style: MAP_STYLES[currentStyle],
        center: toLngLat(config.center),
        zoom: config.zoom,
        cooperativeGestures: true
    });

    map.addControl(new mapboxgl.NavigationControl(), 'top-right');
    map.addControl(new mapboxgl.FullscreenControl(), 'top-right');

    /**
     * Two-option layer switcher. Swapping the style tears out every custom
     * source and layer, which is why installMapLayers() below is bound to
     * style.load rather than run once.
     */
    map.addControl({
        onAdd: function () {
            const container = document.createElement('div');
            container.className = 'mapboxgl-ctrl town-layer-switch';
            container.setAttribute('role', 'group');
            container.setAttribute('aria-label', 'Map style');

            Object.keys(MAP_STYLES).forEach(function (key) {
                const button = document.createElement('button');
                button.type = 'button';
                button.textContent = key === 'standard' ? 'Standard' : 'Satellite';
                button.setAttribute('aria-pressed', String(key === currentStyle));

                button.addEventListener('click', function () {
                    if (key === currentStyle) {
                        return;
                    }

                    currentStyle = key;
                    container.querySelectorAll('button').forEach(function (other, index) {
                        other.setAttribute('aria-pressed', String(Object.keys(MAP_STYLES)[index] === key));
                    });

                    map.setStyle(MAP_STYLES[key]);
                });

                container.appendChild(button);
            });

            return container;
        },
        onRemove: function () {}
    }, 'top-left');

    let allTrails = [];
    let selectedTrailId = null;
    let selectedMarkerEl = null;

    /** Prefer hiking when a trail has several activities, as the main map does. */
    function displayActivity(trail) {
        if (!trail || !Array.isArray(trail.activities) || trail.activities.length === 0) {
            return null;
        }

        return trail.activities.find((activity) => activity.type === 'hiking') || trail.activities[0];
    }

    /** The marker element used on the main map, reproduced exactly. */
    function createMarkerEl(emoji, iconImageUrl) {
        const el = document.createElement('div');
        el.className = 'selectable-marker-el';
        el.style.cssText = 'background-color:' + MARKER_COLOR + ';width:32px;height:32px;border-radius:50%;'
            + 'border:2px solid #ffffff;box-shadow:0 2px 8px rgba(0,0,0,0.4);display:flex;align-items:center;'
            + 'justify-content:center;font-size:15px;cursor:pointer;line-height:1;overflow:hidden;';

        if (iconImageUrl) {
            el.innerHTML = '<img src="' + escapeHtml(iconImageUrl) + '" alt="" style="width:22px;height:22px;object-fit:cover;border-radius:50%;">';
        } else if (emoji === '\u{1F97E}') {
            el.innerHTML = '<img src="' + hikingBootIcon + '" alt="Hiking trail" style="width:21px;height:21px;display:block;object-fit:contain;">';
        } else {
            el.textContent = emoji;
        }

        return el;
    }

    /**
     * Route lines for every trail that has geometry. Fishing lakes are points,
     * and network trails are represented by their network on the main map, so
     * both are skipped here too.
     */
    function buildRouteGeoJSON(trails) {
        const features = [];

        trails.forEach(function (trail) {
            if (trail.location_type === 'fishing_lake' || trail.trail_network_id) {
                return;
            }

            if (!Array.isArray(trail.route_coordinates) || trail.route_coordinates.length === 0) {
                return;
            }

            const sanitized = trail.route_coordinates.map(sanitize).filter((c) => c !== null);

            if (sanitized.length === 0) {
                return;
            }

            const mapboxCoords = sanitized.map(toLngLat);

            // Out-and-back is mirrored so the arrows read forward on the way out
            // and back on the return, matching the main map.
            const displayCoords = trail.trail_type === 'out-and-back'
                ? mapboxCoords.concat(mapboxCoords.slice().reverse())
                : mapboxCoords;

            features.push({
                type: 'Feature',
                id: trail.id,
                properties: { trailId: trail.id, color: ROUTE_COLOR },
                geometry: { type: 'LineString', coordinates: displayCoords }
            });
        });

        return { type: 'FeatureCollection', features: features };
    }

    function selectTrail(trail, markerEl) {
        if (selectedTrailId !== null) {
            map.setFeatureState({ source: 'trail-routes', id: selectedTrailId }, { selected: false });
        }

        if (selectedMarkerEl) {
            selectedMarkerEl.classList.remove('selected');
        }

        selectedTrailId = trail.id;
        selectedMarkerEl = markerEl;
        markerEl.classList.add('selected');

        map.setFeatureState({ source: 'trail-routes', id: trail.id }, { selected: true });

        // Frame the whole route when there is one, otherwise just ease to the pin.
        const coords = (trail.route_coordinates || []).map(sanitize).filter((c) => c !== null);

        if (coords.length > 1) {
            const bounds = new mapboxgl.LngLatBounds();
            coords.forEach((c) => bounds.extend(toLngLat(c)));
            map.fitBounds(bounds, { padding: 70, maxZoom: 14 });
        } else {
            const point = sanitize(trail.coordinates);

            if (point) {
                map.easeTo({ center: toLngLat(point), zoom: Math.max(map.getZoom(), 12) });
            }
        }
    }

    /**
     * Difficulty colours match the main map's getDifficultyColor().
     */
    function difficultyColor(level) {
        const value = parseInt(level, 10);

        if (value <= 2) {
            return '#22C55E';
        }

        if (value === 3) {
            return '#3B82F6';
        }

        return '#EF4444';
    }

    function difficultyLabel(level) {
        return ['', 'Very easy', 'Easy', 'Moderate', 'Hard', 'Very hard'][parseInt(level, 10)] || '';
    }

    const statRow = (icon, value) => value
        ? '<span style="display:inline-flex;align-items:center;gap:3px;white-space:nowrap;">' + icon + value + '</span>'
        : '';

    const ICON_PIN = '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>';
    const ICON_UP = '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>';
    const ICON_CLOCK = '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';

    function popupHtml(trail) {
        const url = config.trailUrlTemplate.replace('__ID__', trail.id);
        const isLake = trail.location_type === 'fishing_lake';
        const photo = trail.preview_photo
            || (Array.isArray(trail.photos) && trail.photos.length ? trail.photos[0].url : null);

        // Media band: a real photo, or the branded placeholder the trail list uses.
        const media = photo
            ? '<img src="' + escapeHtml(photo) + '" alt="" style="width:100%;height:104px;object-fit:cover;display:block;">'
            : '<div style="height:104px;display:flex;align-items:center;justify-content:center;background:'
                + (isLake ? 'linear-gradient(135deg,#0369a1,#0ea5e9)' : 'linear-gradient(135deg,#166534,#22c55e)') + ';">'
                + '<img src="' + xploreLogo + '" alt="" style="width:56px;height:56px;object-fit:contain;opacity:.85;"></div>';

        // Difficulty pill, overlaid on the media band.
        const difficulty = (! isLake && trail.difficulty)
            ? '<span style="position:absolute;top:8px;right:8px;background:' + difficultyColor(trail.difficulty)
                + ';color:#fff;font-size:10px;font-weight:700;padding:3px 7px;border-radius:5px;'
                + 'box-shadow:0 1px 4px rgba(0,0,0,.3);letter-spacing:.02em;">'
                + escapeHtml(difficultyLabel(trail.difficulty)) + '</span>'
            : '';

        const typeBadge = (! isLake && trail.trail_type)
            ? '<span style="position:absolute;top:8px;left:8px;background:rgba(255,255,255,.92);color:#193634;'
                + 'font-size:10px;font-weight:600;padding:3px 7px;border-radius:5px;text-transform:capitalize;">'
                + escapeHtml(String(trail.trail_type).replace(/-/g, ' ')) + '</span>'
            : '';

        // Stats differ for lakes, which have no distance or elevation.
        let stats;

        if (isLake) {
            const species = Array.isArray(trail.fish_species) && trail.fish_species.length
                ? trail.fish_species.length + ' species'
                : null;
            stats = [
                statRow('\u{1F3A3} ', species),
                trail.best_fishing_season
                    ? statRow('\u{1F5D3}\uFE0F ', 'Best in ' + escapeHtml(trail.best_fishing_season))
                    : ''
            ].filter(Boolean).join('<span style="color:#d1d5db;">&middot;</span>');
        } else {
            stats = [
                statRow(ICON_PIN, trail.distance ? trail.distance + ' km' : null),
                statRow(ICON_UP, trail.elevation_gain ? trail.elevation_gain + ' m' : null),
                statRow(ICON_CLOCK, trail.estimated_time ? trail.estimated_time + ' h' : null)
            ].filter(Boolean).join('<span style="color:#d1d5db;">&middot;</span>');
        }

        // Up to two activity chips, coloured from the activity itself.
        const activities = Array.isArray(trail.activities)
            ? trail.activities.slice(0, 2).map(function (activity) {
                const colour = activity.color || '#2C5F5D';

                return '<span style="display:inline-flex;align-items:center;gap:3px;padding:2px 6px;border-radius:4px;'
                    + 'font-size:10px;font-weight:600;background:' + colour + '1A;color:' + colour + ';">'
                    + (activity.icon_image_url
                        ? '<img src="' + escapeHtml(activity.icon_image_url) + '" alt="" style="width:10px;height:10px;object-fit:cover;border-radius:2px;">'
                        : escapeHtml(activity.icon || ''))
                    + ' ' + escapeHtml(activity.name) + '</span>';
            }).join('')
            : '';

        return '<div class="town-popup">'
            + '<div style="position:relative;">' + media + typeBadge + difficulty + '</div>'
            + '<div style="padding:10px 12px 12px;">'
            + '<div style="font-weight:700;font-size:13px;line-height:1.3;color:#111827;">' + escapeHtml(trail.name) + '</div>'
            + (trail.location
                ? '<div style="font-size:11px;color:#9ca3af;margin-top:2px;">' + escapeHtml(trail.location) + '</div>'
                : '')
            + (stats
                ? '<div style="display:flex;gap:6px;align-items:center;font-size:11px;color:#4b5563;margin-top:7px;'
                    + 'font-variant-numeric:tabular-nums;flex-wrap:wrap;">' + stats + '</div>'
                : '')
            + (activities ? '<div style="display:flex;gap:4px;margin-top:8px;flex-wrap:wrap;">' + activities + '</div>' : '')
            + '<a href="' + url + '" style="display:flex;align-items:center;justify-content:center;gap:5px;'
            + 'margin-top:10px;padding:7px 10px;border-radius:7px;background:#2C5F5D;color:#fff;'
            + 'font-size:12px;font-weight:600;text-decoration:none;transition:background .2s ease;"'
            + ' onmouseover="this.style.background=\'#193634\'" onmouseout="this.style.background=\'#2C5F5D\'">'
            + (isLake ? 'View lake' : 'View trail')
            + '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M9 5l7 7-7 7"/></svg>'
            + '</a>'
            + '</div></div>';
    }

    /**
     * Every custom source and layer, in one place.
     *
     * Bound to style.load rather than load: switching between Standard and
     * Satellite replaces the whole style object, taking custom sources and
     * layers with it, so this has to run again each time. DOM markers are not
     * part of the style and survive on their own.
     */
    function installMapLayers() {
        // Direction arrow, tip pointing +x so Mapbox rotates it along the line.
        if (!map.hasImage('trail-arrow')) {
            const arrowSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14"><polygon points="13,7 5,3 7,7 5,11" fill="white"/></svg>';
            const arrowImg = new Image(14, 14);
            arrowImg.onload = function () {
                if (!map.hasImage('trail-arrow')) {
                    map.addImage('trail-arrow', arrowImg);
                }
            };
            arrowImg.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(arrowSvg);
        }

        if (!map.getSource('trail-routes')) {
            map.addSource('trail-routes', {
                type: 'geojson',
                promoteId: 'trailId',
                data: buildRouteGeoJSON(allTrails)
            });
        }

        // Three layers, all hidden until a trail is selected — same as /map.
        if (!map.getLayer('trail-routes-outline')) {
            map.addLayer({
                id: 'trail-routes-outline',
                type: 'line',
                source: 'trail-routes',
                paint: {
                    'line-color': '#000000',
                    'line-width': 8,
                    'line-opacity': ['case', ['boolean', ['feature-state', 'selected'], false], 1, 0]
                }
            });
        }

        if (!map.getLayer('trail-routes-line')) {
            map.addLayer({
                id: 'trail-routes-line',
                type: 'line',
                source: 'trail-routes',
                paint: {
                    'line-color': ['get', 'color'],
                    'line-width': 4,
                    'line-opacity': ['case', ['boolean', ['feature-state', 'selected'], false], 1, 0]
                }
            });
        }

        if (!map.getLayer('trail-routes-arrows')) {
            map.addLayer({
                id: 'trail-routes-arrows',
                type: 'symbol',
                source: 'trail-routes',
                layout: {
                    'symbol-placement': 'line',
                    'symbol-spacing': 120,
                    'icon-image': 'trail-arrow',
                    'icon-size': 0.9,
                    'icon-allow-overlap': true,
                    'icon-ignore-placement': true
                },
                paint: {
                    'icon-opacity': ['case', ['boolean', ['feature-state', 'selected'], false], 1, 0]
                }
            });
        }

        if (!map.getSource('town-facilities')) {
            map.addSource('town-facilities', {
                type: 'geojson',
                data: {
                    type: 'FeatureCollection',
                    features: config.facilities.map(function (facility) {
                        return {
                            type: 'Feature',
                            geometry: { type: 'Point', coordinates: toLngLat(facility.coordinates) },
                            properties: { name: facility.name }
                        };
                    })
                }
            });
        }

        // Facilities stay subordinate to trailheads until you zoom in.
        if (!map.getLayer('facility-points')) {
            map.addLayer({
                id: 'facility-points',
                type: 'circle',
                source: 'town-facilities',
                minzoom: 10,
                paint: {
                    'circle-color': '#E87B35',
                    'circle-radius': 5,
                    'circle-stroke-width': 2,
                    'circle-stroke-color': '#ffffff'
                }
            });
        }

        // A trail selected before the swap keeps its route visible after it.
        if (selectedTrailId !== null) {
            map.setFeatureState({ source: 'trail-routes', id: selectedTrailId }, { selected: true });
        }
    }

    map.on('style.load', installMapLayers);

    map.on('click', 'facility-points', function (event) {
        new mapboxgl.Popup({ offset: 12 })
            .setLngLat(event.features[0].geometry.coordinates)
            .setText(event.features[0].properties.name)
            .addTo(map);
    });

    map.on('mouseenter', 'facility-points', function () { map.getCanvas().style.cursor = 'pointer'; });
    map.on('mouseleave', 'facility-points', function () { map.getCanvas().style.cursor = ''; });

    map.on('load', function () {
        // Same endpoint the main map uses, scoped to this town.
        fetch('/api/trails?town=' + encodeURIComponent(config.townSlug))
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Trail request failed: ' + response.status);
                }

                return response.json();
            })
            .then(function (trails) {
                if (skeleton) {
                    skeleton.remove();
                }

                allTrails = trails;

                if (map.getSource('trail-routes')) {
                    map.getSource('trail-routes').setData(buildRouteGeoJSON(trails));
                }

                const bounds = new mapboxgl.LngLatBounds();
                let plotted = 0;

                trails.forEach(function (trail) {
                    const coords = sanitize(trail.coordinates);

                    if (!coords) {
                        return;
                    }

                    const isLake = trail.location_type === 'fishing_lake';
                    const activity = displayActivity(trail);

                    // Icon precedence matches the main map: the trail's own icon
                    // wins, then the fishing default, then its activity's icon.
                    const emoji = trail.icon || (isLake ? '\u{1F41F}' : ((activity && activity.icon) || '\u{1F4CD}'));
                    const iconImageUrl = trail.icon_image_url
                        || (isLake ? null : ((activity && activity.icon_image_url) || null));

                    const el = createMarkerEl(emoji, iconImageUrl);
                    el.dataset.trailId = trail.id;
                    el.setAttribute('aria-label', trail.name);

                    new mapboxgl.Marker({ element: el, anchor: 'center' })
                        .setLngLat(toLngLat(coords))
                        .setPopup(new mapboxgl.Popup({ offset: 20, maxWidth: '260px' }).setHTML(popupHtml(trail)))
                        .addTo(map);

                    el.addEventListener('click', function () {
                        selectTrail(trail, el);
                    });

                    bounds.extend(toLngLat(coords));
                    plotted++;
                });

                config.facilities.forEach(function (facility) {
                    bounds.extend(toLngLat(facility.coordinates));
                });

                if (plotted || config.facilities.length) {
                    map.fitBounds(bounds, { padding: 60, maxZoom: 12, duration: 0 });
                }
            })
            .catch(function (error) {
                console.error(error);

                if (skeleton) {
                    skeleton.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;'
                        + 'height:100%;color:#4b5563;font-size:0.875rem;text-align:center;padding:1rem;">'
                        + 'The trail map could not be loaded. The trail list below still works.</div>';
                    skeleton.classList.remove('animate-pulse');
                }
            });
    });
})();
</script>
@endpush
