@extends('layouts.public')

@php
    $pageTitle = 'Explore Northern BC — Road Trips from Smithers';
    $pageDescription = 'Where to drive from Smithers, BC: '
        . $destinations->map(fn ($destination) => $destination['town']->name . ' (' . $destination['drive_label'] . ')')->join(', ', ' and ')
        . '. Trails, fishing lakes and local businesses along Highway 16 and Highway 37.';

    $originName = $origin?->name ?? 'Smithers';

    /**
     * The band a town sits in is also the identity for every trail and lake
     * that belongs to it, so the trail and lake strips can show a drive-time
     * badge without a second query. Keyed by town id.
     */
    $driveByTown = $destinations->keyBy(fn ($destination) => $destination['town']->id);

    /**
     * Picks a card photo out of the media the controller already eager loaded.
     * The Trail::$featured_media_url accessor is avoided on purpose: it runs
     * its own query per call, which would be two round trips per card here.
     */
    $cardPhoto = function ($trail): ?string {
        $media = $trail->trailMedia->firstWhere('is_featured', true) ?? $trail->trailMedia->first();

        return $media ? ($media->getThumbnail() ?? $media->getUrl()) : null;
    };
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageDescription)
@section('canonical', route('explore.index'))

@push('meta')
    @include('partials.seo-meta', ['title' => $pageTitle, 'description' => $pageDescription])
@endpush

