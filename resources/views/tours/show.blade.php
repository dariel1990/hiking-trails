@extends('layouts.public')

@section('title', $tour->title . ' — Xplore Smithers')

@push('styles')
<link href="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.css" rel="stylesheet">
<style>
    /* Map style switcher */
    .tour-layer-card {
        position: relative; cursor: pointer; border-radius: 0.5rem;
        overflow: hidden; border: 2px solid #E5E7EB;
        display: flex; flex-direction: column; align-items: center;
    }
    .tour-layer-card:hover { border-color: #a8c4b9; }
    .tour-layer-card.active { border-color: #2C5F5D; box-shadow: 0 0 0 3px rgba(44,95,93,0.12); }
    .tour-layer-card .layer-preview { width: 100%; height: 56px; overflow: hidden; }
    .tour-layer-card .layer-label { display: block; font-size: 0.7rem; font-weight: 600; color: #374151; text-align: center; padding: 0.25rem 0.25rem 0.35rem; }
    .tour-layer-card .layer-checkmark { position: absolute; top: 4px; right: 4px; width: 16px; height: 16px; color: white; background-color: #2C5F5D; border-radius: 50%; padding: 2px; display: none; }
    .tour-layer-card.active .layer-checkmark { display: block; }

    /* Stop cards */
    .stop-card { transition: all 0.18s ease; }
    .stop-card:hover { border-color: #a8c4b9; background-color: #f5f8f7; }
    .stop-card.active { border-color: #2C5F5D; background-color: #f5f8f7; box-shadow: 0 0 0 3px rgba(44,95,93,0.1); }

    /* Timeline connector */
    .timeline-connector {
        position: absolute;
        left: 1.375rem;
        top: 3.5rem;
        bottom: -1rem;
        width: 2px;
        background: linear-gradient(to bottom, #d1e1db, #e5e7eb);
    }
    .stop-timeline-item:last-child .timeline-connector { display: none; }
</style>
@endpush

@section('content')

<!-- Hero -->
<div class="bg-hero-gradient relative overflow-hidden" style="min-height: 340px;">
    @if($tour->cover_image_url)
        <img src="{{ $tour->cover_image_url }}" alt="{{ $tour->title }}"
            class="absolute inset-0 w-full h-full object-cover opacity-40">
    @endif
    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

    <!-- Back link -->
    <div class="relative z-10 pt-24 px-4 max-w-7xl mx-auto">
        <a href="{{ route('tours.index') }}"
            class="inline-flex items-center gap-1.5 text-white/70 hover:text-white text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            All Tours
        </a>
    </div>

    <!-- Hero content -->
    <div class="relative z-10 max-w-7xl mx-auto px-4 pb-10 pt-6">
        <div class="flex flex-wrap items-center gap-2 mb-4">
            <span class="inline-flex items-center rounded-full bg-white/15 border border-white/25 backdrop-blur-sm px-3 py-1 text-xs font-semibold text-white/90">
                {{ App\Models\Tour::getTourTypes()[$tour->tour_type] ?? ucfirst($tour->tour_type) }}
            </span>
            @if($tour->is_featured)
                <span class="inline-flex items-center rounded-full bg-accent-500 px-3 py-1 text-xs font-semibold text-white">
                    ★ Featured Tour
                </span>
            @endif
        </div>

        <h1 class="text-3xl md:text-5xl font-bold text-white mb-3 leading-tight drop-shadow-lg">
            {{ $tour->title }}
        </h1>
        @if($tour->tagline)
            <p class="text-white/75 text-lg max-w-2xl">{{ $tour->tagline }}</p>
        @endif

        <!-- Stats row -->
        <div class="flex flex-wrap gap-3 mt-6">
            <div class="flex items-center gap-2 rounded-full bg-white/10 backdrop-blur-sm border border-white/15 px-3 py-1.5 text-sm text-white/90">
                <svg class="w-4 h-4 text-sand-100" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                </svg>
                <span><strong class="text-white">{{ $tour->stops->count() }}</strong> stop{{ $tour->stops->count() !== 1 ? 's' : '' }}</span>
            </div>
            @if($tour->duration_estimate)
                <div class="flex items-center gap-2 rounded-full bg-white/10 backdrop-blur-sm border border-white/15 px-3 py-1.5 text-sm text-white/90">
                    <svg class="w-4 h-4 text-sand-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <strong class="text-white">{{ $tour->duration_estimate }}</strong>
                </div>
            @endif
            @if($tour->difficulty_summary)
                <div class="flex items-center gap-2 rounded-full bg-white/10 backdrop-blur-sm border border-white/15 px-3 py-1.5 text-sm text-white/90">
                    <svg class="w-4 h-4 text-sand-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    <strong class="text-white">{{ $tour->difficulty_summary }}</strong>
                </div>
            @endif
            @if($tour->total_driving_km)
                <div class="flex items-center gap-2 rounded-full bg-white/10 backdrop-blur-sm border border-white/15 px-3 py-1.5 text-sm text-white/90">
                    <svg class="w-4 h-4 text-sand-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    <strong class="text-white">{{ number_format($tour->total_driving_km, 0) }} km</strong><span class="text-white/60 ml-1">drive</span>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Main content -->
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

            <!-- Map (left 3/5) -->
            <div class="lg:col-span-3">
                <div class="w-full h-[460px] md:h-[560px] sticky top-24">
                    <div id="tour-map" class="w-full h-full rounded-2xl overflow-hidden shadow-lg border border-gray-200 bg-gray-200"></div>

                    <!-- Map Style Switcher -->
                    <div class="absolute top-3 right-14 z-10">
                        <div class="relative">
                            <button id="tour-layers-toggle" title="Change Map Style"
                                class="bg-white rounded-xl shadow-md p-2 hover:bg-gray-50 transition-colors border border-gray-200">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 0v10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2z"/>
                                </svg>
                            </button>
                            <div id="tour-layers-dropdown" class="hidden absolute top-full right-0 mt-2 bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden" style="min-width:196px;">
                                <div class="p-2.5">
                                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-2 pb-2">Map Style</p>
                                    <div class="grid grid-cols-2 gap-1.5">
                                        <button class="tour-layer-card" data-style="mapbox://styles/mapbox/standard">
                                            <div class="layer-preview"><img src="{{ asset('images/map-layers/standard.png') }}" alt="Standard" class="w-full h-full object-cover"></div>
                                            <span class="layer-label">Standard</span>
                                            <svg class="layer-checkmark" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        </button>
                                        <button class="tour-layer-card" data-style="mapbox://styles/mapbox/satellite-streets-v12">
                                            <div class="layer-preview"><img src="{{ asset('images/map-layers/satellite.png') }}" alt="Satellite" class="w-full h-full object-cover"></div>
                                            <span class="layer-label">Satellite</span>
                                            <svg class="layer-checkmark" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        </button>
                                        <button class="tour-layer-card active" data-style="mapbox://styles/mapbox/outdoors-v12">
                                            <div class="layer-preview"><img src="{{ asset('images/map-layers/terrain.png') }}" alt="Terrain" class="w-full h-full object-cover"></div>
                                            <span class="layer-label">Terrain</span>
                                            <svg class="layer-checkmark" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        </button>
                                        <button class="tour-layer-card" data-style="mapbox://styles/mapbox/navigation-day-v1">
                                            <div class="layer-preview"><img src="{{ asset('images/map-layers/outdoor.png') }}" alt="Outdoors" class="w-full h-full object-cover"></div>
                                            <span class="layer-label">Outdoors</span>
                                            <svg class="layer-checkmark" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stop list (right 2/5) -->
            <div class="lg:col-span-2">

                @if($tour->description)
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 mb-6">
                        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">About This Tour</h2>
                        <p class="text-gray-700 text-sm leading-relaxed">{{ $tour->description }}</p>
                    </div>
                @endif

                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-bold text-gray-900">
                        Tour Stops
                        <span class="ml-1.5 text-xs font-semibold text-forest-700 bg-forest-50 border border-forest-200 rounded-full px-2 py-0.5">
                            {{ $tour->stops->count() }}
                        </span>
                    </h2>
                    <p class="text-xs text-gray-400">Click a stop to zoom the map</p>
                </div>

                @php
                /**
                 * Returns the best [lat, lng] for a tour stop:
                 * 1. The stop's linked feature's coordinates (when trail_feature_id is set)
                 * 2. First route_coordinates point of the trail (GPX linestring)
                 * 3. First feature with coordinates on the trail
                 * 4. Trail's start_coordinates
                 */
                $bestCoords = function (App\Models\TourStop $stop): ?array {
                    if ($stop->trail_feature_id && $stop->feature?->coordinates) {
                        return $stop->feature->coordinates;
                    }
                    $trail = $stop->trail;
                    if (!$trail) { return null; }
                    if (!empty($trail->route_coordinates) && count($trail->route_coordinates) > 0) {
                        return $trail->route_coordinates[0];
                    }
                    $feature = $trail->features->whereNotNull('coordinates')->first();
                    if ($feature) { return $feature->coordinates; }
                    return $trail->start_coordinates ?: null;
                };
                @endphp

                <!-- Timeline -->
                <div class="space-y-0">
                    @foreach($tour->stops as $stop)
                        @php
                            $trail = $stop->trail;
                            $displayName = $stop->stop_label ?: ($stop->feature?->name ?? $trail?->name ?? 'Stop ' . $loop->iteration);
                            $coords = $bestCoords($stop);
                            $coordsJs = $coords ? '[' . implode(',', $coords) . ']' : 'null';
                        @endphp
                        <div class="stop-timeline-item relative pl-12 pb-4">
                            <!-- Timeline line -->
                            @if(!$loop->last)
                                <div class="timeline-connector"></div>
                            @endif

                            <!-- Number badge -->
                            <div class="absolute left-0 top-0 flex h-11 w-11 items-center justify-center rounded-full bg-gray-900 text-white text-sm font-bold shadow-md border-2 border-white ring-2 ring-gray-200">
                                {{ $loop->iteration }}
                            </div>

                            <!-- Card -->
                            <div class="stop-card bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden cursor-pointer"
                                id="stop-card-{{ $loop->index }}"
                                onclick="focusStop({{ $loop->index }}, {{ $coordsJs }})">

                                @if($trail?->cover_image_url ?? false)
                                    <div class="relative h-28 overflow-hidden bg-gray-100">
                                        <img src="{{ $trail->cover_image_url }}" alt="{{ $displayName }}"
                                            class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                                    </div>
                                @endif

                                <div class="p-3.5">
                                    <h3 class="font-bold text-gray-900 text-sm leading-snug">{{ $displayName }}</h3>

                                    @if($stop->estimated_visit_time)
                                        <p class="text-xs text-gray-400 mt-0.5 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            {{ $stop->estimated_visit_time }}
                                        </p>
                                    @endif

                                    @if($trail)
                                        <div class="flex flex-wrap gap-1.5 mt-2">
                                            @if($trail->difficulty_level)
                                                <span class="text-xs bg-gray-100 text-gray-600 rounded px-1.5 py-0.5 font-medium">
                                                    Lvl {{ number_format($trail->difficulty_level, 1) }}
                                                </span>
                                            @endif
                                            @if($trail->distance_km)
                                                <span class="text-xs bg-gray-100 text-gray-600 rounded px-1.5 py-0.5 font-medium">
                                                    {{ number_format($trail->distance_km, 1) }} km
                                                </span>
                                            @endif
                                            @if($trail->estimated_time_hours)
                                                <span class="text-xs bg-gray-100 text-gray-600 rounded px-1.5 py-0.5 font-medium">
                                                    {{ $trail->estimated_time_hours }}h hike
                                                </span>
                                            @endif
                                        </div>
                                    @endif

                                    @if($trail && $trail->description)
                                        <p class="text-xs text-gray-500 mt-2 leading-relaxed line-clamp-2">{{ Str::limit(strip_tags($trail->description), 110) }}</p>
                                    @endif

                                    @if($stop->driving_notes)
                                        <p class="text-xs text-blue-600 mt-2 flex items-start gap-1">
                                            <svg class="w-3 h-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                            {{ $stop->driving_notes }}
                                        </p>
                                    @endif

                                    @if($trail)
                                        <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
                                            <a href="{{ route('trails.show', $trail) }}"
                                                onclick="event.stopPropagation()"
                                                class="text-xs font-semibold text-forest-700 hover:text-accent-600 flex items-center gap-1 transition-colors">
                                                Trail Details
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </a>
                                            <span class="text-xs text-gray-400">Stop {{ $loop->iteration }} of {{ $tour->stops->count() }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>

        </div>
    </div>
</div>

@php
$tourStopsData = $tour->stops->values()->map(fn ($s, $i) => [
    'index' => $i,
    'label' => $s->stop_label ?: ($s->feature?->name ?? $s->trail?->name ?? 'Stop'),
    'coords' => $bestCoords($s),
])->values();
@endphp

<script src="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.js"></script>
<script>
mapboxgl.accessToken = @json($mapboxToken);

const tourStops = @json($tourStopsData);
const drivingRoute = @json($tour->driving_route_coordinates);

const map = new mapboxgl.Map({
    container: 'tour-map',
    style: 'mapbox://styles/mapbox/outdoors-v12',
    center: [-127.17, 54.78],
    zoom: 9,
});

map.addControl(new mapboxgl.NavigationControl(), 'top-right');

const activeMarkers = [];

function addRouteAndMarkers() {
    if (drivingRoute && drivingRoute.length > 1) {
        if (!map.getSource('tour-route')) {
            map.addSource('tour-route', {
                type: 'geojson',
                data: { type: 'Feature', geometry: { type: 'LineString', coordinates: drivingRoute } },
            });
        }
        if (!map.getLayer('tour-route-line')) {
            map.addLayer({
                id: 'tour-route-line',
                type: 'line',
                source: 'tour-route',
                paint: { 'line-color': '#2C5F5D', 'line-width': 3, 'line-dasharray': [2, 1] },
            });
        }
    }

    activeMarkers.forEach(m => m.remove());
    activeMarkers.length = 0;

    const bounds = new mapboxgl.LngLatBounds();
    let hasCoords = false;

    tourStops.forEach((stop, i) => {
        if (!stop.coords || stop.coords.length < 2) { return; }

        const [lat, lng] = stop.coords;
        const lngLat = [lng, lat];

        const el = document.createElement('div');
        el.style.cssText = 'width:2rem;height:2rem;border-radius:50%;background:#111827;color:#fff;font-size:0.8rem;font-weight:700;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,0.4);cursor:pointer;border:2.5px solid #fff;transition:box-shadow 0.15s,filter 0.15s;';
        el.textContent = i + 1;
        el.title = stop.label;
        el.addEventListener('mouseenter', () => { el.style.boxShadow = '0 4px 16px rgba(0,0,0,0.5)'; el.style.filter = 'brightness(1.4)'; });
        el.addEventListener('mouseleave', () => { el.style.boxShadow = '0 2px 8px rgba(0,0,0,0.4)'; el.style.filter = ''; });
        el.addEventListener('click', () => focusStop(i, stop.coords));

        const marker = new mapboxgl.Marker({ element: el })
            .setLngLat(lngLat)
            .setPopup(new mapboxgl.Popup({ offset: 25, className: 'tour-popup' })
                .setHTML(`<strong style="font-size:0.8rem">${i + 1}. ${stop.label}</strong>`))
            .addTo(map);

        activeMarkers.push(marker);
        bounds.extend(lngLat);
        hasCoords = true;
    });

    if (hasCoords) {
        map.fitBounds(bounds, { padding: 60, maxZoom: 12 });
    }
}

map.on('load', addRouteAndMarkers);

// Map style switcher
const layersToggle = document.getElementById('tour-layers-toggle');
const layersDropdown = document.getElementById('tour-layers-dropdown');

layersToggle.addEventListener('click', (e) => {
    e.stopPropagation();
    layersDropdown.classList.toggle('hidden');
});

document.addEventListener('click', (e) => {
    if (!layersToggle.contains(e.target) && !layersDropdown.contains(e.target)) {
        layersDropdown.classList.add('hidden');
    }
});

document.querySelectorAll('.tour-layer-card').forEach(btn => {
    btn.addEventListener('click', () => {
        const style = btn.dataset.style;
        if (!style) { return; }
        document.querySelectorAll('.tour-layer-card').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        layersDropdown.classList.add('hidden');
        map.setStyle(style);
        map.once('styledata', addRouteAndMarkers);
    });
});

function focusStop(index, coords) {
    document.querySelectorAll('.stop-card').forEach((c, i) => {
        c.classList.toggle('active', i === index);
    });
    // Scroll the active stop card into view
    const card = document.getElementById('stop-card-' + index);
    if (card) { card.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }

    if (!coords || coords.length < 2) { return; }
    const [lat, lng] = coords;
    map.flyTo({ center: [lng, lat], zoom: 13, duration: 800 });
}
</script>
@endsection
