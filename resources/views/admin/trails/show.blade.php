@extends('layouts.admin')

@section('title', $trail->name)
@section('page-title', 'Trail Details')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    <!-- Header -->
    <div class="space-y-2">
        <div class="flex items-center gap-2 text-sm text-muted-foreground">
            <a href="{{ route('admin.trails.index') }}" class="hover:text-foreground transition-colors">Trails</a>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>{{ $trail->name }}</span>
        </div>
        <div class="flex items-center justify-between">
            <div class="space-y-1">
                <h1 class="text-3xl font-bold tracking-tight">{{ $trail->name }}</h1>
                <p class="text-muted-foreground">{{ $trail->location ?? 'Location not specified' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.trails.edit', $trail) }}" 
                   class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Trail
                </a>
                <a href="{{ route('trails.show', $trail) }}" target="_blank"
                   class="inline-flex items-center justify-center rounded-md bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    View Live
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Trail Map -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Trail Route</h3>
                    <div id="trail-map" class="w-full h-96 rounded-md border border-input bg-muted z-10"></div>
                    <div class="mt-4 flex justify-between items-center">
                        <button id="fit-route-btn" 
                                class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3">
                            Center Route
                        </button>
                        <div class="text-sm text-muted-foreground">
                            @if($trail->route_coordinates)
                                <span class="text-green-600">Route available</span>
                            @else
                                <span class="text-orange-600">No route data</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Description</h3>
                    <p class="text-muted-foreground leading-relaxed">{{ $trail->description }}</p>
                </div>
            </div>

            <!-- Additional Information -->
            @if($trail->directions || $trail->parking_info || $trail->safety_notes)
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-6">Trail Information</h3>
                    
                    <div class="space-y-6">
                        @if($trail->directions)
                        <div class="border-l-4 border-blue-500 pl-4">
                            <h4 class="font-medium mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10.293 15.707a1 1 0 010-1.414L14.586 10l-4.293-4.293a1 1 0 111.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                                Directions
                            </h4>
                            <p class="text-muted-foreground">{{ $trail->directions }}</p>
                        </div>
                        @endif

                        @if($trail->parking_info)
                        <div class="border-l-4 border-green-500 pl-4">
                            <h4 class="font-medium mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                    <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V8a1 1 0 00-1-1h-3z"/>
                                </svg>
                                Parking
                            </h4>
                            <p class="text-muted-foreground">{{ $trail->parking_info }}</p>
                        </div>
                        @endif

                        @if($trail->safety_notes)
                        <div class="border-l-4 border-red-500 pl-4">
                            <h4 class="font-medium mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                Safety Notes
                            </h4>
                            <p class="text-muted-foreground">{{ $trail->safety_notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Photos Section -->
            @if($trail->photos->count() > 0)
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Photos ({{ $trail->photos->count() }})</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($trail->photos as $photo)
                            <div class="relative group overflow-hidden rounded-lg border border-input">
                                <img src="{{ $photo->url }}" alt="{{ $photo->caption ?? $trail->name }}" 
                                     class="w-full h-32 object-cover transition-transform group-hover:scale-105">
                                @if($photo->is_featured)
                                    <div class="absolute top-2 left-2">
                                        <span class="bg-yellow-400 text-yellow-900 px-2 py-1 rounded text-xs font-medium">
                                            Featured
                                        </span>
                                    </div>
                                @endif
                                @if($photo->caption)
                                    <div class="absolute bottom-0 inset-x-0 bg-black bg-opacity-50 text-white p-2">
                                        <p class="text-xs">{{ $photo->caption }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Trail Statistics -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Trail Statistics</h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-3 bg-muted rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">{{ $trail->difficulty_level }}</div>
                                <div class="text-sm text-muted-foreground">Difficulty</div>
                            </div>
                            <div class="text-center p-3 bg-muted rounded-lg">
                                <div class="text-2xl font-bold text-green-600">{{ $trail->distance_km }}</div>
                                <div class="text-sm text-muted-foreground">km</div>
                            </div>
                            <div class="text-center p-3 bg-muted rounded-lg">
                                <div class="text-2xl font-bold text-orange-600">{{ $trail->elevation_gain_m }}</div>
                                <div class="text-sm text-muted-foreground">elevation (m)</div>
                            </div>
                            <div class="text-center p-3 bg-muted rounded-lg">
                                <div class="text-2xl font-bold text-purple-600">{{ $trail->estimated_time_hours }}</div>
                                <div class="text-sm text-muted-foreground">hours</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trail Details -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Details</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between py-2 border-b border-border">
                            <span class="text-muted-foreground">Trail Type</span>
                            <span class="font-medium capitalize">{{ str_replace('-', ' ', $trail->trail_type) }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between py-2 border-b border-border">
                            <span class="text-muted-foreground">Status</span>
                            @php
                                $statusConfig = [
                                    'active' => ['class' => 'bg-green-100 text-green-800', 'label' => 'Active'],
                                    'closed' => ['class' => 'bg-red-100 text-red-800', 'label' => 'Closed'],
                                    'seasonal' => ['class' => 'bg-yellow-100 text-yellow-800', 'label' => 'Seasonal']
                                ];
                                $config = $statusConfig[$trail->status] ?? ['class' => 'bg-gray-100 text-gray-800', 'label' => ucfirst($trail->status)];
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $config['class'] }}">
                                {{ $config['label'] }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between py-2 border-b border-border">
                            <span class="text-muted-foreground">Views</span>
                            <span class="font-medium">{{ number_format($trail->view_count) }}</span>
                        </div>
                        
                        @if($trail->is_featured)
                        <div class="flex items-center justify-between py-2 border-b border-border">
                            <span class="text-muted-foreground">Featured</span>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-800">
                                Yes
                            </span>
                        </div>
                        @endif
                        
                        @if($trail->best_seasons)
                        <div class="py-2">
                            <span class="text-muted-foreground block mb-2">Best Seasons</span>
                            <div class="flex flex-wrap gap-1">
                                @foreach($trail->best_seasons as $season)
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-blue-100 text-blue-700">
                                        {{ $season }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Actions</h3>
                    <div class="space-y-2">
                        <button onclick="window.open('{{ route('map') }}?trail={{ $trail->id }}', '_blank')" 
                                class="w-full inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 9m0 8V9m0 0V7"/>
                            </svg>
                            Show on Map
                        </button>
                        
                        @if($trail->start_coordinates)
                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ $trail->start_coordinates[0] }},{{ $trail->start_coordinates[1] }}" 
                           target="_blank"
                           class="w-full inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 9m0 8V9m0 0V7"/>
                            </svg>
                            Get Directions
                        </a>
                        @endif
                        
                        <form method="POST" action="{{ route('admin.trails.destroy', $trail) }}" class="w-full">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    onclick="return confirm('Are you sure you want to delete this trail? This action cannot be undone.')"
                                    class="w-full inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-destructive text-destructive-foreground hover:bg-destructive/90 h-9 px-3">
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Delete Trail
                            </button>
                        </form>
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
        const map = L.map('trail-map').setView(trail.start_coordinates || [49.2827, -122.7927], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        let trailRoute = null;
        
        // Add trail route if available
        if (trail.route_coordinates && trail.route_coordinates.length > 0) {
            trailRoute = L.polyline(trail.route_coordinates, {
                color: '#3B82F6',
                weight: 4,
                opacity: 0.8,
                lineJoin: 'round',
                lineCap: 'round'
            }).addTo(map);
            
            map.fitBounds(trailRoute.getBounds(), { padding: [20, 20] });
        }
        
        // Start marker
        if (trail.start_coordinates) {
            const startIcon = L.divIcon({
                html: '<div class="bg-green-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm border-2 border-white shadow-lg">S</div>',
                className: 'custom-marker',
                iconSize: [32, 32],
                iconAnchor: [16, 16]
            });
            
            L.marker(trail.start_coordinates, { icon: startIcon })
                .addTo(map)
                .bindPopup('<div class="text-center"><b>Trail Start</b><br><span class="text-sm">' + trail.name + '</span></div>');
        }
        
        // End marker if different from start
        if (trail.end_coordinates && 
            JSON.stringify(trail.start_coordinates) !== JSON.stringify(trail.end_coordinates)) {
            
            const endIcon = L.divIcon({
                html: '<div class="bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm border-2 border-white shadow-lg">E</div>',
                className: 'custom-marker',
                iconSize: [32, 32],
                iconAnchor: [16, 16]
            });
            
            L.marker(trail.end_coordinates, { icon: endIcon })
                .addTo(map)
                .bindPopup('<div class="text-center"><b>Trail End</b></div>');
        }
        
        // Fit route button
        document.getElementById('fit-route-btn').addEventListener('click', function() {
            if (trailRoute) {
                map.fitBounds(trailRoute.getBounds(), { padding: [20, 20] });
            } else if (trail.start_coordinates) {
                map.setView(trail.start_coordinates, 13);
            }
        });
    });
</script>

<style>
/* shadcn/ui color variables */
:root {
  --background: 0 0% 100%;
  --foreground: 222.2 84% 4.9%;
  --card: 0 0% 100%;
  --card-foreground: 222.2 84% 4.9%;
  --popover: 0 0% 100%;
  --popover-foreground: 222.2 84% 4.9%;
  --primary: 221.2 83.2% 53.3%;
  --primary-foreground: 210 40% 98%;
  --secondary: 210 40% 96%;
  --secondary-foreground: 222.2 84% 4.9%;
  --muted: 210 40% 96%;
  --muted-foreground: 215.4 16.3% 46.9%;
  --accent: 210 40% 96%;
  --accent-foreground: 222.2 84% 4.9%;
  --destructive: 0 84.2% 60.2%;
  --destructive-foreground: 210 40% 98%;
  --border: 214.3 31.8% 91.4%;
  --input: 214.3 31.8% 91.4%;
  --ring: 221.2 83.2% 53.3%;
  --radius: 0.5rem;
}

.bg-background { background-color: hsl(var(--background)); }
.text-foreground { color: hsl(var(--foreground)); }
.bg-card { background-color: hsl(var(--card)); }
.text-card-foreground { color: hsl(var(--card-foreground)); }
.bg-primary { background-color: hsl(var(--primary)); }
.text-primary-foreground { color: hsl(var(--primary-foreground)); }
.bg-primary\/90 { background-color: hsl(var(--primary) / 0.9); }
.bg-muted { background-color: hsl(var(--muted)); }
.text-muted-foreground { color: hsl(var(--muted-foreground)); }
.bg-accent { background-color: hsl(var(--accent)); }
.text-accent-foreground { color: hsl(var(--accent-foreground)); }
.hover\:bg-accent:hover { background-color: hsl(var(--accent)); }
.hover\:text-accent-foreground:hover { color: hsl(var(--accent-foreground)); }
.border-border { border-color: hsl(var(--border)); }
.border-input { border-color: hsl(var(--input)); }
.bg-destructive { background-color: hsl(var(--destructive)); }
.text-destructive-foreground { color: hsl(var(--destructive-foreground)); }
.hover\:bg-destructive\/90:hover { background-color: hsl(var(--destructive) / 0.9); }
.ring-ring { --tw-ring-color: hsl(var(--ring)); }
.ring-offset-background { --tw-ring-offset-color: hsl(var(--background)); }
</style>
@endpush
@endsection