@extends('layouts.public')

@section('title', 'Discover Ethical Adventures - Trail Finder')

@section('content')
<!-- Hero Section with XploreSmithers-inspired messaging -->
<section class="relative min-h-screen flex items-center justify-center hero-gradient overflow-hidden">
    <!-- Background Map -->
    <div id="hero-map" class="absolute inset-0 z-10 opacity-40"></div>
    
    <!-- Animated Background Pattern -->
    <div class="absolute inset-0 bg-pattern-mountains z-15"></div>
    
    <!-- Enhanced Overlay for better text visibility -->
    <div class="absolute inset-0 bg-black bg-opacity-50 z-20"></div>
    
    <!-- Content - Properly centered -->
    <div class="relative z-30 text-center text-white max-w-6xl mx-auto px-4 flex flex-col justify-center min-h-screen py-20">
        <!-- Badge -->
        <div class="mb-8 fade-in">
            <span class="inline-flex items-center px-6 py-3 bg-white/30 backdrop-blur-sm rounded-full text-white text-sm font-semibold border border-white/40 shadow-lg">
                🌲 Discover Ethical Adventures
            </span>
        </div>
        
        <!-- Main Headline -->
        <div class="slide-in-up mb-8">
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold leading-tight">
                <span class="text-white text-shadow-lg">Be The Story.</span><br>
                <span class="bg-gradient-to-r from-emerald-300 via-sand-200 to-accent-300 bg-clip-text text-transparent">
                    Explore Responsibly.
                </span>
            </h1>
        </div>
        
        <!-- Subtitle with better visibility -->
        <div class="slide-in-up mb-12" style="animation-delay: 0.2s;">
            <p class="text-large md:text-xl lg:text-2xl text-white leading-relaxed max-w-4xl mx-auto text-shadow-md">
                Discover amazing hiking trails while promoting respectful, sustainable tourism that supports 
                local communities and the natural environment.
            </p>
        </div>
        
        <!-- CTA Buttons -->
        <div class="scale-in space-y-4 md:space-y-0 md:space-x-6 flex flex-col md:flex-row justify-center mb-16" style="animation-delay: 0.6s;">
            <a href="#trails" class="btn-primary text-lg px-10 py-4 hover-glow shadow-xl">
                🗺️ Explore Trails
            </a>
            <a href="{{ route('map') }}" class="bg-transparent border-2 border-white text-white hover:bg-white hover:text-gray-900 font-semibold py-4 px-10 rounded-xl transition-all duration-300 text-lg shadow-xl">
                📍 View Interactive Map
            </a>
        </div>
    </div>
</section>

<!-- Values Section - XploreSmithers inspired -->
<section class="section bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="section-title text-forest-700">Values Before Business</h2>
            <p class="section-subtitle">
                Discover the best trails while supporting ethical tourism practices and local communities
            </p>
        </div>
        
        <div class="feature-grid">
            <!-- Sustainable Tourism -->
            <div class="feature-card group hover-lift p-4">
                <div class="feature-icon group-hover:scale-110">
                    <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <h3 class="feature-title text-forest-700">Sustainable Tourism</h3>
                <p class="feature-description">
                    Every trail recommendation supports conservation efforts and local communities. 
                    We believe in tourism that gives back to the places we explore.
                </p>
            </div>
            
            <!-- Community Connection -->
            <div class="feature-card group hover-lift p-4">
                <div class="feature-icon group-hover:scale-110">
                    <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="feature-title text-forest-700">Community Connection</h3>
                <p class="feature-description">
                    Connect with local culture and traditions. Our platform highlights indigenous knowledge 
                    and supports community-led conservation initiatives.
                </p>
            </div>
            
            <!-- Responsible Adventure -->
            <div class="feature-card group hover-lift p-4">
                <div class="feature-icon group-hover:scale-110">
                    <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3 class="feature-title text-forest-700">Responsible Adventure</h3>
                <p class="feature-description">
                    Leave No Trace principles guide every recommendation. We provide safety information 
                    and environmental guidelines for every trail experience.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Trails Section - Enhanced -->
