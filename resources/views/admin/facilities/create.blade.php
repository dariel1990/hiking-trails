@extends('layouts.admin')

@section('title', 'Add Facility')

@section('content')

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('admin.facilities.index') }}" class="text-gray-600 hover:text-gray-900 mb-4 inline-block">
            ← Back to Facilities
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Add Facility</h1>
        <p class="text-gray-600 mt-1">Add a new facility to appear on the map</p>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('admin.facilities.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Facility Type -->
                <div class="md:col-span-2">
                    <label for="facility_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Facility Type <span class="text-red-500">*</span>
                    </label>
                    <select name="facility_type" 
                            id="facility_type" 
                            required
                            onchange="updateFacilityIcon()"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 @error('facility_type') border-red-300 @enderror">
                        <option value="">-- Select Facility Type --</option>
                        <option value="parking" {{ old('facility_type') === 'parking' ? 'selected' : '' }}>🅿️ Parking</option>
                        <option value="toilets" {{ old('facility_type') === 'toilets' ? 'selected' : '' }}>🚻 Toilets</option>
                        <option value="emergency_kit" {{ old('facility_type') === 'emergency_kit' ? 'selected' : '' }}>🏥 Emergency Kit</option>
                        <option value="lodge" {{ old('facility_type') === 'lodge' ? 'selected' : '' }}>🏠 Lodge</option>
                        <option value="viewpoint" {{ old('facility_type') === 'viewpoint' ? 'selected' : '' }}>👁️ Viewpoint</option>
                        <option value="info" {{ old('facility_type') === 'info' ? 'selected' : '' }}>ℹ️ Information</option>
                        <option value="picnic" {{ old('facility_type') === 'picnic' ? 'selected' : '' }}>🍽️ Picnic Area</option>
                        <option value="water" {{ old('facility_type') === 'water' ? 'selected' : '' }}>💧 Water</option>
                        <option value="shelter" {{ old('facility_type') === 'shelter' ? 'selected' : '' }}>⛺ Shelter</option>
                    </select>
                    @error('facility_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Facility Name -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        Facility Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           value="{{ old('name') }}" 
                           required
                           placeholder="e.g., Main Parking Lot"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 @error('name') border-red-300 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Coordinates with Map -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Facility Location <span class="text-red-500">*</span>
                    </label>
                    <p class="text-sm text-gray-600 mb-3">Click on the map to set the facility location</p>
                    
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
                                   value="{{ old('latitude', '54.7804') }}" 
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
                                   value="{{ old('longitude', '-127.1698') }}" 
                                   required
                                   readonly
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm focus:border-green-500 focus:ring-green-500 @error('longitude') border-red-300 @enderror">
                            @error('longitude')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">
                        Description
                    </label>
                    <textarea name="description" 
                              id="description" 
                              rows="3"
                              placeholder="Additional information about this facility..."
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 @error('description') border-red-300 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Custom Icon (Optional) -->
                <div>
                    <label for="icon" class="block text-sm font-medium text-gray-700">
                        Custom Icon <span class="text-gray-400">(Optional)</span>
                    </label>
                    <input type="text" 
                           name="icon" 
                           id="icon" 
                           value="{{ old('icon') }}"
                           maxlength="10"
                           placeholder="Leave empty for default"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 @error('icon') border-red-300 @enderror">
                    <p class="mt-1 text-xs text-gray-500">Current icon: <span id="icon-preview">--</span></p>
                    @error('icon')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Is Active -->
                <div class="flex items-center">
                    <input type="checkbox" 
                           name="is_active" 
                           id="is_active" 
                           value="1"
                           {{ old('is_active', true) ? 'checked' : '' }}
                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-700">
                        Active (visible on map)
                    </label>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="mt-6 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.facilities.index') }}" 
                   class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-medium transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                    Add Facility
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get default coordinates (Smithers)
    const defaultLat = parseFloat(document.getElementById('latitude').value);
    const defaultLng = parseFloat(document.getElementById('longitude').value);
    
    // Initialize map centered on Smithers
    const map = L.map('coordinate-map').setView([defaultLat, defaultLng], 11);
    
    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 18
    }).addTo(map);
    
    // Marker for selected facility location
    let marker = L.marker([defaultLat, defaultLng], {
        draggable: true
    }).addTo(map);
    
    marker.on('dragend', function(e) {
        const position = marker.getLatLng();
        updateCoordinates(position.lat, position.lng);
    });
    
    // Click on map to set facility location
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
    
    // Update facility icon preview
    window.updateFacilityIcon = function() {
        const select = document.getElementById('facility_type');
        const iconPreview = document.getElementById('icon-preview');
        const selectedOption = select.options[select.selectedIndex];
        
        if (selectedOption.value) {
            const icon = selectedOption.text.split(' ')[0];
            iconPreview.textContent = icon;
        } else {
            iconPreview.textContent = '--';
        }
    };
    
    // Initialize icon preview
    updateFacilityIcon();
});
</script>

@endsection