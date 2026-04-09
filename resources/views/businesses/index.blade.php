@extends('layouts.public')

@section('title', 'Local Businesses — Smithers Partners')

@section('content')

{{-- ── Hero ── --}}
<section class="relative flex items-center justify-center hero-gradient overflow-hidden">
    <div class="absolute inset-0 bg-pattern-trees opacity-20 z-15"></div>
    <div class="absolute inset-0 bg-black bg-opacity-40 z-20"></div>

    <div class="relative z-30 text-center text-white max-w-6xl mx-auto px-4 flex flex-col justify-center py-20">
        <div class="mb-8 fade-in">
            <span class="inline-flex items-center px-6 py-3 bg-white/25 backdrop-blur-sm rounded-full text-white text-sm font-semibold border border-white/30 shadow-lg">
                🏪 {{ $businesses->count() }} Local Partners in Smithers
            </span>
        </div>

        <div class="slide-in-up mb-8">
            <h1 class="text-5xl md:text-7xl font-bold leading-tight">
                <span class="text-white text-shadow-lg">Discover Smithers</span><br>
                <span class="bg-gradient-to-r from-emerald-300 via-sand-200 to-accent-300 bg-clip-text text-transparent">
                    Local Businesses
                </span>
            </h1>
        </div>

        <div class="slide-in-up mb-12" style="animation-delay: 0.2s;">
            <p class="text-xl md:text-2xl text-white leading-relaxed max-w-4xl mx-auto text-shadow-md">
                Support local. Eat, stay, shop, and explore with Smithers' best local partners — your adventure starts here.
            </p>
        </div>

        {{-- Search & Filter --}}
        <div class="w-full max-w-4xl mx-auto scale-in" style="animation-delay: 0.4s;">
            <div class="bg-white/20 backdrop-blur-md rounded-2xl p-6 shadow-2xl border border-white/30">
                <form method="GET" action="{{ route('businesses.public.index') }}">
                    <div class="mb-4">
                        <label class="block text-white text-sm font-medium mb-2">Search Businesses</label>
                        <input type="text" name="search" placeholder="Cafe, restaurant, gear shop..."
                               value="{{ request('search') }}"
                               class="w-full px-4 py-3 bg-white/90 border border-white/40 rounded-lg text-gray-900 placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent font-medium">
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="col-span-2 md:col-span-3">
                            <label class="block text-white text-sm font-medium mb-2">Category</label>
                            <select name="type" class="w-full px-3 py-3 bg-white/90 border border-white/40 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent font-medium text-sm">
                                <option value="">All Categories</option>
                                @foreach($types as $key => $label)
                                    <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2 md:col-span-1 flex items-end">
                            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg">
                                Find Businesses
                            </button>
                        </div>
                    </div>

                    @if(request()->hasAny(['search', 'type']))
                        <div class="flex flex-col md:flex-row items-center justify-between bg-white/10 rounded-lg p-4 border border-white/20 mt-4">
                            <span class="text-white text-sm font-medium mb-2 md:mb-0">
                                {{ $businesses->count() }} {{ Str::plural('business', $businesses->count()) }} found
                            </span>
                            <a href="{{ route('businesses.public.index') }}"
                               class="text-emerald-300 hover:text-emerald-200 text-sm font-medium transition-colors flex items-center">
                                Clear filters
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

{{-- ── Business Listings ── --}}
<section class="section bg-white">
    <div class="max-w-7xl mx-auto px-4">

        @if($businesses->isEmpty())
            <div class="text-center py-20">
                <div class="w-24 h-24 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">No Businesses Found</h3>
                <p class="text-gray-600 mb-8">Try adjusting your search or filters.</p>
                <a href="{{ route('businesses.public.index') }}" class="btn-primary">View All Businesses</a>
            </div>
        @else

            @if(!request()->hasAny(['search', 'type']))
                <div class="text-center mb-12">
                    <h2 class="section-title text-forest-600">Smithers Local Partners</h2>
                    <p class="section-subtitle">
                        Shop local, eat local, stay local. These businesses make Smithers the vibrant community it is.
                    </p>
                </div>
            @else
                <div class="mb-10"></div>
            @endif

            @if(request()->hasAny(['search', 'type']))
                {{-- Flat grid when filtered --}}
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($businesses as $business)
                        @include('businesses._card', ['business' => $business])
                    @endforeach
                </div>
            @else
                {{-- Grouped by type --}}
                @foreach($types as $typeKey => $typeLabel)
                    @if($grouped->has($typeKey))
                        @php $group = $grouped->get($typeKey); @endphp
                        <div class="mb-14">
                            <div class="flex items-center gap-3 mb-6">
                                <span class="text-2xl">{{ explode(' ', $typeLabel)[0] }}</span>
                                <h3 class="text-2xl font-bold text-gray-800">{{ implode(' ', array_slice(explode(' ', $typeLabel), 1)) ?: $typeLabel }}</h3>
                                <span class="text-sm text-gray-500 font-medium">({{ $group->count() }})</span>
                                <div class="flex-1 h-px bg-gray-200"></div>
                            </div>

                            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                                @foreach($group as $business)
                                    @include('businesses._card', ['business' => $business])
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif

        @endif

    </div>
</section>

{{-- ── CTA ── --}}
@if($businesses->isNotEmpty())
<section class="section cta-section">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-4xl font-bold text-white mb-6">Ready to Explore Smithers?</h2>
        <p class="text-xl text-emerald-100 mb-8">
            Find local businesses on the interactive map or head out and discover all that Smithers has to offer.
        </p>
        <div class="flex flex-col md:flex-row gap-4 justify-center">
            <a href="{{ route('map') }}" class="bg-transparent border-2 border-white text-white hover:bg-white hover:text-gray-900 font-semibold py-4 px-10 rounded-xl transition-all duration-300 text-lg shadow-xl hover:scale-105">
                📍 View Interactive Map
            </a>
            <a href="{{ route('trails.index') }}" class="bg-white text-emerald-600 hover:bg-emerald-50 font-semibold py-4 px-10 rounded-xl transition-colors text-lg shadow-xl hover:scale-105 hover:text-accent-600">
                🥾 Explore Trails
            </a>
        </div>
    </div>
</section>
@endif

@endsection
