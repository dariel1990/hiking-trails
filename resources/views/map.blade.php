@extends('layouts.public')

@section('title', 'Interactive Trail Map (Mapbox)')

@push('styles')
<link href="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.css" rel="stylesheet" />
@endpush

@section('content')
<div class="flex h-[calc(100vh-80px)] md:h-[calc(100vh-80px)] max-md:h-[100dvh] overflow-hidden">

    <!-- Mobile Filter Modals (outside the scroll container) -->
    <!-- Distance Modal -->
    <div id="distance-dropdown-mobile" class="hidden fixed inset-0 z-50 md:hidden">
        <div class="absolute inset-0 bg-black bg-opacity-50" onclick="document.getElementById('distance-dropdown-mobile').classList.add('hidden')"></div>
        <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl p-6 max-h-[80vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h4 class="font-semibold text-lg">Distance away</h4>
                <button onclick="document.getElementById('distance-dropdown-mobile').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="space-y-2">
                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                    <input type="radio" name="distance-mobile" value="" class="distance-radio-mobile w-5 h-5" checked>
                    <span class="ml-3 text-base">Any distance</span>
                </label>
                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                    <input type="radio" name="distance-mobile" value="0-5" class="distance-radio-mobile w-5 h-5">
                    <span class="ml-3 text-base">0-5 km</span>
                </label>
                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                    <input type="radio" name="distance-mobile" value="5-10" class="distance-radio-mobile w-5 h-5">
                    <span class="ml-3 text-base">5-10 km</span>
                </label>
                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                    <input type="radio" name="distance-mobile" value="10-20" class="distance-radio-mobile w-5 h-5">
                    <span class="ml-3 text-base">10-20 km</span>
                </label>
                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                    <input type="radio" name="distance-mobile" value="20+" class="distance-radio-mobile w-5 h-5">
                    <span class="ml-3 text-base">20+ km</span>
                </label>
            </div>
        </div>
    </div>

    <!-- Difficulty Modal -->
    <div id="difficulty-dropdown-mobile" class="hidden fixed inset-0 z-50 md:hidden">
        <div class="absolute inset-0 bg-black bg-opacity-50" onclick="document.getElementById('difficulty-dropdown-mobile').classList.add('hidden')"></div>
        <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl p-6 max-h-[80vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h4 class="font-semibold text-lg">Difficulty</h4>
                <button onclick="document.getElementById('difficulty-dropdown-mobile').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="space-y-2">
                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                    <input type="radio" name="difficulty-mobile" value="" class="difficulty-radio-mobile w-5 h-5" checked>
                    <span class="ml-3 text-base">All levels</span>
                </label>
                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                    <input type="radio" name="difficulty-mobile" value="1" class="difficulty-radio-mobile w-5 h-5">
                    <span class="ml-3 text-base">1 - Very Easy</span>
                </label>
                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                    <input type="radio" name="difficulty-mobile" value="2" class="difficulty-radio-mobile w-5 h-5">
                    <span class="ml-3 text-base">2 - Easy</span>
                </label>
                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                    <input type="radio" name="difficulty-mobile" value="3" class="difficulty-radio-mobile w-5 h-5">
                    <span class="ml-3 text-base">3 - Moderate</span>
                </label>
                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                    <input type="radio" name="difficulty-mobile" value="4" class="difficulty-radio-mobile w-5 h-5">
                    <span class="ml-3 text-base">4 - Hard</span>
                </label>
                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                    <input type="radio" name="difficulty-mobile" value="5" class="difficulty-radio-mobile w-5 h-5">
                    <span class="ml-3 text-base">5 - Very Hard</span>
                </label>
            </div>
        </div>
    </div>

    <!-- All Filters Modal -->
    <div id="all-filters-modal" class="hidden fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black bg-opacity-50" onclick="document.getElementById('all-filters-modal').classList.add('hidden')"></div>
        <div class="absolute md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 max-md:bottom-0 max-md:left-0 max-md:right-0 bg-white md:rounded-2xl max-md:rounded-t-2xl max-h-[85vh] md:max-h-[80vh] md:w-[600px] flex flex-col">
            
            <!-- Header (Fixed) -->
            <div class="flex items-center justify-between p-6 pb-4 border-b border-gray-200 flex-shrink-0">
                <h3 class="text-xl font-bold">All Filters</h3>
                <button onclick="document.getElementById('all-filters-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Scrollable Content -->
            <div class="overflow-y-auto flex-1 px-6 py-4">
                <!-- Trail Type -->
                <div class="mb-6">
                    <h4 class="font-semibold text-base mb-3">Trail Type</h4>
                    <div class="space-y-2">
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                            <input type="radio" name="trail-type" value="" class="trail-type-radio w-5 h-5" checked>
                            <span class="ml-3 text-sm">All types</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                            <input type="radio" name="trail-type" value="loop" class="trail-type-radio w-5 h-5">
                            <span class="ml-3 text-sm">Loop</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                            <input type="radio" name="trail-type" value="out-and-back" class="trail-type-radio w-5 h-5">
                            <span class="ml-3 text-sm">Out and Back</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                            <input type="radio" name="trail-type" value="point-to-point" class="trail-type-radio w-5 h-5">
                            <span class="ml-3 text-sm">Point to Point</span>
                        </label>
                    </div>
                </div>

                <!-- Duration -->
                <div class="mb-6 border-t pt-6">
                    <h4 class="font-semibold text-base mb-3">Duration</h4>
                    <div class="space-y-2">
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                            <input type="radio" name="duration" value="" class="duration-radio w-5 h-5" checked>
                            <span class="ml-3 text-sm">Any duration</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                            <input type="radio" name="duration" value="0-1" class="duration-radio w-5 h-5">
                            <span class="ml-3 text-sm">Under 1 hour</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                            <input type="radio" name="duration" value="1-2" class="duration-radio w-5 h-5">
                            <span class="ml-3 text-sm">1-2 hours</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                            <input type="radio" name="duration" value="2-4" class="duration-radio w-5 h-5">
                            <span class="ml-3 text-sm">2-4 hours</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                            <input type="radio" name="duration" value="4-6" class="duration-radio w-5 h-5">
                            <span class="ml-3 text-sm">4-6 hours</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                            <input type="radio" name="duration" value="6+" class="duration-radio w-5 h-5">
                            <span class="ml-3 text-sm">6+ hours</span>
                        </label>
                    </div>
                </div>

                <!-- Elevation Gain -->
                <div class="mb-6 border-t pt-6">
                    <h4 class="font-semibold text-base mb-3">Elevation Gain</h4>
                    <div class="space-y-2">
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                            <input type="radio" name="elevation" value="" class="elevation-radio w-5 h-5" checked>
                            <span class="ml-3 text-sm">Any elevation</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                            <input type="radio" name="elevation" value="0-100" class="elevation-radio w-5 h-5">
                            <span class="ml-3 text-sm">Flat (0-100m)</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                            <input type="radio" name="elevation" value="100-300" class="elevation-radio w-5 h-5">
                            <span class="ml-3 text-sm">Easy climb (100-300m)</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                            <input type="radio" name="elevation" value="300-600" class="elevation-radio w-5 h-5">
                            <span class="ml-3 text-sm">Moderate (300-600m)</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                            <input type="radio" name="elevation" value="600-1000" class="elevation-radio w-5 h-5">
                            <span class="ml-3 text-sm">Steep (600-1000m)</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                            <input type="radio" name="elevation" value="1000+" class="elevation-radio w-5 h-5">
                            <span class="ml-3 text-sm">Very steep (1000m+)</span>
                        </label>
                    </div>
                </div>

                <!-- Features -->
                <div class="mb-6 border-t pt-6">
                    <h4 class="font-semibold text-base mb-3">Features</h4>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                            <input type="checkbox" value="waterfall" class="feature-checkbox w-5 h-5">
                            <span class="ml-3 text-sm">💧 Waterfall</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                            <input type="checkbox" value="viewpoint" class="feature-checkbox w-5 h-5">
                            <span class="ml-3 text-sm">👁️ Viewpoint</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                            <input type="checkbox" value="wildlife" class="feature-checkbox w-5 h-5">
                            <span class="ml-3 text-sm">🦌 Wildlife</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                            <input type="checkbox" value="lake" class="feature-checkbox w-5 h-5">
                            <span class="ml-3 text-sm">🏞️ Lake</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                            <input type="checkbox" value="summit" class="feature-checkbox w-5 h-5">
                            <span class="ml-3 text-sm">⛰️ Summit</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                            <input type="checkbox" value="bridge" class="feature-checkbox w-5 h-5">
                            <span class="ml-3 text-sm">🌉 Bridge</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                            <input type="checkbox" value="forest" class="feature-checkbox w-5 h-5">
                            <span class="ml-3 text-sm">🌲 Forest</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                            <input type="checkbox" value="camping" class="feature-checkbox w-5 h-5">
                            <span class="ml-3 text-sm">⛺ Camping</span>
                        </label>
                    </div>
                </div>

                <!-- Activities -->
                <div class="mb-6 border-t pt-6">
                    <h4 class="font-semibold text-base mb-3">Activities</h4>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($activities as $activity)
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg" data-activity-label>
                            <input 
                                type="checkbox" 
                                value="{{ $activity->slug }}" 
                                class="activity-checkbox w-5 h-5"
                                data-season-applicable="{{ $activity->season_applicable ?? 'both' }}"
                            >
                            <span class="ml-3 text-sm">
                                @if($activity->icon)
                                    {{ $activity->icon }} 
                                @endif
                                {{ $activity->name }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Action Buttons (Fixed at Bottom) -->
            <div class="flex gap-3 p-6 pt-4 border-t border-gray-200 bg-white md:rounded-b-2xl max-md:rounded-t-2xl flex-shrink-0">
                <button onclick="clearAllFilters()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 px-4 rounded-lg font-medium transition-colors">
                    Clear All
                </button>
                <button onclick="applyAllFilters()" class="flex-1 bg-primary-600 hover:bg-primary-700 text-white py-3 px-4 rounded-lg font-medium transition-colors">
                    Apply Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Trail List Panel - Left Side -->
    <div id="trail-list-panel" class="relative z-30 w-1/4 min-w-72 flex-shrink-0 bg-white overflow-hidden flex flex-row max-md:hidden">

        <!-- Left Icon Nav -->
        <div class="flex flex-col items-center gap-1 py-4 border-r border-gray-200 bg-gray-50 w-16 flex-shrink-0">
            <!-- Collapse/expand search button -->
            <button id="collapse-panel-btn" title="Collapse search"
                    class="mb-3 p-2 rounded-full bg-white border border-gray-300 text-gray-600 hover:text-gray-900 hover:bg-gray-100 shadow-sm transition-colors">
                <!-- Chevron left (shown when expanded) -->
                <svg id="collapse-panel-icon-chevron" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                <!-- Magnifying glass (shown when collapsed) -->
                <svg id="collapse-panel-icon-search" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>

            <!-- Hiking Trails -->
            <button data-location-filter="trail"
                    class="location-filter-btn sidebar-nav-btn active-filter flex flex-col items-center gap-1 w-full py-3 px-1 text-center transition-colors">
                <span class="text-xl">🥾</span>
                <span class="text-[10px] font-medium leading-tight">Hiking</span>
            </button>

            <!-- Fishing Lakes -->
            <button data-location-filter="fishing_lake"
                    class="location-filter-btn sidebar-nav-btn flex flex-col items-center gap-1 w-full py-3 px-1 text-center transition-colors">
                <span class="text-xl">🎣</span>
                <span class="text-[10px] font-medium leading-tight">Fishing</span>
            </button>

            <!-- Businesses -->
            <button data-location-filter="business"
                    class="location-filter-btn sidebar-nav-btn flex flex-col items-center gap-1 w-full py-3 px-1 text-center transition-colors">
                <span class="text-xl">🏪</span>
                <span class="text-[10px] font-medium leading-tight">Business</span>
            </button>
        </div>

        <!-- Right Content Area -->
        <div id="trail-list-content" class="flex flex-col flex-1 min-w-0 transition-all duration-300 overflow-hidden">
            <!-- Search Header -->
            <div class="p-3 border-b border-gray-200 bg-white flex-shrink-0">
                <div class="relative mb-2">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" id="trail-list-search"
                           placeholder="Search..."
                           class="w-full pl-9 pr-8 py-2 border border-gray-300 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-gray-50"/>
                    <button id="clear-trail-search-btn" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <p class="text-xs text-gray-500 px-1"><span id="trail-count">0</span> results</p>
            </div>

            <!-- Scrollable List -->
            <div id="trail-list-container" class="flex-1 overflow-y-auto">
                <div id="trail-cards" class="p-3 space-y-2">
                    <div class="text-center py-8 text-gray-400">
                        <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <p class="text-sm">Loading...</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Map Area (flex-1, holds map + all overlays) -->
    <div id="map-area" class="flex-1 relative overflow-hidden">

    <!-- Main Map Container -->
    <div id="main-map" class="absolute inset-0 z-10" style="width:100%;height:100%;"></div>

    <!-- Fly Along Stop Button (hidden unless animation is running).
         Positioned bottom-left, just above the Mapbox attribution bar. -->
    <button id="fly-stop-overlay-btn"
            onclick="window.trailMap && window.trailMap.stopFlyAnimation()"
            class="hidden absolute bottom-8 left-3 max-md:bottom-24 max-md:left-4 z-40 bg-white border border-red-200 text-red-700 hover:bg-red-50 rounded-full shadow-lg px-4 py-2 text-sm font-semibold flex items-center gap-2 transition-colors">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
            <rect x="6" y="6" width="12" height="12" rx="2"/>
        </svg>
        <span>Stop Fly Along</span>
    </button>

    <!-- Bottom-right custom controls (sit above Mapbox zoom) -->
    <div class="absolute bottom-24 right-2.5 z-30 flex flex-col gap-1.5">
        <!-- 3D Toggle -->
        <button id="toggle-3d-btn"
            onclick="window.trailMap && window.trailMap.toggle3D()"
            title="Switch to 3D"
            class="bg-white text-gray-700 shadow-md hover:bg-gray-50 transition-colors border border-gray-300"
            style="width:29px;height:29px;display:flex;align-items:center;justify-content:center;border-radius:4px;">
            <span class="font-bold text-xs leading-none">3D</span>
        </button>
        <!-- My Location -->
        <button id="my-location-btn"
            onclick="window.trailMap && window.trailMap.locateMe()"
            title="My Location"
            class="bg-white text-gray-700 shadow-md hover:bg-gray-50 transition-colors border border-gray-300"
            style="width:29px;height:29px;display:flex;align-items:center;justify-content:center;border-radius:4px;">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </button>
    </div>

    <!-- Map Type Selector - Top Right on Desktop, Bottom Left on Mobile -->
    <div class="absolute top-4 right-4 max-md:top-auto max-md:right-auto max-md:bottom-8 max-md:left-4 z-30">
        <div class="relative">
            <!-- Toggle Button -->
            <button id="layers-toggle" class="bg-white rounded-lg shadow-lg p-3 hover:bg-gray-50 transition-colors">
                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 0v10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2z"/>
                </svg>
            </button>

            <!-- Dropdown Menu - Opens to the right on mobile, down on desktop -->
            <div id="layers-dropdown" class="hidden absolute left-full md:left-auto bottom-0 md:bottom-auto md:top-full md:right-0 ml-2 md:ml-0 md:mt-2 bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden" style="min-width: 200px;">
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

    <!-- Mobile Floating Search Bar -->
    <div class="md:hidden absolute top-2 left-4 right-4 z-30">
        <button id="mobile-search-trigger" class="w-full flex items-center gap-3 bg-white rounded-full px-4 py-3 shadow-lg border border-gray-200 text-left">
            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <span class="text-gray-400 text-sm">Search trails, lakes, businesses...</span>
        </button>
    </div>

    <!-- Mobile Filters Container - Only visible on mobile -->
    <div id="mobile-filters-container" class="md:hidden absolute top-[60px] left-0 right-0 z-20 px-4">
        <div class="overflow-x-auto -mx-3 px-3">
            <div class="flex gap-2 pb-2" style="min-width: min-content;">
                <!-- Season Toggle -->
                <div class="flex gap-1 bg-white rounded-full p-1 shadow-sm border border-gray-300 flex-shrink-0">
                    <button data-season="summer" class="season-btn-mobile active px-4 py-1 rounded-full text-xs font-medium transition-colors">☀️ Summer</button>
                    <button data-season="winter" class="season-btn-mobile px-4 py-1 rounded-full text-xs font-medium transition-colors">❄️ Winter</button>
                </div>
                <button id="distance-filter-btn-mobile" class="filter-pill bg-white hover:bg-gray-50 border border-gray-300 rounded-full px-4 py-2 text-sm font-medium text-gray-700 flex items-center gap-2 shadow-sm flex-shrink-0">
                    <span>Distance</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <button id="difficulty-filter-btn-mobile" class="filter-pill bg-white hover:bg-gray-50 border border-gray-300 rounded-full px-4 py-2 text-sm font-medium text-gray-700 flex items-center gap-2 shadow-sm flex-shrink-0">
                    <span>Difficulty</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <button id="all-filters-btn-mobile" class="filter-pill bg-white hover:bg-gray-50 border border-gray-300 rounded-full px-4 py-2 text-sm font-medium text-gray-700 flex items-center gap-2 shadow-sm flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    <span>More</span>
                    <span id="filter-count-badge-mobile" class="hidden ml-1 bg-primary-600 text-white text-xs rounded-full px-2 py-0.5 font-bold">0</span>
                </button>
            </div>
        </div>
    </div>

    <!-- External Filter Bar - Desktop only, overlays map area top -->
    <div id="external-filters" class="hidden md:flex absolute top-4 left-0 right-16 z-30 flex-wrap gap-2 px-4">
        <div class="flex gap-1 bg-white rounded-full p-1 shadow-sm border border-gray-300">
            <button data-season="summer" class="season-btn active px-4 py-1 rounded-full text-sm font-medium transition-colors">
                ☀️ Summer
            </button>
            <button data-season="winter" class="season-btn px-4 py-1 rounded-full text-sm font-medium transition-colors">
                ❄️ Winter
            </button>
        </div>
        <!-- Distance Filter -->
        <div class="relative">
            <button id="distance-filter-btn" class="filter-pill bg-white hover:bg-gray-50 border border-gray-300 rounded-full px-4 py-2 text-sm font-medium text-gray-700 flex items-center gap-2 shadow-sm">
                <span>Distance away</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <!-- Distance Dropdown -->
            <div id="distance-dropdown" class="hidden absolute top-full left-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 p-4 z-50">
                <h4 class="font-semibold text-sm mb-3">Distance away</h4>
                <div class="space-y-2">
                    <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                        <input type="radio" name="distance" value="" class="distance-radio" checked>
                        <span class="ml-2 text-sm">Any distance</span>
                    </label>
                    <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                        <input type="radio" name="distance" value="0-5" class="distance-radio">
                        <span class="ml-2 text-sm">0-5 km</span>
                    </label>
                    <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                        <input type="radio" name="distance" value="5-10" class="distance-radio">
                        <span class="ml-2 text-sm">5-10 km</span>
                    </label>
                    <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                        <input type="radio" name="distance" value="10-20" class="distance-radio">
                        <span class="ml-2 text-sm">10-20 km</span>
                    </label>
                    <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                        <input type="radio" name="distance" value="20+" class="distance-radio">
                        <span class="ml-2 text-sm">20+ km</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Difficulty Filter -->
        <div class="relative">
            <button id="difficulty-filter-btn" class="filter-pill bg-white hover:bg-gray-50 border border-gray-300 rounded-full px-4 py-2 text-sm font-medium text-gray-700 flex items-center gap-2 shadow-sm">
                <span>Difficulty</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <!-- Difficulty Dropdown -->
            <div id="difficulty-dropdown-external" class="hidden absolute top-full left-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 p-4 z-50">
                <h4 class="font-semibold text-sm mb-3">Difficulty</h4>
                <div class="space-y-2">
                    <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                        <input type="radio" name="difficulty" value="" class="difficulty-radio" checked>
                        <span class="ml-2 text-sm">All levels</span>
                    </label>
                    <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                        <input type="radio" name="difficulty" value="1" class="difficulty-radio">
                        <span class="ml-2 text-sm">1 - Very Easy</span>
                    </label>
                    <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                        <input type="radio" name="difficulty" value="2" class="difficulty-radio">
                        <span class="ml-2 text-sm">2 - Easy</span>
                    </label>
                    <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                        <input type="radio" name="difficulty" value="3" class="difficulty-radio">
                        <span class="ml-2 text-sm">3 - Moderate</span>
                    </label>
                    <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                        <input type="radio" name="difficulty" value="4" class="difficulty-radio">
                        <span class="ml-2 text-sm">4 - Hard</span>
                    </label>
                    <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                        <input type="radio" name="difficulty" value="5" class="difficulty-radio">
                        <span class="ml-2 text-sm">5 - Very Hard</span>
                    </label>
                </div>
            </div>
        </div>


        <!-- All Filters Button -->
        <button id="all-filters-btn" class="filter-pill bg-white hover:bg-gray-50 border border-gray-300 rounded-full px-4 py-2 text-sm font-medium text-gray-700 flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
            <span>All filters</span>
            <span id="filter-count-badge" class="hidden ml-1 bg-primary-600 text-white text-xs rounded-full px-2 py-0.5 font-bold">0</span>
        </button>
    </div>

    <!-- Collapsed Panel Button (Hidden by default) -->
    <!-- Trail Info Panel (Hidden by default) -->
    <div id="trail-info-panel" class="hidden absolute top-16 bottom-4 left-4 md:top-16 md:bottom-4 md:left-4 max-md:inset-x-4 max-md:bottom-4 max-md:top-auto z-40 bg-white rounded-lg shadow-xl w-80 max-md:w-auto flex flex-col overflow-hidden">
        <div id="trail-info-content" class="flex flex-col flex-1 overflow-y-auto">
            <!-- Dynamic content will be loaded here -->
        </div>
    </div>

    <!-- Business Detail Panel -->
    <div id="business-panel" class="biz-panel hidden absolute top-16 bottom-4 left-4 md:top-16 md:bottom-4 md:left-4 max-md:inset-x-4 max-md:bottom-4 max-md:top-auto z-40 bg-white rounded-lg shadow-xl w-80 max-md:w-auto flex flex-col overflow-hidden">
        <div id="business-panel-content" class="flex flex-col flex-1 overflow-y-auto"></div>
    </div>

    </div>{{-- /map-area --}}
</div>{{-- /flex container --}}

<!-- Mobile Search Drawer (full-screen, mobile only) -->
<div id="mobile-search-drawer" class="hidden fixed inset-0 bg-white z-[200] flex-col">
    <!-- Drawer Header -->
    <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-200 bg-white flex-shrink-0 safe-top">
        <button id="mobile-search-back-btn" class="p-2 -ml-2 text-gray-600 hover:text-gray-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        <div class="flex-1 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" id="mobile-search-input"
                   placeholder="Search trails, lakes, businesses..."
                   class="w-full pl-9 pr-9 py-2 border border-gray-200 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 bg-gray-50"/>
            <button id="mobile-search-clear-btn" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
    <!-- Location Type Filter Tabs -->
    <div class="flex border-b border-gray-100 bg-white flex-shrink-0">
        <button data-mobile-location-filter="trail" class="mobile-location-tab flex-1 flex flex-col items-center gap-1 py-2 text-xs font-medium text-primary-600 border-b-2 border-primary-600">
            <span class="text-lg">🥾</span><span>Hiking</span>
        </button>
        <button data-mobile-location-filter="fishing_lake" class="mobile-location-tab flex-1 flex flex-col items-center gap-1 py-2 text-xs font-medium text-gray-500 border-b-2 border-transparent">
            <span class="text-lg">🎣</span><span>Fishing</span>
        </button>
        <button data-mobile-location-filter="business" class="mobile-location-tab flex-1 flex flex-col items-center gap-1 py-2 text-xs font-medium text-gray-500 border-b-2 border-transparent">
            <span class="text-lg">🏪</span><span>Business</span>
        </button>
    </div>
    <!-- Results -->
    <div id="mobile-search-results" class="flex-1 overflow-y-auto">
        <div id="mobile-search-results-inner" class="p-3 space-y-1">
            <p class="text-sm text-gray-400 text-center py-8">Start typing to search...</p>
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

<!-- Facility Media Modal -->
<div id="facility-media-modal" class="hidden fixed inset-0 bg-black bg-opacity-95 z-[9999] flex flex-col items-center justify-center p-4 gap-4">
    <!-- Counter pill -->
    <div id="facility-modal-counter"
         class="absolute top-4 left-1/2 -translate-x-1/2 z-20 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur text-white text-xs font-semibold tracking-wide"></div>

    <!-- Close button -->
    <button type="button" onclick="closeFacilityMediaModal()"
            class="absolute top-4 right-4 z-20 w-10 h-10 rounded-full bg-white/10 hover:bg-white/25 backdrop-blur text-white flex items-center justify-center transition-all">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    <!-- Prev -->
    <button id="facility-modal-prev" type="button"
            class="absolute left-4 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-white/10 hover:bg-white/25 backdrop-blur text-white flex items-center justify-center transition-all">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>

    <!-- Next -->
    <button id="facility-modal-next" type="button"
            class="absolute right-4 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-white/10 hover:bg-white/25 backdrop-blur text-white flex items-center justify-center transition-all">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
        </svg>
    </button>

    <!-- Image / video stage -->
    <div id="facility-modal-content"
         class="max-w-6xl w-full flex items-center justify-center"></div>

    <!-- Caption -->
    <div id="facility-modal-caption"
         class="max-w-2xl text-center text-white/80 text-sm leading-relaxed px-2"></div>
</div>
@push('scripts')
<script src="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.js"></script>
<style>
/* Filter Pills */
.filter-pill {
    transition: all 0.2s;
    white-space: nowrap;
}

.filter-pill:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.filter-pill.active {
    background: #1F2937;
    color: white;
    border-color: #1F2937;
}

#external-filters {
    overflow: visible;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}

#external-filters::-webkit-scrollbar {
    display: none;
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

@media (max-width: 768px) {
    .mapboxgl-ctrl-bottom-right .mapboxgl-ctrl-group {
        margin-bottom: 10px;
        margin-right: 10px;
    }
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

/* Filter chips */
.filter-chip {
    flex-shrink: 0;
    padding: 6px 12px;
    border: 1px solid #E5E7EB;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    background: white;
    cursor: pointer;
    transition: all 0.2s;
}

.filter-chip:hover {
    background: #F3F4F6;
}

.filter-chip.active {
    background: #1F2937;
    color: white;
    border-color: #1F2937;
}

/* Location filter active state */
.location-filter-btn.active-filter {
    border-color: #2563EB;
}

/* Sidebar nav buttons (icon nav column) */
.sidebar-nav-btn {
    color: #6b7280;
    border-left: 3px solid transparent;
}

.sidebar-nav-btn:hover {
    background-color: #f3f4f6;
    color: #374151;
}

.sidebar-nav-btn.active-filter {
    background-color: #dbeafe;
    color: #1d4ed8;
    border-left: 4px solid #1d4ed8;
    font-weight: 600;
}

/* Sidebar styling */
#trail-list-panel {
    transition: width 0.3s ease, min-width 0.3s ease;
    border-right: 1px solid #e5e7eb;
    box-shadow: 2px 0 8px rgba(0, 0, 0, 0.08);
    z-index: 20;
}

/* Trail card in list */
.trail-list-card {
    display: flex;
    gap: 12px;
    padding: 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    background: white;
    border: 1px solid #E5E7EB;
}

.trail-list-card:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    border-color: #2563EB;
}

.trail-list-image {
    width: 120px;
    height: 120px;
    border-radius: 6px;
    object-fit: cover;
    flex-shrink: 0;
}

.trail-list-image-placeholder {
    width: 120px;
    height: 120px;
    border-radius: 6px;
    background: linear-gradient(135deg, #10B981 0%, #3B82F6 100%);
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Scrollbar styling */
#trail-list-container::-webkit-scrollbar {
    width: 6px;
}

#trail-list-container::-webkit-scrollbar-track {
    background: #F3F4F6;
}

#trail-list-container::-webkit-scrollbar-thumb {
    background: #D1D5DB;
    border-radius: 3px;
}

