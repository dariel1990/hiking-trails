@extends('layouts.admin')

@section('title', 'Edit Facility')
@section('page-title', 'Edit Facility')

@section('content')

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

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
            <h2 class="text-2xl font-semibold tracking-tight">Edit Facility</h2>
            <p class="text-sm text-muted-foreground">Update facility information and media</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Main Form -->
        <div class="lg:col-span-2 space-y-6">
            <form action="{{ route('admin.facilities.update', $facility) }}" method="POST" enctype="multipart/form-data" id="update-facility-form" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Basic Information Card -->
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                    <div class="flex flex-col space-y-1.5 p-6 border-b">
                        <h3 class="text-lg font-semibold leading-none tracking-tight">Basic Information</h3>
                        <p class="text-sm text-muted-foreground">Update facility details</p>
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
                                <option value="{{ $type }}" {{ old('facility_type', $facility->facility_type) === $type ? 'selected' : '' }}>{{ $label }}</option>
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
                                   value="{{ old('name', $facility->name) }}" 
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
                                      class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">{{ old('description', $facility->description) }}</textarea>
                            @error('description')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Location Card -->
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                    <div class="flex flex-col space-y-1.5 p-6 border-b">
                        <h3 class="text-lg font-semibold leading-none tracking-tight">Location</h3>
                        <p class="text-sm text-muted-foreground">Search for a location or click on the map to update location</p>
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
                                       value="{{ old('latitude', $facility->latitude) }}" 
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
                                       value="{{ old('longitude', $facility->longitude) }}" 
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

                <!-- Media Management Card -->
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                    <div class="flex flex-col space-y-1.5 p-6 border-b">
                        <h3 class="text-lg font-semibold leading-none tracking-tight">Media Gallery</h3>
                        <p class="text-sm text-muted-foreground">Manage photos and videos for this facility</p>
                    </div>
                    <div class="p-6 space-y-6">
                                <!-- Existing Media -->
                        @if($facility->media->count() > 0)
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none">Existing Media</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4" id="existing-media-grid">
                                @foreach($facility->media as $media)
                                <div class="relative group aspect-square rounded-md overflow-hidden border bg-muted" data-media-id="{{ $media->id }}">
                                    @if($media->media_type === 'photo')
                                        @php
                                            $imageUrl = $media->file_path ? asset('storage/' . $media->file_path) : null;
                                        @endphp
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}"
                                                 class="w-full h-full object-cover"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                                 alt="Facility photo">
                                            <div class="hidden w-full h-full items-center justify-center bg-gray-100 text-gray-400">
                                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-400">
                                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    @else
                                        @php
                                            $thumbnailUrl = $media->thumbnail_url;
                                        @endphp
                                        @if($thumbnailUrl)
                                            <div class="relative w-full h-full">
                                                <img src="{{ $thumbnailUrl }}" 
                                                     class="w-full h-full object-cover"
                                                     alt="Video thumbnail">
                                                <div class="absolute inset-0 flex items-center justify-center bg-black/30">
                                                    <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                                    </svg>
                                                </div>
                                            </div>
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-gray-900">
                                                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    @endif
                                    
                                    <!-- Overlay Actions -->
                                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2">
                                        @if(!$media->is_primary)
                                        <button type="button" 
                                                onclick="setPrimaryMedia({{ $facility->id }}, {{ $media->id }}, this)"
                                                class="text-xs bg-white text-gray-900 px-2 py-1 rounded hover:bg-gray-100">
                                            Set Primary
                                        </button>
                                        @else
                                        <span class="text-xs bg-yellow-500 text-white px-2 py-1 rounded primary-badge">Primary</span>
                                        @endif
                                        <button type="button"
                                                onclick="deleteMedia({{ $facility->id }}, {{ $media->id }}, this)"
                                                class="text-xs bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700">
                                            Delete
                                        </button>
                                    </div>
                                    
                                    @if($media->is_primary)
                                    <div class="absolute top-2 right-2 primary-star">
                                        <span class="bg-yellow-500 text-white text-xs px-2 py-0.5 rounded-full">★</span>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Add New Photos -->
                        <div class="space-y-2 pt-4 border-t">
                            <label class="text-sm font-medium leading-none">Add New Photos</label>
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

                        <!-- Add Video URLs -->
                        <div class="space-y-2 pt-4 border-t">
                            <label class="text-sm font-medium leading-none">Add Video Links</label>
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

                <!-- Submit Buttons (Mobile only - inside form) -->
                <div class="flex flex-col gap-2 lg:hidden">
                    <button type="submit"
                            form="update-facility-form"
                            class="inline-flex items-center justify-center rounded-md bg-black text-white hover:bg-black/90 h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 w-full">
                        Update Facility
                    </button>
                    <a href="{{ route('admin.facilities.index') }}" 
                       class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 w-full">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Right Column - Settings and Actions -->
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
                               value="{{ old('icon', $facility->getRawOriginal('icon')) }}"
                               maxlength="10"
                               placeholder="Leave empty for default"
                               form="update-facility-form"
                               class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-muted-foreground">Current: <span id="icon-preview" class="text-base">{{ $facility->icon }}</span></p>
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
                               form="update-facility-form"
                               {{ old('is_active', $facility->is_active) ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-input text-primary focus:ring-ring">
                        <label for="is_active" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Active (visible on map)
                        </label>
                    </div>
                </div>
            </div>

            <!-- Delete Facility - SEPARATE FORM -->
            <div class="rounded-lg border-2 border-red-200 bg-red-50 text-card-foreground shadow-sm">
                <div class="p-6 space-y-4">
                    <div class="flex items-start gap-3">
                        <svg class="h-5 w-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <h3 class="text-sm font-semibold text-red-900">Delete Facility</h3>
                            <p class="text-xs text-red-700 mt-1">Once deleted, this facility cannot be recovered. This action is permanent.</p>
                        </div>
                    </div>
                    <form action="{{ route('admin.facilities.destroy', $facility) }}" method="POST" id="delete-facility-form">
                        @csrf
                        @method('DELETE')
                        <button type="button"
                                onclick="confirmDeleteFacility()"
                                class="inline-flex items-center justify-center rounded-md bg-red-600 text-white hover:bg-red-700 h-11 px-4 py-2 text-sm font-semibold ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 w-full shadow-sm">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete Facility
                        </button>
                    </form>
                </div>
            </div>

            <!-- Submit Buttons (Desktop only - outside form but linked via form attribute) -->
            <div class="hidden lg:flex flex-col gap-2">
                <button type="submit"
                        form="update-facility-form"
                        class="inline-flex items-center justify-center rounded-md bg-black text-white hover:bg-black/90 h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 w-full">
                    Update Facility
                </button>
                <a href="{{ route('admin.facilities.index') }}" 
                   class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 w-full">
                    Cancel
                </a>
            </div>
        </div>
    </div>
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

// Confirm delete facility
function confirmDeleteFacility() {
    showConfirmModal(
        'Delete Facility',
        'Are you sure you want to delete this facility? This action cannot be undone.',
        function() {
            document.getElementById('delete-facility-form').submit();
        }
    );
}

document.addEventListener('DOMContentLoaded', function() {
    // Get existing facility coordinates
    const existingLat = parseFloat(document.getElementById('latitude').value);
    const existingLng = parseFloat(document.getElementById('longitude').value);
    
    // Initialize map centered on facility location
    const map = L.map('coordinate-map').setView([existingLat, existingLng], 15);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 18
    }).addTo(map);
    
    // Add marker at existing facility location (draggable)
    let marker = L.marker([existingLat, existingLng], {
        draggable: true
    }).addTo(map);
    
    marker.on('dragend', function(e) {
        const position = marker.getLatLng();
        updateCoordinates(position.lat, position.lng);
    });
    
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
    const iconInput = document.getElementById('icon');
    const selectedOption = select.options[select.selectedIndex];
    
    // Only update preview if custom icon field is empty
    if (selectedOption.value && !iconInput.value) {
        const icon = selectedOption.text.split(' ')[0];
        iconPreview.textContent = icon;
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

// Set media as primary via AJAX
async function setPrimaryMedia(facilityId, mediaId, button) {
    showConfirmModal('Set Primary Media', 'Set this as the primary media?', async function() {
        try {
            const response = await fetch(`/admin/facilities/${facilityId}/media/${mediaId}/primary`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });
            
            if (response.ok) {
                // Update UI
                const mediaGrid = document.getElementById('existing-media-grid');
                const allContainers = mediaGrid.querySelectorAll('[data-media-id]');
                
                allContainers.forEach(container => {
                    const id = parseInt(container.dataset.mediaId);
                    const overlay = container.querySelector('.absolute.inset-0');
                    const star = container.querySelector('.primary-star');
                    
                    if (id === mediaId) {
                        // This is now primary
                        if (!star) {
                            const newStar = document.createElement('div');
                            newStar.className = 'absolute top-2 right-2 primary-star';
                            newStar.innerHTML = '<span class="bg-yellow-500 text-white text-xs px-2 py-0.5 rounded-full">★</span>';
                            container.appendChild(newStar);
                        }
                        // Change button to "Primary" text
                        const btn = container.querySelector('button[onclick^="setPrimaryMedia"]');
                        if (btn) {
                            const span = document.createElement('span');
                            span.className = 'text-xs bg-yellow-500 text-white px-2 py-1 rounded primary-badge';
                            span.textContent = 'Primary';
                            btn.replaceWith(span);
                        }
                    } else {
                        // Remove primary status
                        if (star) star.remove();
                        // Change back to "Set Primary" button if needed
                        const badge = container.querySelector('.primary-badge');
                        if (badge) {
                            const otherId = container.dataset.mediaId;
                            const newBtn = document.createElement('button');
                            newBtn.type = 'button';
                            newBtn.className = 'text-xs bg-white text-gray-900 px-2 py-1 rounded hover:bg-gray-100';
                            newBtn.textContent = 'Set Primary';
                            newBtn.onclick = function() { setPrimaryMedia(facilityId, parseInt(otherId), this); };
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
    
    try {
        const response = await fetch(`/admin/facilities/${facilityId}/media/${mediaId}/primary`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });
        
        if (response.ok) {
            // Update UI
            const mediaGrid = document.getElementById('existing-media-grid');
            const allContainers = mediaGrid.querySelectorAll('[data-media-id]');
            
            allContainers.forEach(container => {
                const id = parseInt(container.dataset.mediaId);
                const overlay = container.querySelector('.absolute.inset-0');
                const star = container.querySelector('.primary-star');
                
                if (id === mediaId) {
                    // This is now primary
                    if (!star) {
                        const newStar = document.createElement('div');
                        newStar.className = 'absolute top-2 right-2 primary-star';
                        newStar.innerHTML = '<span class="bg-yellow-500 text-white text-xs px-2 py-0.5 rounded-full">★</span>';
                        container.appendChild(newStar);
                    }
                    // Change button to "Primary" text
                    const btn = container.querySelector('button[onclick^="setPrimaryMedia"]');
                    if (btn) {
                        const span = document.createElement('span');
                        span.className = 'text-xs bg-yellow-500 text-white px-2 py-1 rounded primary-badge';
                        span.textContent = 'Primary';
                        btn.replaceWith(span);
                    }
                } else {
                    // Remove primary status
                    if (star) star.remove();
                    // Change back to "Set Primary" button if needed
                    const badge = container.querySelector('.primary-badge');
                    if (badge) {
                        const otherId = container.dataset.mediaId;
                        const newBtn = document.createElement('button');
                        newBtn.type = 'button';
                        newBtn.className = 'text-xs bg-white text-gray-900 px-2 py-1 rounded hover:bg-gray-100';
                        newBtn.textContent = 'Set Primary';
                        newBtn.onclick = function() { setPrimaryMedia(facilityId, parseInt(otherId), this); };
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
}

// Delete media via AJAX
async function deleteMedia(facilityId, mediaId, button) {
    showConfirmModal('Delete Media', 'Delete this media? This action cannot be undone.', async function() {
        try {
            const response = await fetch(`/admin/facilities/${facilityId}/media/${mediaId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });
            
            if (response.ok) {
                // Remove the media item from the grid
                const container = button.closest('[data-media-id]');
                container.style.opacity = '0';
                container.style.transform = 'scale(0.8)';
                setTimeout(() => container.remove(), 300);
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