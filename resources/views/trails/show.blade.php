@extends('layouts.public')

@section('title', $trail->name)

@section('content')
<div class="bg-gray-50 min-h-screen">
    <!-- Hero Section with Trail Image -->
    <div class="relative h-64 bg-gradient-to-r from-green-600 to-blue-600">
        <div class="absolute inset-0 bg-black bg-opacity-30"></div>
        <div class="relative max-w-7xl mx-auto px-4 h-full flex items-end pb-8">
            <div class="text-white">
                <nav class="mb-4">
                    <a href="{{ route('trails.index') }}" class="inline-flex items-center text-white/80 hover:text-white text-sm">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        Back to Trails
                    </a>
                </nav>
                <h1 class="text-4xl font-bold mb-2">{{ $trail->name }}</h1>
                @if($trail->location)
                    <p class="text-xl text-white/90">{{ $trail->location }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 -mt-16 relative z-10">
        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                <div class="text-3xl font-bold text-blue-600 mb-1">{{ $trail->difficulty_level }}</div>
                <div class="text-sm text-gray-600">Difficulty</div>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                <div class="text-3xl font-bold text-green-600 mb-1">{{ $trail->distance_km }}</div>
                <div class="text-sm text-gray-600">Kilometers</div>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                <div class="text-3xl font-bold text-orange-600 mb-1">{{ $trail->elevation_gain_m }}</div>
                <div class="text-sm text-gray-600">Elevation (m)</div>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                <div class="text-3xl font-bold text-purple-600 mb-1">{{ $trail->estimated_time_hours }}</div>
                <div class="text-sm text-gray-600">Hours</div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Interactive Map -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-green-500 to-blue-500 px-6 py-4">
                        <h2 class="text-xl font-bold text-white">Trail Route</h2>
                    </div>
                    <div class="p-6">
                        <div id="trail-detail-map" class="w-full h-96 rounded-lg border"></div>
                        <div class="mt-4 flex justify-between items-center">
                            <button id="fit-route-btn" class="bg-primary-600 text-white px-6 py-2 rounded-lg hover:bg-primary-700 transition-colors font-medium">
                                Center Route
                            </button>
                            <div class="text-sm text-gray-500">
                                @if($trail->route_coordinates)
                                    <span class="text-green-600">✓ Route available</span>
                                @else
                                    <span class="text-orange-600">⚠ Route pending</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">About This Trail</h2>
                    <p class="text-gray-700 leading-relaxed text-lg">{{ $trail->description }}</p>
                </div>

                <!-- Trail Information -->
                @if($trail->directions || $trail->parking_info || $trail->safety_notes)
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Trail Information</h2>
                    
                    <div class="space-y-6">
                        @if($trail->directions)
                        <div class="border-l-4 border-blue-500 pl-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10.293 15.707a1 1 0 010-1.414L14.586 10l-4.293-4.293a1 1 0 111.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                Directions
                            </h3>
                            <p class="text-gray-700">{{ $trail->directions }}</p>
                        </div>
                        @endif

                        @if($trail->parking_info)
                        <div class="border-l-4 border-green-500 pl-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                        <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V8a1 1 0 00-1-1h-3z"/>
                                    </svg>
                                </div>
                                Parking
                            </h3>
                            <p class="text-gray-700">{{ $trail->parking_info }}</p>
                        </div>
                        @endif

                        @if($trail->safety_notes)
                        <div class="border-l-4 border-red-500 pl-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                Safety Notes
                            </h3>
                            <p class="text-gray-700">{{ $trail->safety_notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Trail Details Card -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Trail Details</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600 font-medium">Trail Type</span>
                            <span class="font-semibold capitalize px-3 py-1 bg-gray-100 rounded-full text-sm">
                                {{ str_replace('-', ' ', $trail->trail_type) }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600 font-medium">Status</span>
                            @if($trail->status === 'active')
                                <span class="font-semibold text-green-600 px-3 py-1 bg-green-100 rounded-full text-sm">Active</span>
                            @elseif($trail->status === 'seasonal')
                                <span class="font-semibold text-yellow-600 px-3 py-1 bg-yellow-100 rounded-full text-sm">Seasonal</span>
                            @else
                                <span class="font-semibold text-red-600 px-3 py-1 bg-red-100 rounded-full text-sm">Closed</span>
                            @endif
                        </div>
                        
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600 font-medium">Views</span>
                            <span class="font-semibold">{{ number_format($trail->view_count) }}</span>
                        </div>
                        
                        @if($trail->best_seasons)
                        <div class="py-2">
                            <span class="text-gray-600 font-medium block mb-2">Best Seasons</span>
                            <div class="flex flex-wrap gap-2">
                                @foreach($trail->best_seasons as $season)
                                    <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                                        {{ $season }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Quick Actions</h3>
                    <div class="space-y-3">
                        <button id="show-on-map-btn" class="w-full bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white py-3 px-4 rounded-lg font-medium transition-all duration-200 transform hover:scale-105">
                            Show on Main Map
                        </button>
                        
                        @if($trail->start_coordinates)
                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ $trail->start_coordinates[0] }},{{ $trail->start_coordinates[1] }}" 
                           target="_blank"
                           class="w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white py-3 px-4 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 text-center block">
                            Get Directions
                        </a>
                        @endif
                        
                        <button class="w-full bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white py-3 px-4 rounded-lg font-medium transition-all duration-200 transform hover:scale-105">
                            Download GPX
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const trail = @json($trail);
        
        // Initialize map
        const map = L.map('trail-detail-map').setView(trail.start_coordinates, 13);
        
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>'
        }).addTo(map);
        
        let trailRoute = null;
        
        // Add trail route
        if (trail.route_coordinates && trail.route_coordinates.length > 0) {
            trailRoute = L.polyline(trail.route_coordinates, {
                color: '#10B981',
                weight: 5,
                opacity: 0.8,
                lineJoin: 'round',
                lineCap: 'round'
            }).addTo(map);
            
            map.fitBounds(trailRoute.getBounds(), { padding: [30, 30] });
        }
        
        // Custom markers
        const startIcon = L.divIcon({
            html: '<div class="bg-green-500 text-white rounded-full w-10 h-10 flex items-center justify-center font-bold border-2 border-white shadow-lg">S</div>',
            className: 'custom-marker',
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });
        
        L.marker(trail.start_coordinates, { icon: startIcon })
            .addTo(map)
            .bindPopup(`<div class="text-center"><b>Trail Start</b><br><span class="text-sm">${trail.name}</span></div>`);
        
        // End marker if different
        if (trail.end_coordinates && 
            JSON.stringify(trail.start_coordinates) !== JSON.stringify(trail.end_coordinates)) {
            
            const endIcon = L.divIcon({
                html: '<div class="bg-red-500 text-white rounded-full w-10 h-10 flex items-center justify-center font-bold border-2 border-white shadow-lg">E</div>',
                className: 'custom-marker',
                iconSize: [40, 40],
                iconAnchor: [20, 20]
            });
            
            L.marker(trail.end_coordinates, { icon: endIcon })
                .addTo(map)
                .bindPopup('<div class="text-center"><b>Trail End</b></div>');
        }
        
        // Event listeners
        document.getElementById('fit-route-btn').addEventListener('click', function() {
            if (trailRoute) {
                map.fitBounds(trailRoute.getBounds(), { padding: [30, 30] });
            } else {
                map.setView(trail.start_coordinates, 13);
            }
        });
        
        document.getElementById('show-on-map-btn').addEventListener('click', function() {
            window.open(`{{ route('map') }}?trail=${trail.id}`, '_blank');
        });
    });
</script>
@endpush
@endsection