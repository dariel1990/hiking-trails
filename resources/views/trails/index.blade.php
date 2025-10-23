@extends('layouts.public')

@section('title', 'Discover All Trail Adventures')

@section('content')
<!-- Enhanced Hero Section with Wavy Divider -->
<section class="relative flex items-center justify-center hero-gradient overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 bg-pattern-trees opacity-20 z-15"></div>
    
    <!-- Enhanced Overlay -->
    <div class="absolute inset-0 bg-black bg-opacity-40 z-20"></div>
    
    <!-- Content -->
    <div class="relative z-30 text-center text-white max-w-6xl mx-auto px-4 flex flex-col justify-center py-20">
        <!-- Badge -->
        <div class="mb-8 fade-in">
            <span class="inline-flex items-center px-6 py-3 bg-white/25 backdrop-blur-sm rounded-full text-white text-sm font-semibold border border-white/30 shadow-lg">
                🌲 {{ $trails->total() }} Ethical Adventures Await
            </span>
        </div>
        
        <!-- Main Headline -->
        <div class="slide-in-up mb-8">
            <h1 class="text-5xl md:text-7xl font-bold leading-tight">
                <span class="text-white text-shadow-lg">Discover Amazing</span><br>
                <span class="bg-gradient-to-r from-emerald-300 via-sand-200 to-accent-300 bg-clip-text text-transparent">
                    Trail Adventures
                </span>
            </h1>
        </div>
        
        <!-- Subtitle -->
        <div class="slide-in-up mb-12" style="animation-delay: 0.2s;">
            <p class="text-xl md:text-2xl text-white leading-relaxed max-w-4xl mx-auto text-shadow-md">
                Explore {{ $trails->total() }} carefully curated hiking trails with detailed information, photos, and maps. 
                Every adventure supports sustainable tourism and local communities.
            </p>
        </div>
        
        <!-- Enhanced Search Bar */ -->
        <div class="w-full max-w-5xl mx-auto scale-in" style="animation-delay: 0.4s;">
            <div class="bg-white/20 backdrop-blur-md rounded-2xl p-6 shadow-2xl border border-white/30">
                <form method="GET" action="{{ route('trails.index') }}" class="space-y-6">
                    <!-- Main search and filters -->
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <!-- Search Input -->
                        <div class="md:col-span-4">
                            <label class="block text-white text-sm font-medium mb-2">Search Adventures</label>
                            <input type="text" name="search" placeholder="Trail name, location..." 
                                   value="{{ request('search') }}"
                                   class="w-full px-4 py-3 bg-white/90 border border-white/40 rounded-lg text-gray-900 placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent font-medium">
                        </div>
                        
                        <!-- Difficulty Filter -->
                        <div class="md:col-span-3">
                            <label class="block text-white text-sm font-medium mb-2">Challenge Level</label>
                            <select name="difficulty" class="w-full px-4 py-3 bg-white/90 border border-white/40 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent font-medium">
                                <option value="">All Levels</option>
                                <option value="1" {{ request('difficulty') == '1' ? 'selected' : '' }}>1 - Very Easy</option>
                                <option value="2" {{ request('difficulty') == '2' ? 'selected' : '' }}>2 - Easy</option>
                                <option value="3" {{ request('difficulty') == '3' ? 'selected' : '' }}>3 - Moderate</option>
                                <option value="4" {{ request('difficulty') == '4' ? 'selected' : '' }}>4 - Hard</option>
                                <option value="5" {{ request('difficulty') == '5' ? 'selected' : '' }}>5 - Very Hard</option>
                            </select>
                        </div>
                        
                        <!-- Distance Filter -->
                        <div class="md:col-span-3">
                            <label class="block text-white text-sm font-medium mb-2">Distance Range</label>
                            <select name="distance" class="w-full px-4 py-3 bg-white/90 border border-white/40 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent font-medium">
                                <option value="">Any Distance</option>
                                <option value="0-5" {{ request('distance') == '0-5' ? 'selected' : '' }}>Under 5km</option>
                                <option value="5-10" {{ request('distance') == '5-10' ? 'selected' : '' }}>5-10km</option>
                                <option value="10-20" {{ request('distance') == '10-20' ? 'selected' : '' }}>10-20km</option>
                                <option value="20+" {{ request('distance') == '20+' ? 'selected' : '' }}>Over 20km</option>
                            </select>
                        </div>
                        
                        <!-- Search Button -->
                        <div class="md:col-span-2 flex items-end">
                            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white hover:text-accent-200 font-semibold py-3 px-4 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg">
                                Find Trails
                            </button>
                        </div>
                    </div>
                    
                    <!-- Filter Results Info -->
                    @if(request()->hasAny(['search', 'difficulty', 'distance']))
                        <div class="flex flex-col md:flex-row items-center justify-between bg-white/10 rounded-lg p-4 border border-white/20">
                            <span class="text-white text-sm font-medium mb-2 md:mb-0">
                                {{ $trails->total() }} sustainable adventures found
                            </span>
                            <a href="{{ route('trails.index') }}" 
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

