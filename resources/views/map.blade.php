@extends('layouts.public')

@section('title', 'Interactive Trail Map')
@section('content')
<div class="relative" style="height: calc(100vh - 100px);">
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
                <button class="filter-pill bg-white hover:bg-gray-50 border border-gray-300 rounded-full px-4 py-2 text-sm font-medium text-gray-700 flex items-center gap-2 shadow-sm flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                    <span>More</span>
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

    <!-- Map Type Selector - Top Right on Desktop, Bottom Left on Mobile -->
    <div class="absolute top-4 right-4 md:top-4 md:right-4 max-md:top-auto max-md:right-auto max-md:bottom-4 max-md:left-4 z-30">
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
        <div class="px-4 py-2 bg-gray-50 border-b border-gray-200">
            <p class="text-sm text-gray-600">
                <span id="trail-count">0</span> trails
            </p>
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
        <button class="filter-pill bg-white hover:bg-gray-50 border border-gray-300 rounded-full px-4 py-2 text-sm font-medium text-gray-700 flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
            <span>All filters</span>
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
</style>
<script>

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
            }).setView([49.2827, -122.7927], 10);

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
                'satellite': L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    attribution: '© Esri, Maxar, Earthstar Geographics',
                    maxZoom: 18
                }),
                'terrain': L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                    attribution: 'Map data: © OpenStreetMap, SRTM | Map style: © OpenTopoMap',
                    maxZoom: 18
                }),
                'outdoors': L.tileLayer('https://{s}.tile-cyclosm.openstreetmap.fr/cyclosm/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap, CyclOSM',
                    maxZoom: 20
                })
            };

            // Track current map type
            this.currentMapType = 'standard';

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
            this.map.on('moveend', () => {
                this.updateVisibleTrails();
            });

            this.map.on('zoomend', () => {
                this.updateVisibleTrails();
            });

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
            const bounds = this.map.getBounds();
            
            const visibleTrails = this.allTrails.filter(trail => {
                if (!trail.coordinates) return false;
                
                // Sanitize coordinates
                const coords = this.sanitizeCoordinates(trail.coordinates);
                if (!coords) return false;
                
                const [lat, lng] = coords;
                
                // Validate lat/lng are numbers
                if (typeof lat !== 'number' || typeof lng !== 'number') return false;
                
                const isVisible = bounds.contains([lat, lng]);
                
                if (!isVisible && trail.route_coordinates && trail.route_coordinates.length > 0) {
                    return trail.route_coordinates.some(coord => {
                        const sanitized = this.sanitizeCoordinates(coord);
                        return sanitized && bounds.contains(sanitized);
                    });
                }
                
                return isVisible;
            });
            
            return this.filterTrails(visibleTrails);
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

                // Apply seasonal recommendation filter
                if (trail.seasonal_info && !trail.seasonal_info.recommended) {
                    return false;
                }

                return true;
            });
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
            
            allFilteredTrails.forEach(trail => {
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

            const icon = L.divIcon({
                html: `<div style="background-color: ${colors[activity.type] || '#6B7280'};" class="w-6 h-6 rounded-full border-2 border-white shadow-lg flex items-center justify-center text-white text-xs font-bold">${activity.icon || '•'}</div>`,
                className: 'custom-trail-marker',
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });

            const marker = L.marker(coords, { icon })
                .bindPopup(this.createPopupContent(trail));

            marker.on('click', () => {
                this.showTrailInfo(trail);
            });

            return marker;
        }

        createHighlightMarkers(trail) {
            if (!trail.highlights || trail.highlights.length === 0) return;

            trail.highlights.forEach(highlight => {
                const icon = L.divIcon({
                    html: `<div style="background-color: ${highlight.color};" class="w-8 h-8 rounded-full border-2 border-white shadow-lg flex items-center justify-center text-white text-lg font-bold">${highlight.icon}</div>`,
                    className: 'custom-highlight-marker',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });

                const marker = L.marker(highlight.coordinates, { icon })
                    .bindPopup(this.createHighlightPopupContent(trail, highlight));

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
                        <p class="text-gray-700">${highlight.description}</p>
                    </div>
                ` : ''}
                
                <div class="border-t pt-4 mb-4">
                    <h4 class="font-semibold text-gray-900 mb-2">Located on Trail:</h4>
                    <div class="bg-gray-50 p-3 rounded">
                        <h5 class="font-medium text-gray-900">${trail.name}</h5>
                        <div class="grid grid-cols-2 gap-2 mt-2 text-sm">
                            <span>Distance: ${trail.distance}km</span>
                            <span>Difficulty: ${trail.difficulty}/5</span>
                        </div>
                    </div>
                </div>
                
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
            
            content.innerHTML = `
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-bold text-gray-900">${trail.name}</h3>
                    <button onclick="closeTrailInfoPanel()" 
                            class="text-gray-400 hover:text-gray-600 flex-shrink-0 ml-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                ${trail.preview_photo ? `
                    <img src="${trail.preview_photo}" alt="${trail.name}" 
                        class="w-full h-40 object-cover rounded-lg mb-4">
                ` : `
                    <div class="w-full h-40 bg-gradient-to-br from-green-400 to-blue-600 rounded-lg mb-4 flex items-center justify-center">
                        <svg class="w-12 h-12 text-white opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                `}
                
                <div class="grid grid-cols-2 gap-3 mb-4 text-sm">
                    <div class="bg-blue-50 p-2 rounded text-center">
                        <div class="font-bold text-blue-600">${trail.distance}</div>
                        <div class="text-gray-600">km</div>
                    </div>
                    <div class="bg-green-50 p-2 rounded text-center">
                        <div class="font-bold text-green-600">${trail.elevation_gain || 0}</div>
                        <div class="text-gray-600">meters</div>
                    </div>
                    <div class="bg-yellow-50 p-2 rounded text-center">
                        <div class="font-bold text-yellow-600">${trail.estimated_time || 'N/A'}</div>
                        <div class="text-gray-600">hours</div>
                    </div>
                    <div class="bg-purple-50 p-2 rounded text-center">
                        <div class="font-bold text-purple-600">${trail.difficulty}</div>
                        <div class="text-gray-600">difficulty</div>
                    </div>
                </div>

                ${trail.seasonal_info?.notes ? `
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-3 mb-4">
                        <p class="text-sm text-blue-700">
                            <strong>Seasonal Note:</strong> ${trail.seasonal_info.notes}
                        </p>
                    </div>
                ` : ''}

                <div class="space-y-2 mb-4">
                    <div class="flex flex-wrap gap-1">
                        ${trail.activities.map(activity => 
                            `<span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded">${activity.icon} ${activity.name}</span>`
                        ).join('')}
                    </div>
                </div>

                <a href="/trails/${trail.id}" 
                class="block w-full bg-primary-600 hover:bg-primary-700 text-white text-center py-2 px-4 rounded-md font-medium transition-colors">
                    View Full Details
                </a>
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
                        <p class="text-sm mt-2">Try zooming out or adjusting your filters</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = trails.map(trail => `
                <div class="trail-list-card" onclick="window.trailMap.focusOnTrailById(${trail.id})">
                    ${trail.preview_photo ? 
                        `<img src="${trail.preview_photo}" alt="${trail.name}" class="trail-list-image">` :
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
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                ${trail.difficulty}
                            </span>
                            <span>•</span>
                            <span>${trail.distance} km</span>
                            <span>•</span>
                            <span>${trail.estimated_time || 'N/A'}h</span>
                        </div>
                        
                        <div class="flex items-center gap-1">
                            ${trail.activities.slice(0, 3).map(activity => 
                                `<span class="px-2 py-0.5 bg-gray-100 text-gray-700 text-xs rounded">${activity.icon}</span>`
                            ).join('')}
                        </div>
                    </div>
                </div>
            `).join('');
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
        const trailMap = new EnhancedTrailMap();

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