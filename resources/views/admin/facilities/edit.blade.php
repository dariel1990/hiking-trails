@extends('layouts.admin')

@section('title', 'Edit Facility')
@section('page-title', 'Edit Facility')

@section('content')
<link href="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.css" rel="stylesheet">

@php
    $rawIcon = $facility->getRawOriginal('icon');
    $resolvedIcon = $facility->icon ?: '📍';
@endphp

{{-- Confirmation Modal --}}
<div id="confirm-modal" class="hidden fixed inset-0 bg-black/50 z-[100] flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full border border-gray-200">
        <div class="p-6 space-y-4">
            <div class="space-y-2">
                <h3 id="confirm-modal-title" class="text-lg font-semibold tracking-tight text-gray-900">Are you sure?</h3>
                <p id="confirm-modal-message" class="text-sm text-gray-500">This action cannot be undone.</p>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="closeConfirmModal()"
                        class="bg-white border border-gray-400 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg font-medium text-sm transition-colors">
                    Cancel
                </button>
                <button type="button" id="confirm-modal-action"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition-colors shadow-sm">
                    Continue
                </button>
            </div>
        </div>
    </div>
</div>

<div x-data="facilityIconState('{{ $rawIcon }}', @js($resolvedIcon))" class="px-4 lg:px-8 py-6">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-6 mb-6 border-b border-gray-200">
        <div>
            <nav class="flex items-center gap-2 text-xs text-gray-500 mb-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Admin</a>
                <span>/</span>
                <a href="{{ route('admin.facilities.index') }}" class="hover:text-gray-700">Facilities</a>
                <span>/</span>
                <span class="text-gray-700 font-medium truncate max-w-xs">{{ $facility->name }}</span>
            </nav>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-xl select-none"
                     x-text="iconPreview"></div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">Edit Facility</h1>
                    <p class="text-sm text-gray-500">{{ $facility->name }} · {{ $facility->facility_type_label }}</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.facilities.index') }}"
               class="bg-white border border-gray-400 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg font-medium text-sm transition-colors">
                Cancel
            </a>
            <button type="submit" form="facility-form"
                    class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg font-medium text-sm transition-colors shadow-sm">
                Save Changes
            </button>
        </div>
    </div>

    <form id="facility-form" action="{{ route('admin.facilities.update', $facility) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

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
                            <p class="text-xs text-gray-500">Type, name, description, and map icon</p>
                        </div>
                    </div>
                    <div class="px-6 py-5 space-y-5">
                        <div>
                            <label for="facility_type" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Facility Type <span class="text-red-500">*</span>
                            </label>
                            <select name="facility_type" id="facility_type" required
                                    x-on:change="onTypeChange($event.target)"
                                    class="block w-full rounded-lg border-gray-400 shadow-sm px-4 py-2.5 focus:border-green-500 focus:ring-green-500 @error('facility_type') border-red-300 @enderror">
                                <option value="">Select facility type</option>
                                @foreach(App\Models\Facility::getFacilityTypes() as $type => $label)
                                    <option value="{{ $type }}" {{ old('facility_type', $facility->facility_type) === $type ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('facility_type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Facility Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name"
                                   value="{{ old('name', $facility->name) }}" required
                                   placeholder="e.g., Main Parking Lot"
                                   class="block w-full rounded-lg border-gray-400 shadow-sm px-4 py-2.5 focus:border-green-500 focus:ring-green-500 @error('name') border-red-300 @enderror">
                            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="icon" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Custom Icon <span class="text-gray-400 font-normal text-xs">(Optional)</span>
                                </label>
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 flex-shrink-0 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-xl select-none"
                                         x-text="iconPreview"></div>
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
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Visibility</label>
                                <label for="is_active"
                                       class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100/70 transition-colors h-[calc(100%-1.5rem)]">
                                    <input type="checkbox" name="is_active" id="is_active" value="1"
                                           {{ old('is_active', $facility->is_active) ? 'checked' : '' }}
                                           class="mt-0.5 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-400 rounded">
                                    <div>
                                        <span class="block text-sm font-medium text-gray-700">Active</span>
                                        <span class="block text-xs text-gray-500 mt-0.5">Show this facility on the public map.</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                            <textarea name="description" id="description" rows="4"
                                      placeholder="Describe this facility and what visitors can expect..."
                                      class="block w-full rounded-lg border-gray-400 shadow-sm px-4 py-2.5 focus:border-green-500 focus:ring-green-500 @error('description') border-red-300 @enderror">{{ old('description', $facility->description) }}</textarea>
                            @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                {{-- Media --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-sm font-semibold text-gray-900">Media Gallery</h2>
                            <p class="text-xs text-gray-500">Manage existing photos / videos and add new ones</p>
                        </div>
                        @if($facility->media->count() > 0)
                            <span class="text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded-full">
                                {{ $facility->media->count() }} {{ Str::plural('item', $facility->media->count()) }}
                            </span>
                        @endif
                    </div>
                    <div class="px-6 py-5 space-y-6">

                        {{-- Existing Media --}}
                        @if($facility->media->count() > 0)
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Existing</p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" id="existing-media-grid">
                                @foreach($facility->media as $media)
                                <div class="relative group aspect-square rounded-lg overflow-hidden border border-gray-200 bg-gray-100" data-media-id="{{ $media->id }}">
                                    @if($media->media_type === 'photo')
                                        @php $imageUrl = $media->file_path ? asset('storage/' . $media->file_path) : null; @endphp
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="Facility photo" class="w-full h-full object-cover"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="hidden w-full h-full items-center justify-center bg-gray-100 text-gray-400">
                                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-400">
                                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    @else
                                        @php $thumbnailUrl = $media->thumbnail_url; @endphp
                                        @if($thumbnailUrl)
                                            <div class="relative w-full h-full">
                                                <img src="{{ $thumbnailUrl }}" alt="Video thumbnail" class="w-full h-full object-cover">
                                                <div class="absolute inset-0 flex items-center justify-center bg-black/40">
                                                    <svg class="w-10 h-10 text-white drop-shadow" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                                    </svg>
                                                </div>
                                            </div>
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-gray-900 text-white">
                                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    @endif

                                    {{-- Hover actions --}}
                                    <div class="absolute inset-0 bg-black/55 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2">
                                        @if(!$media->is_primary)
                                        <button type="button"
                                                onclick="setPrimaryMedia({{ $facility->id }}, {{ $media->id }}, this)"
                                                class="text-xs bg-white text-gray-900 px-2.5 py-1 rounded-md hover:bg-gray-50 font-medium">
                                            Set Primary
                                        </button>
                                        @else
                                        <span class="text-xs bg-amber-500 text-white px-2.5 py-1 rounded-md primary-badge font-medium">Primary</span>
                                        @endif
                                        <button type="button"
                                                onclick="deleteMedia({{ $facility->id }}, {{ $media->id }}, this)"
                                                class="text-xs bg-red-600 text-white px-2.5 py-1 rounded-md hover:bg-red-700 font-medium">
                                            Delete
                                        </button>
                                    </div>

                                    @if($media->is_primary)
                                    <div class="absolute top-2 right-2 primary-star">
                                        <span class="bg-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow">★ PRIMARY</span>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Add new photos --}}
                        <div class="@if($facility->media->count() > 0) pt-5 border-t border-gray-100 @endif">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Add Photos</p>
                            <input type="file" id="photos" name="photos[]" multiple accept="image/*"
                                   class="hidden" onchange="handlePhotoSelection(this)">
                            <label for="photos"
                                   class="flex flex-col items-center justify-center gap-2 w-full h-44 rounded-lg border-2 border-dashed border-gray-400 bg-gray-50 hover:bg-gray-100 hover:border-gray-400 cursor-pointer transition-colors">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <div class="text-center">
                                    <p class="text-sm font-medium text-gray-700">Click to upload photos</p>
                                    <p class="text-xs text-gray-500 mt-0.5">Multiple images supported · JPG, PNG, WebP</p>
                                </div>
                            </label>
                            <div id="photo-preview" class="grid grid-cols-3 sm:grid-cols-4 gap-3 mt-4"></div>
                        </div>

                        {{-- Add video URLs --}}
                        <div class="pt-5 border-t border-gray-100">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Add Video Links</p>
                            <div id="video-urls-container" class="space-y-2">
                                <div class="flex gap-2">
                                    <input type="url" name="video_urls[]"
                                           placeholder="https://youtube.com/watch?v=... or https://vimeo.com/..."
                                           class="flex-1 rounded-lg border-gray-400 shadow-sm px-4 py-2.5 text-sm focus:border-green-500 focus:ring-green-500">
                                    <button type="button" onclick="addVideoUrlField()"
                                            class="flex-shrink-0 inline-flex items-center justify-center w-11 h-11 rounded-lg bg-white border border-gray-400 hover:bg-gray-50 text-gray-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">Supports YouTube and Vimeo links.</p>
                        </div>
                    </div>
                </div>

                {{-- Danger Zone --}}
                <div class="bg-red-50/40 rounded-xl border border-red-200">
                    <div class="px-6 py-4 border-b border-red-200/70 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-red-900">Danger Zone</h2>
                            <p class="text-xs text-red-700/80">Permanent actions that cannot be undone</p>
                        </div>
                    </div>
                    <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Delete this facility</p>
                            <p class="text-xs text-gray-600 mt-0.5">All photos, videos, and references on the map will be removed.</p>
                        </div>
                        <button type="button" onclick="confirmDeleteFacility()"
                                class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition-colors shadow-sm flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete Facility
                        </button>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Location --}}
            <div class="xl:col-span-5">
                <div class="xl:sticky xl:top-6">
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
                                <p class="text-xs text-gray-500">Search or click the map to update the position</p>
                            </div>
                            <span class="text-xs text-red-500 font-medium">Required</span>
                        </div>

                        <div class="px-6 py-4 border-b border-gray-100">
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input type="text" id="map-search-input"
                                       placeholder="Search for a place..."
                                       autocomplete="off"
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

                        <div id="coordinate-map" class="w-full h-[420px] border-y border-gray-100 relative z-0"></div>

                        <div class="px-6 py-4 grid grid-cols-2 gap-4 bg-gray-50/50">
                            <div>
                                <label for="latitude" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Latitude</label>
                                <input type="number" name="latitude" id="latitude"
                                       step="0.0000001" value="{{ old('latitude', $facility->latitude) }}"
                                       required readonly
                                       class="block w-full rounded-lg border-gray-400 bg-white shadow-sm text-sm font-mono px-3 py-2 @error('latitude') border-red-300 @enderror">
                                @error('latitude')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="longitude" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Longitude</label>
                                <input type="number" name="longitude" id="longitude"
                                       step="0.0000001" value="{{ old('longitude', $facility->longitude) }}"
                                       required readonly
                                       class="block w-full rounded-lg border-gray-400 bg-white shadow-sm text-sm font-mono px-3 py-2 @error('longitude') border-red-300 @enderror">
                                @error('longitude')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Separate delete form (target of confirmDeleteFacility) --}}
    <form action="{{ route('admin.facilities.destroy', $facility) }}" method="POST" id="delete-facility-form" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>

