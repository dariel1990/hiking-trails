@extends('layouts.admin')

@section('title', 'Edit Highlight')
@section('page-title', 'Edit Highlight')

@section('content')
<link href="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.css" rel="stylesheet">

@php
    $coords = is_array($highlight->coordinates) ? $highlight->coordinates : [];
    $existingLat = old('latitude', $coords[0] ?? '');
    $existingLng = old('longitude', $coords[1] ?? '');
    $rawIcon = $highlight->getRawOriginal('icon');
    $resolvedIcon = $highlight->icon ?: '📍';
@endphp

<div x-data="highlightIconState('{{ $rawIcon }}', @js($resolvedIcon))" class="px-4 lg:px-8 py-6">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-6 mb-6 border-b border-gray-200">
        <div>
            <nav class="flex items-center gap-2 text-xs text-gray-500 mb-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Admin</a>
                <span>/</span>
                <a href="{{ route('admin.highlights.index') }}" class="hover:text-gray-700">Trail Highlights</a>
                <span>/</span>
                <span class="text-gray-700 font-medium truncate max-w-xs">{{ $highlight->name }}</span>
            </nav>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg border flex items-center justify-center text-xl select-none"
                     x-text="iconPreview"
                     :style="`background-color: ${colorValue}1A; border-color: ${colorValue}33;`"></div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">Edit Highlight</h1>
                    <p class="text-sm text-gray-500">
                        {{ $highlight->name }}
                        @if($highlight->trail)
                            · <a href="{{ route('admin.trails.edit', $highlight->trail) }}" class="hover:underline">{{ $highlight->trail->name }}</a>
                        @endif
                    </p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.highlights.index') }}"
               class="bg-white border border-gray-400 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg font-medium text-sm transition-colors">
                Cancel
            </a>
            <button type="submit" form="highlight-form"
                    class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg font-medium text-sm transition-colors shadow-sm">
                Save Changes
            </button>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
            <p class="text-sm font-medium text-red-800 mb-1">Please fix the following:</p>
            <ul class="list-disc list-inside text-sm text-red-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="highlight-form" action="{{ route('admin.highlights.update', $highlight) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

            {{-- LEFT: Form fields --}}
            <div class="xl:col-span-7 space-y-6">

                {{-- Details --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-900">Details</h2>
                        <p class="text-xs text-gray-500">Type, name, description, icon, and color</p>
                    </div>
                    <div class="px-6 py-5 space-y-5">
                        <div>
                            <label for="feature_type" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Highlight Type <span class="text-red-500">*</span>
                            </label>
                            <select name="feature_type" id="feature_type" required
                                    x-on:change="onTypeChange($event.target)"
                                    class="block w-full rounded-lg border-gray-400 shadow-sm px-4 py-2.5 focus:border-green-500 focus:ring-green-500 @error('feature_type') border-red-300 @enderror">
                                @foreach($featureTypes as $type => $label)
                                    <option value="{{ $type }}" {{ old('feature_type', $highlight->feature_type) === $type ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('feature_type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name"
                                   value="{{ old('name', $highlight->name) }}" required
                                   placeholder="e.g., Twin Falls Overlook"
                                   class="block w-full rounded-lg border-gray-400 shadow-sm px-4 py-2.5 focus:border-green-500 focus:ring-green-500 @error('name') border-red-300 @enderror">
                            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                            <textarea name="description" id="description" rows="4"
                                      placeholder="Describe this highlight..."
                                      class="block w-full rounded-lg border-gray-400 shadow-sm px-4 py-2.5 focus:border-green-500 focus:ring-green-500 @error('description') border-red-300 @enderror">{{ old('description', $highlight->description) }}</textarea>
                            @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="icon" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Custom Icon <span class="text-gray-400 font-normal text-xs">(Optional)</span>
                                </label>
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 flex-shrink-0 rounded-lg border flex items-center justify-center text-xl select-none"
                                         x-text="iconPreview"
                                         :style="`background-color: ${colorValue}1A; border-color: ${colorValue}33;`"></div>
                                    <input type="text" name="icon" id="icon"
                                           value="{{ old('icon', $rawIcon) }}" maxlength="10"
                                           placeholder="📍"
                                           x-model="customIcon"
                                           class="block w-full rounded-lg border-gray-400 shadow-sm px-4 py-2.5 focus:border-green-500 focus:ring-green-500 @error('icon') border-red-300 @enderror">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Leave empty to use the type's default icon.</p>
                                @error('icon')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="color" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Marker Color <span class="text-gray-400 font-normal text-xs">(Optional)</span>
                                </label>
                                <div class="flex items-center gap-3">
                                    <input type="color" id="color-picker"
                                           x-model="colorValue"
                                           class="h-11 w-14 flex-shrink-0 rounded-lg border border-gray-300 cursor-pointer p-1">
                                    <input type="text" name="color" id="color"
                                           value="{{ old('color', $highlight->getRawOriginal('color')) }}"
                                           placeholder="#3B82F6" maxlength="7"
                                           x-model="colorValue"
                                           class="block w-full rounded-lg border-gray-400 shadow-sm px-4 py-2.5 font-mono text-sm focus:border-green-500 focus:ring-green-500 @error('color') border-red-300 @enderror">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Color of this highlight's map marker.</p>
                                @error('color')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Media (read-only preview) --}}
                @if($highlight->media->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                        <div class="flex-1">
                            <h2 class="text-sm font-semibold text-gray-900">Media</h2>
                            <p class="text-xs text-gray-500">Click to preview. Photos and videos are managed from the trail builder.</p>
                        </div>
                        <span class="text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded-full">
                            {{ $highlight->media->count() }} {{ Str::plural('item', $highlight->media->count()) }}
                        </span>
                    </div>
                    <div class="px-6 py-5">
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach($highlight->media as $media)
                            <div class="relative aspect-square rounded-lg overflow-hidden border border-gray-200 bg-gray-100 cursor-pointer group hover:opacity-90 transition"
                                 onclick="openMediaCarousel('highlight-{{ $highlight->id }}', {{ $loop->index }})">
                                @if($media->isPhoto())
                                    <img src="{{ $media->url }}" alt="{{ $media->caption ?? $highlight->name }}" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/25 transition-all flex items-center justify-center">
                                        <svg class="w-7 h-7 text-white opacity-0 group-hover:opacity-100 transition-opacity drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                        </svg>
                                    </div>
                                @else
                                    <div class="video-thumb relative w-full h-full bg-gray-900"
                                         @if($media->isExternal() && $media->video_url) data-video-url="{{ $media->video_url }}" @endif>
                                        @if($media->thumbnail_path)
                                            <img src="{{ $media->thumbnail_url }}" alt="Video thumbnail" class="w-full h-full object-cover">
                                        @else
                                            <div class="video-icon-placeholder w-full h-full flex items-center justify-center">
                                                <svg class="w-9 h-9 text-white opacity-75" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                                </svg>
                                            </div>
                                        @endif
                                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                            <div class="bg-white/90 rounded-full p-2 group-hover:scale-110 transition-transform">
                                                <svg class="w-5 h-5 text-gray-900" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- Danger Zone --}}
                <div class="bg-red-50/40 rounded-xl border border-red-200">
                    <div class="px-6 py-4 border-b border-red-200/70">
                        <h2 class="text-sm font-semibold text-red-900">Danger Zone</h2>
                        <p class="text-xs text-red-700/80">Permanent actions that cannot be undone</p>
                    </div>
                    <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Delete this highlight</p>
                            <p class="text-xs text-gray-600 mt-0.5">The highlight and its media links will be removed from the trail.</p>
                        </div>
                        <button type="submit" form="delete-highlight-form"
                                onclick="return confirm('Are you sure you want to delete this highlight? This cannot be undone.');"
                                class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition-colors shadow-sm flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete Highlight
                        </button>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Location --}}
            <div class="xl:col-span-5">
                <div class="xl:sticky xl:top-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                            <div class="flex-1 min-w-0">
                                <h2 class="text-sm font-semibold text-gray-900">Map Location</h2>
                                <p class="text-xs text-gray-500">Search or click the map to update the position</p>
                            </div>
                            <span class="text-xs text-red-500 font-medium">Required</span>
                        </div>

                        <div class="px-6 py-4 border-b border-gray-100 relative">
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input type="text" id="map-search-input" placeholder="Search for a place..." autocomplete="off"
                                       class="block w-full rounded-lg border-gray-400 shadow-sm pl-10 pr-10 py-2.5 text-sm focus:border-green-500 focus:ring-green-500">
                                <button type="button" id="clear-search-btn"
                                        class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                                <div id="search-loading" class="hidden absolute right-9 top-1/2 -translate-y-1/2">
                                    <svg class="animate-spin w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div id="search-results-dropdown"
                                 class="hidden absolute z-30 mt-1 w-[calc(100%-3rem)] bg-white rounded-lg shadow-lg border border-gray-200 max-h-60 overflow-y-auto"></div>
                        </div>

                        <div class="relative">
                            <div id="coordinate-map" class="w-full h-[420px] border-y border-gray-100 z-0"></div>
                            <div class="absolute top-2 right-2 z-10">
                                <div class="relative">
                                    <button id="admin-layers-toggle" type="button"
                                        class="bg-white rounded-lg shadow-md p-2 hover:bg-gray-50 transition-colors border border-gray-200">
                                        <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 0v10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2z"/>
                                        </svg>
                                    </button>
                                    <div id="admin-layers-dropdown" class="hidden absolute top-full right-0 mt-1 bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden z-50" style="min-width:200px;">
                                        <div class="p-2">
                                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 py-2">Map Style</div>
                                            <div class="grid grid-cols-2 gap-2 mb-1">
                                                <button type="button" class="admin-layer-card active" data-map-type="standard">
                                                    <div class="admin-layer-preview"><img src="{{ asset('images/map-layers/standard.png') }}" alt="Standard"></div>
                                                    <span class="admin-layer-label">Standard</span>
                                                    <svg class="admin-layer-check" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                </button>
                                                <button type="button" class="admin-layer-card" data-map-type="satellite">
                                                    <div class="admin-layer-preview"><img src="{{ asset('images/map-layers/satellite.png') }}" alt="Satellite"></div>
                                                    <span class="admin-layer-label">Satellite</span>
                                                    <svg class="admin-layer-check" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                </button>
                                                <button type="button" class="admin-layer-card" data-map-type="terrain">
                                                    <div class="admin-layer-preview"><img src="{{ asset('images/map-layers/terrain.png') }}" alt="Terrain"></div>
                                                    <span class="admin-layer-label">Terrain</span>
                                                    <svg class="admin-layer-check" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                </button>
                                                <button type="button" class="admin-layer-card" data-map-type="outdoors">
                                                    <div class="admin-layer-preview"><img src="{{ asset('images/map-layers/outdoor.png') }}" alt="Outdoors"></div>
                                                    <span class="admin-layer-label">Outdoors</span>
                                                    <svg class="admin-layer-check" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 grid grid-cols-2 gap-4 bg-gray-50/50">
                            <div>
                                <label for="latitude" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Latitude</label>
                                <input type="number" name="latitude" id="latitude"
                                       step="0.0000001" value="{{ $existingLat }}" required readonly
                                       class="block w-full rounded-lg border-gray-400 bg-white shadow-sm text-sm font-mono px-3 py-2 @error('latitude') border-red-300 @enderror">
                                @error('latitude')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="longitude" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Longitude</label>
                                <input type="number" name="longitude" id="longitude"
                                       step="0.0000001" value="{{ $existingLng }}" required readonly
                                       class="block w-full rounded-lg border-gray-400 bg-white shadow-sm text-sm font-mono px-3 py-2 @error('longitude') border-red-300 @enderror">
                                @error('longitude')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Separate delete form --}}
    <form action="{{ route('admin.highlights.destroy', $highlight) }}" method="POST" id="delete-highlight-form" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>

