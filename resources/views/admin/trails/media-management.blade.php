@extends('layouts.admin')

@section('title', 'Media Management - ' . $trail->name)
@section('page-title', 'Media Management')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-muted-foreground mb-2">
                <a href="{{ route('admin.trails.index') }}" class="hover:text-foreground">Trails</a>
                <span>/</span>
                <a href="{{ route('admin.trails.show', $trail) }}" class="hover:text-foreground">{{ $trail->name }}</a>
                <span>/</span>
                <span>Media</span>
            </div>
            <h1 class="text-3xl font-bold">Media & Features Management</h1>
            <p class="text-muted-foreground mt-1">Upload and link photos/videos to trail features</p>
        </div>
        <a href="{{ route('admin.trails.show', $trail) }}" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Trail
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg border p-4">
            <div class="text-sm text-muted-foreground">Total Media</div>
            <div class="text-2xl font-bold">{{ $trail->media->count() }}</div>
        </div>
        <div class="bg-white rounded-lg border p-4">
            <div class="text-sm text-muted-foreground">Photos</div>
            <div class="text-2xl font-bold">{{ $trail->photoMedia->count() }}</div>
        </div>
        <div class="bg-white rounded-lg border p-4">
            <div class="text-sm text-muted-foreground">Videos</div>
            <div class="text-2xl font-bold">{{ $trail->videoMedia->count() }}</div>
        </div>
        <div class="bg-white rounded-lg border p-4">
            <div class="text-sm text-muted-foreground">Features</div>
            <div class="text-2xl font-bold">{{ $trail->features->count() }}</div>
        </div>
    </div>

    <!-- Trail Photos Management Section -->
    <div class="bg-white rounded-lg border p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl font-semibold">Trail Photos</h2>
                <p class="text-sm text-muted-foreground">General photos for this trail (not linked to specific features)</p>
            </div>
            <span class="text-sm text-muted-foreground">{{ $trail->generalMedia()->count() }} photos</span>
        </div>

        @php
            $trailPhotos = $trail->generalMedia;
        @endphp

        @if($trailPhotos->count() > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
                @foreach($trailPhotos as $photo)
                    <div class="relative group rounded-lg overflow-hidden border-2 {{ $photo->is_featured ? 'border-yellow-400' : 'border-gray-200' }}" 
                        data-trail-photo-id="{{ $photo->id }}">
                        
                        <!-- Photo -->
                        <div class="aspect-square bg-gray-100">
                            <img src="{{ $photo->url }}" 
                                alt="{{ $photo->caption ?? 'Trail photo' }}" 
                                class="w-full h-full object-cover">
                        </div>
                        
                        <!-- Featured Badge -->
                        @if($photo->is_featured)
                        <div class="absolute top-2 left-2 bg-yellow-400 text-yellow-900 text-xs font-semibold px-2 py-1 rounded shadow-md">
                            ⭐ Featured
                        </div>
                        @endif
                        
                        <!-- Photo Info Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                            <div class="absolute bottom-0 left-0 right-0 p-2">
                                <p class="text-white text-xs font-medium truncate">{{ $photo->original_name }}</p>
                                <p class="text-white/70 text-xs">{{ $photo->formatted_size }}</p>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="absolute top-2 right-2 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <!-- Edit Button -->
                            <button type="button" 
                                    onclick="editTrailPhoto({{ $photo->id }}, '{{ addslashes($photo->caption ?? '') }}')"
                                    class="bg-blue-500 hover:bg-blue-600 text-white rounded-full p-1.5 shadow-lg"
                                    title="Edit caption">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            
                            <!-- Delete Button -->
                            <button type="button" 
                                    onclick="deleteTrailPhoto({{ $photo->id }})"
                                    class="bg-red-500 hover:bg-red-600 text-white rounded-full p-1.5 shadow-lg"
                                    title="Delete photo">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Set as Featured Button -->
                        @if(!$photo->is_featured)
                        <button type="button" 
                                onclick="setTrailPhotoAsFeatured({{ $photo->id }})"
                                class="absolute bottom-2 left-2 right-2 bg-yellow-400 hover:bg-yellow-500 text-yellow-900 text-xs font-semibold px-3 py-1.5 rounded shadow-lg opacity-0 group-hover:opacity-100 transition-all">
                            ⭐ Set as Featured
                        </button>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-muted-foreground border-2 border-dashed rounded-lg">
                <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="mb-2">No trail photos yet</p>
                <p class="text-sm">Photos added during trail creation/editing will appear here</p>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Media Library -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Upload Section -->
            <div class="bg-white rounded-lg border p-6">
                <h2 class="text-xl font-semibold mb-4">Upload Media</h2>
                
                <!-- Upload Tabs -->
                <div class="flex gap-2 mb-4 border-b">
                    <button type="button" onclick="switchUploadType('photo')" 
                            id="tab-photo"
                            class="upload-tab px-4 py-2 font-medium border-b-2 border-primary text-primary">
                        📸 Photos
                    </button>
                    <button type="button" onclick="switchUploadType('video')" 
                            id="tab-video"
                            class="upload-tab px-4 py-2 font-medium border-b-2 border-transparent text-muted-foreground hover:text-foreground">
                        🎥 Upload Video
                    </button>
                    <button type="button" onclick="switchUploadType('video_url')" 
                            id="tab-video_url"
                            class="upload-tab px-4 py-2 font-medium border-b-2 border-transparent text-muted-foreground hover:text-foreground">
                        🔗 Video URL
                    </button>
                </div>

                <!-- Photo Upload -->
                <div id="upload-photo" class="upload-section">
                    <div class="border-2 border-dashed rounded-lg p-8 text-center hover:border-primary transition-colors cursor-pointer" 
                         onclick="document.getElementById('photo-file-input').click()">
                        <input type="file" id="photo-file-input" accept="image/*" multiple class="hidden">
                        <svg class="mx-auto h-12 w-12 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="mt-2 text-sm"><span class="font-semibold text-primary">Click to upload</span> or drag photos</p>
                        <p class="text-xs text-muted-foreground">PNG, JPG, GIF up to 50MB</p>
                    </div>
                </div>

                <!-- Video Upload -->
                <div id="upload-video" class="upload-section hidden">
                    <div class="border-2 border-dashed rounded-lg p-8 text-center hover:border-primary transition-colors cursor-pointer" 
                         onclick="document.getElementById('video-file-input').click()">
                        <input type="file" id="video-file-input" accept="video/*" class="hidden">
                        <svg class="mx-auto h-12 w-12 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <p class="mt-2 text-sm"><span class="font-semibold text-primary">Click to upload</span> or drag video</p>
                        <p class="text-xs text-muted-foreground">MP4, MOV, AVI up to 50MB</p>
                    </div>
                </div>

                <!-- Video URL -->
                <div id="upload-video_url" class="upload-section hidden">
                    <div class="space-y-3">
                        <input type="url" id="video-url-input" placeholder="https://www.youtube.com/watch?v=..." 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <button type="button" onclick="uploadVideoUrl()" 
                                class="w-full btn btn-primary">
                            Add Video URL
                        </button>
                        <p class="text-xs text-muted-foreground">Supports YouTube and Vimeo links</p>
                    </div>
                </div>
            </div>

            <!-- Media Library -->
            <div class="bg-white rounded-lg border p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold">Media Library</h2>
                    <div class="flex gap-2">
                        <button type="button" onclick="filterMedia('all')" class="filter-btn px-3 py-1 text-sm rounded bg-primary text-white" data-filter="all">
                            All ({{ $trail->media->count() }})
                        </button>
                        <button type="button" onclick="filterMedia('photo')" class="filter-btn px-3 py-1 text-sm rounded bg-gray-100" data-filter="photo">
                            Photos ({{ $trail->photoMedia->count() }})
                        </button>
                        <button type="button" onclick="filterMedia('video')" class="filter-btn px-3 py-1 text-sm rounded bg-gray-100" data-filter="video">
                            Videos ({{ $trail->videoMedia->count() }})
                        </button>
                        <button type="button" onclick="filterMedia('unlinked')" class="filter-btn px-3 py-1 text-sm rounded bg-gray-100" data-filter="unlinked">
                            Unlinked ({{ $trail->media->filter(fn($m) => $m->features->isEmpty())->count() }})
                        </button>
                    </div>
                </div>

                <div id="media-grid" class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @forelse($trail->media as $media)
                        <div class="media-item group relative border rounded-lg overflow-hidden hover:shadow-lg transition-shadow cursor-pointer"
                             data-media-id="{{ $media->id }}"
                             data-media-type="{{ $media->media_type }}"
                             data-is-linked="{{ $media->features->isNotEmpty() ? 'true' : 'false' }}"
                             onclick="selectMedia({{ $media->id }})">
                            
                            <!-- Media Preview -->
                            <div class="aspect-square bg-gray-100 relative">
                                @if($media->isPhoto())
                                    <img src="{{ $media->url }}" alt="{{ $media->caption ?? 'Trail photo' }}" 
                                         class="w-full h-full object-cover">
                                @elseif($media->isExternal())
                                    <div class="w-full h-full flex items-center justify-center bg-gray-900 text-white">
                                        <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M10 16.5l6-4.5-6-4.5v9zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gray-800 text-white">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif

                                <!-- Linked Badge -->
                                @if($media->features->isNotEmpty())
                                    <div class="absolute top-2 right-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full">
                                        🔗 {{ $media->features->count() }}
                                    </div>
                                @endif

                                <!-- Type Badge -->
                                <div class="absolute top-2 left-2 bg-black/70 text-white text-xs px-2 py-1 rounded">
                                    @if($media->isPhoto()) 📸 Photo
                                    @elseif($media->isExternal()) 🔗 {{ ucfirst($media->video_provider) }}
                                    @else 🎥 Video
                                    @endif
                                </div>
                            </div>

                            <!-- Media Info -->
                            <div class="p-3">
                                <p class="text-sm font-medium truncate">{{ $media->caption ?? 'Untitled' }}</p>
                                @if($media->features->isNotEmpty())
                                    <p class="text-xs text-muted-foreground">
                                        Linked to: {{ $media->features->pluck('name')->join(', ') }}
                                    </p>
                                @endif
                            </div>

                            <!-- Hover Actions -->
                            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                <button onclick="event.stopPropagation(); editMedia({{ $media->id }})" 
                                        class="p-2 bg-white rounded-full hover:bg-gray-100" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button onclick="event.stopPropagation(); deleteMedia({{ $media->id }})" 
                                        class="p-2 bg-red-500 text-white rounded-full hover:bg-red-600" title="Delete">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-12 text-muted-foreground">
                            <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p>No media uploaded yet</p>
                            <p class="text-sm">Upload photos or videos to get started</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Column: Features -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg border p-6 sticky top-6">
                <h2 class="text-xl font-semibold mb-4">Trail Features</h2>
                
                <div id="selected-media-info" class="hidden mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <p class="font-medium text-blue-900">Selected Media</p>
                        <button onclick="deselectMedia()" class="text-blue-600 hover:text-blue-800 text-sm">Clear</button>
                    </div>
                    <div id="selected-media-preview"></div>
                    <p class="text-sm text-blue-700 mt-2">Click a feature below to link</p>
                </div>

                <div class="space-y-3" id="features-list">
                    @forelse($trail->features as $feature)
                        <div class="border rounded-lg p-4 hover:shadow-md transition-shadow feature-item"
                             data-feature-id="{{ $feature->id }}">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-2xl">{{ $feature->icon }}</span>
                                        <h3 class="font-semibold">{{ $feature->name }}</h3>
                                    </div>
                                    <p class="text-xs text-muted-foreground mb-2">{{ ucfirst($feature->feature_type) }}</p>
                                    
                                    <!-- Media Count -->
                                    <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                        <span>📸 {{ $feature->photos()->count() }}/10</span>
                                        <span>🎥 {{ $feature->videos()->count() }}/1</span>
                                    </div>
                                </div>
                                <button onclick="viewFeatureMedia({{ $feature->id }})" 
                                        class="text-primary hover:text-primary/80 text-sm">
                                    View
                                </button>
                            </div>

                            <!-- Link Button (shown when media is selected) -->
                            <button onclick="linkToFeature({{ $feature->id }})" 
                                    class="link-btn hidden w-full mt-3 btn btn-sm btn-primary"
                                    data-feature-id="{{ $feature->id }}">
                                🔗 Link Selected Media
                            </button>

                            <!-- Linked Media Preview -->
                            @if($feature->media->isNotEmpty())
                                <div class="mt-3 flex gap-1 flex-wrap">
                                    @foreach($feature->media->take(4) as $linkedMedia)
                                        <div class="w-12 h-12 rounded border overflow-hidden relative group">
                                            @if($linkedMedia->isPhoto())
                                                <img src="{{ $linkedMedia->url }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full bg-gray-800 flex items-center justify-center text-white text-xs">
                                                    🎥
                                                </div>
                                            @endif
                                            <button onclick="event.stopPropagation(); unlinkFromFeature({{ $linkedMedia->id }}, {{ $feature->id }})" 
                                                    class="absolute inset-0 bg-red-500/90 text-white opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center"
                                                    title="Unlink">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    @endforeach
                                    @if($feature->media->count() > 4)
                                        <div class="w-12 h-12 rounded border bg-gray-100 flex items-center justify-center text-xs font-medium text-muted-foreground">
                                            +{{ $feature->media->count() - 4 }}
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-8 text-muted-foreground">
                            <p class="mb-2">No features added yet</p>
                            <a href="{{ route('admin.trails.edit', $trail) }}" class="text-sm text-primary hover:underline">
                                Add features to the trail
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Media Modal (Simple) -->
<div id="edit-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h3 class="text-lg font-semibold mb-4">Edit Media</h3>
        <form id="edit-form" onsubmit="return updateMedia(event)">
            <input type="hidden" id="edit-media-id">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Caption</label>
                    <input type="text" id="edit-caption" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Description</label>
                    <textarea id="edit-description" rows="3" class="w-full px-3 py-2 border rounded-lg"></textarea>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 btn btn-primary">Save</button>
                    <button type="button" onclick="closeEditModal()" class="flex-1 btn btn-secondary">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const trailId = {{ $trail->id }};
    let selectedMediaId = null;
    let selectedMediaType = null;

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        console.error('CSRF token not found! Add <meta name="csrf-token"> to layout.');
    }

    // Upload Type Switcher
    function switchUploadType(type) {
        document.querySelectorAll('.upload-section').forEach(el => el.classList.add('hidden'));
        document.getElementById(`upload-${type}`).classList.remove('hidden');
        
        document.querySelectorAll('.upload-tab').forEach(tab => {
            tab.classList.remove('border-primary', 'text-primary');
            tab.classList.add('border-transparent', 'text-muted-foreground');
        });
        document.getElementById(`tab-${type}`).classList.remove('border-transparent', 'text-muted-foreground');
        document.getElementById(`tab-${type}`).classList.add('border-primary', 'text-primary');
    }

    // Photo Upload Handler
    document.getElementById('photo-file-input')?.addEventListener('change', function(e) {
        const files = e.target.files;
        if (files.length > 0) {
            console.log('Files selected:', files.length);
            uploadFiles(files, 'photo');
        }
    });

    // Video Upload Handler
    document.getElementById('video-file-input')?.addEventListener('change', function(e) {
        const files = e.target.files;
        if (files.length > 0) {
            console.log('Video selected:', files[0].name);
            uploadFiles(files, 'video');
        }
    });

    // Upload Files Function
    async function uploadFiles(files, type) {
        console.log('Starting upload...', type, files.length, 'files');
        
        if (!csrfToken) {
            alert('CSRF token missing. Please refresh the page.');
            return;
        }

        for (const file of files) {
            // Validate file size (50MB max)
            if (file.size > 50 * 1024 * 1024) {
                alert(`${file.name} is too large. Maximum size is 50MB.`);
                continue;
            }

            // Validate file type
            if (type === 'photo' && !file.type.startsWith('image/')) {
                alert(`${file.name} is not an image file.`);
                continue;
            }
            if (type === 'video' && !file.type.startsWith('video/')) {
                alert(`${file.name} is not a video file.`);
                continue;
            }

            const formData = new FormData();
            formData.append('media_type', type);
            formData.append('file', file);
            
            console.log('Uploading:', file.name, 'Type:', type);
            console.log('Upload URL:', `/admin/trails/${trailId}/media/upload`);

            try {
                const response = await fetch(`/admin/trails/${trailId}/media/upload`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken.content,
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                console.log('Response status:', response.status);
                const data = await response.json();
                console.log('Response data:', data);
                
                if (response.ok && data.success) {
                    showNotification('Upload successful!', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showNotification(data.message || 'Upload failed', 'error');
                    if (data.errors) {
                        console.error('Validation errors:', data.errors);
                        Object.values(data.errors).forEach(errors => {
                            errors.forEach(error => alert(error));
                        });
                    }
                }
            } catch (error) {
                console.error('Upload error:', error);
                showNotification('Upload failed: ' + error.message, 'error');
            }
        }
    }

    // Upload Video URL
    async function uploadVideoUrl() {
        const url = document.getElementById('video-url-input').value.trim();
        if (!url) {
            alert('Please enter a video URL');
            return;
        }

        if (!csrfToken) {
            alert('CSRF token missing. Please refresh the page.');
            return;
        }

        console.log('Uploading video URL:', url);

        const formData = new FormData();
        formData.append('media_type', 'video_url');
        formData.append('video_url', url);

        try {
            const response = await fetch(`/admin/trails/${trailId}/media/upload`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken.content,
                    'Accept': 'application/json',
                },
                body: formData
            });

            console.log('Response status:', response.status);
            const data = await response.json();
            console.log('Response data:', data);
            
            if (response.ok && data.success) {
                showNotification('Video URL added successfully!', 'success');
                document.getElementById('video-url-input').value = '';
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showNotification(data.message || 'Failed to add video URL', 'error');
                if (data.errors) {
                    console.error('Validation errors:', data.errors);
                    Object.values(data.errors).forEach(errors => {
                        errors.forEach(error => alert(error));
                    });
                }
            }
        } catch (error) {
            console.error('Upload error:', error);
            showNotification('Failed to add video URL: ' + error.message, 'error');
        }
    }

    // Filter Media
    function filterMedia(type) {
        const mediaItems = document.querySelectorAll('.media-item');
        
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('bg-primary', 'text-white');
            btn.classList.add('bg-gray-100');
        });
        document.querySelector(`[data-filter="${type}"]`).classList.add('bg-primary', 'text-white');
        document.querySelector(`[data-filter="${type}"]`).classList.remove('bg-gray-100');

        mediaItems.forEach(item => {
            const mediaType = item.dataset.mediaType;
            const isLinked = item.dataset.isLinked === 'true';

            if (type === 'all') {
                item.classList.remove('hidden');
            } else if (type === 'unlinked') {
                item.classList.toggle('hidden', isLinked);
            } else if (type === 'video') {
                item.classList.toggle('hidden', !mediaType.includes('video'));
            } else {
                item.classList.toggle('hidden', mediaType !== type);
            }
        });
    }

    // Select Media
    function selectMedia(mediaId) {
        selectedMediaId = mediaId;
        const mediaItem = document.querySelector(`[data-media-id="${mediaId}"]`);
        selectedMediaType = mediaItem.dataset.mediaType;

        document.querySelectorAll('.media-item').forEach(item => {
            item.classList.remove('ring-4', 'ring-primary');
        });
        mediaItem.classList.add('ring-4', 'ring-primary');

        const infoPanel = document.getElementById('selected-media-info');
        infoPanel.classList.remove('hidden');

        document.querySelectorAll('.link-btn').forEach(btn => btn.classList.remove('hidden'));
    }

    // Deselect Media
    function deselectMedia() {
        selectedMediaId = null;
        selectedMediaType = null;
        document.querySelectorAll('.media-item').forEach(item => {
            item.classList.remove('ring-4', 'ring-primary');
        });
        document.getElementById('selected-media-info').classList.add('hidden');
        document.querySelectorAll('.link-btn').forEach(btn => btn.classList.add('hidden'));
    }

    // Link to Feature
    async function linkToFeature(featureId) {
        if (!selectedMediaId) {
            showNotification('Please select a media item first', 'error');
            return;
        }

        if (!csrfToken) {
            alert('CSRF token missing. Please refresh the page.');
            return;
        }

        console.log('Linking media', selectedMediaId, 'to feature', featureId);

        try {
            const response = await fetch(`/admin/trails/${trailId}/media/${selectedMediaId}/link/${featureId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken.content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();
            
            if (response.ok && data.success) {
                showNotification('Media linked successfully!', 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showNotification(data.message || 'Failed to link media', 'error');
            }
        } catch (error) {
            console.error('Link error:', error);
            showNotification('Failed to link media: ' + error.message, 'error');
        }
    }

    // Unlink from Feature
    async function unlinkFromFeature(mediaId, featureId) {
        if (!confirm('Unlink this media from the feature?')) return;

        if (!csrfToken) {
            alert('CSRF token missing. Please refresh the page.');
            return;
        }

        console.log('Unlinking media', mediaId, 'from feature', featureId);

        try {
            const response = await fetch(`/admin/trails/${trailId}/media/${mediaId}/link/${featureId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken.content,
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();
            
            if (response.ok && data.success) {
                showNotification('Media unlinked successfully!', 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showNotification('Failed to unlink media', 'error');
            }
        } catch (error) {
            console.error('Unlink error:', error);
            showNotification('Failed to unlink media: ' + error.message, 'error');
        }
    }

    // Edit Media
    function editMedia(mediaId) {
        const mediaItem = document.querySelector(`[data-media-id="${mediaId}"]`);
        const caption = mediaItem.querySelector('.text-sm.font-medium')?.textContent || '';
        
        document.getElementById('edit-media-id').value = mediaId;
        document.getElementById('edit-caption').value = caption;
        document.getElementById('edit-modal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
    }

    async function updateMedia(e) {
        e.preventDefault();
        
        if (!csrfToken) {
            alert('CSRF token missing. Please refresh the page.');
            return false;
        }
        
        const mediaId = document.getElementById('edit-media-id').value;
        const caption = document.getElementById('edit-caption').value;
        const description = document.getElementById('edit-description').value;

        try {
            const response = await fetch(`/admin/trails/${trailId}/media/${mediaId}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': csrfToken.content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ caption, description })
            });

            const data = await response.json();
            
            if (response.ok && data.success) {
                showNotification('Media updated!', 'success');
                closeEditModal();
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showNotification('Update failed', 'error');
            }
        } catch (error) {
            console.error('Update error:', error);
            showNotification('Update failed: ' + error.message, 'error');
        }

        return false;
    }

    // Delete Media
    async function deleteMedia(mediaId) {
        if (!confirm('Delete this media? This action cannot be undone.')) return;

        if (!csrfToken) {
            alert('CSRF token missing. Please refresh the page.');
            return;
        }

        try {
            const response = await fetch(`/admin/trails/${trailId}/media/${mediaId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken.content,
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();
            
            if (response.ok && data.success) {
                showNotification('Media deleted!', 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showNotification('Delete failed', 'error');
            }
        } catch (error) {
            console.error('Delete error:', error);
            showNotification('Delete failed: ' + error.message, 'error');
        }
    }

    // Notification Helper
    function showNotification(message, type = 'info') {
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            info: 'bg-blue-500'
        };
        
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transition = 'opacity 0.3s';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // View Feature Media (placeholder)
    function viewFeatureMedia(featureId) {
        console.log('View media for feature:', featureId);
        // Future enhancement: show modal with all media for this feature
    }

    // Log initialization
    console.log('Media management initialized for trail:', trailId);
    console.log('CSRF token present:', !!csrfToken);

    // Trail Photo Management Functions
    async function editTrailPhoto(photoId, currentCaption) {
        const newCaption = prompt('Edit photo caption:', currentCaption);
        
        if (newCaption === null) return; // User cancelled
        
        try {
            const response = await fetch(`/admin/trails/${trailId}/media/${photoId}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': csrfToken.content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    caption: newCaption
                })
            });

            const data = await response.json();
            
            if (response.ok && data.success) {
                showNotification('Caption updated!', 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showNotification('Update failed', 'error');
            }
        } catch (error) {
            console.error('Edit error:', error);
            showNotification('Update failed: ' + error.message, 'error');
        }
    }

    async function deleteTrailPhoto(photoId) {
        if (!confirm('Delete this trail photo? This cannot be undone.')) return;
        
        try {
            const response = await fetch(`/admin/trails/${trailId}/media/${photoId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken.content,
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();
            
            if (response.ok && data.success) {
                showNotification('Photo deleted!', 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showNotification('Delete failed', 'error');
            }
        } catch (error) {
            console.error('Delete error:', error);
            showNotification('Delete failed: ' + error.message, 'error');
        }
    }

    async function setTrailPhotoAsFeatured(photoId) {
        try {
            const response = await fetch(`/admin/trails/${trailId}/media/${photoId}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': csrfToken.content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    is_featured: true
                })
            });

            const data = await response.json();
            
            if (response.ok && data.success) {
                showNotification('Featured photo updated!', 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showNotification('Update failed', 'error');
            }
        } catch (error) {
            console.error('Featured update error:', error);
            showNotification('Update failed: ' + error.message, 'error');
        }
    }
</script>
@endpush
@endsection