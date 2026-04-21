@extends('layouts.public')

@section('title', $network->network_name)

@section('content')

@if($network->slug === 'hudson-bay-mountain-ski-ride-smithers')
    <!-- Sponsor Welcome Banner (Trail Network Only) -->
    <div id="sponsor-banner" class="fixed top-20 left-0 right-0 z-[60] bg-gradient-to-r from-accent-500 to-forest-600 shadow-lg">
        <div class="max-w-4xl mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <a href="https://bvliving.ca/" target="_blank" rel="noopener noreferrer" class="flex items-center space-x-4 flex-1 hover:opacity-90 transition-opacity group">
                    <div class="flex-shrink-0 bg-white rounded-lg p-1.5">
                        <img src="{{ asset('images/phil-bernier-realtor-logo.png') }}" 
                            alt="Phil Bernier Realtor Logo" 
                            class="w-8 h-8 object-contain group-hover:scale-110 transition-transform">
                    </div>
                    <div class="flex-1 text-center md:text-left">
                        <p class="text-white font-medium text-sm md:text-base">
                            <span class="hidden md:inline">Welcome to Hudson Bay Mountain! </span>
                            Trail maps proudly sponsored by <span class="font-bold"> <br>Phil Bernier – REALTOR®</span>
                            <span class="hidden md:inline ml-2 text-white/90">| Click to explore Smithers real estate</span>
                        </p>
                    </div>
                    <div class="hidden md:flex items-center space-x-2 px-4 py-2 bg-white/20 rounded-lg hover:bg-white/30 transition-colors">
                        <span class="text-white text-sm font-semibold">Visit BVLiving.ca</span>
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </div>
                </a>
                <button onclick="document.getElementById('sponsor-banner').style.display='none'; localStorage.setItem('hbm-sponsor-banner-dismissed', 'true');" 
                        class="flex-shrink-0 ml-4 text-white hover:text-gray-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
@endif

<link href="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.css" rel="stylesheet">

<style>
    #network-map {
        height: calc(100vh - 80px);
        width: 100%;
        z-index: 1;
    }

    /* Trail name labels */
    .trail-name-label {
        background: transparent !important;
        border: none !important;
        pointer-events: auto !important;  /* Changed from none to auto */
    }

    .trail-label-text {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: bold;
        color: white;
        white-space: nowrap;
        text-align: center;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        border: 2px solid white;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .trail-label-text:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
    }

    /* Sidebar */
    .network-sidebar {
        position: absolute;
        top: 16px;
        bottom: 16px;
        left: 20px;
        z-index: 1000;
        width: 350px;
        overflow-y: auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transition: transform 0.3s ease-in-out;
    }

    @media (max-width: 768px) {
        .network-sidebar {
            width: calc(100% - 40px);
            max-width: 400px;
        }
        
        .network-sidebar.hidden-mobile {
            transform: translateX(calc(-100% - 40px));
        }
    }

    /* Sidebar toggle button for mobile */
    .sidebar-toggle {
        position: absolute;
        top: 16px;
        left: 20px;
        z-index: 30;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        padding: 12px;
        cursor: pointer;
        display: none;
        transition: all 0.3s ease-in-out;
    }

    .sidebar-toggle:hover {
        background: #f9fafb;
    }

    .sidebar-toggle.hidden {
        opacity: 0;
        pointer-events: none;
        transform: scale(0.8);
    }

    @media (max-width: 768px) {
        .sidebar-toggle {
            display: block;
        }
    }

    /* Custom scrollbar */
    .network-sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .network-sidebar::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .network-sidebar::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }

    .network-sidebar::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Custom scrollbar for trails list */
    .overflow-y-auto::-webkit-scrollbar {
        width: 8px;
    }

    .overflow-y-auto::-webkit-scrollbar-track {
        background: #f9fafb;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 4px;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }

    /* Custom scrollbar for trails list */
    .overflow-y-auto::-webkit-scrollbar {
        width: 8px;
    }

    .overflow-y-auto::-webkit-scrollbar-track {
        background: #f9fafb;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 4px;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }

    /* Facility marker styling */
    .facility-marker {
        background: transparent !important;
        border: none !important;
    }



    /* Trail center dot (replaces circleMarker for reliable clicking) */
    .trail-center-dot {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.35);
        cursor: pointer;
        /* Do NOT transition transform — Mapbox uses CSS transform to position markers;
           animating it causes dots to drift/lag during zoom and pan. */
        transition: box-shadow 0.15s ease, outline-color 0.15s ease;
        outline: 8px solid transparent;
    }

    .trail-center-dot:hover {
        box-shadow: 0 4px 14px rgba(0,0,0,0.55);
        outline-color: rgba(255,255,255,0.25);
    }

    /* Waypoint markers */
    .waypoint-marker {
        background: transparent !important;
        border: none !important;
    }

    .waypoint-start {
        width: 16px;
        height: 16px;
        background: #10b981;
        border: 3px solid white;
        box-shadow: 0 3px 8px rgba(16, 185, 129, 0.5);
    }

    .waypoint-end {
        width: 16px;
        height: 16px;
        background: #ef4444;
        border: 3px solid white;
        box-shadow: 0 3px 8px rgba(239, 68, 68, 0.5);
    }



    /* Layer option cards with images */
    .layer-option-card {
        position: relative;
        cursor: pointer;
        border-radius: 0.5rem;
        overflow: hidden;
        transition: all 0.2s;
        border: 2px solid transparent;
        display: flex;
        flex-direction: column;
        align-items: center;
        background: white;
    }

    .layer-option-card:hover {
        border-color: #93C5FD;
    }

    .layer-option-card.active {
        border-color: #2563EB;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .layer-preview {
        width: 100%;
        height: 70px;
        border-radius: 0.375rem;
        overflow: hidden;
        position: relative;
    }

    .layer-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .layer-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 500;
        color: #374151;
        text-align: center;
        margin-top: 0.5rem;
        padding: 0 0.25rem;
    }

    .layer-checkmark {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 20px;
        height: 20px;
        color: white;
        background-color: #2563EB;
        border-radius: 50%;
        padding: 2px;
        display: none;
    }

    .layer-option-card.active .layer-checkmark {
        display: block;
    }

    #layers-dropdown {
        animation: slideDown 0.2s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Sticky sidebar header */
    .network-sidebar .sidebar-header {
        position: sticky;
        top: 0;
        background: white;
        z-index: 10;
        border-bottom: 1px solid #e5e7eb;
    }

    /* Legend positioning */
    .map-legend {
        position: absolute;
        top: 6rem;
        right: 1rem;
        z-index: 30;
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        padding: 1rem;
        max-width: 12rem;
    }

    @media (max-width: 768px) {
        .map-legend {
            top: auto;
            bottom: 1.5rem;
            right: auto;
            left: 1rem;
        }
    }

    /* Trail Details Card */
    .trail-details-card {
        position: absolute;
        top: 20px;
        left: 390px; /* 350px sidebar + 40px gap */
        z-index: 999;
        width: 350px;
        max-height: calc(100vh - 140px);
        overflow-y: auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease-in-out;
        opacity: 0;
        transform: translateX(-20px);
        pointer-events: none;
    }

    .trail-details-card.visible {
        opacity: 1;
        transform: translateX(0);
        pointer-events: auto;
    }

    @media (max-width: 768px) {
        .trail-details-card {
            left: 20px;
            right: 20px;
            width: auto;
            top: 80px; /* Position below the hamburger button (20px top + 48px button height + 12px gap) */
            bottom: auto;
            max-height: calc(100vh - 100px); /* Leave space at bottom */
            transform: translateY(-20px);
        }
        
        .trail-details-card.visible {
            transform: translateY(0);
        }
    }

    /* Custom scrollbar for trail details */
    .trail-details-card::-webkit-scrollbar {
        width: 6px;
    }

    .trail-details-card::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .trail-details-card::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }

    .trail-details-card::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>