#trail-list-container::-webkit-scrollbar-thumb:hover {
    background: #9CA3AF;
}
/* Ensure controls panel doesn't get too wide */
#controls-content {
    max-height: calc(100vh - 200px);
    overflow-y: auto;
}

/* Smooth transitions for collapsing */
#controls-content, #map-type-options {
    transition: all 0.3s ease-in-out;
}

/* Prevent pointer events on map when interacting with controls */
#controls-panel {
    pointer-events: auto;
}

/* Mobile Responsive - Stack Map Type Options Vertically */
@media (max-width: 768px) {
    /* Stack the entire map type selector vertically on mobile */
    .absolute.bottom-6.left-6.z-30 {
        flex-direction: column;
        align-items: flex-start;
    }
    
    /* Make options stack vertically instead of horizontally */
    #map-type-options {
        flex-direction: column;
        gap: 0.5rem;
        width: 95px;
        max-width: calc(100vw - 3rem);
    }
    
    /* Keep buttons in column layout with icon above text */
    #map-type-options .map-layer-btn {
        width: 100%;
        min-width: 100%;
        flex-direction: column;  /* Changed from row to column */
        justify-content: center;  /* Changed from flex-start to center */
        align-items: center;      /* Added this */
        padding: 12px;
        gap: 4px;                 /* Reduced gap */
    }
    
    /* Adjust icon size for mobile */
    #map-type-options .map-layer-icon-small {
        width: 48px;
        height: 48px;
        margin-bottom: 4px;       /* Added margin below icon */
    }
    
    /* Center label text for mobile */
    #map-type-options .map-layer-label {
        text-align: center;       /* Changed from left to center */
        font-size: 13px;          /* Slightly larger for readability */
    }
    
    /* Keep collapsed button the same */
    #map-type-toggle {
        width: 100%;
    }
    
    /* Adjust positioning for mobile */
    .absolute.bottom-6.left-6 {
        bottom: 1rem;
        left: 1rem;
        right: auto;
    }
}

/* Tablet adjustments */
@media (max-width: 1024px) and (min-width: 769px) {
    #map-type-options .map-layer-btn {
        min-width: 70px;
    }
}

/* Mobile filters container */
#mobile-filters-container {
    pointer-events: auto;
}

#mobile-filters-container .overflow-x-auto {
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

#mobile-filters-container .overflow-x-auto::-webkit-scrollbar {
    display: none;
}


/* Hide mobile filters on desktop */
@media (min-width: 769px) {
    #mobile-filters-container {
        display: none !important;
    }
}

/* Trail Info Panel positioning */
#trail-info-panel {
    transition: left 0.3s ease-in-out;
}

@media (max-width: 768px) {
    #trail-info-panel {
        max-height: calc(100vh - 2rem);
        position: fixed !important;
        z-index: 50 !important;
        /* Override any JavaScript-set left property on mobile */
        left: 1rem !important;
        right: 1rem !important;
        top: auto !important;
        bottom: 1rem !important;
        width: auto !important;
    }
    
    /* Add backdrop on mobile when panel is visible */
    #trail-info-panel:not(.hidden)::before {
        content: '';
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: -1;
        pointer-events: none;
    }
    
    /* Ensure content is above backdrop */
    #trail-info-content {
        position: relative;
        z-index: 1;
        background: white;
        border-radius: 0.5rem;
    }
}

/* Smooth scrolling for trail info content */
#trail-info-content {
    -webkit-overflow-scrolling: touch;
}

/* Season Toggle Buttons */
.season-btn,
.season-btn-mobile {
    color: #6B7280;
    background: transparent;
}

.season-btn:hover,
.season-btn-mobile:hover {
    background: #F3F4F6;
}

.season-btn.active,
.season-btn-mobile.active {
    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    color: white;
    box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
}

.season-btn[data-season="winter"].active,
.season-btn-mobile[data-season="winter"].active {
    background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
}


/* Selected state for trail/business/fishing/highlight markers — styled in place
   so the marker never shifts off its lat/lng. No transform transition: it would
   interact with Mapbox's per-frame transform on the marker container during zoom
   and cause visible drift. */
.selectable-marker-el.selected {
    transform: scale(1.35);
    box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.55), 0 4px 14px rgba(0, 0, 0, 0.55) !important;
}

/* Network markers always render on top of other markers */
.mapboxgl-marker:has(.network-marker-el) {
    z-index: 10 !important;
}
.network-marker-el.selected {
    transform: scale(1.18);
    box-shadow: 0 0 0 4px rgba(22, 101, 52, 0.35), 0 4px 14px rgba(0, 0, 0, 0.5) !important;
}

/* My Location marker */
.my-location-marker {
    background: transparent !important;
    border: none !important;
}

.my-location-dot {
    width: 18px;
    height: 18px;
    background: #2563EB;
    border: 3px solid white;
    border-radius: 50%;
    box-shadow: 0 0 0 2px #2563EB;
    position: relative;
}

.my-location-dot::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 18px;
    height: 18px;
    background: rgba(37, 99, 235, 0.25);
    border-radius: 50%;
    animation: location-pulse 1.8s ease-out infinite;
}

@keyframes location-pulse {
    0%   { transform: translate(-50%, -50%) scale(1); opacity: 1; }
    100% { transform: translate(-50%, -50%) scale(3.5); opacity: 0; }
}

#my-location-btn.active {
    background: #EFF6FF;
    border-color: #2563EB;
    color: #2563EB;
}

.mapbox-my-location-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    background: white;
    border: 2px solid rgba(0,0,0,0.2);
    border-radius: 4px;
    cursor: pointer;
    color: #374151;
}
.mapbox-my-location-btn:hover {
    background: #f3f4f6;
    color: #2563eb;
}
.mapbox-my-location-btn.active {
    background: #eff6ff;
    color: #2563eb;
    border-color: #2563eb;
}
@media (min-width: 768px) {
    .mapbox-my-location-btn { display: none; }
}

/* Mapbox popup customization */
.mapboxgl-popup-content {
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    padding: 0;
}
.mapboxgl-popup-close-button {
    font-size: 18px;
    padding: 4px 8px;
    color: #6b7280;
}

/* Facility marker styling */
.facility-marker {
    background: transparent !important;
    border: none !important;
}

/* Facility popup styling - Mapbox */
.facility-popup .mapboxgl-popup-content {
    border-radius: 14px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.18), 0 2px 8px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.06);
    padding: 0;
    overflow: hidden;
}

.facility-popup .mapboxgl-popup-tip {
    border-top-color: #fff;
    border-bottom-color: #fff;
}

.facility-popup .mapboxgl-popup-close-button {
    top: 10px;
    right: 10px;
    width: 26px;
    height: 26px;
    padding: 0;
    border-radius: 50%;
    background: rgba(17, 24, 39, 0.06);
    color: #4b5563;
    font-size: 18px;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s ease, color 0.15s ease;
}

.facility-popup .mapboxgl-popup-close-button:hover {
    background: rgba(17, 24, 39, 0.12);
    color: #111827;
}

/* Facility Popup Gallery Styles */
.facility-popup-content {
    padding: 16px 18px 16px;
    min-width: 280px;
    max-width: 320px;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

.facility-popup-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 10px;
    padding-right: 28px;
}

.facility-popup-icon {
    flex-shrink: 0;
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border: 1px solid #bbf7d0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    line-height: 1;
    margin: 0;
}

.facility-popup-title {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    line-height: 1.3;
    letter-spacing: -0.01em;
    word-break: break-word;
}

.facility-popup-type {
    display: inline-block;
    margin: 0 0 12px 0;
    padding: 3px 10px;
    background: #f0fdf4;
    color: #166534;
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    border-radius: 999px;
}

.facility-popup-description {
    margin: 0;
    font-size: 13px;
    color: #4b5563;
    line-height: 1.55;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.facility-popup-description.expanded {
    -webkit-line-clamp: unset;
    overflow: visible;
}

.facility-popup-readmore {
    margin: 4px 0 0;
    padding: 0;
    background: none;
    border: 0;
    color: #166534;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    line-height: 1.4;
    display: none; /* shown via JS only when description is actually clipped */
}

.facility-popup-readmore:hover {
    text-decoration: underline;
}

/* ── Business slide panel ────────────────────────────────── */
.biz-panel {
    font-family: 'Inter', system-ui, sans-serif;
}

@media (max-width: 768px) {
    .biz-panel {
        max-height: calc(100vh - 2rem);
        position: fixed !important;
        z-index: 50 !important;
        left: 1rem !important;
        right: 1rem !important;
        top: auto !important;
        bottom: 1rem !important;
        width: auto !important;
    }

    .biz-panel:not(.hidden)::before {
        content: '';
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: -1;
        pointer-events: none;
    }

    #business-panel-content {
        position: relative;
        z-index: 1;
        background: white;
        border-radius: 0.5rem;
    }
}

.biz-panel-close {
    position: absolute;
    top: 12px;
    right: 12px;
    z-index: 10;
    background: rgba(255,255,255,0.9);
    border: none;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #374151;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    transition: background 0.15s;
}

.biz-panel-close:hover {
    background: #f3f4f6;
}

.biz-panel-hero {
    width: 100%;
    height: 200px;
    overflow: hidden;
    background: linear-gradient(135deg, #1e40af, #3b82f6);
    position: relative;
    flex-shrink: 0;
}

.biz-panel-hero img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.biz-panel-hero-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 64px;
}

