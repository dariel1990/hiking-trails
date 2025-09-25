@extends('layouts.admin')

@section('title', 'Manage Highlights - ' . $trail->name)
@section('page-title', 'Trail Highlights: ' . $trail->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.trails.show', $trail) }}" class="text-primary-600 hover:text-primary-700 flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to Trail Details
    </a>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <!-- Map for adding highlights -->
    <div class="admin-card">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Add Highlight to Map</h3>
            
            <div id="highlights-map" class="w-full h-96 rounded-lg border border-gray-300 mb-4"></div>
            
            <p class="text-sm text-gray-600 mb-4">
                <strong>Instructions:</strong> Click on the map to place a highlight marker, then fill in the details below.
            </p>

            <form id="highlight-form" method="POST" action="{{ route('admin.trails.highlights.store', $trail) }}" class="space-y-4">
                @csrf
                
                <input type="hidden" name="lat" id="highlight-lat" required>
                <input type="hidden" name="lng" id="highlight-lng" required>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Highlight Type *</label>
                    <select name="type" id="highlight-type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">Select type...</option>
                        @foreach($highlightTypes as $key => $type)
                            <option value="{{ $key }}" data-icon="{{ $type['icon'] }}" data-color="{{ $type['color'] }}">
                                {{ $type['icon'] }} {{ $type['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                    <input type="text" name="name" id="highlight-name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                           placeholder="e.g., Eagle's Nest Viewpoint">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="highlight-description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                              placeholder="Optional description of this highlight..."></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Custom Icon (emoji)</label>
                        <input type="text" name="icon" id="highlight-icon" maxlength="10"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                               placeholder="🏔️">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Marker Color</label>
                        <input type="color" name="color" id="highlight-color" value="#10B981"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>

                <div class="flex space-x-3">
                    <button type="submit" class="admin-button-primary flex-1">
                        Add Highlight
                    </button>
                    <button type="button" id="clear-form-btn" class="admin-button-secondary">
                        Clear
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Existing highlights list -->
    <div class="admin-card">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                Existing Highlights ({{ $trail->highlights->count() }})
            </h3>

            @if($trail->highlights->count() > 0)
                <div class="space-y-3 max-h-[600px] overflow-y-auto">
                    @foreach($trail->highlights as $highlight)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow" data-highlight-id="{{ $highlight->id }}">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex items-start space-x-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-xl" 
                                         style="background-color: {{ $highlight->color }}">
                                        {{ $highlight->display_icon }}
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900">{{ $highlight->name }}</h4>
                                        <p class="text-sm text-gray-600">
                                            {{ $highlightTypes[$highlight->type]['name'] ?? ucfirst($highlight->type) }}
                                        </p>
                                        @if($highlight->description)
                                            <p class="text-sm text-gray-500 mt-1">{{ $highlight->description }}</p>
                                        @endif
                                        <p class="text-xs text-gray-400 mt-1">
                                            📍 {{ $highlight->coordinates[0] }}, {{ $highlight->coordinates[1] }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <button onclick="editHighlight({{ $highlight->id }})" 
                                            class="text-blue-600 hover:text-blue-800">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <form method="POST" action="{{ route('admin.trails.highlights.delete', [$trail, $highlight]) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Delete this highlight?')" 
                                                class="text-red-600 hover:text-red-800">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No highlights yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by adding highlights to the map.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    let highlightsMap;
    let currentMarker = null;
    let existingMarkers = [];
    const trail = @json($trail);
    const highlights = @json($trail->highlights);

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize map
        highlightsMap = L.map('highlights-map').setView(
            trail.start_coordinates, 
            13
        );

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(highlightsMap);

        // Add trail route if available
        if (trail.route_coordinates && trail.route_coordinates.length > 0) {
            L.polyline(trail.route_coordinates, {
                color: '#10B981',
                weight: 4,
                opacity: 0.6
            }).addTo(highlightsMap);
        }

        // Add existing highlights
        highlights.forEach(highlight => {
            addExistingHighlight(highlight);
        });

        // Click handler to add new highlight
        highlightsMap.on('click', function(e) {
            const lat = e.latlng.lat.toFixed(6);
            const lng = e.latlng.lng.toFixed(6);

            // Remove previous temporary marker
            if (currentMarker) {
                highlightsMap.removeLayer(currentMarker);
            }

            // Add new temporary marker
            currentMarker = L.marker([lat, lng], {
                icon: L.divIcon({
                    html: '<div class="bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center border-2 border-white shadow-lg">📍</div>',
                    className: 'custom-marker',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                })
            }).addTo(highlightsMap);

            // Update form fields
            document.getElementById('highlight-lat').value = lat;
            document.getElementById('highlight-lng').value = lng;

            currentMarker.bindPopup('New highlight location<br>Fill in details below').openPopup();
        });

        // Type selector change
        document.getElementById('highlight-type').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const color = selectedOption.dataset.color;
            
            if (color) {
                document.getElementById('highlight-color').value = color;
            }
        });

        // Clear form button
        document.getElementById('clear-form-btn').addEventListener('click', function() {
            document.getElementById('highlight-form').reset();
            document.getElementById('highlight-lat').value = '';
            document.getElementById('highlight-lng').value = '';
            
            if (currentMarker) {
                highlightsMap.removeLayer(currentMarker);
                currentMarker = null;
            }
        });
    });

    function addExistingHighlight(highlight) {
        const icon = L.divIcon({
            html: `<div style="background-color: ${highlight.color};" class="text-white rounded-full w-8 h-8 flex items-center justify-center border-2 border-white shadow-lg text-lg">${highlight.display_icon}</div>`,
            className: 'custom-marker',
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });

        const marker = L.marker(highlight.coordinates, { icon })
            .addTo(highlightsMap)
            .bindPopup(`<b>${highlight.name}</b><br>${highlight.description || ''}`);

        existingMarkers.push({ id: highlight.id, marker: marker });
    }

    function editHighlight(highlightId) {
        // This will be implemented in the next step for inline editing
        alert('Edit functionality coming in next step!');
    }
</script>
@endpush
@endsection