<section id="trails" class="section bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="section-title text-forest-700">Featured Trail Adventures</h2>
            <p class="section-subtitle">
                Start your ethical adventure with these carefully curated hiking destinations
            </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($featuredTrails as $trail)
            <div class="trail-card group hover-lift">
                <!-- Trail Image -->
                <div class="trail-card-image group-hover:scale-105 transition-transform duration-500" onclick="window.location.href='{{ route('trails.show', $trail->id) }}'">
                    @if($trail->featuredPhoto)
                        <img src="{{ $trail->featuredPhoto->url }}" alt="{{ $trail->name }}" 
                             class="w-full h-full object-cover">
                    @else
                        <div class="absolute inset-0 flex items-center justify-center">
                            <img src="{{ asset('images/no-image.png') }}" 
                            alt="Trail Finder Logo" 
                            class="w-24 h-24 object-contain transition-all duration-300 group-hover:scale-105">
                        </div>
                    @endif
                    
                    <!-- Badges -->
                    @if($trail->is_featured)
                        <div class="absolute top-3 left-3">
                            <span class="badge bg-amber-400 text-amber-900 font-bold">
                                ⭐ Featured
                            </span>
                        </div>
                    @endif
                    
                    <div class="absolute top-3 right-3">
                        <span class="difficulty-badge difficulty-{{ intval($trail->difficulty_level) }}">
                            {{ $trail->difficulty_level }}/5
                        </span>
                    </div>
                </div>
                
                <!-- Trail Info -->
                <div class="trail-card-body">
                    <div class="mb-3">
                        <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-accent-600 transition-colors">
                            <a href="{{ route('trails.show', $trail->id) }}">{{ $trail->name }}</a>
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

                    <!-- Trail Stats -->
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

                    <!-- Trail Type and Action -->
                    <div class="flex items-center justify-between">
                        <span class="badge-secondary">
                            {{ ucwords(str_replace('-', ' ', $trail->trail_type)) }}
                        </span>
                        
                        <a href="{{ route('trails.show', $trail->id) }}" 
                           class="text-emerald-600 hover:text-emerald-700 font-semibold text-sm group-hover:text-emerald-700 flex items-center">
                            Explore Trail
                            <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                    @if($trail->best_seasons)
                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <p class="text-xs text-gray-500 mb-1">Best seasons:</p>
                            <div class="flex flex-wrap gap-1">
                                @foreach(array_slice($trail->best_seasons, 0, 3) as $season)
                                    <span class="season-{{ strtolower($season) }} text-xs px-2 py-1 rounded border">
                                        {{ $season }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-12">
            <a href="{{ route('trails.index') }}" class="btn-primary text-lg px-10 py-4 hover-glow">
                Discover All Trails
            </a>
        </div>
    </div>
</section>

<!-- Statistics Section - Enhanced -->
<section class="section cta-section">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-white mb-4">Adventure by the Numbers</h2>
            <p class="text-xl text-emerald-100">Join thousands of ethical adventurers</p>
        </div>
        
        <div class="grid md:grid-cols-4 gap-8">
            <div class="stats-card text-center bg-white/10 backdrop-blur-sm border border-white/20">
                <div class="stats-number text-white">{{ $stats['total_trails'] ?? 0 }}</div>
                <div class="stats-label text-emerald-200">Curated Trails</div>
                <p class="text-sm text-emerald-100 mt-2">Ethically sourced adventures</p>
            </div>
            <div class="stats-card text-center bg-white/10 backdrop-blur-sm border border-white/20">
                <div class="stats-number text-white">{{ number_format($stats['total_distance'] ?? 0) }}</div>
                <div class="stats-label text-emerald-200">Kilometers of Trails</div>
                <p class="text-sm text-emerald-100 mt-2">Sustainable paths to explore</p>
            </div>
            <div class="stats-card text-center bg-white/10 backdrop-blur-sm border border-white/20">
                <div class="stats-number text-white">{{ number_format($stats['total_elevation'] ?? 0) }}</div>
                <div class="stats-label text-emerald-200">Meters of Elevation</div>
                <p class="text-sm text-emerald-100 mt-2">Respect the mountain</p>
            </div>
            <div class="stats-card text-center bg-white/10 backdrop-blur-sm border border-white/20">
                <div class="stats-number text-white">{{ $stats['locations_count'] ?? 0 }}</div>
                <div class="stats-label text-emerald-200">Locations Supported</div>
                <p class="text-sm text-emerald-100 mt-2">Communities we partner with</p>
            </div>
        </div>
    </div>
</section>

<!-- Community Section - New -->
<section class="section bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="section-title text-forest-700">Join Our Community</h2>
            <p class="section-subtitle">
                Be part of the movement that's putting ethical trail discovery on the map
            </p>
        </div>
        
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <h3 class="text-3xl font-bold text-gray-900 mb-6 text-forest-700">Adventure with Purpose</h3>
                <div class="space-y-6">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">Leave No Trace Promise</h4>
                            <p class="text-gray-600">Every adventure follows sustainable practices that preserve nature for future generations.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">Community Support</h4>
                            <p class="text-gray-600">Connect with local communities and support indigenous-led conservation efforts.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">Safety First</h4>
                            <p class="text-gray-600">Comprehensive safety information and emergency protocols for every trail experience.</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8">
                    <a href="{{ route('trails.index') }}" class="btn-primary hover-glow">
                        Start Your Journey
                    </a>
                </div>
            </div>
            
            <div class="relative">
                <div class="aspect-square bg-gradient-to-br from-emerald-400 to-green-600 rounded-2xl overflow-hidden">
                    <!-- Placeholder for community image -->
                    <div class="w-full h-full flex items-center justify-center text-white">
                        <div class="text-center">
                            <svg class="w-24 h-24 mx-auto mb-4 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <p class="text-lg font-medium">Community of Ethical Adventurers</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize hero map with better styling
        const heroMap = L.map('hero-map', {
            zoomControl: false,
            scrollWheelZoom: false,
            doubleClickZoom: false,
            boxZoom: false,
            keyboard: false,
            dragging: false,
            touchZoom: false
        }).setView([49.2827, -122.7927], 10);
        
        // Use a more natural map style
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '© OpenStreetMap contributors © CARTO'
        }).addTo(heroMap);

        // Add featured trail markers with custom styling
        const trails = @json($featuredTrails);
        trails.forEach((trail, index) => {
            if (trail.start_coordinates) {
                // Create custom marker
                const customIcon = L.divIcon({
                    html: `<div class="bg-emerald-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm shadow-lg border-2 border-white">
                        ${index + 1}
                    </div>`,
                    className: 'custom-marker',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });
                
                L.marker(trail.start_coordinates, { icon: customIcon })
                    .addTo(heroMap)
                    .bindPopup(`
                        <div class="text-center">
                            <strong class="text-emerald-600">${trail.name}</strong><br>
                            <span class="text-gray-600">${trail.distance_km} km • Difficulty ${trail.difficulty_level}/5</span><br>
                            <a href="/trails/${trail.id}" class="text-emerald-600 hover:text-emerald-700 font-medium text-sm">View Details →</a>
                        </div>
                    `);
            }
        });
        
        // Smooth scroll functionality
        document.querySelector('a[href="#trails"]').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('trails').scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
        });
        
        // Add scroll-triggered animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, observerOptions);
        
        // Observe elements for animation
        document.querySelectorAll('.feature-card, .trail-card, .stats-card').forEach(el => {
            observer.observe(el);
        });
    });
</script>
@endpush