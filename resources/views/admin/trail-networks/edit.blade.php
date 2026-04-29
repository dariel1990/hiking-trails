@extends('layouts.admin')

@section('title', 'Edit Trail Network')
@section('page-title', 'Edit Trail Network')

@section('content')
<link href="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.css" rel="stylesheet">

<div x-data="iconPicker('{{ old('icon', $trailNetwork->icon ?? '') }}', {{ $trailNetwork->image ? "'".asset('storage/'.$trailNetwork->image)."'" : 'null' }})" class="px-4 lg:px-8 py-6">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-6 mb-6 border-b border-gray-200">
        <div>
            <nav class="flex items-center gap-2 text-xs text-gray-500 mb-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Admin</a>
                <span>/</span>
                <a href="{{ route('admin.trail-networks.index') }}" class="hover:text-gray-700">Trail Networks</a>
                <span>/</span>
                <span class="text-gray-700 font-medium truncate max-w-xs">{{ $trailNetwork->network_name }}</span>
            </nav>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-600 border-2 border-white shadow-md flex items-center justify-center text-lg select-none"
                     x-text="selectedIcon"></div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">Edit Trail Network</h1>
                    <p class="text-sm text-gray-500">{{ $trailNetwork->network_name }} · {{ $trailNetwork->trails()->count() }} trail(s)</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.trail-networks.index') }}"
               class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg font-medium text-sm transition-colors">
                Cancel
            </a>
            <button type="submit" form="trail-network-form"
                    class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg font-medium text-sm transition-colors shadow-sm">
                Save Changes
            </button>
        </div>
    </div>

    <form id="trail-network-form" action="{{ route('admin.trail-networks.update', $trailNetwork) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="remove_image" :value="removeExistingImage ? '1' : '0'">

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

            {{-- LEFT: Form fields --}}
            <div class="xl:col-span-7 space-y-6">

                {{-- Identity --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-green-50 text-green-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900">Identity</h2>
                            <p class="text-xs text-gray-500">Name, slug, type, and map icon</p>
                        </div>
                    </div>
                    <div class="px-6 py-5 space-y-5">
                        <div>
                            <label for="network_name" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Network Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="network_name" id="network_name"
                                   value="{{ old('network_name', $trailNetwork->network_name) }}" required
                                   placeholder="e.g., Hudson Bay Mountain Ski Resort"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm px-4 py-2.5 focus:border-green-500 focus:ring-green-500 @error('network_name') border-red-300 @enderror">
                            @error('network_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="slug" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Slug <span class="text-gray-400 font-normal text-xs">(auto-generated)</span>
                                </label>
                                <input type="text" name="slug" id="slug"
                                       value="{{ old('slug', $trailNetwork->slug) }}"
                                       placeholder="hudson-bay-mountain"
                                       class="block w-full rounded-lg border-gray-300 shadow-sm px-4 py-2.5 focus:border-green-500 focus:ring-green-500 @error('slug') border-red-300 @enderror">
                                @error('slug')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Network Type <span class="text-red-500">*</span>
                                </label>
                                <select name="type" id="type" required
                                        x-on:change="onTypeChange($event.target.value)"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm px-4 py-2.5 focus:border-green-500 focus:ring-green-500 @error('type') border-red-300 @enderror">
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
                        </div>

                        {{-- Icon Picker --}}
                        <div class="pt-2">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-gray-700">Map Marker Icon</label>
                                <span class="text-xs text-gray-400">Select a preset or type your own</span>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50/50 p-4">
                                <div class="flex items-start gap-5">
                                    <div class="flex-shrink-0 flex flex-col items-center gap-1.5">
                                        <div class="w-12 h-12 rounded-lg bg-amber-600 border-2 border-white shadow-md flex items-center justify-center text-2xl select-none"
                                             x-text="selectedIcon"></div>
                                        <span class="text-[10px] uppercase tracking-wide text-gray-400">Preview</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="grid grid-cols-9 gap-1.5 mb-3">
                                            <template x-for="emoji in emojiOptions" :key="emoji">
                                                <button type="button"
                                                        class="aspect-square rounded-lg border-2 text-lg flex items-center justify-center hover:bg-white transition-all"
                                                        :class="selectedIcon === emoji ? 'border-green-500 bg-white shadow-sm' : 'border-gray-200 bg-white/60'"
                                                        x-on:click="selectIcon(emoji)"
                                                        x-text="emoji"></button>
                                            </template>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <input type="text"
                                                   placeholder="Type any emoji..."
                                                   maxlength="4"
                                                   class="block w-40 rounded-lg border-gray-300 shadow-sm text-sm px-3 py-2 focus:border-green-500 focus:ring-green-500"
                                                   x-model="customEmoji"
                                                   x-on:input="if (customEmoji.trim()) { selectIcon(customEmoji.trim()) }">
                                            <span class="text-xs text-gray-400">or paste a custom emoji</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="icon" :value="selectedIcon">
                        </div>
                    </div>
                </div>

                {{-- Cover Image --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900">Cover Image</h2>
                            <p class="text-xs text-gray-500">Optional — shown on the map when this network is selected</p>
                        </div>
                    </div>
                    <div class="px-6 py-5">
                        <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp"
                               class="hidden" x-on:change="onImageChange($event)">

                        <template x-if="!imagePreview">
                            <label for="image"
                                   class="flex flex-col items-center justify-center gap-2 w-full h-44 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 hover:bg-gray-100 hover:border-gray-400 cursor-pointer transition-colors">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <div class="text-center">
                                    <p class="text-sm font-medium text-gray-700">Click to upload an image</p>
                                    <p class="text-xs text-gray-500 mt-0.5">JPG, PNG, or WebP · up to 5 MB</p>
                                </div>
                            </label>
                        </template>

                        <template x-if="imagePreview">
                            <div class="relative rounded-lg overflow-hidden border border-gray-200 bg-gray-100">
                                <img :src="imagePreview" alt="Cover preview" class="w-full h-56 object-cover">
                                <div class="absolute top-2 right-2 flex gap-2">
                                    <label for="image"
                                           class="bg-white/95 hover:bg-white text-gray-700 text-xs font-medium px-3 py-1.5 rounded-md shadow cursor-pointer transition-colors">
                                        Change
                                    </label>
                                    <button type="button" x-on:click="clearImage()"
                                            class="bg-white/95 hover:bg-red-50 text-red-600 text-xs font-medium px-3 py-1.5 rounded-md shadow transition-colors">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </template>
                        @error('image')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Details --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900">Details</h2>
                            <p class="text-xs text-gray-500">Description and visibility</p>
                        </div>
                    </div>
                    <div class="px-6 py-5 space-y-5">
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                            <textarea name="description" id="description" rows="5"
                                      placeholder="Describe this trail network — terrain, season, highlights..."
                                      class="block w-full rounded-lg border-gray-300 shadow-sm px-4 py-2.5 focus:border-green-500 focus:ring-green-500 @error('description') border-red-300 @enderror">{{ old('description', $trailNetwork->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <label for="is_always_visible"
                               class="flex items-start gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100/70 transition-colors">
                            <input type="checkbox" name="is_always_visible" id="is_always_visible" value="1"
                                   {{ old('is_always_visible', $trailNetwork->is_always_visible) ? 'checked' : '' }}
                                   class="mt-0.5 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                            <div>
                                <span class="block text-sm font-medium text-gray-700">Always visible on main map</span>
                                <span class="block text-xs text-gray-500 mt-0.5">
                                    Show this network's marker even when it has no active trails.
                                </span>
                            </div>
                        </label>
                    </div>
                </div>

            </div>

            {{-- RIGHT: Map + Contact & Web --}}
            <div class="xl:col-span-5 space-y-6">
                {{-- Map --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h2 class="text-sm font-semibold text-gray-900">Map Location</h2>
                            <p class="text-xs text-gray-500">Click the map or drag the marker</p>
                        </div>
                        <span class="text-xs text-red-500 font-medium">Required</span>
                    </div>
                    <div class="relative">
                        <div id="coordinate-map" class="w-full h-[480px] border-y border-gray-100"></div>
                        {{-- Layer switcher --}}
                        <div class="absolute top-3 left-3 z-10 inline-flex bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden text-xs font-medium">
                            <button type="button" data-map-layer="standard"
                                    class="map-layer-btn px-3 py-1.5 transition-colors bg-white text-gray-700 hover:bg-gray-50">Standard</button>
                            <button type="button" data-map-layer="satellite"
                                    class="map-layer-btn px-3 py-1.5 transition-colors bg-green-600 text-white border-l border-gray-200">Satellite</button>
                        </div>
                    </div>
                    <div class="px-6 py-4 grid grid-cols-2 gap-4 bg-gray-50/50">
                        <div>
                            <label for="latitude" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Latitude</label>
                            <input type="number" name="latitude" id="latitude"
                                   step="0.0000001" value="{{ old('latitude', $trailNetwork->latitude) }}"
                                   required readonly
                                   class="block w-full rounded-lg border-gray-300 bg-white shadow-sm text-sm font-mono px-3 py-2 @error('latitude') border-red-300 @enderror">
                            @error('latitude')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="longitude" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Longitude</label>
                            <input type="number" name="longitude" id="longitude"
                                   step="0.0000001" value="{{ old('longitude', $trailNetwork->longitude) }}"
                                   required readonly
                                   class="block w-full rounded-lg border-gray-300 bg-white shadow-sm text-sm font-mono px-3 py-2 @error('longitude') border-red-300 @enderror">
                            @error('longitude')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Contact & Web --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 015.656 5.656l-4 4a4 4 0 01-5.656-5.656m1.414-1.414a4 4 0 015.656-5.656l4 4"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900">Contact & Web</h2>
                            <p class="text-xs text-gray-500">Address and website link</p>
                        </div>
                    </div>
                    <div class="px-6 py-5 space-y-5">
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-1.5">Address</label>
                            <input type="text" name="address" id="address"
                                   value="{{ old('address', $trailNetwork->address) }}"
                                   placeholder="e.g., Smithers, BC"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm px-4 py-2.5 focus:border-green-500 focus:ring-green-500 @error('address') border-red-300 @enderror">
                            @error('address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="website_url" class="block text-sm font-medium text-gray-700 mb-1.5">Website URL</label>
                            <input type="url" name="website_url" id="website_url"
                                   value="{{ old('website_url', $trailNetwork->website_url) }}"
                                   placeholder="https://example.com"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm px-4 py-2.5 focus:border-green-500 focus:ring-green-500 @error('website_url') border-red-300 @enderror">
                            @error('website_url')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.js"></script>
<script>
function iconPicker(initial, existingImage = null) {
    return {
        selectedIcon: initial || '🏔️',
        customEmoji: '',
        emojiOptions: ['🏔️', '⛷️', '🎿', '🏂', '❄️', '🥾', '🧗', '🚵', '🚴', '🌲', '⛺', '🌄', '🗺️', '📍', '⭐', '🏕️', '🌊', '🦅'],
        imagePreview: existingImage || null,
        hasExistingImage: !!existingImage,
        removeExistingImage: false,
        selectIcon(emoji) {
            this.selectedIcon = emoji;
        },
        onTypeChange(type) {
            const defaults = {
                nordic_skiing: '⛷️',
                downhill_skiing: '🎿',
                hiking: '🥾',
                mountain_biking: '🚵',
            };
            if (defaults[type]) {
                this.selectedIcon = defaults[type];
            }
        },
        onImageChange(event) {
            const file = event.target.files && event.target.files[0];
            if (!file) { return; }
            this.removeExistingImage = false;
            const reader = new FileReader();
            reader.onload = (e) => { this.imagePreview = e.target.result; };
            reader.readAsDataURL(file);
        },
        clearImage() {
            this.imagePreview = null;
            this.removeExistingImage = this.hasExistingImage;
            const input = document.getElementById('image');
            if (input) { input.value = ''; }
        },
    };
}

document.addEventListener('DOMContentLoaded', function () {
    mapboxgl.accessToken = '{{ config('services.mapbox.access_token') }}';

    const existingLat = parseFloat(document.getElementById('latitude').value) || 54.7804;
    const existingLng = parseFloat(document.getElementById('longitude').value) || -127.1698;

    const mapStyles = {
        standard: 'mapbox://styles/mapbox/streets-v12',
        satellite: 'mapbox://styles/mapbox/satellite-streets-v12',
    };
    let currentLayer = 'satellite';

    const map = new mapboxgl.Map({
        container: 'coordinate-map',
        style: mapStyles[currentLayer],
        center: [existingLng, existingLat],
        zoom: 13,
        attributionControl: false,
    });

    map.addControl(new mapboxgl.NavigationControl({ showCompass: false }), 'bottom-right');

    const marker = new mapboxgl.Marker({ draggable: true, color: '#D97706' })
        .setLngLat([existingLng, existingLat])
        .addTo(map);

    marker.on('dragend', () => {
        const pos = marker.getLngLat();
        updateCoordinates(pos.lat, pos.lng);
    });

    map.on('click', (e) => {
        marker.setLngLat([e.lngLat.lng, e.lngLat.lat]);
        updateCoordinates(e.lngLat.lat, e.lngLat.lng);
    });

    function updateCoordinates(lat, lng) {
        document.getElementById('latitude').value = lat.toFixed(7);
        document.getElementById('longitude').value = lng.toFixed(7);
    }

    document.querySelectorAll('.map-layer-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            const layer = btn.dataset.mapLayer;
            if (!mapStyles[layer] || layer === currentLayer) { return; }
            currentLayer = layer;
            map.setStyle(mapStyles[layer]);
            document.querySelectorAll('.map-layer-btn').forEach((b) => {
                const active = b.dataset.mapLayer === layer;
                b.classList.toggle('bg-green-600', active);
                b.classList.toggle('text-white', active);
                b.classList.toggle('bg-white', !active);
                b.classList.toggle('text-gray-700', !active);
                b.classList.toggle('hover:bg-gray-50', !active);
            });
        });
    });
});
</script>
@endsection