<!-- Enhanced Trail Cards Section -->
<section class="section bg-white">
    <div class="max-w-7xl mx-auto px-4">
        
        <!-- Section Header -->
        @if($trails->count() > 0)
        <div class="text-center mb-12">
            <h2 class="section-title text-forest-600">Ethical Trail Adventures</h2>
            <p class="section-subtitle">
                Every trail supports sustainable tourism and local communities. Choose your next responsible adventure.
            </p>
        </div>
        @endif

        <!-- Enhanced Trail Cards Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
            @forelse($trails as $trail)
            <div class="trail-card group cursor-pointer hover-lift" 
                 onclick="window.location.href='{{ route('trails.show', $trail->id) }}'">
                
                <!-- Enhanced Trail Image -->
                <div class="trail-card-image group-hover:scale-105 transition-transform duration-500">
                    @php
                        // Prefer the model accessor which only returns photos
                        $featuredUrl = $trail->featured_media_url;
                        // If not present, try the first photo on the trailMedia collection
                        if (!$featuredUrl) {
                            $firstPhoto = $trail->trailMedia->where('media_type', 'photo')->first();
                            $featuredUrl = $firstPhoto ? $firstPhoto->getThumbnail() ?? $firstPhoto->getUrl() : null;
                        }
                    @endphp
                    @if($featuredUrl)
                        <img src="{{ $featuredUrl }}" 
                             alt="{{ $trail->name }}" 
                             class="w-full h-full object-cover">
                    @else
                        <div class="absolute inset-0 flex items-center justify-center">
                            <img src="{{ asset('images/no-image.png') }}" 
                            alt="Trail Finder Logo" 
                            class="w-24 h-24 object-contain transition-all duration-300 group-hover:scale-105">
                        </div>
                    @endif
                    
                    <!-- Enhanced Badges -->
                    @if($trail->is_featured)
                        <div class="absolute top-3 left-3">
                            <span class="badge bg-amber-400 text-amber-900 font-bold shadow-lg">
                                ⭐ Featured
                            </span>
                        </div>
                    @endif
                    
                    <div class="absolute top-3 right-3">
                        <span class="difficulty-badge difficulty-{{ intval($trail->difficulty_level) }} shadow-lg">
                            {{ $trail->difficulty_level }}/5
                        </span>
                    </div>
                    
                    <!-- Hover Overlay -->
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                        <div class="transform scale-0 group-hover:scale-100 transition-transform duration-300">
                            <span class="bg-white text-gray-900 px-6 py-3 rounded-lg font-semibold shadow-lg">
                                Explore Trail
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Trail Info -->
                <div class="trail-card-body">
                    <div class="mb-4">
                        <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-accent-600 transition-colors">
                            {{ $trail->name }}
                        </h3>
                        @if($trail->location)
                            <p class="text-sm text-gray-500 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                                {{ $trail->location }}
                            </p>
                        @endif
                    </div>
                    
                    <p class="text-gray-600 text-sm mb-4 line-clamp-2 leading-relaxed">
                        {{ Str::limit($trail->description, 120) }}
                    </p>

                    <!-- Enhanced Trail Stats -->
                    <div class="grid grid-cols-3 gap-3 mb-4">
                        <div class="text-center p-3 bg-blue-50 rounded-lg">
                            <div class="text-lg font-bold text-blue-600">{{ $trail->distance_km }}</div>
                            <div class="text-xs text-gray-500">km</div>
                        </div>
                        <div class="text-center p-3 bg-green-50 rounded-lg">
                            <div class="text-lg font-bold text-green-600">{{ $trail->elevation_gain_m }}</div>
                            <div class="text-xs text-gray-500">meters</div>
                        </div>
                        <div class="text-center p-3 bg-amber-50 rounded-lg">
                            <div class="text-lg font-bold text-amber-600">{{ $trail->estimated_time_hours }}</div>
                            <div class="text-xs text-gray-500">hours</div>
                        </div>
                    </div>

                    <!-- Trail Type and Views -->
                    <div class="flex items-center justify-between mb-4">
                        <span class="badge-secondary">
                            {{ ucwords(str_replace('-', ' ', $trail->trail_type)) }}
                        </span>
                        <div class="flex items-center text-xs text-gray-500">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            {{ $trail->view_count }} adventurers
                        </div>
                    </div>

                    <!-- Action and Seasons -->
                    <div class="flex items-center justify-between">
                        <a href="{{ route('trails.show', $trail->id) }}" 
                           class="text-emerald-600 hover:text-emerald-700 font-semibold text-sm group-hover:text-emerald-700 flex items-center">
                            Start Adventure
                            <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        
                        @if($trail->best_seasons)
                            <div class="flex flex-wrap gap-1">
                                @foreach(array_slice($trail->best_seasons, 0, 2) as $season)
                                    <span class="season-{{ strtolower($season) }} text-xs px-2 py-1 rounded border">
                                        {{ $season }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <!-- Enhanced Empty State -->
            <div class="col-span-full text-center py-20">
                <div class="max-w-md mx-auto">
                    <div class="w-24 h-24 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">No Trail Adventures Found</h3>
                    <p class="text-gray-600 mb-8">
                        @if(request()->hasAny(['search', 'difficulty', 'distance']))
                            We couldn't find trails matching your criteria. Try adjusting your search filters to discover more adventures.
                        @else
                            We're curating amazing trail adventures for you. Check back soon for new sustainable tourism opportunities.
                        @endif
                    </p>
                    @if(request()->hasAny(['search', 'difficulty', 'distance']))
                        <a href="{{ route('trails.index') }}" class="btn-primary">
                            View All Adventures
                        </a>
                    @endif
                </div>
            </div>
            @endforelse
        </div>

        <!-- Enhanced Pagination -->
        @if($trails->hasPages())
            <div class="flex justify-center">
                <div class="bg-white rounded-lg shadow-md p-2">
                    {{ $trails->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>
</section>

<!-- Call-to-Action Section -->
@if($trails->count() > 0)
<section class="section cta-section">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-4xl font-bold text-white mb-6">Ready for Your Next Adventure?</h2>
        <p class="text-xl text-emerald-100 mb-8">
            Join thousands of ethical adventurers who choose sustainable tourism and support local communities.
        </p>
        <div class="flex flex-col md:flex-row gap-4 justify-center">
            <a href="{{ route('map') }}" class="bg-transparent border-2 border-white text-white hover:bg-white hover:text-gray-900 font-semibold py-4 px-10 rounded-xl transition-all duration-300 text-lg shadow-xl hover:scale-105">
                📍 View Interactive Map
            </a>
            <a href="{{ route('home') }}#community" class="bg-white text-emerald-600 hover:bg-emerald-50 font-semibold py-4 px-10 rounded-xl transition-colors text-lg shadow-xl hover:scale-105 hover:text-accent-600">
                Learn About Our Mission
            </a>
        </div>
    </div>
</section>
@endif
@endsection