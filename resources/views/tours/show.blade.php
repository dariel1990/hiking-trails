@extends('layouts.public')

@php
    $tourTypeLabel = App\Models\Tour::getTourTypes()[$tour->tour_type] ?? ucfirst($tour->tour_type);
    $tourTypeText = explode(' ', $tourTypeLabel, 2)[1] ?? $tourTypeLabel;
    $stopCount = $tour->stops->count();
    // Roaming activities (no fixed spot) are listed separately and get no map pin.
    $mappedStops = $tour->stops->reject(fn ($stop) => $stop->is_roaming);
    $roamingStops = $tour->stops->filter(fn ($stop) => $stop->is_roaming);
    $stopNoun = 'stop' . ($stopCount === 1 ? '' : 's');
    $streetRoute = $streetRoute ?? null;
    $stopLegs = $tour->stopLegs($streetRoute);
    $legsAreEstimates = $streetRoute === null;
@endphp

@section('title', $tour->title . ' — Xplore Smithers')

@push('meta')
<!-- Open Graph / Facebook Meta Tags -->
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="{{ $tour->title }} — Xplore Smithers Tour">
<meta property="og:description" content="{{ Str::limit($tour->tagline ?: strip_tags($tour->description ?? ''), 200) }}">
@if($tour->cover_image_url)
<meta property="og:image" content="{{ url($tour->cover_image_url) }}">
<meta property="og:image:secure_url" content="{{ url($tour->cover_image_url) }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ $tour->title }} - Tour Photo">
@endif
<meta property="og:site_name" content="Xplore Smithers">

<!-- Twitter Card Meta Tags -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{{ url()->current() }}">
<meta name="twitter:title" content="{{ $tour->title }} — Xplore Smithers Tour">
<meta name="twitter:description" content="{{ Str::limit($tour->tagline ?: strip_tags($tour->description ?? ''), 200) }}">
@if($tour->cover_image_url)
<meta name="twitter:image" content="{{ url($tour->cover_image_url) }}">
<meta name="twitter:image:alt" content="{{ $tour->title }} - Tour Photo">
@endif
@endpush