@push('styles')
<link href="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.css" rel="stylesheet">
<style>
    /* Terrain imagery sits behind a scrim, so it is dimmed and gently zooms on
       hover rather than being the loud element in the card. Matches the
       treatment on the town hub. */
    .rt-terrain { transition: transform 0.7s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.4s ease; }
    .rt-card:hover .rt-terrain { transform: scale(1.05); opacity: 1; }

    .rt-card { transition: transform 0.3s ease, box-shadow 0.3s ease; box-shadow: 0 2px 12px -6px rgba(25, 54, 52, 0.28); }
    .rt-card:hover { transform: translateY(-4px); box-shadow: 0 26px 50px -18px rgba(25, 54, 52, 0.42); }
    .rt-card:active { transform: translateY(-1px) scale(0.996); }

    /* Small-caps province tag, so ", BC" reads as a label instead of washed-out
       punctuation. Same rule as towns/index. */
    .province-tag {
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .rt-eyebrow {
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
    }

    /* Horizontal strips: scroll-snap on touch, plain grid from lg up. The
       scrollbar is hidden because the peeking next card is the affordance. */
    .rt-strip {
        scroll-snap-type: x mandatory;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .rt-strip::-webkit-scrollbar { display: none; }
    .rt-strip > * { scroll-snap-align: start; }

    /* The chip rail pins under the fixed 5rem navigation. */
    .rt-chip-rail { top: 5rem; }

    .rt-map { height: 420px; }
    @media (min-width: 1024px) { .rt-map { height: 560px; } }

    #rt-map .mapboxgl-popup-content {
        padding: 0;
        border-radius: 0.875rem;
        overflow: hidden;
        box-shadow: 0 18px 40px -12px rgba(25, 54, 52, 0.45);
    }
    #rt-map .mapboxgl-popup-close-button { width: 28px; height: 28px; font-size: 19px; color: #fff; }
</style>
@endpush

@section('content')

{{-- ---------------------------------------------------------------- 1. Hero --}}
<section class="relative flex items-center justify-center hero-gradient overflow-hidden">
    <div class="absolute inset-0 bg-pattern-trees opacity-20 z-15"></div>
    <div class="absolute inset-0 bg-black bg-opacity-40 z-20"></div>

    <div class="relative z-30 text-center text-white max-w-6xl mx-auto px-4 flex flex-col justify-center py-20 lg:py-24">
        <div class="mb-8 fade-in">
            <span class="inline-flex items-center px-6 py-3 bg-white/25 backdrop-blur-sm rounded-full text-white text-sm font-semibold border border-white/30 shadow-lg">
                🛣️ Highway 16 &amp; Highway 37
            </span>
        </div>

        <div class="slide-in-up mb-8">
            <h1 class="text-5xl md:text-7xl font-bold leading-tight text-balance">
                <span class="text-white text-shadow-lg">Explore Northern BC.</span><br>
                <span class="bg-gradient-to-r from-emerald-300 via-sand-200 to-accent-300 bg-clip-text text-transparent">
                    Road trips from {{ $originName }}.
                </span>
            </h1>
        </div>

        <div class="slide-in-up mb-12" style="animation-delay: 0.2s;">
            <p class="text-xl md:text-2xl text-white leading-relaxed max-w-3xl mx-auto text-shadow-md text-pretty">
                Fifteen minutes down the valley or four hours to tidewater — here is what waits at the end
                of every drive out of {{ $originName }}, and how long it takes to get there.
            </p>
        </div>

        {{-- Live stat strip --}}
        <div class="slide-in-up grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-5 max-w-4xl mx-auto w-full" style="animation-delay: 0.35s;">
            @foreach([
                ['value' => $stats['towns'], 'label' => Str::plural('town', $stats['towns']) . ' to drive to'],
                ['value' => $stats['trails'], 'label' => Str::plural('trail', $stats['trails']) . ' beyond ' . $originName],
                ['value' => $stats['lakes'], 'label' => 'fishing ' . Str::plural('lake', $stats['lakes'])],
                ['value' => $stats['businesses'], 'label' => 'local ' . Str::plural('business', $stats['businesses'])],
            ] as $stat)
                <div class="rounded-2xl bg-white/15 backdrop-blur-sm border border-white/25 px-4 py-5 shadow-lg">
                    <div class="text-3xl lg:text-4xl font-bold text-white tabular-nums leading-none">{{ number_format($stat['value']) }}</div>
                    <div class="rt-eyebrow text-white/70 mt-2 leading-snug">{{ $stat['label'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

@if($destinations->isEmpty())

    <section class="section bg-white">
        <div class="max-w-2xl mx-auto px-4 text-center">
            <h2 class="section-title text-forest-600 text-balance">No destinations published yet</h2>
            <p class="section-subtitle text-pretty">
                Once other communities are added they will appear here, sorted by how far they are from {{ $originName }}.
            </p>
            <a href="{{ route('trails.index') }}" class="btn-primary">Browse all trails</a>
        </div>
    </section>

@else

{{-- ------------------------------------------- 2 + 3. Selector and town cards --}}
<div x-data="{ band: 'all' }">

    {{-- Sticky distance selector --}}
    <div class="rt-chip-rail sticky z-30 bg-white/95 backdrop-blur-md border-b border-forest-100 shadow-sm">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center gap-4 py-3.5">
                <span class="rt-eyebrow text-forest-400 hidden md:block flex-shrink-0">How far?</span>

                <div class="rt-strip flex items-center gap-2 overflow-x-auto -mx-1 px-1">
                    <button type="button" @click="band = 'all'"
                            class="flex-shrink-0 inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold border transition-all duration-200"
                            :class="band === 'all'
                                ? 'bg-forest-700 text-white border-forest-700 shadow-md'
                                : 'bg-white text-forest-700 border-forest-200 hover:border-forest-400 hover:bg-forest-50'">
                        All trips
                        <span class="tabular-nums text-xs opacity-70">{{ $destinations->count() }}</span>
                    </button>

                    @foreach($bands as $band)
                        <button type="button" @click="band = '{{ $band['key'] }}'"
                                @class([
                                    'flex-shrink-0 inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold border transition-all duration-200',
                                    'opacity-40' => $band['towns']->isEmpty(),
                                ])
                                @disabled($band['towns']->isEmpty())
                                :class="band === '{{ $band['key'] }}'
                                    ? 'bg-forest-700 text-white border-forest-700 shadow-md'
                                    : 'bg-white text-forest-700 border-forest-200 hover:border-forest-400 hover:bg-forest-50'">
                            {{ $band['label'] }}
                            <span class="tabular-nums text-xs opacity-70">{{ $band['towns']->count() }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Road-trip town cards, one band at a time --}}
    <section class="section bg-white">
        <div class="max-w-7xl mx-auto px-4 flex flex-col gap-16 lg:gap-20">

            @foreach($bands as $band)
                @continue($band['towns']->isEmpty())

                {{-- No x-cloak: the default band is "all", so every section is
                     correct before Alpine boots and stays visible if it never does. --}}
                <div x-show="band === 'all' || band === '{{ $band['key'] }}'"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0">

                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8 pb-5 border-b-2 border-forest-100">
                        <div>
                            <p class="rt-eyebrow text-accent-600 mb-2">
                                {{ $loop->iteration }} / {{ $bands->count() }} &nbsp;·&nbsp; from {{ $originName }}
                            </p>
                            <h2 class="font-display text-3xl lg:text-4xl font-bold text-forest-800 leading-none">{{ $band['label'] }}</h2>
                        </div>
                        <p class="text-gray-500 md:text-right md:max-w-sm text-pretty leading-relaxed">{{ $band['blurb'] }}</p>
                    </div>

                    <div class="grid lg:grid-cols-2 gap-6 lg:gap-8">
                        @foreach($band['towns'] as $destination)
                            @php($town = $destination['town'])
                            @php($accent = $town->colorOrDefault())
                            @php($artwork = $town->hero_image ? asset('storage/'.$town->hero_image) : $town->staticMapUrl(760, 420, null, false))

                            <article class="rt-card group relative flex flex-col rounded-3xl overflow-hidden bg-white ring-1 ring-forest-100"
                                     style="--accent: {{ $accent }}">

                                {{-- Artwork + identity --}}
                                <div class="relative h-56 lg:h-64 overflow-hidden bg-forest-800">
                                    @if($artwork)
                                        <img src="{{ $artwork }}"
                                             alt="Terrain around {{ $town->name }}, British Columbia"
                                             loading="lazy"
                                             class="rt-terrain absolute inset-0 w-full h-full object-cover opacity-90">
                                    @endif
                                    <div class="absolute inset-0 bg-gradient-to-t from-forest-900 via-forest-900/55 to-forest-900/10"></div>
                                    <div class="absolute inset-0 bg-pattern-contour opacity-25 mix-blend-overlay"></div>

                                    {{-- Drive-time badge: the one element that must stay
                                         visible at every width, so it is pinned top-left. --}}
                                    <div class="absolute top-4 left-4 inline-flex items-stretch rounded-xl overflow-hidden shadow-lg">
                                        <span class="flex items-center gap-1.5 bg-white px-3 py-2 text-sm font-bold text-forest-800 tabular-nums">
                                            <svg class="w-4 h-4 text-accent-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ $destination['drive_label'] }}
                                        </span>
                                        <span class="flex items-center bg-forest-800 px-3 py-2 text-sm font-semibold text-white/85 tabular-nums">
                                            {{ $destination['road_km'] }} km
                                        </span>
                                    </div>

                                    <span class="absolute top-4 right-4 inline-flex items-center rounded-md px-2.5 py-1.5 text-white shadow-lg rt-eyebrow"
                                          style="background-color: var(--accent)">
                                        {{ $band['label'] }}
                                    </span>

                                    <div class="absolute inset-x-0 bottom-0 p-5 lg:p-6">
                                        <h3 class="font-display text-3xl lg:text-4xl font-bold text-white leading-none text-shadow-md">
                                            {{ $town->name }}<span class="province-tag align-middle ml-2.5 text-white/60">{{ $town->province_code }}</span>
                                        </h3>
                                        @if($town->tagline)
                                            <p class="text-white/85 mt-2.5 leading-snug line-clamp-2 text-pretty">{{ $town->tagline }}</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Counts --}}
                                <div class="grid grid-cols-3 divide-x divide-forest-100 border-b border-forest-100">
                                    @foreach([
                                        ['value' => $destination['trails_count'], 'label' => Str::plural('trail', $destination['trails_count'])],
                                        ['value' => $destination['lakes_count'], 'label' => Str::plural('lake', $destination['lakes_count'])],
                                        ['value' => $destination['businesses_count'], 'label' => Str::plural('business', $destination['businesses_count'])],
                                    ] as $count)
                                        <div class="px-4 py-4 text-center">
                                            <div class="text-2xl font-bold tabular-nums leading-none"
                                                 style="color: {{ $count['value'] ? 'var(--accent)' : '#9CA3AF' }}">{{ $count['value'] }}</div>
                                            <div class="rt-eyebrow text-gray-400 mt-1.5">{{ $count['label'] }}</div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Named highlights --}}
                                <div class="flex-1 p-5 lg:p-6">
                                    @if(filled($destination['highlights']))
                                        <p class="rt-eyebrow text-gray-400 mb-3.5">While you are there</p>
                                        <ul class="flex flex-col gap-2.5">
                                            @foreach($destination['highlights'] as $highlight)
                                                <li>
                                                    <a href="{{ $highlight['url'] }}"
                                                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 -mx-1 hover:bg-forest-50 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-forest-500">
                                                        <span class="w-1.5 h-9 rounded-full flex-shrink-0" style="background-color: var(--accent)" aria-hidden="true"></span>
                                                        <span class="min-w-0 flex-1">
                                                            <span class="block text-[0.625rem] font-bold uppercase tracking-[0.14em] text-gray-400">{{ $highlight['label'] }}</span>
                                                            <span class="block font-semibold text-gray-900 truncate leading-snug">{{ $highlight['name'] }}</span>
                                                        </span>
                                                        <span class="hidden sm:block text-xs text-gray-500 flex-shrink-0 tabular-nums">{{ $highlight['meta'] }}</span>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <div class="rounded-xl bg-sand-50 border border-sand-200 px-4 py-5 text-center">
                                            <p class="rt-eyebrow text-sand-600 mb-1.5">Mapping in progress</p>
                                            <p class="text-sm text-gray-500 text-pretty leading-relaxed">
                                                {{ $town->name }} is on the map but has no published trails yet. It is still
                                                {{ $destination['drive_label'] }} of open highway.
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                {{-- CTA --}}
                                <a href="{{ route('towns.show', $town) }}"
                                   class="flex items-center justify-between gap-3 px-5 lg:px-6 py-4 text-white font-semibold transition-opacity hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-forest-500"
                                   style="background-color: var(--accent)">
                                    <span>Plan a trip to {{ $town->name }}</span>
                                    <svg class="w-5 h-5 transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <p class="text-xs text-gray-400 text-center">
                Drive times are highway estimates from {{ $originName }}, not live traffic. Terrain imagery &copy;
                <a href="https://www.mapbox.com/about/maps/" target="_blank" rel="noopener" class="hover:text-gray-600 underline underline-offset-2">Mapbox</a>
                &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener" class="hover:text-gray-600 underline underline-offset-2">OpenStreetMap</a>
            </p>
        </div>
    </section>