{{-- Media preview lightbox --}}
<div id="media-modal" class="hidden fixed inset-0 bg-black bg-opacity-95 z-[9999] flex flex-col items-center justify-center p-4 gap-4">
    <div id="modal-counter" class="absolute top-4 left-1/2 -translate-x-1/2 z-20 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur text-white text-xs font-semibold tracking-wide"></div>
    <button type="button" onclick="closeMediaModal()"
            class="absolute top-4 right-4 z-20 w-10 h-10 rounded-full bg-white/10 hover:bg-white/25 backdrop-blur text-white flex items-center justify-center transition-all">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
    <button id="modal-prev" type="button"
            class="absolute left-4 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-white/10 hover:bg-white/25 backdrop-blur text-white flex items-center justify-center transition-all">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>
    <button id="modal-next" type="button"
            class="absolute right-4 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-white/10 hover:bg-white/25 backdrop-blur text-white flex items-center justify-center transition-all">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
        </svg>
    </button>
    <div id="modal-content" class="max-w-6xl w-full flex items-center justify-center"></div>
    <div id="modal-caption" class="max-w-2xl text-center text-white/80 text-sm leading-relaxed px-2"></div>
</div>

<script>
window._trailMediaLists = window._trailMediaLists || {};
window._trailMediaLists['highlight-{{ $highlight->id }}'] = [
@foreach($highlight->media as $media)
    @php
        if ($media->isPhoto()) {
            $jsType = 'photo';
            $jsUrl = $media->url;
        } else {
            $jsType = 'video';
            $jsUrl = $media->video_url ?? $media->url;
        }
    @endphp
    { type: @json($jsType), url: @json($jsUrl), caption: @json($media->caption ?? $highlight->name) },
@endforeach
];

