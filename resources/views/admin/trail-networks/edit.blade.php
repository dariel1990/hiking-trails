@extends('layouts.admin')

@section('title', 'Edit Trail Network')

@section('content')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('admin.trail-networks.index') }}" class="text-gray-600 hover:text-gray-900 mb-4 inline-block">
            ← Back to Trail Networks
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Edit Trail Network</h1>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('admin.trail-networks.update', $trailNetwork) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Network Name -->
                <div class="md:col-span-2">
                    <label for="network_name" class="block text-sm font-medium text-gray-700">
                        Network Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="network_name" 
                           id="network_name" 
                           value="{{ old('network_name', $trailNetwork->network_name) }}" 
                           required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 @error('network_name') border-red-300 @enderror">
                    @error('network_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Slug -->
                <div class="md:col-span-2">
                    <label for="slug" class="block text-sm font-medium text-gray-700">
                        Slug <span class="text-gray-400">(Optional - auto-generated from name)</span>
                    </label>
                    <input type="text" 
                           name="slug" 
                           id="slug" 
                           value="{{ old('slug', $trailNetwork->slug) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 @error('slug') border-red-300 @enderror">
                    @error('slug')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">
                        Network Type <span class="text-red-500">*</span>
                    </label>
                    <select name="type" 
                            id="type" 
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 @error('type') border-red-300 @enderror">
                        <option value="">-- Select Type --</option>
                        <option value="nordic_skiing" {{ old('type', $trailNetwork->type) === 'nordic_skiing' ? 'selected' : '' }}>Nordic Skiing</option>
                        <option value="downhill_skiing" {{ old('type', $trailNetwork->type) === 'downhill_skiing' ? 'selected' : '' }}>Downhill Skiing</option>
                        <option value="hiking" {{ old('type', $trailNetwork->type) === 'hiking' ? 'selected' : '' }}>Hiking</option>
                        <option value="mountain_biking" {{ old('type', $trailNetwork->type) === 'mountain_biking' ? 'selected' : '' }}>Mountain Biking</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Is Always Visible -->
                <div class="flex items-center">
                    <input type="checkbox" 
                           name="is_always_visible" 
                           id="is_always_visible" 
                           value="1"
                           {{ old('is_always_visible', $trailNetwork->is_always_visible) ? 'checked' : '' }}
                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                    <label for="is_always_visible" class="ml-2 block text-sm text-gray-700">
                        Always visible on main map
                    </label>
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">
                        Description
                    </label>
                    <textarea name="description" 
                              id="description" 
                              rows="4"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 @error('description') border-red-300 @enderror">{{ old('description', $trailNetwork->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Coordinates with Map -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Location Coordinates <span class="text-red-500">*</span>
                    </label>
                    <p class="text-sm text-gray-600 mb-3">Click on the map to update the center point of this trail network</p>
                    
                    <!-- Map Container -->
                    <div id="coordinate-map" class="w-full h-96 rounded-lg border border-gray-300 mb-4"></div>
                    
                    <!-- Coordinate Inputs -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="latitude" class="block text-sm font-medium text-gray-700">
                                Latitude <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                name="latitude" 
                                id="latitude" 
                                step="0.0000001"
                                value="{{ old('latitude', $trailNetwork->latitude) }}" 
                                required
                                readonly
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm focus:border-green-500 focus:ring-green-500 @error('latitude') border-red-300 @enderror">
                            @error('latitude')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="longitude" class="block text-sm font-medium text-gray-700">
                                Longitude <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                name="longitude" 
                                id="longitude" 
                                step="0.0000001"
                                value="{{ old('longitude', $trailNetwork->longitude) }}" 
                                required
                                readonly
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm focus:border-green-500 focus:ring-green-500 @error('longitude') border-red-300 @enderror">
                            @error('longitude')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                <!-- Address -->
                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700">
                        Address
                    </label>
                    <input type="text" 
                           name="address" 
                           id="address" 
                           value="{{ old('address', $trailNetwork->address) }}"
                           placeholder="e.g., Smithers, BC"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 @error('address') border-red-300 @enderror">
                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Website URL -->
                <div class="md:col-span-2">
                    <label for="website_url" class="block text-sm font-medium text-gray-700">
                        Website URL
                    </label>
                    <input type="url" 
                           name="website_url" 
                           id="website_url" 
                           value="{{ old('website_url', $trailNetwork->website_url) }}"
                           placeholder="https://example.com"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 @error('website_url') border-red-300 @enderror">
                    @error('website_url')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="mt-6 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.trail-networks.index') }}" 
                   class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-medium transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                    Update Trail Network
                </button>
            </div>
        </form>
    </div>
</div>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get existing coordinates
    const existingLat = parseFloat(document.getElementById('latitude').value);
    const existingLng = parseFloat(document.getElementById('longitude').value);
    
    // Initialize map centered on existing location
    const map = L.map('coordinate-map').setView([existingLat, existingLng], 13);
    
    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 18
    }).addTo(map);
    
    // Add marker at existing location
    let marker = L.marker([existingLat, existingLng], {
        draggable: true
    }).addTo(map);
    
    marker.on('dragend', function(e) {
        const position = marker.getLatLng();
        updateCoordinates(position.lat, position.lng);
    });
    
    // Click on map to update location
    map.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        
        marker.setLatLng([lat, lng]);
        updateCoordinates(lat, lng);
    });
    
    function updateCoordinates(lat, lng) {
        document.getElementById('latitude').value = lat.toFixed(7);
        document.getElementById('longitude').value = lng.toFixed(7);
    }
});
</script>
@endsection