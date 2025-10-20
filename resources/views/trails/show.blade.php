@extends('layouts.public')

@section('title', $trail->name . ' - Trail Finder')

@push('styles')
<link href="https://cesium.com/downloads/cesiumjs/releases/1.95/Build/Cesium/Widgets/widgets.css" rel="stylesheet">
<style>
    /* Sticky Sidebar */
    .sticky-sidebar {
        position: sticky;
        top: 100px;
        align-self: flex-start;
    }
    
    /* Enhanced Typography */
    .prose h2 {
        font-size: 1.875rem;
        font-weight: 700;
        margin-top: 2rem;
        margin-bottom: 1rem;
        color: #1f2937;
    }
    
    .prose p {
        font-size: 1.125rem;
        line-height: 1.8;
        color: #374151;
        margin-bottom: 1.5rem;
    }
    
    /* Tab Navigation */
    .tab-nav {
        border-bottom: 2px solid #e5e7eb;
        margin-bottom: 2rem;
    }
    
    .tab-button {
        padding: 1rem 1.5rem;
        font-weight: 600;
        color: #6b7280;
        border-bottom: 3px solid transparent;
        transition: all 0.2s;
        cursor: pointer;
    }
    
    .tab-button:hover {
        color: #10b981;
    }
    
    .tab-button.active {
        color: #10b981;
        border-bottom-color: #10b981;
    }
    
    .tab-content {
        display: none;
    }
    
    .tab-content.active {
        display: block;
        animation: fadeIn 0.3s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Photo Gallery Grid */
    .photo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1rem;
    }
    
    .photo-grid-item {
        position: relative;
        aspect-ratio: 4/3;
        overflow: hidden;
        border-radius: 0.75rem;
        cursor: pointer;
        transition: transform 0.3s ease;
    }
    
    .photo-grid-item:hover {
        transform: scale(1.02);
    }
    
    .photo-grid-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    /* Lightbox */
    .lightbox {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.95);
        padding: 2rem;
    }
    
    .lightbox.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .lightbox-content {
        max-width: 90vw;
        max-height: 90vh;
        position: relative;
    }
    
    .lightbox-content img {
        max-width: 100%;
        max-height: 90vh;
        object-fit: contain;
    }
    
    .lightbox-close {
        position: fixed;
        top: 20px;
        right: 20px;
        color: white;
        font-size: 2rem;
        cursor: pointer;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, 0.5);
        border-radius: 50%;
        transition: all 0.2s ease;
    }

    .lightbox-close:hover {
        background: rgba(0, 0, 0, 0.8);
        transform: scale(1.1);
    }
    
    /* Stats Cards Enhancement */
    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    
    /* Map Container */
    #trail-detail-map, #trail-3d-viewer {
        border-radius: 1rem;
        overflow: hidden;
    }
    
    /* Elevation Profile Chart */
    #elevation-chart {
        height: 250px;
    }
    
    /* Print Styles */
    @media print {
        .sticky-sidebar, .tab-nav, #trail-detail-map, #trail-3d-viewer {
            display: none;
        }
    }

    #trail-3d-viewer:fullscreen,
    #trail-3d-viewer:-webkit-full-screen {
        width: 100vw !important;
        height: 100vh !important;
    }

    .cesium-button svg {
        display: block;
        margin: auto;
    }

    .cesium-toolbar-button {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<div class="relative h-[60vh] bg-gray-900 overflow-hidden">
    @if($trail->media && $trail->media->count() > 0)
        <img src="{{ $trail->media->first()->url }}" 
             alt="{{ $trail->name }}" 
             class="w-full h-full object-cover">
    @else
        <div class="relative min-h-screen flex items-center justify-center hero-gradient overflow-hidden">
            <div id="hero-map" class="absolute inset-0 z-10 opacity-40"></div>
            <!-- Animated Background Pattern -->
            <div class="absolute inset-0 bg-pattern-mountains z-10"></div>
            
            <!-- Enhanced Overlay for better text visibility -->
            <div class="absolute inset-0 bg-black bg-opacity-50 z-10"></div>
        </div>
    @endif
    
    <!-- Gradient Overlay -->
    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>
    
    <!-- Hero Content -->
    <div class="absolute inset-0 flex items-end z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12 w-full">
            <!-- Breadcrumb -->
            <nav class="mb-6">
                <a href="{{ route('trails.index') }}" 
                   class="inline-flex items-center text-white/90 hover:text-white text-sm font-medium transition-colors group">
                    <svg class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Back to Trails
                </a>
            </nav>
            
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                <div class="flex-1">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-4 leading-tight">
                        {{ $trail->name }}
                    </h1>
                    
                    @if($trail->location)
                        <p class="text-xl sm:text-2xl text-emerald-200 flex items-center mb-4">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                            {{ $trail->location }}
                        </p>
                    @endif
                    
                    <!-- Quick Stats Inline -->
                    <div class="flex flex-wrap gap-4 text-white/90">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                            <span class="font-semibold">{{ $trail->distance_km }} km</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                            </svg>
                            <span class="font-semibold">{{ $trail->elevation_gain_m }}m gain</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="font-semibold">{{ $trail->estimated_time_hours }} hours</span>
                        </div>
                    </div>
                </div>
                
                <!-- Difficulty Badge -->
                <div class="inline-flex items-center gap-3">
                    @if($trail->is_featured)
                        <span class="px-4 py-2 bg-amber-400 text-amber-900 rounded-full font-bold text-sm">
                            Featured Trail
                        </span>
                    @endif
                    
                    <span class="px-6 py-3 bg-white/95 backdrop-blur text-emerald-600 rounded-full font-bold text-lg">
                        Level {{ $trail->difficulty_level }}/5
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-12 -mt-20 relative z-10">
            <div class="stat-card text-center">
                <div class="text-4xl font-bold text-emerald-600 mb-2">{{ $trail->difficulty_level }}</div>
                <div class="text-sm font-medium text-gray-600">Difficulty</div>
                <div class="text-xs text-gray-500 mt-1">{{ $trail->difficulty_text }}</div>
            </div>
            <div class="stat-card text-center">
                <div class="text-4xl font-bold text-blue-600 mb-2">{{ $trail->distance_km }}</div>
                <div class="text-sm font-medium text-gray-600">Distance (km)</div>
                <div class="text-xs text-gray-500 mt-1">Total Length</div>
            </div>
            <div class="stat-card text-center">
                <div class="text-4xl font-bold text-orange-600 mb-2">{{ $trail->elevation_gain_m }}</div>
                <div class="text-sm font-medium text-gray-600">Elevation (m)</div>
                <div class="text-xs text-gray-500 mt-1">Total Gain</div>
            </div>
            <div class="stat-card text-center">
                <div class="text-4xl font-bold text-purple-600 mb-2">{{ $trail->estimated_time_hours }}</div>
                <div class="text-sm font-medium text-gray-600">Time (hrs)</div>
                <div class="text-xs text-gray-500 mt-1">Estimated</div>
            </div>
        </div>
        
        <!-- Two Column Layout -->
        <div class="lg:grid lg:grid-cols-12 lg:gap-12">
            
            <!-- Main Content Area -->
            <div class="lg:col-span-8">
                
                <!-- Tab Navigation -->
                <div class="tab-nav flex space-x-1 overflow-x-auto">
                    <button class="tab-button active" data-tab="overview">Overview</button>
                    <button class="tab-button" data-tab="route">Route & Map</button>
                    <button class="tab-button" data-tab="photos">Photos</button>
                    <button class="tab-button" data-tab="planning">Planning</button>
                </div>
                
                <!-- Overview Tab -->
                <div id="overview-tab" class="tab-content active">
                    <div class="prose prose-lg max-w-none">
                        <h2 class="text-forest-700">About This Trail</h2>
                        <p class="lead text-xl text-gray-700 leading-relaxed">{{ $trail->description }}</p>
                        
                        <!-- Ethical Tourism Callout -->
                        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-6 rounded-r-lg my-8">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-emerald-600 mt-1 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                                <div>
                                    <h3 class="font-bold text-emerald-900 mb-2">Responsible Adventure</h3>
                                    <p class="text-emerald-800 mb-0">This trail supports sustainable tourism and local communities. Please follow Leave No Trace principles and respect the natural environment.</p>
                                </div>
                            </div>
                        </div>
                        
                        @if($trail->highlights && $trail->highlights->count() > 0)
                        <h2 class="text-forest-700">Trail Highlights</h2>
                        <div class="grid sm:grid-cols-2 gap-4 not-prose">
                            @foreach($trail->highlights as $highlight)
                            <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors cursor-pointer"
                                 onclick="focusFeature({{ json_encode($highlight->coordinates) }}, '{{ $highlight->name }}')">
                                <div class="flex items-start">
                                    <div class="text-3xl mr-3">{{ $highlight->icon }}</div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900">{{ $highlight->name }}</h4>
                                        <p class="text-sm text-gray-600 capitalize">{{ str_replace('_', ' ', $highlight->feature_type) }}</p>
                                        @if($highlight->description)
                                            <p class="text-sm text-gray-700 mt-1">{{ $highlight->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                
                <!-- Route & Map Tab -->
                <div id="route-tab" class="tab-content">
                    <div class="mb-8">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-3xl font-bold text-forest-700">Interactive Trail Map</h2>
                            <div class="flex space-x-2">
                                <button id="view-2d-btn" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">
                                    2D Map
                                </button>
                                <button id="view-3d-btn" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors">
                                    3D Terrain
                                </button>
                            </div>
                        </div>
                        
                        <!-- 2D Map -->
                        <div id="trail-detail-map" class="w-full shadow-lg z-10" style="height: 500px;"></div>
                        
                        <!-- 3D Viewer -->
                        <div id="trail-3d-viewer" class="w-full h-[500px] shadow-lg hidden"></div>
                        
                        <div class="mt-4 flex justify-between items-center">
                            <button id="fit-route-btn" class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors font-medium">
                                <span id="fit-btn-text">Center Route</span>
                            </button>
                            
                            <div id="3d-loading" class="hidden flex items-center text-gray-600">
                                <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Loading 3D terrain...
                            </div>
                            
                            @if($trail->route_coordinates)
                                <span class="text-sm text-green-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    3D terrain available
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Elevation Profile -->
                    <div class="mt-8">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-2xl font-bold text-gray-900">Elevation Profile</h3>
                            <button type="button" id="load-elevation" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                Refresh Profile
                            </button>
                        </div>
                        <div id="elevation-chart" class="w-full h-48 bg-gray-50 rounded-lg border hidden">
                            <canvas id="elevation-canvas" class="w-full h-full"></canvas>
                        </div>
                        <div id="elevation-stats" class="hidden text-sm text-gray-600 grid grid-cols-4 gap-3 mt-3">
                            <div class="text-center">
                                <div class="font-bold text-lg">-</div>
                                <div class="text-xs">Max Elevation</div>
                            </div>
                            <div class="text-center">
                                <div class="font-bold text-lg">-</div>
                                <div class="text-xs">Min Elevation</div>
                            </div>
                            <div class="text-center">
                                <div class="font-bold text-lg" id="elevation-gain-display">-</div>
                                <div class="text-xs">Elevation Gain</div>
                            </div>
                            <div class="text-center">
                                <div class="font-bold text-lg">-</div>
                                <div class="text-xs">Elevation Loss</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Photos Tab -->
                <div id="photos-tab" class="tab-content">
                    @if($trail->media && $trail->media->count() > 0)
                        <h2 class="text-3xl font-bold text-gray-900 mb-6">Trail Photos</h2>
                        <div class="photo-grid">
                            @foreach($trail->media as $photo)
                            <div class="photo-grid-item" onclick="openLightbox({{ $loop->index }})">
                                <img src="{{ $photo->url }}" alt="{{ $photo->caption ?: $trail->name }}" loading="lazy">
                                @if($photo->caption)
                                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-3">
                                        <p class="text-white text-sm">{{ $photo->caption }}</p>
                                    </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-gray-600">No photos available yet</p>
                        </div>
                    @endif
                </div>
                
                <!-- Planning Tab -->
                <div id="planning-tab" class="tab-content">
                    <div class="prose prose-lg max-w-none">
                        <h2 class="text-forest-700">Trip Planning Information</h2>
                        
                        @if($trail->directions)
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-r-lg mb-6">
                            <h3 class="flex items-center text-blue-900 mb-3">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10.293 15.707a1 1 0 010-1.414L14.586 10l-4.293-4.293a1 1 0 111.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                                Getting There
                            </h3>
                            <p class="text-blue-900 mb-0">{{ $trail->directions }}</p>
                        </div>
                        @endif

                        @if($trail->parking_info)
                        <div class="bg-green-50 border-l-4 border-green-500 p-6 rounded-r-lg mb-6">
                            <h3 class="flex items-center text-green-900 mb-3">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                    <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V8a1 1 0 00-1-1h-3z"/>
                                </svg>
                                Parking
                            </h3>
                            <p class="text-green-900 mb-0">{{ $trail->parking_info }}</p>
                        </div>
                        @endif

                        @if($trail->safety_notes)
                        <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-r-lg mb-6">
                            <h3 class="flex items-center text-red-900 mb-3">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                Safety & Preparation
                            </h3>
                            <p class="text-red-900 mb-0">{{ $trail->safety_notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                
            </div>
            
            <!-- Sidebar -->
            <aside class="lg:col-span-4 mt-12 lg:mt-0">
                <div class="sticky-sidebar space-y-6">
                    
                    <!-- Trail Details Card -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                        <h3 class="text-xl font-bold text-forest-700 mb-4">Trail Details</h3>
                        
                        <dl class="space-y-3">
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <dt class="text-gray-600 font-medium">Type</dt>
                                <dd class="font-semibold capitalize text-emerald-600">
                                    {{ str_replace('-', ' ', $trail->trail_type) }}
                                </dd>
                            </div>
                            
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <dt class="text-gray-600 font-medium">Status</dt>
                                <dd>
                                    @if($trail->status === 'active')
                                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-semibold">Open</span>
                                    @elseif($trail->status === 'seasonal')
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-semibold">Seasonal</span>
                                    @else
                                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-semibold">Closed</span>
                                    @endif
                                </dd>
                            </div>
                            
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <dt class="text-gray-600 font-medium">Views</dt>
                                <dd class="font-semibold">{{ number_format($trail->view_count) }}</dd>
                            </div>
                            
                            @if($trail->best_seasons)
                            <div class="py-2">
                                <dt class="text-gray-600 font-medium mb-2">Best Seasons</dt>
                                <dd class="flex flex-wrap gap-2">
                                    @foreach($trail->best_seasons as $season)
                                        <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-sm font-medium">
                                            {{ $season }}
                                        </span>
                                    @endforeach
                                </dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                        <h3 class="text-xl font-bold text-forest-700 mb-4">Plan Your Visit</h3>
                        <div class="space-y-3">
                            <button id="show-on-map-btn" class="w-full bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 text-white py-3 px-4 rounded-lg font-semibold transition-all duration-200 transform hover:scale-105">
                                View on Interactive Map
                            </button>
                            
                            @if($trail->start_coordinates)
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $trail->start_coordinates[0] }},{{ $trail->start_coordinates[1] }}" 
                               target="_blank"
                               class="block w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-lg font-semibold transition-all duration-200 transform hover:scale-105 text-center">
                                Get Directions
                            </a>
                            @endif
                            
                            <button id="download-gpx-btn" class="w-full bg-gray-600 hover:bg-gray-700 text-white py-3 px-4 rounded-lg font-semibold transition-all duration-200">
                                Download GPX
                            </button>
                        </div>
                        
                        <!-- Leave No Trace -->
                        <div class="mt-6 bg-emerald-50 border border-emerald-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-emerald-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                                <div>
                                    <h4 class="font-semibold text-emerald-900 mb-2 text-sm">Leave No Trace</h4>
                                    <ul class="text-xs text-emerald-800 space-y-1">
                                        <li>• Stay on designated trails</li>
                                        <li>• Pack out all waste</li>
                                        <li>• Respect wildlife</li>
                                        <li>• Be considerate of others</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </aside>
            
        </div>
    </div>
</div>

<!-- Lightbox for Photos -->
<div id="lightbox" class="lightbox">
    <div class="lightbox-content">
        <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
        <img id="lightbox-img" src="" alt="">
    </div>
</div>

@endsection

@push('scripts')
@vite(['resources/js/app.js'])
<script src="{{ asset('js/cesium-loader.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab Navigation
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetTab = button.dataset.tab;
            
            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked button and corresponding content
            button.classList.add('active');
            document.getElementById(`${targetTab}-tab`).classList.add('active');
            
            // If switching to route tab, invalidate map size
            if (targetTab === 'route' && window.trailMap) {
                setTimeout(() => {
                    window.trailMap.invalidateSize();
                }, 100);
            }
        });
    });
    
    // Photo Lightbox
    const trailPhotos = @json($trail->media->map(function($photo) {
        return ['url' => $photo->url, 'caption' => $photo->caption];
    }) ?? []);

    window.openLightbox = function(index) {
        if (trailPhotos[index]) {
            document.getElementById('lightbox-img').src = trailPhotos[index].url;
            document.getElementById('lightbox').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    };
    
    window.closeLightbox = function() {
        document.getElementById('lightbox').classList.remove('active');
        document.body.style.overflow = 'auto';
    };
    
    // Close lightbox on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeLightbox();
    });
    
    // Close lightbox on background click
    document.getElementById('lightbox').addEventListener('click', (e) => {
        if (e.target.id === 'lightbox') closeLightbox();
    });
    
    // Trail Map and 3D Functionality
    const trail = @json($trail);
    let trail3DViewer = null;
    let currentView = '2d';
    let trailRoute = null;
    
    // Initialize 2D map
    const map = L.map('trail-detail-map').setView(trail.start_coordinates, 13);
    window.trailMap = map;
    
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
    }).addTo(map);
    
    // Add trail route
    if (trail.route_coordinates && trail.route_coordinates.length > 0) {
        trailRoute = L.polyline(trail.route_coordinates, {
            color: '#10B981',
            weight: 4,
            opacity: 0.8,
            lineJoin: 'round',
            lineCap: 'round'
        }).addTo(map);
        
        map.fitBounds(trailRoute.getBounds(), { padding: [50, 50] });
    }
    
    // Add start marker
    const startIcon = L.divIcon({
        html: '<div class="w-10 h-10 bg-emerald-500 text-white rounded-full flex items-center justify-center font-bold text-lg border-2 border-white shadow-lg">S</div>',
        className: '',
        iconSize: [40, 40],
        iconAnchor: [20, 20]
    });
    
    L.marker(trail.start_coordinates, { icon: startIcon })
        .addTo(map)
        .bindPopup(`<div class="font-semibold">${trail.name}</div><div class="text-sm text-gray-600">Trail Start</div>`);
    
    // Add end marker if different
    if (trail.end_coordinates && 
        JSON.stringify(trail.start_coordinates) !== JSON.stringify(trail.end_coordinates)) {
        
        const endIcon = L.divIcon({
            html: '<div class="w-10 h-10 bg-red-500 text-white rounded-full flex items-center justify-center font-bold text-lg border-2 border-white shadow-lg">E</div>',
            className: '',
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });
        
        L.marker(trail.end_coordinates, { icon: endIcon })
            .addTo(map)
            .bindPopup('<div class="font-semibold">Trail End</div>');
    }

    // Add highlight markers if available
    if (trail.highlights && trail.highlights.length > 0) {
        trail.highlights.forEach(feature => {
            const highlightIcon = L.divIcon({
                html: `<div style="background-color: ${feature.color || '#EC4899'};" class="w-8 h-8 rounded-full border-2 border-white shadow-lg flex items-center justify-center text-white text-lg">${feature.icon || '📍'}</div>`,
                className: 'custom-highlight-marker',
                iconSize: [32, 32],
                iconAnchor: [16, 16]
            });
            
            const marker = L.marker(feature.coordinates, { icon: highlightIcon })
                .addTo(map)
                .bindPopup(`
                    <div class="p-2">
                        <div class="flex items-center mb-2">
                            <span class="text-xl mr-2">${feature.icon || '📍'}</span>
                            <strong>${feature.name}</strong>
                        </div>
                        ${feature.description ? `<p class="text-sm text-gray-600">${feature.description}</p>` : ''}
                        ${feature.feature_type ? `<div class="text-xs text-gray-500 mt-1 capitalize">${feature.feature_type.replace('_', ' ')}</div>` : ''}
                    </div>
                `);
        });
    }
    
    // View toggle handlers
    document.getElementById('view-2d-btn').addEventListener('click', function() {
        if (currentView === '2d') return;
        
        currentView = '2d';
        document.getElementById('trail-detail-map').classList.remove('hidden');
        document.getElementById('trail-3d-viewer').classList.add('hidden');
        document.getElementById('3d-loading').classList.add('hidden');
        
        this.classList.add('bg-emerald-600', 'text-white');
        this.classList.remove('bg-gray-200', 'text-gray-700');
        document.getElementById('view-3d-btn').classList.remove('bg-emerald-600', 'text-white');
        document.getElementById('view-3d-btn').classList.add('bg-gray-200', 'text-gray-700');
        
        document.getElementById('fit-btn-text').textContent = 'Center Route';
        
        if (trail3DViewer) {
            trail3DViewer.destroy();
            trail3DViewer = null;
        }
        
        setTimeout(() => map.invalidateSize(), 100);
    });
    
    document.getElementById('view-3d-btn').addEventListener('click', function() {
        if (currentView === '3d') return;
        
        currentView = '3d';
        document.getElementById('trail-detail-map').classList.add('hidden');
        document.getElementById('trail-3d-viewer').classList.remove('hidden');
        document.getElementById('3d-loading').classList.remove('hidden');
        
        this.classList.add('bg-emerald-600', 'text-white');
        this.classList.remove('bg-gray-200', 'text-gray-700');
        document.getElementById('view-2d-btn').classList.remove('bg-emerald-600', 'text-white');
        document.getElementById('view-2d-btn').classList.add('bg-gray-200', 'text-gray-700');
        
        document.getElementById('fit-btn-text').textContent = 'Fly to Trail';
        
        setTimeout(() => {
            try {
                trail3DViewer = new Trail3DViewer('trail-3d-viewer', trail);
                document.getElementById('3d-loading').classList.add('hidden');
            } catch (error) {
                console.error('Failed to load 3D viewer:', error);
                document.getElementById('3d-loading').innerHTML = '<span class="text-red-600">3D loading failed</span>';
            }
        }, 100);
    });
    
    // Fit route button
    document.getElementById('fit-route-btn').addEventListener('click', function() {
        if (currentView === '2d') {
            if (trailRoute) {
                map.fitBounds(trailRoute.getBounds(), { padding: [50, 50] });
            } else {
                map.setView(trail.start_coordinates, 13);
            }
        } else if (currentView === '3d' && trail3DViewer) {
            trail3DViewer.flyToTrail();
        }
    });
    
    // Show on main map button
    document.getElementById('show-on-map-btn').addEventListener('click', function() {
        window.open(`{{ route('map') }}?trail=${trail.id}`, '_blank');
    });
    
    // Focus feature function
    window.focusFeature = function(coordinates, name) {
        // Switch to route tab
        document.querySelector('[data-tab="route"]').click();
        
        setTimeout(() => {
            map.setView(coordinates, 16, { animate: true });
            
            map.eachLayer(layer => {
                if (layer instanceof L.Marker) {
                    const popup = layer.getPopup();
                    if (popup && popup.getContent().includes(name)) {
                        layer.openPopup();
                    }
                }
            });
        }, 300);
    };

    // Elevation Profile Functions
    async function loadElevationProfile() {
        if (!trail.route_coordinates || trail.route_coordinates.length < 2) {
            alert('No route data available for elevation profile');
            return;
        }

        document.getElementById('load-elevation').textContent = 'Loading...';

        try {
            const response = await fetch('/api/elevation-profile', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    coordinates: trail.route_coordinates
                })
            });

            if (response.ok) {
                const data = await response.json();
                displayElevationProfile(data);
                document.getElementById('load-elevation').textContent = 'Refresh Profile';
            } else {
                console.warn('Failed to load elevation profile');
                document.getElementById('load-elevation').textContent = 'Load Profile (Failed)';
            }
        } catch (error) {
            console.error('Error loading elevation profile:', error);
            document.getElementById('load-elevation').textContent = 'Load Profile (Error)';
        }
    }

    function displayElevationProfile(elevationData) {
        const chart = document.getElementById('elevation-chart');
        const stats = document.getElementById('elevation-stats');
        const canvas = document.getElementById('elevation-canvas');
        
        if (!canvas || !elevationData.geometry) {
            return;
        }

        chart.classList.remove('hidden');
        stats.classList.remove('hidden');

        const coordinates = elevationData.geometry.coordinates;
        const elevations = coordinates.map(coord => coord[2]);
        const maxElev = Math.max(...elevations);
        const minElev = Math.min(...elevations);
        const totalGain = calculateElevationGain(coordinates);
        const totalLoss = calculateElevationLoss(coordinates);

        // Update stats display
        const statDivs = stats.querySelectorAll('.font-bold');
        statDivs[0].textContent = Math.round(maxElev) + 'm';
        statDivs[1].textContent = Math.round(minElev) + 'm';
        statDivs[2].textContent = Math.round(totalGain) + 'm';
        statDivs[3].textContent = Math.round(totalLoss) + 'm';

        drawElevationChart(canvas, coordinates);
    }

    function drawElevationChart(canvas, coordinates) {
        const ctx = canvas.getContext('2d');
        const width = canvas.width = canvas.offsetWidth;
        const height = canvas.height = canvas.offsetHeight;

        ctx.clearRect(0, 0, width, height);

        if (coordinates.length < 2) return;

        const elevations = coordinates.map(coord => coord[2]);
        const minElev = Math.min(...elevations);
        const maxElev = Math.max(...elevations);
        const elevRange = maxElev - minElev || 1;

        // Draw elevation line
        ctx.beginPath();
        ctx.strokeStyle = '#10B981';
        ctx.lineWidth = 2;

        elevations.forEach((elevation, index) => {
            const x = (index / (elevations.length - 1)) * width;
            const y = height - ((elevation - minElev) / elevRange) * height;
            
            if (index === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        });

        ctx.stroke();

        // Fill area under curve
        ctx.lineTo(width, height);
        ctx.lineTo(0, height);
        ctx.closePath();
        ctx.fillStyle = 'rgba(16, 185, 129, 0.1)';
        ctx.fill();
    }

    function calculateElevationGain(coordinates) {
        let gain = 0;
        for (let i = 1; i < coordinates.length; i++) {
            const diff = coordinates[i][2] - coordinates[i-1][2];
            if (diff > 0) gain += diff;
        }
        return gain;
    }

    function calculateElevationLoss(coordinates) {
        let loss = 0;
        for (let i = 1; i < coordinates.length; i++) {
            const diff = coordinates[i-1][2] - coordinates[i][2];
            if (diff > 0) loss += diff;
        }
        return loss;
    }

    // Add button handler
    document.getElementById('load-elevation')?.addEventListener('click', loadElevationProfile);

    // Auto-load elevation profile on page load
    if (trail.route_coordinates && trail.route_coordinates.length > 0) {
        loadElevationProfile();
    }

    // Download GPX functionality
    document.getElementById('download-gpx-btn')?.addEventListener('click', function() {
        if (!trail.route_coordinates || trail.route_coordinates.length < 2) {
            alert('No route data available for download');
            return;
        }

        const gpxContent = generateGPX(trail.route_coordinates, trail.name);
        const blob = new Blob([gpxContent], { type: 'application/gpx+xml' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `${trail.name.replace(/[^a-z0-9]/gi, '_')}.gpx`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    });

    function generateGPX(coordinates, name) {
        const trackPoints = coordinates.map(coord => 
            `      <trkpt lat="${coord[0]}" lon="${coord[1]}"></trkpt>`
        ).join('\n');

        return `<?xml version="1.0" encoding="UTF-8"?>
    <gpx version="1.1" creator="Trail Finder" xmlns="http://www.topografix.com/GPX/1/1">
    <trk>
        <name>${name}</name>
        <trkseg>
    ${trackPoints}
        </trkseg>
    </trk>
    </gpx>`;
    }
});


</script>
@endpush