@extends('layouts.public')

@section('title', $trail->name . ' - Trail Finder')
@push('meta')
<!-- Open Graph / Facebook Meta Tags -->
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="{{ $trail->name }} - {{ $trail->difficulty_text }} Trail">
<meta property="og:description" content="{{ Str::limit($trail->description, 200) }}">
@if($trail->media && $trail->media->count() > 0)
<meta property="og:image" content="{{ url($trail->media->first()->url) }}">
<meta property="og:image:secure_url" content="{{ url($trail->media->first()->url) }}">
<meta property="og:image:type" content="image/jpeg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ $trail->name }} - Trail Photo">
@endif
<meta property="og:site_name" content="Trail Finder">

<!-- Twitter Card Meta Tags -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{{ url()->current() }}">
<meta name="twitter:title" content="{{ $trail->name }} - {{ $trail->difficulty_text }} Trail">
<meta name="twitter:description" content="{{ Str::limit($trail->description, 200) }}">
@if($trail->media && $trail->media->count() > 0)
<meta name="twitter:image" content="{{ url($trail->media->first()->url) }}">
<meta name="twitter:image:alt" content="{{ $trail->name }} - Trail Photo">
@endif
@endpush
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
        
        <!-- Admin Options Card (NEW - Below Stats) -->
        @auth
            @if(auth()->user()->isAdmin())
                <div class="mb-8 relative z-10">
                    <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-xl shadow-lg p-6 border-2 border-amber-200">
                        <!-- Header -->
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold text-amber-900 flex items-center">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                </svg>
                                Admin Options
                            </h3>
                            <span class="px-2 py-1 bg-amber-600 text-white text-xs font-bold rounded-full">ADMIN</span>
                        </div>
                        
                        <!-- Action Buttons Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <!-- Edit Trail -->
                            <a href="{{ route('admin.trails.edit', $trail->id) }}" 
                            class="flex flex-col items-center justify-center gap-2 bg-white hover:bg-amber-50 text-amber-900 py-4 px-4 rounded-lg font-semibold transition-all duration-200 border-2 border-amber-200 hover:border-amber-400 shadow-sm hover:shadow-md group">
                                <svg class="w-8 h-8 text-amber-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                <span class="text-sm">Edit Trail</span>
                            </a>
                            
                            <!-- Toggle Featured -->
                            <form action="{{ route('admin.trails.toggle-featured', $trail->id) }}" method="POST" class="w-full">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        class="flex flex-col items-center justify-center gap-2 w-full h-full bg-white hover:bg-amber-50 text-amber-900 py-4 px-4 rounded-lg font-semibold transition-all duration-200 border-2 border-amber-200 hover:border-amber-400 shadow-sm hover:shadow-md group">
                                    <svg class="w-8 h-8 {{ $trail->is_featured ? 'text-yellow-500' : 'text-gray-400' }} group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <span class="text-sm text-center">{{ $trail->is_featured ? 'Remove Featured' : 'Mark Featured' }}</span>
                                </button>
                            </form>
                            
                            <!-- View in Admin Dashboard -->
                            <a href="{{ route('admin.trails.show', $trail->id) }}" 
                            class="flex flex-col items-center justify-center gap-2 bg-white hover:bg-amber-50 text-amber-900 py-4 px-4 rounded-lg font-semibold transition-all duration-200 border-2 border-amber-200 hover:border-amber-400 shadow-sm hover:shadow-md group">
                                <svg class="w-8 h-8 text-emerald-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <span class="text-sm">View Details</span>
                            </a>
                            
                            <!-- Delete Trail -->
                            <button onclick="confirmDelete()" 
                                    class="flex flex-col items-center justify-center gap-2 bg-red-50 hover:bg-red-100 text-red-700 py-4 px-4 rounded-lg font-semibold transition-all duration-200 border-2 border-red-200 hover:border-red-400 shadow-sm hover:shadow-md group">
                                <svg class="w-8 h-8 text-red-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span class="text-sm">Delete Trail</span>
                            </button>
                            
                            <!-- Hidden delete form -->
                            <form id="delete-trail-form" action="{{ route('admin.trails.destroy', $trail->id) }}" method="POST" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                        
                        <!-- Quick Stats Row -->
                        <div class="mt-4 pt-4 border-t-2 border-amber-200">
                            <div class="grid grid-cols-3 gap-3 text-center">
                                <div class="bg-white rounded-lg p-3 border border-amber-100">
                                    <div class="text-xl font-bold text-amber-900">{{ number_format($trail->view_count) }}</div>
                                    <div class="text-xs text-amber-700">Total Views</div>
                                </div>
                                <div class="bg-white rounded-lg p-3 border border-amber-100">
                                    <div class="text-xl font-bold text-amber-900">{{ $trail->media->count() }}</div>
                                    <div class="text-xs text-amber-700">Media Items</div>
                                </div>
                                <div class="bg-white rounded-lg p-3 border border-amber-100">
                                    <div class="text-xl font-bold text-amber-900">{{ $trail->highlights->count() }}</div>
                                    <div class="text-xs text-amber-700">Highlights</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Last Updated Info -->
                        <div class="mt-3 text-xs text-amber-700 text-center bg-white rounded-lg p-2 border border-amber-100">
                            <span class="font-semibold">Last updated:</span> {{ $trail->updated_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
            @endif
        @endauth
        <!-- Two Column Layout -->
        <div class="lg:grid lg:grid-cols-12 lg:gap-12">
            
            <!-- Main Content Area -->
            <div class="lg:col-span-8">
                
                <!-- Tab Navigation -->
                <div class="tab-nav overflow-x-auto flex space-x-1">
                    <button class="tab-button active" data-tab="overview">Overview</button>
                    <button class="tab-button whitespace-nowrap" data-tab="route">Route & Map</button>
                    <button class="tab-button whitespace-nowrap" data-tab="photos">Videos & Photos</button>
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
                            <div class="grid grid-cols-1 gap-4 not-prose">
                                @foreach($trail->highlights as $highlight)
                                <div class="bg-white rounded-xl border-2 border-gray-100 hover:border-emerald-300 hover:shadow-lg transition-all duration-300 cursor-pointer overflow-hidden"
                                    onclick="focusFeature({{ json_encode($highlight->coordinates) }}, '{{ $highlight->name }}')">
                                    
                                    <div class="p-4">
                                        <!-- Header with Icon and Info -->
                                        <div class="flex items-start gap-3 mb-3">
                                            <div style="background-color: {{ $highlight->color }};" 
                                                class="w-10 h-10 rounded-lg flex items-center justify-center text-white shadow-md flex-shrink-0 text-xl">
                                                {{ $highlight->icon }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h4 class="font-bold text-gray-900 text-base leading-tight mb-1.5">
                                                    {{ $highlight->name }}
                                                </h4>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-emerald-100 text-emerald-700 capitalize">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                                    {{ str_replace('_', ' ', $highlight->feature_type) }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Description -->
                                        @if($highlight->description)
                                            <p class="text-sm text-gray-600 leading-relaxed mb-3 line-clamp-2 border-t border-gray-100 pt-3">
                                                {{ $highlight->description }}
                                            </p>
                                        @endif
                                        
                                        <!-- Media Grid -->
                                        @if($highlight->media && $highlight->media->count() > 0)
                                            <div class="border-t border-gray-100 pt-3">
                                                <div class="grid grid-cols-3 gap-1.5">
                                                    @foreach($highlight->media->take(3) as $media)
                                                        @if($media->media_type === 'photo')
                                                            <div class="relative aspect-square rounded overflow-hidden cursor-pointer hover:opacity-90 transition group"
                                                                onclick="event.stopPropagation(); openMediaModal('{{ $media->url }}', 'photo', '{{ $media->caption ?? $highlight->name }}')">
                                                                <img src="{{ $media->url }}" 
                                                                    alt="{{ $media->caption ?? $highlight->name }}"
                                                                    class="w-full h-full object-cover">
                                                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all flex items-center justify-center">
                                                                    <svg class="w-6 h-6 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                                                    </svg>
                                                                </div>
                                                            </div>
                                                        @elseif($media->media_type === 'video_url')
                                                            <div class="relative aspect-square rounded overflow-hidden cursor-pointer hover:opacity-90 transition group bg-gray-900"
                                                                data-video-url="{{ $media->video_url }}"
                                                                onclick="event.stopPropagation(); openMediaModal('{{ $media->video_url }}', 'video', '{{ $media->caption ?? $highlight->name }}')">
                                                                <div class="video-icon-placeholder w-full h-full flex items-center justify-center">
                                                                    <svg class="w-8 h-8 text-white opacity-75" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                                                    </svg>
                                                                </div>
                                                                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                                                    <div class="bg-white bg-opacity-90 rounded-full p-2">
                                                                        <svg class="w-5 h-5 text-gray-900" fill="currentColor" viewBox="0 0 20 20">
                                                                            <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @elseif($media->media_type === 'video')
                                                            @php
                                                                // If it's a local video with URL, try to get thumbnail
                                                                $videoUrl = $media->url;
                                                                $hasExternalUrl = false;
                                                                
                                                                // Check if it's actually an external video URL stored as 'video' type
                                                                if (filter_var($videoUrl, FILTER_VALIDATE_URL) && 
                                                                    (strpos($videoUrl, 'youtube.com') !== false || 
                                                                     strpos($videoUrl, 'youtu.be') !== false || 
                                                                     strpos($videoUrl, 'vimeo.com') !== false)) {
                                                                    $hasExternalUrl = true;
                                                                }
                                                            @endphp
                                                            
                                                            <div class="relative aspect-square rounded overflow-hidden cursor-pointer hover:opacity-90 transition group bg-gray-900"
                                                                @if($hasExternalUrl) data-video-url="{{ $videoUrl }}" @endif
                                                                onclick="event.stopPropagation(); openMediaModal('{{ $videoUrl }}', 'video', '{{ $media->caption ?? $highlight->name }}')">
                                                                
                                                                @if($hasExternalUrl)
                                                                    <!-- Dynamic thumbnail will load here -->
                                                                    <div class="video-icon-placeholder w-full h-full flex items-center justify-center">
                                                                        <svg class="w-8 h-8 text-white opacity-75" fill="currentColor" viewBox="0 0 20 20">
                                                                            <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                                                        </svg>
                                                                    </div>
                                                                @else
                                                                    <!-- Local video - show video element or placeholder -->
                                                                    <div class="w-full h-full flex items-center justify-center">
                                                                        <svg class="w-8 h-8 text-white opacity-75" fill="currentColor" viewBox="0 0 20 20">
                                                                            <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                                                        </svg>
                                                                    </div>
                                                                @endif
                                                                
                                                                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                                                    <div class="bg-white bg-opacity-90 rounded-full p-2">
                                                                        <svg class="w-5 h-5 text-gray-900" fill="currentColor" viewBox="0 0 20 20">
                                                                            <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                                
                                                @if($highlight->media->count() > 3)
                                                    <div class="text-center mt-2">
                                                        <span class="text-xs text-emerald-600 font-semibold">
                                                            +{{ $highlight->media->count() - 3 }} more
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
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
                            <h2 class="text-2xl sm:text-3xl font-bold text-forest-700">Interactive Trail Map</h2>
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
                    <!-- Photos & Videos Section -->
                    @if($generalMedia->count() > 0)
                    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Media ({{ $generalMedia->count() }})</h3>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                @foreach($generalMedia as $media)
                                    @if($media->media_type === 'photo')
                                        {{-- Photo Display --}}
                                        <div class="relative group overflow-hidden rounded-lg border border-input cursor-pointer"
                                            onclick="openMediaModal('{{ asset('storage/' . $media->storage_path) }}', 'photo', '{{ $media->caption ?? $trail->name }}')">
                                            <img src="{{ asset('storage/' . $media->storage_path) }}" 
                                                alt="{{ $media->caption ?? $trail->name }}" 
                                                class="w-full h-32 object-cover transition-transform group-hover:scale-105">
                                            @if($media->is_featured)
                                                <div class="absolute top-2 left-2">
                                                    <span class="bg-yellow-400 text-yellow-900 px-2 py-1 rounded text-xs font-medium">
                                                        ⭐ Featured
                                                    </span>
                                                </div>
                                            @endif
                                            @if($media->caption)
                                                <div class="absolute bottom-0 inset-x-0 bg-black bg-opacity-50 text-white p-2">
                                                    <p class="text-xs">{{ $media->caption }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    @elseif($media->media_type === 'video_url')
                                        {{-- Video URL Display --}}
                                        <div class="relative group overflow-hidden rounded-lg border border-input cursor-pointer"
                                            onclick="openMediaModal('{{ $media->video_url }}', 'video', '{{ $media->caption ?? $trail->name }}')">
                                            <div class="w-full h-32 bg-gray-900 flex items-center justify-center relative">
                                                {{-- Video Thumbnail from provider --}}
                                                @if($media->video_provider === 'youtube')
                                                    @php
                                                        preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/', $media->video_url, $matches);
                                                        $videoId = $matches[1] ?? null;
                                                    @endphp
                                                    @if($videoId)
                                                        <img src="https://img.youtube.com/vi/{{ $videoId }}/mqdefault.jpg" 
                                                            alt="Video thumbnail"
                                                            class="w-full h-full object-cover">
                                                    @endif
                                                @elseif($media->video_provider === 'vimeo')
                                                    @php
                                                        preg_match('/vimeo\.com\/(\d+)/', $media->video_url, $matches);
                                                        $videoId = $matches[1] ?? null;
                                                    @endphp
                                                    {{-- Vimeo thumbnails require API, so we'll show a placeholder --}}
                                                    <svg class="w-12 h-12 text-white opacity-75" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                                    </svg>
                                                @else
                                                    {{-- Generic video icon --}}
                                                    <svg class="w-12 h-12 text-white opacity-75" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                                    </svg>
                                                @endif
                                                
                                                {{-- Play button overlay --}}
                                                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                                    <div class="bg-white bg-opacity-90 rounded-full p-3 shadow-lg">
                                                        <svg class="w-8 h-8 text-gray-900" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            {{-- Video badge --}}
                                            <div class="absolute top-2 left-2">
                                                <span class="bg-red-600 text-white px-2 py-1 rounded text-xs font-medium flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                                    </svg>
                                                    Video
                                                </span>
                                            </div>
                                            
                                            @if($media->is_featured)
                                                <div class="absolute top-2 right-2">
                                                    <span class="bg-yellow-400 text-yellow-900 px-2 py-1 rounded text-xs font-medium">
                                                        ⭐ Featured
                                                    </span>
                                                </div>
                                            @endif
                                            
                                            @if($media->caption)
                                                <div class="absolute bottom-0 inset-x-0 bg-black bg-opacity-50 text-white p-2">
                                                    <p class="text-xs">{{ $media->caption }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
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
                            <a href="https://www.google.com/maps/dir/Hazelton,+BC/{{ $trail->start_coordinates[0] }},{{ $trail->start_coordinates[1] }}" 
                               target="_blank"
                               class="block w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-lg font-semibold transition-all duration-200 transform hover:scale-105 text-center">
                                Get Directions
                            </a>
                            @endif
                            
                            <button id="download-gpx-btn" class="w-full bg-gray-600 hover:bg-gray-700 text-white py-3 px-4 rounded-lg font-semibold transition-all duration-200">
                                Download GPX
                            </button>

                            <!-- Share Button (NEW) -->
                            <button id="share-trail-btn" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 px-4 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                                </svg>
                                Share Trail
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

<div id="media-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4">
    <div class="relative max-w-5xl w-full bg-white rounded-lg shadow-xl">
        <!-- Close button -->
        <button onclick="closeMediaModal()" 
                class="absolute top-4 right-4 z-10 bg-gray-900 bg-opacity-75 hover:bg-opacity-100 text-white rounded-full p-2 transition-all">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        
        <!-- Content container -->
        <div id="modal-content" class="p-4">
            <!-- Content will be dynamically inserted here -->
        </div>
        
        <!-- Caption -->
        <div id="modal-caption" class="px-6 pb-6 text-center text-gray-700">
            <!-- Caption will be dynamically inserted here -->
        </div>
    </div>
</div>

<!-- Share Modal (COMPLETE VERSION) -->
<div id="share-modal" 
     class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4"
     style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all"
         onclick="event.stopPropagation()">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 px-6 py-4 flex justify-between items-center">
            <h3 class="text-xl font-bold text-white">Share This Trail</h3>
            <button id="close-share-modal" 
                    class="text-white hover:text-gray-200 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <!-- Trail Preview -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="font-semibold text-gray-900 mb-1">{{ $trail->name }}</h4>
                <p class="text-sm text-gray-600 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    </svg>
                    {{ $trail->location }}
                </p>
            </div>

            <!-- Copy Link Section -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Trail Link</label>
                <div class="flex gap-2">
                    <input type="text" 
                           id="trail-url" 
                           readonly 
                           value="{{ url()->current() }}"
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <button id="copy-link-btn"
                            class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors flex items-center gap-2 font-medium whitespace-nowrap">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <span id="copy-btn-text">Copy</span>
                    </button>
                </div>
            </div>

            <!-- Social Share Buttons -->
            <div class="space-y-3">
                <p class="text-sm font-medium text-gray-700 mb-3">Share via social media</p>
                
                <!-- Facebook -->
                <button onclick="shareToFacebook()" 
                        class="w-full flex items-center gap-3 px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-sm hover:shadow-md">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    <span class="font-semibold">Share on Facebook</span>
                </button>

                <!-- Twitter/X -->
                <button onclick="shareToTwitter()" 
                        class="w-full flex items-center gap-3 px-4 py-3 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors shadow-sm hover:shadow-md">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                    </svg>
                    <span class="font-semibold">Share on X (Twitter)</span>
                </button>

                <!-- WhatsApp -->
                <button onclick="shareToWhatsApp()" 
                        class="w-full flex items-center gap-3 px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors shadow-sm hover:shadow-md">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                    </svg>
                    <span class="font-semibold">Share on WhatsApp</span>
                </button>

                <!-- Email -->
                <button onclick="shareViaEmail()" 
                        class="w-full flex items-center gap-3 px-4 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors shadow-sm hover:shadow-md">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span class="font-semibold">Share via Email</span>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
@vite(['resources/js/app.js'])
<script src="{{ asset('js/cesium-loader.js') }}"></script>
<script>
// Video Thumbnail Generator Functions
function getVideoThumbnail(videoUrl) {
    // YouTube
    const youtubeMatch = videoUrl.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/);
    if (youtubeMatch) {
        const videoId = youtubeMatch[1];
        return `https://img.youtube.com/vi/${videoId}/hqdefault.jpg`;
    }
    
    // Vimeo
    const vimeoMatch = videoUrl.match(/vimeo\.com\/(\d+)/);
    if (vimeoMatch) {
        const videoId = vimeoMatch[1];
        return `https://vumbnail.com/${videoId}.jpg`;
    }
    
    return null;
}

function loadVideoThumbnails() {
    // Find all video thumbnail containers
    const videoContainers = document.querySelectorAll('[data-video-url]');
    
    videoContainers.forEach(container => {
        const videoUrl = container.getAttribute('data-video-url');
        const thumbnailUrl = getVideoThumbnail(videoUrl);
        
        if (thumbnailUrl) {
            // Create img element
            const img = document.createElement('img');
            img.src = thumbnailUrl;
            img.alt = 'Video thumbnail';
            img.className = 'w-full h-full object-cover';
            
            // Handle thumbnail load error
            img.onerror = function() {
                // Keep the default video icon if thumbnail fails
                console.log('Thumbnail failed to load for:', videoUrl);
            };
            
            // Replace the video icon with thumbnail
            img.onload = function() {
                const iconDiv = container.querySelector('.video-icon-placeholder');
                if (iconDiv) {
                    iconDiv.remove();
                }
                container.prepend(img);
            };
        }
    });
}

// Call after page loads
document.addEventListener('DOMContentLoaded', loadVideoThumbnails);

function openMediaModal(url, type, caption) {
    const modal = document.getElementById('media-modal');
    const content = document.getElementById('modal-content');
    const captionEl = document.getElementById('modal-caption');
    
    if (type === 'photo') {
        content.innerHTML = `<img src="${url}" alt="${caption}" class="w-full h-auto max-h-[70vh] object-contain rounded-lg">`;
    } else if (type === 'video') {
        // Convert video URL to embed URL
        let embedUrl = '';
        
        // YouTube
        const youtubeMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/);
        if (youtubeMatch) {
            embedUrl = `https://www.youtube.com/embed/${youtubeMatch[1]}`;
        }
        
        // Vimeo
        const vimeoMatch = url.match(/vimeo\.com\/(\d+)/);
        if (vimeoMatch) {
            embedUrl = `https://player.vimeo.com/video/${vimeoMatch[1]}`;
        }
        
        if (embedUrl) {
            content.innerHTML = `
                <div class="relative" style="padding-bottom: 56.25%; height: 0;">
                    <iframe src="${embedUrl}" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen
                            class="absolute top-0 left-0 w-full h-full rounded-lg">
                    </iframe>
                </div>
            `;
        }
    }
    
    captionEl.textContent = caption;
    modal.classList.remove('hidden');
}

function closeMediaModal() {
    const modal = document.getElementById('media-modal');
    const content = document.getElementById('modal-content');
    
    modal.classList.add('hidden');
    content.innerHTML = ''; // Clear content to stop video playback
}

// Close modal when clicking outside
document.getElementById('media-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeMediaModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMediaModal();
    }
});
</script>
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
                    // Auto-trigger center route button
                    document.getElementById('fit-route-btn')?.click();
                }, 300);
            }
        });
    });
    
    // Photo Lightbox
    @php
        $generalMediaForJs = $trail->generalMedia;
    @endphp
    const trailPhotos = @json($generalMediaForJs->map(function($photo) {
        return ['url' => $photo->url, 'caption' => $photo->caption];
    }) ?? []);

    window.openLightbox = function(index) {
        if (trailPhotos[index]) {
            document.getElementById('lightbox-img').src = trailPhotos[index].url;
            const lightbox = document.getElementById('lightbox');
            lightbox.classList.add('active');
            lightbox.style.zIndex = '9999';
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
    
    // Trail Map and 3D Functionality
    const trail = @json($trail);
    let trail3DViewer = null;
    let currentView = '2d';
    let trailRoute = null;
    
    // Initialize 2D map
    const map = L.map('trail-detail-map').setView(trail.start_coordinates, 13);
    window.trailMap = map;
    
    L.tileLayer('https://{s}.tile-cyclosm.openstreetmap.fr/cyclosm/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap, CyclOSM',
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
            
            // Build media HTML for popup
            let mediaHTML = '';
            if (feature.media && feature.media.length > 0) {
                const displayMedia = feature.media.slice(0, 3); // Show max 3 media items
                const hasMore = feature.media.length > 3;
                const basePath = window.location.origin;
                mediaHTML = `
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <div class="grid grid-cols-3 gap-1.5">
                            ${displayMedia.map(media => {
                                if (media.media_type === 'photo') {
                                    return `
                                        <div class="relative aspect-square rounded overflow-hidden cursor-pointer hover:opacity-90 transition group"
                                            onclick="event.stopPropagation(); openMediaModal('${basePath}/storage/${media.storage_path}', 'photo', '${media.caption || feature.name}')">
                                            <img src="${basePath}/storage/${media.storage_path}" 
                                                alt="${media.caption || feature.name}"
                                                class="w-full h-full object-cover">
                                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all flex items-center justify-center">
                                                <svg class="w-5 h-5 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                                </svg>
                                            </div>
                                        </div>
                                    `;
                                } else if (media.media_type === 'video_url') {
                                    // Generate thumbnail for video
                                    let thumbnailHTML = '';
                                    const videoUrl = media.video_url || media.url;
                                    
                                    if (media.video_provider === 'youtube') {
                                        // Extract YouTube video ID and create thumbnail
                                        const youtubeMatch = videoUrl.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/);
                                        if (youtubeMatch && youtubeMatch[1]) {
                                            const videoId = youtubeMatch[1];
                                            thumbnailHTML = `
                                                <img src="https://img.youtube.com/vi/${videoId}/mqdefault.jpg" 
                                                    alt="${media.caption || feature.name}"
                                                    class="w-full h-full object-cover"
                                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="w-full h-full hidden items-center justify-center bg-gray-900">
                                                    <svg class="w-8 h-8 text-white opacity-75" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                                    </svg>
                                                </div>
                                            `;
                                        }
                                    } else if (media.video_provider === 'vimeo') {
                                        // For Vimeo, we'd need an API call, so we'll use generic icon for now
                                        thumbnailHTML = `
                                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-500 to-blue-700">
                                                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                                </svg>
                                            </div>
                                        `;
                                    }
                                    
                                    // Fallback if no thumbnail could be generated
                                    if (!thumbnailHTML) {
                                        thumbnailHTML = `
                                            <div class="w-full h-full flex items-center justify-center bg-gray-900">
                                                <svg class="w-8 h-8 text-white opacity-75" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                                </svg>
                                            </div>
                                        `;
                                    }
                                    
                                    return `
                                        <div class="relative aspect-square rounded overflow-hidden cursor-pointer hover:opacity-90 transition group"
                                            onclick="event.stopPropagation(); openMediaModal('${videoUrl}', 'video', '${media.caption || feature.name}')">
                                            ${thumbnailHTML}
                                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all"></div>
                                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                                <div class="bg-white bg-opacity-90 rounded-full p-1.5 shadow-lg group-hover:scale-110 transition-transform">
                                                    <svg class="w-4 h-4 text-gray-900" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                }
                                return '';
                            }).join('')}
                        </div>
                        ${hasMore ? `
                            <div class="text-center mt-2">
                                <span class="text-xs text-emerald-600 font-medium">+${feature.media.length - 3} more</span>
                            </div>
                        ` : ''}
                    </div>
                `;
            }

            const marker = L.marker(feature.coordinates, { icon: highlightIcon })
                .addTo(map)
                .bindPopup(`
                    <div class="min-w-[240px] max-w-[320px]">
                        <div class="space-y-2">
                            <div class="flex items-start gap-2">
                                <div style="background-color: ${feature.color || '#6366f1'};" class="w-8 h-8 rounded-lg flex items-center justify-center text-white shadow-sm flex-shrink-0">
                                    ${feature.icon || '📍'}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-semibold text-sm text-gray-900 leading-tight mb-1">${feature.name}</h4>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-emerald-100 text-emerald-700 capitalize">
                                        ${(feature.feature_type || '').replace(/_/g, ' ')}
                                    </span>
                                </div>
                            </div>
                            ${feature.description ? `
                                <p class="text-xs text-gray-600 leading-relaxed pt-2 border-t border-gray-100">
                                    ${feature.description}
                                </p>
                            ` : ''}
                            ${mediaHTML}
                        </div>
                    </div>
                `, {
                    maxWidth: 320,
                    className: 'custom-popup'
                });
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
        console.log('loadElevationProfile called');
        
        if (!trail.route_coordinates || trail.route_coordinates.length < 2) {
            console.log('No route data available for elevation profile');
            document.getElementById('elevation-chart').classList.add('hidden');
            document.getElementById('elevation-stats').classList.add('hidden');
            return;
        }

        // Check if coordinates have elevation data (3rd coordinate)
        const hasElevation = trail.route_coordinates[0].length >= 3;
        
        if (!hasElevation) {
            console.log('Route coordinates do not have elevation data. Fetching from API...');
            
            document.getElementById('load-elevation').textContent = 'Loading elevation data...';

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
                    console.log('Elevation data received:', data);
                    displayElevationProfile(data);
                    document.getElementById('load-elevation').textContent = 'Refresh Profile';
                } else {
                    console.warn('Failed to load elevation profile from API');
                    document.getElementById('load-elevation').textContent = 'Elevation data unavailable';
                    document.getElementById('elevation-chart').classList.add('hidden');
                    document.getElementById('elevation-stats').classList.add('hidden');
                }
            } catch (error) {
                console.error('Error loading elevation profile:', error);
                document.getElementById('load-elevation').textContent = 'Failed to load elevation';
                document.getElementById('elevation-chart').classList.add('hidden');
                document.getElementById('elevation-stats').classList.add('hidden');
            }
        } else {
            // Use existing elevation data from route coordinates
            console.log('Using existing elevation data');
            displayElevationProfile({
                geometry: {
                    coordinates: trail.route_coordinates
                }
            });
            document.getElementById('load-elevation').textContent = 'Refresh Profile';
        }
    }

    function displayElevationProfile(elevationData) {
        const chart = document.getElementById('elevation-chart');
        const stats = document.getElementById('elevation-stats');
        const canvas = document.getElementById('elevation-canvas');
        
        if (!canvas || !elevationData.geometry || !elevationData.geometry.coordinates) {
            console.log('Invalid elevation data');
            return;
        }

        const coordinates = elevationData.geometry.coordinates;
        
        // Check if coordinates have elevation data (z-coordinate)
        if (coordinates[0].length < 3) {
            console.log('No elevation data in coordinates');
            chart.classList.add('hidden');
            stats.classList.add('hidden');
            return;
        }

        chart.classList.remove('hidden');
        stats.classList.remove('hidden');

        const elevations = coordinates.map(coord => coord[2]);
        const maxElev = Math.max(...elevations);
        const minElev = Math.min(...elevations);
        const totalGain = calculateElevationGain(coordinates);
        const totalLoss = calculateElevationLoss(coordinates);

        // Update stats display
        const statDivs = stats.querySelectorAll('.font-bold');
        if (statDivs.length >= 4) {
            statDivs[0].textContent = Math.round(maxElev) + 'm';
            statDivs[1].textContent = Math.round(minElev) + 'm';
            statDivs[2].textContent = Math.round(totalGain) + 'm';
            statDivs[3].textContent = Math.round(totalLoss) + 'm';
        }

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
    
    // Load elevation profile when Route tab is activated
    let elevationProfileLoaded = false;

    function loadElevationIfNeeded() {
        if (!elevationProfileLoaded && trail.route_coordinates && trail.route_coordinates.length > 0) {
            elevationProfileLoaded = true;
            console.log('Loading elevation profile...');
            // Wait for tab transition to complete
            setTimeout(() => {
                loadElevationProfile();
            }, 300);
        }
    }

    // Add event listener to Route tab button
    const routeTabButton = document.querySelector('[data-tab="route"]');
    if (routeTabButton) {
        routeTabButton.addEventListener('click', function() {
            // Load elevation when route tab is clicked
            loadElevationIfNeeded();
        });
    }

    // If user loads page with hash #route or route tab is already active
    if (window.location.hash === '#route' || document.getElementById('route-tab')?.classList.contains('active')) {
        setTimeout(loadElevationIfNeeded, 500);
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

        return `<gpx version="1.1" creator="Trail Finder" xmlns="http://www.topografix.com/GPX/1/1">
        <trk>
            <name>${name}</name>
            <trkseg>
        ${trackPoints}
            </trkseg>
        </trk>
        </gpx>`;
    }

    // Share Modal Functionality (NEW)
    const shareBtn = document.getElementById('share-trail-btn');
    const shareModal = document.getElementById('share-modal');
    const closeModalBtn = document.getElementById('close-share-modal');
    const copyLinkBtn = document.getElementById('copy-link-btn');
    const trailUrlInput = document.getElementById('trail-url');

    // Open modal
    shareBtn?.addEventListener('click', function() {
        shareModal.style.display = 'flex';
        setTimeout(() => {
            shareModal.classList.remove('hidden');
        }, 10);
    });

    // Close modal
    function closeShareModal() {
        shareModal.classList.add('hidden');
        setTimeout(() => {
            shareModal.style.display = 'none';
        }, 300);
    }

    closeModalBtn?.addEventListener('click', closeShareModal);
    
    // Close on backdrop click
    shareModal?.addEventListener('click', function(e) {
        if (e.target === shareModal) {
            closeShareModal();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !shareModal.classList.contains('hidden')) {
            closeShareModal();
        }
    });

    // Copy link functionality
    copyLinkBtn?.addEventListener('click', async function() {
        const url = trailUrlInput.value;
        const btnText = document.getElementById('copy-btn-text');
        
        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(url);
            } else {
                // Fallback for older browsers
                trailUrlInput.select();
                document.execCommand('copy');
            }
            
            // Success feedback
            btnText.textContent = 'Copied!';
            copyLinkBtn.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
            copyLinkBtn.classList.add('bg-green-600');
            
            setTimeout(() => {
                btnText.textContent = 'Copy';
                copyLinkBtn.classList.remove('bg-green-600');
                copyLinkBtn.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
            }, 2000);
        } catch (err) {
            console.error('Failed to copy:', err);
            btnText.textContent = 'Failed';
            setTimeout(() => {
                btnText.textContent = 'Copy';
            }, 2000);
        }
    });

    // Trail data for sharing
    const trailData = {
        name: '{{ addslashes($trail->name) }}',
        difficulty: '{{ $trail->difficulty_text }}',
        location: '{{ addslashes($trail->location) }}',
        distance: '{{ $trail->distance ? number_format($trail->distance, 1) . " km" : "" }}'
    };

    // Social Share Functions
    window.shareToFacebook = function() {
        const url = encodeURIComponent(trailUrlInput.value);
        const shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
        window.open(shareUrl, 'facebook-share', 'width=600,height=400');
    };

    window.shareToTwitter = function() {
        const url = encodeURIComponent(trailUrlInput.value);
        const text = encodeURIComponent(`Check out ${trailData.name} - ${trailData.difficulty} trail in ${trailData.location}! 🥾⛰️`);
        const shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${text}`;
        window.open(shareUrl, 'twitter-share', 'width=600,height=400');
    };

    window.shareToWhatsApp = function() {
        const url = encodeURIComponent(trailUrlInput.value);
        const text = encodeURIComponent(`Check out this trail: ${trailData.name} - ${trailData.difficulty} in ${trailData.location}! 🥾⛰️\n\n`);
        const shareUrl = `https://wa.me/?text=${text}${url}`;
        window.open(shareUrl, 'whatsapp-share');
    };

    window.shareViaEmail = function() {
        const url = trailUrlInput.value;
        const subject = encodeURIComponent(`Check out this trail: ${trailData.name}`);
        
        let emailBody = `I found this amazing trail and thought you might be interested!\n\n`;
        emailBody += `Trail: ${trailData.name}\n`;
        emailBody += `Location: ${trailData.location}\n`;
        emailBody += `Difficulty: ${trailData.difficulty}\n`;
        if (trailData.distance) {
            emailBody += `Distance: ${trailData.distance}\n`;
        }
        emailBody += `Link: ${url}\n\n`;
        emailBody += `Happy hiking! 🥾⛰️`;
        
        const body = encodeURIComponent(emailBody);
        window.location.href = `mailto:?subject=${subject}&body=${body}`;
    };    
});

// Admin delete confirmation
function confirmDelete() {
    if (confirm('Are you sure you want to delete this trail? This action cannot be undone.')) {
        if (confirm('Final confirmation: This will permanently delete all trail data, media, and highlights. Continue?')) {
            document.getElementById('delete-trail-form').submit();
        }
    }
}

</script>
@endpush