<div class="relative">
    @if($network->slug === 'hudson-bay-mountain-ski-ride-smithers')
        <!-- Sponsor Badge - Floating Corner (Trail Network Only - Mobile & Desktop) -->
        <a href="https://bvliving.ca/" target="_blank" rel="noopener noreferrer" class="fixed bottom-3 md:bottom-3 right-12 md:right-12 z-[45]">
            <div class="bg-white rounded-lg shadow-xl border-2 border-accent-500/20 p-3 md:p-4 hover:shadow-2xl hover:scale-105 transition-all duration-300 group">
                <div class="flex items-center space-x-2 md:space-x-3">
                    <div class="flex-shrink-0">
                        <img src="{{ asset('images/phil-bernier-realtor-logo.png') }}" 
                            alt="Phil Bernier Realtor Logo" 
                            class="w-10 h-10 md:w-12 md:h-12 object-contain group-hover:scale-110 transition-transform">
                    </div>
                    <div class="text-left">
                        <p class="text-xs text-gray-500 font-medium">Sponsored by</p>
                        <p class="text-sm font-bold text-gray-900 group-hover:text-accent-600 transition-colors">Phil Bernier</p>
                        <p class="text-xs text-forest-600 font-semibold">REALTOR®</p>
                    </div>
                </div>
                <div class="mt-2 pt-2 border-t border-gray-100 hidden md:block">
                    <p class="text-xs text-gray-500 group-hover:text-accent-600 transition-colors flex items-center space-x-1">
                        <span>Learn more</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </p>
                </div>
            </div>
        </a>
    @endif
    <!-- Mobile Sidebar Toggle -->
    <button class="sidebar-toggle" id="sidebar-toggle">
        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>
    <!-- Map Container -->
    <div id="network-map"></div>

    <!-- Sidebar -->
    <div class="network-sidebar">
        <!-- Header -->
        <div class="sidebar-header p-6 pb-3 bg-white">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $network->network_name }}</h1>
                    <span class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium
                        {{ $network->type === 'nordic_skiing' ? 'bg-blue-100 text-blue-700' : '' }}
                        {{ $network->type === 'downhill_skiing' ? 'bg-purple-100 text-purple-700' : '' }}
                        {{ $network->type === 'hiking' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $network->type === 'mountain_biking' ? 'bg-orange-100 text-orange-700' : '' }}">
                        {{ ucwords(str_replace('_', ' ', $network->type)) }}
                    </span>
                </div>
                <button id="sidebar-close" class="flex-shrink-0 ml-3 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            @if($network->description)
                <p class="text-sm text-gray-600 leading-relaxed mb-4">{{ $network->description }}</p>
            @endif

            <div class="space-y-2 text-sm">
                @if($network->address)
                    <div class="flex items-center text-gray-600">
                        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        <span>{{ $network->address }}</span>
                    </div>
                @endif

                @if($network->website_url)
                    <div class="flex items-center">
                        <a href="{{ $network->website_url }}" 
                        target="_blank"
                        class="inline-flex items-center text-sm font-medium text-green-600 hover:text-green-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                            </svg>
                            Visit Website
                        </a>
                    </div>
                @endif
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-3 mt-4">
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <div class="text-2xl font-bold text-gray-900">{{ $network->trails->count() }}</div>
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Trails</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($network->trails->sum('distance_km'), 1) }}</div>
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total km</div>
                </div>
            </div>

            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mt-3 mb-2">Trails</h3>
            <!-- Search Box -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" 
                    id="trail-search" 
                    placeholder="Search trails..." 
                    class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                    onkeyup="searchTrails()">
            </div>
        </div>

        <!-- Trails Section -->
        <div class="bg-white">
            <!-- Scrollable Trails Container -->
            <div class="overflow-y-auto" style="max-height: 320px;">
                @if($network->trails->count() > 0)
                    <div class="p-3 space-y-2" id="trails-container">
                        @foreach($network->trails->sortBy('difficulty_level') as $trail)
                            @php
                                $difficultyLevel = floor($trail->difficulty_level);
                            @endphp
                            <div class="group p-3 bg-white hover:bg-gray-50 rounded-lg cursor-pointer transition-all border border-gray-200 hover:border-gray-300 hover:shadow-sm trail-item"
                                data-trail-id="{{ $trail->id }}"
                                data-trail-name="{{ strtolower($trail->name) }}"
                                onclick="focusTrail({{ $trail->id }})">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-medium text-gray-900 text-sm truncate trail-name mb-1">{{ $trail->name }}</h4>
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                @if($difficultyLevel <= 2) bg-green-100 text-green-700 ring-1 ring-green-600/20
                                                @elseif($difficultyLevel == 3) bg-blue-100 text-blue-700 ring-1 ring-blue-600/20
                                                @else bg-red-100 text-red-700 ring-1 ring-red-600/20
                                                @endif">
                                                Level {{ $trail->difficulty_level }}
                                            </span>
                                            <span class="text-xs text-gray-500 font-medium">{{ $trail->distance_km }} km</span>
                                        </div>
                                    </div>
                                    <div class="ml-3 flex-shrink-0">
                                        <div class="w-9 h-9 rounded-full flex items-center justify-center shadow-sm ring-2 ring-white trail-color-badge"
                                            data-difficulty="{{ $difficultyLevel }}">
                                            <span class="text-white text-xs font-bold">{{ substr($trail->name, 0, 1) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center">
                        <div class="text-gray-400 mb-2">
                            <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                        </div>
                        <p class="text-gray-500 text-sm">No trails in this network yet.</p>
                    </div>
                @endif
            </div>

            <!-- No Results Message -->
            <div id="no-results" class="hidden p-8 text-center border-t border-gray-200">
                <div class="text-gray-400 mb-2">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <p class="text-gray-500 text-sm font-medium">No trails found</p>
                <p class="text-gray-400 text-xs mt-1">Try adjusting your search</p>
            </div>
        </div>
    </div>
    <div class="trail-details-card" id="trail-details-card">
        <!-- Content will be dynamically inserted here -->
    </div>
    <!-- Map Type Selector  - Top Right -->
    <div class="absolute top-4 right-4 z-40">
        <div class="relative">
            <!-- Toggle Button -->
            <button id="layers-toggle" class="bg-white rounded-lg shadow-lg p-3 hover:bg-gray-50 transition-colors">
                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 0v10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2z"/>
                </svg>
            </button>
            
            <!-- Dropdown Menu -->
            <div id="layers-dropdown" class="hidden absolute top-full right-0 mt-2 bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden" style="min-width: 200px;">
                <div class="p-2">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 py-2">Map Style</div>
                    <div class="grid grid-cols-2 gap-2 mb-2">
                        <button class="layer-option-card active" data-map-type="standard">
                            <div class="layer-preview">
                                <img src="{{ asset('images/map-layers/standard.png') }}" 
                                    alt="Standard" class="w-full h-full object-cover">
                            </div>
                            <span class="layer-label">Standard</span>
                            <svg class="layer-checkmark" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        
                        <button class="layer-option-card" data-map-type="satellite">
                            <div class="layer-preview">
                                <img src="{{ asset('images/map-layers/satellite.png') }}" 
                                    alt="Satellite" class="w-full h-full object-cover">
                            </div>
                            <span class="layer-label">Satellite</span>
                            <svg class="layer-checkmark" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        
                        <button class="layer-option-card" data-map-type="terrain">
                            <div class="layer-preview">
                                <img src="{{ asset('images/map-layers/terrain.png') }}" 
                                    alt="Terrain" class="w-full h-full object-cover">
                            </div>
                            <span class="layer-label">Terrain</span>
                            <svg class="layer-checkmark" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <button class="layer-option-card" data-map-type="outdoors">
                            <div class="layer-preview">
                                <img src="{{ asset('images/map-layers/outdoor.png') }}" 
                                    alt="Outdoors" class="w-full h-full object-cover">
                            </div>
                            <span class="layer-label">Outdoors</span>
                            <svg class="layer-checkmark" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Legend - Responsive Positioning -->
    <div class="map-legend">
        <h3 class="text-xs font-semibold text-gray-900 uppercase tracking-wide mb-3">Difficulty</h3>
        <div class="space-y-2">
            <div class="flex items-center">
                <div class="w-3 h-3 rounded-full bg-green-500 mr-2 ring-2 ring-green-500/20"></div>
                <span class="text-xs text-gray-700 font-medium">Easy (1-2)</span>
            </div>
            <div class="flex items-center">
                <div class="w-3 h-3 rounded-full bg-blue-500 mr-2 ring-2 ring-blue-500/20"></div>
                <span class="text-xs text-gray-700 font-medium">Intermediate (3)</span>
            </div>
            <div class="flex items-center">
                <div class="w-3 h-3 rounded-full bg-red-500 mr-2 ring-2 ring-red-500/20"></div>
                <span class="text-xs text-gray-700 font-medium">Advanced (4-5)</span>
            </div>
        </div>
    </div>
</div>

<!-- Media Modal for Highlights -->
<div id="highlight-media-modal" class="hidden fixed inset-0 bg-black bg-opacity-90 z-[9999] flex items-center justify-center p-4">
    <div class="relative max-w-5xl w-full bg-white rounded-lg shadow-xl">
        <!-- Close button -->
        <button onclick="closeHighlightMediaModal()" 
                class="absolute top-4 right-4 z-10 bg-gray-900 bg-opacity-75 hover:bg-opacity-100 text-white rounded-full p-2 transition-all">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        
        <!-- Content container -->
        <div id="highlight-modal-content" class="p-4">
            <!-- Content will be dynamically inserted here -->
        </div>
        
        <!-- Caption -->
        <div id="highlight-modal-caption" class="px-6 pb-6 text-center text-gray-700">
            <!-- Caption will be dynamically inserted here -->
        </div>
    </div>
</div>

<script src="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.js"></script>

<script>
// Network data
const networkData = @json($network);
const trails = @json($network->trails);

// Difficulty color mapping
function getDifficultyColor(difficulty) {
    const colors = {
        1: '#22c55e',
        2: '#22c55e',
        3: '#3b82f6',
        4: '#ef4444',
        5: '#ef4444',
    };
    return colors[Math.floor(difficulty)] || '#6b7280';
}

// State
const trailCenterMarkers = {};
const waypointMarkers = {};
let selectedTrailId = null;
const facilityMarkers = [];
const highlightMarkers = [];
let _mapLoaded = false;

// Map styles
const mapStyles = {
    'standard':  'mapbox://styles/mapbox/standard',
    'satellite': 'mapbox://styles/mapbox/satellite-streets-v12',
    'terrain':   'mapbox://styles/mapbox/outdoors-v12',
    'outdoors':  'mapbox://styles/mapbox/navigation-day-v1',
};
let currentMapType = 'standard';

// Initialize Mapbox map
mapboxgl.accessToken = '{{ $mapboxToken }}';

const map = new mapboxgl.Map({
    container: 'network-map',
    style: mapStyles[currentMapType],
    center: [networkData.longitude, networkData.latitude],
    zoom: 13,
    attributionControl: false,
});

map.addControl(new mapboxgl.NavigationControl({ showCompass: false }), 'bottom-right');
map.addControl(new mapboxgl.AttributionControl({ compact: true }), 'bottom-left');

// ── Trail GeoJSON features ────────────────────────────────────────────────────
const trailFeatures = trails
    .filter(t => t.route_coordinates && t.route_coordinates.length > 0)
    .map(t => ({
        type: 'Feature',
        id: t.id,
        properties: { trailId: t.id, color: getDifficultyColor(t.difficulty_level), name: t.name },
        geometry: { type: 'LineString', coordinates: t.route_coordinates.map(c => [c[1], c[0]]) },
    }));

function initMapLayers() {
    // 3D terrain
    if (!map.getSource('mapbox-dem')) {
        map.addSource('mapbox-dem', { type: 'raster-dem', url: 'mapbox://mapbox.mapbox-terrain-dem-v1', tileSize: 512, maxzoom: 14 });
    }
    map.setTerrain({ source: 'mapbox-dem', exaggeration: 1.5 });

    // Arrow image
    const arrowSVG = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14"><polygon points="13,7 5,3 7,7 5,11" fill="white"/></svg>`;
    const arrowImg = new Image(14, 14);
    arrowImg.onload = () => { if (!map.hasImage('trail-arrow')) map.addImage('trail-arrow', arrowImg); };
    arrowImg.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(arrowSVG);

    // Trail routes source
    if (!map.getSource('trail-routes')) {
        map.addSource('trail-routes', { type: 'geojson', data: { type: 'FeatureCollection', features: trailFeatures } });
    } else {
        map.getSource('trail-routes').setData({ type: 'FeatureCollection', features: trailFeatures });
    }

    // Highlight outline (yellow glow when selected)
    if (!map.getLayer('trail-routes-outline')) {
        map.addLayer({
            id: 'trail-routes-outline',
            type: 'line',
            source: 'trail-routes',
            paint: {
                'line-color': '#f3fd44',
                'line-width': 15,
                'line-opacity': ['case', ['boolean', ['feature-state', 'selected'], false], 1, 0],
            },
            layout: { 'line-join': 'round', 'line-cap': 'round' },
        });
    }

    // Trail line
    if (!map.getLayer('trail-routes-line')) {
        map.addLayer({
            id: 'trail-routes-line',
            type: 'line',
            source: 'trail-routes',
            paint: {
                'line-color': ['get', 'color'],
                'line-width': ['case', ['boolean', ['feature-state', 'selected'], false], 5, 3],
            },
            layout: { 'line-join': 'round', 'line-cap': 'round' },
        });
    }

    // Direction arrows
    if (!map.getLayer('trail-routes-arrows')) {
        map.addLayer({
            id: 'trail-routes-arrows',
            type: 'symbol',
            source: 'trail-routes',
            layout: {
                'symbol-placement': 'line',
                'symbol-spacing': 120,
                'icon-image': 'trail-arrow',
                'icon-size': 1,
                'icon-allow-overlap': true,
                'icon-ignore-placement': true,
            },
        });
    }

    // Click trail line → focus
    map.on('click', 'trail-routes-line', (e) => {
        if (e.features.length > 0) focusTrail(e.features[0].properties.trailId);
    });
    map.on('mouseenter', 'trail-routes-line', () => { map.getCanvas().style.cursor = 'pointer'; });
    map.on('mouseleave', 'trail-routes-line', () => { map.getCanvas().style.cursor = ''; });

    // Restore selected state after style reload
    if (selectedTrailId !== null) {
        map.setFeatureState({ source: 'trail-routes', id: selectedTrailId }, { selected: true });
    }
}

map.on('load', () => {
    _mapLoaded = true;
    initMapLayers();

    // Add trail center dot markers
    trails.forEach(trail => {
        if (!trail.route_coordinates || !trail.route_coordinates.length) return;
        const midPoint = trail.route_coordinates[Math.floor(trail.route_coordinates.length / 2)];
        const color = getDifficultyColor(trail.difficulty_level);

        const el = document.createElement('div');
        el.className = 'trail-center-dot';
        el.style.backgroundColor = color;
        el.dataset.trailId = trail.id;
        el.title = trail.name;
        el.addEventListener('click', (e) => { e.stopPropagation(); focusTrail(trail.id); });

        trailCenterMarkers[trail.id] = new mapboxgl.Marker({ element: el, anchor: 'center' })
            .setLngLat([midPoint[1], midPoint[0]])
            .addTo(map);

        // Start marker
        const startCoord = trail.route_coordinates[0];
        const startEl = document.createElement('div');
        startEl.className = 'waypoint-start';
        startEl.style.cssText = 'width:12px;height:12px;border-radius:50%;background:#10b981;border:3px solid white;box-shadow:0 2px 6px rgba(16,185,129,0.5);';
        const startMarker = new mapboxgl.Marker({ element: startEl, anchor: 'center' })
            .setLngLat([startCoord[1], startCoord[0]])
            .setPopup(new mapboxgl.Popup({ offset: 10 }).setText('Start'))
            .addTo(map);

        // End marker
        const endCoord = trail.route_coordinates[trail.route_coordinates.length - 1];
        const endEl = document.createElement('div');
        endEl.style.cssText = 'width:12px;height:12px;border-radius:50%;background:#ef4444;border:3px solid white;box-shadow:0 2px 6px rgba(239,68,68,0.5);';
        const endMarker = new mapboxgl.Marker({ element: endEl, anchor: 'center' })
            .setLngLat([endCoord[1], endCoord[0]])
            .setPopup(new mapboxgl.Popup({ offset: 10 }).setText('End'))
            .addTo(map);

        waypointMarkers[trail.id] = [startMarker, endMarker];

        // Store trail details data
        if (!window.trailDetailsData) window.trailDetailsData = {};
        window.trailDetailsData[trail.id] = {
            id: trail.id, name: trail.name, trail_type: trail.trail_type,
            description: trail.description, distance_km: trail.distance_km,
            difficulty_level: trail.difficulty_level, elevation_gain: trail.elevation_gain_m,
            preview_photo: trail.preview_photo, photos: trail.photos,
        };
    });

    // Fit map to all trails
    const allCoords = trails.filter(t => t.route_coordinates && t.route_coordinates.length).flatMap(t => t.route_coordinates);
    if (allCoords.length) {
        const lngs = allCoords.map(c => c[1]);
        const lats = allCoords.map(c => c[0]);
        map.fitBounds([[Math.min(...lngs), Math.min(...lats)], [Math.max(...lngs), Math.max(...lats)]], { padding: 50, duration: 0 });
    }

    // Load facilities
    fetch('/api/facilities')
        .then(r => r.json())
        .then(facilities => {
            facilities.forEach(facility => {
                const el = document.createElement('div');
                el.style.cssText = 'background:white;color:#059669;padding:6px;border-radius:50%;font-size:18px;box-shadow:0 4px 12px rgba(0,0,0,0.15);border:3px solid #059669;width:38px;height:38px;display:flex;align-items:center;justify-content:center;cursor:pointer;';
                el.textContent = facility.icon;

                const popup = new mapboxgl.Popup({ maxWidth: '300px', offset: 20 }).setHTML(`
                    <div style="padding:12px;min-width:200px;">
                        <div style="display:flex;align-items:center;margin-bottom:8px;">
                            <span style="font-size:24px;margin-right:8px;">${facility.icon}</span>
                            <h3 style="margin:0;font-size:16px;font-weight:bold;color:#1f2937;">${facility.name}</h3>
                        </div>
                        <p style="margin:0 0 8px 0;font-size:12px;color:#6b7280;"><strong>Type:</strong> ${facility.facility_type.replace(/_/g, ' ').replace(/\w/g, l => l.toUpperCase())}</p>
                        ${facility.description ? `<p style="margin:0;font-size:13px;color:#4b5563;line-height:1.4;">${facility.description}</p>` : ''}
                    </div>
                `);

                const marker = new mapboxgl.Marker({ element: el, anchor: 'center' })
                    .setLngLat([facility.longitude, facility.latitude])
                    .setPopup(popup)
                    .addTo(map);
                facilityMarkers.push(marker);
            });
        })
        .catch(err => console.error('Error loading facilities:', err));

    // Load highlights
    fetch('/api/highlights')
        .then(r => r.json())
        .then(highlights => {
            highlights.forEach(highlight => {
                if (!highlight.coordinates || highlight.coordinates.length < 2) return;
                const el = document.createElement('div');
                el.style.cssText = `background-color:${highlight.color || '#8B5CF6'};padding:8px;border-radius:50%;font-size:20px;box-shadow:0 4px 12px rgba(0,0,0,0.15);border:2px solid white;width:40px;height:40px;display:flex;align-items:center;justify-content:center;cursor:pointer;`;
                el.textContent = highlight.icon || '📍';

                let mediaHTML = '';
                if (highlight.media && highlight.media.length > 0) {
                    const m = highlight.media[0];
                    if (m.media_type === 'photo') {
                        mediaHTML = `<img src="${m.url}" alt="${highlight.name}" class="w-full h-32 object-cover rounded-lg mb-2 cursor-pointer hover:opacity-90" onclick="event.stopPropagation();openHighlightMediaModal('${m.url}','photo','${highlight.name}')">`;
                    }
                }

                const popup = new mapboxgl.Popup({ maxWidth: '300px', offset: 20 }).setHTML(`
                    <div style="padding:12px;min-width:220px;max-width:280px;">
                        ${mediaHTML}
                        <div style="display:flex;align-items:center;margin-bottom:8px;">
                            <span style="font-size:24px;margin-right:8px;">${highlight.icon || '📍'}</span>
                            <h3 style="margin:0;font-size:16px;font-weight:bold;color:#1f2937;">${highlight.name}</h3>
                        </div>
                        ${highlight.description ? `<p style="margin:0 0 8px 0;font-size:13px;color:#4b5563;line-height:1.4;">${highlight.description}</p>` : ''}
                    </div>
                `);

                const marker = new mapboxgl.Marker({ element: el, anchor: 'center' })
                    .setLngLat([highlight.coordinates[1], highlight.coordinates[0]])
                    .setPopup(popup)
                    .addTo(map);
                highlightMarkers.push(marker);
            });
        })
        .catch(err => console.error('Error loading highlights:', err));
});

map.on('style.load', () => {
    if (_mapLoaded) initMapLayers();
});

// Close popups when clicking map background
map.on('click', () => {});

// Layers dropdown toggle
document.getElementById('layers-toggle').addEventListener('click', (e) => {
    e.stopPropagation();
    document.getElementById('layers-dropdown').classList.toggle('hidden');
});

document.addEventListener('click', (e) => {
    const dropdown = document.getElementById('layers-dropdown');
    const toggle = document.getElementById('layers-toggle');
    if (!dropdown.contains(e.target) && !toggle.contains(e.target)) {
        dropdown.classList.add('hidden');
    }
});

// Map layer switching
document.querySelectorAll('.layer-option-card').forEach(btn => {
    btn.addEventListener('click', () => {
        const mapType = btn.dataset.mapType;
        if (!mapType || !mapStyles[mapType]) return;
        currentMapType = mapType;
        map.setStyle(mapStyles[mapType]);
        document.querySelectorAll('.layer-option-card').forEach(b => b.classList.toggle('active', b.dataset.mapType === mapType));
        document.getElementById('layers-dropdown').classList.add('hidden');
    });
});

// Focus on trail when clicked in sidebar
window.focusTrail = function(trailId) {
    // Close sidebar on mobile
    if (window.innerWidth <= 768) {
        const sidebar = document.querySelector('.network-sidebar');
        const toggleBtn = document.getElementById('sidebar-toggle');
        if (sidebar && toggleBtn) {
            sidebar.classList.add('hidden-mobile');
            toggleBtn.classList.remove('hidden');
        }
    }

    // Deselect previous trail
    if (selectedTrailId !== null) {
        map.setFeatureState({ source: 'trail-routes', id: selectedTrailId }, { selected: false });
    }

    // Select new trail
    map.setFeatureState({ source: 'trail-routes', id: trailId }, { selected: true });
    selectedTrailId = trailId;

    // Fit bounds to selected trail
    const trail = trails.find(t => t.id == trailId);
    if (trail && trail.route_coordinates && trail.route_coordinates.length) {
        const coords = trail.route_coordinates;
        const lngs = coords.map(c => c[1]);
        const lats = coords.map(c => c[0]);
        const bounds = [[Math.min(...lngs), Math.min(...lats)], [Math.max(...lngs), Math.max(...lats)]];

        map.fitBounds(bounds, window.innerWidth <= 768
            ? { padding: 50, maxZoom: 16 }
            : { padding: { top: 50, left: 420, right: 50, bottom: 50 }, maxZoom: 16 }
        );
    }

    // Show trail details card
    setTimeout(() => showTrailDetailsCard(trailId), 300);
};

// Function to show trail details in card
function showTrailDetailsCard(trailId) {
    const trail = window.trailDetailsData[trailId];
    if (!trail) return;

    const card = document.getElementById('trail-details-card');
    const color = getDifficultyColor(trail.difficulty_level);
    const difficultyLevel = Math.floor(trail.difficulty_level);
    
    const featuredImage = trail.preview_photo || (trail.photos && trail.photos.length > 0 ? trail.photos[0].url : null);

    const cardContent = `
        <div class="relative">
            <!-- Close Button -->
            <button onclick="closeTrailDetailsCard()" class="absolute top-4 right-4 z-10 bg-white rounded-full p-2 shadow-lg hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <!-- Featured Image -->
            ${featuredImage ? `
                <div class="rounded-t-xl overflow-hidden">
                    <img src="${featuredImage}" 
                        alt="${trail.name}"
                        class="w-full h-48 object-cover"
                        onerror="this.parentElement.innerHTML='<div class=\\'w-full h-48 bg-gradient-to-br from-green-500 to-blue-500 flex items-center justify-center\\'><svg class=\\'w-12 h-12 text-white opacity-75\\' fill=\\'none\\' stroke=\\'currentColor\\' viewBox=\\'0 0 24 24\\'><path stroke-linecap=\\'round\\' stroke-linejoin=\\'round\\' stroke-width=\\'2\\' d=\\'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z\\'></path></svg></div>'">
                </div>
            ` : `
                <div class="w-full h-48 rounded-t-xl bg-gradient-to-br from-green-500 to-blue-500 flex items-center justify-center">
                    <svg class="w-12 h-12 text-white opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
            `}

            <!-- Content -->
            <div class="p-6">
                <!-- Trail Name and Type -->
                <div class="mb-4">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">${trail.name}</h3>
                    <p class="text-sm text-gray-600 capitalize">
                        ${trail.trail_type ? trail.trail_type.replace(/-/g, ' ') : 'Trail'}
                    </p>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Distance</div>
                        <div class="text-lg font-bold text-gray-900">${trail.distance_km} km</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Difficulty</div>
                        <div>
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                ${difficultyLevel <= 2 ? 'bg-green-100 text-green-700 ring-1 ring-green-600/20' : ''}
                                ${difficultyLevel == 3 ? 'bg-blue-100 text-blue-700 ring-1 ring-blue-600/20' : ''}
                                ${difficultyLevel >= 4 ? 'bg-red-100 text-red-700 ring-1 ring-red-600/20' : ''}">
                                Level ${trail.difficulty_level}
                            </span>
                        </div>
                    </div>
                    ${trail.elevation_gain ? `
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-200 col-span-2">
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Elevation Gain</div>
                            <div class="text-lg font-bold text-gray-900">${trail.elevation_gain} m</div>
                        </div>
                    ` : ''}
                </div>

                <!-- Description -->
                ${trail.description ? `
                    <div class="mb-4">
                        <h4 class="text-sm font-semibold text-gray-900 mb-2">Description</h4>
                        <p class="text-sm text-gray-600 leading-relaxed">${trail.description}</p>
                    </div>
                ` : ''}

                <!-- Action Buttons -->
                <div class="space-y-2">
                    <a href="/trails/${trail.id}" 
                       class="block w-full text-center bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-semibold transition-colors">
                        View Full Trail Details
                    </a>
                    <button onclick="closeTrailDetailsCard()" 
                            class="w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-3 rounded-lg font-semibold transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    `;

    card.innerHTML = cardContent;
    card.classList.add('visible');
}

// Function to close trail details card
function closeTrailDetailsCard() {
    const card = document.getElementById('trail-details-card');
    card.classList.remove('visible');
    // Keep the trail selected - don't deselect it
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.trail-color-badge').forEach(badge => {
        const difficulty = parseInt(badge.dataset.difficulty);
        const color = getDifficultyColor(difficulty);
        badge.style.backgroundColor = color;
    });
});