</div>

{{-- --------------------------------------------------- 4. Trails beyond town --}}
@if($featuredTrails->isNotEmpty())
<section class="section bg-forest-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
            <div>
                <p class="rt-eyebrow text-accent-600 mb-2">Worth the drive</p>
                <h2 class="font-display text-3xl lg:text-4xl font-bold text-forest-800 leading-tight text-balance">
                    Trails beyond {{ $originName }}
                </h2>
            </div>
            <p class="text-gray-500 md:text-right md:max-w-sm text-pretty leading-relaxed">
                The best-rated routes in the other {{ Str::plural('town', $stats['towns']) }}, each tagged with the
                drive that gets you to the trailhead.
            </p>
        </div>

        <div class="rt-strip flex lg:grid lg:grid-cols-4 gap-5 overflow-x-auto lg:overflow-visible -mx-4 px-4 lg:mx-0 lg:px-0 pb-2">
            @foreach($featuredTrails as $trail)
                @php($drive = $driveByTown->get($trail->town_id))
                @php($accent = $trail->town?->colorOrDefault() ?? \App\Models\Town::DEFAULT_COLOR)
                @php($photo = $cardPhoto($trail))

                <a href="{{ route('trails.show', $trail->id) }}"
                   class="rt-card group flex-shrink-0 w-[17rem] lg:w-auto flex flex-col rounded-2xl overflow-hidden bg-white ring-1 ring-forest-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-forest-500"
                   style="--accent: {{ $accent }}">

                    <div class="relative h-40 overflow-hidden bg-forest-800">
                        @if($photo)
                            <img src="{{ $photo }}" alt="{{ $trail->name }}" loading="lazy"
                                 class="rt-terrain absolute inset-0 w-full h-full object-cover">
                        @else
                            <div class="absolute inset-0 flex items-center justify-center bg-emerald-600">
                                <img src="{{ asset('images/xplore-smithers-logo.png') }}" alt="" class="w-24 h-24 object-contain opacity-80">
                            </div>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-black/55 to-transparent"></div>

                        @if($drive)
                            <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 rounded-lg bg-white/95 backdrop-blur-sm px-2.5 py-1.5 text-xs font-bold text-forest-800 shadow-md tabular-nums">
                                <svg class="w-3.5 h-3.5 text-accent-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $drive['drive_label'] }}
                            </span>
                        @endif

                        @if($trail->is_featured)
                            <span class="absolute top-3 right-3 badge bg-amber-400 text-amber-900 font-bold shadow-md">⭐</span>
                        @endif

                        {{-- Town identity: a colour bar the width of the card, so the
                             palette reads as a system rather than as decoration. --}}
                        <span class="absolute inset-x-0 bottom-0 h-1.5" style="background-color: var(--accent)" aria-hidden="true"></span>
                    </div>

                    <div class="flex-1 flex flex-col p-4">
                        <span class="inline-flex items-center gap-1.5 rt-eyebrow mb-2" style="color: var(--accent)">
                            <span class="w-2 h-2 rounded-full" style="background-color: var(--accent)" aria-hidden="true"></span>
                            {{ $trail->town?->name }}
                        </span>

                        <h3 class="font-bold text-gray-900 leading-snug line-clamp-2 group-hover:text-accent-600 transition-colors">
                            {{ $trail->name }}
                        </h3>

                        <div class="mt-auto pt-4 flex items-center justify-between gap-2">
                            <span class="text-sm text-gray-500 tabular-nums">
                                @if($trail->distance_km)
                                    {{ number_format((float) $trail->distance_km, 1) }} km
                                @endif
                                @if($trail->elevation_gain_m)
                                    <span class="text-gray-300">·</span> {{ number_format((int) $trail->elevation_gain_m) }} m
                                @endif
                            </span>
                            @if($trail->difficulty_level)
                                <span class="difficulty-badge difficulty-{{ (int) $trail->difficulty_level }}">{{ $trail->difficulty_level }}/5</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('trails.index') }}" class="btn-secondary">Browse every trail</a>
        </div>
    </div>