let _modalState = { listId: null, index: 0 };

function openMediaCarousel(listId, index) {
    const list = window._trailMediaLists[listId];
    if (!list || !list.length) { return; }
    _modalState.listId = listId;
    _modalState.index = Math.max(0, Math.min(index || 0, list.length - 1));
    const modal = document.getElementById('media-modal');
    if (modal.parentElement !== document.body) { document.body.appendChild(modal); }
    modal.classList.remove('hidden');
    _renderModalItem();
}

function _renderModalItem() {
    const { listId, index } = _modalState;
    const list = window._trailMediaLists[listId];
    if (!list) { return; }
    const item = list[index];
    const total = list.length;
    const content = document.getElementById('modal-content');
    const counter = document.getElementById('modal-counter');
    const captionEl = document.getElementById('modal-caption');
    const prevBtn = document.getElementById('modal-prev');
    const nextBtn = document.getElementById('modal-next');

    if (item.type === 'video') {
        const youtubeMatch = (item.url || '').match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/);
        const vimeoMatch = (item.url || '').match(/vimeo\.com\/(\d+)/);
        let embedUrl = '';
        if (youtubeMatch) { embedUrl = `https://www.youtube.com/embed/${youtubeMatch[1]}`; }
        else if (vimeoMatch) { embedUrl = `https://player.vimeo.com/video/${vimeoMatch[1]}`; }

        if (embedUrl) {
            content.innerHTML = `
                <div class="relative w-full max-w-5xl" style="padding-bottom: 56.25%;">
                    <iframe src="${embedUrl}" frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                            class="absolute inset-0 w-full h-full rounded-lg shadow-2xl"></iframe>
                </div>`;
        } else {
            content.innerHTML = `<video src="${item.url}" controls autoplay class="max-w-full max-h-[78vh] rounded-lg shadow-2xl"></video>`;
        }
    } else {
        content.innerHTML = `<img src="${item.url}" alt="" class="max-w-full max-h-[78vh] object-contain rounded-lg shadow-2xl">`;
    }

    counter.textContent = `${index + 1} / ${total}`;
    captionEl.textContent = item.caption || '';

    const showArrows = total > 1;
    prevBtn.style.display = showArrows ? '' : 'none';
    nextBtn.style.display = showArrows ? '' : 'none';
    counter.style.display = showArrows ? '' : 'none';
}

