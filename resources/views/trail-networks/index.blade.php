@extends('layouts.public')

@section('title', 'Trail Networks')

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
                🗺️ {{ $networks->count() }} Trail Networks Available
            </span>
        </div>
        
        <!-- Main Headline -->
        <div class="slide-in-up mb-8">
            <h1 class="text-5xl md:text-7xl font-bold leading-tight">
                <span class="text-white text-shadow-lg">Explore Organized</span><br>
                <span class="bg-gradient-to-r from-emerald-300 via-sand-200 to-accent-300 bg-clip-text text-transparent">
                    Trail Networks
                </span>
            </h1>
        </div>
        
        <!-- Subtitle -->
        <div class="slide-in-up mb-12" style="animation-delay: 0.2s;">
            <p class="text-xl md:text-2xl text-white leading-relaxed max-w-4xl mx-auto text-shadow-md">
                Discover curated trail systems including Nordic ski trails, downhill ski runs, and organized hiking networks. 
                Each network offers multiple interconnected trails for extended adventures.
            </p>
        </div>
    </div>
</section>

<!-- Enhanced Trail Networks Section -->
<section class="section bg-white">
    <div class="max-w-7xl mx-auto px-4">
        
        <!-- Section Header -->
        @if($networks->count() > 0)
        <div class="text-center mb-12">
            <h2 class="section-title text-forest-600">Trail Network Systems</h2>
            <p class="section-subtitle">
                Organized trail systems with comprehensive maps, facilities, and detailed information for your adventure.
            </p>
        </div>
        @endif

        <!-- Enhanced Network Cards Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
            @forelse($networks as $network)
            <div class="trail-card group cursor-pointer hover-lift" 
                 onclick="window.location.href='{{ route('trail-networks.show', $network->slug) }}'">
                
                <!-- Enhanced Network Header with Gradient -->
                <div class="trail-card-image group-hover:scale-105 transition-transform duration-500">
                    
                    <!-- Content Overlay -->
                    <div class="relative h-full flex flex-col items-center justify-center p-6 text-white">
                        <!-- Network Icon -->
                        <div class="absolute inset-0 flex items-center justify-center">
                            <img src="{{ asset('images/no-image.png') }}" 
                            alt="Trail Finder Logo" 
                            class="w-24 h-24 object-contain transition-all duration-300 group-hover:scale-105">
                        </div>
                    </div>
                    
                    <!-- Hover Overlay -->
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300 flex items-center justify-center">
                        <div class="transform scale-0 group-hover:scale-100 transition-transform duration-300">
                            <span class="bg-white text-gray-900 px-6 py-3 rounded-lg font-semibold shadow-lg">
                                Explore Network
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Network Info -->
                <div class="trail-card-body">
                    <h2 class="text-2xl font-bold mb-2">{{ $network->network_name }}</h2>
                    <span class="inline-block px-4 py-1 bg-white/30 backdrop-blur-sm rounded-full text-sm font-semibold border border-gray/50">
                        {{ ucwords(str_replace('_', ' ', $network->type)) }}
                    </span><br><br>
                    @if($network->description)
                        <p class="text-gray-600 text-sm mb-4 line-clamp-2 leading-relaxed">
                            {{ Str::limit($network->description, 120) }}
                        </p>
                    @endif

                    <!-- Network Stats -->
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="text-center p-3 bg-emerald-50 rounded-lg">
                            <div class="text-2xl font-bold text-emerald-600">{{ $network->trails_count }}</div>
                            <div class="text-xs text-gray-500">Trails</div>
                        </div>
                        <div class="text-center p-3 bg-blue-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">
                                {{ number_format($network->trails->sum('distance_km'), 1) }}
                            </div>
                            <div class="text-xs text-gray-500">Total km</div>
                        </div>
                    </div>

                    <!-- Location -->
                    @if($network->address)
                        <div class="flex items-center text-gray-500 text-sm mb-4">
                            <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                            <span class="truncate">{{ $network->address }}</span>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('trail-networks.show', $network->slug) }}" 
                           class="text-center bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-md flex items-center justify-center">
                            View Network Map
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        
                        @if($network->website_url)
                            <a href="{{ $network->website_url }}" 
                               target="_blank"
                               class="text-center text-emerald-600 hover:text-emerald-700 text-sm font-medium transition-colors flex items-center justify-center">
                                Visit Website
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">No Trail Networks Yet</h3>
                    <p class="text-gray-600 mb-8">
                        We're curating organized trail systems for you. Check back soon for Nordic skiing, downhill runs, and hiking networks.
                    </p>
                    <a href="{{ route('trails.index') }}" class="btn-primary">
                        Browse Individual Trails
                    </a>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Call-to-Action Section -->
@if($networks->count() > 0)
<section class="section cta-section">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-4xl font-bold text-white mb-6">Ready to Explore Trail Networks?</h2>
        <p class="text-xl text-emerald-100 mb-8">
            Experience organized trail systems with comprehensive maps, facilities, and multiple interconnected routes for extended adventures.
        </p>
        <div class="flex flex-col md:flex-row gap-4 justify-center">
            <a href="{{ route('map') }}" class="bg-transparent border-2 border-white text-white hover:bg-white hover:text-gray-900 font-semibold py-4 px-10 rounded-xl transition-all duration-300 text-lg shadow-xl hover:scale-105">
                📍 View Interactive Map
            </a>
            <a href="{{ route('trails.index') }}" class="bg-white text-emerald-600 hover:bg-emerald-50 font-semibold py-4 px-10 rounded-xl transition-colors text-lg shadow-xl hover:scale-105 hover:text-accent-600">
                Browse All Trails
            </a>
        </div>
    </div>
</section>
@endif
@endsection