.biz-panel-body {
    padding: 20px 20px 28px;
    /* Firefox: hide scrollbar by default */
    scrollbar-width: thin;
    scrollbar-color: transparent transparent;
    transition: scrollbar-color 0.25s ease;
}
.biz-panel-body:hover,
.biz-panel-body.is-scrolling {
    scrollbar-color: rgba(0, 0, 0, 0.28) transparent;
}
/* WebKit / Chromium / Safari */
.biz-panel-body::-webkit-scrollbar {
    width: 8px;
}
.biz-panel-body::-webkit-scrollbar-track {
    background: transparent;
}
.biz-panel-body::-webkit-scrollbar-thumb {
    background-color: transparent;
    border-radius: 999px;
    border: 2px solid transparent;
    background-clip: padding-box;
    transition: background-color 0.25s ease;
}
.biz-panel-body:hover::-webkit-scrollbar-thumb,
.biz-panel-body.is-scrolling::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.28);
}
.biz-panel-body:hover::-webkit-scrollbar-thumb:hover {
    background-color: rgba(0, 0, 0, 0.5);
}

.biz-panel-name {
    font-size: 22px;
    font-weight: 800;
    color: #111827;
    margin: 0 0 4px;
    line-height: 1.2;
    padding-right: 32px;
}

.biz-panel-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 14px;
}

.biz-panel-type {
    font-size: 13px;
    font-weight: 600;
    color: #2563EB;
}

.biz-panel-dot {
    color: #d1d5db;
    font-size: 16px;
    line-height: 1;
}

.biz-panel-price-badge {
    font-size: 12px;
    font-weight: 700;
    background: #dbeafe;
    color: #1d4ed8;
    padding: 2px 8px;
    border-radius: 999px;
}

.biz-panel-seasonal-badge {
    font-size: 12px;
    font-weight: 600;
    background: #fef3c7;
    color: #92400e;
    padding: 2px 8px;
    border-radius: 999px;
}

.biz-panel-tagline {
    font-size: 14px;
    color: #4b5563;
    font-style: italic;
    line-height: 1.5;
    margin: 0 0 18px;
}

.biz-panel-actions {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.biz-panel-action-btn {
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
    transition: background 0.15s, box-shadow 0.15s, border-color 0.15s;
    font-family: inherit;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.biz-panel-action-btn:hover {
    background: #dcfce7;
    border-color: #86efac;
    box-shadow: 0 2px 8px rgba(22,163,74,0.15);
}

.biz-panel-action-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #16a34a;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    flex-shrink: 0;
}

.biz-panel-action-icon svg {
    width: 18px;
    height: 18px;
}

.biz-panel-action-label {
    font-size: 11px;
    font-weight: 600;
    color: #166534;
    text-align: center;
    line-height: 1.2;
}

.biz-panel-divider {
    border: none;
    border-top: 1px solid #f3f4f6;
    margin: 0 0 16px;
}

.biz-panel-info-row {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 8px 0;
    font-size: 13px;
    color: #374151;
    border-bottom: 1px solid #f9fafb;
}

.biz-panel-info-row:last-child {
    border-bottom: none;
}

.biz-panel-info-icon {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
    color: #6b7280;
    margin-top: 1px;
}

.biz-panel-info-link {
    color: #2563EB;
    text-decoration: none;
    font-weight: 500;
}

.biz-panel-info-link:hover {
    text-decoration: underline;
}
/* ── /Business slide panel ────────────────────────────────── */

.facility-media-gallery {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #e5e7eb;
}

.facility-media-count {
    margin: 0 0 8px 0;
    font-size: 12px;
    color: #6b7280;
    font-weight: 500;
}

.facility-media-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 6px;
}

.facility-media-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: 6px;
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}

.facility-media-item:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.facility-media-thumbnail {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.facility-media-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
    font-weight: 600;
}

.facility-video-badge {
    position: absolute;
    bottom: 4px;
    right: 4px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
}

/* Facility Gallery Modal */
.facility-gallery-modal {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.9);
    z-index: 99999;
    display: none;
    align-items: center;
    justify-content: center;
}

.facility-gallery-modal.active {
    display: flex;
}

.facility-gallery-container {
    max-width: 90vw;
    max-height: 90vh;
    width: 100%;
    display: flex;
    flex-direction: column;
    background: #1f2937;
    border-radius: 12px;
    overflow: hidden;
}

.facility-gallery-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: #111827;
    border-bottom: 1px solid #374151;
}

.facility-gallery-title {
    color: white;
    font-size: 16px;
    font-weight: 600;
    margin: 0;
}

.facility-gallery-close {
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: color 0.2s;
}

.facility-gallery-close:hover {
    color: white;
    background: #374151;
}

.facility-gallery-content {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    position: relative;
    min-height: 400px;
}

.facility-gallery-main {
    max-width: 100%;
    max-height: 60vh;
    border-radius: 8px;
}

.facility-gallery-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0, 0, 0, 0.5);
    border: none;
    color: white;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}

.facility-gallery-nav:hover {
    background: rgba(0, 0, 0, 0.8);
}

.facility-gallery-prev {
    left: 20px;
}

.facility-gallery-next {
    right: 20px;
}

.facility-gallery-thumbnails {
    display: flex;
    gap: 8px;
    padding: 16px 20px;
    background: #111827;
    overflow-x: auto;
    border-top: 1px solid #374151;
}

.facility-gallery-thumb {
    width: 80px;
    height: 60px;
    border-radius: 6px;
    overflow: hidden;
    cursor: pointer;
    opacity: 0.6;
    transition: opacity 0.2s;
    flex-shrink: 0;
}

.facility-gallery-thumb.active {
    opacity: 1;
    box-shadow: 0 0 0 2px #3b82f6;
}

.facility-gallery-thumb:hover {
    opacity: 1;
}

