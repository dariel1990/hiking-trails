@extends('layouts.public')

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

        <div class="slide-in-up mb-12" style="animation-delay: 0.2s;">
            <p class="text-xl md:text-2xl text-white leading-relaxed max-w-4xl mx-auto text-shadow-md">
                Explore {{ $fishingLakes->total() }} pristine fishing lakes with detailed information on fish species, best seasons, and access points.
                Every lake supports sustainable tourism and local communities.
            </p>
        </div>

        @include('partials.app-promo-banner')

        <!-- Search Bar -->
        <div class="w-full max-w-5xl mx-auto scale-in" style="animation-delay: 0.4s;">
            <div class="bg-white/20 backdrop-blur-md rounded-2xl p-6 shadow-2xl border border-white/30">
                <form method="GET" action="{{ route('fishing-lakes.index') }}">
                    <div class="mb-4">
                        <label class="block text-white text-sm font-medium mb-2">Search Lakes</label>
                        <input type="text" name="search" placeholder="Lake name, location..."
                            value="{{ request('search') }}"
                            class="w-full px-4 py-3 bg-white/90 border border-white/40 rounded-lg text-gray-900 placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent font-medium">
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-white text-sm font-medium mb-2">Activity Type</label>
                            <select name="activity" class="w-full px-3 py-3 bg-white/90 border border-white/40 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent font-medium text-sm">
                                <option value="">All Activities</option>
                                @foreach($activities as $activity)
                                    <option value="{{ $activity->slug }}" {{ request('activity') == $activity->slug ? 'selected' : '' }}>
                                        {{ $activity->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-white text-sm font-medium mb-2">Best Season</label>
                            <select name="season" class="w-full px-3 py-3 bg-white/90 border border-white/40 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent font-medium text-sm">
                                <option value="">All Seasons</option>
                                <option value="spring" {{ request('season') == 'spring' ? 'selected' : '' }}>🌸 Spring</option>
                                <option value="summer" {{ request('season') == 'summer' ? 'selected' : '' }}>☀️ Summer</option>
                                <option value="fall" {{ request('season') == 'fall' ? 'selected' : '' }}>🍂 Fall</option>
                                <option value="winter" {{ request('season') == 'winter' ? 'selected' : '' }}>❄️ Winter</option>
                            </select>
                        </div>

                        <div class="col-span-2 flex items-end">
                            <button type="submit" class="btn-primary w-full">
                                Find Lakes
                            </button>
                        </div>
                    </div>

                    @if(request()->hasAny(['search', 'activity', 'season']))
                        <div class="flex flex-col md:flex-row items-center justify-between bg-white/10 rounded-lg p-4 border border-white/20 mt-4">
                            <span class="text-white text-sm font-medium mb-2 md:mb-0">
                                {{ $fishingLakes->total() }} fishing lakes found
                            </span>
                            <a href="{{ route('fishing-lakes.index') }}"
                            class="text-emerald-300 hover:text-emerald-200 text-sm font-medium transition-colors flex items-center">
                                Clear all filters
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </a>
                        </div>
                    @endif
                </form>
            </div>
        </div>
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
                        @if(request()->hasAny(['search', 'activity', 'season']))
                            We couldn't find fishing lakes matching your criteria. Try adjusting your search filters.
                        @else
                            We're curating pristine fishing destinations for you. Check back soon!
                        @endif
                    </p>
                    @if(request()->hasAny(['search', 'activity', 'season']))
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
