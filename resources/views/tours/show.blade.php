@extends('layouts.public')

@section('title', $tour->title . ' — Xplore Smithers')

@push('styles')
<link href="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.css" rel="stylesheet">
<style>
    .stop-card.active { border-color: #3b82f6; background-color: #eff6ff; }
    .stop-number { min-width: 2rem; min-height: 2rem; }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 pt-20">

    <!-- Hero -->
    <div class="relative h-64 md:h-80 bg-gradient-to-br from-blue-500 to-green-600 overflow-hidden">
        @if($tour->cover_image_url)
            <img src="{{ $tour->cover_image_url }}" alt="{{ $tour->title }}"
                class="absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0 bg-black/40"></div>
        @else
            <div class="absolute inset-0 flex items-center justify-center text-8xl opacity-20">🗺️</div>
        @endif
        <div class="relative z-10 h-full flex flex-col justify-end p-6 md:p-10 max-w-7xl mx-auto w-full">
            <div class="flex items-center gap-2 mb-3">
                <span class="inline-flex items-center rounded-full bg-white/20 backdrop-blur-sm border border-white/30 px-3 py-1 text-xs font-semibold text-white">
                    {{ App\Models\Tour::getTourTypes()[$tour->tour_type] ?? ucfirst($tour->tour_type) }}
                </span>
            </div>
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-2 drop-shadow">{{ $tour->title }}</h1>
            @if($tour->tagline)
                <p class="text-white/90 text-lg drop-shadow">{{ $tour->tagline }}</p>
            @endif
        </div>
    </div>

    <!-- Stats bar -->
    <div class="bg-white border-b shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4 flex flex-wrap gap-6">
            <div class="flex items-center gap-2 text-sm text-gray-700">
                <span class="text-lg">📍</span>
                <span><strong>{{ $tour->stops->count() }}</strong> stop{{ $tour->stops->count() !== 1 ? 's' : '' }}</span>
            </div>
            @if($tour->duration_estimate)
                <div class="flex items-center gap-2 text-sm text-gray-700">
                    <span class="text-lg">🕐</span>
                    <span><strong>{{ $tour->duration_estimate }}</strong></span>
                </div>
            @endif
            @if($tour->difficulty_summary)
                <div class="flex items-center gap-2 text-sm text-gray-700">
                    <span class="text-lg">🥾</span>
                    <span><strong>{{ $tour->difficulty_summary }}</strong></span>
                </div>
            @endif
            @if($tour->total_driving_km)
                <div class="flex items-center gap-2 text-sm text-gray-700">
                    <span class="text-lg">🚗</span>
                    <span><strong>{{ number_format($tour->total_driving_km, 0) }} km</strong> total drive</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Main content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

            <!-- Map (left, 3/5) -->
            <div class="lg:col-span-3">
                <div id="tour-map" class="w-full h-[420px] md:h-[520px] rounded-2xl overflow-hidden shadow-md border bg-gray-200 sticky top-24"></div>
            </div>

            <!-- Stop list (right, 2/5) -->
            <div class="lg:col-span-2 space-y-4">
                @if($tour->description)
                    <div class="bg-white rounded-xl border shadow-sm p-5">
                        <p class="text-gray-700 text-sm leading-relaxed">{{ $tour->description }}</p>
                    </div>
                @endif

                <h2 class="text-lg font-bold text-gray-900">Stops on This Tour</h2>

                @foreach($tour->stops as $stop)
                    @php
                        $trail = $stop->trail;
                        $displayName = $stop->stop_label ?: ($trail?->name ?? '');
                        $coords = $trail?->start_coordinates ?? null;
                        $coordsJs = $coords ? '[' . implode(',', $coords) . ']' : 'null';
                    @endphp
                    <div class="stop-card bg-white rounded-xl border shadow-sm p-4 cursor-pointer hover:border-blue-300 transition-all"
                        id="stop-card-{{ $loop->index }}"
                        onclick="focusStop({{ $loop->index }}, {{ $coordsJs }})">
                        <div class="flex gap-3 items-start">
                            <div class="stop-number flex h-8 w-8 items-center justify-center rounded-full bg-black text-white text-sm font-bold flex-shrink-0 mt-0.5">
                                {{ $loop->iteration }}
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 text-sm">{{ $displayName }}</h3>
                                @if($stop->estimated_visit_time)
                                    <p class="text-xs text-gray-500 mt-0.5">⏱ {{ $stop->estimated_visit_time }}</p>
                                @endif
                                @if($trail && $trail->description)
                                    <p class="text-xs text-gray-600 mt-2 line-clamp-2">{{ Str::limit($trail->description, 120) }}</p>
                                @endif
                                @if($stop->driving_notes)
                                    <p class="text-xs text-blue-600 mt-1">🚗 {{ $stop->driving_notes }}</p>
                                @endif
                                @if($trail)
                                    <a href="{{ route('trails.show', $trail) }}"
                                        onclick="event.stopPropagation()"
                                        class="inline-flex items-center gap-1 mt-2 text-xs font-medium text-blue-600 hover:text-blue-800 transition-colors">
                                        Full Details →
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>

</div>

@php
$tourStopsData = $tour->stops->values()->map(fn ($s, $i) => [
    'index' => $i,
    'label' => $s->stop_label ?: ($s->trail?->name ?? 'Stop'),
    'coords' => $s->trail?->start_coordinates ?? null,
])->values();
@endphp

<script src="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.js"></script>
<script>
mapboxgl.accessToken = @json($mapboxToken);

// Stop data from PHP
const tourStops = @json($tourStopsData);

const drivingRoute = @json($tour->driving_route_coordinates);

const map = new mapboxgl.Map({
    container: 'tour-map',
    style: 'mapbox://styles/mapbox/outdoors-v12',
    center: [-127.17, 54.78],
    zoom: 9,
});

map.addControl(new mapboxgl.NavigationControl(), 'top-right');

map.on('load', () => {
    // Add driving route if available
    if (drivingRoute && drivingRoute.length > 1) {
        map.addSource('tour-route', {
            type: 'geojson',
            data: {
                type: 'Feature',
                geometry: { type: 'LineString', coordinates: drivingRoute },
            },
        });
        map.addLayer({
            id: 'tour-route-line',
            type: 'line',
            source: 'tour-route',
            paint: {
                'line-color': '#3b82f6',
                'line-width': 3,
                'line-dasharray': [2, 1],
            },
        });
    }

    // Add numbered markers and fit bounds
    const bounds = new mapboxgl.LngLatBounds();
    let hasCoords = false;

    tourStops.forEach((stop, i) => {
        if (!stop.coords || stop.coords.length < 2) { return; }

        const [lat, lng] = stop.coords; // DB format [lat, lng]
        const lngLat = [lng, lat]; // Mapbox format [lng, lat]

        const el = document.createElement('div');
        el.className = 'flex h-8 w-8 items-center justify-center rounded-full bg-black text-white text-sm font-bold shadow-lg cursor-pointer border-2 border-white';
        el.textContent = i + 1;
        el.title = stop.label;
        el.addEventListener('click', () => focusStop(i, stop.coords));

        new mapboxgl.Marker({ element: el })
            .setLngLat(lngLat)
            .setPopup(new mapboxgl.Popup({ offset: 25 }).setHTML(`<strong>${stop.label}</strong>`))
            .addTo(map);

        bounds.extend(lngLat);
        hasCoords = true;
    });

    if (hasCoords) {
        map.fitBounds(bounds, { padding: 60, maxZoom: 12 });
    }
});

function focusStop(index, coords) {
    // Highlight active card
    document.querySelectorAll('.stop-card').forEach((c, i) => {
        c.classList.toggle('active', i === index);
    });

    if (!coords || coords.length < 2) { return; }
    const [lat, lng] = coords;
    map.flyTo({ center: [lng, lat], zoom: 13, duration: 800 });
}
</script>
@endsection
