@extends('layouts.public')

@php
    /** Filters besides the season (always set; Summer by default), for the empty-state message. */
    $hasOtherFilters = request()->hasAny(['town', 'search', 'activity']);
    $total = $fishingLakes->total();

    /** Link for the season tabs without JavaScript: the current filters with only the season changed, back on page one. */
    $seasonUrl = fn (?string $value) => route('fishing-lakes.index').(($query = http_build_query(
        collect(request()->query())->except(['season', 'lake_page', 'ajax_type'])->when($value, fn ($c) => $c->put('season', $value))->all()
    )) ? '?'.$query : '');

    $pageConfig = [
        'baseUrl' => route('fishing-lakes.index'),
        'season' => $season,
        'total' => $total,
        'nextPage' => $fishingLakes->hasMorePages() ? $fishingLakes->currentPage() + 1 : null,
        'remaining' => max(0, $total - $fishingLakes->count()),
        'hasOtherFilters' => $hasOtherFilters,
    ];
@endphp


@section('title', 'Discover Fishing Lakes')

@section('content')
{{-- One component for the season tabs and the lake list, so switching season swaps the list without reloading the page. --}}
<div x-data="fishingLakesPage(@js($pageConfig))">

<!-- Enhanced Hero Section -->
<section class="relative flex items-center justify-center hero-gradient overflow-hidden">
    <div class="absolute inset-0 bg-pattern-trees opacity-20 z-15"></div>
    <div class="absolute inset-0 bg-black bg-opacity-40 z-20"></div>

    <div class="relative z-30 text-center text-white max-w-6xl mx-auto px-4 flex flex-col justify-center py-20">
        <div class="mb-8 fade-in">
            <span class="inline-flex items-center px-6 py-3 bg-white/25 backdrop-blur-sm rounded-full text-white text-sm font-semibold border border-white/30 shadow-lg">
                🐟&nbsp;<span x-text="total">{{ $total }}</span>&nbsp;Fishing Lakes Await
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
                <span class="sm:hidden"><span x-text="total">{{ $total }}</span> lakes with fish species, seasons and access notes.</span>
                <span class="hidden sm:inline">
                    Explore <span x-text="total">{{ $total }}</span> pristine fishing lakes with detailed information on fish species, best seasons, and access points.
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
            :result-count="$total"
            result-noun="lake"
            :filters="[
                ['name' => 'town', 'label' => 'Town', 'placeholder' => 'All towns', 'options' => $towns->pluck('name', 'slug')],
                ['name' => 'activity', 'label' => 'Activity', 'placeholder' => 'All activities', 'options' => $activities->pluck('name', 'slug')],
                ['name' => 'season', 'hidden' => true],
            ]"
        />

        {{-- Summer (open water) / Winter (ice fishing) tabs; Summer is selected by default. They swap
             the list in place; the hrefs are the no-JavaScript fallback. --}}
        <nav class="mt-4 w-full max-w-xs mx-auto grid grid-cols-2 gap-1 rounded-2xl bg-white/20 backdrop-blur-md p-1.5 border border-white/30 shadow-xl text-sm font-semibold sm:inline-grid sm:w-auto"
             aria-label="Fishing season">
            @foreach(\App\Models\Trail::getFishingSeasons() as $value => $label)
                @php $isActive = $season === $value; @endphp
                <a href="{{ $seasonUrl($value) }}"
                   @if($isActive) aria-current="page" @endif
                   :aria-current="season === '{{ $value }}' ? 'page' : null"
                   @click.prevent="setSeason('{{ $value }}')"
                   class="flex items-center justify-center whitespace-nowrap rounded-xl px-5 py-2.5 transition-colors {{ $isActive ? 'bg-white text-forest-700 shadow-md' : 'text-white hover:bg-white/15' }}"
                   :class="{ 'bg-white text-forest-700 shadow-md': season === '{{ $value }}', 'text-white hover:bg-white/15': season !== '{{ $value }}' }">
                    {{ $label }}
                </a>
            @endforeach
        </nav>
    </div>
</section>

