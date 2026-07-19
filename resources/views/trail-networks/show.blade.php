@extends('layouts.public')

@section('title', $network->network_name)

@push('meta')
<!-- Open Graph / Facebook Meta Tags -->
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="{{ $network->network_name }} — Xplore Smithers">
<meta property="og:description" content="{{ Str::limit($network->description, 200) }}">
@if($network->image)
<meta property="og:image" content="{{ url('storage/'.$network->image) }}">
<meta property="og:image:secure_url" content="{{ url('storage/'.$network->image) }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ $network->network_name }}">
@endif
<meta property="og:site_name" content="Xplore Smithers">

<!-- Twitter Card Meta Tags -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{{ url()->current() }}">
<meta name="twitter:title" content="{{ $network->network_name }} — Xplore Smithers">
<meta name="twitter:description" content="{{ Str::limit($network->description, 200) }}">
@if($network->image)
<meta name="twitter:image" content="{{ url('storage/'.$network->image) }}">
<meta name="twitter:image:alt" content="{{ $network->network_name }}">
@endif
@endpush

@section('content')

@include('partials.sponsor-banners', ['network' => $network])

<link href="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.css" rel="stylesheet">

<style>
    #network-map {
        flex: 1;
        height: 100%;
        min-width: 0;
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
        position: relative;
        order: -1;
        z-index: 30;
        width: 25%;
        min-width: 320px;
        max-width: 400px;
        flex-shrink: 0;
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background: white;
        border-right: 1px solid #e5e7eb;
        transition: transform 0.3s ease-in-out;
    }

    @media (max-width: 768px) {
        .network-sidebar {
            position: fixed;
            top: 80px;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            max-width: none;
            min-width: 0;
            z-index: 9999;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .network-sidebar.hidden-mobile {
            transform: translateX(-100%);
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
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
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
        flex-shrink: 0;
        background: white;
        z-index: 10;
        border-bottom: 1px solid #e5e7eb;
    }

    /* Legend positioning — sits at the bottom-right of the map area, beside the zoom control */
    .map-legend {
        position: absolute;
        bottom: .6rem;
        right: 3rem;
        z-index: 30;
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        padding: 0.75rem 1rem;
        max-width: 16rem;
    }

    /* When a sponsor banner occupies the bottom, lift the legend just above it */
    .map-legend--top {
        bottom: 5.5rem;
        right: 3rem;
    }

    .legend-label {
        font-size: 0.75rem;
        font-weight: 500;
        color: #374151;
    }

    @media (max-width: 768px) {
        .map-legend {
            bottom: 1rem;
            left: 1rem;
            right: auto;
            padding: 0.4rem 0.6rem;
        }

        .map-legend h3 {
            font-size: 0.6rem;
            margin-bottom: 0.3rem;
        }

        .legend-label {
            font-size: 0.65rem;
        }

        .map-legend .space-y-1 > * + * {
            margin-top: 0.15rem;
        }

        .map-legend--top {
            bottom: 5.5rem;
            left: 1rem;
            right: auto;
        }
    }

    /* Trail Details Card — biz-panel style */
    .trail-details-card {
        position: absolute;
        top: 1rem;
        bottom: 1rem;
        left: calc(clamp(320px, 25%, 400px) + 1rem);
        z-index: 999;
        width: 20rem;
        background: #fff;
        border-radius: 0.5rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.18);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        font-family: 'Inter', system-ui, sans-serif;
        opacity: 0;
        transform: translateX(-20px);
        pointer-events: none;
        transition: opacity 0.25s ease, transform 0.25s ease;
    }

    .trail-details-card.visible {
        opacity: 1;
        transform: translateX(0);
        pointer-events: auto;
    }

    @media (max-width: 768px) {
        .trail-details-card {
            display: none !important;
        }
    }

    .tdc-hero {
        position: relative;
        flex-shrink: 0;
        width: 100%;
        height: 200px;
        overflow: hidden;
        background: linear-gradient(135deg, #166534, #22c55e);
    }

    .tdc-hero img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .tdc-hero-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }
    .tdc-hero-placeholder img {
        max-width: 70%;
        max-height: 70%;
        object-fit: contain;
        filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.25));
    }

    .tdc-close {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(255, 255, 255, 0.92);
        border: none;
        cursor: pointer;
        border-radius: 50%;
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18);
        color: #374151;
        transition: background 0.15s;
    }

    .tdc-close:hover { background: #f3f4f6; }

    .tdc-body {
        flex: 1;
        overflow-y: auto;
        padding: 20px 20px 24px;
        scrollbar-width: thin;
        scrollbar-color: transparent transparent;
        transition: scrollbar-color 0.25s ease;
    }
    .tdc-body:hover { scrollbar-color: rgba(0,0,0,0.28) transparent; }
    .tdc-body::-webkit-scrollbar { width: 8px; }
    .tdc-body::-webkit-scrollbar-track { background: transparent; }
    .tdc-body::-webkit-scrollbar-thumb {
        background-color: transparent;
        border-radius: 999px;
        border: 2px solid transparent;
        background-clip: padding-box;
        transition: background-color 0.25s ease;
    }
    .tdc-body:hover::-webkit-scrollbar-thumb { background-color: rgba(0,0,0,0.28); }

    .tdc-name {
        font-size: 22px;
        font-weight: 800;
        color: #111827;
        line-height: 1.2;
        margin: 0 0 4px;
        padding-right: 8px;
    }

    .tdc-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .tdc-type {
        font-size: 13px;
        font-weight: 600;
        color: #16a34a;
    }

    .tdc-dot {
        color: #d1d5db;
        font-size: 16px;
        line-height: 1;
    }

    .tdc-difficulty-badge {
        font-size: 12px;
        font-weight: 700;
        padding: 2px 10px;
        border-radius: 999px;
    }

    .tdc-actions {
        display: flex;
        gap: 10px;
        margin-bottom: 18px;
    }

    .tdc-action-btn {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        padding: 10px 8px;
        border-radius: 12px;
        background: #f0fdf4;
        border: 1.5px solid #bbf7d0;
        cursor: pointer;
        text-decoration: none;
        font-family: inherit;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        transition: background 0.15s, border-color 0.15s, box-shadow 0.15s;
    }

    .tdc-action-btn:hover {
        background: #dcfce7;
        border-color: #86efac;
        box-shadow: 0 2px 8px rgba(22, 163, 74, 0.15);
    }

    .tdc-action-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #16a34a;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .tdc-action-icon svg { width: 18px; height: 18px; }

    .tdc-action-label {
        font-size: 11px;
        font-weight: 600;
        color: #166534;
        text-align: center;
        line-height: 1.2;
    }

    .tdc-divider {
        border: none;
        border-top: 1px solid #f3f4f6;
        margin: 0 0 16px;
    }

    .tdc-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 16px;
    }

    .tdc-stat {
        border-radius: 10px;
        padding: 12px;
        text-align: center;
    }
    .tdc-stat-value {
        font-size: 20px;
        font-weight: 800;
        line-height: 1.1;
    }
    .tdc-stat-label {
        font-size: 11px;
        color: #6b7280;
        margin-top: 2px;
    }

    .tdc-description {
        font-size: 13px;
        color: #4b5563;
        line-height: 1.6;
        margin: 0;
    }
