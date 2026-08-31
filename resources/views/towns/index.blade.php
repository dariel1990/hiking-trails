@extends('layouts.public')

@php
    $pageTitle = 'Hiking Trails by Town — Northern British Columbia';
    $pageDescription = 'Browse hiking trails, fishing lakes and things to do in '
        . $towns->pluck('name')->join(', ', ' and ')
        . ', British Columbia.';

    // The town with the most mapped trails leads the grid; the rest follow in
    // their configured order. One large tile beats another row of equal cards.
    $featured = $towns->sortByDesc('hiking_trails_count')->first();
    $rest = $towns->reject(fn ($town) => $featured && $town->is($featured))->values();

    $totalTrails = $towns->sum('hiking_trails_count');
    $totalLakes = $towns->sum('fishing_lakes_count');
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageDescription)
@section('canonical', route('towns.index'))

@push('meta')
    @include('partials.seo-meta', ['title' => $pageTitle, 'description' => $pageDescription])
@endpush

@push('styles')
<style>
    /* Terrain thumbnails sit behind a scrim, so they are dimmed and gently
       zoom on hover rather than being the loud element in the tile. */
    .town-terrain { transition: transform 0.7s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.4s ease; }
    .town-lead:hover .town-terrain,
    .town-row:hover .town-terrain { transform: scale(1.06); opacity: 1; }

    .town-lead,
    .town-row { transition: transform 0.3s ease, box-shadow 0.3s ease; }

    /* Shadows carry the forest hue rather than neutral black. */
    .town-lead { box-shadow: 0 18px 40px -12px rgba(25, 54, 52, 0.35); }
    .town-lead:hover { transform: translateY(-4px); box-shadow: 0 30px 60px -12px rgba(25, 54, 52, 0.45); }
    .town-lead:active { transform: translateY(-1px) scale(0.995); }

    .town-row { box-shadow: 0 2px 10px -4px rgba(25, 54, 52, 0.22); }
    .town-row:hover { transform: translateY(-2px); box-shadow: 0 16px 32px -10px rgba(25, 54, 52, 0.3); }
    .town-row:active { transform: translateY(0) scale(0.995); }
    .town-row:hover .town-row-arrow { transform: translateX(0.3rem); }
    .town-row-arrow { transition: transform 0.25s ease, color 0.25s ease; }

    /* Small-caps province tag, so ", BC" reads as a label instead of washed-out punctuation. */
    .province-tag {
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }
</style>
@endpush

@section('content')

{{-- Hero --}}
<section class="relative flex items-center justify-center hero-gradient overflow-hidden">
    <div class="absolute inset-0 bg-pattern-trees opacity-20 z-15"></div>
    <div class="absolute inset-0 bg-black bg-opacity-40 z-20"></div>

    <div class="relative z-30 text-center text-white max-w-6xl mx-auto px-4 flex flex-col justify-center py-20">
        <div class="mb-8 fade-in">
            <span class="inline-flex items-center px-6 py-3 bg-white/25 backdrop-blur-sm rounded-full text-white text-sm font-semibold border border-white/30 shadow-lg">
                🧭 {{ $towns->count() }} {{ Str::plural('community', $towns->count()) }} along Highway 16
            </span>
        </div>

        <div class="slide-in-up mb-8">
            <h1 class="text-5xl md:text-7xl font-bold leading-tight text-balance">
                <span class="text-white text-shadow-lg">Every trail,</span><br>
                <span class="bg-gradient-to-r from-emerald-300 via-sand-200 to-accent-300 bg-clip-text text-transparent">
                    town by town
                </span>
            </h1>
        </div>

        <div class="slide-in-up mb-12" style="animation-delay: 0.2s;">
            <p class="text-xl md:text-2xl text-white leading-relaxed max-w-3xl mx-auto text-shadow-md text-pretty">
                {{ number_format($totalTrails) }} mapped trails and {{ number_format($totalLakes) }} fishing lakes
                across northern British Columbia, grouped by the community closest to each trailhead.
            </p>
        </div>

        @include('partials.app-promo-banner')
    </div>