.facility-gallery-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.facility-gallery-caption {
    padding: 12px 20px;
    background: #111827;
    color: #9ca3af;
    font-size: 14px;
    text-align: center;
    border-top: 1px solid #374151;
}
</style>
<script>

    function escapeHtml(str) {
        if (str == null) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Video Thumbnail Generator Functions
    function getVideoThumbnail(videoUrl) {
        // YouTube
        const youtubeMatch = videoUrl.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/);
        if (youtubeMatch) {
            const videoId = youtubeMatch[1];
            return `https://img.youtube.com/vi/${videoId}/hqdefault.jpg`;
        }
        
        // Vimeo - returns placeholder, actual thumbnail needs API call
        const vimeoMatch = videoUrl.match(/vimeo\.com\/(\d+)/);
        if (vimeoMatch) {
            const videoId = vimeoMatch[1];
            return `https://vumbnail.com/${videoId}.jpg`; // Third-party service for Vimeo thumbnails
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

    // Facility Media Modal — carousel through every photo/video for the facility.
    // Media is cached per facility id by loadFacilities().
    window._facilityMediaCache = window._facilityMediaCache || {};
    let _facilityModalState = { facilityId: null, index: 0 };

    function openFacilityMediaModal(facilityId, index) {
        const data = window._facilityMediaCache[facilityId];
        if (!data || !data.media || !data.media.length) { return; }
        _facilityModalState.facilityId = facilityId;
        _facilityModalState.index = Math.max(0, Math.min(index || 0, data.media.length - 1));
        document.getElementById('facility-media-modal').classList.remove('hidden');
        _renderFacilityMediaItem();
    }

    function _renderFacilityMediaItem() {
        const { facilityId, index } = _facilityModalState;
        const data = window._facilityMediaCache[facilityId];
        if (!data) { return; }
        const media = data.media[index];
        const total = data.media.length;
        const content   = document.getElementById('facility-modal-content');
        const counter   = document.getElementById('facility-modal-counter');
        const captionEl = document.getElementById('facility-modal-caption');
        const prevBtn   = document.getElementById('facility-modal-prev');
        const nextBtn   = document.getElementById('facility-modal-next');

        const isVideo = media.media_type === 'video_url' || media.media_type === 'video';
        const fullUrl = isVideo
            ? (media.url || media.video_url)
            : (media.url || media.thumbnail_url);

        if (isVideo) {
            const embedUrl = getVideoEmbedUrl(fullUrl);
            if (embedUrl) {
                content.innerHTML = `
                    <div class="relative w-full max-w-5xl" style="padding-bottom: 56.25%;">
                        <iframe src="${embedUrl}"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                                class="absolute inset-0 w-full h-full rounded-lg shadow-2xl"></iframe>
                    </div>`;
            } else {
                content.innerHTML = `<p class="text-red-300 text-center p-4">Unable to load video</p>`;
            }
        } else {
            content.innerHTML = `<img src="${fullUrl}" alt="" class="max-w-full max-h-[78vh] object-contain rounded-lg shadow-2xl">`;
        }

        counter.textContent = `${index + 1} / ${total}`;
        captionEl.textContent = media.caption || data.name || '';

        const showArrows = total > 1;
        prevBtn.style.display = showArrows ? '' : 'none';
        nextBtn.style.display = showArrows ? '' : 'none';
        counter.style.display = showArrows ? '' : 'none';
    }

    function _facilityMediaStep(delta) {
        const data = window._facilityMediaCache[_facilityModalState.facilityId];
        if (!data || !data.media.length) { return; }
        const total = data.media.length;
        _facilityModalState.index = (_facilityModalState.index + delta + total) % total;
        _renderFacilityMediaItem();
    }

    function closeFacilityMediaModal() {
        const modal = document.getElementById('facility-media-modal');
        const content = document.getElementById('facility-modal-content');
        modal.classList.add('hidden');
        content.innerHTML = ''; // stop any video playback
        _facilityModalState.facilityId = null;
    }

    document.getElementById('facility-modal-prev')?.addEventListener('click', (e) => {
        e.stopPropagation();
        _facilityMediaStep(-1);
    });
    document.getElementById('facility-modal-next')?.addEventListener('click', (e) => {
        e.stopPropagation();
        _facilityMediaStep(1);
    });

    // Close modal when clicking outside
    document.getElementById('highlight-media-modal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeHighlightMediaModal();
        }
    });

    document.getElementById('facility-media-modal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeFacilityMediaModal();
        }
    });

    // Close modal with Escape key, navigate facility carousel with arrow keys
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeHighlightMediaModal();
            closeFacilityMediaModal();
            return;
        }
        const facModal = document.getElementById('facility-media-modal');
        if (facModal && !facModal.classList.contains('hidden')) {
            if (e.key === 'ArrowLeft')  { _facilityMediaStep(-1); }
            if (e.key === 'ArrowRight') { _facilityMediaStep(1); }
        }
    });

    // Advanced Filters State
    let advancedFilters = {
        trailType: '',
        duration: '',
        elevation: '',
        features: [],
        activities: []
    };

    // Open All Filters Modal
    document.getElementById('all-filters-btn')?.addEventListener('click', function() {
        document.getElementById('all-filters-modal').classList.remove('hidden');
    });

    document.getElementById('all-filters-btn-mobile')?.addEventListener('click', function() {
        document.getElementById('all-filters-modal').classList.remove('hidden');
    });

    // Apply All Filters
    function applyAllFilters() {
        // Get trail type
        const trailTypeRadio = document.querySelector('.trail-type-radio:checked');
        advancedFilters.trailType = trailTypeRadio ? trailTypeRadio.value : '';

        // Get duration
        const durationRadio = document.querySelector('.duration-radio:checked');
        advancedFilters.duration = durationRadio ? durationRadio.value : '';

        // Get elevation
        const elevationRadio = document.querySelector('.elevation-radio:checked');
        advancedFilters.elevation = elevationRadio ? elevationRadio.value : '';

        // Get features (multi-select)
        const featureCheckboxes = document.querySelectorAll('.feature-checkbox:checked');
        advancedFilters.features = Array.from(featureCheckboxes).map(cb => cb.value);

        // Get activities (multi-select)
        const activityCheckboxes = document.querySelectorAll('.activity-checkbox:checked');
        advancedFilters.activities = Array.from(activityCheckboxes).map(cb => cb.value);

        // Close modal
        document.getElementById('all-filters-modal').classList.add('hidden');

        // Update filter count badge
        updateFilterCountBadge();

        // Apply filters to map
        if (window.trailMap) {
            window.trailMap.applyAdvancedFilters(advancedFilters);
        }
    }

    // Clear All Filters
    function clearAllFilters() {
        // Reset all radio buttons
        document.querySelectorAll('.trail-type-radio[value=""]')[0].checked = true;
        document.querySelectorAll('.duration-radio[value=""]')[0].checked = true;
        document.querySelectorAll('.elevation-radio[value=""]')[0].checked = true;

        // Uncheck all checkboxes
        document.querySelectorAll('.feature-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.activity-checkbox').forEach(cb => cb.checked = false);

        // Reset filters state
        advancedFilters = {
            trailType: '',
            duration: '',
            elevation: '',
            features: [],
            activities: []
        };

        // Update badge
        updateFilterCountBadge();

        // Apply filters (will show all)
        if (window.trailMap) {
            window.trailMap.applyAdvancedFilters(advancedFilters);
        }
    }

    // Update Filter Count Badge
    function updateFilterCountBadge() {
        let count = 0;
        if (advancedFilters.trailType) count++;
        if (advancedFilters.duration) count++;
        if (advancedFilters.elevation) count++;
        count += advancedFilters.features.length;
        count += advancedFilters.activities.length;

        const badge = document.getElementById('filter-count-badge');
        const badgeMobile = document.getElementById('filter-count-badge-mobile');

        if (count > 0) {
            if (badge) {
                badge.textContent = count;
                badge.classList.remove('hidden');
            }
            if (badgeMobile) {
                badgeMobile.textContent = count;
                badgeMobile.classList.remove('hidden');
            }
        } else {
            if (badge) badge.classList.add('hidden');
            if (badgeMobile) badgeMobile.classList.add('hidden');
        }
    }
    mapboxgl.accessToken = '{{ $mapboxToken }}';

    class EnhancedTrailMap {
        constructor() {
            this.map = null;
            this.currentSeason = 'summer';
            this.currentDistance = '';
            this.currentDifficulty = '';
            this.activeFilters = ['hiking', 'fishing', 'camping', 'viewpoint', 'highlights',
                      'snowshoeing', 'ice-fishing', 'cross-country-skiing', 'downhill-skiing'];
            this.allTrails = [];
            this.init();

            window.trailMap = this;
        }

        init() {
            // Map style definitions
            this.mapStyles = {
                'standard':  'mapbox://styles/mapbox/standard',
                'satellite': 'mapbox://styles/mapbox/satellite-streets-v12',
                'terrain':   'mapbox://styles/mapbox/outdoors-v12',
                'outdoors':  'mapbox://styles/mapbox/navigation-day-v1',
            };
            this.currentMapType = 'standard';

            // Initialize Mapbox map with 3D terrain
            this.map = new mapboxgl.Map({
                container: 'main-map',
                style: this.mapStyles[this.currentMapType],
                center: [-127.1698, 54.7804], // [lng, lat]
                zoom: 10,
                pitch: 0,
                bearing: 0,
                attributionControl: false,
            });

            // Navigation control (zoom + compass) — bottom right
            this.map.addControl(new mapboxgl.NavigationControl({ showCompass: false }), 'bottom-right');

            // Attribution
            this.map.addControl(new mapboxgl.AttributionControl({ compact: true }), 'bottom-left');


            // Marker storage per activity type (arrays of mapboxgl.Marker)
            this.overlayMarkers = {
                'hiking': [], 'fishing': [], 'camping': [], 'viewpoint': [], 'highlights': [],
                'snowshoeing': [], 'ice-fishing': [], 'cross-country-skiing': [], 'downhill-skiing': []
            };
            this.businessMarkers = {};
            this.networkMarkers = {};
            this.networkData = [];
            this.facilityMarkers = [];
            this.showBusinesses = true;
            this.currentTrails = [];
            this.businessData = [];
            this.activeLocationFilter = 'trail';
            this._selectedTrailId = null;
            this._selectedPinMarker = null;
            this._selectedOriginalEl = null;
            this._locationMarker = null;
            this._locationCircle = null;
            this._is3D = false;
            this._isFlying = false;
            this._flyAnimation = null;
            this._flyTimeout = null;
            this._hikerMarker = null;

            this.setupEventListeners();
            this.updateActivityFilters(this.currentSeason);

            // Wait for map style to load before adding sources/layers and data
            this.map.on('load', () => {
                this._initMapLayers();
                this.loadTrails();
                this.loadFacilities();
                this.loadBusinesses();
                this.loadTrailNetworks();
            });

            // Re-init layers when style changes (after setStyle)
            this.map.on('style.load', () => {
                this._initMapLayers();
                // Restore pitch after style swap if 3D was active
                if (this._is3D) this.map.setPitch(45);
                // Re-render trails/businesses after style reload
                if (this.allTrails && this.allTrails.length) {
                    this.applyFilters();
                    this.renderBusinessMarkers();
                    this.renderNetworkMarkers();
                }
            });
        }

        _initMapLayers() {
            // 3D Terrain source — terrain itself is only applied when 3D mode is on
            // (terrain causes HTML markers to drift during pan in 2D view)
            if (!this.map.getSource('mapbox-dem')) {
                this.map.addSource('mapbox-dem', {
                    type: 'raster-dem',
                    url: 'mapbox://mapbox.mapbox-terrain-dem-v1',
                    tileSize: 512,
                    maxzoom: 14,
                });
            }
            if (this._is3D) {
                this.map.setTerrain({ source: 'mapbox-dem', exaggeration: 1.5 });
            } else {
                this.map.setTerrain(null);
            }

            // Sky / atmosphere layer
            if (!this.map.getLayer('sky')) {
                this.map.addLayer({
                    id: 'sky',
                    type: 'sky',
                    paint: {
                        'sky-type': 'atmosphere',
                        'sky-atmosphere-sun': [0.0, 90.0],
                        'sky-atmosphere-sun-intensity': 15,
                    },
                });
            }

            // Arrow image for route direction indicators
            // Tip points right (+x) so Mapbox rotates it along line travel direction
            const arrowSVG = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14"><polygon points="13,7 5,3 7,7 5,11" fill="white"/></svg>`;
            const arrowImg = new Image(14, 14);
            arrowImg.onload = () => {
                if (!this.map.hasImage('trail-arrow')) {
                    this.map.addImage('trail-arrow', arrowImg);
                }
            };
            arrowImg.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(arrowSVG);

            // GeoJSON source for trail routes
            if (!this.map.getSource('trail-routes')) {
                this.map.addSource('trail-routes', {
                    type: 'geojson',
                    promoteId: 'trailId',
                    data: { type: 'FeatureCollection', features: [] }
                });
            }

            // Highlight/outline layer (yellow glow when selected)
            if (!this.map.getLayer('trail-routes-outline')) {
                this.map.addLayer({
                    id: 'trail-routes-outline',
                    type: 'line',
                    source: 'trail-routes',
                    paint: {
                        'line-color': '#F5CBA7',
                        'line-width': 15,
                        'line-opacity': ['case', ['boolean', ['feature-state', 'selected'], false], 1, 0],
                    },
                });
            }

            // Visible route layer
            if (!this.map.getLayer('trail-routes-line')) {
                this.map.addLayer({
                    id: 'trail-routes-line',
                    type: 'line',
                    source: 'trail-routes',
                    paint: {
                        'line-color': ['get', 'color'],
                        'line-width': ['case', ['boolean', ['feature-state', 'selected'], false], 4, 3],
                        'line-opacity': 1,
                    },
                });
            }

            // Direction arrow layer
            if (!this.map.getLayer('trail-routes-arrows')) {
                this.map.addLayer({
                    id: 'trail-routes-arrows',
                    type: 'symbol',
                    source: 'trail-routes',
                    layout: {
                        'symbol-placement': 'line',
                        'symbol-spacing': 120,
                        'icon-image': 'trail-arrow',
                        'icon-size': 0.9,
                        'icon-allow-overlap': true,
                        'icon-ignore-placement': true,
                    },
                });
            }

            // Click on route line
            this.map.on('click', 'trail-routes-line', (e) => {
                if (e.features.length > 0) {
                    this.highlightTrailRoute(e.features[0].properties.trailId);
                }
            });
            this.map.on('mouseenter', 'trail-routes-line', () => { this.map.getCanvas().style.cursor = 'pointer'; });
            this.map.on('mouseleave', 'trail-routes-line', () => { this.map.getCanvas().style.cursor = ''; });
        }

        setupEventListeners() {
            // Season switching for both desktop and mobile
            document.querySelectorAll('.season-btn, .season-btn-mobile').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const season = e.target.dataset.season;
                    
                    // Update all season buttons (desktop and mobile)
                    document.querySelectorAll('.season-btn, .season-btn-mobile').forEach(b => {
                        if (b.dataset.season === season) {
                            b.classList.add('active');
                        } else {
                            b.classList.remove('active');
                        }
                    });
                    
                    this.switchSeason(season);
                });
            });

            // Map layer button clicks (in expanded options only)
            document.querySelectorAll('.layer-option-card').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const mapType = btn.dataset.mapType;
                    if (mapType) {
                        this.switchMapType(mapType);
                    }
                });
            });

            // Activity filtering
            document.querySelectorAll('.activity-filter').forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    this.updateFilters();
                });
            });

            // Close panels when clicking map background
            this.map.on('click', (e) => {
                if (!e.features || e.features.length === 0) {
                    this.closeBusinessPanel();
                    document.getElementById('trail-info-panel')?.classList.add('hidden');
                    this._clearSelection();
                }
            });


            // Other filters - Check if elements exist first
            const difficultyFilter = document.getElementById('difficulty-filter');
            if (difficultyFilter) {
                difficultyFilter.addEventListener('change', () => {
                    this.updateFilters();
                });
            }

            const distanceFilter = document.getElementById('distance-filter');
            if (distanceFilter) {
                distanceFilter.addEventListener('change', () => {
                    this.updateFilters();
                });
            }

            // Clear filters - Check if exists
            const clearFiltersBtn = document.getElementById('clear-filters');
            if (clearFiltersBtn) {
                clearFiltersBtn.addEventListener('click', () => {
                    this.clearFilters();
                });
            }

            // Layers dropdown toggle
            document.getElementById('layers-toggle').addEventListener('click', (e) => {
                e.stopPropagation();
                const dropdown = document.getElementById('layers-dropdown');
                dropdown.classList.toggle('hidden');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                const dropdown = document.getElementById('layers-dropdown');
                const toggle = document.getElementById('layers-toggle');
                if (!dropdown.contains(e.target) && !toggle.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });

            // Layer option clicks
            document.querySelectorAll('.layer-option').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const mapType = btn.dataset.mapType;
                    if (mapType) {
                        this.switchMapType(mapType);
                    }
                });
            });

            // Panel search area collapse/expand
            const collapseBtn = document.getElementById('collapse-panel-btn');
            const trailListContent = document.getElementById('trail-list-content');
            const iconChevron = document.getElementById('collapse-panel-icon-chevron');
            const iconSearch = document.getElementById('collapse-panel-icon-search');
            const trailListPanel = document.getElementById('trail-list-panel');

            function applyTrailListState(collapsed) {
                if (!trailListContent || !trailListPanel) { return; }
                if (collapsed) {
                    trailListContent.classList.add('hidden');
                    trailListPanel.style.width = '4rem';
                    trailListPanel.style.minWidth = '4rem';
                    iconChevron?.classList.add('hidden');
                    iconSearch?.classList.remove('hidden');
                    if (collapseBtn) { collapseBtn.title = 'Show search'; }
                } else {
                    trailListContent.classList.remove('hidden');
                    trailListPanel.style.width = '';
                    trailListPanel.style.minWidth = '';
                    iconChevron?.classList.remove('hidden');
                    iconSearch?.classList.add('hidden');
                    if (collapseBtn) { collapseBtn.title = 'Collapse search'; }
                }
                setTimeout(() => {
                    if (window.trailMap && window.trailMap.map) {
                        window.trailMap.map.resize();
                    }
                }, 320);
            }

            window.trailListPanelApi = {
                collapse() { applyTrailListState(true); },
                expand()   { applyTrailListState(false); },
                toggle()   { applyTrailListState(!trailListContent?.classList.contains('hidden')); },
                isCollapsed() { return !!trailListContent?.classList.contains('hidden'); },
            };

            if (collapseBtn && trailListContent && trailListPanel) {
                collapseBtn.addEventListener('click', () => window.trailListPanelApi.toggle());
            }

            // Click on map to collapse controls
            this.map.on('click', () => {
                const controlsContent = document.getElementById('controls-content');
                if (controlsContent) controlsContent.classList.add('hidden');
            });

            // External filter dropdowns
            const distanceBtn = document.getElementById('distance-filter-btn');
            const distanceDropdown = document.getElementById('distance-dropdown');
            const difficultyBtn = document.getElementById('difficulty-filter-btn');
            const difficultyDropdown = document.getElementById('difficulty-dropdown-external');

            if (distanceBtn && distanceDropdown) {
                distanceBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    distanceDropdown.classList.toggle('hidden');
                    if (difficultyDropdown) difficultyDropdown.classList.add('hidden');
                });
            }

            if (difficultyBtn && difficultyDropdown) {
                difficultyBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    difficultyDropdown.classList.toggle('hidden');
                    if (distanceDropdown) distanceDropdown.classList.add('hidden');
                });
            }

            // Handle filter selections
            document.querySelectorAll('.distance-radio').forEach(radio => {
                radio.addEventListener('change', (e) => {
                    this.currentDistance = e.target.value;
                    this.applyFilters();
                    if (distanceDropdown) distanceDropdown.classList.add('hidden');
                });
            });

            document.querySelectorAll('.difficulty-radio').forEach(radio => {
                radio.addEventListener('change', (e) => {
                    this.currentDifficulty = e.target.value;
                    this.applyFilters();
                    if (difficultyDropdown) difficultyDropdown.classList.add('hidden');
                });
            });

            // Close dropdowns when clicking outside
            document.addEventListener('click', () => {
                if (distanceDropdown) distanceDropdown.classList.add('hidden');
                if (difficultyDropdown) difficultyDropdown.classList.add('hidden');
            });

            // Mobile filter dropdowns
            const distanceBtnMobile = document.getElementById('distance-filter-btn-mobile');
            const distanceDropdownMobile = document.getElementById('distance-dropdown-mobile');
            const difficultyBtnMobile = document.getElementById('difficulty-filter-btn-mobile');
            const difficultyDropdownMobile = document.getElementById('difficulty-dropdown-mobile');

            if (distanceBtnMobile && distanceDropdownMobile) {
                distanceBtnMobile.addEventListener('click', (e) => {
                    e.stopPropagation();
                    distanceDropdownMobile.classList.toggle('hidden');
                    if (difficultyDropdownMobile) difficultyDropdownMobile.classList.add('hidden');
                });
            }

            if (difficultyBtnMobile && difficultyDropdownMobile) {
                difficultyBtnMobile.addEventListener('click', (e) => {
                    e.stopPropagation();
                    difficultyDropdownMobile.classList.toggle('hidden');
                    if (distanceDropdownMobile) distanceDropdownMobile.classList.add('hidden');
                });
            }

            // Handle mobile filter selections
            document.querySelectorAll('.distance-radio-mobile').forEach(radio => {
                radio.addEventListener('change', (e) => {
                    this.currentDistance = e.target.value;
                    this.applyFilters();
                    if (distanceDropdownMobile) distanceDropdownMobile.classList.add('hidden');
                });
            });

            document.querySelectorAll('.trail-type-radio').forEach(radio => {
                radio.addEventListener('change', () => {
                    updateFilterCountBadge();
                });
            });

            document.querySelectorAll('.duration-radio').forEach(radio => {
                radio.addEventListener('change', () => {
                    updateFilterCountBadge();
                });
            });

            document.querySelectorAll('.elevation-radio').forEach(radio => {
                radio.addEventListener('change', () => {
                    updateFilterCountBadge();
                });
            });

            document.querySelectorAll('.feature-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    updateFilterCountBadge();
                });
            });

            document.querySelectorAll('.activity-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    updateFilterCountBadge();
                });
            });

            document.querySelectorAll('.difficulty-radio-mobile').forEach(radio => {
                radio.addEventListener('change', (e) => {
                    this.currentDifficulty = e.target.value;
                    this.applyFilters();
                    if (difficultyDropdownMobile) difficultyDropdownMobile.classList.add('hidden');
                });
            });

            // Close mobile dropdowns when clicking outside
            document.addEventListener('click', () => {
                if (distanceDropdownMobile) distanceDropdownMobile.classList.add('hidden');
                if (difficultyDropdownMobile) difficultyDropdownMobile.classList.add('hidden');
            });

        }

        updateActivityFilters(season) {
            // Define which activities are available for each season (map overlays)
            const seasonalActivities = {
                summer: ['hiking', 'fishing', 'camping', 'viewpoint', 'highlights'],
                winter: ['snowshoeing', 'ice-fishing', 'cross-country-skiing', 'downhill-skiing', 'viewpoint', 'highlights'],
            };

            // Get valid activities for this season
            const validActivities = seasonalActivities[season] || seasonalActivities.summer;

            // Ensure overlay marker arrays exist for all valid activities
            validActivities.forEach(activityType => {
                if (!this.overlayMarkers[activityType]) {
                    this.overlayMarkers[activityType] = [];
                }
            });

            // Update the map overlay activity filters list
            this.activeFilters = ['highlights', ...validActivities];

            // Update the Advanced Filters (modal) activity options to match season_applicable
            // Rules:
            // - summer season shows activities where season_applicable is summer or both
            // - winter season shows activities where season_applicable is winter or both
            const allowedSeasonValues = new Set([season, 'both']);

            document.querySelectorAll('.activity-checkbox').forEach(cb => {
                const applicable = (cb.dataset.seasonApplicable || 'both').toLowerCase();
                const shouldShow = allowedSeasonValues.has(applicable);

                const label = cb.closest('label');
                if (label) {
                    label.classList.toggle('hidden', !shouldShow);
                }

                if (!shouldShow) {
                    cb.checked = false;
                    cb.disabled = true;
                } else {
                    cb.disabled = false;
                }
            });

            // If the user had previously selected activities that are no longer valid for the season,
            // remove them from the active advanced filters state.
            if (typeof advancedFilters !== 'undefined' && advancedFilters?.activities) {
                advancedFilters.activities = advancedFilters.activities.filter(activitySlug => {
                    const input = document.querySelector(`.activity-checkbox[value="${CSS.escape(activitySlug)}"]`);
                    return input && !input.disabled;
                });

                // Keep the UI badge accurate
                if (typeof updateFilterCountBadge === 'function') {
                    updateFilterCountBadge();
                }
            }
        }

        getVisibleTrails() {
            // Simply return filtered trails without map bounds check
            return this.filterTrails(this.allTrails);
        }

        filterTrails(trails) {
            return trails.filter(trail => {
                // Apply difficulty filter
                if (this.currentDifficulty && trail.difficulty != this.currentDifficulty) {
                    return false;
                }

                // Apply distance filter
                if (this.currentDistance && !this.matchesDistanceFilter(trail.distance, this.currentDistance)) {
                    return false;
                }

                // Apply advanced filters
                if (!this.matchesAdvancedFilters(trail)) {
                    return false;
                }

                return true;
            });
        }

        matchesAdvancedFilters(trail) {
            // Trail Type filter
            if (advancedFilters.trailType && trail.trail_type !== advancedFilters.trailType) {
                return false;
            }

            // Duration filter
            if (advancedFilters.duration) {
                const duration = parseFloat(trail.estimated_time);
                if (advancedFilters.duration === '0-1' && duration >= 1) return false;
                if (advancedFilters.duration === '1-2' && (duration < 1 || duration >= 2)) return false;
                if (advancedFilters.duration === '2-4' && (duration < 2 || duration >= 4)) return false;
                if (advancedFilters.duration === '4-6' && (duration < 4 || duration >= 6)) return false;
                if (advancedFilters.duration === '6+' && duration < 6) return false;
            }

            // Elevation filter
            if (advancedFilters.elevation) {
                const elevation = parseInt(trail.elevation_gain);
                if (advancedFilters.elevation === '0-100' && elevation >= 100) return false;
                if (advancedFilters.elevation === '100-300' && (elevation < 100 || elevation >= 300)) return false;
                if (advancedFilters.elevation === '300-600' && (elevation < 300 || elevation >= 600)) return false;
                if (advancedFilters.elevation === '600-1000' && (elevation < 600 || elevation >= 1000)) return false;
                if (advancedFilters.elevation === '1000+' && elevation < 1000) return false;
            }

            // Features filter (OR logic - trail must have ANY of the selected features)
            if (advancedFilters.features.length > 0) {
                if (!trail.highlights || trail.highlights.length === 0) {
                    return false;
                }
                
                const trailFeatureTypes = trail.highlights.map(h => h.type);
                const hasAnyFeature = advancedFilters.features.some(feature => 
                    trailFeatureTypes.includes(feature)
                );
                
                if (!hasAnyFeature) {
                    return false;
                }
            }

            // Activities filter (AND logic - trail must have ALL selected activities)
            if (advancedFilters.activities.length > 0) {
                if (!trail.activities || trail.activities.length === 0) {
                    return false;
                }
                
                const trailActivityTypes = trail.activities.map(a => a.type || a.slug || a.name);
                const hasAnyActivity = advancedFilters.activities.some(activity => 
                    trailActivityTypes.includes(activity)
                );
                
                if (!hasAnyActivity) {
                    return false;
                }
            }

            return true;
        }

        applyAdvancedFilters(filters) {
            // This method is called from the global applyAllFilters function
            // Trigger a re-render of the map and list
            this.applyFilters();
        }

        updateVisibleTrails() {
            const visibleTrails = this.getVisibleTrails();
            this.renderTrailList(visibleTrails, this.businessData || []);
        }

        // Add this function to your EnhancedTrailMap class
        getDistanceColor(distance) {
            return '#8B5E3C';
        }

        // Winter: color network-trail routes by difficulty (1=very easy, 2=easy, 3=moderate, 4=hard, 5=very hard)
        getDifficultyColor(difficulty) {
            const level = parseInt(difficulty, 10);
            switch (level) {
                case 2: return '#22C55E';            // Easy → green
                case 1:
                case 3: return '#F97316';            // Very Easy / Moderate → orange
                case 4:
                case 5: return '#EF4444';            // Hard / Very Hard → red
                default: return this.getDistanceColor();
            }
        }

        getRouteColor(trail) {
            if (this.currentSeason === 'winter' && trail.trail_network_id) {
                return this.getDifficultyColor(trail.difficulty);
            }
            return this.getDistanceColor(trail.distance);
        }

        // For non-fishing trails, pick the activity that should drive the marker icon/colour.
        // Prefer hiking when present, otherwise fall back to the first activity.
        getTrailDisplayActivity(trail) {
            if (!trail || !Array.isArray(trail.activities) || trail.activities.length === 0) {
                return null;
            }
            return trail.activities.find(a => a.type === 'hiking') || trail.activities[0];
        }

        buildRouteGeoJSON(trails) {
            // Build a GeoJSON FeatureCollection from filtered trails for Mapbox source
            const features = [];
            trails.forEach(trail => {
                if (trail.location_type === 'fishing_lake') return;
                if (!trail.route_coordinates || trail.route_coordinates.length === 0) return;

                const sanitized = trail.route_coordinates
                    .map(c => this.sanitizeCoordinates(c))
                    .filter(c => c !== null);
                if (sanitized.length === 0) return;

                const smoothed = this.smoothCoordinates(sanitized);
                // Mapbox coords are [lng, lat], data is [lat, lng] — swap
                const mapboxCoords = smoothed.map(c => [c[1], c[0]]);

                features.push({
                    type: 'Feature',
                    id: trail.id,
                    properties: {
                        trailId: trail.id,
                        color: this.getRouteColor(trail),
                        status: trail.status || 'active',
                    },
                    geometry: { type: 'LineString', coordinates: mapboxCoords },
                });
            });
            return { type: 'FeatureCollection', features };
        }

        highlightTrailRoute(trailId) {
            // Deselect previous
            if (this._selectedTrailId !== null) {
                try {
                    this.map.setFeatureState(
                        { source: 'trail-routes', id: this._selectedTrailId },
                        { selected: false }
                    );
                } catch(e) { /* ignore */ }
            }
            // Select new
            this._selectedTrailId = trailId;
            try {
                this.map.setFeatureState(
                    { source: 'trail-routes', id: trailId },
                    { selected: true }
                );
            } catch(e) { /* ignore */ }

            const trail = this.allTrails.find(t => t.id == trailId);
            if (trail) this.showTrailInfo(trail);
        }

        switchSeason(season) {
            
            // Update current season
            this.currentSeason = season;
            
            // Update UI - all season buttons
            document.querySelectorAll('.season-btn, .season-btn-mobile').forEach(btn => {
                if (btn.dataset.season === season) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });

            // Update activity filters based on season
            this.updateActivityFilters(season);
            
            // Reload trails with new season
            this.loadTrails(season);
        }

        toggle3D() {
            this._is3D = !this._is3D;
            if (this._is3D) {
                this.map.setTerrain({ source: 'mapbox-dem', exaggeration: 1.5 });
            } else {
                this.map.setTerrain(null);
            }
            this.map.easeTo({
                pitch: this._is3D ? 60 : 0,
                bearing: this._is3D ? -10 : 0,
                duration: 800,
            });
            const btn = document.getElementById('toggle-3d-btn');
            if (btn) {
                btn.innerHTML = this._is3D
                    ? `<span class="font-bold text-xs">2D</span>`
                    : `<span class="font-bold text-xs">3D</span>`;
                btn.title = this._is3D ? 'Switch to 2D' : 'Switch to 3D';
                btn.classList.toggle('bg-primary-600', this._is3D);
                btn.classList.toggle('text-white', this._is3D);
                btn.classList.toggle('bg-white', !this._is3D);
                btn.classList.toggle('text-gray-700', !this._is3D);
            }
        }

        switchMapType(mapType) {
            this.currentMapType = mapType;
            const style = this.mapStyles[mapType] || this.mapStyles['outdoors'];
            this.map.setStyle(style);

            document.querySelectorAll('.layer-option-card').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.mapType === mapType);
            });
            document.getElementById('layers-dropdown').classList.add('hidden');

            // Re-render markers with updated color once the new style has loaded
            this.map.once('styledata', () => {
                this._initMapLayers();
                this.applyFilters();
                this.renderBusinessMarkers();
                this.loadFacilities();
            });
        }

        showToast(message, duration = 3000) {
            const existing = document.getElementById('map-toast');
            if (existing) existing.remove();

            const toast = document.createElement('div');
            toast.id = 'map-toast';
            toast.textContent = message;
            toast.style.cssText = 'position:fixed;bottom:80px;left:50%;transform:translateX(-50%);background:#1f2937;color:#fff;padding:10px 18px;border-radius:8px;font-size:14px;z-index:9999;box-shadow:0 4px 12px rgba(0,0,0,0.3);pointer-events:none;';
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), duration);
        }

        searchTrails(searchTerm) {
            if (!searchTerm) {
                this.updateVisibleTrails();
                return;
            }

            // Search trails
            const visibleTrails = this.getVisibleTrails();
            const matchedTrails = visibleTrails.filter(trail =>
                trail.name.toLowerCase().includes(searchTerm) ||
                (trail.location && trail.location.toLowerCase().includes(searchTerm)) ||
                (trail.description && trail.description.toLowerCase().includes(searchTerm))
            );

            // Search businesses
            const matchedBusinesses = (this.businessData || []).filter(b =>
                b.name.toLowerCase().includes(searchTerm) ||
                (b.address && b.address.toLowerCase().includes(searchTerm)) ||
                (b.tagline && b.tagline.toLowerCase().includes(searchTerm)) ||
                (b.business_type_label && b.business_type_label.toLowerCase().includes(searchTerm))
            );

            this.renderTrailList(matchedTrails, matchedBusinesses);
        }

        updateFilters() {
            // Get active activity filters
            this.activeFilters = Array.from(
                document.querySelectorAll('.activity-filter:checked')
            ).map(cb => cb.dataset.activity);

            this.applyFilters();
        }

        applyFilters() {
            // Remove all existing overlay markers
            Object.values(this.overlayMarkers).forEach(markers => {
                markers.forEach(m => m.remove());
            });
            Object.keys(this.overlayMarkers).forEach(k => { this.overlayMarkers[k] = []; });

            const visibleTrails = this.getVisibleTrails();
            this.renderTrailList(visibleTrails, this.businessData || []);

            const allFilteredTrails = this.filterTrails(this.allTrails);

            // Trails to draw on the map (routes + point markers) — filtered by location filter
            let mapTrails;
            if (this.activeLocationFilter === 'business') {
                mapTrails = [];
            } else if (this.activeLocationFilter === 'fishing_lake') {
                mapTrails = allFilteredTrails.filter(t => t.location_type === 'fishing_lake');
            } else {
                mapTrails = allFilteredTrails.filter(t => t.location_type !== 'fishing_lake');
            }

            // Update route GeoJSON source
            const source = this.map.getSource('trail-routes');
            if (source) {
                source.setData(this.buildRouteGeoJSON(mapTrails));
                // Reset selected state after data update
                if (this._selectedTrailId) {
                    setTimeout(() => {
                        try {
                            this.map.setFeatureState(
                                { source: 'trail-routes', id: this._selectedTrailId },
                                { selected: true }
                            );
                        } catch(e) { /* ignore */ }
                    }, 100);
                }
            }

            mapTrails.forEach(trail => {
                // Network trails are represented by a single network marker — skip individual markers
                if (trail.trail_network_id) { return; }

                const isFishingLake = trail.location_type === 'fishing_lake';

                // Highlight markers
                if (this.activeFilters.includes('highlights')) {
                    this.createHighlightMarkers(trail);
                }

                if (isFishingLake) {
                    if (!this.overlayMarkers['fishing']) this.overlayMarkers['fishing'] = [];
                    const marker = this.createTrailMarker(trail, { type: 'fishing', icon: '🎣', color: '#3B82F6' });
                    if (marker) this.overlayMarkers['fishing'].push(marker);
                } else if (this.currentSeason === 'summer') {
                    if (this.activeFilters.includes('hiking')) {
                        if (!this.overlayMarkers['hiking']) this.overlayMarkers['hiking'] = [];
                        const displayActivity = this.getTrailDisplayActivity(trail);
                        const markerConfig = {
                            type: 'hiking',
                            icon: (displayActivity && displayActivity.icon) || '🥾',
                            color: (displayActivity && displayActivity.color) || '#10B981',
                        };
                        const marker = this.createTrailMarker(trail, markerConfig);
                        if (marker) this.overlayMarkers['hiking'].push(marker);
                    }
                } else {
                    trail.activities.forEach(activity => {
                        if (this.activeFilters.includes(activity.type)) {
                            if (!this.overlayMarkers[activity.type]) this.overlayMarkers[activity.type] = [];
                            const marker = this.createTrailMarker(trail, activity);
                            if (marker) this.overlayMarkers[activity.type].push(marker);
                        }
                    });
                }
            });

            // Show markers for active filters
            this.activeFilters.forEach(activityType => {
                (this.overlayMarkers[activityType] || []).forEach(m => m.addTo(this.map));
            });
        }

        matchesDistanceFilter(distance, filter) {
            switch(filter) {
                case '0-5': return distance <= 5;
                case '5-10': return distance > 5 && distance <= 10;
                case '10-20': return distance > 10 && distance <= 20;
                case '20+': return distance > 20;
                default: return true;
            }
        }

        // Unified marker color — light green on satellite, dark teal otherwise
        get markerColor() { return this.currentMapType === 'satellite' ? '#4ade80' : '#1B3935'; }

        _createMarkerEl(emoji) {
            const el = document.createElement('div');
            el.className = 'selectable-marker-el';
            el.dataset.emoji = emoji;
            el.style.cssText = `background-color:${this.markerColor};width:32px;height:32px;border-radius:50%;border:2px solid #ffffff;box-shadow:0 2px 8px rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;font-size:15px;cursor:pointer;line-height:1;`;
            el.textContent = emoji;
            return el;
        }

        _createSelectedPinEl(emoji) {
            const color = this.markerColor;
            const el = document.createElement('div');
            el.style.cssText = 'width:48px;height:60px;position:relative;cursor:pointer;filter:drop-shadow(0 4px 10px rgba(0,0,0,0.5));';
            el.innerHTML = `
                <svg viewBox="0 0 48 60" width="48" height="60" style="display:block;">
                    <path d="M24 0C10.745 0 0 10.745 0 24C0 42 24 60 24 60C24 60 48 42 48 24C48 10.745 37.255 0 24 0Z" fill="${color}"/>
                    <circle cx="24" cy="24" r="13" fill="white"/>
                </svg>
                <span style="position:absolute;top:24px;left:50%;transform:translate(-50%,-50%);font-size:15px;line-height:1;pointer-events:none;">${emoji}</span>
            `;
            return el;
        }

        _selectMarker(originalEl, lat, lng) {
            this._clearSelection();
            if (!originalEl) return;

            // Style the marker in place — never swap or hide it, so its lat/lng
            // anchor never changes and the marker can't visually jump on click.
            originalEl.classList.add('selected');
            this._selectedOriginalEl = originalEl;
        }

        _clearSelection() {
            if (this._selectedPinMarker) {
                this._selectedPinMarker.remove();
                this._selectedPinMarker = null;
            }
            if (this._selectedOriginalEl) {
                this._selectedOriginalEl.classList.remove('selected');
                this._selectedOriginalEl.style.visibility = '';
                this._selectedOriginalEl = null;
            }
        }

        createTrailMarker(trail, activity) {
            const coords = this.sanitizeCoordinates(trail.coordinates);
            if (!coords) {
                console.warn('Invalid coordinates for trail:', trail.name);
                return null;
            }

            const isFishingLake = trail.location_type === 'fishing_lake';
            const emoji = isFishingLake ? '🎣' : (activity.icon || '📍');

            const el = this._createMarkerEl(emoji);
            el.dataset.trailId = trail.id;
            el.addEventListener('click', (e) => {
                e.stopPropagation();
                this._selectMarker(el, coords[0], coords[1]);
                this.focusOnTrail(trail);
            });

            // [lng, lat] for Mapbox
            return new mapboxgl.Marker({ element: el, anchor: 'center' })
                .setLngLat([coords[1], coords[0]]);
        }

        createHighlightMarkers(trail) {
            if (!trail.highlights || trail.highlights.length === 0) return;

            trail.highlights.forEach(highlight => {
                const el = this._createMarkerEl(highlight.icon || '📍');
                const coords = highlight.coordinates; // [lat, lng]
                el.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this._selectMarker(el, coords[0], coords[1]);
                    this.showHighlightInfo(trail, highlight);
                });
                const marker = new mapboxgl.Marker({ element: el, anchor: 'center' })
                    .setLngLat([coords[1], coords[0]])
                    .addTo(this.map);

                if (!this.overlayMarkers['highlights']) this.overlayMarkers['highlights'] = [];
                this.overlayMarkers['highlights'].push(marker);
            });
        }

        createHighlightPopupContent(trail, highlight) {
            return `
                <div class="max-w-xs">
                    <div class="flex items-center mb-2">
                        <div style="background-color: ${highlight.color};" class="w-8 h-8 rounded-full flex items-center justify-center text-white mr-2">
                            ${highlight.icon}
                        </div>
                        <h5 class="font-bold text-base">${highlight.name}</h5>
                    </div>
                    
                    <p class="text-xs text-gray-600 mb-2 capitalize">${highlight.type.replace('_', ' ')}</p>
                    
                    ${highlight.description ? `<p class="text-sm text-gray-700 mb-2">${highlight.description}</p>` : ''}
                    
                    <div class="text-xs text-gray-500 mb-2">
                        On trail: <a href="/trails/${trail.id}" class="text-primary-600 hover:underline">${trail.name}</a>
                    </div>
                    
                    <button onclick="window.trailMap.viewHighlight(${trail.id}, ${JSON.stringify(highlight.coordinates).replace(/"/g, '&quot;')})" 
                            class="bg-primary-500 text-white px-3 py-1 rounded text-xs hover:bg-primary-600 w-full">
                        Focus on Map
                    </button>
                </div>
            `;
        }

        showHighlightInfo(trail, highlight) {
            const panel = document.getElementById('trail-info-panel');
            const content = document.getElementById('trail-info-content');

            // Close business panel if open
            document.getElementById('business-panel')?.classList.add('hidden');

            // Hero — first photo or colored placeholder
            const firstPhoto = highlight.media?.find(m => m.media_type === 'photo');
            const heroGradient = highlight.color
                ? `linear-gradient(135deg, ${highlight.color}cc, ${highlight.color})`
                : 'linear-gradient(135deg, #4b5563, #1f2937)';
            const hero = firstPhoto
                ? `<div class="biz-panel-hero"><img src="${firstPhoto.url}" alt="${escapeHtml(highlight.name)}"></div>`
                : `<div class="biz-panel-hero" style="background:${heroGradient};"><div class="biz-panel-hero-placeholder">${highlight.icon || '📍'}</div></div>`;

            // Action buttons
            const coordsJson = JSON.stringify(highlight.coordinates).replace(/"/g, '&quot;');
            const actions = `
                <div class="biz-panel-actions">
                    <button onclick="window.trailMap.viewHighlight(${trail.id}, ${coordsJson})" class="biz-panel-action-btn">
                        <div class="biz-panel-action-icon" style="background:${highlight.color || '#16a34a'};">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <span class="biz-panel-action-label" style="color:${highlight.color || '#166534'};">Center</span>
                    </button>
                    <a href="/trails/${trail.id}" class="biz-panel-action-btn">
                        <div class="biz-panel-action-icon" style="background:${highlight.color || '#16a34a'};">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 9m0 8V9m0 0V7"/></svg>
                        </div>
                        <span class="biz-panel-action-label" style="color:${highlight.color || '#166534'};">Full Trail</span>
                    </a>
                </div>`;

            // Media grid (remaining photos/videos after hero)
            let mediaHTML = '';
            if (highlight.media && highlight.media.length > 0) {
                const mediaItems = firstPhoto
                    ? highlight.media.filter(m => m !== firstPhoto)
                    : highlight.media;
                if (mediaItems.length > 0) {
                    const cols = Math.min(mediaItems.length, 3);
                    mediaHTML = `
                        <hr class="biz-panel-divider">
                        <div style="display:grid;grid-template-columns:repeat(${cols},1fr);gap:6px;">
                            ${mediaItems.map(media => {
                                if (media.media_type === 'photo') {
                                    return `
                                        <div style="aspect-ratio:1;border-radius:8px;overflow:hidden;cursor:pointer;position:relative;"
                                            onclick="openHighlightMediaModal('${media.url}', 'photo', '${media.caption || highlight.name}')">
                                            <img src="${media.url}" alt="${media.caption || ''}" style="width:100%;height:100%;object-fit:cover;" loading="lazy">
                                            <div style="position:absolute;inset:0;background:rgba(0,0,0,0);transition:background 0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.25)'" onmouseout="this.style.background='rgba(0,0,0,0)'"></div>
                                        </div>`;
                                } else {
                                    const videoUrl = media.video_url || media.url;
                                    const thumb = getVideoThumbnail(videoUrl);
                                    return `
                                        <div style="aspect-ratio:1;border-radius:8px;overflow:hidden;cursor:pointer;position:relative;background:#111827;"
                                            onclick="openHighlightMediaModal('${videoUrl}', 'video', '${media.caption || highlight.name}')">
                                            ${thumb ? `<img src="${thumb}" style="width:100%;height:100%;object-fit:cover;" loading="lazy">` : ''}
                                            <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none;">
                                                <div style="background:rgba(255,255,255,0.9);border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;">
                                                    <svg width="14" height="14" fill="#111827" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                                                </div>
                                            </div>
                                        </div>`;
                                }
                            }).join('')}
                        </div>`;
                }
            }

            content.innerHTML = `
                <div style="position:relative;flex-shrink:0;">
                    ${hero}
                    <button onclick="closeTrailInfoPanel()"
                            style="position:absolute;top:10px;right:10px;background:rgba(255,255,255,0.92);border:none;cursor:pointer;border-radius:50%;width:34px;height:34px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,0.18);"
                            aria-label="Close">
                        <svg width="16" height="16" fill="none" stroke="#374151" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="biz-panel-body" style="flex:1;overflow-y:auto;">
                    <h2 class="biz-panel-name">${escapeHtml(highlight.name)}</h2>
                    <div class="biz-panel-meta">
                        <span class="biz-panel-type" style="color:${highlight.color || '#2563eb'};">${highlight.icon || ''} ${escapeHtml(highlight.type.replace(/_/g, ' '))}</span>
                        <span class="biz-panel-dot">·</span>
                        <span style="font-size:12px;color:#6b7280;">on ${escapeHtml(trail.name)}</span>
                    </div>
                    ${actions}
                    ${highlight.description ? `
                        <hr class="biz-panel-divider">
                        <p style="font-size:13px;color:#4b5563;line-height:1.6;margin:0;">${escapeHtml(highlight.description)}</p>
                    ` : ''}
                    ${mediaHTML}
                </div>
            `;

            panel.classList.remove('hidden');
        }

        viewHighlight(trailId, coordinates) {
            // coordinates is [lat, lng], Mapbox needs [lng, lat]
            this.map.flyTo({ center: [coordinates[1], coordinates[0]], zoom: 16, duration: 1000 });
        }

        createPopupContent(trail) {
            const seasonalNote = trail.seasonal_info?.notes ? 
                `<div class="text-xs text-blue-600 mt-1">${trail.seasonal_info.notes}</div>` : '';

            return `
                <div class="max-w-sm">
                    <h5 class="font-bold text-lg mb-2">${escapeHtml(trail.name)}</h5>
                    <div class="grid grid-cols-2 gap-2 text-xs mb-2">
                        <span><strong>Distance:</strong> ${trail.distance} km</span>
                        <span><strong>Difficulty:</strong> ${trail.difficulty}/5</span>
                        <span><strong>Elevation:</strong> ${trail.elevation_gain || 0}m</span>
                        <span><strong>Time:</strong> ${trail.estimated_time || 'N/A'}h</span>
                    </div>
                    ${seasonalNote}
                    <div class="mt-3">
                        <button onclick="window.trailMap.viewRoute(${trail.id})" class="bg-primary-500 text-white px-3 py-1 rounded text-sm hover:bg-primary-600 inline-block">
                            View Route
                        </button>
                    </div>
                </div>
            `;
        }

        viewRoute(trailId) {
            const trail = this.allTrails.find(t => t.id == trailId);
            if (!trail) return;

            if (!trail.route_coordinates || trail.route_coordinates.length === 0) {
                this.showToast('Route data is not available for this trail.');
                return;
            }

            this.highlightTrailRoute(trailId);

            // Fit map to route bounds
            const sanitized = trail.route_coordinates
                .map(c => this.sanitizeCoordinates(c)).filter(c => c !== null);
            if (sanitized.length > 0) {
                const lngs = sanitized.map(c => c[1]);
                const lats = sanitized.map(c => c[0]);
                this.map.fitBounds([
                    [Math.min(...lngs), Math.min(...lats)],
                    [Math.max(...lngs), Math.max(...lats)]
                ], { padding: 40, maxZoom: 18 });
            }
        }

        clearRoute() {
            // Deselect highlighted trail
            if (this._selectedTrailId !== null) {
                try {
                    this.map.setFeatureState(
                        { source: 'trail-routes', id: this._selectedTrailId },
                        { selected: false }
                    );
                } catch(e) { /* ignore */ }
                this._selectedTrailId = null;
            }
        }

        showTrailInfo(trail) {
            const panel = document.getElementById('trail-info-panel');
            const content = document.getElementById('trail-info-content');

            if (!trail) return;

            const isFishingLake = trail.location_type === 'fishing_lake';

            // Stop any active fly animation when switching trails
            if (this._isFlying) this.stopFlyAnimation();

            // Close business panel if open (without clearing selection — trail selection is being set)
            document.getElementById('business-panel')?.classList.add('hidden');

            // Hero image or placeholder
            const imageUrl = trail.preview_photo || (trail.photos && trail.photos.length > 0 ? trail.photos[0].url : null);
            const heroPlaceholderEmoji = isFishingLake ? '🎣' : '🥾';
            const heroGradient = isFishingLake
                ? 'linear-gradient(135deg, #0369a1, #0ea5e9)'
                : 'linear-gradient(135deg, #166534, #22c55e)';
            const hero = imageUrl
                ? `<div class="biz-panel-hero"><img src="${imageUrl}" alt="${escapeHtml(trail.name)}"></div>`
                : `<div class="biz-panel-hero" style="background:${heroGradient};"><div class="biz-panel-hero-placeholder">${heroPlaceholderEmoji}</div></div>`;

            // Meta badges — type tag + activities
            const metaParts = [];
            if (isFishingLake) {
                metaParts.push(`<span class="biz-panel-type">🎣 Fishing Lake</span>`);
                if (trail.status) {
                    const statusBg = trail.status === 'active' ? '#dcfce7' : (trail.status === 'closed' ? '#fee2e2' : '#fef3c7');
                    const statusColor = trail.status === 'active' ? '#166534' : (trail.status === 'closed' ? '#991b1b' : '#92400e');
                    const statusLabel = trail.status === 'active' ? 'Open' : (trail.status === 'closed' ? 'Closed' : 'Seasonal');
                    metaParts.push(`<span class="biz-panel-dot">·</span><span style="font-size:12px;font-weight:700;background:${statusBg};color:${statusColor};padding:2px 8px;border-radius:999px;">${statusLabel}</span>`);
                }
            } else {
                metaParts.push(`<span class="biz-panel-type">🥾 Hiking Trail</span>`);
            }
            if (trail.activities && trail.activities.length > 0) {
                trail.activities.forEach(activity => {
                    metaParts.push(`<span class="biz-panel-dot">·</span><span style="font-size:12px;font-weight:600;background:${activity.color}20;color:${activity.color};padding:2px 8px;border-radius:999px;">${activity.icon} ${activity.name}</span>`);
                });
            }

            // Action buttons — green theme (border+shadow handled by CSS, icon/label color overrides)
            const trailActionIcon = `style="background:#16a34a;"`;
            const trailActionLabel = `style="color:#166534;"`;
            const trailActionBtn = ``;
            const actions = [];
            if (isFishingLake) {
                actions.push(`
                    <button onclick="window.trailMap.viewRoute(${trail.id})" class="biz-panel-action-btn" ${trailActionBtn}>
                        <div class="biz-panel-action-icon" ${trailActionIcon}>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <span class="biz-panel-action-label" ${trailActionLabel}>View Location</span>
                    </button>`);
            } else {
                actions.push(`
                    <button onclick="window.trailMap.viewRoute(${trail.id})" class="biz-panel-action-btn" ${trailActionBtn}>
                        <div class="biz-panel-action-icon" ${trailActionIcon}>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 9m0 8V9m0 0V7"/></svg>
                        </div>
                        <span class="biz-panel-action-label" ${trailActionLabel}>View Route</span>
                    </button>`);
                if (trail.route_coordinates && trail.route_coordinates.length > 1) {
                    actions.push(`
                        <button id="fly-along-btn" onclick="window.trailMap.flyAlongTrail(${trail.id})" class="biz-panel-action-btn" ${trailActionBtn}>
                            <div class="biz-panel-action-icon" ${trailActionIcon}>
                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M13.49 5.48c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm-3.6 13.9 1-4.4 2.1 2v6h2v-7.5l-2.1-2 .6-3c1.3 1.5 3.3 2.5 5.5 2.5v-2c-1.9 0-3.5-1-4.3-2.4l-1-1.6c-.4-.6-1-1-1.7-1-.3 0-.5.1-.8.1l-5.2 2.2v4.7h2v-3.4l1.8-.7-1.6 8.1-4.9-1-.4 2 7 1.4z"/></svg>
                            </div>
                            <span class="biz-panel-action-label" ${trailActionLabel}>Fly Along</span>
                        </button>`);
                }
            }
            actions.push(`
                <a href="/trails/${trail.id}" target="_blank" class="biz-panel-action-btn" ${trailActionBtn}>
                    <div class="biz-panel-action-icon" ${trailActionIcon}>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="biz-panel-action-label" ${trailActionLabel}>Details</span>
                </a>`);

            // Stats section
            let statsHTML = '';
            if (isFishingLake) {
                const fishSpeciesHTML = trail.fish_species && trail.fish_species.length > 0
                    ? `<div class="mb-4">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Fish Species</div>
                        <div class="flex flex-wrap gap-1.5">
                            ${trail.fish_species.map(s => `<span style="display:inline-flex;align-items:center;border-radius:999px;padding:2px 10px;font-size:12px;font-weight:500;background:#dbeafe;color:#1e40af;">${s}</span>`).join('')}
                        </div>
                    </div>`
                    : '';
                const distanceText = trail.fishing_distance_from_town
                    ? `${trail.fishing_distance_from_town} KM from Smithers`
                    : null;
                statsHTML = `
                    ${trail.fishing_location || distanceText ? `
                    <div class="biz-panel-info-row">
                        <svg class="biz-panel-info-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>${escapeHtml(trail.fishing_location || '')}${trail.fishing_location && distanceText ? ' · ' : ''}${distanceText || ''}</span>
                    </div>` : ''}
                    ${fishSpeciesHTML}`;
            } else {
                statsHTML = `
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px;">
                        <div style="background:#eff6ff;border-radius:10px;padding:12px;text-align:center;">
                            <div style="font-size:20px;font-weight:800;color:#2563eb;">${trail.distance || '—'}</div>
                            <div style="font-size:11px;color:#6b7280;margin-top:2px;">km</div>
                        </div>
                        <div style="background:#f0fdf4;border-radius:10px;padding:12px;text-align:center;">
                            <div style="font-size:20px;font-weight:800;color:#16a34a;">${trail.elevation_gain || 0}</div>
                            <div style="font-size:11px;color:#6b7280;margin-top:2px;">meters gain</div>
                        </div>
                        <div style="background:#fefce8;border-radius:10px;padding:12px;text-align:center;">
                            <div style="font-size:20px;font-weight:800;color:#ca8a04;">${trail.estimated_time || '—'}</div>
                            <div style="font-size:11px;color:#6b7280;margin-top:2px;">hours</div>
                        </div>
                        <div style="background:#faf5ff;border-radius:10px;padding:12px;text-align:center;">
                            <div style="font-size:20px;font-weight:800;color:#9333ea;text-transform:capitalize;">${trail.difficulty || '—'}</div>
                            <div style="font-size:11px;color:#6b7280;margin-top:2px;">difficulty</div>
                        </div>
                    </div>`;
            }

            // Highlights
            let highlightsHTML = '';
            if (trail.highlights && trail.highlights.length > 0) {
                highlightsHTML = `
                    <hr class="biz-panel-divider">
                    <div style="font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:10px;">Trail Highlights</div>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        ${trail.highlights.map(highlight => `
                            <div style="display:flex;align-items:flex-start;gap:10px;padding:10px;background:#faf5ff;border-radius:10px;cursor:pointer;"
                                onclick="window.trailMap.focusOnHighlight(${highlight.coordinates[0]}, ${highlight.coordinates[1]})">
                                ${highlight.photo_url ? `<img src="${highlight.photo_url}" alt="${highlight.name}" style="width:52px;height:52px;object-fit:cover;border-radius:8px;flex-shrink:0;">` : ''}
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:13px;font-weight:600;color:#111827;">${highlight.name}</div>
                                    ${highlight.description ? `<div style="font-size:12px;color:#6b7280;margin-top:2px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">${highlight.description}</div>` : ''}
                                </div>
                            </div>
                        `).join('')}
                    </div>`;
            }

            content.innerHTML = `
                <div style="position:relative;flex-shrink:0;">
                    ${hero}
                    <button onclick="closeTrailInfoPanel()"
                            style="position:absolute;top:10px;right:10px;background:rgba(255,255,255,0.92);border:none;cursor:pointer;border-radius:50%;width:34px;height:34px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,0.18);"
                            aria-label="Close">
                        <svg width="16" height="16" fill="none" stroke="#374151" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="biz-panel-body" style="flex:1;overflow-y:auto;">
                    <h2 class="biz-panel-name">${escapeHtml(trail.name)}</h2>
                    <div class="biz-panel-meta">${metaParts.join('')}</div>
                    ${actions.length ? `<div class="biz-panel-actions">${actions.join('')}</div>` : ''}
                    <hr class="biz-panel-divider">
                    ${statsHTML}
                    ${trail.description ? `<p style="font-size:13px;color:#4b5563;line-height:1.6;margin:0 0 4px;">${escapeHtml(trail.description)}</p>` : ''}
                    ${highlightsHTML}
                </div>
            `;

            panel.classList.remove('hidden');
        }

        /**
         * Smooth a coordinate array using a moving average.
         * Preserves the first and last point so trail endpoints stay accurate.
         * @param {Array} coords  - Array of [lat, lng] pairs
         * @param {number} window - Averaging window size (odd number recommended)
         * @param {number} passes - Number of smoothing passes
         */
        smoothCoordinates(coords, window = 3, passes = 2) {
            if (!coords || coords.length < 3) return coords;

            let result = coords;
            const half = Math.floor(window / 2);

            for (let p = 0; p < passes; p++) {
                const smoothed = [result[0]]; // keep first point

                for (let i = 1; i < result.length - 1; i++) {
                    const start = Math.max(0, i - half);
                    const end = Math.min(result.length - 1, i + half);
                    let sumLat = 0, sumLng = 0, count = 0;

                    for (let j = start; j <= end; j++) {
                        sumLat += result[j][0];
                        sumLng += result[j][1];
                        count++;
                    }

                    smoothed.push([sumLat / count, sumLng / count]);
                }

                smoothed.push(result[result.length - 1]); // keep last point
                result = smoothed;
            }

            return result;
        }

        sanitizeCoordinates(coords) {
            if (!coords) return null;
            
            // If it's already a proper [lat, lng] array
            if (Array.isArray(coords) && coords.length === 2 && 
                typeof coords[0] === 'number' && typeof coords[1] === 'number') {
                return coords;
            }
            
            // If coordinates are nested (common issue)
            if (Array.isArray(coords) && Array.isArray(coords[0])) {
                return this.sanitizeCoordinates(coords[0]);
            }
            
            return null;
        }

        focusOnTrail(trail) {
            if (!trail || !trail.coordinates) {
                console.warn('Trail has no coordinates:', trail);
                return;
            }

            // Reflect selection on the map marker (in case this was triggered by a list click)
            const markerEl = document.querySelector(`.selectable-marker-el[data-trail-id="${trail.id}"]`);
            const coords = this.sanitizeCoordinates(trail.coordinates);
            if (markerEl && coords) {
                this._selectMarker(markerEl, coords[0], coords[1]);
            }

            this.showTrailInfo(trail);

            const isFishingLake = trail.location_type === 'fishing_lake';
            if (!isFishingLake && trail.route_coordinates && trail.route_coordinates.length > 1) {
                try {
                    this.highlightTrailRoute(trail.id);
                    const sanitized = trail.route_coordinates
                        .map(c => this.sanitizeCoordinates(c)).filter(c => c !== null);
                } catch (error) {
                    console.error('Error focusing trail route:', error);
                }
            }
        }

        focusOnTrailById(trailId) {
            const trail = this.allTrails.find(t => t.id == trailId);
            if (!trail) { return; }

            // Trail belongs to a network — show the network panel instead
            if (trail.trail_network_id) {
                const network = (this.networkData || []).find(n => n.id == trail.trail_network_id);
                if (network) {
                    const marker = this.networkMarkers[network.id];
                    if (marker) {
                        const { lat, lng } = marker.getLngLat();
                        this._selectMarker(marker.getElement(), lat, lng);
                        this.map.flyTo({ center: [lng, lat], zoom: 13, duration: 800 });
                    }
                    this.showNetworkInfo(network);
                    return;
                }
            }

            this.focusOnTrail(trail);
        }

        focusOnBusiness(businessId) {
            const business = (this.businessData || []).find(b => b.id == businessId);
            if (business) {
                const markerEl = document.querySelector(`.selectable-marker-el[data-business-id="${business.id}"]`);
                if (markerEl) {
                    this._selectMarker(markerEl, business.latitude, business.longitude);
                }
                this.openBusinessPanel(business);
            }
        }

        openBusinessPanel(business) {
            const panel = document.getElementById('business-panel');
            const content = document.getElementById('business-panel-content');
            if (!panel || !content) { return; }

            const hero = business.photo_url
                ? `<div class="biz-panel-hero"><img src="${business.photo_url}" alt="${business.name}"></div>`
                : `<div class="biz-panel-hero"><div class="biz-panel-hero-placeholder">${business.icon}</div></div>`;

            const metaParts = [`<span class="biz-panel-type">${business.business_type_label}</span>`];
            if (business.price_range) {
                metaParts.push(`<span class="biz-panel-dot">·</span><span class="biz-panel-price-badge">${business.price_range}</span>`);
            }
            if (business.is_seasonal && business.season_open) {
                metaParts.push(`<span class="biz-panel-dot">·</span><span class="biz-panel-seasonal-badge">🗓 ${business.season_open}</span>`);
            }

            const tagline = business.tagline
                ? `<p class="biz-panel-tagline">${business.tagline}</p>`
                : '';

            // Action buttons
            const actions = [];
            if (business.phone) {
                actions.push(`
                    <a href="tel:${business.phone}" class="biz-panel-action-btn">
                        <div class="biz-panel-action-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <span class="biz-panel-action-label">Call</span>
                    </a>`);
            }
            if (business.website) {
                actions.push(`
                    <a href="${business.website}" target="_blank" class="biz-panel-action-btn">
                        <div class="biz-panel-action-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        </div>
                        <span class="biz-panel-action-label">Website</span>
                    </a>`);
            }
            if (business.facebook_url) {
                actions.push(`
                    <a href="${business.facebook_url}" target="_blank" class="biz-panel-action-btn">
                        <div class="biz-panel-action-icon">
                            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </div>
                        <span class="biz-panel-action-label">Facebook</span>
                    </a>`);
            }
            // Always show directions
            actions.push(`
                <a href="https://www.google.com/maps/search/?api=1&query=${business.latitude},${business.longitude}" target="_blank" class="biz-panel-action-btn">
                    <div class="biz-panel-action-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <span class="biz-panel-action-label">Directions</span>
                </a>`);

            // Info rows
            const infoRows = [];
            if (business.address) {
                infoRows.push(`
                    <div class="biz-panel-info-row">
                        <svg class="biz-panel-info-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>${business.address}</span>
                    </div>`);
            }
            if (business.phone) {
                infoRows.push(`
                    <div class="biz-panel-info-row">
                        <svg class="biz-panel-info-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <a href="tel:${business.phone}" class="biz-panel-info-link">${business.phone}</a>
                    </div>`);
            }
            if (business.website) {
                const host = (() => { try { return new URL(business.website).hostname.replace('www.',''); } catch(e) { return business.website; } })();
                infoRows.push(`
                    <div class="biz-panel-info-row">
                        <svg class="biz-panel-info-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        <a href="${business.website}" target="_blank" class="biz-panel-info-link">${host}</a>
                    </div>`);
            }

            content.innerHTML = `
                <div style="position:relative;flex-shrink:0;">
                    ${hero}
                    <button onclick="document.getElementById('business-panel').classList.add('hidden');window.trailMap?._clearSelection();"
                            style="position:absolute;top:10px;right:10px;background:rgba(255,255,255,0.92);border:none;cursor:pointer;border-radius:50%;width:34px;height:34px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,0.18);"
                            aria-label="Close">
                        <svg width="16" height="16" fill="none" stroke="#374151" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="biz-panel-body">
                    <h2 class="biz-panel-name">${escapeHtml(business.name)}</h2>
                    <div class="biz-panel-meta">${metaParts.join('')}</div>
                    ${tagline}
                    ${actions.length ? `<div class="biz-panel-actions">${actions.join('')}</div>` : ''}
                    ${infoRows.length ? `<hr class="biz-panel-divider">${infoRows.join('')}` : ''}
                </div>
            `;

            // Close trail panel if open
            document.getElementById('trail-info-panel')?.classList.add('hidden');

            panel.classList.remove('hidden');
        }

        closeBusinessPanel() {
            document.getElementById('business-panel')?.classList.add('hidden');
            this._clearSelection();
        }

        locateMe() {
            const btn = document.getElementById('my-location-btn');

            if (!navigator.geolocation) {
                alert('Your browser does not support geolocation.');
                return;
            }

            if (btn) btn.disabled = true;

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    // Remove previous location marker
                    if (this._locationMarker) this._locationMarker.remove();

                    // Pulsing dot (keep existing CSS animation)
                    const dotEl = document.createElement('div');
                    dotEl.className = 'my-location-marker';
                    dotEl.innerHTML = '<div class="my-location-dot"></div>';
                    dotEl.style.cssText = 'cursor:pointer;';

                    this._locationMarker = new mapboxgl.Marker({ element: dotEl, anchor: 'center' })
                        .setLngLat([lng, lat])
                        .setPopup(new mapboxgl.Popup({ offset: 12 })
                            .setHTML(`<div class="facility-popup-content" style="min-width:0;"><strong>You are here</strong></div>`))
                        .addTo(this.map);

                    this.map.flyTo({ center: [lng, lat], zoom: 15, duration: 800 });

                    if (btn) { btn.disabled = false; btn.classList.add('active'); }
                },
                (error) => {
                    if (btn) btn.disabled = false;
                    const messages = {
                        1: 'Location access was denied. Please allow location in your browser settings.',
                        2: 'Your location could not be determined. Try again.',
                        3: 'Location request timed out. Try again.',
                    };
                    alert(messages[error.code] || 'Unable to get your location.');
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 }
            );
        }

        focusOnHighlight(lat, lng) {
            this.map.flyTo({ center: [lng, lat], zoom: 16, duration: 1000 });
        }

        async loadTrails(seasonFilter = null) {
            try {
                const season = seasonFilter || this.currentSeason;
                const params = new URLSearchParams({
                    season,
                    filters: this.activeFilters.join(',')
                });

                // Add difficulty and distance filters if set
                if (this.currentDifficulty) {
                    params.append('difficulty', this.currentDifficulty);
                }
                if (this.currentDistance) {
                    params.append('distance', this.currentDistance);
                }

                const response = await fetch(`/api/trails?${params}`);
                if (!response.ok) {
                    throw new Error(`API returned ${response.status}: ${response.statusText}`);
                }
                this.allTrails = await response.json();
                
                // Render trail list
                this.updateVisibleTrails();
                
                this.applyFilters();
                
                // Check if there's a trail parameter in URL and focus on it
                const urlParams = new URLSearchParams(window.location.search);
                const trailId = urlParams.get('trail');
                
                if (trailId) {
                    setTimeout(() => {
                        const trail = this.allTrails.find(t => t.id == trailId);
                        if (trail) {
                            this.focusOnTrail(trail);
                        }
                    }, 500);
                }
            } catch (error) {
                console.error('Error loading trails:', error);
            }
        }

        async loadFacilities() {
            try {
                const response = await fetch('/api/facilities');
                const facilities = await response.json();

                facilities.forEach(facility => {
                    const el = this._createMarkerEl(facility.icon || '📍');

                    // Cache the full media list so the modal carousel can navigate it
                    if (facility.media && facility.media.length) {
                        window._facilityMediaCache[facility.id] = {
                            name: facility.name,
                            media: facility.media,
                        };
                    }

                    let popupContent = `<div class="facility-popup-content">
                        <div class="facility-popup-header">
                            <span class="facility-popup-icon">${facility.icon}</span>
                            <h3 class="facility-popup-title">${facility.name}</h3>
                        </div>
                        <p class="facility-popup-type">${facility.facility_type_label}</p>
                        ${facility.description ? `
                            <p class="facility-popup-description">${facility.description}</p>
                            <button type="button" class="facility-popup-readmore" data-state="collapsed">Read more</button>
                        ` : ''}`;

                    if (facility.media && facility.media.length > 0) {
                        popupContent += `<div class="facility-media-gallery">
                            <p class="facility-media-count">${facility.media_count} ${facility.media_count === 1 ? 'photo/video' : 'photos/videos'}</p>
                            <div class="facility-media-grid">`;
                        facility.media.slice(0, 4).forEach((media, index) => {
                            const isVideo = media.media_type === 'video_url' || media.media_type === 'video';
                            const thumbnailUrl = media.thumbnail_url || media.url;
                            const remainingCount = facility.media.length - 4;
                            const overlay = (index === 3 && remainingCount > 0)
                                ? `<div class="facility-media-overlay">+${remainingCount} more</div>`
                                : '';
                            const videoBadge = isVideo ? '<div class="facility-video-badge">▶</div>' : '';
                            popupContent += `<div class="facility-media-item" onclick="openFacilityMediaModal(${facility.id}, ${index})"><img src="${thumbnailUrl}" class="facility-media-thumbnail">${overlay}${videoBadge}</div>`;
                        });
                        popupContent += `</div></div>`;
                    }
                    popupContent += `</div>`;

                    const popup = new mapboxgl.Popup({ maxWidth: '320px', className: 'facility-popup', offset: 28 })
                        .setHTML(popupContent);

                    // Show the "Read more" button only if the description is actually
                    // clipped by the 3-line clamp. Runs after the popup is in the DOM.
                    popup.on('open', () => {
                        const root = popup.getElement();
                        if (!root) { return; }
                        const desc = root.querySelector('.facility-popup-description');
                        const btn = root.querySelector('.facility-popup-readmore');
                        if (!desc || !btn) { return; }
                        // Reset so re-opening after expand still re-evaluates correctly
                        desc.classList.remove('expanded');
                        btn.dataset.state = 'collapsed';
                        btn.textContent = 'Read more';
                        if (desc.scrollHeight > desc.clientHeight + 1) {
                            btn.style.display = 'inline';
                        } else {
                            btn.style.display = 'none';
                        }
                    });

                    el.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this._selectMarker(el, facility.latitude, facility.longitude);
                        popup.setLngLat([facility.longitude, facility.latitude]).addTo(this.map);
                    });

                    const marker = new mapboxgl.Marker({ element: el, anchor: 'center' })
                        .setLngLat([facility.longitude, facility.latitude]);

                    if (this.activeLocationFilter === 'trail') {
                        marker.addTo(this.map);
                    }

                    this.facilityMarkers.push(marker);
                });
            } catch (error) {
                console.error('Error loading facilities:', error);
            }
        }

        toggleFacilityVisibility() {
            const show = this.activeLocationFilter === 'trail';
            (this.facilityMarkers || []).forEach(m => {
                if (show) { m.addTo(this.map); } else { m.remove(); }
            });
        }

        async loadBusinesses() {
            try {
                const response = await fetch('/api/businesses');
                this.businessData = await response.json();
                this.renderBusinessMarkers();

                const params = new URLSearchParams(window.location.search);
                const businessId = params.get('business');
                if (businessId) {
                    const business = this.businessData.find(b => b.id == businessId);
                    if (business && business.latitude && business.longitude) {
                        this.map.flyTo({ center: [business.longitude, business.latitude], zoom: 17 });
                        setTimeout(() => {
                            const marker = this.businessMarkers[business.id];
                            if (marker) this.openBusinessPanel(business);
                        }, 800);
                    }
                }
            } catch (error) {
                console.error('Error loading businesses:', error);
            }
        }

        renderBusinessMarkers() {
            // Remove existing business markers
            Object.values(this.businessMarkers).forEach(m => m.remove());
            this.businessMarkers = {};

            if (!this.showBusinesses) return;
            if (this.activeLocationFilter !== 'business') return;

            (this.businessData || []).forEach(business => {
                const el = this._createMarkerEl(business.icon || '🏪');
                el.dataset.businessId = business.id;
                el.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this._selectMarker(el, business.latitude, business.longitude);
                    this.openBusinessPanel(business);
                });

                const marker = new mapboxgl.Marker({ element: el, anchor: 'center' })
                    .setLngLat([business.longitude, business.latitude])
                    .addTo(this.map);

                this.businessMarkers[business.id] = marker;
            });
        }

        async loadTrailNetworks() {
            try {
                const response = await fetch('/api/trail-networks');
                this.networkData = await response.json();
                this.renderNetworkMarkers();
            } catch (error) {
                console.error('Error loading trail networks:', error);
            }
        }

        _createNetworkMarkerEl(emoji) {
            const el = document.createElement('div');
            el.className = 'selectable-marker-el network-marker-el';
            el.dataset.emoji = emoji;
            el.style.cssText = 'background-color:#166534;width:38px;height:38px;border-radius:8px;border:2px solid #ffffff;box-shadow:0 3px 10px rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;font-size:17px;cursor:pointer;line-height:1;';
            el.textContent = emoji;
            return el;
        }

        renderNetworkMarkers() {
            Object.values(this.networkMarkers).forEach(m => m.remove());
            this.networkMarkers = {};

            if (this.activeLocationFilter !== 'trail') return;

            (this.networkData || []).forEach(network => {
                let lat = network.latitude;
                let lng = network.longitude;

                // Fall back to centroid of member trails if network has no coordinates
                if (!lat || !lng) {
                    const memberTrails = (this.allTrails || []).filter(t => t.trail_network_id == network.id);
                    const coords = memberTrails.map(t => this.sanitizeCoordinates(t.coordinates)).filter(Boolean);
                    if (coords.length === 0) { return; }
                    lat = coords.reduce((s, c) => s + c[0], 0) / coords.length;
                    lng = coords.reduce((s, c) => s + c[1], 0) / coords.length;
                }

                if (!lat || !lng) { return; }

                const type = (network.type || '').toLowerCase();
                let icon = network.icon || '🏔️';
                if (!network.icon) {
                    if (type.includes('ski') || type.includes('snow')) { icon = '⛷️'; }
                    else if (type.includes('hike') || type.includes('hiking')) { icon = '🥾'; }
                    else if (type.includes('bike') || type.includes('cycling')) { icon = '🚵'; }
                }

                const el = this._createNetworkMarkerEl(icon);
                el.dataset.networkId = network.id;

                el.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this._selectMarker(el, lat, lng);
                    this.showNetworkInfo(network);
                });

                const marker = new mapboxgl.Marker({ element: el, anchor: 'center' })
                    .setLngLat([lng, lat])
                    .addTo(this.map);

                this.networkMarkers[network.id] = marker;
            });
        }

        showNetworkInfo(network) {
            const panel = document.getElementById('trail-info-panel');
            const content = document.getElementById('trail-info-content');
            if (!panel || !content) { return; }

            document.getElementById('business-panel')?.classList.add('hidden');
            if (this._isFlying) { this.stopFlyAnimation(); }

            const type = (network.type || '').toLowerCase();
            let heroIcon = network.icon || '🏔️';
            if (!network.icon) {
                if (type.includes('ski') || type.includes('snow')) { heroIcon = '⛷️'; }
                else if (type.includes('hike') || type.includes('hiking')) { heroIcon = '🥾'; }
                else if (type.includes('bike') || type.includes('cycling')) { heroIcon = '🚵'; }
            }

            const hero = network.image
                ? `<div class="biz-panel-hero" style="background-image:url('${network.image}');background-size:cover;background-position:center;"></div>`
                : `<div class="biz-panel-hero" style="background:linear-gradient(135deg,#14532d,#166534);"><div class="biz-panel-hero-placeholder">${heroIcon}</div></div>`;

            const actions = [];
            actions.push(`
                <a href="/trail-networks/${network.slug}" class="biz-panel-action-btn">
                    <div class="biz-panel-action-icon" style="background:#166534;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 9m0 8V9m0 0V7"/></svg>
                    </div>
                    <span class="biz-panel-action-label" style="color:#14532d;">View Network</span>
                </a>`);
            if (network.website_url) {
                actions.push(`
                    <a href="${network.website_url}" target="_blank" class="biz-panel-action-btn">
                        <div class="biz-panel-action-icon" style="background:#166534;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        </div>
                        <span class="biz-panel-action-label" style="color:#14532d;">Website</span>
                    </a>`);
            }

            const diffLabel = ['', 'Very Easy', 'Easy', 'Moderate', 'Hard', 'Very Hard'];
            const trailRows = (network.trails || []).map(t => {
                const color = this.getDifficultyColor(t.difficulty);
                const label = diffLabel[t.difficulty] || ('Level ' + t.difficulty);
                return `
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f3f4f6;">
                        <span style="font-size:13px;color:#374151;font-weight:500;">${escapeHtml(t.name)}</span>
                        <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                            ${t.distance ? `<span style="font-size:11px;color:#6b7280;">${t.distance} km</span>` : ''}
                            ${t.difficulty ? `<span style="font-size:11px;font-weight:700;color:${color};background:${color}20;padding:2px 7px;border-radius:999px;">${label}</span>` : ''}
                        </div>
                    </div>`;
            }).join('');

            content.innerHTML = `
                <div style="position:relative;flex-shrink:0;">
                    ${hero}
                    <button onclick="closeTrailInfoPanel()"
                            style="position:absolute;top:10px;right:10px;background:rgba(255,255,255,0.92);border:none;cursor:pointer;border-radius:50%;width:34px;height:34px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,0.18);"
                            aria-label="Close">
                        <svg width="16" height="16" fill="none" stroke="#374151" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="biz-panel-body" style="flex:1;overflow-y:auto;">
                    <h2 class="biz-panel-name">${escapeHtml(network.name)}</h2>
                    <div class="biz-panel-meta">
                        <span class="biz-panel-type">🏔️ Trail Network</span>
                        ${network.type ? `<span class="biz-panel-dot">·</span><span style="font-size:12px;font-weight:600;background:#dcfce7;color:#166534;padding:2px 8px;border-radius:999px;">${escapeHtml(network.type)}</span>` : ''}
                        <span class="biz-panel-dot">·</span>
                        <span style="font-size:12px;color:#6b7280;">${network.trail_count} trail${network.trail_count !== 1 ? 's' : ''}</span>
                    </div>
                    <div class="biz-panel-actions">${actions.join('')}</div>
                    <hr class="biz-panel-divider">
                    ${network.description ? `<p style="font-size:13px;color:#4b5563;line-height:1.6;margin:0 0 16px;">${escapeHtml(network.description)}</p>` : ''}
                    ${trailRows ? `
                        <div style="font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Trails in this Network</div>
                        <div>${trailRows}</div>` : ''}
                </div>
            `;

            panel.classList.remove('hidden');
        }

        renderTrailList(trails, businesses = [], targetContainerId = null) {
            const container = document.getElementById(targetContainerId || 'trail-cards');
            const countElement = document.getElementById('trail-count');
            const totalCount = trails.length + businesses.length;
            if (countElement && !targetContainerId) { countElement.textContent = totalCount; }
            if (!container) { return; }

            if (trails.length === 0 && businesses.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="font-medium">No results found</p>
                        <p class="text-sm mt-2">Try adjusting your search or filters</p>
                    </div>
                `;
                return;
            }

            const renderBusinessCards = (items) => items.map(b => `
                <div class="business-list-card trail-list-card" data-location-type="business" data-business-id="${b.id}"
                    onclick="window.trailMap.focusOnBusiness(${b.id})">
                    <div class="flex-shrink-0 text-xl w-8 text-center">${b.icon}</div>
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-sm text-gray-900 truncate">${escapeHtml(b.name)}</div>
                        <div class="text-xs text-gray-500">${escapeHtml(b.business_type_label)}${b.address ? ' · ' + escapeHtml(b.address) : ''}</div>
                    </div>
                </div>
            `).join('');

            const renderTrailCards = (items) => items.map(trail => {
                // Use preview_photo or first photo from photos array
                const imageUrl = trail.preview_photo || (trail.photos && trail.photos.length > 0 ? trail.photos[0].url : null);

                // Winter + network-trail difficulty color accent on left border
                const showDifficultyAccent = this.currentSeason === 'winter'
                    && trail.trail_network_id
                    && trail.location_type !== 'fishing_lake';
                const accentStyle = showDifficultyAccent
                    ? `style="border-left: 4px solid ${this.getDifficultyColor(trail.difficulty)};"`
                    : '';

                // Activity-driven placeholder icon (falls back to 🥾 / 🎣 if no activities)
                const placeholderActivity = this.getTrailDisplayActivity(trail);
                const placeholderIcon = trail.location_type === 'fishing_lake'
                    ? '🎣'
                    : ((placeholderActivity && placeholderActivity.icon) || '🥾');

                return `
                    <div class="trail-list-card" data-location-type="${trail.location_type}" ${accentStyle} onclick="window.trailMap.focusOnTrailById(${trail.id})">
                        ${imageUrl ?
                            `<img src="${imageUrl}" alt="${trail.name}" class="trail-list-image">` :
                            `<div class="trail-list-image-placeholder" style="background: ${trail.location_type === 'fishing_lake' ? 'linear-gradient(135deg, #0369a1, #0ea5e9)' : 'linear-gradient(135deg, #166534, #22c55e)'};">
                                <span style="font-size: 2rem;">${placeholderIcon}</span>
                            </div>`
                        }
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-900 text-sm mb-1 truncate">${escapeHtml(trail.name)}</h3>
                            <p class="text-xs text-gray-600 mb-2">${escapeHtml(trail.location) || 'Location not specified'}</p>
                            
                            <div class="flex items-center gap-2 text-xs text-gray-600 mb-2">
                                ${trail.location_type === 'fishing_lake' ? `
                                    <span class="flex items-center">
                                        🎣 ${trail.fish_species && trail.fish_species.length > 0 ? trail.fish_species.length + ' species' : 'Various species'}
                                    </span>
                                    ${trail.best_fishing_season ? `
                                    <span class="flex items-center">
                                        🗓️ Best: <span class="capitalize ml-1">${trail.best_fishing_season}</span>
                                    </span>` : ''}
                                ` : `
                                    <span class="flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        </svg>
                                        ${trail.distance ? trail.distance + ' km' : 'Distance N/A'}
                                    </span>
                                    <span class="flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        ${trail.difficulty ? 'Level ' + trail.difficulty : 'Any level'}
                                    </span>
                                `}
                            </div>
                            
                            <div class="flex gap-1.5 flex-wrap">
                                ${trail.highlights && trail.highlights.length > 0 ? `
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                        ${trail.highlights.length} highlights
                                    </span>
                                ` : ''}
                                ${trail.activities && trail.activities.length > 0 ? 
                                    trail.activities.slice(0, 2).map(activity => `
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" style="background-color: ${activity.color}20; color: ${activity.color};">
                                            ${activity.icon} ${activity.name}
                                        </span>
                                    `).join('')
                                : ''}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            // Build grouped or flat output based on active filter
            const activeFilter = this.activeLocationFilter || 'all';
            const hikingTrails = trails.filter(t => t.location_type === 'trail');
            const fishingLakes = trails.filter(t => t.location_type === 'fishing_lake');

            const sectionHeader = (emoji, label, count) =>
                `<div class="px-1 pt-3 pb-1"><p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-1">${emoji} ${label} (${count})</p></div>`;

            if (activeFilter === 'all') {
                let html = '';
                if (hikingTrails.length > 0) {
                    html += sectionHeader('🥾', 'Hiking Trails', hikingTrails.length);
                    html += renderTrailCards(hikingTrails);
                }
                if (fishingLakes.length > 0) {
                    html += sectionHeader('🎣', 'Fishing Lakes', fishingLakes.length);
                    html += renderTrailCards(fishingLakes);
                }
                if (businesses.length > 0) {
                    html += sectionHeader('🏪', 'Businesses', businesses.length);
                    html += renderBusinessCards(businesses);
                }
                container.innerHTML = html;
            } else if (activeFilter === 'business') {
                container.innerHTML = sectionHeader('🏪', 'Businesses', businesses.length) + renderBusinessCards(businesses);
            } else if (activeFilter === 'trail') {
                container.innerHTML = sectionHeader('🥾', 'Hiking Trails', trails.length) + renderTrailCards(trails);
            } else if (activeFilter === 'fishing_lake') {
                container.innerHTML = sectionHeader('🎣', 'Fishing Lakes', trails.length) + renderTrailCards(trails);
            } else {
                container.innerHTML = renderTrailCards(trails);
            }
        }

        clearFilters() {
            // Reset checkboxes - keep hiking and highlights checked
            document.querySelectorAll('.activity-filter').forEach(cb => {
                cb.checked = cb.dataset.activity === 'hiking' || cb.dataset.activity === 'highlights';
            });

            // Reset selects
            const difficultyFilterEl = document.getElementById('difficulty-filter');
            const distanceFilterEl = document.getElementById('distance-filter');
            if (difficultyFilterEl) difficultyFilterEl.value = '';
            if (distanceFilterEl) distanceFilterEl.value = '';

            // Update filters
            this.activeFilters = ['hiking', 'fishing', 'highlights'];
            this.applyFilters();
        }

        // ── Fly Along Trail ─────────────────────────────────────────────────

        flyAlongTrail(trailId) {
            // If already flying (including the pending timeout phase), stop cleanly first
            if (this._isFlying || this._flyTimeout) {
                this.stopFlyAnimation();
                return;
            }

            const trail = this.allTrails.find(t => t.id == trailId);
            if (!trail || !trail.route_coordinates || trail.route_coordinates.length < 2) {
                this.showToast('No route available for this trail.');
                return;
            }

            const coords = trail.route_coordinates
                .map(c => this.sanitizeCoordinates(c))
                .filter(c => c !== null);

            if (coords.length < 2) {
                this.showToast('Not enough route data for animation.');
                return;
            }

            const smoothed = this.smoothCoordinates(coords, 5, 2);

            // Clear UI so the animation has a full map stage
            if (typeof closeTrailInfoPanel === 'function') { closeTrailInfoPanel(); }
            if (window.trailListPanelApi && !window.trailListPanelApi.isCollapsed()) {
                this._flyAutoCollapsed = true;
                window.trailListPanelApi.collapse();
            } else {
                this._flyAutoCollapsed = false;
            }
            const stopBtn = document.getElementById('fly-stop-overlay-btn');
            if (stopBtn) { stopBtn.classList.remove('hidden'); }

            this._isFlying = true;
            this._updateFlyButton(true);

            // Switch to satellite for the flyover; remember previous style to restore on stop
            this._preFlyMapType = this.currentMapType;
            const needsStyleSwitch = this.currentMapType !== 'satellite';
            if (needsStyleSwitch) {
                this.switchMapType('satellite');
            }

            const beginAnimation = () => {
                if (!this._isFlying) return;

                // Place hiker marker at trail start
                if (this._hikerMarker) { this._hikerMarker.remove(); this._hikerMarker = null; }
                this._hikerMarker = new mapboxgl.Marker({ element: this._createHikerMarkerEl(), anchor: 'center' })
                    .setLngLat([smoothed[0][1], smoothed[0][0]])
                    .addTo(this.map);

                // Fit full trail into view, then begin animation once camera settles
                const lngs = smoothed.map(c => c[1]);
                const lats = smoothed.map(c => c[0]);
                this.map.fitBounds(
                    [[Math.min(...lngs), Math.min(...lats)], [Math.max(...lngs), Math.max(...lats)]],
                    { padding: 80, maxZoom: 14, duration: 1500 }
                );

                this._flyTimeout = setTimeout(() => {
                    this._flyTimeout = null;
                    if (!this._isFlying) return;
                    this._animateAlongTrail(smoothed);
                }, 1800);
            };

            if (needsStyleSwitch) {
                // Wait for the new style to fully load before placing the marker / fitting bounds
                this.map.once('idle', beginAnimation);
            } else {
                beginAnimation();
            }
        }

        _createHikerMarkerEl() {
            const el = document.createElement('div');
            el.style.cssText = [
                'width:48px', 'height:48px',
                'pointer-events:none', 'user-select:none',
                'filter:drop-shadow(0 2px 6px rgba(0,0,0,0.45))',
            ].join(';');
            el.innerHTML = `<img src="{{ asset('images/hiking-person.png') }}" alt="Hiker" style="width:100%;height:100%;display:block;object-fit:contain;">`;
            return el;
        }

        _animateAlongTrail(rawCoords) {
            // Strip any entry that is not a clean [lat, lng] pair with finite numbers
            const coords = rawCoords.filter(
                c => Array.isArray(c) && c.length === 2 &&
                     isFinite(c[0]) && isFinite(c[1])
            );

            if (coords.length < 2) {
                this.stopFlyAnimation();
                return;
            }

            const last = coords.length - 1;
            // Duration scales with route length — ~100ms per point, min 20s
            const DURATION_MS = Math.max(20000, coords.length * 100);
            const startTime = performance.now();

            const animate = (now) => {
                if (!this._isFlying) return;

                const progress  = Math.min((now - startTime) / DURATION_MS, 1);
                const rawIndex  = progress * last;
                const i         = Math.min(Math.floor(rawIndex), last);
                const t         = rawIndex - i;

                const cur  = coords[i];
                const next = coords[Math.min(i + 1, last)];

                // Guard: skip frame if either point is somehow missing
                if (!cur || !next) {
                    this._flyAnimation = requestAnimationFrame(animate);
                    return;
                }

                // Interpolate hiker position smoothly between waypoints
                const lng = cur[1]  + (next[1]  - cur[1])  * t;
                const lat = cur[0]  + (next[0]  - cur[0])  * t;

                if (this._hikerMarker) this._hikerMarker.setLngLat([lng, lat]);

                // Camera lags 8 waypoints behind so hiker stays visible ahead
                const camI   = Math.max(0, i - 8);
                const camA   = coords[camI];
                const camB   = coords[Math.min(camI + 1, last)];

                if (camA && camB) {
                    this.map.easeTo({
                        center:   [camA[1] + (camB[1] - camA[1]) * t,
                                   camA[0] + (camB[0] - camA[0]) * t],
                        bearing:  this._getBearing(cur, next),
                        pitch:    60,
                        zoom:     15,
                        duration: 150,
                        easing:   x => x,
                    });
                }

                if (progress < 1) {
                    this._flyAnimation = requestAnimationFrame(animate);
                } else {
                    this.stopFlyAnimation();
                }
            };

            this._flyAnimation = requestAnimationFrame(animate);
        }

        _getBearing(start, end) {
            const toRad = d => d * Math.PI / 180;
            const dLng  = toRad(end[1] - start[1]);
            const lat1  = toRad(start[0]);
            const lat2  = toRad(end[0]);
            const x = Math.sin(dLng) * Math.cos(lat2);
            const y = Math.cos(lat1) * Math.sin(lat2) - Math.sin(lat1) * Math.cos(lat2) * Math.cos(dLng);
            return (Math.atan2(x, y) * 180 / Math.PI + 360) % 360;
        }

        stopFlyAnimation() {
            this._isFlying = false;

            // Cancel the pre-animation delay if it hasn't fired yet — this was the bug
            if (this._flyTimeout) {
                clearTimeout(this._flyTimeout);
                this._flyTimeout = null;
            }

            if (this._flyAnimation) {
                cancelAnimationFrame(this._flyAnimation);
                this._flyAnimation = null;
            }

            if (this._hikerMarker) {
                this._hikerMarker.remove();
                this._hikerMarker = null;
            }

            const stopBtn = document.getElementById('fly-stop-overlay-btn');
            if (stopBtn) { stopBtn.classList.add('hidden'); }

            // Restore the trail list if we were the ones that collapsed it
            if (this._flyAutoCollapsed && window.trailListPanelApi) {
                window.trailListPanelApi.expand();
            }
            this._flyAutoCollapsed = false;

            // Stay on satellite after the flyover — do not restore the previous map type
            this._preFlyMapType = null;

            this.map.easeTo({ pitch: 0, bearing: 0, duration: 1000 });
            this._updateFlyButton(false);
        }

        _updateFlyButton(isFlying) {
            const btn = document.getElementById('fly-along-btn');
            if (!btn) return;
            if (isFlying) {
                btn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="6" y="6" width="12" height="12" rx="2" stroke-width="2" fill="currentColor"/></svg><span>Stop Animation</span>`;
                btn.classList.remove('bg-gray-100', 'hover:bg-gray-200', 'text-gray-700');
                btn.classList.add('bg-red-100', 'hover:bg-red-200', 'text-red-700');
            } else {
                btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M13.49 5.48c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm-3.6 13.9 1-4.4 2.1 2v6h2v-7.5l-2.1-2 .6-3c1.3 1.5 3.3 2.5 5.5 2.5v-2c-1.9 0-3.5-1-4.3-2.4l-1-1.6c-.4-.6-1-1-1.7-1-.3 0-.5.1-.8.1l-5.2 2.2v4.7h2v-3.4l1.8-.7-1.6 8.1-4.9-1-.4 2 7 1.4z"/></svg><span>Fly Along Trail</span>`;
                btn.classList.remove('bg-red-100', 'hover:bg-red-200', 'text-red-700');
                btn.classList.add('bg-gray-100', 'hover:bg-gray-200', 'text-gray-700');
            }
        }

        // ── /Fly Along Trail ─────────────────────────────────────────────────

         updateHighlightsCount() {
            const count = this.allTrails.reduce((total, trail) => {
                return total + (trail.highlights ? trail.highlights.length : 0);
            }, 0);
            
            // You can display this count somewhere in your UI
            console.log(`Total highlights: ${count}`);
        }
    }

    // Global function to close trail info panel
    function closeTrailInfoPanel() {
        const panel = document.getElementById('trail-info-panel');
        panel.classList.add('hidden');
        if (window.trailMap) { window.trailMap._clearSelection(); }
    }

     // Global function to handle trail card clicks
    function handleTrailCardClick(trailId, event) {
        event.stopPropagation();
        
        const trail = window.trailMap.allTrails.find(t => t.id == trailId);
        if (!trail) return;
        
        const isMobile = window.innerWidth <= 768;
        
        if (isMobile) {
            // On mobile: Show trail info panel as overlay
            window.trailMap.showTrailInfo(trail);
        } else {
            // On desktop: Navigate to trail detail page
            window.location.href = `/trails/${trailId}`;
        }
    }

    // Expand / collapse the description in facility popups (delegated click handler).
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.facility-popup-readmore');
        if (!btn) { return; }
        const desc = btn.previousElementSibling;
        if (!desc || !desc.classList.contains('facility-popup-description')) { return; }
        const expanded = btn.dataset.state === 'expanded';
        desc.classList.toggle('expanded', !expanded);
        btn.dataset.state = expanded ? 'collapsed' : 'expanded';
        btn.textContent = expanded ? 'Read more' : 'Show less';
    });

    // Show the panel scrollbar while the user is actively scrolling.
    // Scroll events don't bubble, but they propagate in the capture phase.
    (function () {
        const timers = new WeakMap();
        document.addEventListener('scroll', function (e) {
            const el = e.target;
            if (!(el instanceof Element) || !el.classList || !el.classList.contains('biz-panel-body')) {
                return;
            }
            el.classList.add('is-scrolling');
            const prev = timers.get(el);
            if (prev) { clearTimeout(prev); }
            timers.set(el, setTimeout(() => {
                el.classList.remove('is-scrolling');
                timers.delete(el);
            }, 800));
        }, true);
    })();

    // Scripts are at bottom of body — DOM is ready, initialize immediately
    initMap();

    function initMap() {
        window.trailMap = new EnhancedTrailMap();
        const trailMap = window.trailMap;

        const searchInput = document.getElementById('trail-list-search');
        const clearSearchBtn = document.getElementById('clear-trail-search-btn');
        let activeLocationFilter = 'trail';

        function filterBusinesses(businesses, searchTerm) {
            if (!searchTerm) return businesses;
            return businesses.filter(b =>
                b.name.toLowerCase().includes(searchTerm) ||
                (b.address && b.address.toLowerCase().includes(searchTerm)) ||
                (b.business_type_label && b.business_type_label.toLowerCase().includes(searchTerm))
            );
        }

        function filterTrails(trails, searchTerm) {
            if (!searchTerm) return trails;
            return trails.filter(t =>
                t.name.toLowerCase().includes(searchTerm) ||
                (t.location && t.location.toLowerCase().includes(searchTerm))
            );
        }

        function refreshList() {
            const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
            if (!window.trailMap) return;

            const allTrails = window.trailMap.allTrails || window.trailMap.currentTrails || [];
            const allBusinesses = window.trailMap.businessData || [];

            if (activeLocationFilter === 'business') {
                window.trailMap.renderTrailList([], filterBusinesses(allBusinesses, searchTerm));
            } else if (activeLocationFilter === 'fishing_lake') {
                const fishing = filterTrails(allTrails.filter(t => t.location_type === 'fishing_lake'), searchTerm);
                window.trailMap.renderTrailList(fishing, []);
            } else {
                // Trails (hiking)
                const hiking = filterTrails(allTrails.filter(t => t.location_type === 'trail'), searchTerm);
                window.trailMap.renderTrailList(hiking, []);
            }
        }

        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                if (e.target.value) {
                    clearSearchBtn.classList.remove('hidden');
                } else {
                    clearSearchBtn.classList.add('hidden');
                }
                refreshList();
            });

            if (clearSearchBtn) {
                clearSearchBtn.addEventListener('click', function() {
                    searchInput.value = '';
                    clearSearchBtn.classList.add('hidden');
                    refreshList();
                });
            }
        }

        // Location type filter buttons
        document.querySelectorAll('.location-filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                activeLocationFilter = this.dataset.locationFilter;
                if (window.trailMap) {
                    window.trailMap.activeLocationFilter = activeLocationFilter;
                    window.trailMap.applyFilters();
                    window.trailMap.renderBusinessMarkers();
                    window.trailMap.renderNetworkMarkers();
                    window.trailMap.toggleFacilityVisibility();
                }

                document.querySelectorAll('.location-filter-btn').forEach(b => {
                    b.classList.remove('active-filter');
                });
                this.classList.add('active-filter');

                refreshList();
            });
        });

        // Initialize mobile state
        function initializeMobileState() {
            const trailListPanel = document.getElementById('trail-list-panel');
            if (trailListPanel) {
                if (window.innerWidth <= 768) {
                    trailListPanel.classList.add('hidden');
                } else {
                    trailListPanel.classList.remove('hidden');
                }
            }
        }
        
        // Clear any stale inline left styles from old JS
        document.getElementById('trail-info-panel')?.style.removeProperty('left');

        // Call on page load
        initializeMobileState();
        
        // Re-initialize on window resize
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(initializeMobileState, 250);
        });


        // ── Mobile Search Drawer ──────────────────────────────────────────
        window.mobileSearchDrawer = {
            open() {
                const drawer = document.getElementById('mobile-search-drawer');
                if (drawer) {
                    drawer.classList.remove('hidden');
                    drawer.style.display = 'flex';
                }
                setTimeout(() => {
                    const input = document.getElementById('mobile-search-input');
                    if (input) { input.focus(); }
                }, 50);
                // Show all results immediately (same as desktop)
                window.mobileSearchDrawer.refreshResults();
            },
            refreshResults() {
                if (!window.trailMap) { return; }
                const input = document.getElementById('mobile-search-input');
                const q = input ? input.value.trim().toLowerCase() : '';
                const allTrails     = window.trailMap.allTrails || [];
                const allBusinesses = window.trailMap.businessData || [];
                const filter = typeof mobileLocationFilter !== 'undefined' ? mobileLocationFilter : 'trail';
                let trails, businesses;
                if (filter === 'business') {
                    trails = [];
                    businesses = q ? allBusinesses.filter(b =>
                        b.name.toLowerCase().includes(q) || (b.address && b.address.toLowerCase().includes(q))
                    ) : allBusinesses;
                } else if (filter === 'fishing_lake') {
                    trails = allTrails.filter(t => t.location_type === 'fishing_lake' &&
                        (!q || t.name.toLowerCase().includes(q) || (t.location && t.location.toLowerCase().includes(q))));
                    businesses = [];
                } else {
                    trails = allTrails.filter(t => t.location_type === 'trail' &&
                        (!q || t.name.toLowerCase().includes(q) || (t.location && t.location.toLowerCase().includes(q))));
                    businesses = [];
                }
                const saved = window.trailMap.activeLocationFilter;
                window.trailMap.activeLocationFilter = filter;
                window.trailMap.renderTrailList(trails, businesses, 'mobile-search-results-inner');
                window.trailMap.activeLocationFilter = saved;
            },
            close() {
                const drawer = document.getElementById('mobile-search-drawer');
                if (drawer) {
                    drawer.classList.add('hidden');
                    drawer.style.display = '';
                }
                const input = document.getElementById('mobile-search-input');
                if (input) { input.value = ''; }
                const clearBtn = document.getElementById('mobile-search-clear-btn');
                if (clearBtn) { clearBtn.classList.add('hidden'); }
                const inner = document.getElementById('mobile-search-results-inner');
                if (inner) { inner.innerHTML = '<p class="text-sm text-gray-400 text-center py-8">Start typing to search...</p>'; }
                // Reset tabs to "Hiking"
                if (typeof mobileLocationFilter !== 'undefined') { mobileLocationFilter = 'trail'; }
                document.querySelectorAll('.mobile-location-tab').forEach(b => {
                    b.classList.remove('text-primary-600', 'border-primary-600');
                    b.classList.add('text-gray-500', 'border-transparent');
                });
                const trailTab = document.querySelector('[data-mobile-location-filter="trail"]');
                if (trailTab) {
                    trailTab.classList.remove('text-gray-500', 'border-transparent');
                    trailTab.classList.add('text-primary-600', 'border-primary-600');
                }
            },
        };

        document.getElementById('mobile-search-trigger')?.addEventListener('click', () => {
            window.mobileSearchDrawer.open();
        });
        document.getElementById('mobile-search-back-btn')?.addEventListener('click', () => {
            window.mobileSearchDrawer.close();
        });
        document.getElementById('mobile-search-input')?.addEventListener('input', function () {
            const clearBtn = document.getElementById('mobile-search-clear-btn');
            if (clearBtn) { clearBtn.classList.toggle('hidden', this.value.length === 0); }
            window.mobileSearchDrawer.refreshResults();
        });
        document.getElementById('mobile-search-clear-btn')?.addEventListener('click', () => {
            const input = document.getElementById('mobile-search-input');
            if (input) { input.value = ''; input.focus(); }
            const clearBtn = document.getElementById('mobile-search-clear-btn');
            if (clearBtn) { clearBtn.classList.add('hidden'); }
            window.mobileSearchDrawer.refreshResults();
        });

        // Close drawer when a result card is tapped
        document.getElementById('mobile-search-results-inner')?.addEventListener('click', (e) => {
            const card = e.target.closest('.trail-list-card, .business-list-card');
            if (card) { window.mobileSearchDrawer.close(); }
        });

        // Mobile search drawer location filter tabs
        let mobileLocationFilter = 'trail';
        document.querySelectorAll('.mobile-location-tab').forEach(btn => {
            btn.addEventListener('click', function () {
                mobileLocationFilter = this.dataset.mobileLocationFilter;
                document.querySelectorAll('.mobile-location-tab').forEach(b => {
                    b.classList.remove('text-primary-600', 'border-primary-600');
                    b.classList.add('text-gray-500', 'border-transparent');
                });
                this.classList.remove('text-gray-500', 'border-transparent');
                this.classList.add('text-primary-600', 'border-primary-600');
                window.mobileSearchDrawer.refreshResults();
            });
        });

        // Trail parameter handling (existing code)
        const urlParams = new URLSearchParams(window.location.search);
        const trailId = urlParams.get('trail');
        
        if (trailId) {
            setTimeout(() => {
                const trail = trailMap.allTrails.find(t => t.id == trailId);
                if (trail) {
                    trailMap.focusOnTrail(trail);
                }
            }, 1500);
        }
    }
</script>
@endpush
@endsection