// Search trails function
window.searchTrails = function() {
    const searchTerm = document.getElementById('trail-search').value.toLowerCase().trim();
    const trailItems = document.querySelectorAll('.trail-item');
    const trailsContainer = document.getElementById('trails-container');
    const noResults = document.getElementById('no-results');
    let visibleCount = 0;

    trailItems.forEach(item => {
        const trailName = item.dataset.trailName;
        
        if (trailName.includes(searchTerm)) {
            item.style.display = '';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });

    // Show/hide no results message
    if (visibleCount === 0 && searchTerm !== '') {
        if (noResults) noResults.classList.remove('hidden');
        if (trailsContainer) trailsContainer.classList.add('hidden');
    } else {
        if (noResults) noResults.classList.add('hidden');
        if (trailsContainer) trailsContainer.classList.remove('hidden');
    }
};

// Mobile sidebar toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.network-sidebar');
    const toggleBtn = document.getElementById('sidebar-toggle');
    const closeBtn = document.getElementById('sidebar-close');
    
    if (toggleBtn && sidebar && closeBtn) {
        // Function to check if we're on mobile
        function isMobile() {
            return window.innerWidth <= 768;
        }
        
        // Initialize - on mobile: sidebar hidden, toggle visible
        if (isMobile()) {
            sidebar.classList.add('hidden-mobile');
            toggleBtn.classList.remove('hidden');
        } else {
            // On desktop: sidebar visible, toggle hidden
            sidebar.classList.remove('hidden-mobile');
            toggleBtn.classList.add('hidden');
        }
        
        // Show sidebar when hamburger button is clicked
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.remove('hidden-mobile');
            toggleBtn.classList.add('hidden');
        });
        
        // Hide sidebar when close button is clicked
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (isMobile()) {
                // On mobile: close sidebar and show hamburger
                sidebar.classList.add('hidden-mobile');
                toggleBtn.classList.remove('hidden');
            } else {
                // On desktop: navigate to index
                window.location.href = "{{ route('trail-networks.index') }}";
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (!isMobile()) {
                // Switching to desktop view
                sidebar.classList.remove('hidden-mobile');
                toggleBtn.classList.add('hidden');
            }
        });
    }
});

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

