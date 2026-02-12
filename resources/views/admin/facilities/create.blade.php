@extends('layouts.admin')

@section('title', 'Add Facility')
@section('page-title', 'Add Facility')

@section('content')

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.facilities.index') }}" 
           class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 w-10">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="space-y-1">
            <h2 class="text-2xl font-semibold tracking-tight">Add Facility</h2>
            <p class="text-sm text-muted-foreground">Create a new facility or point of interest</p>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirm-modal" class="hidden fixed inset-0 bg-black/50 z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full transform transition-all border">
            <div class="p-6 space-y-4">
                <div class="space-y-2">
                    <h3 id="confirm-modal-title" class="text-lg font-semibold leading-none tracking-tight">Are you absolutely sure?</h3>
                    <p id="confirm-modal-message" class="text-sm text-muted-foreground">This action cannot be undone.</p>
                </div>
                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="closeConfirmModal()" class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2 text-sm font-medium transition-colors">
                        Cancel
                    </button>
                    <button type="button" id="confirm-modal-action" class="inline-flex items-center justify-center rounded-md bg-black text-white hover:bg-black/90 h-9 px-4 py-2 text-sm font-medium transition-colors">
                        Continue
                    </button>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.facilities.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Basic Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information Card -->
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                    <div class="flex flex-col space-y-1.5 p-6 border-b">
                        <h3 class="text-lg font-semibold leading-none tracking-tight">Basic Information</h3>
                        <p class="text-sm text-muted-foreground">Enter the facility details</p>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Facility Type -->
                        <div class="space-y-2">
                            <label for="facility_type" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                Facility Type <span class="text-red-500">*</span>
                            </label>
                            <select name="facility_type" 
                                    id="facility_type" 
                                    required
                                    onchange="updateFacilityIcon()"
                                    class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                <option value="">Select facility type</option>
                                @foreach(App\Models\Facility::getFacilityTypes() as $type => $label)
                                <option value="{{ $type }}" {{ old('facility_type') === $type ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('facility_type')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Facility Name -->
                        <div class="space-y-2">
                            <label for="name" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                Facility Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   value="{{ old('name') }}" 
                                   required
                                   placeholder="e.g., Main Parking Lot"
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                            @error('name')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <label for="description" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                Description
                            </label>
                            <textarea name="description" 
                                      id="description" 
                                      rows="4"
                                      placeholder="Describe this facility and what visitors can expect..."
                                      class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Location Card with Search -->
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                    <div class="flex flex-col space-y-1.5 p-6 border-b">
                        <h3 class="text-lg font-semibold leading-none tracking-tight">Location</h3>
                        <p class="text-sm text-muted-foreground">Search for a location or click on the map to set the facility location</p>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Map Search -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none">Search Location</label>
                            <div class="relative">
                                <input type="text" 
                                       id="map-search-input" 
                                       placeholder="Search for a place (e.g., 'Smithers Main Street')..."
                                       class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 pl-10 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                       autocomplete="off">
                                <svg class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <button type="button" id="clear-search-btn" class="hidden absolute right-3 top-2.5 text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                                <div id="search-loading" class="hidden absolute right-10 top-2.5">
                                    <svg class="animate-spin h-4 w-4 text-muted-foreground" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div id="search-results-dropdown" class="hidden absolute z-50 mt-1 w-full max-w-xl bg-white rounded-md shadow-lg border border-gray-200 max-h-60 overflow-y-auto"></div>
                        </div>

                        <!-- Map -->
                        <div id="coordinate-map" class="w-full h-[400px] rounded-md border border-input relative z-0"></div>
                        
                        <!-- Coordinates -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label for="latitude" class="text-sm font-medium leading-none">
                                    Latitude <span class="text-red-500">*</span>
                                </label>
                                <input type="number" 
                                       name="latitude" 
                                       id="latitude" 
                                       step="0.0000001"
                                       value="{{ old('latitude', '54.7804') }}" 
                                       required
                                       readonly
                                       class="flex h-10 w-full rounded-md border border-input bg-muted px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                @error('latitude')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="longitude" class="text-sm font-medium leading-none">
                                    Longitude <span class="text-red-500">*</span>
                                </label>
                                <input type="number" 
                                       name="longitude" 
                                       id="longitude" 
                                       step="0.0000001"
                                       value="{{ old('longitude', '-127.1698') }}" 
                                       required
                                       readonly
                                       class="flex h-10 w-full rounded-md border border-input bg-muted px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                @error('longitude')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Media Upload Card -->
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                    <div class="flex flex-col space-y-1.5 p-6 border-b">
                        <h3 class="text-lg font-semibold leading-none tracking-tight">Media</h3>
                        <p class="text-sm text-muted-foreground">Add photos and videos to showcase this facility</p>
                    </div>
                    <div class="p-6 space-y-6">
                        <!-- Photos Upload -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none">Photos</label>
                            <div class="border-2 border-dashed border-input rounded-lg p-8 text-center hover:bg-muted/50 transition-colors cursor-pointer" onclick="document.getElementById('photos').click()">
                                <input type="file" 
                                       id="photos" 
                                       name="photos[]" 
                                       multiple 
                                       accept="image/*"
                                       class="hidden"
                                       onchange="handlePhotoSelection(this)">
                                <svg class="mx-auto h-12 w-12 text-muted-foreground mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-sm font-medium text-muted-foreground">Click to upload photos</p>
                                <p class="text-xs text-muted-foreground mt-1">or drag and drop</p>
                            </div>
                            <div id="photo-preview" class="grid grid-cols-4 gap-2 mt-4"></div>
                        </div>

                        <!-- Video URLs -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none">Video Links</label>
                            <div id="video-urls-container" class="space-y-2">
                                <div class="flex gap-2">
                                    <input type="url" 
                                           name="video_urls[]" 
                                           placeholder="https://youtube.com/watch?v=... or https://vimeo.com/..."
                                           class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                    <button type="button" 
                                            onclick="addVideoUrlField()"
                                            class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 w-10">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <p class="text-xs text-muted-foreground">Support YouTube and Vimeo links</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Settings -->
            <div class="space-y-6">
                <!-- Settings Card -->
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                    <div class="flex flex-col space-y-1.5 p-6 border-b">
                        <h3 class="text-lg font-semibold leading-none tracking-tight">Settings</h3>
                        <p class="text-sm text-muted-foreground">Configure visibility and display</p>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Custom Icon -->
                        <div class="space-y-2">
                            <label for="icon" class="text-sm font-medium leading-none">
                                Custom Icon <span class="text-muted-foreground">(Optional)</span>
                            </label>
                            <input type="text" 
                                   name="icon" 
                                   id="icon" 
                                   value="{{ old('icon') }}"
                                   maxlength="10"
                                   placeholder="Leave empty for default"
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            <div class="flex items-center justify-between">
                                <p class="text-xs text-muted-foreground">Default icon: <span id="icon-preview" class="text-base">📍</span></p>
                            </div>
                            @error('icon')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Active Status -->
                        <div class="flex items-center space-x-2 pt-4 border-t">
                            <input type="checkbox" 
                                   name="is_active" 
                                   id="is_active" 
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="h-4 w-4 rounded border-input text-primary focus:ring-ring">
                            <label for="is_active" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                Active (visible on map)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex flex-col gap-2">
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-md bg-black text-white hover:bg-black/90 h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 w-full">
                        Create Facility
                    </button>
                    <a href="{{ route('admin.facilities.index') }}" 
                       class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 w-full">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Modal functions
let confirmCallback = null;

function showConfirmModal(title, message, callback) {
    document.getElementById('confirm-modal-title').textContent = title;
    document.getElementById('confirm-modal-message').textContent = message;
    confirmCallback = callback;
    document.getElementById('confirm-modal').classList.remove('hidden');
}

function closeConfirmModal() {
    document.getElementById('confirm-modal').classList.add('hidden');
    confirmCallback = null;
}

document.getElementById('confirm-modal-action').addEventListener('click', function() {
    if (confirmCallback) {
        confirmCallback();
    }
    closeConfirmModal();
});

// Close modal on backdrop click
document.getElementById('confirm-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeConfirmModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeConfirmModal();
    }
});

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

    // Map Search Functionality
    const searchInput = document.getElementById('map-search-input');
    const clearBtn = document.getElementById('clear-search-btn');
    const loadingIndicator = document.getElementById('search-loading');
    const resultsDropdown = document.getElementById('search-results-dropdown');
    let searchTimeout = null;
    let searchMarker = null;

    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        if (query.length > 2) {
            clearBtn.classList.remove('hidden');
            
            // Debounce search
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 500);
        } else {
            clearBtn.classList.add('hidden');
            resultsDropdown.classList.add('hidden');
        }
    });

    clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        clearBtn.classList.add('hidden');
        resultsDropdown.classList.add('hidden');
        if (searchMarker) {
            map.removeLayer(searchMarker);
            searchMarker = null;
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !resultsDropdown.contains(e.target)) {
            resultsDropdown.classList.add('hidden');
        }
    });

    async function performSearch(query) {
        loadingIndicator.classList.remove('hidden');
        
        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&addressdetails=1`;
        
        try {
            const response = await fetch(url);
            const results = await response.json();
            
            loadingIndicator.classList.add('hidden');
            
            if (results.length > 0) {
                displaySearchResults(results);
            } else {
                resultsDropdown.innerHTML = '<div class="p-3 text-sm text-muted-foreground">No results found</div>';
                resultsDropdown.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Search error:', error);
            loadingIndicator.classList.add('hidden');
        }
    }

    function displaySearchResults(results) {
        resultsDropdown.innerHTML = results.map(result => `
            <div class="search-result-item p-3 hover:bg-muted cursor-pointer border-b last:border-b-0" 
                 data-lat="${result.lat}" 
                 data-lon="${result.lon}"
                 data-name="${result.display_name}">
                <div class="font-medium text-sm truncate">${result.display_name}</div>
                <div class="text-xs text-muted-foreground truncate">${result.type || 'Location'}</div>
            </div>
        `).join('');
        
        resultsDropdown.classList.remove('hidden');
        
        // Add click handlers
        resultsDropdown.querySelectorAll('.search-result-item').forEach(item => {
            item.addEventListener('click', function() {
                const lat = parseFloat(this.dataset.lat);
                const lon = parseFloat(this.dataset.lon);
                const name = this.dataset.name;
                
                // Update marker position
                marker.setLatLng([lat, lon]);
                updateCoordinates(lat, lon);
                
                // Pan map to location
                map.setView([lat, lon], 15);
                
                // Update search input
                searchInput.value = name;
                resultsDropdown.classList.add('hidden');
                
                // Add search result marker temporarily
                if (searchMarker) {
                    map.removeLayer(searchMarker);
                }
                
                searchMarker = L.marker([lat, lon], {
                    icon: L.divIcon({
                        className: 'custom-search-marker',
                        html: '<div style="background: #3b82f6; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 0 2px #3b82f6;"></div>',
                        iconSize: [12, 12],
                        iconAnchor: [6, 6]
                    })
                }).addTo(map);
                
                searchMarker.bindPopup(`<b>${name}</b>`).openPopup();
            });
        });
    }
});

// Update facility icon preview
function updateFacilityIcon() {
    const select = document.getElementById('facility_type');
    const iconPreview = document.getElementById('icon-preview');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption.value) {
        const icon = selectedOption.text.split(' ')[0];
        iconPreview.textContent = icon;
    } else {
        iconPreview.textContent = '📍';
    }
}

// Photo preview handling
function handlePhotoSelection(input) {
    const previewContainer = document.getElementById('photo-preview');
    previewContainer.innerHTML = '';
    
    if (input.files) {
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'relative aspect-square rounded-md overflow-hidden border';
                div.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-full object-cover">
                `;
                previewContainer.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }
}

// Add video URL field
function addVideoUrlField() {
    const container = document.getElementById('video-urls-container');
    const div = document.createElement('div');
    div.className = 'flex gap-2';
    div.innerHTML = `
        <input type="url" 
               name="video_urls[]" 
               placeholder="https://youtube.com/watch?v=... or https://vimeo.com/..."
               class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
        <button type="button" 
                onclick="this.parentElement.remove()"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-destructive hover:text-destructive-foreground h-10 w-10">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    `;
    container.appendChild(div);
}

// Initialize icon preview
updateFacilityIcon();
</script>

<style>
.search-result-item {
    transition: background-color 0.15s ease;
}

.search-result-item:hover {
    background-color: hsl(var(--muted));
}

.custom-search-marker {
    background: transparent;
    border: none;
}
</style>

@endsection