@push('styles')
@include('tours._map-styles')
<style>
    /* Stop cards */
    .stop-card { --stop-card-bg: #ffffff; transition: all 0.18s ease; }
    .stop-card:hover { --stop-card-bg: var(--tour-primary-soft); border-color: var(--tour-primary-border); background-color: var(--tour-primary-soft); }
    .stop-card.active { --stop-card-bg: var(--tour-primary-soft); border-color: var(--tour-primary); background-color: var(--tour-primary-soft); box-shadow: 0 0 0 3px rgba(0,0,0,0.06); }
    /* Fades a clamped description into whatever colour the card currently is. */
    .stop-description-fade { background: linear-gradient(to top, var(--stop-card-bg, #ffffff), transparent); }
    .stop-number-badge { background: linear-gradient(145deg, var(--tour-primary), var(--tour-primary-dark)); }

    /* Timeline connector */
    .timeline-connector {
        position: absolute;
        left: 1.375rem;
        top: 3.5rem;
        bottom: -1rem;
        width: 2px;
        background: linear-gradient(to bottom, var(--tour-primary-border), #e5e7eb);
    }
    .stop-timeline-item:last-child .timeline-connector { display: none; }
    .stop-rich > * + * { margin-top: 0.6em; }
    .stop-rich strong { font-weight: 600; color: #374151; }
    .stop-rich em { font-style: italic; }
    .stop-rich a { color: var(--tour-primary, #2C5F5D); font-weight: 500; text-decoration: underline; text-underline-offset: 2px; }
    .stop-rich ul { list-style: disc; padding-left: 1.25em; }
    .stop-rich ol { list-style: decimal; padding-left: 1.25em; }
    .stop-rich li + li { margin-top: 0.2em; }
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
            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 border border-white/25 backdrop-blur-sm px-3 py-1 text-xs font-semibold text-white/90">
                @if($tour->icon_image_url)
                    <img src="{{ $tour->icon_image_url }}" alt="" class="w-4 h-4 rounded-full object-cover">
                    {{ $tourTypeText }}
                @else
                    {{ $tourTypeLabel }}
                @endif
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
                <span><strong class="text-white">{{ $stopCount }}</strong> {{ $stopNoun }}</span>
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
            @if(! $tour->total_driving_km && $streetRoute)
                <div class="flex items-center gap-2 rounded-full bg-white/10 backdrop-blur-sm border border-white/15 px-3 py-1.5 text-sm text-white/90">
                    <span aria-hidden="true">{{ $streetRoute['profile'] === 'walking' ? '🚶' : '🚗' }}</span>
                    <strong class="text-white">{{ number_format($streetRoute['distance_km'], 1) }} km</strong><span class="text-white/60 ml-1">{{ $streetRoute['profile'] === 'walking' ? 'walk' : 'drive' }}</span>
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
            <button type="button" data-share-open="tour-share"
                class="flex items-center gap-2 rounded-full bg-white/10 backdrop-blur-sm border border-white/15 px-3 py-1.5 text-sm text-white/90 hover:bg-white/20 transition-colors cursor-pointer">
                <svg class="w-4 h-4 text-sand-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                </svg>
                <strong class="text-white">Share</strong>
            </button>
        </div>
    </div>
</div>

<!-- Main content -->
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="lg:grid lg:grid-cols-5 lg:gap-8">

            <!-- Map (left 3/5) -->
            <div id="tour-map-sticky" class="lg:col-span-3 w-auto md:w-full md:sticky md:top-20 lg:top-24 z-20 pt-4 pb-6 px-[5px] md:p-0 bg-white md:bg-gray-50 rounded-none md:rounded-2xl -mx-4 md:mx-0">
                <div class="relative h-[260px] md:h-[400px] lg:h-[560px]">
                <div id="tour-map" class="w-full h-full rounded-none md:rounded-2xl overflow-hidden shadow-lg border border-gray-200 bg-gray-200"></div>

                <!-- Reset view -->
                <button type="button" id="tour-map-reset" title="Show the whole route"
                    class="absolute bottom-3 left-3 z-10 inline-flex items-center gap-1.5 bg-white rounded-xl shadow-md px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition-colors border border-gray-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4h4M20 8V4h-4M4 16v4h4M20 16v4h-4"/>
                    </svg>
                    Reset view
                </button>

                @if($mappedStops->count() > 1)
                    <!-- Previous / next stop -->
                    <div id="tour-stepper"
                        class="absolute bottom-8 right-3 z-10 flex items-center rounded-xl border border-gray-200 bg-white shadow-md">
                        <button type="button" id="tour-stepper-prev" onclick="stepTourStop(-1)"
                            class="flex h-8 w-8 items-center justify-center rounded-l-xl text-gray-600 hover:bg-gray-50 disabled:opacity-30 transition-colors"
                            aria-label="Previous stop" disabled>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <span id="tour-stepper-label" class="px-1 text-xs font-semibold text-gray-700 tabular-nums">Start tour</span>
                        <button type="button" id="tour-stepper-next" onclick="stepTourStop(1)"
                            class="flex h-8 w-8 items-center justify-center rounded-r-xl text-gray-600 hover:bg-gray-50 disabled:opacity-30 transition-colors"
                            aria-label="Next stop">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                @endif

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

                @if($mappedStops->isNotEmpty())
                    <div class="mt-3 flex justify-center md:justify-start">
                        <a href="{{ route('map', ['tour' => $tour->urlKey()]) }}"
                            class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                            View on Interactive Map
                        </a>
                    </div>
                @endif
            </div>

            <!-- Stop list (right 2/5) -->
            <div class="lg:col-span-2 mt-6 lg:mt-0">

                @if($tour->description)
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 mb-6"
                        x-data="{ open: false, clamped: false }"
                        x-init="$nextTick(() => clamped = $refs.desc.scrollHeight > $refs.desc.clientHeight + 1)">
                        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">About This Tour</h2>
                        <p x-ref="desc" :class="{ 'line-clamp-4': !open }"
                            class="text-gray-700 text-sm leading-relaxed whitespace-pre-line line-clamp-4">{{ $tour->description }}</p>
                        <button type="button" x-show="clamped" x-cloak @click="open = !open"
                            class="mt-2 text-xs font-semibold text-forest-700 hover:text-accent-600 transition-colors"
                            x-text="open ? 'Show less' : 'Read more'"></button>
                    </div>
                @endif

                @if($tour->video_url)
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 mb-6">
                        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Video</h2>
                        @include('partials.video-embed', ['model' => $tour, 'embedId' => 'tour-video'])
                    </div>
                @endif

                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-bold text-gray-900">
                        Tour Stops
                        <span class="ml-1.5 text-xs font-semibold text-forest-700 bg-forest-50 border border-forest-200 rounded-full px-2 py-0.5">
                            {{ $stopCount }}
                        </span>
                    </h2>
                    @if($mappedStops->isNotEmpty())
                        <p class="text-xs text-gray-400">Click a stop to zoom the map</p>
                    @endif
                </div>


                <!-- Timeline -->
                <div class="space-y-0">
                    @foreach($mappedStops as $stopIndex => $stop)
                        @php
                            $trail = $stop->trail;
                            $displayName = $stop->displayName('Stop ' . $loop->iteration);
                            $coords = $stop->coordinates();
                            $coordsJs = $coords ? '[' . implode(',', $coords) . ']' : 'null';
                            $photoUrls = $stop->photoUrls() ?: array_values(array_filter([$trail?->cover_image_url]));
                            $thumbnailUrl = $photoUrls[0] ?? null;
                        @endphp
                        <div class="stop-timeline-item relative pl-12 pb-4">
                            <!-- Timeline line -->
                            @if(!$loop->last)
                                <div class="timeline-connector"></div>
                            @endif

                            <!-- Number badge, or the activity's icon -->
                            @include('tours._stop-badge', ['stop' => $stop, 'number' => $loop->iteration])

                            <!-- Card -->
                            <div class="stop-card bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden cursor-pointer"
                                id="stop-card-{{ $stopIndex }}"
                                onclick="focusStop({{ $stopIndex }}, {{ $coordsJs }})">

                                @if($thumbnailUrl)
                                    <button type="button"
                                        class="stop-image group relative block w-full aspect-[16/9] overflow-hidden bg-gray-100"
                                        data-photos="{{ json_encode($photoUrls) }}"
                                        data-caption="{{ $displayName }}"
                                        onclick="event.stopPropagation(); openStopImage(this)"
                                        aria-label="View full-size {{ count($photoUrls) > 1 ? 'photos' : 'photo' }} of {{ $displayName }}">
                                        <img src="{{ $thumbnailUrl }}" alt="{{ $displayName }}" loading="lazy"
                                            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                                        <span class="absolute bottom-2 right-2 inline-flex items-center gap-1 rounded-full bg-black/60 px-2.5 py-1 text-[11px] font-semibold text-white backdrop-blur-sm opacity-90 group-hover:opacity-100">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4h4M20 8V4h-4M4 16v4h4M20 16v4h-4"/></svg>
                                            {{ count($photoUrls) > 1 ? count($photoUrls).' photos' : 'View photo' }}
                                        </span>
                                    </button>
                                @endif

                                <!-- Summary row (always visible) -->
                                <div class="flex items-center gap-3 p-3">
                                    <div class="min-w-0 flex-1">
                                        <h3 class="font-bold text-gray-900 text-sm leading-snug">{{ $displayName }}</h3>

                                        <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                            @if($stop->estimated_visit_time)
                                                <span class="text-xs text-gray-400 flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    {{ $stop->estimated_visit_time }}
                                                </span>
                                            @endif
                                            @if($trail)
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
                                            @elseif($stop->facility)
                                                <span class="text-xs bg-amber-50 text-amber-700 rounded px-1.5 py-0.5 font-medium">
                                                    {{ $stop->facility->facility_type_label }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Details -->
                                <div class="px-3.5 pb-3.5">
                                    @php
                                        $fallbackDescription = strip_tags((string) ($trail?->description ?: $stop->facility?->description));
                                    @endphp
                                    @if(filled($stop->description) || filled($fallbackDescription))
                                        <div class="relative" x-data="{ open: false, clamped: false }"
                                            x-init="$nextTick(() => clamped = $refs.desc.scrollHeight > $refs.desc.clientHeight + 1)">
                                            @if(filled($stop->description))
                                                <div x-ref="desc" :class="{ 'max-h-[5.7rem] overflow-hidden': !open }"
                                                    onclick="if (event.target.closest('a')) { event.stopPropagation(); }"
                                                    class="stop-description stop-rich text-sm text-gray-600 leading-relaxed max-h-[5.7rem] overflow-hidden">{!! $stop->descriptionHtml() !!}</div>
                                            @else
                                                <p x-ref="desc" :class="{ 'max-h-[5.7rem] overflow-hidden': !open }"
                                                    class="stop-description text-sm text-gray-600 leading-relaxed whitespace-pre-line max-h-[5.7rem] overflow-hidden">{{ $fallbackDescription }}</p>
                                            @endif
                                            <div x-show="clamped && !open" x-cloak
                                                class="pointer-events-none absolute inset-x-0 bottom-6 h-8 stop-description-fade"></div>
                                            <button type="button" x-show="clamped" x-cloak
                                                @click.stop="open = !open"
                                                class="mt-1.5 inline-flex items-center gap-1 text-xs font-semibold text-forest-700 hover:text-accent-600 transition-colors">
                                                <span x-text="open ? 'Show less' : 'Read more'">Read more</span>
                                                <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </button>
                                        </div>
                                    @endif

                                    @if($stop->driving_notes)
                                        <p class="text-xs text-blue-600 mt-2 flex items-start gap-1">
                                            <svg class="w-3 h-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                            {{ $stop->driving_notes }}
                                        </p>
                                    @endif

                                    <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between gap-2">
                                        @if($trail)
                                            <a href="{{ $trail->publicUrl() }}"
                                                onclick="event.stopPropagation()"
                                                class="text-xs font-semibold text-forest-700 hover:text-accent-600 flex items-center gap-1 transition-colors">
                                                Trail Details
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </a>
                                        @elseif($stop->facility)
                                            <a href="{{ route('map') }}?facility={{ $stop->facility->id }}"
                                                onclick="event.stopPropagation()"
                                                class="text-xs font-semibold text-forest-700 hover:text-accent-600 flex items-center gap-1 transition-colors">
                                                View on Map
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </a>
                                        @elseif($coords)
                                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $coords[0] }},{{ $coords[1] }}"
                                                target="_blank" rel="noopener"
                                                onclick="event.stopPropagation()"
                                                class="text-xs font-semibold text-forest-700 hover:text-accent-600 flex items-center gap-1 transition-colors">
                                                Get Directions
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </a>
                                        @else
                                            <span></span>
                                        @endif

                                        <span class="text-xs text-gray-400 tabular-nums">Stop {{ $loop->iteration }} of {{ $mappedStops->count() }}</span>
                                    </div>
                                </div>
                            </div>

                            @if(! $loop->last && isset($stopLegs[$stopIndex]))
                                @php $leg = $stopLegs[$stopIndex]; @endphp
                                <p class="stop-leg mt-2 ml-1 inline-flex items-center gap-1.5 text-xs font-medium text-gray-500">
                                    <span aria-hidden="true">{{ $leg['mode'] === 'walk' ? '🚶' : '🚗' }}</span>
                                    @php $legDistance = $leg['distance_km'] < 1 ? (round($leg['distance_km'] * 100) * 10).' m' : number_format($leg['distance_km'], 1).' km'; @endphp
                                    <span>{{ $legsAreEstimates ? '~' : '' }}{{ $legDistance }} · {{ $leg['minutes'] }} min {{ $leg['mode'] }} to next stop</span>
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if($roamingStops->isNotEmpty())
                    <!-- Roaming activities: no fixed spot, so listed without a map pin -->
                    <div class="mt-6">
                        <h2 class="text-base font-bold text-gray-900 mb-1">Around downtown</h2>
                        <p class="text-xs text-gray-400 mb-4">These happen all around the event — keep an eye out!</p>
                        <div class="grid gap-3">
                            @foreach($roamingStops as $stop)
                                <div class="flex gap-3 rounded-xl border border-dashed border-gray-300 bg-white p-3 shadow-sm">
                                    @include('tours._stop-badge', ['stop' => $stop, 'number' => null, 'inline' => true])
                                    <div class="min-w-0 flex-1">
                                        <h3 class="font-bold text-gray-900 text-sm leading-snug">{{ $stop->displayName() }}</h3>
                                        @if($stop->estimated_visit_time)
                                            <p class="text-xs font-semibold text-gray-500 mt-0.5">{{ $stop->estimated_visit_time }}</p>
                                        @endif
                                        @if($stop->description)
                                            <div class="stop-rich text-xs text-gray-600 leading-relaxed mt-1">{!! $stop->descriptionHtml() !!}</div>
                                        @endif
                                        @if($stop->driving_notes)
                                            <p class="text-xs text-gray-500 mt-1">{{ $stop->driving_notes }}</p>
                                        @endif
                                    </div>
                                    @php $roamingPhotos = $stop->photoUrls(); @endphp
                                    @if($roamingPhotos)
                                        <button type="button"
                                            class="stop-image relative h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg bg-gray-100"
                                            data-photos="{{ json_encode($roamingPhotos) }}"
                                            data-caption="{{ $stop->displayName() }}"
                                            onclick="openStopImage(this)"
                                            aria-label="View full-size {{ count($roamingPhotos) > 1 ? 'photos' : 'photo' }} of {{ $stop->displayName() }}">
                                            <img src="{{ $roamingPhotos[0] }}" alt="{{ $stop->displayName() }}" loading="lazy"
                                                class="h-full w-full object-cover transition-transform duration-300 hover:scale-105">
                                            @if(count($roamingPhotos) > 1)
                                                <span class="absolute bottom-1 right-1 rounded-full bg-black/60 px-1.5 text-[10px] font-semibold text-white">+{{ count($roamingPhotos) - 1 }}</span>
                                            @endif
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

        </div>
    </div>
</div>

{{-- Full-size stop photo viewer (steps through all of a stop's photos) --}}
<div id="stop-image-viewer"
    class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/90 p-4 sm:p-8"
    role="dialog" aria-modal="true" aria-label="Stop photos"
    onclick="closeStopImage()">
    <span id="stop-image-viewer-counter" class="absolute top-5 left-5 text-sm font-semibold text-white/80 tabular-nums"></span>
    <button type="button" onclick="event.stopPropagation(); closeStopImage()"
        class="absolute top-4 right-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/25 transition-colors"
        aria-label="Close photo">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
    <button type="button" id="stop-image-viewer-prev" onclick="event.stopPropagation(); stepStopImage(-1)"
        class="absolute left-2 sm:left-5 top-1/2 -translate-y-1/2 flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/25 transition-colors"
        aria-label="Previous photo">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </button>
    <button type="button" id="stop-image-viewer-next" onclick="event.stopPropagation(); stepStopImage(1)"
        class="absolute right-2 sm:right-5 top-1/2 -translate-y-1/2 flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/25 transition-colors"
        aria-label="Next photo">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </button>
    <figure class="flex max-h-full max-w-5xl flex-col items-center gap-3" onclick="event.stopPropagation()">
        <img id="stop-image-viewer-img" src="" alt="" class="max-h-[80vh] max-w-full rounded-lg object-contain shadow-2xl select-none">
        <figcaption id="stop-image-viewer-caption" class="text-center text-sm font-medium text-white/85"></figcaption>
    </figure>
</div>

<script>
    const stopImageViewer = { photos: [], position: 0, caption: '', touchStartX: null };

    function openStopImage(trigger) {
        let photos = [];
        try {
            photos = JSON.parse(trigger.dataset.photos || '[]');
        } catch {
            photos = [];
        }
        if (!photos.length) {
            return;
        }

        stopImageViewer.photos = photos;
        stopImageViewer.position = 0;
        stopImageViewer.caption = trigger.dataset.caption || '';
        renderStopImage();

        const viewer = document.getElementById('stop-image-viewer');
        viewer.classList.remove('hidden');
        viewer.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function renderStopImage() {
        const { photos, position, caption } = stopImageViewer;
        const hasMany = photos.length > 1;
        const img = document.getElementById('stop-image-viewer-img');

        img.src = photos[position];
        img.alt = caption;
        document.getElementById('stop-image-viewer-caption').textContent = caption;
        document.getElementById('stop-image-viewer-counter').textContent = hasMany ? `${position + 1} / ${photos.length}` : '';
        document.getElementById('stop-image-viewer-prev').classList.toggle('hidden', !hasMany);
        document.getElementById('stop-image-viewer-next').classList.toggle('hidden', !hasMany);

        // Warm the next photo so stepping feels instant.
        if (hasMany) {
            new Image().src = photos[(position + 1) % photos.length];
        }
    }

    function stepStopImage(direction) {
        const count = stopImageViewer.photos.length;
        if (count < 2) {
            return;
        }
        stopImageViewer.position = (stopImageViewer.position + direction + count) % count;
        renderStopImage();
    }

    function closeStopImage() {
        const viewer = document.getElementById('stop-image-viewer');
        viewer.classList.add('hidden');
        viewer.classList.remove('flex');
        document.getElementById('stop-image-viewer-img').src = '';
        document.body.style.overflow = '';
    }

    function stopImageViewerIsOpen() {
        return !document.getElementById('stop-image-viewer').classList.contains('hidden');
    }

    document.addEventListener('keydown', (e) => {
        if (!stopImageViewerIsOpen()) {
            return;
        }
        if (e.key === 'Escape') {
            closeStopImage();
        } else if (e.key === 'ArrowLeft') {
            stepStopImage(-1);
        } else if (e.key === 'ArrowRight') {
            stepStopImage(1);
        }
    });

    // Swipe left/right on touch screens.
    const stopImageViewerEl = document.getElementById('stop-image-viewer');
    stopImageViewerEl.addEventListener('touchstart', (e) => {
        stopImageViewer.touchStartX = e.changedTouches[0].clientX;
    }, { passive: true });
    stopImageViewerEl.addEventListener('touchend', (e) => {
        if (stopImageViewer.touchStartX === null) {
            return;
        }
        const deltaX = e.changedTouches[0].clientX - stopImageViewer.touchStartX;
        stopImageViewer.touchStartX = null;
        if (Math.abs(deltaX) > 50) {
            stepStopImage(deltaX < 0 ? 1 : -1);
        }
    }, { passive: true });
</script>

@include('tours._map-script')

<script>
    // Stops in list order (keys match the map's stop indexes); roaming stops are excluded.
    const tourStepOrder = @json($mappedStops->keys()->values());
    let currentTourStop = null;

    function stepTourStop(direction, fromIndex = currentTourStop) {
        const position = tourStepOrder.indexOf(fromIndex);
        const target = position === -1
            ? tourStepOrder[direction > 0 ? 0 : tourStepOrder.length - 1]
            : tourStepOrder[position + direction];

        if (target === undefined) {
            return;
        }

        focusStop(target, tourStops[target]?.coords ?? null);
    }

    document.addEventListener('tour:stop-focused', (e) => {
        currentTourStop = e.detail.index;

        const position = tourStepOrder.indexOf(currentTourStop);
        const label = document.getElementById('tour-stepper-label');
        if (!label) {
            return;
        }
        label.textContent = position === -1 ? 'Start tour' : `Stop ${position + 1} of ${tourStepOrder.length}`;
        document.getElementById('tour-stepper-prev').disabled = position <= 0;
        document.getElementById('tour-stepper-next').disabled = position >= tourStepOrder.length - 1;
    });
</script>

@include('partials.share-modal', [
    'shareId' => 'tour-share',
    'kicker' => 'Share This Tour',
    'title' => $tour->title,
    'subtitle' => $tour->tagline,
    'shareText' => 'Check out the '.$tour->title.' tour in Smithers, BC! 🥾⛰️',
    'emailSubject' => 'Check out this tour: '.$tour->title,
    'emailBody' => 'I found this tour and thought you might be interested!'."\n\n".'Tour: '.$tour->title.($tour->tagline ? "\n".$tour->tagline : ''),
])
@endsection
