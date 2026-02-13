@extends('layouts.public')

@section('title', 'Interactive Trail Map')
@section('content')
<div class="relative h-[calc(100vh-100px)] md:h-[calc(100vh-100px)] max-md:h-[calc(100vh-160px)]">
    <!-- Main Map Container -->
    <div id="main-map" class="absolute inset-0 z-10"></div>

    <!-- Mobile Filters Container - Only visible on mobile -->
    <div id="mobile-filters-container" class="md:hidden absolute top-4 left-0 right-0 z-20 px-4">
        <div class="overflow-x-auto -mx-3 px-3">
            <div class="flex gap-2 pb-2" style="min-width: min-content;">
                <!-- Season Toggle -->
                <div class="flex gap-1 bg-white rounded-full p-1 shadow-sm border border-gray-300 flex-shrink-0">
                    <button data-season="summer" class="season-btn-mobile active px-4 py-1 rounded-full text-xs font-medium transition-colors">
                        ☀️ Summer
                    </button>
                    <button data-season="winter" class="season-btn-mobile px-4 py-1 rounded-full text-xs font-medium transition-colors">
                        ❄️ Winter
                    </button>
                </div>
                <!-- Distance Filter -->
                <button id="distance-filter-btn-mobile" class="filter-pill bg-white hover:bg-gray-50 border border-gray-300 rounded-full px-4 py-2 text-sm font-medium text-gray-700 flex items-center gap-2 shadow-sm flex-shrink-0">
                    <span>Distance</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Difficulty Filter -->
                <button id="difficulty-filter-btn-mobile" class="filter-pill bg-white hover:bg-gray-50 border border-gray-300 rounded-full px-4 py-2 text-sm font-medium text-gray-700 flex items-center gap-2 shadow-sm flex-shrink-0">
                    <span>Difficulty</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- All Filters Button -->
                <button id="all-filters-btn-mobile" class="filter-pill bg-white hover:bg-gray-50 border border-gray-300 rounded-full px-4 py-2 text-sm font-medium text-gray-700 flex items-center gap-2 shadow-sm flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                    <span>More</span>
                    <span id="filter-count-badge-mobile" class="hidden ml-1 bg-primary-600 text-white text-xs rounded-full px-2 py-0.5 font-bold">0</span>
                </button>
            </div>
        </div>
    </div>

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
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                            <input type="checkbox" value="{{ $activity->slug }}" class="activity-checkbox w-5 h-5">
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

    <!-- Map Type Selector - Top Right on Desktop, Bottom Left on Mobile -->
    <div class="absolute top-4 right-4 md:top-4 md:right-4 max-md:top-auto max-md:right-auto max-md:bottom-8 max-md:left-4 z-30">
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
                        <button class="layer-option-card" data-map-type="standard">
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

                        <button class="layer-option-card active" data-map-type="outdoors">
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
    
    <!-- Trail List Panel - Left Side -->
    <div id="trail-list-panel" class="absolute top-4 left-4 bottom-4 z-30 w-96 bg-white rounded-lg shadow-lg overflow-hidden flex flex-col">
        <!-- Panel Header -->
        <div class="p-4 border-b border-gray-200 bg-white">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-xl font-bold text-gray-900">Explore trails</h2>
                <button id="collapse-panel-btn" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Trail Count -->
        <div class="overflow-y-auto" id="trail-results">
            <!-- Search Input -->
            <div class="p-3 border-b border-gray-200">
                <div class="relative">
                    <input 
                        type="text" 
                        id="trail-list-search" 
                        placeholder="Search trails by name..." 
                        class="w-full px-4 py-2 pl-10 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm"
                    />
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <button id="clear-trail-search-btn" class="hidden absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="py-0 px-2 text-sm text-gray-600 mt-2">
                    <span id="trail-count">0</span> trails found
                </div>
            </div>
        </div>
        
        <!-- Scrollable Trail List -->
        <div id="trail-list-container" class="flex-1 overflow-y-auto">
            <div id="trail-cards" class="p-4 space-y-3">
                <!-- Trail cards will be dynamically inserted here -->
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <p>Loading trails...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- External Filter Bar - Beside Panel (moves with panel) -->
    <div id="external-filters" class="absolute top-4 left-[26rem] z-30 flex flex-wrap gap-2 transition-all duration-300">
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
    <button id="expand-panel-btn" class="hidden absolute top-4 left-4 z-30 bg-white rounded-lg shadow-lg hover:bg-gray-50 transition-colors overflow-hidden">
        <div class="flex items-center">
            <div class="p-3 border-r border-gray-200">
                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
            <div class="px-4 py-2">
                <div class="text-xs text-gray-500 uppercase tracking-wide">Trails</div>
                <div id="collapsed-trail-count" class="text-lg font-bold text-gray-900">0</div>
            </div>
        </div>
    </button>

    <!-- Trail Info Panel (Hidden by default) -->
    <div id="trail-info-panel" class="hidden absolute top-16 left-[26rem] md:top-16 md:left-[26rem] max-md:inset-4 max-md:top-auto max-md:bottom-4 z-40 bg-white rounded-lg shadow-xl w-80 max-md:w-auto max-h-[calc(100vh-9rem)] md:max-h-[calc(100vh-5rem)] overflow-y-auto">
        <div id="trail-info-content" class="p-6">
            <!-- Dynamic content will be loaded here -->
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
<div id="facility-media-modal" class="hidden fixed inset-0 bg-black bg-opacity-90 z-[9999] flex items-center justify-center p-4">
    <div class="relative max-w-5xl w-full bg-white rounded-lg shadow-xl">
        <!-- Close button -->
        <button onclick="closeFacilityMediaModal()" 
                class="absolute top-4 right-4 z-10 bg-gray-900 bg-opacity-75 hover:bg-opacity-100 text-white rounded-full p-2 transition-all">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        
        <!-- Content container -->
        <div id="facility-modal-content" class="p-4">
            <!-- Content will be dynamically inserted here -->
        </div>
        
        <!-- Caption -->
        <div id="facility-modal-caption" class="px-6 pb-6 text-center text-gray-700">
            <!-- Caption will be dynamically inserted here -->
        </div>
    </div>
</div>
@push('scripts')
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
    transition: left 0.3s ease-in-out;
}

