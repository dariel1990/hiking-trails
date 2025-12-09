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

{{-- Donation Section Component --}}
<section class="donation-section-wrapper py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="donation-section max-w-4xl mx-auto bg-white rounded-3xl shadow-xl overflow-hidden">
            
            <!-- Header -->
            <div class="donation-header bg-gradient-to-br from-[#10221B] to-[#1a3329] text-center py-12 px-8 relative overflow-hidden">
                <div class="absolute top-[-50%] right-[-20%] w-[300px] h-[300px] bg-[#1DC5CE] opacity-10 rounded-full"></div>
                <div class="absolute bottom-[-30%] left-[-10%] w-[200px] h-[200px] bg-[#F29727] opacity-10 rounded-full"></div>
                
                <div class="relative z-10">
                    <i class="fas fa-heart text-5xl text-[#1DC5CE] mb-4 inline-block animate-pulse"></i>
                    <h2 class="text-4xl font-semibold text-white mb-2">Support Xplore Smithers</h2>
                </div>
            </div>

            <!-- Content -->
            <div class="donation-content p-10">
                <div class="donation-pitch text-lg leading-relaxed text-[#483E3E]">
                    <p class="mb-5">
                        <strong class="text-[#10221B] font-semibold">Xplore Smithers is a 100% independent initiative</strong> created to promote ethical, authentic tourism and to support our local economy.
                    </p>
                    
                    <p class="mb-5">
                        We're not funded by any government or organization — everything you see, from the photos and videos to the maps and community stories, is made with passion, time, and a genuine love for this region.
                    </p>
                    
                    <div class="bg-gradient-to-br from-blue-50 to-cyan-50 border-l-4 border-[#1DC5CE] rounded-xl p-6 my-8">
                        <p class="text-[#10221B] font-medium m-0">
                            Your support helps us keep creating content that celebrates local businesses, connects visitors with real experiences, and shines a light on the people who make Northern BC special.
                        </p>
                    </div>
                </div>

                <!-- Features Grid -->
                <div class="grid md:grid-cols-3 gap-6 my-8">
                    <div class="text-center p-4">
                        <i class="fas fa-camera text-4xl text-[#1DC5CE] mb-3"></i>
                        <h4 class="text-[#10221B] font-semibold text-sm mb-2">Original Content</h4>
                        <p class="text-[#483E3E] text-sm opacity-80">Photos & videos of local gems</p>
                    </div>
                    <div class="text-center p-4">
                        <i class="fas fa-map-marked-alt text-4xl text-[#1DC5CE] mb-3"></i>
                        <h4 class="text-[#10221B] font-semibold text-sm mb-2">Community Maps</h4>
                        <p class="text-[#483E3E] text-sm opacity-80">Discover hidden treasures</p>
                    </div>
                    <div class="text-center p-4">
                        <i class="fas fa-users text-4xl text-[#1DC5CE] mb-3"></i>
                        <h4 class="text-[#10221B] font-semibold text-sm mb-2">Local Stories</h4>
                        <p class="text-[#483E3E] text-sm opacity-80">Real people, real experiences</p>
                    </div>
                </div>

                <p class="text-center my-8 text-[#483E3E]">
                    If you believe in what we're doing, consider making a donation — every contribution helps us keep exploring, filming, and sharing the true spirit of Smithers.
                </p>

                <!-- Donate Button -->
                <div class="text-center pt-8 border-t border-gray-200">
                    <a href="https://xploresmithers.com/support/" 
                       class="inline-flex items-center gap-3 bg-gradient-to-r from-[#10221B] to-[#1DC5CE] text-white px-10 py-4 rounded-full font-semibold text-lg transition-all duration-300 shadow-lg hover:shadow-xl hover:-translate-y-1">
                        <i class="fas fa-hand-holding-heart text-xl"></i>
                        Donate & Support
                    </a>
                    <p class="text-[#483E3E] opacity-70 text-sm italic mt-6">
                        Thank you for helping us share the beauty of Northern BC
                    </p>
                </div>
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
<style>
    :root {
        --color-primary: #F29727;
        --color-secondary: #10221B;
        --color-text: #483E3E;
        --color-accent: #1DC5CE;
        --color-gold: #DDAA6B;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .donation-section {
        max-width: 800px;
        margin: 0 auto;
        background: white;
        border-radius: 1.5rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .donation-header {
        background: linear-gradient(135deg, var(--color-secondary) 0%, #1a3329 100%);
        padding: 3rem 2rem;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .donation-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: rgba(29, 197, 206, 0.1);
        border-radius: 50%;
    }

    .donation-header::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 200px;
        height: 200px;
        background: rgba(242, 151, 39, 0.1);
        border-radius: 50%;
    }

    .donation-header h2 {
        color: white;
        font-size: 2rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 1;
    }

    .donation-header .heart-icon {
        font-size: 2.5rem;
        color: var(--color-accent);
        margin-bottom: 1rem;
        animation: heartbeat 1.5s ease-in-out infinite;
    }

    @keyframes heartbeat {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }

    .donation-content {
        padding: 2.5rem 2rem;
    }

    .donation-pitch {
        font-size: 1.05rem;
        line-height: 1.8;
        margin-bottom: 2rem;
        color: var(--color-text);
    }

    .donation-pitch strong {
        color: var(--color-secondary);
        font-weight: 600;
    }

    .highlight-box {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border-left: 4px solid var(--color-accent);
        padding: 1.5rem;
        border-radius: 0.75rem;
        margin: 2rem 0;
    }

    .highlight-box p {
        margin: 0;
        font-size: 0.95rem;
        color: var(--color-secondary);
        font-weight: 500;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin: 2rem 0;
    }

    .feature-item {
        text-align: center;
        padding: 1rem;
    }

    .feature-item i {
        font-size: 2rem;
        color: var(--color-primary);
        margin-bottom: 0.75rem;
    }

    .feature-item h4 {
        color: var(--color-secondary);
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .feature-item p {
        font-size: 0.85rem;
        color: var(--color-text);
        opacity: 0.8;
    }

    .donate-button {
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        background: linear-gradient(135deg, var(--color-primary) 0%, #e08616 100%);
        color: white;
        padding: 1rem 2.5rem;
        border-radius: 3rem;
        text-decoration: none;
        font-weight: 600;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        box-shadow: 0 8px 20px rgba(242, 151, 39, 0.3);
        margin-top: 1.5rem;
    }

    .donate-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 30px rgba(242, 151, 39, 0.4);
        background: linear-gradient(135deg, #e08616 0%, var(--color-primary) 100%);
    }

    .donate-button i {
        font-size: 1.3rem;
    }

    .button-wrapper {
        text-align: center;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #e5e7eb;
    }

    .gratitude-text {
        text-align: center;
        margin-top: 1.5rem;
        font-size: 0.9rem;
        color: var(--color-text);
        opacity: 0.7;
        font-style: italic;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .donation-header {
            padding: 2rem 1.5rem;
        }

        .donation-header h2 {
            font-size: 1.5rem;
        }

        .donation-content {
            padding: 2rem 1.5rem;
        }

        .donation-pitch {
            font-size: 1rem;
        }

        .features-grid {
            grid-template-columns: 1fr;
        }

        .donate-button {
            padding: 0.875rem 2rem;
            font-size: 1rem;
        }
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.05); opacity: 0.9; }
    }
    
    .animate-pulse {
        animation: pulse 2s ease-in-out infinite;
    }
</style>
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