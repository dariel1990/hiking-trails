@extends('layouts.public')

@php
    /** Every param the filter panel owns, so the empty state stays in step with it. */
    $filterKeys = ['town', 'search', 'activity', 'season'];
    $isFiltered = request()->hasAny($filterKeys);
@endphp


@section('title', 'Discover Fishing Lakes')

@section('content')
<!-- Enhanced Hero Section -->
<section class="relative flex items-center justify-center hero-gradient overflow-hidden">
    <div class="absolute inset-0 bg-pattern-trees opacity-20 z-15"></div>
    <div class="absolute inset-0 bg-black bg-opacity-40 z-20"></div>

    <div class="relative z-30 text-center text-white max-w-6xl mx-auto px-4 flex flex-col justify-center py-20">
        <div class="mb-8 fade-in">
            <span class="inline-flex items-center px-6 py-3 bg-white/25 backdrop-blur-sm rounded-full text-white text-sm font-semibold border border-white/30 shadow-lg">
                🐟 {{ $fishingLakes->total() }} Fishing Lakes Await
            </span>
        </div>

        <div class="slide-in-up mb-8">
            <h1 class="text-5xl md:text-7xl font-bold leading-tight">
                <span class="text-white text-shadow-lg">Discover Pristine</span><br>
                <span class="bg-gradient-to-r from-emerald-300 via-sand-200 to-accent-300 bg-clip-text text-transparent">
                    Fishing Lakes
                </span>
            </h1>
        </div>

        {{-- Phone gets its own short line rather than a clamped desktop one. --}}
        <div class="slide-in-up mb-8" style="animation-delay: 0.2s;">
            <p class="text-lg md:text-2xl text-white leading-relaxed max-w-4xl mx-auto text-shadow-md text-pretty">
                <span class="sm:hidden">{{ $fishingLakes->total() }} lakes with fish species, seasons and access notes.</span>
                <span class="hidden sm:inline">
                    Explore {{ $fishingLakes->total() }} pristine fishing lakes with detailed information on fish species, best seasons, and access points.
                    Every lake supports sustainable tourism and local communities.
                </span>
            </p>
        </div>

        @include('partials.app-promo-banner')

        <x-filter-panel
            :action="route('fishing-lakes.index')"
            search-label="Search lakes"
            search-placeholder="Lake name, location…"
            submit-label="Show lakes"
            :result-count="$fishingLakes->total()"
            result-noun="lake"
            :filters="[
                ['name' => 'town', 'label' => 'Town', 'placeholder' => 'All towns', 'options' => $towns->pluck('name', 'slug')],
                ['name' => 'activity', 'label' => 'Activity', 'placeholder' => 'All activities', 'options' => $activities->pluck('name', 'slug')],
                ['name' => 'season', 'label' => 'Best season', 'placeholder' => 'Any season', 'options' => \App\Models\Trail::getSeasons()],
            ]"
        />
    </div>
</section>

<!-- Lake Cards Section -->
<section class="section bg-white">
    <div class="max-w-7xl mx-auto px-4">

        @if($fishingLakes->total() > 0)
            <div class="text-center mb-12">
                <h2 class="section-title text-forest-600">Pristine Fishing Lakes</h2>
                <p class="section-subtitle">
                    Every lake supports sustainable tourism and local communities. Cast your line responsibly.
                </p>
            </div>

            <div class="flex items-center gap-3 mb-6">
                <span class="text-2xl">🐟</span>
                <h3 class="text-2xl font-bold text-gray-800">Fishing Lakes</h3>
                <span class="text-sm text-gray-500 font-medium">({{ $fishingLakes->total() }})</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            <div
                x-data="{
                    nextPage: {{ $fishingLakes->hasMorePages() ? $fishingLakes->currentPage() + 1 : 'null' }},
                    remaining: {{ max(0, $fishingLakes->total() - $fishingLakes->count()) }},
                    loading: false,
                    async loadMore() {
                        if (!this.nextPage || this.loading) return;
                        this.loading = true;
                        const params = new URLSearchParams(window.location.search);
                        params.set('ajax_type', 'lakes');
                        params.set('lake_page', this.nextPage);
                        const res = await fetch('{{ route('fishing-lakes.index') }}?' + params.toString());
                        const data = await res.json();
                        this.$refs.grid.insertAdjacentHTML('beforeend', data.html);
                        this.nextPage = data.has_more ? data.next_page : null;
                        this.remaining = Math.max(0, this.remaining - {{ $fishingLakes->perPage() }});
                        this.loading = false;
                    }
                }"
            >
                <div x-ref="grid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-6">
                    @include('trails._cards', ['trails' => $fishingLakes, 'type' => 'lakes'])
                </div>

                <div x-show="nextPage !== null" class="flex items-center gap-4 mb-12 select-none">
                    <div class="flex-1 h-px bg-gray-200"></div>
                    <button
                        x-on:click="loadMore"
                        :disabled="loading"
                        class="group flex items-center gap-1.5 text-sm text-gray-500 hover:text-emerald-600 transition-colors duration-200 disabled:opacity-50"
                    >
                        <svg x-show="loading" class="animate-spin w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        <span x-text="loading ? 'Loading...' : 'Show more lakes'"></span>
                        <svg x-show="!loading" class="w-3.5 h-3.5 transition-transform duration-200 group-hover:translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div class="flex-1 h-px bg-gray-200"></div>
                </div>
            </div>
        @else
            <div class="text-center py-20">
                <div class="max-w-md mx-auto">
                    <div class="w-24 h-24 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">No Lakes Found</h3>
                    <p class="text-gray-600 mb-8">
                        @if($isFiltered)
                            We couldn't find fishing lakes matching your criteria. Try adjusting your search filters.
                        @else
                            We're curating pristine fishing destinations for you. Check back soon!
                        @endif
                    </p>
                    @if($isFiltered)
                        <a href="{{ route('fishing-lakes.index') }}" class="btn-primary">View All Lakes</a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</section>

@if($fishingLakes->total() > 0)
<section class="section cta-section">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-4xl font-bold text-white mb-6">Ready to Cast Your Line?</h2>
        <p class="text-xl text-emerald-100 mb-8">
            Join thousands of ethical anglers who choose sustainable tourism and support local communities.
        </p>
        <div class="flex flex-col md:flex-row gap-4 justify-center">
            <a href="{{ route('map') }}" class="bg-transparent border-2 border-white text-white hover:bg-white hover:text-gray-900 font-semibold py-4 px-10 rounded-xl transition-all duration-300 text-lg shadow-xl hover:scale-105">
                View Interactive Map
            </a>
            <a href="{{ route('trails.index') }}" class="bg-white text-forest-600 hover:bg-forest-50 font-semibold py-4 px-10 rounded-xl transition-all duration-300 text-lg shadow-xl hover:scale-105">
                Browse Hiking Trails
            </a>
        </div>
    </div>
</section>
@endif
@endsection