/* Responsive positioning */
@media (max-width: 1024px) {
    #external-filters {
        left: 1rem !important;
        top: auto;
        bottom: 1rem;
        right: 1rem;
        flex-direction: row;
        overflow-x: auto;
    }
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
    .leaflet-bottom.leaflet-right .leaflet-control-zoom {
        bottom: 10px !important; /* Same as layers-toggle bottom-8 */
        right: 10px !important;
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

/* Hide desktop filters on mobile */
/* Mobile adjustments */
@media (max-width: 768px) {
    #external-filters {
        display: none !important;
    }
    
    /* Position adjustments for mobile */
    #trail-list-panel {
        top: 4.5rem !important;
    }
    
    #expand-panel-btn {
        top: 4.5rem !important;
    }
    
    /* Start with panel hidden on mobile */
    #trail-list-panel.initial-mobile-hidden {
        display: none;
    }
    
    /* Show expand button by default on mobile */
    #expand-panel-btn.initial-mobile-show {
        display: flex;
    }
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

#trail-info-panel .panel-content {
    height: 300px;
    overflow-y: auto;
}

/* Facility marker styling */
.facility-marker {
    background: transparent !important;
    border: none !important;
}

/* Facility popup styling */
.facility-popup .leaflet-popup-content-wrapper {
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.facility-popup .leaflet-popup-content {
    margin: 0;
}

/* Facility Popup Gallery Styles */
.facility-popup-content {
    padding: 16px;
    min-width: 280px;
}

.facility-popup-header {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
}

.facility-popup-icon {
    font-size: 28px;
    margin-right: 12px;
}

.facility-popup-title {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #111827;
}

.facility-popup-type {
    margin: 0 0 8px 0;
    font-size: 13px;
    color: #6b7280;
    text-transform: capitalize;
}

.facility-popup-description {
    margin: 0 0 12px 0;
    font-size: 14px;
    color: #374151;
    line-height: 1.5;
}

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

    // Facility Media Modal Functions
    function openFacilityMediaModal(url, type, caption) {
        const modal = document.getElementById('facility-media-modal');
        const content = document.getElementById('facility-modal-content');
        const captionEl = document.getElementById('facility-modal-caption');
        
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

    function closeFacilityMediaModal() {
        const modal = document.getElementById('facility-media-modal');
        const content = document.getElementById('facility-modal-content');
        
        modal.classList.add('hidden');
        content.innerHTML = ''; // Clear content to stop video playback
    }

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

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeHighlightMediaModal();
            closeFacilityMediaModal();
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
    class EnhancedTrailMap {
        constructor() {
            this.map = null;
            this.currentSeason = 'summer';
            this.currentDistance = '';
            this.currentDifficulty = '';
            this.activeFilters = ['hiking', 'fishing', 'camping', 'viewpoint', 'highlights', 
                      'snowshoeing', 'ice-fishing', 'cross-country-skiing', 'downhill-skiing'];
            this.baseLayers = {};
            this.overlayLayers = {};
            this.routeLayer = null;  // Add this line
            this.allTrails = [];
            this.highlightedRoute = null;
            this.init();

            window.trailMap = this;
        }

        init() {
            // Initialize map
            this.map = L.map('main-map', {
                zoomControl: false
            }).setView([54.7804, -127.1698], 10);

            // Add zoom control to bottom right
            L.control.zoom({
                position: 'bottomright'
            }).addTo(this.map);

            // Base layers for different map types
            this.baseLayers = {
                'standard': L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 19
                }),
                'satellite': L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                    attribution: '© Google',
                    maxZoom: 22,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                }),
                'terrain': L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                    attribution: 'Map data: © OpenStreetMap, SRTM | Map style: © OpenTopoMap',
                    maxZoom: 18
                }),
                'outdoors': L.tileLayer('https://{s}.tile-cyclosm.openstreetmap.fr/cyclosm/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap, CyclOSM',
                    maxZoom: 18
                })
            };

            // Track current map type
            this.currentMapType = 'outdoors';

            // Activity overlay layers
            this.overlayLayers = {
                // Summer activities
                'hiking': L.layerGroup(),
                'fishing': L.layerGroup(),
                'camping': L.layerGroup(),
                'viewpoint': L.layerGroup(),
                'highlights': L.layerGroup(),
                // Winter activities
                'snowshoeing': L.layerGroup(),
                'ice-fishing': L.layerGroup(),
                'cross-country-skiing': L.layerGroup(),
                'downhill-skiing': L.layerGroup()
            };

            // Add default base layer
            this.baseLayers[this.currentMapType].addTo(this.map);

            this.setupEventListeners();
            this.loadTrails();
            this.loadFacilities();
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

            // Panel collapse/expand - Check if elements exist
            const collapseBtn = document.getElementById('collapse-panel-btn');
            const expandBtn = document.getElementById('expand-panel-btn');
            const trailListPanel = document.getElementById('trail-list-panel');
            const externalFilters = document.getElementById('external-filters');

            if (collapseBtn && expandBtn && trailListPanel && externalFilters) {
                collapseBtn.addEventListener('click', () => {
                    trailListPanel.classList.add('hidden');
                    expandBtn.classList.remove('hidden');
                    trailListPanel.dataset.manuallyCollapsed = 'true';
                    trailListPanel.dataset.lastViewport = window.innerWidth > 768 ? 'desktop' : 'mobile';
                    
                    // Move filters and trail info panel on desktop only
                    if (window.innerWidth > 768) {
                        externalFilters.style.left = '9.5rem';
                        
                        // Move trail info panel
                        const trailInfoPanel = document.getElementById('trail-info-panel');
                        if (trailInfoPanel) {
                            trailInfoPanel.style.left = '9.5rem';
                        }
                    }
                });

                expandBtn.addEventListener('click', () => {
                    trailListPanel.classList.remove('hidden');
                    expandBtn.classList.add('hidden');
                    trailListPanel.dataset.manuallyCollapsed = 'false';
                    trailListPanel.dataset.lastViewport = window.innerWidth > 768 ? 'desktop' : 'mobile';
                    
                    // Move filters and trail info panel back on desktop only
                    if (window.innerWidth > 768) {
                        externalFilters.style.left = '26rem';
                        
                        // Move trail info panel back
                        const trailInfoPanel = document.getElementById('trail-info-panel');
                        if (trailInfoPanel) {
                            trailInfoPanel.style.left = '26rem';
                        }
                    }
                });
            }

            // Click on map to collapse controls (if they exist)
            this.map.on('click', () => {
                const controlsContent = document.getElementById('controls-content');
                if (controlsContent) {
                    controlsContent.classList.add('hidden');
                }
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

            // Map movement/zoom event - update visible trails
            // this.map.on('moveend', () => {
            //     this.updateVisibleTrails();
            // });

            // this.map.on('zoomend', () => {
            //     this.updateVisibleTrails();
            // });

            // Geocoding search with debouncing
            const globalSearch = document.getElementById('global-trail-search');
            if (globalSearch) {
                let searchTimeout;
                
                globalSearch.addEventListener('input', (e) => {
                    clearTimeout(searchTimeout);
                    const query = e.target.value.trim();
                    
                    if (query.length === 0) {
                        // If search is cleared, show all visible trails
                        this.updateVisibleTrails();
                        const suggestionsDiv = document.getElementById('search-suggestions');
                        if (suggestionsDiv) {
                            suggestionsDiv.classList.add('hidden');
                        }
                        return;
                    }
                    
                    if (query.length < 2) {
                        return; // Don't search until at least 2 characters
                    }
                    
                    // Debounce search by 300ms
                    searchTimeout = setTimeout(() => {
                        this.performGeocodeSearch(query);
                    }, 300);
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

            // Close suggestions when clicking outside
            document.addEventListener('click', (e) => {
                const suggestionsDiv = document.getElementById('search-suggestions');
                const searchInput = document.getElementById('global-trail-search');
                if (suggestionsDiv && !suggestionsDiv.contains(e.target) && e.target !== searchInput) {
                    suggestionsDiv.classList.add('hidden');
                }
            });
        }

        updateActivityFilters(season) {
            // Define which activities are available for each season
            const seasonalActivities = {
                'summer': ['hiking', 'fishing', 'camping', 'viewpoint', 'highlights'],
                'winter': ['snowshoeing', 'ice-fishing', 'cross-country-skiing', 'downhill-skiing', 'viewpoint', 'highlights']
            };
            
            // Get valid activities for this season
            const validActivities = seasonalActivities[season] || seasonalActivities['summer'];
            
            // Ensure overlay layers exist for all valid activities
            validActivities.forEach(activityType => {
                if (!this.overlayLayers[activityType]) {
                    this.overlayLayers[activityType] = L.layerGroup();
                }
            });
            
            // Filter current active filters to only include valid ones for this season
            const currentActivityFilters = this.activeFilters.filter(f => f !== 'highlights');
            const validCurrentFilters = currentActivityFilters.filter(f => validActivities.includes(f));
            
           this.activeFilters = ['highlights', ...validActivities];
        }

        async performGeocodeSearch(query) {
            const suggestionsDiv = document.getElementById('search-suggestions');
            
            // Search local trails first
            const matchingTrails = this.allTrails.filter(trail => {
                return trail.name.toLowerCase().includes(query.toLowerCase()) ||
                    (trail.location && trail.location.toLowerCase().includes(query.toLowerCase()));
            });
            
            // Geocode search for places
            let placeResults = [];
            try {
                const response = await fetch(
                    `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5`
                );
                placeResults = await response.json();
            } catch (error) {
                console.error('Geocoding error:', error);
            }
            
            // Show suggestions
            if (suggestionsDiv && (matchingTrails.length > 0 || placeResults.length > 0)) {
                let html = '';
                
                // Trail results
                if (matchingTrails.length > 0) {
                    html += '<div class="p-2 bg-gray-50 text-xs font-semibold text-gray-500 uppercase">Trails</div>';
                    matchingTrails.slice(0, 5).forEach(trail => {
                        html += `
                            <div class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 suggestion-item" 
                                data-type="trail" 
                                data-id="${trail.id}">
                                <div class="font-medium text-gray-900">${trail.name}</div>
                                <div class="text-sm text-gray-600">${trail.location || ''} · ${trail.distance}km</div>
                            </div>
                        `;
                    });
                }
                
                // Place results
                if (placeResults.length > 0) {
                    html += '<div class="p-2 bg-gray-50 text-xs font-semibold text-gray-500 uppercase">Places</div>';
                    placeResults.forEach(place => {
                        html += `
                            <div class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 suggestion-item" 
                                data-type="place" 
                                data-lat="${place.lat}" 
                                data-lon="${place.lon}"
                                data-boundingbox='${JSON.stringify(place.boundingbox)}'
                                data-name="${place.display_name}">
                                <div class="font-medium text-gray-900 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>
                                    ${place.display_name}
                                </div>
                            </div>
                        `;
                    });
                }
                
                suggestionsDiv.innerHTML = html;
                suggestionsDiv.classList.remove('hidden');
                
                // Add click handlers
                document.querySelectorAll('.suggestion-item').forEach(item => {
                    item.addEventListener('click', () => {
                        const searchInput = document.getElementById('global-trail-search');
                        
                        if (item.dataset.type === 'trail') {
                            const trail = this.allTrails.find(t => t.id == item.dataset.id);
                            if (trail) {
                                this.focusOnTrail(trail);
                                if (searchInput) searchInput.value = trail.name;
                            }
                        } else if (item.dataset.type === 'place') {
                            const lat = parseFloat(item.dataset.lat);
                            const lon = parseFloat(item.dataset.lon);
                            const boundingBox = item.dataset.boundingbox ? JSON.parse(item.dataset.boundingbox) : null;
                            
                            if (boundingBox) {
                                // If we have bounding box, fit the map to show the whole area
                                const southWest = [parseFloat(boundingBox[0]), parseFloat(boundingBox[2])];
                                const northEast = [parseFloat(boundingBox[1]), parseFloat(boundingBox[3])];
                                this.map.fitBounds([southWest, northEast], {
                                    padding: [50, 50],
                                    animate: true,
                                    duration: 1
                                });
                            } else {
                                // Fallback to center point with appropriate zoom
                                this.map.setView([lat, lon], 12, {
                                    animate: true,
                                    duration: 1
                                });
                            }
                            
                            setTimeout(() => this.updateVisibleTrails(), 500);
                            if (searchInput) searchInput.value = item.dataset.name;
                        }
                        suggestionsDiv.classList.add('hidden');
                    });
                });
            } else if (suggestionsDiv) {
                suggestionsDiv.innerHTML = '<div class="p-4 text-center text-gray-500 text-sm">No results found</div>';
                suggestionsDiv.classList.remove('hidden');
            }
        }

        getVisibleTrails() {
            // Simply return filtered trails without map bounds check
            return this.filterTrails(this.allTrails);
        }

        filterTrails(trails) {
            return trails.filter(trail => {
                // Check if trail has activities for current season (MOST IMPORTANT CHECK)
                const hasSeasonalActivities = trail.activities && trail.activities.length > 0;
                if (!hasSeasonalActivities) {
                    return false; // Hide trails with no activities for selected season
                }

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
            this.renderTrailList(visibleTrails);
        }

        // Add this function to your EnhancedTrailMap class
        getDistanceColor(distance) {
            if (distance <= 5) return '#10B981';      // Green - Short trails
            if (distance <= 10) return '#F59E0B';     // Orange - Medium trails  
            if (distance <= 20) return '#EF4444';     // Red - Long trails
            return '#7C2D12';                         // Dark Red - Very long trails
        }

        addTrailRoute(trail) {
            if (!trail.route_coordinates || trail.route_coordinates.length === 0) {
                return null;
            }
            
            try {
                // Sanitize all route coordinates
                const sanitizedRoute = trail.route_coordinates
                    .map(coord => this.sanitizeCoordinates(coord))
                    .filter(coord => coord !== null);
                
                if (sanitizedRoute.length === 0) {
                    console.warn('No valid coordinates in route for trail:', trail.name);
                    return null;
                }
                
                const routeColor = this.getDistanceColor(trail.distance);
                
                const route = L.polyline(sanitizedRoute, {
                    color: routeColor,
                    weight: 4,
                    opacity: 0.8,
                    dashArray: trail.status === 'seasonal' ? '10, 5' : null
                });
                
                return route;
            } catch (error) {
                console.error('Error creating route for trail:', trail.name, error);
                return null;
            }
        }

        switchSeason(season) {
            console.log('Switching to season:', season);
            
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
            this.loadTrails();
        }

        switchMapType(mapType) {
            // Remove current base layer
            if (this.map.hasLayer(this.baseLayers[this.currentMapType])) {
                this.map.removeLayer(this.baseLayers[this.currentMapType]);
            }
            
            // Update current map type
            this.currentMapType = mapType;
            
            // Add new base layer
            this.baseLayers[this.currentMapType].addTo(this.map);
            
            // Update active state in dropdown
            document.querySelectorAll('.layer-option-card').forEach(btn => {
                if (btn.dataset.mapType === mapType) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
            
            // Close dropdown
            document.getElementById('layers-dropdown').classList.add('hidden');
        }

        updateCollapsedButton(mapType) {
            const mapTypeData = {
                'standard': {
                    label: 'Map',
                    icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>`,
                    bgClass: ''
                },
                'terrain': {
                    label: 'Terrain',
                    icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>`,
                    bgClass: 'bg-gray-200'
                },
                'satellite': {
                    label: 'Satellite',
                    icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>`,
                    bgClass: 'bg-gray-300'
                },
                'outdoors': {
                    label: 'Outdoors',
                    icon: `<svg class="w-6 h-6 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"/>
                        </svg>`,
                    bgClass: 'bg-green-100'
                }
            };
            
            const data = mapTypeData[mapType];
            const iconElement = document.getElementById('current-map-icon');
            const labelElement = document.getElementById('current-map-label');
            
            // Update icon
            iconElement.innerHTML = data.icon;
            iconElement.className = 'map-layer-icon-small ' + data.bgClass;
            
            // Update label
            labelElement.textContent = data.label;
        }

        searchTrails(searchTerm) {
            if (!searchTerm) {
                // If search is empty, show visible trails with filters
                this.updateVisibleTrails();
                return;
            }
            
            // Search within visible trails
            const visibleTrails = this.getVisibleTrails();
            
            const matchedTrails = visibleTrails.filter(trail => {
                // Search in name, location, and description
                return trail.name.toLowerCase().includes(searchTerm) ||
                    (trail.location && trail.location.toLowerCase().includes(searchTerm)) ||
                    (trail.description && trail.description.toLowerCase().includes(searchTerm));
            });
            
            this.renderTrailList(matchedTrails);
        }

        updateFilters() {
            // Get active activity filters
            this.activeFilters = Array.from(
                document.querySelectorAll('.activity-filter:checked')
            ).map(cb => cb.dataset.activity);

            this.applyFilters();
        }

        // In your applyFilters function, make sure you have these variable declarations at the top:
        applyFilters() {
            // Clear all overlay layers
            Object.values(this.overlayLayers).forEach(layer => {
                this.map.removeLayer(layer);
                layer.clearLayers();
            });

            if (this.routeLayer) {
                this.map.removeLayer(this.routeLayer);
            }
            this.routeLayer = L.layerGroup().addTo(this.map);

            const visibleTrails = this.getVisibleTrails();
            this.renderTrailList(visibleTrails);

            const allFilteredTrails = this.filterTrails(this.allTrails);
            console.log('Filtered trails:', allFilteredTrails);
            
            allFilteredTrails.forEach(trail => {
                // Check if trail has activities for current season
                const hasSeasonalActivities = trail.activities && trail.activities.length > 0;
                
                // Only render trail if it has activities for the selected season
                if (!hasSeasonalActivities) {
                    console.log('Skipping trail (no seasonal activities):', trail.name);
                    return; // Skip this trail entirely
                }

                // Add trail route
                const route = this.addTrailRoute(trail);
                if (route) {
                    this.routeLayer.addLayer(route);
                }

                // Add highlights if enabled
                if (this.activeFilters.includes('highlights')) {
                    this.createHighlightMarkers(trail);
                }

                // Add markers for active activity types
                trail.activities.forEach(activity => {
                    if (this.activeFilters.includes(activity.type)) {
                        const marker = this.createTrailMarker(trail, activity);
                        if (marker) {
                            this.overlayLayers[activity.type].addLayer(marker);
                        }
                    }
                });
            });

            // Only add layers that exist and are in active filters
            this.activeFilters.forEach(activityType => {
                if (this.overlayLayers[activityType]) {
                    this.overlayLayers[activityType].addTo(this.map);
                }
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

        createTrailMarker(trail, activity) {
            const coords = this.sanitizeCoordinates(trail.coordinates);
            if (!coords) {
                console.warn('Invalid coordinates for trail:', trail.name);
                return null;
            }
            
            const colors = {
                // Summer activities
                hiking: '#10B981',
                fishing: '#3B82F6',
                camping: '#F59E0B',
                viewpoint: '#8B5CF6',
                // Winter activities
                snowshoeing: '#06B6D4',
                'ice-fishing': '#0EA5E9',
                'cross-country-skiing': '#3B82F6',
                'downhill-skiing': '#6366F1'
            };

            // Check if this is a fishing lake
            const isFishingLake = trail.location_type === 'fishing_lake';
            
            let icon;
            if (isFishingLake) {
                // Fishing lake icon - larger blue marker with fish emoji
                icon = L.divIcon({
                    html: `<div style="background-color: #3B82F6;" class="w-8 h-8 rounded-full border-2 border-white shadow-lg flex items-center justify-center text-white text-lg">🐟</div>`,
                    className: 'custom-fishing-marker',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });
            } else {
                // Trail icon - regular activity-based icon
                icon = L.divIcon({
                    html: `<div style="background-color: ${colors[activity.type] || '#6B7280'};" class="w-6 h-6 rounded-full border-2 border-white shadow-lg flex items-center justify-center text-white text-xs font-bold">${activity.icon || '•'}</div>`,
                    className: 'custom-trail-marker',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });
            }

            const marker = L.marker(coords, { icon });
                //.bindPopup(this.createPopupContent(trail));

            marker.on('click', () => {
                this.showTrailInfo(trail);
            });

            return marker;
        }

        createHighlightMarkers(trail) {
            if (!trail.highlights || trail.highlights.length === 0) return;

            trail.highlights.forEach(highlight => {
                const icon = L.divIcon({
                    html: `<div style="background-color: ${highlight.color || '#6366f1'};" class="w-8 h-8 rounded-full border-2 border-white shadow-lg flex items-center justify-center text-white text-lg">${highlight.icon || '📍'}</div>`,
                    className: 'custom-highlight-marker',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });

                const marker = L.marker(highlight.coordinates, { icon });
                    //.bindPopup(this.createHighlightPopupContent(trail, highlight));

                // Add click event
                marker.on('click', () => {
                    this.showHighlightInfo(trail, highlight);
                });

                this.overlayLayers['highlights'].addLayer(marker);
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
            
            // Build media HTML if media exists
            let mediaHTML = '';
            if (highlight.media && highlight.media.length > 0) {
                mediaHTML = `
                    <div class="mb-4 border-t pt-4">
                        <div class="grid grid-cols-${Math.min(highlight.media.length, 3)} gap-2">
                            ${highlight.media.map(media => {
                                if (media.media_type === 'photo') {
                                    return `
                                        <div class="relative aspect-square rounded-lg overflow-hidden cursor-pointer hover:opacity-90 transition group"
                                            onclick="openHighlightMediaModal('${media.url}', 'photo', '${media.caption || highlight.name}')">
                                            <img src="${media.url}" 
                                                alt="${media.caption || highlight.name}"
                                                class="w-full h-full object-cover">
                                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all flex items-center justify-center">
                                                <svg class="w-6 h-6 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                                </svg>
                                            </div>
                                        </div>
                                    `;
                                } else if (media.media_type === 'video_url' || media.media_type === 'video') {
                                    const videoUrl = media.video_url || media.url;
                                    const thumbnailUrl = getVideoThumbnail(videoUrl);
                                    
                                    return `
                                        <div class="relative aspect-square rounded-lg overflow-hidden cursor-pointer hover:opacity-90 transition group bg-gray-900"
                                            onclick="openHighlightMediaModal('${videoUrl}', 'video', '${media.caption || highlight.name}')">
                                            ${thumbnailUrl ? `
                                                <img src="${thumbnailUrl}" 
                                                    alt="Video thumbnail"
                                                    class="w-full h-full object-cover"
                                                    onerror="this.parentElement.innerHTML='<div class=\\'w-full h-full flex items-center justify-center\\'><svg class=\\'w-8 h-8 text-white opacity-75\\' fill=\\'currentColor\\' viewBox=\\'0 0 20 20\\'><path d=\\'M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z\\'></path></svg></div>'">
                                            ` : `
                                                <div class="w-full h-full flex items-center justify-center">
                                                    <svg class="w-8 h-8 text-white opacity-75" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                                    </svg>
                                                </div>
                                            `}
                                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                                <div class="bg-white bg-opacity-90 rounded-full p-2">
                                                    <svg class="w-5 h-5 text-gray-900" fill="currentColor" viewBox="0 0 20 20">
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
                    </div>
                `;
            }
            
            content.innerHTML = `
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center">
                        <div style="background-color: ${highlight.color};" class="w-10 h-10 rounded-full flex items-center justify-center text-white text-xl mr-3">
                            ${highlight.icon}
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">${highlight.name}</h3>
                            <p class="text-sm text-gray-600 capitalize">${highlight.type.replace('_', ' ')}</p>
                        </div>
                    </div>
                    <button onclick="this.closest('#trail-info-panel').classList.add('hidden')" 
                            class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                ${highlight.description ? `
                    <div class="mb-4">
                        <p class="text-gray-700 text-xs">${highlight.description}</p>
                    </div>
                ` : ''}

                ${mediaHTML}
                
                <div class="space-y-2">
                    <a href="/trails/${trail.id}" 
                    class="block w-full bg-primary-600 hover:bg-primary-700 text-white text-center py-2 px-4 rounded-md font-medium transition-colors">
                        View Full Trail
                    </a>
                    <button onclick="window.trailMap.viewHighlight(${trail.id}, ${JSON.stringify(highlight.coordinates).replace(/"/g, '&quot;')})" 
                            class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded-md font-medium transition-colors">
                        Center on Map
                    </button>
                </div>
            `;
            
            panel.classList.remove('hidden');
        }

        viewHighlight(trailId, coordinates) {
            this.map.closePopup();
            this.map.setView(coordinates, 16, {
                animate: true,
                duration: 1
            });
        }

        createPopupContent(trail) {
            const seasonalNote = trail.seasonal_info?.notes ? 
                `<div class="text-xs text-blue-600 mt-1">${trail.seasonal_info.notes}</div>` : '';

            return `
                <div class="max-w-sm">
                    <h5 class="font-bold text-lg mb-2">${trail.name}</h5>
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

            // Close any open popups
            this.map.closePopup();

            // Clear existing route highlights
            if (this.highlightedRoute) {
                this.map.removeLayer(this.highlightedRoute);
            }

            // Check if trail has route coordinates
            if (!trail.route_coordinates || trail.route_coordinates.length === 0) {
                alert('Route data not available for this trail.');
                return;
            }

            // Create highlighted route
            this.highlightedRoute = L.polyline(trail.route_coordinates, {
                color: '#FF0000',
                weight: 6,
                opacity: 0.9,
                dashArray: '10, 5'
            }).addTo(this.map);

            // Zoom to route bounds with padding
            this.map.fitBounds(this.highlightedRoute.getBounds(), { 
                padding: [20, 20],
                maxZoom: 18
            });

            // Add route popup
            this.highlightedRoute.bindPopup(`
                <div class="text-center">
                    <b>${trail.name} Route</b><br>
                    <span class="text-sm">${trail.distance}km trail path</span><br>
                    <button onclick="window.trailMap.clearRoute()" class="mt-2 bg-gray-500 text-white px-2 py-1 rounded text-xs">
                        Clear Route
                    </button>
                </div>
            `).openPopup();
        }

        // Add method to clear highlighted route
        clearRoute() {
            if (this.highlightedRoute) {
                this.map.removeLayer(this.highlightedRoute);
                this.highlightedRoute = null;
            }
            this.map.closePopup();
        }

        showTrailInfo(trail) {
            const panel = document.getElementById('trail-info-panel');
            const content = document.getElementById('trail-info-content');
            
            if (!trail) return;
            
            // Format photos gallery - NOW USING trail.photos from trail_media
            let photosHTML = '';
        
            // Format highlights with media - NOW USING features with their own media
            let highlightsHTML = '';
            if (trail.highlights && trail.highlights.length > 0) {
                highlightsHTML = `
                    <div class="mt-4 pt-4 border-t">
                        <h4 class="font-semibold text-sm mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            Trail Highlights
                        </h4>
                        <div class="space-y-2">
                            ${trail.highlights.map(highlight => `
                                <div class="bg-purple-50 rounded p-3 cursor-pointer hover:bg-purple-100 transition"
                                    onclick="window.trailMap.focusOnHighlight(${highlight.coordinates[0]}, ${highlight.coordinates[1]})">
                                    <div class="flex items-start gap-3">
                                        ${highlight.photo_url ? `
                                            <img src="${highlight.photo_url}" 
                                                alt="${highlight.name}" 
                                                class="w-16 h-16 object-cover rounded flex-shrink-0">
                                        ` : ''}
                                        <div class="flex-1 min-w-0">
                                            <div class="font-medium text-sm">${highlight.name}</div>
                                            ${highlight.description ? `
                                                <div class="text-xs text-gray-600 mt-1 line-clamp-2">${highlight.description}</div>
                                            ` : ''}
                                            ${highlight.media_count > 1 ? `
                                                <div class="text-xs text-purple-600 mt-1">+${highlight.media_count - 1} more</div>
                                            ` : ''}
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }
            
            content.innerHTML = `
                <!-- Fixed Header -->
                <div class="flex items-start justify-between mb-4">
                    <h3 class="text-xl font-bold pr-8">${trail.name}</h3>
                    <button onclick="closeTrailInfoPanel()" 
                            class="text-gray-400 hover:text-gray-600 transition flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <!-- Scrollable Content with Fixed 100px Height -->
                <div class="panel-content mb-4">
                    ${photosHTML}
                    
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="bg-blue-50 p-3 rounded text-center">
                            <div class="text-2xl font-bold text-blue-600">${trail.distance}</div>
                            <div class="text-xs text-gray-600">km</div>
                        </div>
                        <div class="bg-green-50 p-3 rounded text-center">
                            <div class="text-2xl font-bold text-green-600">${trail.elevation_gain || 0}</div>
                            <div class="text-xs text-gray-600">meters</div>
                        </div>
                        <div class="bg-yellow-50 p-3 rounded text-center">
                            <div class="text-2xl font-bold text-yellow-600">${trail.estimated_time || 'N/A'}</div>
                            <div class="text-xs text-gray-600">hours</div>
                        </div>
                        <div class="bg-purple-50 p-3 rounded text-center">
                            <div class="text-2xl font-bold text-purple-600">${trail.difficulty}</div>
                            <div class="text-xs text-gray-600">difficulty</div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <p class="text-sm text-gray-600 leading-relaxed">${trail.description}</p>
                    </div>
                    
                    ${highlightsHTML}
                </div>
                
                <!-- Fixed Footer Buttons -->
                <div class="space-y-2">
                    <button onclick="window.trailMap.viewRoute(${trail.id})" 
                            class="w-full bg-primary-500 hover:bg-primary-600 text-white font-medium py-2.5 px-4 rounded transition flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 9m0 8V9m0 0V7"/>
                        </svg>
                        View Full Route
                    </button>
                    <a href="/trails/${trail.id}" target="_blank"
                    class="block w-full bg-white border-2 border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2.5 px-4 rounded transition text-center">
                        View Trail Details
                    </a>
                </div>
            `;
            
            panel.classList.remove('hidden');
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
            // Validate trail object
            if (!trail || !trail.coordinates) {
                console.warn('Trail has no coordinates:', trail);
                return;
            }

            // Show trail info panel
            this.showTrailInfo(trail);
            
            // If trail has route, display it highlighted and fit to bounds
            if (trail.route_coordinates && trail.route_coordinates.length > 0) {
                // Clear existing highlighted route
                if (this.highlightedRoute) {
                    this.map.removeLayer(this.highlightedRoute);
                }
                
                try {
                    // Create highlighted route
                    this.highlightedRoute = L.polyline(trail.route_coordinates, {
                        color: '#FF0000',
                        weight: 6,
                        opacity: 0.9,
                        dashArray: '10, 5'
                    }).addTo(this.map);
                    
                    // Fit map to route with maximum zoom
                    this.map.fitBounds(this.highlightedRoute.getBounds(), { 
                        padding: [50, 50],
                        maxZoom: 18
                    });
                } catch (error) {
                    console.error('Error creating trail route:', error);
                    // Fallback to coordinates
                    this.map.setView(trail.coordinates, 15, {
                        animate: true,
                        duration: 1
                    });
                }
            } else {
                // If no route, just zoom to trail start coordinates
                if (Array.isArray(trail.coordinates) && trail.coordinates.length === 2) {
                    this.map.setView(trail.coordinates, 15, {
                        animate: true,
                        duration: 1
                    });
                }
            }
        }

        focusOnTrailById(trailId) {
            const trail = this.allTrails.find(t => t.id == trailId);
            if (trail) {
                this.focusOnTrail(trail);
            }
        }

        focusOnHighlight(lat, lng) {
            this.map.setView([lat, lng], 16, {
                animate: true,
                duration: 1
            });
            
            // Add a temporary highlight marker
            const highlightMarker = L.circleMarker([lat, lng], {
                radius: 15,
                color: '#8B5CF6',
                fillColor: '#8B5CF6',
                fillOpacity: 0.3,
                weight: 3
            }).addTo(this.map);
            
            // Remove after animation
            setTimeout(() => {
                this.map.removeLayer(highlightMarker);
            }, 3000);
        }

        async loadTrails() {
            try {
                const params = new URLSearchParams({
                    season: this.currentSeason,
                    filters: this.activeFilters.join(',')
                });

                // Add difficulty and distance filters if set
                if (this.currentDifficulty) {
                    params.append('difficulty', this.currentDifficulty);
                }
                if (this.currentDistance) {
                    params.append('distance', this.currentDistance);
                }

                console.log('Loading trails with params:', params.toString());
                
                const response = await fetch(`/api/trails?${params}`);
                this.allTrails = await response.json();
                
                console.log('Loaded trails:', this.allTrails.length);
                
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
                
                console.log('Loaded facilities:', facilities);
                
                facilities.forEach(facility => {
                    // Create custom icon with facility emoji
                    const facilityIcon = L.divIcon({
                        className: 'facility-marker',
                        html: `
                            <div style="
                                background: white;
                                color: #059669;
                                padding: 8px;
                                border-radius: 50%;
                                font-size: 20px;
                                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                                border: 3px solid #059669;
                                width: 44px;
                                height: 44px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            ">
                                ${facility.icon}
                            </div>
                        `,
                        iconSize: [44, 44],
                        iconAnchor: [22, 22]
                    });
                    
                    const facilityMarker = L.marker([facility.latitude, facility.longitude], {
                        icon: facilityIcon,
                        zIndexOffset: 500 // Below network markers but above trails
                    }).addTo(this.map);
                    
                    // Create popup content with media gallery
                    let popupContent = `
                        <div class="facility-popup-content">
                            <div class="facility-popup-header">
                                <span class="facility-popup-icon">${facility.icon}</span>
                                <h3 class="facility-popup-title">${facility.name}</h3>
                            </div>
                            <p class="facility-popup-type">
                                ${facility.facility_type_label}
                            </p>
                            ${facility.description ? `
                                <p class="facility-popup-description">${facility.description}</p>
                            ` : ''}
                    `;
                    
                    // Add media gallery if facility has media
                    if (facility.media && facility.media.length > 0) {
                        popupContent += `
                            <div class="facility-media-gallery">
                                <p class="facility-media-count">${facility.media_count} ${facility.media_count === 1 ? 'photo/video' : 'photos/videos'}</p>
                                <div class="facility-media-grid">
                        `;
                        
                        // Show up to 4 media items
                        facility.media.slice(0, 4).forEach((media, index) => {
                            const isVideo = media.media_type === 'video_url';
                            const thumbnailUrl = media.thumbnail_url || media.url;
                            const fullUrl = isVideo ? media.url : (thumbnailUrl || media.url);
                            const mediaType = isVideo ? 'video' : 'photo';
                            const remainingCount = facility.media.length - 4;
                            
                            if (index === 3 && remainingCount > 0) {
                                // Show "+X more" overlay on the 4th item if there are more
                                popupContent += `
                                    <div class="facility-media-item" onclick="openFacilityMediaModal('${fullUrl}', '${mediaType}', '${media.caption || facility.name}')">
                                        <img src="${thumbnailUrl}" class="facility-media-thumbnail" alt="${media.caption || facility.name}">
                                        <div class="facility-media-overlay">+${remainingCount} more</div>
                                        ${isVideo ? '<div class="facility-video-badge">▶</div>' : ''}
                                    </div>
                                `;
                            } else {
                                popupContent += `
                                    <div class="facility-media-item" onclick="openFacilityMediaModal('${fullUrl}', '${mediaType}', '${media.caption || facility.name}')">
                                        <img src="${thumbnailUrl}" class="facility-media-thumbnail" alt="${media.caption || facility.name}">
                                        ${isVideo ? '<div class="facility-video-badge">▶</div>' : ''}
                                    </div>
                                `;
                            }
                        });
                        
                        popupContent += `
                                </div>
                            </div>
                        `;
                    }
                    
                    popupContent += `</div>`;
                    
                    facilityMarker.bindPopup(popupContent, {
                        maxWidth: 320,
                        className: 'facility-popup'
                    });
                });
                
            } catch (error) {
                console.error('Error loading facilities:', error);
            }
        }

        renderTrailList(trails) {
            const container = document.getElementById('trail-cards');
            const countElement = document.getElementById('trail-count');
            const collapsedCountElement = document.getElementById('collapsed-trail-count');
            
            countElement.textContent = trails.length;
            if (collapsedCountElement) {
                collapsedCountElement.textContent = trails.length;
            }
            
            if (trails.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="font-medium">No trails found in this area</p>
                        <p class="text-sm mt-2">Try zooming out or adjusting your filters</p6
                    </div>
                `;
                return;
            }
            
            container.innerHTML = trails.map(trail => {
                // Use preview_photo or first photo from photos array
                const imageUrl = trail.preview_photo || (trail.photos && trail.photos.length > 0 ? trail.photos[0].url : null);
                
                return `
                    <div class="trail-list-card" onclick="window.trailMap.focusOnTrailById(${trail.id})">
                        ${imageUrl ? 
                            `<img src="${imageUrl}" alt="${trail.name}" class="trail-list-image">` :
                            `<div class="trail-list-image-placeholder">
                                <svg class="w-12 h-12 text-white opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                </svg>
                            </div>`
                        }
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-900 text-sm mb-1 truncate">${trail.name}</h3>
                            <p class="text-xs text-gray-600 mb-2">${trail.location || 'Location not specified'}</p>
                            
                            <div class="flex items-center gap-2 text-xs text-gray-600 mb-2">
                                <span class="flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>
                                    ${trail.distance} km
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    Level ${trail.difficulty}
                                </span>
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
        }

        clearFilters() {
            // Reset checkboxes - keep hiking and highlights checked
            document.querySelectorAll('.activity-filter').forEach(cb => {
                cb.checked = cb.dataset.activity === 'hiking' || cb.dataset.activity === 'highlights';
            });

            // Reset selects
            document.getElementById('difficulty-filter').value = '';
            document.getElementById('distance-filter').value = '';

            // Update filters
            this.activeFilters = ['hiking', 'highlights'];
            this.applyFilters();
        }

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

    // Initialize map when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        window.trailMap = new EnhancedTrailMap();
        const trailMap = window.trailMap;

        const searchInput = document.getElementById('trail-list-search');
        const clearSearchBtn = document.getElementById('clear-trail-search-btn');
        
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase().trim();
                
                // Show/hide clear button
                if (searchTerm) {
                    clearSearchBtn.classList.remove('hidden');
                } else {
                    clearSearchBtn.classList.add('hidden');
                }
                
                // Filter trail cards using the correct class
                const trailCards = document.querySelectorAll('.trail-list-card');
                let visibleCount = 0;
                
                trailCards.forEach(card => {
                    // Get trail name from h3 element
                    const trailName = card.querySelector('h3')?.textContent.toLowerCase() || '';
                    
                    if (trailName.includes(searchTerm)) {
                        card.style.display = '';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                // Update count
                const countElement = document.getElementById('trail-count');
                if (countElement) {
                    countElement.textContent = visibleCount;
                }
            });
            
            // Clear search button
            if (clearSearchBtn) {
                clearSearchBtn.addEventListener('click', function() {
                    searchInput.value = '';
                    clearSearchBtn.classList.add('hidden');
                    
                    // Show all trail cards
                    const trailCards = document.querySelectorAll('.trail-list-card');
                    trailCards.forEach(card => {
                        card.style.display = '';
                    });
                    
                    // Reset count to all visible trails
                    trailMap.updateVisibleTrails();
                });
            }
        }

        // Initialize mobile state
        function initializeMobileState() {
            const trailListPanel = document.getElementById('trail-list-panel');
            const expandBtn = document.getElementById('expand-panel-btn');
            const externalFilters = document.getElementById('external-filters');
            const trailInfoPanel = document.getElementById('trail-info-panel');
            
            if (window.innerWidth <= 768) {
                // Mobile: hide panel, show expand button
                if (trailListPanel && expandBtn) {
                    trailListPanel.classList.add('hidden');
                    expandBtn.classList.remove('hidden');
                }
            } else {
                // Desktop: show panel, hide expand button (unless manually collapsed on desktop)
                if (trailListPanel && expandBtn) {
                    const wasManuallyCollapsed = trailListPanel.dataset.manuallyCollapsed === 'true';
                    const lastViewportWasDesktop = trailListPanel.dataset.lastViewport === 'desktop';
                    
                    if (!wasManuallyCollapsed || !lastViewportWasDesktop) {
                        trailListPanel.classList.remove('hidden');
                        expandBtn.classList.add('hidden');
                        trailListPanel.dataset.manuallyCollapsed = 'false';
                        
                        // Reset filter and trail info panel position
                        if (externalFilters) {
                            externalFilters.style.left = '26rem';
                        }
                        if (trailInfoPanel) {
                            trailInfoPanel.style.left = '26rem';
                        }
                    } else {
                        // Panel is collapsed, position filters and info panel accordingly
                        if (externalFilters) {
                            externalFilters.style.left = '9.5rem';
                        }
                        if (trailInfoPanel) {
                            trailInfoPanel.style.left = '9.5rem';
                        }
                    }
                    
                    trailListPanel.dataset.lastViewport = 'desktop';
                }
            }
        }
        
        // Call on page load
        initializeMobileState();
        
        // Re-initialize on window resize
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(initializeMobileState, 250);
        });
        
        // Move filters to mobile container on small screens
        function handleFilterPosition() {
            const mobileContainer = document.getElementById('mobile-filters');
            const externalFilters = document.getElementById('external-filters');
            
            if (window.innerWidth <= 768) {
                // Mobile: Move filters into mobile container
                if (mobileContainer && externalFilters) {
                    const filterButtons = externalFilters.querySelectorAll('.filter-pill, .relative');
                    const mobileFlexContainer = mobileContainer.querySelector('.flex');
                    
                    if (mobileFlexContainer) {
                        // Clear mobile container
                        mobileFlexContainer.innerHTML = '';
                        
                        // Clone filter buttons to mobile container
                        filterButtons.forEach(button => {
                            mobileFlexContainer.appendChild(button.cloneNode(true));
                        });
                        
                        // Hide desktop filters
                        externalFilters.style.display = 'none';
                        
                        // Re-attach event listeners to cloned buttons
                        reattachFilterEventListeners(mobileFlexContainer);
                    }
                }
            } else {
                // Desktop: Show original filters
                if (externalFilters) {
                    externalFilters.style.display = 'flex';
                }
            }
        }
        
        function reattachFilterEventListeners(container) {
            // Distance filter
            const distanceBtn = container.querySelector('#distance-filter-btn');
            const distanceDropdown = document.getElementById('distance-dropdown');
            if (distanceBtn && distanceDropdown) {
                distanceBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    distanceDropdown.classList.toggle('hidden');
                });
            }
            
            // Difficulty filter
            const difficultyBtn = container.querySelector('#difficulty-filter-btn');
            const difficultyDropdown = document.getElementById('difficulty-dropdown-external');
            if (difficultyBtn && difficultyDropdown) {
                difficultyBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    difficultyDropdown.classList.toggle('hidden');
                });
            }
        }
        
        // Initial check
        handleFilterPosition();
        
        // Re-check on window resize
        window.addEventListener('resize', handleFilterPosition);
        
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
    });
</script>
@endpush
@endsection