</section>

{{-- Town grid --}}
<section class="section bg-white">
    <div class="max-w-7xl mx-auto px-4">

        <div class="text-center mb-12">
            <h2 class="section-title text-forest-600 text-balance">Pick a starting point</h2>
            <p class="section-subtitle text-pretty">
                Each town page pulls in its own trails, lakes, tours and local businesses, with a map
                centred on the valley around it.
            </p>
        </div>

        @if($towns->isEmpty())
            <div class="text-center py-20">
                <div class="max-w-md mx-auto">
                    <div class="w-24 h-24 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">No towns published yet</h3>
                    <p class="text-gray-600 mb-8 text-pretty">Town pages are on the way. Browse the full trail list in the meantime.</p>
                    <a href="{{ route('trails.index') }}" class="btn-primary">View all trails</a>
                </div>
            </div>
        @else
            <div class="grid lg:grid-cols-12 gap-6 lg:gap-8">

                {{-- Lead tile: one continuous panel, so there is no empty body
                     below the artwork when a town has no hero photo. --}}
                @if($featured)
                    @php($leadMap = $featured->hero_image ? asset('storage/'.$featured->hero_image) : $featured->staticMapUrl(740, 720, null, false))
                    <article class="lg:col-span-7">
                        <a href="{{ route('towns.show', $featured) }}"
                           class="town-lead group relative flex flex-col justify-end h-full min-h-[26rem] lg:min-h-[34rem] rounded-3xl overflow-hidden bg-forest-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2">

                            @if($leadMap)
                                <img src="{{ $leadMap }}"
                                     alt="Terrain around {{ $featured->name }}, British Columbia"
                                     class="town-terrain absolute inset-0 w-full h-full object-cover opacity-90">
                            @endif

                            {{-- Bottom-weighted scrim keeps the type legible without flattening the map --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-forest-900 via-forest-900/70 to-forest-900/10"></div>
                            <div class="absolute inset-0 bg-pattern-contour opacity-30 mix-blend-overlay"></div>

                            <span class="absolute top-6 left-6 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-accent-500 text-white text-[0.6875rem] font-bold uppercase tracking-[0.12em] shadow-lg">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M10 2l2.4 5.3 5.6.6-4.2 3.9 1.2 5.7L10 14.6 5 17.5l1.2-5.7L2 7.9l5.6-.6z"/></svg>
                                Most trails
                            </span>

                            <div class="relative p-7 lg:p-10">
                                <h3 class="font-display text-4xl lg:text-5xl font-bold text-white leading-none text-shadow-md">
                                    {{ $featured->name }}<span class="province-tag align-middle ml-3 text-emerald-300/90">{{ $featured->province_code }}</span>
                                </h3>

                                @if($featured->tagline)
                                    <p class="text-white/85 mt-4 max-w-lg leading-relaxed text-pretty">{{ $featured->tagline }}</p>
                                @endif

                                <div class="flex flex-wrap items-end justify-between gap-6 mt-8 pt-6 border-t border-white/20">
                                    <dl class="flex items-end gap-8">
                                        <div>
                                            <dd class="text-3xl lg:text-4xl font-bold text-white tabular-nums leading-none">{{ $featured->hiking_trails_count }}</dd>
                                            <dt class="text-[0.6875rem] uppercase tracking-[0.12em] text-white/60 mt-1.5">Trails</dt>
                                        </div>
                                        @if($featured->fishing_lakes_count)
                                            <div>
                                                <dd class="text-3xl lg:text-4xl font-bold text-white tabular-nums leading-none">{{ $featured->fishing_lakes_count }}</dd>
                                                <dt class="text-[0.6875rem] uppercase tracking-[0.12em] text-white/60 mt-1.5">Lakes</dt>
                                            </div>
                                        @endif
                                    </dl>

                                    <span class="inline-flex items-center gap-2 text-white font-semibold border-b-2 border-accent-500 pb-0.5 transition-colors group-hover:border-white">
                                        Explore {{ $featured->name }}
                                        <svg class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </article>
                @endif

                {{-- Remaining towns --}}
                <div class="lg:col-span-5 flex flex-col gap-4">
                    @foreach($rest as $town)
                        @php($rowMap = $town->hero_image ? asset('storage/'.$town->hero_image) : $town->staticMapUrl(200, 240))
                        <article class="flex-1">
                            <a href="{{ route('towns.show', $town) }}"
                               class="town-row group grid grid-cols-[6.5rem,1fr,auto] sm:grid-cols-[7.5rem,1fr,auto] items-stretch h-full rounded-2xl overflow-hidden bg-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2">

                                <div class="relative overflow-hidden bg-forest-800">
                                    @if($rowMap)
                                        <img src="{{ $rowMap }}"
                                             alt="Terrain around {{ $town->name }}, British Columbia"
                                             loading="lazy"
                                             class="town-terrain absolute inset-0 w-full h-full object-cover opacity-90">
                                    @endif
                                    <div class="absolute inset-0 bg-forest-900/25"></div>
                                    <div class="absolute inset-0 bg-pattern-contour opacity-25 mix-blend-overlay"></div>
                                </div>

                                <div class="min-w-0 py-5 px-5">
                                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-forest-700 transition-colors leading-tight">
                                        {{ $town->name }}<span class="province-tag align-middle ml-2 text-forest-400">{{ $town->province_code }}</span>
                                    </h3>

                                    @if($town->tagline)
                                        <p class="text-sm text-gray-500 mt-1.5 line-clamp-2 leading-snug text-pretty">{{ $town->tagline }}</p>
                                    @endif

                                    <p class="mt-3 flex items-center gap-2 text-xs text-gray-500 tabular-nums">
                                        <span><span class="font-bold text-forest-700">{{ $town->hiking_trails_count }}</span> {{ Str::plural('trail', $town->hiking_trails_count) }}</span>
                                        @if($town->fishing_lakes_count)
                                            <span class="w-1 h-1 rounded-full bg-gray-300" aria-hidden="true"></span>
                                            <span><span class="font-bold text-forest-700">{{ $town->fishing_lakes_count }}</span> {{ Str::plural('lake', $town->fishing_lakes_count) }}</span>
                                        @endif
                                    </p>
                                </div>

                                <div class="flex items-center pr-5 pl-1 text-gray-300 group-hover:text-accent-500">
                                    <svg class="town-row-arrow w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>

            <p class="mt-8 text-xs text-gray-400 text-center">
                Terrain imagery &copy; <a href="https://www.mapbox.com/about/maps/" target="_blank" rel="noopener" class="hover:text-gray-600 underline underline-offset-2">Mapbox</a>
                &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener" class="hover:text-gray-600 underline underline-offset-2">OpenStreetMap</a>
            </p>

        @endif
    </div>
</section>

{{-- Closing call to action --}}
@if($towns->isNotEmpty())
<section class="section cta-section">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-4xl font-bold text-white mb-6 text-balance">Not sure where to start?</h2>
        <p class="text-xl text-emerald-100 mb-8 text-pretty">
            Open the map and see every trail, lake and facility across the region at once.
        </p>
        <div class="flex flex-col md:flex-row gap-4 justify-center">
            <a href="{{ route('map') }}"
               class="bg-transparent border-2 border-white text-white hover:bg-white hover:text-forest-700 font-semibold py-4 px-10 rounded-xl transition-all duration-300 text-lg shadow-xl hover:scale-105 active:scale-95">
                View interactive map
            </a>
            <a href="{{ route('trails.index') }}"
               class="bg-white text-forest-600 hover:bg-forest-50 font-semibold py-4 px-10 rounded-xl transition-all duration-300 text-lg shadow-xl hover:scale-105 active:scale-95">
                Browse all trails
            </a>
        </div>
    </div>
</section>
@endif

@endsection