</style>

<div class="relative flex h-[calc(100vh-80px)] max-md:h-[calc(100dvh-80px)] overflow-hidden">
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
                <div class="flex items-center flex-shrink-0 ml-3 gap-2">
                    <button type="button" data-share-open="network-share" title="Share this network"
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                        </svg>
                    </button>
                    <button id="sidebar-close" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
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
        <div class="bg-white flex-1 min-h-0 flex flex-col">
            <!-- Scrollable Trails Container -->
            <div class="overflow-y-auto flex-1 min-h-0">
                @if($network->video_url)
                    <div class="p-3 pb-0">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-2">Video</h3>
                        @include('partials.video-embed', ['model' => $network, 'embedId' => 'network-video'])
                    </div>
                @endif
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
                                            @php
                                                $dl = $difficultyLevel;
                                                $dlLabel = match(true) {
                                                    $dl == 1 => '🟢 Green',
                                                    $dl == 2 => '🔵 Blue',
                                                    $dl == 3 => '⚫ Black Diamond',
                                                    $dl == 4 => '⚫⚫ Double Black Diamond',
                                                    $dl >= 5 => '🔴 Proline',
                                                    default  => "Level {$dl}",
                                                };
                                                $dlClass = match(true) {
                                                    $dl == 1 => 'bg-green-100 text-green-700 ring-1 ring-green-600/20',
                                                    $dl == 2 => 'bg-blue-100 text-blue-700 ring-1 ring-blue-600/20',
                                                    $dl == 3 => 'bg-gray-900 text-white ring-1 ring-gray-700',
                                                    $dl == 4 => 'bg-gray-950 text-white ring-1 ring-gray-700',
                                                    $dl >= 5 => 'bg-red-100 text-red-700 ring-1 ring-red-600/20',
                                                    default  => 'bg-gray-100 text-gray-700',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $dlClass }}">
                                                {{ $dlLabel }}
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

    <!-- Mobile Bottom Card (mobile only, same style as main map) -->
    <div id="network-mobile-card" class="hidden fixed bottom-0 inset-x-0 z-[1000] bg-white md:hidden"
         style="border-radius:16px 16px 0 0;box-shadow:0 -4px 24px rgba(0,0,0,0.18);"
         ontouchstart="event.stopPropagation()" onclick="event.stopPropagation()">
        <div class="w-10 h-1 bg-gray-300 rounded-full mx-auto mt-3"></div>
        <div class="flex items-center px-4 pt-3 pb-2 gap-3">
            <div class="w-[68px] h-[68px] rounded-xl overflow-hidden flex-shrink-0 bg-gray-100">
                <img id="nmc-img" src="" alt="" class="hidden w-full h-full object-cover">
                <div id="nmc-placeholder" class="w-full h-full flex items-center justify-center text-2xl"></div>
            </div>
            <div class="flex-1 min-w-0">
                <h3 id="nmc-name" class="font-bold text-gray-900 text-[15px] leading-tight truncate"></h3>
                <div id="nmc-meta" class="flex items-center gap-1.5 mt-1 flex-wrap"></div>
                <p id="nmc-stats" class="text-xs text-gray-500 mt-0.5"></p>
            </div>
        </div>
        <div id="nmc-actions" class="flex gap-2 px-4 pb-5 pt-1"></div>
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
// ── XploreSmithers Pro gating ────────────────────────────────────────────
// In-app: window.Offline drives entitlement + native paywall.
// Browser: window.xsWeb (injected by the layout) carries the server Pro flag;
// non-subscribers are sent to the /pro web paywall.
window.xsInApp = function () {
    return !!(window.Offline && window.Offline.isAvailable && window.Offline.isAvailable());
};
window.xsIsPro = function () {
    if (window.xsInApp()) {
        try { return JSON.parse(window.Offline.subscriptionStatus()).active === true; }
        catch (e) { return false; }
    }
    return !!(window.xsWeb && window.xsWeb.entitled);
};
window.xsRequirePro = function (featureKey, onAllowed) {
    if (window.xsIsPro()) {
        if (typeof onAllowed === 'function') { onAllowed(); }
        return;
    }
    if (window.xsInApp()) {
        try { window.Offline.openPaywall(featureKey); } catch (e) {}
    } else if (typeof window.xsShowProModal === 'function') {
        window.xsShowProModal(featureKey);
    } else {
        window.location.href = (window.xsWeb && window.xsWeb.proUrl) ? window.xsWeb.proUrl : '/pro';
    }
};
window.gateProFeature = window.xsRequirePro; // back-compat alias