</section>
@endif

{{-- ------------------------------------------------------ 5. Fishing lakes --}}
@if($fishingLakes->isNotEmpty())
<section class="section bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
            <div>
                <p class="rt-eyebrow text-accent-600 mb-2">Pack the rod</p>
                <h2 class="font-display text-3xl lg:text-4xl font-bold text-forest-800 leading-tight text-balance">
                    Lakes down the highway
                </h2>
            </div>
            <p class="text-gray-500 md:text-right md:max-w-sm text-pretty leading-relaxed">
                Every one of these is outside {{ $originName }} — close enough for a morning cast, far enough
                to have the shoreline to yourself.
            </p>
        </div>

        <div class="rt-strip flex lg:grid lg:grid-cols-4 gap-5 overflow-x-auto lg:overflow-visible -mx-4 px-4 lg:mx-0 lg:px-0 pb-2">
            @foreach($fishingLakes as $lake)
                @php($drive = $driveByTown->get($lake->town_id))
                @php($accent = $lake->town?->colorOrDefault() ?? \App\Models\Town::DEFAULT_COLOR)
                @php($photo = $cardPhoto($lake))
                @php($species = collect($lake->fish_species ?? [])->filter()->values())

                <a href="{{ route('trails.show', $lake->id) }}"
                   class="rt-card group flex-shrink-0 w-[17rem] lg:w-auto flex flex-col rounded-2xl overflow-hidden bg-white ring-1 ring-forest-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-forest-500"
                   style="--accent: {{ $accent }}">

                    <div class="relative h-40 overflow-hidden bg-forest-800">
                        @if($photo)
                            <img src="{{ $photo }}" alt="{{ $lake->name }}" loading="lazy"
                                 class="rt-terrain absolute inset-0 w-full h-full object-cover">
                        @else
                            <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-emerald-600 to-forest-800">
                                <span class="text-6xl opacity-70" aria-hidden="true">🐟</span>
                            </div>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-black/55 to-transparent"></div>

                        @if($drive)
                            <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 rounded-lg bg-white/95 backdrop-blur-sm px-2.5 py-1.5 text-xs font-bold text-forest-800 shadow-md tabular-nums">
                                <svg class="w-3.5 h-3.5 text-accent-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $drive['drive_label'] }}
                            </span>
                        @endif

                        @if($lake->best_fishing_season)
                            <span class="absolute bottom-3 right-3 inline-flex items-center rounded-md bg-black/55 backdrop-blur-sm px-2 py-1 text-[0.6875rem] font-semibold text-white capitalize">
                                Best in {{ Str::lower($lake->best_fishing_season) }}
                            </span>
                        @endif

                        <span class="absolute inset-x-0 bottom-0 h-1.5" style="background-color: var(--accent)" aria-hidden="true"></span>
                    </div>

                    <div class="flex-1 flex flex-col p-4">
                        <span class="inline-flex items-center gap-1.5 rt-eyebrow mb-2" style="color: var(--accent)">
                            <span class="w-2 h-2 rounded-full" style="background-color: var(--accent)" aria-hidden="true"></span>
                            {{ $lake->town?->name }}
                        </span>

                        <h3 class="font-bold text-gray-900 leading-snug line-clamp-2 group-hover:text-accent-600 transition-colors">
                            {{ $lake->name }}
                        </h3>

                        <div class="mt-auto pt-4">
                            @if($species->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($species->take(3) as $fish)
                                        <span class="inline-flex items-center rounded-md bg-primary-50 border border-primary-100 px-2 py-0.5 text-[0.6875rem] font-medium text-forest-700">{{ $fish }}</span>
                                    @endforeach
                                    @if($species->count() > 3)
                                        <span class="inline-flex items-center px-1 text-[0.6875rem] font-medium text-gray-400 tabular-nums">+{{ $species->count() - 3 }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-sm text-gray-400">Fishing lake</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('fishing-lakes.index') }}" class="btn-secondary">See all fishing lakes</a>
        </div>
    </div>
</section>
@endif

{{-- --------------------------------------------------- 6. Support local --}}
@if($businessesByTown->isNotEmpty())
<section class="section bg-forest-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
            <p class="rt-eyebrow text-accent-600 mb-2">Spend it where you stop</p>
            <h2 class="section-title text-forest-700 text-balance">Support local along the way</h2>
            <p class="section-subtitle text-pretty">
                A road trip is only as good as the coffee, the bed and the tackle shop at the other end.
                These are the businesses in the towns you are driving to.
            </p>
        </div>

        <div class="flex flex-col gap-14">
            @foreach($businessesByTown as $group)
                @php($accent = $group['town']->colorOrDefault())
                <div>
                    <div class="flex flex-wrap items-center gap-3 mb-6 pb-4 border-b border-forest-200">
                        <span class="w-3.5 h-3.5 rounded-full flex-shrink-0" style="background-color: {{ $accent }}" aria-hidden="true"></span>
                        <h3 class="font-display text-2xl font-bold text-forest-800 leading-none">{{ $group['town']->name }}</h3>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white border border-forest-200 px-3 py-1 text-xs font-semibold text-forest-700 tabular-nums">
                            <svg class="w-3.5 h-3.5 text-accent-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $group['drive_label'] }} from {{ $originName }}
                        </span>
                        <a href="{{ route('towns.show', $group['town']) }}" class="ml-auto text-sm font-semibold text-forest-600 hover:text-accent-600 transition-colors inline-flex items-center gap-1">
                            {{ $group['town']->name }} guide
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($group['businesses'] as $business)
                            @include('businesses._card', ['business' => $business])
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-12 text-center">
            <a href="{{ route('businesses.public.index') }}" class="btn-secondary">See the full directory</a>
        </div>
    </div>
</section>
@endif

{{-- --------------------------------------------------------- 7. Multi-day tours --}}
@if($tours->isNotEmpty())
<section class="section bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
            <p class="rt-eyebrow text-accent-600 mb-2">Routes already planned</p>
            <h2 class="section-title text-forest-700 text-balance">Self-guided tours out of town</h2>
            <p class="section-subtitle text-pretty">
                Stop-by-stop driving routes someone else has already worked out, with the whole day mapped.
            </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($tours as $tour)
                @php($drive = $driveByTown->get($tour->town_id))
                <div class="flex flex-col gap-2">
                    @if($tour->town)
                        <div class="flex flex-wrap items-center gap-2 px-1">
                            <span class="inline-flex items-center gap-1.5 rt-eyebrow" style="color: {{ $tour->town->colorOrDefault() }}">
                                <span class="w-2 h-2 rounded-full" style="background-color: {{ $tour->town->colorOrDefault() }}" aria-hidden="true"></span>
                                {{ $tour->town->name }}
                            </span>
                            @if($drive)
                                <span class="text-xs text-gray-400 tabular-nums">· {{ $drive['drive_label'] }} from {{ $originName }}</span>
                            @endif
                        </div>
                    @endif
                    @include('tours._card', ['tour' => $tour])
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ------------------------------------------------------------ 8. Map teaser --}}
<section class="section bg-forest-900 relative overflow-hidden">
    <div class="absolute inset-0 bg-pattern-contour opacity-10"></div>

    <div class="relative max-w-7xl mx-auto px-4">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 mb-8">
            <div>
                <p class="rt-eyebrow text-accent-400 mb-2">The whole region</p>
                <h2 class="font-display text-3xl lg:text-4xl font-bold text-white leading-tight text-balance">
                    Every road out of {{ $originName }}
                </h2>
                <p class="text-forest-200 mt-3 max-w-xl text-pretty leading-relaxed">
                    {{ $originName }} sits in the middle. Each colour is a town, and each colour is used the same
                    way everywhere else on this site.
                </p>
            </div>

            <a href="{{ route('map') }}"
               class="inline-flex items-center justify-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-semibold px-6 py-3.5 rounded-xl shadow-lg transition-all duration-300 hover:scale-105 active:scale-95 flex-shrink-0">
                Open the interactive map
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <div class="rounded-3xl overflow-hidden shadow-2xl ring-1 ring-white/10">
            <div id="rt-map" class="rt-map w-full bg-forest-800"
                 role="application"
                 aria-label="Map of {{ $originName }} and the towns around it">
                <div id="rt-map-skeleton" class="w-full h-full animate-pulse bg-gradient-to-br from-forest-800 via-forest-700 to-forest-800"></div>
            </div>
        </div>

        {{-- Legend doubles as a jump list; the colours match the map markers --}}
        <div class="rt-strip mt-6 flex gap-2.5 overflow-x-auto pb-1">
            <span class="flex-shrink-0 inline-flex items-center gap-2 rounded-full bg-white/10 border border-white/20 px-3.5 py-2 text-sm font-semibold text-white">
                <span class="w-2.5 h-2.5 rounded-full ring-2 ring-white/60" style="background-color: {{ $origin?->colorOrDefault() }}" aria-hidden="true"></span>
                {{ $originName }} · start here
            </span>
            @foreach($destinations as $destination)
                <a href="{{ route('towns.show', $destination['town']) }}"
                   class="flex-shrink-0 inline-flex items-center gap-2 rounded-full bg-white/5 hover:bg-white/15 border border-white/15 px-3.5 py-2 text-sm font-medium text-forest-100 transition-colors">
                    <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $destination['town']->colorOrDefault() }}" aria-hidden="true"></span>
                    {{ $destination['town']->name }}
                    <span class="text-forest-300 tabular-nums text-xs">{{ $destination['drive_label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>

@endif

{{-- --------------------------------------------------------- 9. Closing CTA --}}
<section class="section cta-section">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-4xl font-bold text-white mb-6 text-balance">Pick a direction and go</h2>
        <p class="text-xl text-emerald-100 mb-8 text-pretty">
            Save the trails you want to hit, take the maps offline, and find out what is at the far end of
            Highway 16 before you turn the key.
        </p>
        <div class="flex flex-col md:flex-row gap-4 justify-center">
            <a href="{{ route('map') }}"
               class="bg-white text-forest-600 hover:bg-forest-50 font-semibold py-4 px-10 rounded-xl transition-all duration-300 text-lg shadow-xl hover:scale-105 active:scale-95">
                See the full map
            </a>
            <a href="{{ route('towns.index') }}"
               class="bg-transparent border-2 border-white text-white hover:bg-white hover:text-forest-700 font-semibold py-4 px-10 rounded-xl transition-all duration-300 text-lg shadow-xl hover:scale-105 active:scale-95">
                Browse by town
            </a>
        </div>

        <div class="mt-10">
            @include('partials.app-promo-banner')
        </div>
    </div>
</section>

@endsection

@push('scripts')
@if($mapConfig && $mapboxToken)
<script src="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.js"></script>
<script>
/**
 * Regional teaser map: the origin plus one marker per town in that town's own
 * colour, and a dashed line from the origin to each of them so the page's
 * "road trips from here" idea is legible at a glance. Deliberately simple —
 * the real thing lives at /map.
 */
(function () {
    const config = @json($mapConfig);
    const token = @json($mapboxToken);
    const skeleton = document.getElementById('rt-map-skeleton');

    if (!token || !window.mapboxgl) {
        return;
    }

    mapboxgl.accessToken = token;

    // Stored coordinates are [lat, lng]; Mapbox wants [lng, lat].
    const toLngLat = (coords) => [coords[1], coords[0]];

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    })[character]);

    const map = new mapboxgl.Map({
        container: 'rt-map',
        style: 'mapbox://styles/mapbox/outdoors-v12',
        center: toLngLat(config.origin.coordinates),
        zoom: 6,
        cooperativeGestures: true
    });

    map.addControl(new mapboxgl.NavigationControl(), 'top-right');

    const originLngLat = toLngLat(config.origin.coordinates);

    const marker = (place, isOrigin) => {
        const element = document.createElement('a');
        element.href = place.url;
        element.title = place.name;
        element.style.cssText = [
            'display:flex', 'align-items:center', 'justify-content:center',
            'width:' + (isOrigin ? '30px' : '22px'),
            'height:' + (isOrigin ? '30px' : '22px'),
            'border-radius:9999px',
            'background:' + place.color,
            'border:' + (isOrigin ? '4px' : '3px') + ' solid #fff',
            'box-shadow:0 3px 10px rgba(0,0,0,.45)',
            'cursor:pointer'
        ].join(';');

        if (isOrigin) {
            const pulse = document.createElement('span');
            pulse.style.cssText = 'width:8px;height:8px;border-radius:9999px;background:#fff';
            element.appendChild(pulse);
        }

        const detail = isOrigin
            ? 'Your starting point'
            : escapeHtml(place.drive) + ' &middot; ' + escapeHtml(String(place.road_km)) + ' km';

        const popup = new mapboxgl.Popup({ offset: isOrigin ? 20 : 16, maxWidth: '240px' }).setHTML(
            '<div style="padding:14px 16px;background:' + place.color + ';color:#fff">' +
                '<div style="font-size:11px;letter-spacing:.14em;text-transform:uppercase;opacity:.75">' +
                    (isOrigin ? 'Start' : 'Road trip') +
                '</div>' +
                '<div style="font-size:18px;font-weight:700;line-height:1.2;margin-top:4px">' + escapeHtml(place.name) + '</div>' +
                '<div style="font-size:13px;opacity:.9;margin-top:6px">' + detail + '</div>' +
            '</div>'
        );

        return new mapboxgl.Marker({ element: element, anchor: 'center' })
            .setLngLat(toLngLat(place.coordinates))
            .setPopup(popup)
            .addTo(map);
    };

    map.on('load', function () {
        if (skeleton) {
            skeleton.remove();
        }

        map.addSource('rt-routes', {
            type: 'geojson',
            data: {
                type: 'FeatureCollection',
                features: config.towns.map(function (town) {
                    return {
                        type: 'Feature',
                        properties: { color: town.color },
                        geometry: { type: 'LineString', coordinates: [originLngLat, toLngLat(town.coordinates)] }
                    };
                })
            }
        });

        map.addLayer({
            id: 'rt-routes-line',
            type: 'line',
            source: 'rt-routes',
            layout: { 'line-cap': 'round' },
            paint: {
                'line-color': ['get', 'color'],
                'line-width': 2.5,
                'line-opacity': 0.75,
                'line-dasharray': [1.5, 1.5]
            }
        });

        marker(config.origin, true);
        config.towns.forEach(function (town) {
            marker(town, false);
        });

        const bounds = new mapboxgl.LngLatBounds();
        bounds.extend(originLngLat);
        config.towns.forEach(function (town) {
            bounds.extend(toLngLat(town.coordinates));
        });

        map.fitBounds(bounds, { padding: { top: 70, bottom: 70, left: 60, right: 60 }, duration: 0 });
    });
})();
</script>
@endif
@endpush
