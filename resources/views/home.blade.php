@extends('layouts.public')

@section('title', 'Discover Amazing Hiking Trails')

@section('content')
<!-- Hero Section with Map -->
<div class="relative h-screen">
    <div id="hero-map" class="absolute inset-0 z-10"></div>
    
    <!-- Overlay Content -->
    <div class="absolute inset-0 bg-black bg-opacity-40 z-20 flex items-center justify-center">
        <div class="text-center text-white max-w-4xl px-4">
            <h1 class="text-5xl md:text-7xl font-bold mb-6">
                Discover Amazing Trails
            </h1>
            <p class="text-xl md:text-2xl mb-8 text-gray-200">
                Explore hundreds of hiking trails with detailed maps, photos, and information
            </p>
            <div class="space-x-4">
                <a href="#trails" class="bg-primary-600 hover:bg-primary-700 text-white px-8 py-4 rounded-lg text-lg font-semibold transition-colors">
                    Browse Trails
                </a>
                <a href="{{ route('map') }}" class="bg-transparent border-2 border-white text-white hover:bg-white hover:text-gray-900 px-8 py-4 rounded-lg text-lg font-semibold transition-colors">
                    View Map
                </a>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 z-30 w-full max-w-2xl px-4">
        <div class="bg-white rounded-lg shadow-2xl p-6">
            <form action="{{ route('trails.index') }}" method="GET" class="flex space-x-4">
                <div class="flex-1">
                    <input type="text" name="search" placeholder="Search trails by name or location..." 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" 
                           value="{{ request('search') }}">
                </div>
                <div class="w-48">
                    <select name="difficulty" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">All Difficulties</option>
                        <option value="1" {{ request('difficulty') == '1' ? 'selected' : '' }}>Easy</option>
                        <option value="2" {{ request('difficulty') == '2' ? 'selected' : '' }}>Moderate</option>
                        <option value="3" {{ request('difficulty') == '3' ? 'selected' : '' }}>Hard</option>
                        <option value="4" {{ request('difficulty') == '4' ? 'selected' : '' }}>Very Hard</option>
                    </select>
                </div>
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-8 py-3 rounded-lg font-semibold transition-colors">
                    Search
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Featured Trails Section -->
<section id="trails" class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Featured Trails</h2>
            <p class="text-xl text-gray-600">Start your adventure with these popular hiking destinations</p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($featuredTrails as $trail)
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <div class="h-64 bg-gradient-to-br from-green-400 to-blue-600 relative overflow-hidden">
                    @if($trail->featured_image)
                        <img src="{{ $trail->featured_image }}" alt="{{ $trail->name }}" 
                             class="w-full h-full object-cover">
                    @else
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg class="w-16 h-16 text-white opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                        </div>
                    @endif
                    <div class="absolute top-4 right-4">
                        <span class="bg-white bg-opacity-90 text-gray-900 px-3 py-1 rounded-full text-sm font-semibold">
                            {{ $trail->difficulty_level }}/5
                        </span>
                    </div>
                </div>
                
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $trail->name }}</h3>
                    <p class="text-gray-600 mb-4 line-clamp-2">{{ $trail->description }}</p>
                    
                    <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                        <span>📍 {{ $trail->location ?? 'Location TBD' }}</span>
                        <span>⏱️ {{ $trail->estimated_time_hours }}h</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="text-sm">
                            <span class="text-primary-600 font-semibold">{{ $trail->distance_km }} km</span>
                            <span class="text-gray-400 mx-2">•</span>
                            <span class="text-green-600 font-semibold">{{ $trail->elevation_gain_m }}m ↗</span>
                        </div>
                        <a href="{{ route('trails.show', $trail->id) }}" 
                           class="text-primary-600 hover:text-primary-700 font-semibold">
                            View Details →
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-12">
            <a href="{{ route('trails.index') }}" 
               class="bg-primary-600 hover:bg-primary-700 text-white px-8 py-4 rounded-lg text-lg font-semibold transition-colors inline-block">
                View All Trails
            </a>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-16 bg-primary-600 text-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid md:grid-cols-4 gap-8 text-center">
            <div>
                <div class="text-4xl font-bold mb-2">{{ $stats['total_trails'] ?? 0 }}</div>
                <div class="text-primary-100">Trails Available</div>
            </div>
            <div>
                <div class="text-4xl font-bold mb-2">{{ number_format($stats['total_distance'] ?? 0) }}</div>
                <div class="text-primary-100">Kilometers of Trails</div>
            </div>
            <div>
                <div class="text-4xl font-bold mb-2">{{ number_format($stats['total_elevation'] ?? 0) }}</div>
                <div class="text-primary-100">Meters of Elevation</div>
            </div>
            <div>
                <div class="text-4xl font-bold mb-2">{{ $stats['locations_count'] ?? 0 }}</div>
                <div class="text-primary-100">Locations Covered</div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize hero map
        const heroMap = L.map('hero-map', {
            zoomControl: false,
            scrollWheelZoom: false,
            doubleClickZoom: false,
            boxZoom: false,
            keyboard: false,
            dragging: false,
            touchZoom: false
        }).setView([49.2827, -122.7927], 10);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(heroMap);

        // Add some sample trail markers
        const trails = @json($featuredTrails);
        trails.forEach(trail => {
            if (trail.start_coordinates) {
                L.marker(trail.start_coordinates)
                    .addTo(heroMap)
                    .bindPopup(`<strong>${trail.name}</strong><br>${trail.distance_km} km`);
            }
        });
        
        // Smooth scroll to trails section
        document.querySelector('a[href="#trails"]').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('trails').scrollIntoView({ 
                behavior: 'smooth' 
            });
        });
    });
</script>
@endpush