// Network data
const networkData = @json($network);
const trails = @json($network->trails);
const facilitiesData = @json($facilities);
const hasSponsors = {{ $network->activeSponsors->count() > 0 ? 'true' : 'false' }};
const isMobileDevice = () => window.innerWidth <= 768;

// Difficulty color mapping
function getDifficultyColor(difficulty) {
    const colors = {
        1: '#22c55e', // Green
        2: '#3b82f6', // Blue
        3: '#1a1a1a', // Black Diamond
        4: '#1a1a1a', // Double Black Diamond
        5: '#ef4444', // Proline (red)
    };
    return colors[Math.floor(difficulty)] || '#6b7280';
}

function getDifficultyLabel(difficulty) {
    const labels = {
        1: 'Green',
        2: 'Blue',
        3: 'Black Diamond',
        4: 'Double Black Diamond',
        5: 'Proline',
    };
    return labels[Math.floor(difficulty)] || `Level ${difficulty}`;
}

function getDifficultyStyle(difficulty) {
    const styles = {
        1: 'background:#dcfce7;color:#166534;',
        2: 'background:#dbeafe;color:#1d4ed8;',
        3: 'background:#1a1a1a;color:#ffffff;',
        4: 'background:#111827;color:#ffffff;',
        5: 'background:#fee2e2;color:#991b1b;',
    };
    return styles[Math.floor(difficulty)] || 'background:#f3f4f6;color:#374151;';
}

// State
const trailCenterMarkers = {};
const waypointMarkers = {};
let selectedTrailId = null;
const facilityMarkers = [];
const FACILITY_NORMAL_STYLE  = 'width:22px;height:22px;background:#fff;border:1.5px solid #6b7280;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;cursor:pointer;box-shadow:0 1px 4px rgba(0,0,0,0.2);transition:box-shadow 0.15s ease,border-color 0.15s ease;';
const FACILITY_SELECTED_STYLE = 'width:22px;height:22px;background:#fff;border:2.5px solid #eab308;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;cursor:pointer;box-shadow:0 0 0 4px rgba(234,179,8,0.3),0 2px 8px rgba(0,0,0,0.3);transform:scale(1.25);transition:box-shadow 0.15s ease,border-color 0.15s ease;';
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

mapboxgl.accessToken = '{{ $mapboxToken }}';