<script src="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.js"></script>
<script>
function facilityIconState(initialCustom, initialResolved) {
    return {
        customIcon: initialCustom || '',
        typeIcon: initialResolved || '📍',
        get iconPreview() {
            const c = (this.customIcon || '').trim();
            return c || this.typeIcon;
        },
        onTypeChange(select) {
            const opt = select.options[select.selectedIndex];
            if (opt && opt.value) {
                this.typeIcon = (opt.text || '').trim().split(' ')[0] || '📍';
            } else {
                this.typeIcon = '📍';
            }
        },
    };
}

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
document.getElementById('confirm-modal-action').addEventListener('click', function () {
    if (confirmCallback) { confirmCallback(); }
    closeConfirmModal();
});
document.getElementById('confirm-modal').addEventListener('click', function (e) {
    if (e.target === this) { closeConfirmModal(); }
});
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { closeConfirmModal(); }
});

function confirmDeleteFacility() {
    showConfirmModal(
        'Delete Facility',
        'Are you sure you want to delete this facility? All photos, videos, and references on the map will be removed. This cannot be undone.',
        function () { document.getElementById('delete-facility-form').submit(); }
    );
}

document.addEventListener('DOMContentLoaded', function () {
    mapboxgl.accessToken = '{{ config('services.mapbox.access_token') }}';

    const existingLat = parseFloat(document.getElementById('latitude').value) || 54.7804;
    const existingLng = parseFloat(document.getElementById('longitude').value) || -127.1698;

    const map = new mapboxgl.Map({
        container: 'coordinate-map',
        style: 'mapbox://styles/mapbox/streets-v12',
        center: [existingLng, existingLat],
        zoom: 15,
        attributionControl: false,
    });

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
});