function _modalStep(delta) {
    const list = window._trailMediaLists[_modalState.listId];
    if (!list || !list.length) { return; }
    _modalState.index = (_modalState.index + delta + list.length) % list.length;
    _renderModalItem();
}

function closeMediaModal() {
    const modal = document.getElementById('media-modal');
    const content = document.getElementById('modal-content');
    modal.classList.add('hidden');
    content.innerHTML = ''; // stop video playback
    _modalState.listId = null;
}

document.getElementById('modal-prev')?.addEventListener('click', (e) => { e.stopPropagation(); _modalStep(-1); });
document.getElementById('modal-next')?.addEventListener('click', (e) => { e.stopPropagation(); _modalStep(1); });
document.getElementById('media-modal')?.addEventListener('click', function (e) {
    if (e.target === this) { closeMediaModal(); }
});
document.addEventListener('keydown', function (e) {
    const modal = document.getElementById('media-modal');
    if (!modal || modal.classList.contains('hidden')) { return; }
    if (e.key === 'Escape') { closeMediaModal(); }
    if (e.key === 'ArrowLeft') { _modalStep(-1); }
    if (e.key === 'ArrowRight') { _modalStep(1); }
});

// Load YouTube/Vimeo poster thumbnails for external-video tiles
function getVideoThumbnail(videoUrl) {
    const youtubeMatch = videoUrl.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/);
    if (youtubeMatch) { return `https://img.youtube.com/vi/${youtubeMatch[1]}/hqdefault.jpg`; }
    const vimeoMatch = videoUrl.match(/vimeo\.com\/(\d+)/);
    if (vimeoMatch) { return `https://vumbnail.com/${vimeoMatch[1]}.jpg`; }
    return null;
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.video-thumb[data-video-url]').forEach(function (container) {
        const thumbnailUrl = getVideoThumbnail(container.getAttribute('data-video-url'));
        if (!thumbnailUrl) { return; }
        const img = document.createElement('img');
        img.src = thumbnailUrl;
        img.alt = 'Video thumbnail';
        img.className = 'absolute inset-0 w-full h-full object-cover';
        img.onload = function () {
            const iconDiv = container.querySelector('.video-icon-placeholder');
            if (iconDiv) { iconDiv.remove(); }
            container.prepend(img);
        };
    });
});
</script>