const map = new mapboxgl.Map({
    container: 'network-map',
    style: mapStyles[currentMapType],
    center: [networkData.longitude || -127.2, networkData.latitude || 54.7],
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
    // Terrain intentionally disabled — 3D terrain causes HTML marker drift during pan.
    // This page uses a flat 2D view with no pitch toggle.

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

    // Highlight outline (yellow glow when selected or hovered)
    if (!map.getLayer('trail-routes-outline')) {
        map.addLayer({
            id: 'trail-routes-outline',
            type: 'line',
            source: 'trail-routes',
            paint: {
                'line-color': ['case', ['boolean', ['feature-state', 'selected'], false], '#f3fd44', 'rgba(255,255,255,0.6)'],
                'line-width': ['case', ['boolean', ['feature-state', 'selected'], false], 8, 6],
                'line-opacity': ['case',
                    ['boolean', ['feature-state', 'selected'], false], 1,
                    ['boolean', ['feature-state', 'hovered'], false], 0.7,
                    0
                ],
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
                'line-width': ['case',
                    ['boolean', ['feature-state', 'selected'], false], 3.5,
                    ['boolean', ['feature-state', 'hovered'], false], 3,
                    1.5
                ],
            },
            layout: { 'line-join': 'round', 'line-cap': 'round' },
        });
    }

    // Invisible wide hit area for easier clicking
    if (!map.getLayer('trail-routes-hit')) {
        map.addLayer({
            id: 'trail-routes-hit',
            type: 'line',
            source: 'trail-routes',
            paint: { 'line-color': 'transparent', 'line-width': 20 },
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

    // Click / hover handlers (registered once only)
    if (!window._trailLineClickRegistered) {
        window._trailLineClickRegistered = true;
        let hoveredTrailId = null;

        // Click on hit area → show details (skip if a facility was just clicked)
        ['trail-routes-hit', 'trail-routes-line'].forEach(layer => {
            map.on('click', layer, (e) => {
                if (window._facilityClicked) return;
                if (!e.features.length) return;
                focusTrail(e.features[0].properties.trailId);
            });
        });

        // Hover: set hovered feature state (skip if cursor is over a facility marker)
        map.on('mousemove', 'trail-routes-hit', (e) => {
            if (window._facilityClicked) return;
            map.getCanvas().style.cursor = 'pointer';
            if (!e.features.length) return;
            const id = e.features[0].id;
            if (hoveredTrailId !== null && hoveredTrailId !== id) {
                map.setFeatureState({ source: 'trail-routes', id: hoveredTrailId }, { hovered: false });
            }
            hoveredTrailId = id;
            map.setFeatureState({ source: 'trail-routes', id: hoveredTrailId }, { hovered: true });
        });

        map.on('mouseleave', 'trail-routes-hit', () => {
            map.getCanvas().style.cursor = '';
            if (hoveredTrailId !== null) {
                map.setFeatureState({ source: 'trail-routes', id: hoveredTrailId }, { hovered: false });
                hoveredTrailId = null;
            }
        });
    }

    // Restore selected state after style reload
    if (selectedTrailId !== null) {
        map.setFeatureState({ source: 'trail-routes', id: selectedTrailId }, { selected: true });
    }
}

// Fit the camera to cover every trail (and facility) in the network
function fitNetworkBounds(options = {}) {
    const bounds = new mapboxgl.LngLatBounds();
    let hasPoint = false;

    trailFeatures.forEach(f => {
        f.geometry.coordinates.forEach(c => { bounds.extend(c); hasPoint = true; });
    });
    facilitiesData.forEach(facility => {
        if (facility.latitude && facility.longitude) {
            bounds.extend([facility.longitude, facility.latitude]);
            hasPoint = true;
        }
    });

    if (!hasPoint) return;

    const padding = window.innerWidth <= 768
        ? { padding: 40, maxZoom: 15, duration: 0 }
        : { padding: { top: 60, left: 420, right: 60, bottom: 60 }, maxZoom: 15, duration: 0 };
    map.fitBounds(bounds, { ...padding, ...options });
}

map.on('load', () => {
    _mapLoaded = true;
    initMapLayers();

    // Fit the camera to the whole network on load
    fitNetworkBounds();

    trails.forEach(trail => {
        if (!trail.route_coordinates || !trail.route_coordinates.length) return;

        // Store trail details data
        if (!window.trailDetailsData) window.trailDetailsData = {};
        window.trailDetailsData[trail.id] = {
            id: trail.id, name: trail.name, trail_type: trail.trail_type,
            description: trail.description, distance_km: trail.distance_km,
            difficulty_level: trail.difficulty_level, elevation_gain: trail.elevation_gain_m,
            preview_photo: trail.preview_photo, photos: trail.photos,
            route_coordinates: trail.route_coordinates,
        };
    });

    // Facility markers
    window._selectedFacilityEl = null;

    facilitiesData.forEach(facility => {
        if (!facility.latitude || !facility.longitude) return;
        // Outer element is owned by Mapbox (it writes the positioning transform
        // here every frame). The inner element carries all visual styling, the
        // selected-state scale, and transitions — Mapbox never touches it, so
        // transitioning its transform no longer fights the map positioning.
        const el = document.createElement('div');
        el.className = 'facility-marker';
        const inner = document.createElement('div');
        inner.className = 'facility-marker-inner';
        inner.style.cssText = FACILITY_NORMAL_STYLE;
        if (facility.icon_image_url) {
            inner.style.overflow = 'hidden';
            inner.innerHTML = `<img src="${facility.icon_image_url}" alt="" style="width:16px;height:16px;object-fit:cover;border-radius:50%;">`;
        } else {
            inner.textContent = facility.icon || '📍';
        }
        el.appendChild(inner);
        el.addEventListener('click', (e) => {
            e.stopPropagation();
            // Guard: prevent the map trail-click handler from also firing
            window._facilityClicked = true;
            setTimeout(() => { window._facilityClicked = false; }, 50);

            // Deselect any active trail line
            if (selectedTrailId !== null) {
                map.setFeatureState({ source: 'trail-routes', id: selectedTrailId }, { selected: false });
                selectedTrailId = null;
            }

            // Remove highlight from previously selected marker
            if (window._selectedFacilityEl && window._selectedFacilityEl !== inner) {
                window._selectedFacilityEl.style.cssText = FACILITY_NORMAL_STYLE;
            }
            // Apply highlight to clicked marker
            inner.style.cssText = FACILITY_SELECTED_STYLE;
            window._selectedFacilityEl = inner;
            showFacilityDetailsCard(facility);
        });
        const marker = new mapboxgl.Marker({ element: el, anchor: 'center' })
            .setLngLat([facility.longitude, facility.latitude])
            .addTo(map);
        facilityMarkers.push(marker);
    });

    // Auto-focus a specific trail when navigated here with ?trail={id}
    const requestedTrailId = new URLSearchParams(window.location.search).get('trail');
    if (requestedTrailId) {
        setTimeout(() => window.focusTrail(parseInt(requestedTrailId, 10)), 300);
    }
});


map.on('style.load', () => {
    if (_mapLoaded) initMapLayers();
});

// Close details card when clicking empty map space
map.on('click', (e) => {
    if (!map.queryRenderedFeatures(e.point, { layers: ['trail-routes-hit', 'trail-routes-line'] }).length) {
        closeTrailDetailsCard();
        closeNetworkMobileCard();
    }
});

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

    // Fit map to selected trail bounds
    const trail = trails.find(t => t.id == trailId);
    if (trail && trail.route_coordinates && trail.route_coordinates.length) {
        const coords = trail.route_coordinates;
        const lngs = coords.map(c => c[1]);
        const lats = coords.map(c => c[0]);
        const bounds = [[Math.min(...lngs), Math.min(...lats)], [Math.max(...lngs), Math.max(...lats)]];
        map.fitBounds(bounds, window.innerWidth <= 768
            ? { padding: 50, maxZoom: 16, duration: 800 }
            : { padding: { top: 50, left: 420, right: 50, bottom: 50 }, maxZoom: 16, duration: 800 }
        );
    }

    // Show trail details card after camera settles
    setTimeout(() => showTrailDetailsCard(trailId), 300);
};

// ── Mobile bottom card helpers ────────────────────────────────────────────────
function getSponsorHeight() {
    const stack = document.querySelector('.sponsor-bnr-stack');
    if (!stack) return 0;
    const visible = Array.from(stack.querySelectorAll('.sponsor-bnr')).filter(b => b.style.display !== 'none');
    return visible.length > 0 ? stack.offsetHeight : 0;
}

function updateMobileCardPosition() {
    const card = document.getElementById('network-mobile-card');
    if (!card || card.classList.contains('hidden')) return;
    card.style.bottom = getSponsorHeight() + 'px';
}

function showNetworkMobileCard({ imageUrl, placeholderIcon, placeholderBg, name, metaHtml, statsText, actionsHtml }) {
    const card = document.getElementById('network-mobile-card');
    const img = document.getElementById('nmc-img');
    const placeholder = document.getElementById('nmc-placeholder');
    if (imageUrl) {
        img.src = imageUrl;
        img.classList.remove('hidden');
        placeholder.classList.add('hidden');
    } else {
        img.classList.add('hidden');
        placeholder.classList.remove('hidden');
        placeholder.textContent = placeholderIcon || '🥾';
        placeholder.style.background = placeholderBg || 'linear-gradient(135deg,#166534,#22c55e)';
    }
    document.getElementById('nmc-name').textContent = name;
    document.getElementById('nmc-meta').innerHTML = metaHtml || '';
    document.getElementById('nmc-stats').textContent = statsText || '';
    document.getElementById('nmc-actions').innerHTML = actionsHtml || '';
    card.style.bottom = getSponsorHeight() + 'px';
    card.classList.remove('hidden');
}

function closeNetworkMobileCard() {
    document.getElementById('network-mobile-card').classList.add('hidden');
}

function showMobileTrailCard(trailId) {
    const trail = window.trailDetailsData[trailId];
    if (!trail) return;
    const btnClass = 'flex-1 flex items-center justify-center gap-1.5 py-2 px-2 rounded-lg text-xs font-semibold border border-gray-200 bg-gray-50 text-gray-700 transition-colors';
    const diffLevel = Math.floor(trail.difficulty_level);
    const diffColor = getDifficultyColor(diffLevel);
    const diffLabel = getDifficultyLabel(diffLevel);
    const trailType = trail.trail_type ? trail.trail_type.replace(/[-_]/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) : 'Trail';
    const parts = [];
    if (trail.distance_km) parts.push(`${parseFloat(trail.distance_km).toFixed(1)} km`);
    if (trail.elevation_gain) parts.push(`${trail.elevation_gain}m gain`);
    showNetworkMobileCard({
        imageUrl: trail.preview_photo || null,
        placeholderIcon: '🥾',
        placeholderBg: 'linear-gradient(135deg,#166534,#22c55e)',
        name: trail.name,
        metaHtml: `<span style="width:9px;height:9px;border-radius:50%;background:${diffColor};display:inline-block;flex-shrink:0;"></span><span style="font-size:12px;font-weight:600;color:#374151;">${diffLabel}</span><span style="color:#d1d5db;font-size:11px;">·</span><span style="font-size:12px;color:#6b7280;">${trailType}</span>`,
        statsText: parts.join(' · '),
        actionsHtml: `<button type="button" onclick="focusTrail(${trail.id})" class="${btnClass}"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 9m0 8V9m0 0V7"/></svg>Route</button><a href="/trails/${trail.id}" class="${btnClass}"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Details</a>`,
    });
}

function showMobileFacilityCard(facility) {
    const btnClass = 'flex-1 flex items-center justify-center gap-1.5 py-2 px-2 rounded-lg text-xs font-semibold border border-gray-200 bg-gray-50 text-gray-700 transition-colors';
    const typeLabel = facility.facility_type.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    showNetworkMobileCard({
        imageUrl: facility.icon_image_url || null,
        placeholderIcon: facility.icon || '📍',
        placeholderBg: 'linear-gradient(135deg,#134e4a,#0d9488)',
        name: facility.name,
        metaHtml: `<span style="font-size:12px;font-weight:600;color:#0d9488;">${typeLabel}</span>`,
        statsText: facility.description || '',
        actionsHtml: `<button type="button" onclick="map.easeTo({center:[${facility.longitude},${facility.latitude}],zoom:17,duration:800})" class="${btnClass}"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>Location</button>`,
    });
}
// ─────────────────────────────────────────────────────────────────────────────

// Function to show trail details in card
function showTrailDetailsCard(trailId) {
    if (isMobileDevice()) { showMobileTrailCard(trailId); return; }
    const trail = window.trailDetailsData[trailId];
    if (!trail) return;

    const card = document.getElementById('trail-details-card');
    const difficultyLevel = Math.floor(trail.difficulty_level);
    const featuredImage = trail.preview_photo || (trail.photos && trail.photos.length > 0 ? trail.photos[0].url : null);
    const trailType = trail.trail_type ? trail.trail_type.replace(/[-_]/g, ' ') : 'Trail';

    const difficultyClass = getDifficultyStyle(difficultyLevel);
    const difficultyLabel = getDifficultyLabel(difficultyLevel);

    const stats = [
        { value: trail.distance_km ?? '—', label: 'Distance (km)', bg: '#eff6ff', color: '#2563eb' },
        { value: trail.elevation_gain ?? 0, label: 'Elevation (m)', bg: '#f0fdf4', color: '#16a34a' },
    ];

    const heroHTML = featuredImage
        ? `<img src="${featuredImage}" alt="${trail.name}">`
        : `<div class="tdc-hero-placeholder"><img src="/images/xplore-smithers-logo.png" alt="Xplore Smithers"></div>`;

    card.innerHTML = `
        <div class="tdc-hero">
            ${heroHTML}
            <button class="tdc-close" onclick="closeTrailDetailsCard()" aria-label="Close">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="tdc-body">
            <h2 class="tdc-name">${trail.name}</h2>
            <div class="tdc-meta">
                <span class="tdc-type">🥾 ${trailType.replace(/\b\w/g, c => c.toUpperCase())}</span>
                <span class="tdc-dot">·</span>
                <span class="tdc-difficulty-badge" style="${difficultyClass}">${difficultyLabel}</span>
            </div>
            <div class="tdc-actions">
                <button type="button" onclick="focusTrail(${trail.id})" class="tdc-action-btn">
                    <div class="tdc-action-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 9m0 8V9m0 0V7"/></svg>
                    </div>
                    <span class="tdc-action-label">View Route</span>
                </button>
                <a href="/trails/${trail.id}" target="_blank" rel="noopener" class="tdc-action-btn">
                    <div class="tdc-action-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="tdc-action-label">Details</span>
                </a>
            </div>
            <hr class="tdc-divider">
            <div class="tdc-stats">
                ${stats.map(s => `
                    <div class="tdc-stat" style="background:${s.bg};">
                        <div class="tdc-stat-value" style="color:${s.color};">${s.value}</div>
                        <div class="tdc-stat-label">${s.label}</div>
                    </div>
                `).join('')}
            </div>
            ${trail.description ? `<p class="tdc-description">${trail.description}</p>` : ''}
            <hr class="tdc-divider">
            <div id="tdc-elevation-section">
                <p style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;margin:0 0 8px;">Elevation Profile</p>
                <div id="tdc-elevation-loading" style="text-align:center;padding:16px 0;color:#9ca3af;font-size:12px;">Loading profile…</div>
                <canvas id="tdc-elevation-canvas" style="display:none;width:100%;height:90px;border-radius:6px;"></canvas>
            </div>
        </div>
    `;
    card.classList.add('visible');

    // Fetch elevation profile after card is shown
    if (trail.route_coordinates && trail.route_coordinates.length >= 2) {
        loadTrailElevationProfile(trail.route_coordinates);
    } else {
        document.getElementById('tdc-elevation-loading').textContent = 'No route data available.';
    }
}

async function loadTrailElevationProfile(routeCoords) {
    const loadingEl = document.getElementById('tdc-elevation-loading');
    const canvas    = document.getElementById('tdc-elevation-canvas');
    if (!loadingEl || !canvas) return;

    try {
        const res = await fetch('/api/elevation-profile', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ coordinates: routeCoords }),
        });

        if (!res.ok) throw new Error('failed');

        const data = await res.json();
        if (!data.geometry || !data.geometry.coordinates) throw new Error('no data');

        const coords = data.geometry.coordinates; // [lng, lat, elevation]
        const elevs  = coords.map(c => c[2]);
        const maxEl  = Math.max(...elevs);
        const minEl  = Math.min(...elevs);

        // Draw chart
        loadingEl.style.display = 'none';
        canvas.style.display = 'block';
        const w = canvas.width  = canvas.offsetWidth;
        const h = canvas.height = 90;
        const ctx = canvas.getContext('2d');
        const range = maxEl - minEl || 1;
        const pad = 4;

        const drawChart = (hoverIdx = -1) => {
            ctx.clearRect(0, 0, w, h);

            // Gradient fill
            const grad = ctx.createLinearGradient(0, 0, 0, h);
            grad.addColorStop(0, 'rgba(59,130,246,0.35)');
            grad.addColorStop(1, 'rgba(59,130,246,0.02)');

            ctx.beginPath();
            elevs.forEach((el, i) => {
                const x = pad + (i / (elevs.length - 1)) * (w - pad * 2);
                const y = pad + (1 - (el - minEl) / range) * (h - pad * 2);
                i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
            });
            ctx.strokeStyle = '#3b82f6';
            ctx.lineWidth = 2;
            ctx.lineJoin = 'round';
            ctx.stroke();
            ctx.lineTo(w - pad, h);
            ctx.lineTo(pad, h);
            ctx.closePath();
            ctx.fillStyle = grad;
            ctx.fill();

            if (hoverIdx >= 0 && hoverIdx < elevs.length) {
                const el = elevs[hoverIdx];
                const x = pad + (hoverIdx / (elevs.length - 1)) * (w - pad * 2);
                const y = pad + (1 - (el - minEl) / range) * (h - pad * 2);

                // Dashed vertical line
                ctx.beginPath();
                ctx.strokeStyle = 'rgba(59,130,246,0.35)';
                ctx.lineWidth = 1;
                ctx.setLineDash([3, 3]);
                ctx.moveTo(x, 0); ctx.lineTo(x, h);
                ctx.stroke();
                ctx.setLineDash([]);

                // Circle on the line
                ctx.beginPath();
                ctx.arc(x, y, 5, 0, Math.PI * 2);
                ctx.fillStyle = '#3b82f6';
                ctx.fill();
                ctx.strokeStyle = '#fff';
                ctx.lineWidth = 2;
                ctx.stroke();

                // Tooltip
                const label = Math.round(el) + 'm';
                ctx.font = 'bold 10px Inter, system-ui, sans-serif';
                const boxW = ctx.measureText(label).width + 12;
                const boxH = 18;
                let bx = x - boxW / 2;
                bx = Math.max(2, Math.min(w - boxW - 2, bx));
                const by = Math.max(2, y - boxH - 8);

                ctx.fillStyle = 'rgba(17,24,39,0.82)';
                ctx.beginPath();
                ctx.rect(bx, by, boxW, boxH);
                ctx.fill();

                ctx.fillStyle = '#fff';
                ctx.fillText(label, bx + 6, by + 12);
            }
        };

        drawChart();

        // ── Mapbox hover point ────────────────────────────────────────────
        const HOVER_SOURCE = 'elev-hover-point';
        const HOVER_LAYER  = 'elev-hover-layer';

        const ensureHoverLayer = () => {
            if (!map.getSource(HOVER_SOURCE)) {
                map.addSource(HOVER_SOURCE, {
                    type: 'geojson',
                    data: { type: 'FeatureCollection', features: [] },
                });
            }
            if (!map.getLayer(HOVER_LAYER)) {
                map.addLayer({
                    id: HOVER_LAYER,
                    type: 'circle',
                    source: HOVER_SOURCE,
                    paint: {
                        'circle-radius': 9,
                        'circle-color': '#fff',
                        'circle-stroke-width': 3,
                        'circle-stroke-color': '#3b82f6',
                    },
                });
            }
        };

        const setMapPoint = (lng, lat) => {
            try {
                ensureHoverLayer();
                map.getSource(HOVER_SOURCE).setData({
                    type: 'FeatureCollection',
                    features: [{ type: 'Feature', geometry: { type: 'Point', coordinates: [lng, lat] } }],
                });
            } catch (err) {}
        };

        const clearMapPoint = () => {
            try {
                map.getSource(HOVER_SOURCE)?.setData({ type: 'FeatureCollection', features: [] });
            } catch (_) {}
            drawChart();
        };

        const getIdx = (clientX) => {
            const rect = canvas.getBoundingClientRect();
            const frac = Math.max(0, Math.min(1, (clientX - rect.left) / rect.width));
            return Math.round(frac * (elevs.length - 1));
        };

        const onScrub = (clientX) => {
            const idx = getIdx(clientX);
            drawChart(idx);
            const coord = coords[idx];
            if (coord) setMapPoint(coord[0], coord[1]);
        };

        canvas.style.cursor = 'crosshair';
        canvas.addEventListener('mousemove', e => onScrub(e.clientX));
        canvas.addEventListener('mouseleave', clearMapPoint);
        canvas.addEventListener('touchmove', e => { e.preventDefault(); onScrub(e.touches[0].clientX); }, { passive: false });
        canvas.addEventListener('touchend', clearMapPoint);

    } catch {
        if (loadingEl) loadingEl.textContent = 'Elevation data unavailable.';
    }
}