function handlePhotoSelection(input) {
    const previewContainer = document.getElementById('photo-preview');
    previewContainer.innerHTML = '';
    if (input.files) {
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const div = document.createElement('div');
                div.className = 'relative aspect-square rounded-lg overflow-hidden border border-gray-200 bg-gray-100';
                div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                previewContainer.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }
}

function addVideoUrlField() {
    const container = document.getElementById('video-urls-container');
    const div = document.createElement('div');
    div.className = 'flex gap-2';
    div.innerHTML = `
        <input type="url" name="video_urls[]"
               placeholder="https://youtube.com/watch?v=... or https://vimeo.com/..."
               class="flex-1 rounded-lg border-gray-400 shadow-sm px-4 py-2.5 text-sm focus:border-green-500 focus:ring-green-500">
        <button type="button" onclick="this.parentElement.remove()"
                class="flex-shrink-0 inline-flex items-center justify-center w-11 h-11 rounded-lg bg-white border border-gray-400 hover:bg-red-50 hover:text-red-600 hover:border-red-200 text-gray-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    `;
    container.appendChild(div);
}

async function setPrimaryMedia(facilityId, mediaId, button) {
    showConfirmModal('Set Primary Media', 'Set this as the primary media for the facility?', async function () {
        try {
            const response = await fetch(`/admin/facilities/${facilityId}/media/${mediaId}/primary`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });
            if (response.ok) {
                const mediaGrid = document.getElementById('existing-media-grid');
                const allContainers = mediaGrid.querySelectorAll('[data-media-id]');
                allContainers.forEach(container => {
                    const id = parseInt(container.dataset.mediaId);
                    const star = container.querySelector('.primary-star');
                    if (id === mediaId) {
                        if (!star) {
                            const newStar = document.createElement('div');
                            newStar.className = 'absolute top-2 right-2 primary-star';
                            newStar.innerHTML = '<span class="bg-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow">★ PRIMARY</span>';
                            container.appendChild(newStar);
                        }
                        const btn = container.querySelector('button[onclick^="setPrimaryMedia"]');
                        if (btn) {
                            const span = document.createElement('span');
                            span.className = 'text-xs bg-amber-500 text-white px-2.5 py-1 rounded-md primary-badge font-medium';
                            span.textContent = 'Primary';
                            btn.replaceWith(span);
                        }
                    } else {
                        if (star) { star.remove(); }
                        const badge = container.querySelector('.primary-badge');
                        if (badge) {
                            const otherId = container.dataset.mediaId;
                            const newBtn = document.createElement('button');
                            newBtn.type = 'button';
                            newBtn.className = 'text-xs bg-white text-gray-900 px-2.5 py-1 rounded-md hover:bg-gray-50 font-medium';
                            newBtn.textContent = 'Set Primary';
                            newBtn.onclick = function () { setPrimaryMedia(facilityId, parseInt(otherId), this); };
                            badge.replaceWith(newBtn);
                        }
                    }
                });
            } else {
                alert('Failed to set primary media');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred');
        }
    });
}

async function deleteMedia(facilityId, mediaId, button) {
    showConfirmModal('Delete Media', 'Delete this media item? This cannot be undone.', async function () {
        try {
            const response = await fetch(`/admin/facilities/${facilityId}/media/${mediaId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });
            if (response.ok) {
                const container = button.closest('[data-media-id]');
                container.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                container.style.opacity = '0';
                container.style.transform = 'scale(0.85)';
                setTimeout(() => container.remove(), 280);
            } else {
                alert('Failed to delete media');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred');
        }
    });
}
</script>

@endsection