function getVideoEmbedUrl(videoUrl) {
    // YouTube
    const youtubeMatch = videoUrl.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/);
    if (youtubeMatch) {
        return `https://www.youtube.com/embed/${youtubeMatch[1]}`;
    }
    
    // Vimeo
    const vimeoMatch = videoUrl.match(/vimeo\.com\/(\d+)/);
    if (vimeoMatch) {
        return `https://player.vimeo.com/video/${vimeoMatch[1]}`;
    }
    
    return null;
}

// Highlight Media Modal Functions
function openHighlightMediaModal(url, type, caption) {
    const modal = document.getElementById('highlight-media-modal');
    const content = document.getElementById('highlight-modal-content');
    const captionEl = document.getElementById('highlight-modal-caption');
    
    if (type === 'photo') {
        content.innerHTML = `<img src="${url}" alt="${caption}" class="w-full h-auto max-h-[70vh] object-contain rounded-lg">`;
    } else if (type === 'video') {
        const embedUrl = getVideoEmbedUrl(url);
        
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
        } else {
            content.innerHTML = `<p class="text-red-500 text-center p-4">Unable to load video</p>`;
        }
    }
    
    captionEl.textContent = caption || '';
    modal.classList.remove('hidden');
}

function closeHighlightMediaModal() {
    const modal = document.getElementById('highlight-media-modal');
    const content = document.getElementById('highlight-modal-content');
    
    modal.classList.add('hidden');
    content.innerHTML = ''; // Clear content to stop video playback
}

// Close modal when clicking outside
document.getElementById('highlight-media-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeHighlightMediaModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeHighlightMediaModal();
    }
});
</script>
@endsection