// Function to close trail details card
function closeTrailDetailsCard() {
    const card = document.getElementById('trail-details-card');
    card.classList.remove('visible');

    // Deselect active trail line
    if (selectedTrailId !== null) {
        try { map.setFeatureState({ source: 'trail-routes', id: selectedTrailId }, { selected: false }); } catch(e) {}
        selectedTrailId = null;
    }

    // Clear facility highlight
    if (window._selectedFacilityEl) {
        window._selectedFacilityEl.style.cssText = FACILITY_NORMAL_STYLE;
        window._selectedFacilityEl = null;
    }
}

// Show facility details in the same card
function showFacilityDetailsCard(facility) {
    // Points of interest (facilities) are a Pro feature.
    if (!window.xsIsPro()) { window.xsRequirePro('poi'); return; }
    if (isMobileDevice()) { showMobileFacilityCard(facility); return; }
    const card = document.getElementById('trail-details-card');
    const typeLabel = facility.facility_type.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());

    card.innerHTML = `
        <div class="tdc-hero" style="background:linear-gradient(135deg,#134e4a,#0d9488);display:flex;align-items:center;justify-content:center;">
            ${facility.icon_image_url
                ? `<img src="${facility.icon_image_url}" alt="" style="width:80px;height:80px;object-fit:cover;border-radius:50%;border:3px solid rgba(255,255,255,0.3);">`
                : `<span style="font-size:56px;line-height:1;">${facility.icon || '📍'}</span>`}
            <button class="tdc-close" onclick="closeTrailDetailsCard()" aria-label="Close">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="tdc-body">
            <h2 class="tdc-name">${facility.name}</h2>
            <div class="tdc-meta">
                <span class="tdc-type">${typeLabel}</span>
            </div>
            <div class="tdc-actions">
                <button type="button" onclick="map.easeTo({center:[${facility.longitude},${facility.latitude}],zoom:17,duration:800})" class="tdc-action-btn">
                    <div class="tdc-action-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <span class="tdc-action-label">View Location</span>
                </button>
            </div>
            ${facility.description ? `<hr class="tdc-divider"><p class="tdc-description">${facility.description}</p>` : ''}
        </div>
    `;
    card.classList.add('visible');
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.trail-color-badge').forEach(badge => {
        const difficulty = parseInt(badge.dataset.difficulty);
        const color = getDifficultyColor(difficulty);
        badge.style.backgroundColor = color;
    });

    // Intercept sponsor close buttons so the mobile card adjusts position
    document.querySelectorAll('.sponsor-bnr__close').forEach(btn => {
        btn.addEventListener('click', function() {
            const banner = this.closest('[data-sponsor-banner]');
            if (banner) banner.style.display = 'none';
            setTimeout(updateMobileCardPosition, 50);
        }, true); // capture phase so it fires before the inline onclick
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
            setTimeout(() => map.resize(), 310);
        });

        // Hide sidebar when close button is clicked
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (isMobile()) {
                // On mobile: close sidebar and show hamburger
                sidebar.classList.add('hidden-mobile');
                toggleBtn.classList.remove('hidden');
                setTimeout(() => map.resize(), 310);
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
    // Pro video content is gated; photos stay free.
    if (type === 'video' && !window.xsIsPro()) { window.xsRequirePro('video'); return; }
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

@include('partials.share-modal', [
    'shareId' => 'network-share',
    'kicker' => 'Share This Network',
    'title' => $network->network_name,
    'subtitle' => $network->address ?: ucwords(str_replace('_', ' ', $network->type)),
    'shareText' => 'Check out the '.$network->network_name.' trail network in Smithers, BC! 🥾⛰️',
    'emailSubject' => 'Check out this trail network: '.$network->network_name,
    'emailBody' => 'I found this trail network and thought you might be interested!'."\n\n".'Network: '.$network->network_name.($network->address ? "\n".'Location: '.$network->address : ''),
])
@endsection