<script src="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.js"></script>
<script>
function highlightIconState(initialCustom, initialResolved) {
    return {
        customIcon: initialCustom || '',
        typeIcon: initialResolved || '📍',
        colorValue: @js($highlight->color),
        get iconPreview() {
            const c = (this.customIcon || '').trim();
            return c || this.typeIcon;
        },
        onTypeChange(select) {
            const opt = select.options[select.selectedIndex];
            this.typeIcon = (opt && opt.value) ? ((opt.text || '').trim().split(' ')[0] || '📍') : '📍';
        },
    };
}

document.addEventListener('DOMContentLoaded', function () {
    mapboxgl.accessToken = '{{ config('services.mapbox.access_token') }}';

    const existingLat = parseFloat(document.getElementById('latitude').value) || 54.7804;
    const existingLng = parseFloat(document.getElementById('longitude').value) || -127.1698;

    window.coordinateMap = new mapboxgl.Map({
        container: 'coordinate-map',
        style: 'mapbox://styles/mapbox/standard',
        center: [existingLng, existingLat],
        zoom: 15,
        attributionControl: false,
    });
    const map = window.coordinateMap;

    map.addControl(new mapboxgl.NavigationControl({ showCompass: false }), 'bottom-right');

    const marker = new mapboxgl.Marker({ draggable: true, color: '#16a34a' })
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

    // Search
    const searchInput = document.getElementById('map-search-input');
    const clearBtn = document.getElementById('clear-search-btn');
    const loadingIndicator = document.getElementById('search-loading');
    const resultsDropdown = document.getElementById('search-results-dropdown');
    let searchTimeout = null;

    searchInput.addEventListener('input', function () {
        const query = this.value.trim();
        if (query.length > 2) {
            clearBtn.classList.remove('hidden');
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => performSearch(query), 500);
        } else {
            clearBtn.classList.add('hidden');
            resultsDropdown.classList.add('hidden');
        }
    });

    clearBtn.addEventListener('click', function () {
        searchInput.value = '';
        clearBtn.classList.add('hidden');
        resultsDropdown.classList.add('hidden');
    });

    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !resultsDropdown.contains(e.target)) {
            resultsDropdown.classList.add('hidden');
        }
    });

    async function performSearch(query) {
        loadingIndicator.classList.remove('hidden');
        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&addressdetails=1`);
            const results = await response.json();
            loadingIndicator.classList.add('hidden');
            if (results.length > 0) {
                resultsDropdown.innerHTML = results.map(r => `
                    <div class="search-result-item p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                         data-lat="${r.lat}" data-lon="${r.lon}" data-name="${r.display_name}">
                        <div class="font-medium text-sm text-gray-900 truncate">${r.display_name}</div>
                        <div class="text-xs text-gray-500 truncate">${r.type || 'Location'}</div>
                    </div>
                `).join('');
                resultsDropdown.classList.remove('hidden');
                resultsDropdown.querySelectorAll('.search-result-item').forEach(item => {
                    item.addEventListener('click', function () {
                        const lat = parseFloat(this.dataset.lat);
                        const lon = parseFloat(this.dataset.lon);
                        marker.setLngLat([lon, lat]);
                        updateCoordinates(lat, lon);
                        map.flyTo({ center: [lon, lat], zoom: 15 });
                        searchInput.value = this.dataset.name;
                        resultsDropdown.classList.add('hidden');
                    });
                });
            } else {
                resultsDropdown.innerHTML = '<div class="p-3 text-sm text-gray-500">No results found</div>';
                resultsDropdown.classList.remove('hidden');
            }
        } catch (error) {
            loadingIndicator.classList.add('hidden');
        }
    }

    // Map style toggle
    var layersToggle = document.getElementById('admin-layers-toggle');
    var layersDropdown = document.getElementById('admin-layers-dropdown');
    layersToggle.addEventListener('click', function (e) {
        e.stopPropagation();
        layersDropdown.classList.toggle('hidden');
    });
    document.addEventListener('click', function (e) {
        if (!layersDropdown.contains(e.target) && !layersToggle.contains(e.target)) {
            layersDropdown.classList.add('hidden');
        }
    });
    document.querySelectorAll('.admin-layer-card').forEach(function (btn) {
        btn.addEventListener('click', function () { switchMapStyle(this.dataset.mapType); });
    });
});

const adminMapStyles = {
    'standard':  'mapbox://styles/mapbox/standard',
    'satellite': 'mapbox://styles/mapbox/satellite-streets-v12',
    'terrain':   'mapbox://styles/mapbox/outdoors-v12',
    'outdoors':  'mapbox://styles/mapbox/navigation-day-v1',
};

function switchMapStyle(mapType) {
    if (!window.coordinateMap) { return; }
    window.coordinateMap.setStyle(adminMapStyles[mapType] || adminMapStyles['standard']);
    document.querySelectorAll('.admin-layer-card').forEach(function (b) {
        b.classList.toggle('active', b.dataset.mapType === mapType);
    });
    document.getElementById('admin-layers-dropdown').classList.add('hidden');
}
</script>

<style>
.admin-layer-card { position: relative; cursor: pointer; border-radius: 0.5rem; overflow: hidden; border: 2px solid transparent; display: flex; flex-direction: column; align-items: center; transition: all 0.2s; background: none; padding: 0; }
.admin-layer-card:hover { border-color: #93C5FD; }
.admin-layer-card.active { border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
.admin-layer-preview { width: 100%; height: 70px; border-radius: 0.375rem; overflow: hidden; }
.admin-layer-preview img { width: 100%; height: 100%; object-fit: cover; display: block; }
.admin-layer-label { display: block; font-size: 0.75rem; font-weight: 500; color: #374151; text-align: center; margin-top: 0.5rem; padding: 0 0.25rem 0.375rem; }
.admin-layer-check { position: absolute; top: 4px; right: 4px; width: 20px; height: 20px; color: white; background: #2563EB; border-radius: 50%; padding: 2px; display: none; }
.admin-layer-card.active .admin-layer-check { display: block; }
</style>
@endsection