<!-- Lake Cards Section -->
<section class="section bg-white">
    <div class="max-w-7xl mx-auto px-4">

        <div x-show="total > 0" @if($total === 0) style="display: none" @endif>
            <div class="text-center mb-12">
                <h2 class="section-title text-forest-600">Pristine Fishing Lakes</h2>
                <p class="section-subtitle">
                    Every lake supports sustainable tourism and local communities. Cast your line responsibly.
                </p>
            </div>

            <div class="flex items-center gap-3 mb-6">
                <span class="text-2xl">🐟</span>
                <h3 class="text-2xl font-bold text-gray-800">Fishing Lakes</h3>
                <span class="text-sm text-gray-500 font-medium">(<span x-text="total">{{ $total }}</span>)</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            <div x-ref="grid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-6 transition-opacity duration-200"
                 :class="switching && 'opacity-40 pointer-events-none'" :aria-busy="switching">
                @include('trails._cards', ['trails' => $fishingLakes, 'type' => 'lakes'])
            </div>

            <div x-show="nextPage !== null" @if(! $fishingLakes->hasMorePages()) style="display: none" @endif class="flex items-center gap-4 mb-12 select-none">
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
                    <span x-text="loading ? 'Loading...' : 'Show more lakes'">Show more lakes</span>
                    <svg x-show="!loading" class="w-3.5 h-3.5 transition-transform duration-200 group-hover:translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>
        </div>

        <div x-show="total === 0" @if($total > 0) style="display: none" @endif class="text-center py-20">
            <div class="max-w-md mx-auto">
                <div class="w-24 h-24 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">No Lakes Found</h3>
                <p class="text-gray-600 mb-8" x-text="emptyMessage">
                    @if($hasOtherFilters)
                        No lakes match your filters for {{ $season }} fishing. Try another season or fewer filters.
                    @else
                        No lakes are listed for {{ $season }} fishing yet.
                    @endif
                </p>
                @php $otherSeason = $season === 'winter' ? 'summer' : 'winter'; @endphp
                <a href="{{ $seasonUrl($otherSeason) }}" @click.prevent="setSeason(otherSeason)" class="btn-primary"
                   x-text="`See ${otherSeason} lakes`">See {{ $otherSeason }} lakes</a>
            </div>
        </div>
    </div>
</section>

@if($total > 0)
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

</div>

<script>
    function fishingLakesPage(config) {
        return {
            ...config,
            loading: false,
            switching: false,
            requestId: 0,

            init() {
                // Back / forward between seasons swaps the list again instead of leaving it stale.
                window.addEventListener('popstate', () => {
                    const season = new URLSearchParams(window.location.search).get('season');
                    this.loadSeason(season === 'winter' ? 'winter' : 'summer', false);
                });
            },

            get emptyMessage() {
                return this.hasOtherFilters
                    ? `No lakes match your filters for ${this.season} fishing. Try another season or fewer filters.`
                    : `No lakes are listed for ${this.season} fishing yet.`;
            },

            get otherSeason() {
                return this.season === 'winter' ? 'summer' : 'winter';
            },

            /** Tab click: one season is always selected (Summer by default). */
            setSeason(value) {
                if (value !== this.season) {
                    this.loadSeason(value, true);
                }
            },

            /** The page's query with a different season, back on page one. */
            queryFor(season) {
                const params = new URLSearchParams(window.location.search);
                ['season', 'lake_page', 'ajax_type'].forEach((key) => params.delete(key));
                if (season) {
                    params.set('season', season);
                }
                return params;
            },

            async loadSeason(season, pushHistory) {
                const params = this.queryFor(season);
                const query = params.toString();
                if (pushHistory) {
                    history.pushState({}, '', this.baseUrl + (query ? '?' + query : ''));
                }

                this.season = season;
                this.switching = true;
                const requestId = ++this.requestId;

                params.set('ajax_type', 'lakes');
                params.set('lake_page', 1);

                try {
                    const response = await fetch(this.baseUrl + '?' + params.toString(), { headers: { Accept: 'application/json' } });
                    if (!response.ok) {
                        throw new Error('Request failed');
                    }
                    const data = await response.json();
                    if (requestId !== this.requestId) {
                        return; // A newer tab click already won.
                    }

                    this.$refs.grid.innerHTML = data.html;
                    this.total = data.total;
                    this.nextPage = data.has_more ? data.next_page : null;
                    this.remaining = Math.max(0, data.total - data.count);
                    this.syncFilterPanel();
                } catch (error) {
                    // Fall back to a normal page load so the visitor still gets the right lakes.
                    window.location.href = this.baseUrl + (query ? '?' + query : '');
                } finally {
                    if (requestId === this.requestId) {
                        this.switching = false;
                    }
                }
            },

            /** Keep the Filters form, its chips and the result count in step with the chosen season. */
            syncFilterPanel() {
                const hidden = document.getElementById('filter-season');
                if (hidden) {
                    hidden.value = this.season;
                    hidden.disabled = false;
                }

                document.querySelectorAll('a.filter-chip').forEach((chip) => {
                    const url = new URL(chip.href, window.location.origin);
                    url.searchParams.set('season', this.season);
                    chip.href = url.toString();
                });

                const count = document.querySelector('[data-filter-result-count]');
                if (count) {
                    const noun = count.dataset.noun + (this.total === 1 ? '' : 's');
                    count.textContent = `${this.total.toLocaleString()} ${noun} found`;
                }
            },

            async loadMore() {
                if (!this.nextPage || this.loading) {
                    return;
                }
                this.loading = true;
                const params = new URLSearchParams(window.location.search);
                params.set('ajax_type', 'lakes');
                params.set('lake_page', this.nextPage);
                try {
                    const response = await fetch(this.baseUrl + '?' + params.toString(), { headers: { Accept: 'application/json' } });
                    const data = await response.json();
                    this.$refs.grid.insertAdjacentHTML('beforeend', data.html);
                    this.nextPage = data.has_more ? data.next_page : null;
                    this.remaining = Math.max(0, this.remaining - data.count);
                } finally {
                    this.loading = false;
                }
            },
        };
    }
</script>
@endsection
