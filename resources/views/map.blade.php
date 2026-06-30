@extends('layouts.public')

@section('title', 'Interactive Trail Map (Mapbox)')

@push('styles')
<link href="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.css" rel="stylesheet" />
@endpush

@section('content')
<div class="flex h-[calc(100vh-80px)] md:h-[calc(100vh-80px)] max-md:h-[100dvh] overflow-hidden">

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

                <!-- Distance -->
                <div class="mb-6">
                    <h4 class="font-semibold text-base mb-3">Distance</h4>
                    <div class="flex flex-wrap gap-2">
                        <button data-dist="" class="dist-chip active-dist px-4 py-2 rounded-full text-sm font-medium border-2 border-transparent transition-all bg-gray-100 hover:bg-gray-200">Any</button>
                        <button data-dist="0-5" class="dist-chip px-4 py-2 rounded-full text-sm font-medium border-2 border-transparent transition-all bg-gray-100 hover:bg-gray-200">&lt; 5 km</button>
                        <button data-dist="5-10" class="dist-chip px-4 py-2 rounded-full text-sm font-medium border-2 border-transparent transition-all bg-gray-100 hover:bg-gray-200">5 – 10 km</button>
                        <button data-dist="10-20" class="dist-chip px-4 py-2 rounded-full text-sm font-medium border-2 border-transparent transition-all bg-gray-100 hover:bg-gray-200">10 – 20 km</button>
                        <button data-dist="20+" class="dist-chip px-4 py-2 rounded-full text-sm font-medium border-2 border-transparent transition-all bg-gray-100 hover:bg-gray-200">20+ km</button>
                    </div>
                </div>

                <!-- Difficulty -->
                <div class="mb-6 border-t pt-6">
                    <h4 class="font-semibold text-base mb-3">Difficulty</h4>
                    <div class="flex flex-wrap gap-2">
                        <button data-diff="" class="diff-chip active-diff px-4 py-2 rounded-full text-sm font-medium border-2 border-transparent transition-all bg-gray-100 hover:bg-gray-200">All levels</button>
                        <button data-diff="1" class="diff-chip px-4 py-2 rounded-full text-sm font-medium border-2 border-transparent transition-all bg-gray-100 text-emerald-700 hover:bg-gray-200">1 · Easy</button>
                        <button data-diff="2" class="diff-chip px-4 py-2 rounded-full text-sm font-medium border-2 border-transparent transition-all bg-gray-100 text-lime-700 hover:bg-gray-200">2 · Moderate</button>
                        <button data-diff="3" class="diff-chip px-4 py-2 rounded-full text-sm font-medium border-2 border-transparent transition-all bg-gray-100 text-yellow-700 hover:bg-gray-200">3 · Mod-Hard</button>
                        <button data-diff="4" class="diff-chip px-4 py-2 rounded-full text-sm font-medium border-2 border-transparent transition-all bg-gray-100 text-orange-700 hover:bg-gray-200">4 · Hard</button>
                        <button data-diff="5" class="diff-chip px-4 py-2 rounded-full text-sm font-medium border-2 border-transparent transition-all bg-gray-100 text-red-700 hover:bg-gray-200">5 · Expert</button>
                    </div>
                </div>

                <!-- Trail Type -->
                <div class="mb-6 border-t pt-6">
                    <h4 class="font-semibold text-base mb-3">Trail Type</h4>
                    <div class="flex flex-wrap gap-2">
                        <button data-value="" class="trail-type-chip active-chip px-4 py-2 rounded-full text-sm font-medium border-2 border-transparent transition-all bg-gray-100 hover:bg-gray-200" onclick="setChip(this,'trail-type-chip')">All types</button>
                        <button data-value="loop" class="trail-type-chip px-4 py-2 rounded-full text-sm font-medium border-2 border-transparent transition-all bg-gray-100 hover:bg-gray-200" onclick="setChip(this,'trail-type-chip')">Loop</button>
                        <button data-value="out-and-back" class="trail-type-chip px-4 py-2 rounded-full text-sm font-medium border-2 border-transparent transition-all bg-gray-100 hover:bg-gray-200" onclick="setChip(this,'trail-type-chip')">Out &amp; Back</button>
                        <button data-value="point-to-point" class="trail-type-chip px-4 py-2 rounded-full text-sm font-medium border-2 border-transparent transition-all bg-gray-100 hover:bg-gray-200" onclick="setChip(this,'trail-type-chip')">Point to Point</button>
                    </div>
                </div>

                <!-- Duration -->
                <div class="mb-6 border-t pt-6">
                    <h4 class="font-semibold text-base mb-3">Duration</h4>
                    <div class="flex flex-wrap gap-2">
                        <button data-value="" class="duration-chip active-chip px-4 py-2 rounded-full text-sm font-medium border-2 border-transparent transition-all bg-gray-100 hover:bg-gray-200" onclick="setChip(this,'duration-chip')">Any</button>
                        <button data-value="0-1" class="duration-chip px-4 py-2 rounded-full text-sm font-medium border-2 border-transparent transition-all bg-gray-100 hover:bg-gray-200" onclick="setChip(this,'duration-chip')">&lt; 1 hr</button>
                        <button data-value="1-2" class="duration-chip px-4 py-2 rounded-full text-sm font-medium border-2 border-transparent transition-all bg-gray-100 hover:bg-gray-200" onclick="setChip(this,'duration-chip')">1–2 hrs</button>
                        <button data-value="2-4" class="duration-chip px-4 py-2 rounded-full text-sm font-medium border-2 border-transparent transition-all bg-gray-100 hover:bg-gray-200" onclick="setChip(this,'duration-chip')">2–4 hrs</button>
                        <button data-value="4-6" class="duration-chip px-4 py-2 rounded-full text-sm font-medium border-2 border-transparent transition-all bg-gray-100 hover:bg-gray-200" onclick="setChip(this,'duration-chip')">4–6 hrs</button>
                        <button data-value="6+" class="duration-chip px-4 py-2 rounded-full text-sm font-medium border-2 border-transparent transition-all bg-gray-100 hover:bg-gray-200" onclick="setChip(this,'duration-chip')">6+ hrs</button>
                    </div>
                </div>

                <!-- Elevation Gain -->
                <div class="mb-6 border-t pt-6">
                    <h4 class="font-semibold text-base mb-3">Elevation Gain</h4>
                    <div class="flex flex-wrap gap-2">
                        <button data-value="" class="elevation-chip active-chip px-4 py-2 rounded-full text-sm font-medium border-2 border-transparent transition-all bg-gray-100 hover:bg-gray-200" onclick="setChip(this,'elevation-chip')">Any</button>
                        <button data-value="0-100" class="elevation-chip px-4 py-2 rounded-full text-sm font-medium border-2 border-transparent transition-all bg-gray-100 hover:bg-gray-200" onclick="setChip(this,'elevation-chip')">Flat (0–100m)</button>
                        <button data-value="100-300" class="elevation-chip px-4 py-2 rounded-full text-sm font-medium border-2 border-transparent transition-all bg-gray-100 hover:bg-gray-200" onclick="setChip(this,'elevation-chip')">Easy (100–300m)</button>
                        <button data-value="300-600" class="elevation-chip px-4 py-2 rounded-full text-sm font-medium border-2 border-transparent transition-all bg-gray-100 hover:bg-gray-200" onclick="setChip(this,'elevation-chip')">Moderate (300–600m)</button>
                        <button data-value="600-1000" class="elevation-chip px-4 py-2 rounded-full text-sm font-medium border-2 border-transparent transition-all bg-gray-100 hover:bg-gray-200" onclick="setChip(this,'elevation-chip')">Steep (600–1000m)</button>
                        <button data-value="1000+" class="elevation-chip px-4 py-2 rounded-full text-sm font-medium border-2 border-transparent transition-all bg-gray-100 hover:bg-gray-200" onclick="setChip(this,'elevation-chip')">Very Steep (1000m+)</button>
                    </div>
                </div>

                <!-- Map Layers -->
                <div class="mb-6 border-t pt-6">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-base">Map Layers</h4>
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-500">
                            <input type="checkbox" class="section-toggle w-4 h-4" data-target="layer-checkbox">
                            <span class="select-none">All</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                            <input type="checkbox" id="show-businesses-checkbox" class="layer-checkbox w-5 h-5" value="businesses">
                            <span class="ml-3 text-sm">🏪 Businesses</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg">
                            <input type="checkbox" id="show-facilities-checkbox" class="layer-checkbox w-5 h-5" value="facilities">
                            <span class="ml-3 text-sm">📍 Facilities</span>
                        </label>
                    </div>
                </div>

                <!-- Features -->
                <div class="mb-6 border-t pt-6">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-base">Features</h4>
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-500">
                            <input type="checkbox" class="section-toggle w-4 h-4" data-target="feature-checkbox">
                            <span class="select-none">All</span>
                        </label>
                    </div>
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
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-base">Activities</h4>
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-500">
                            <input type="checkbox" class="section-toggle w-4 h-4" data-target="activity-checkbox">
                            <span class="select-none">All</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($activities as $activity)
                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-3 rounded-lg" data-activity-label>
                            <input
                                type="checkbox"
                                value="{{ $activity->slug }}"
                                class="activity-checkbox w-5 h-5"
                                data-season-applicable="{{ $activity->season_applicable ?? 'both' }}"
                            >
                            <span class="ml-3 text-sm flex items-center gap-1">
                                @if($activity->icon_image)
                                    <img src="{{ Storage::url($activity->icon_image) }}" alt="" class="w-5 h-5 object-cover rounded inline-block">
                                @elseif($activity->icon)
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
            <!-- Tours -->
            <button data-location-filter="tour"
                    class="location-filter-btn sidebar-nav-btn flex flex-col items-center gap-1 w-full py-3 px-1 text-center transition-colors">
                <span class="text-xl">🗺️</span>
                <span class="text-[10px] font-medium leading-tight">Tours</span>
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
            onclick="event.stopPropagation(); window.trailMap && window.trailMap.stopFlyAnimation()"
            class="hidden absolute bottom-8 left-3 max-md:bottom-24 max-md:left-4 z-40 bg-white border border-red-200 text-red-700 hover:bg-red-50 rounded-full shadow-lg px-4 py-2 text-sm font-semibold flex items-center gap-2 transition-colors">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
            <rect x="6" y="6" width="12" height="12" rx="2"/>
        </svg>
        <span>Stop Fly Along</span>
    </button>

    <!-- Live stats counter — shown only during fly-along -->
    <div id="fly-stats-overlay"
         class="hidden absolute top-4 z-40 rounded-2xl overflow-hidden pointer-events-none"
         style="right:90px;background:rgba(0,0,0,0.68);backdrop-filter:blur(10px);min-width:190px;box-shadow:0 4px 24px rgba(0,0,0,0.35);">
        <div style="padding:8px 14px 6px;border-bottom:1px solid rgba(255,255,255,0.07);">
            <span style="font-size:9px;letter-spacing:0.12em;color:rgba(255,255,255,0.4);text-transform:uppercase;font-weight:700;">Trail Progress</span>
        </div>
        <div style="padding:8px 14px 12px;display:flex;gap:18px;align-items:flex-end;">
            <div>
                <div style="font-size:24px;font-weight:800;color:#fff;line-height:1;font-variant-numeric:tabular-nums;" id="fly-stat-dist">0.0</div>
                <div style="font-size:10px;color:rgba(255,255,255,0.45);margin-top:3px;">km traveled</div>
            </div>
            <div style="width:1px;height:32px;background:rgba(255,255,255,0.1);flex-shrink:0;"></div>
            <div>
                <div style="font-size:24px;font-weight:800;color:#60a5fa;line-height:1;font-variant-numeric:tabular-nums;" id="fly-stat-elev">0</div>
                <div style="font-size:10px;color:rgba(255,255,255,0.45);margin-top:3px;">m gain</div>
            </div>
        </div>
    </div>

    <!-- Bottom-right custom controls (sit above Mapbox zoom) — on mobile move to top-right below layers -->
    <div class="absolute bottom-24 right-2.5 max-md:bottom-auto max-md:top-[182px] max-md:right-4 z-30 flex flex-col gap-1.5">
        <!-- 3D Toggle -->
        <div id="view-mode-control" class="relative" style="width:29px;height:29px;">
            <!-- Sliding options pill (appears to the left of the button) -->
            <div id="view-mode-options"
                class="absolute right-full top-1/2 -translate-y-1/2 mr-2 flex items-center gap-1 bg-white rounded-full p-1 shadow-md border border-gray-300 pointer-events-none opacity-0 -translate-x-2 transition-all duration-200">
                <button type="button" data-view-mode="2d"
                    class="view-mode-option px-3 py-1 rounded-full text-xs font-bold text-gray-500 hover:bg-gray-100 transition-colors leading-none">
                    2D
                </button>
                <button type="button" data-view-mode="3d"
                    class="view-mode-option active px-3 py-1 rounded-full text-xs font-bold text-gray-500 hover:bg-gray-100 transition-colors leading-none">
                    3D
                </button>
            </div>
            <button id="toggle-3d-btn"
                type="button"
                title="Change view"
                class="bg-white text-gray-700 shadow-md hover:bg-gray-50 transition-colors border border-gray-300"
                style="width:29px;height:29px;display:flex;align-items:center;justify-content:center;border-radius:4px;">
                <span class="font-bold text-xs leading-none">3D</span>
            </button>
        </div>
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

    <!-- Map Type Selector - Top Right (desktop) / Below filter bar (mobile) -->
    <div class="absolute top-4 right-4 max-md:top-28 z-30">
        <div class="relative">
            <!-- Toggle Button -->
            <button id="layers-toggle" class="bg-white rounded-lg shadow-lg p-3 hover:bg-gray-50 transition-colors">
                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 0v10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2z"/>
                </svg>
            </button>

            <!-- Dropdown Menu - Opens to the right on mobile, down on desktop -->
            <div id="layers-dropdown" class="hidden absolute top-full right-0 mt-2 bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden" style="min-width: 200px;">
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

                        <button class="layer-option-card active" data-map-type="satellite">
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
    <div id="mobile-search-bar" class="md:hidden absolute top-2 left-4 right-4 z-30">
        <button id="mobile-search-trigger" class="w-full flex items-center gap-3 bg-white rounded-full px-4 py-3 shadow-lg border border-gray-200 text-left">
            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <span class="text-gray-400 text-sm">Search trails, lakes, businesses...</span>
        </button>
    </div>

    <!-- Filter Bar — Season toggle + single Filters button -->
    <div id="filter-bar" class="absolute top-16 md:top-3 left-0 right-0 z-30 md:right-16 pointer-events-none">
        <div class="px-3 pointer-events-auto">
            <div class="flex items-center gap-2 max-md:w-full max-md:items-stretch">
                <!-- Season -->
                <div class="flex gap-0.5 bg-white rounded-2xl p-1 max-md:p-0.5 shadow-md border border-gray-200 flex-shrink-0 max-md:[flex:6] max-md:items-stretch">
                    <button data-season="summer" class="season-btn active flex items-center justify-center max-md:flex-1 px-3 py-1.5 max-md:py-0 rounded-xl text-xs font-semibold transition-all">☀️ Summer</button>
                    <button data-season="winter" class="season-btn flex items-center justify-center max-md:flex-1 px-3 py-1.5 max-md:py-0 rounded-xl text-xs font-semibold transition-all">❄️ Winter</button>
                </div>
                <!-- Filters -->
                <button id="all-filters-btn" class="flex items-center justify-center gap-1.5 bg-white rounded-2xl px-3 py-2 shadow-md border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition-all flex-shrink-0 max-md:[flex:4]">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18M6 10h12M9 16h6"/>
                    </svg>
                    Filters
                    <span id="filter-count-badge" class="hidden bg-primary-600 text-white text-xs rounded-full px-1.5 leading-5 font-bold">0</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Collapsed Panel Button (Hidden by default) -->
    <!-- Trail Info Panel (Hidden by default) -->
    <div id="trail-info-panel" class="hidden absolute top-16 bottom-4 left-4 md:top-16 md:bottom-4 md:left-4 max-md:inset-x-4 max-md:bottom-4 max-md:top-auto z-40 bg-white rounded-lg shadow-xl w-80 max-md:w-auto flex flex-col overflow-hidden">
        <div id="trail-info-content" class="flex flex-col flex-1 overflow-hidden">
            <!-- Dynamic content will be loaded here -->
        </div>
    </div>

    <!-- Business Detail Panel -->
    <div id="business-panel" class="biz-panel hidden absolute top-16 bottom-4 left-4 md:top-16 md:bottom-4 md:left-4 max-md:inset-x-4 max-md:bottom-4 max-md:top-auto z-40 bg-white rounded-lg shadow-xl w-80 max-md:w-auto flex flex-col overflow-hidden">
        <div id="business-panel-content" class="flex flex-col flex-1 overflow-hidden"></div>
    </div>

    <!-- Mobile Trail Bottom Card (mobile only) -->
    <div id="mobile-trail-card" class="md:hidden hidden fixed bottom-0 inset-x-0 z-50 bg-white" style="border-radius:16px 16px 0 0;box-shadow:0 -4px 24px rgba(0,0,0,0.18);"
         ontouchstart="event.stopPropagation()" onclick="event.stopPropagation()">
        <div class="relative flex items-center justify-center pt-3 pb-0 px-4">
            <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
            <button onclick="closeMobileTrailCard()" class="absolute right-3 top-2 p-1 text-gray-400 hover:text-gray-600" aria-label="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <!-- Main row -->
        <div class="flex items-center px-4 pt-2 pb-2 gap-3">
            <div class="w-[68px] h-[68px] rounded-xl overflow-hidden flex-shrink-0 bg-gray-100">
                <img id="mobile-trail-img" src="" alt="" class="hidden w-full h-full object-cover">
                <div id="mobile-trail-placeholder" class="w-full h-full flex items-center justify-center text-2xl"></div>
            </div>
            <div class="flex-1 min-w-0">
                <h3 id="mobile-trail-name" class="font-bold text-gray-900 text-[15px] leading-tight truncate"></h3>
                <div id="mobile-trail-diff-row" class="flex items-center gap-1.5 mt-1"></div>
                <p id="mobile-trail-stats" class="text-xs text-gray-500 mt-0.5"></p>
            </div>
        </div>
        <!-- Hero image / gallery (shown below the info row when available) -->
        <div id="mobile-trail-hero" class="hidden px-4 pb-3">
            <img id="mobile-trail-hero-img" src="" alt="" class="hidden rounded-xl object-cover">
            <div id="mobile-trail-hero-grid" class="hidden facility-media-grid"></div>
        </div>
        <!-- Action buttons -->
        <div id="mobile-trail-actions" class="flex gap-2 px-4 pb-5 pt-1"></div>
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
        <button data-mobile-location-filter="tour" class="mobile-location-tab flex-1 flex flex-col items-center gap-1 py-2 text-xs font-medium text-gray-500 border-b-2 border-transparent">
            <span class="text-lg">🗺️</span><span>Tours</span>
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
    padding: 12px;
}

.trail-list-image-placeholder img {
    max-width: 80%;
    max-height: 80%;
    object-fit: contain;
    filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.25));
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
    #trail-info-panel,
    #business-panel {
        display: none !important;
    }
    .trail-desc-text,
    .biz-panel-description,
    .mobile-hide-desc {
        display: none !important;
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

/* Distance & Difficulty chips */
.dist-chip { color: #4B5563; }
.dist-chip.active-dist {
    background: #1d4ed8;
    color: white !important;
    box-shadow: 0 1px 4px rgba(29,78,216,0.35);
}
.diff-chip.active-diff {
    color: white !important;
    box-shadow: 0 1px 4px rgba(0,0,0,0.25);
}
.diff-chip[data-diff=""].active-diff  { background: #374151; }
.diff-chip[data-diff="1"].active-diff { background: #059669; }
.diff-chip[data-diff="2"].active-diff { background: #65a30d; }
.diff-chip[data-diff="3"].active-diff { background: #d97706; }
.diff-chip[data-diff="4"].active-diff { background: #ea580c; }
.diff-chip[data-diff="5"].active-diff { background: #dc2626; }

/* Trail Type / Duration / Elevation chips */
.trail-type-chip.active-chip,
.duration-chip.active-chip,
.elevation-chip.active-chip {
    background: #1d4ed8;
    color: white !important;
    box-shadow: 0 1px 4px rgba(29,78,216,0.35);
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
/* View mode (2D / 3D) sliding options */
#view-mode-options.is-open {
    pointer-events: auto;
    opacity: 1;
    transform: translate(0, -50%);
}

.view-mode-option.active {
    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    color: #fff;
    box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
}

.view-mode-option.active:hover {
    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
}

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
        position: fixed !important;
        z-index: 50 !important;
        /* Sit just below the mobile filter bar (top:60px + ~48px pills + gap) */
        top: 115px !important;
        bottom: 1rem !important;
        left: 1rem !important;
        right: 1rem !important;
        max-height: none !important;
        height: auto !important;
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
    padding: 24px;
}

.biz-panel-hero-placeholder img {
    max-width: 70%;
    max-height: 70%;
    object-fit: contain;
    filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.25));
}

.biz-panel-body {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
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
    margin: 0 0 12px;
}

.biz-panel-description {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.6;
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

@keyframes spin { to { transform: rotate(360deg); } }

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

#mobile-trail-hero-grid:not(.hidden) {
    display: flex;
    justify-content: center;
    gap: 6px;
}

#mobile-trail-hero-grid .facility-media-item {
    aspect-ratio: 1;
    width: 56px;
    flex-shrink: 0;
    border-radius: 6px;
    cursor: pointer;
}

#mobile-trail-hero:not(.hidden) {
    display: flex;
    justify-content: center;
}

#mobile-trail-hero-img {
    height: 56px;
    cursor: pointer;
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

    // Facility Media Modal — carousel through every photo/video for the facility.
    // Media is cached per facility id by loadFacilities().
    window._facilityMediaCache = window._facilityMediaCache || {};
    let _facilityModalState = { facilityId: null, index: 0 };

    // Pro video content is gated; photos in the gallery stay free.
    function _facilityMediaIsGatedVideo(media) {
        const isVideo = media && (media.media_type === 'video_url' || media.media_type === 'video');
        return isVideo && !window.xsIsPro();
    }

    function openFacilityMediaModal(facilityId, index) {
        const data = window._facilityMediaCache[facilityId];
        if (!data || !data.media || !data.media.length) { return; }
        const targetIndex = Math.max(0, Math.min(index || 0, data.media.length - 1));
        if (_facilityMediaIsGatedVideo(data.media[targetIndex])) {
            window.xsRequirePro('video');
            return;
        }
        _facilityModalState.facilityId = facilityId;
        _facilityModalState.index = targetIndex;
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
        const nextIndex = (_facilityModalState.index + delta + total) % total;
        if (_facilityMediaIsGatedVideo(data.media[nextIndex])) {
            window.xsRequirePro('video');
            return;
        }
        _facilityModalState.index = nextIndex;
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
    // Section "All" toggle — checks/unchecks every child checkbox in the group
    document.querySelectorAll('.section-toggle').forEach(toggle => {
        toggle.addEventListener('change', function () {
            const targetClass = this.dataset.target;
            document.querySelectorAll('.' + targetClass).forEach(cb => {
                cb.checked = this.checked;
            });
            updateSectionToggleState(targetClass);
            updateFilterCountBadge();
        });
    });

    // Keep the "All" toggle in sync when individual checkboxes change
    function updateSectionToggleState(targetClass) {
        const checkboxes = Array.from(document.querySelectorAll('.' + targetClass));
        const visibleCheckboxes = checkboxes.filter(cb => !cb.closest('label')?.classList.contains('hidden'));
        if (visibleCheckboxes.length === 0) return;
        const allChecked = visibleCheckboxes.every(cb => cb.checked);
        const noneChecked = visibleCheckboxes.every(cb => !cb.checked);
        const toggle = document.querySelector(`.section-toggle[data-target="${targetClass}"]`);
        if (!toggle) return;
        toggle.checked = allChecked;
        toggle.indeterminate = !allChecked && !noneChecked;
    }

    // Patch individual checkbox change to update their section toggle
    document.querySelectorAll('.layer-checkbox, .feature-checkbox, .activity-checkbox').forEach(cb => {
        cb.addEventListener('change', function () {
            const cls = Array.from(this.classList).find(c => c.endsWith('-checkbox') && c !== 'w-5' && c !== 'h-5');
            if (cls) updateSectionToggleState(cls);
            updateFilterCountBadge();
        });
    });

    let advancedFilters = {
        trailType: '',
        duration: '',
        elevation: '',
        layers: [],
        features: [],
        activities: [],
    };

    // Open All Filters Modal
    document.getElementById('all-filters-btn')?.addEventListener('click', function() {
        document.getElementById('all-filters-modal').classList.remove('hidden');
    });

    document.getElementById('all-filters-btn-mobile')?.addEventListener('click', function() {
        document.getElementById('all-filters-modal').classList.remove('hidden');
    });

    // Helper — single-select chip toggle
    function setChip(el, chipClass) {
        document.querySelectorAll('.' + chipClass).forEach(b => b.classList.remove('active-chip'));
        el.classList.add('active-chip');
        updateFilterCountBadge();
    }

    // Apply All Filters
    function applyAllFilters() {
        // Commit distance + difficulty chip selections to the map
        if (window.trailMap) {
            window.trailMap.currentDistance = document.querySelector('.dist-chip.active-dist')?.dataset.dist ?? '';
            window.trailMap.currentDifficulty = document.querySelector('.diff-chip.active-diff')?.dataset.diff ?? '';
        }

        // Get trail type
        advancedFilters.trailType = document.querySelector('.trail-type-chip.active-chip')?.dataset.value ?? '';

        // Get duration
        advancedFilters.duration = document.querySelector('.duration-chip.active-chip')?.dataset.value ?? '';

        // Get elevation
        advancedFilters.elevation = document.querySelector('.elevation-chip.active-chip')?.dataset.value ?? '';

        // Get layers (businesses / facilities)
        advancedFilters.layers = Array.from(document.querySelectorAll('.layer-checkbox:checked')).map(cb => cb.value);

        // Get features (multi-select)
        advancedFilters.features = Array.from(document.querySelectorAll('.feature-checkbox:checked')).map(cb => cb.value);

        // Get activities (multi-select)
        advancedFilters.activities = Array.from(document.querySelectorAll('.activity-checkbox:checked')).map(cb => cb.value);

        // Close modal
        document.getElementById('all-filters-modal').classList.add('hidden');

        // Close any open trail/business detail panels
        document.getElementById('trail-info-panel')?.classList.add('hidden');
        document.getElementById('business-panel')?.classList.add('hidden');
        if (window.trailMap) {
            window.trailMap._clearSelection();
            window.trailMap._selectedTrailId = null;
        }

        // Update filter count badge
        updateFilterCountBadge();

        // Apply filters to map
        if (window.trailMap) {
            window.trailMap.applyAdvancedFilters(advancedFilters);
        }
    }

    // Clear All Filters — restores default checked state
    function clearAllFilters() {
        // Reset distance + difficulty chips
        document.querySelectorAll('.dist-chip').forEach(b => b.classList.remove('active-dist'));
        const anyDist = document.querySelector('.dist-chip[data-dist=""]');
        if (anyDist) anyDist.classList.add('active-dist');
        document.querySelectorAll('.diff-chip').forEach(b => b.classList.remove('active-diff'));
        const allDiff = document.querySelector('.diff-chip[data-diff=""]');
        if (allDiff) allDiff.classList.add('active-diff');
        if (window.trailMap) { window.trailMap.currentDistance = ''; window.trailMap.currentDifficulty = ''; }

        // Reset trail type / duration / elevation chips
        ['trail-type-chip', 'duration-chip', 'elevation-chip'].forEach(cls => {
            document.querySelectorAll('.' + cls).forEach(b => b.classList.remove('active-chip'));
            const def = document.querySelector('.' + cls + '[data-value=""]');
            if (def) def.classList.add('active-chip');
        });

        // Restore default: all unchecked (empty = no filter = show all)
        document.querySelectorAll('.layer-checkbox, .feature-checkbox, .activity-checkbox').forEach(cb => { cb.checked = false; });
        document.querySelectorAll('.section-toggle').forEach(cb => { cb.checked = false; cb.indeterminate = false; });

        // Reset filters state — all empty = no filter = show all
        advancedFilters = {
            trailType: '',
            duration: '',
            elevation: '',
            layers: [],
            features: [],
            activities: [],
        };

        // Update badge
        updateFilterCountBadge();

        // Apply filters (empty layers = show all businesses + facilities)
        if (window.trailMap) {
            window.trailMap.applyAdvancedFilters(advancedFilters);
        }
    }

    // Update Filter Count Badge — counts active filters
    function updateFilterCountBadge() {
        let count = 0;

        // Distance active (read chip, not committed state)
        if (document.querySelector('.dist-chip.active-dist')?.dataset.dist) count++;

        // Difficulty active (read chip, not committed state)
        if (document.querySelector('.diff-chip.active-diff')?.dataset.diff) count++;

        // Trail type / duration / elevation chips (non-default = data-value != "")
        if (document.querySelector('.trail-type-chip.active-chip')?.dataset.value) count++;
        if (document.querySelector('.duration-chip.active-chip')?.dataset.value) count++;
        if (document.querySelector('.elevation-chip.active-chip')?.dataset.value) count++;

        // Checked visible activity checkboxes
        document.querySelectorAll('.activity-checkbox:checked').forEach(cb => {
            if (!cb.closest('label')?.classList.contains('hidden')) count++;
        });

        // Checked feature checkboxes
        document.querySelectorAll('.feature-checkbox:checked').forEach(() => count++);

        // Checked layer checkboxes
        document.querySelectorAll('.layer-checkbox:checked').forEach(() => count++);

        const badge = document.getElementById('filter-count-badge');

        if (count > 0) {
            if (badge) { badge.textContent = count; badge.classList.remove('hidden'); }
        } else {
            if (badge) badge.classList.add('hidden');
        }
    }
    mapboxgl.accessToken = '{{ $mapboxToken }}';

    class EnhancedTrailMap {
        constructor() {
            this.map = null;
            this.currentSeason = 'summer';
            this.currentDistance = '';
            this.currentDifficulty = '';
            this.activeFilters = {!! json_encode($activities->pluck('slug')->push('viewpoint')->push('highlights')->unique()->values()) !!};
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
            this.currentMapType = 'satellite';

            // If we're arriving focused on a specific trail (e.g. via "Back to Trails"),
            // start the camera there directly instead of flashing the default wide view.
            const initialFocusCoordinates = @json($focusCoordinates);
            const hasInitialFocus = Array.isArray(initialFocusCoordinates) && initialFocusCoordinates.length === 2;

            // Initialize Mapbox map with 3D terrain
            this.map = new mapboxgl.Map({
                container: 'main-map',
                style: this.mapStyles[this.currentMapType],
                center: hasInitialFocus ? [initialFocusCoordinates[1], initialFocusCoordinates[0]] : [-127.1698, 54.7804], // [lng, lat]
                zoom: hasInitialFocus ? 12 : 10,
                pitch: hasInitialFocus ? 0 : 60,
                bearing: hasInitialFocus ? 0 : -10,
                attributionControl: false,
            });

            // Navigation control (zoom + compass) — bottom right
            this.map.addControl(new mapboxgl.NavigationControl({ showCompass: false }), 'bottom-right');

            // Attribution
            this.map.addControl(new mapboxgl.AttributionControl({ compact: true }), 'bottom-left');


            // Marker storage per activity type (arrays of mapboxgl.Marker)
            this.overlayMarkers = {
                'hiking': [], 'fishing': [], 'camping': [], 'viewpoint': [], 'highlights': [], 'mountain-biking': [],
                'snowshoeing': [], 'ice-fishing': [], 'cross-country-skiing': [], 'downhill-skiing': []
            };
            this.businessMarkers = {};
            this.networkMarkers = {};
            this.networkData = [];
            this.facilityMarkers = [];
            this.showBusinesses = true;
            this.showFacilities = true;
            this.currentTrails = [];
            this.businessData = [];
            this.activeLocationFilter = 'trail';
            this._selectedTrailId = null;
            this._selectedPinMarker = null;
            this._selectedOriginalEl = null;
            this._mobileCardTrailId = null;
            this._locationMarker = null;
            this._locationCircle = null;
            this._is3D = true;
            this._isFlying = false;
            this._flyAnimation = null;
            this._flyTimeout = null;
            this._hikerMarker = null;
            this._flyTrailId = null;

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
                        'line-color': '#000000',
                        'line-width': 8,
                        'line-opacity': ['case', ['boolean', ['feature-state', 'selected'], false], 1, 0],
                    },
                });
            }

            // Visible route layer — only shown when trail is selected
            if (!this.map.getLayer('trail-routes-line')) {
                this.map.addLayer({
                    id: 'trail-routes-line',
                    type: 'line',
                    source: 'trail-routes',
                    paint: {
                        'line-color': ['get', 'color'],
                        'line-width': 4,
                        'line-opacity': ['case', ['boolean', ['feature-state', 'selected'], false], 1, 0],
                    },
                });
            }

            // Direction arrow layer — only shown when trail is selected
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
                    paint: {
                        'icon-opacity': ['case', ['boolean', ['feature-state', 'selected'], false], 1, 0],
                    },
                });
            }

            // Dedicated source for fly-along — lineMetrics:true enables line-gradient
            if (!this.map.getSource('fly-draw')) {
                this.map.addSource('fly-draw', {
                    type: 'geojson',
                    lineMetrics: true,
                    data: { type: 'FeatureCollection', features: [] },
                });
            }
            // Green base — full unwalked trail
            if (!this.map.getLayer('fly-draw-base')) {
                this.map.addLayer({
                    id: 'fly-draw-base',
                    type: 'line',
                    source: 'fly-draw',
                    layout: { 'line-cap': 'round', 'line-join': 'round' },
                    paint: {
                        'line-color': '#22c55e',
                        'line-width': 4,
                        'line-opacity': 0,
                        'line-trim-offset': [0, 0],
                    },
                });
            }
            // Blue glow behind the walked portion
            if (!this.map.getLayer('fly-draw-glow')) {
                this.map.addLayer({
                    id: 'fly-draw-glow',
                    type: 'line',
                    source: 'fly-draw',
                    layout: { 'line-cap': 'round', 'line-join': 'round' },
                    paint: {
                        'line-gradient': ['interpolate', ['linear'], ['line-progress'],
                            0, '#60a5fa', 1, '#60a5fa',
                        ],
                        'line-width': 16,
                        'line-blur': 8,
                        'line-opacity': 0,
                        'line-trim-offset': [0, 1],
                    },
                });
            }
            // Crisp blue progress line
            if (!this.map.getLayer('fly-draw-progress')) {
                this.map.addLayer({
                    id: 'fly-draw-progress',
                    type: 'line',
                    source: 'fly-draw',
                    layout: { 'line-cap': 'round', 'line-join': 'round' },
                    paint: {
                        'line-gradient': ['interpolate', ['linear'], ['line-progress'],
                            0, '#3b82f6', 1, '#3b82f6',
                        ],
                        'line-width': 5,
                        'line-opacity': 0,
                        'line-trim-offset': [0, 1],
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
                    this.closeAllPanels();
                    // Mobile card closes only on deliberate map tap (not from button actions)
                    closeMobileTrailCard();
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

            // View mode (2D / 3D) — trigger expands the options sliding to the left
            const viewModeBtn = document.getElementById('toggle-3d-btn');
            if (viewModeBtn) {
                viewModeBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.toggleViewModeOptions();
                });
            }
            document.querySelectorAll('.view-mode-option').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.setViewMode(btn.dataset.viewMode);
                });
            });
            document.addEventListener('click', (e) => {
                const wrap = document.getElementById('view-mode-control');
                if (wrap && !wrap.contains(e.target)) {
                    this.toggleViewModeOptions(false);
                }
            });

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

            // Distance chips — visual only; applied on "Apply Filters"
            document.querySelectorAll('.dist-chip').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    document.querySelectorAll('.dist-chip').forEach(b => b.classList.remove('active-dist'));
                    btn.classList.add('active-dist');
                    if (typeof updateFilterCountBadge === 'function') updateFilterCountBadge();
                });
            });

            // Difficulty chips — visual only; applied on "Apply Filters"
            document.querySelectorAll('.diff-chip').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    document.querySelectorAll('.diff-chip').forEach(b => b.classList.remove('active-diff'));
                    btn.classList.add('active-diff');
                    if (typeof updateFilterCountBadge === 'function') updateFilterCountBadge();
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

        }

        updateActivityFilters(season) {
            // Define which activities are available for each season (map overlays)
            const seasonalActivities = {
                summer: {!! json_encode($activities->filter(fn($a) => in_array($a->season_applicable, ['summer', 'both']))->pluck('slug')->push('viewpoint')->push('highlights')->unique()->values()) !!},
                winter: {!! json_encode($activities->filter(fn($a) => in_array($a->season_applicable, ['winter', 'both']))->pluck('slug')->push('viewpoint')->push('highlights')->unique()->values()) !!},
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

            // Re-sync the Activities section toggle after season change
            if (typeof updateSectionToggleState === 'function') {
                updateSectionToggleState('activity-checkbox');
            }

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
            // When only Map Layer filters are active (no trail-specific filters),
            // trails are not businesses or facilities so they should not appear.
            const hasTrailFilters = advancedFilters.activities.length > 0 ||
                                    advancedFilters.features.length > 0 ||
                                    advancedFilters.trailType ||
                                    advancedFilters.duration ||
                                    advancedFilters.elevation;
            if (advancedFilters.layers.length > 0 && !hasTrailFilters) {
                return false;
            }

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

            // Features filter: trail must have ANY of the selected features.
            // Trails with no highlights are excluded when a feature filter is active.
            if (advancedFilters.features.length > 0) {
                const trailFeatureTypes = Array.isArray(trail.highlights) ? trail.highlights.map(h => h.type) : [];
                const hasAnyFeature = advancedFilters.features.some(feature => trailFeatureTypes.includes(feature));
                if (!hasAnyFeature) return false;
            }

            // Activities filter: empty = no filter (show all). Any checked = show only matching trails.
            if (advancedFilters.activities.length > 0) {
                const trailActivities = Array.isArray(trail.activities) ? trail.activities : [];
                const trailActivityTypes = trailActivities.map(a => a.type || a.slug || a.name).filter(Boolean);
                const hasAnyActivity = advancedFilters.activities.some(activity => trailActivityTypes.includes(activity));
                if (!hasAnyActivity) return false;
            }

            return true;
        }

        applyAdvancedFilters(filters) {
            // Unified filter logic: nothing checked = show all.
            // Once ANY filter is active, only explicitly selected types appear.
            const checkedLayers = filters.layers || [];
            const hasAnyFilter = checkedLayers.length > 0 ||
                                 (filters.activities && filters.activities.length > 0) ||
                                 (filters.features && filters.features.length > 0);
            this.showBusinesses = !hasAnyFilter || checkedLayers.includes('businesses');
            this.showFacilities = !hasAnyFilter || checkedLayers.includes('facilities');

            this.applyFilters();
            this.renderNetworkMarkers();
            this.renderBusinessMarkers();
            this.renderFacilityMarkers();
        }

        updateVisibleTrails() {
            const allFilteredTrails = this.filterTrails(this.allTrails);
            let listTrails, listBusinesses;
            if (this.activeLocationFilter === 'business') {
                listTrails = [];
                listBusinesses = this.businessData || [];
            } else if (this.activeLocationFilter === 'fishing_lake') {
                listTrails = allFilteredTrails.filter(t => t.location_type === 'fishing_lake');
                listBusinesses = [];
            } else {
                listTrails = allFilteredTrails.filter(t => t.location_type === 'trail');
                listBusinesses = [];
            }
            this.renderTrailList(listTrails, listBusinesses);
        }

        // Add this function to your EnhancedTrailMap class
        getDistanceColor(distance) {
            return '#8B5E3C';
        }

        // Winter: color network-trail routes by difficulty
        // Easy (1-2) → green, Intermediate (3) → blue, Advanced (4-5) → red
        getDifficultyColor(difficulty) {
            const level = parseInt(difficulty, 10);
            switch (level) {
                case 1:
                case 2: return '#22C55E';            // Easy → green
                case 3: return '#3B82F6';            // Intermediate → blue
                case 4:
                case 5: return '#EF4444';            // Advanced → red
                default: return this.getDistanceColor();
            }
        }

        getRouteColor(trail) {
            if (this.currentSeason === 'winter' && trail.trail_network_id) {
                return this.getDifficultyColor(trail.difficulty);
            }
            return '#22c55e';
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
                // Trails that belong to a network are represented by a single network marker
                // on the main map — their routes only show on the dedicated network page.
                if (trail.trail_network_id) return;
                if (!trail.route_coordinates || trail.route_coordinates.length === 0) return;

                const sanitized = trail.route_coordinates
                    .map(c => this.sanitizeCoordinates(c))
                    .filter(c => c !== null);
                if (sanitized.length === 0) return;

                // Smoothing disabled for testing — render sanitized coords as-is
                // Mapbox coords are [lng, lat], data is [lat, lng] — swap
                const mapboxCoords = sanitized.map(c => [c[1], c[0]]);

                // Out-and-back: mirror so arrows go forward on the way out and back on the return
                const displayCoords = trail.trail_type === 'out-and-back'
                    ? [...mapboxCoords, ...[...mapboxCoords].reverse()]
                    : mapboxCoords;

                features.push({
                    type: 'Feature',
                    id: trail.id,
                    properties: {
                        trailId: trail.id,
                        color: this.getRouteColor(trail),
                        status: trail.status || 'active',
                    },
                    geometry: { type: 'LineString', coordinates: displayCoords },
                });
            });
            return { type: 'FeatureCollection', features };
        }

        highlightTrailRoute(trailId, { showPanel = true } = {}) {
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

            if (!showPanel) return;
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

            // Re-render network markers so seasonal networks are filtered correctly
            this.renderNetworkMarkers();

            // Reload trails with new season
            this.loadTrails(season);
        }

        toggleViewModeOptions(force) {
            const opts = document.getElementById('view-mode-options');
            if (!opts) { return; }
            const willOpen = typeof force === 'boolean' ? force : !opts.classList.contains('is-open');
            opts.classList.toggle('is-open', willOpen);
        }

        setViewMode(mode) {
            const next3D = mode === '3d';
            if (next3D !== this._is3D) {
                this._is3D = next3D;
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
                    btn.innerHTML = `<span class="font-bold text-xs leading-none">${this._is3D ? '3D' : '2D'}</span>`;
                }
            }
            document.querySelectorAll('.view-mode-option').forEach(b => {
                b.classList.toggle('active', b.dataset.viewMode === mode);
            });
            this.toggleViewModeOptions(false);
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

            const allFilteredTrails = this.filterTrails(this.allTrails);

            // For the sidebar list, respect the active location filter tab.
            // The map always shows all filtered trails regardless of which tab is active.
            let listTrails, listBusinesses;
            if (this.activeLocationFilter === 'business') {
                listTrails = [];
                listBusinesses = this.businessData || [];
            } else if (this.activeLocationFilter === 'fishing_lake') {
                listTrails = allFilteredTrails.filter(t => t.location_type === 'fishing_lake');
                listBusinesses = [];
            } else {
                listTrails = allFilteredTrails.filter(t => t.location_type === 'trail');
                listBusinesses = [];
            }
            this.renderTrailList(listTrails, listBusinesses);

            const mapTrails = allFilteredTrails;

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
                    if (this.currentSeason === 'summer') {
                        // Default fishing-lake marker in summer
                        if (!this.overlayMarkers['fishing']) this.overlayMarkers['fishing'] = [];
                        const marker = this.createTrailMarker(trail, { type: 'fishing', icon: '🐟', color: '#3B82F6' });
                        if (marker) this.overlayMarkers['fishing'].push(marker);
                    } else {
                        // Winter: render the lake under any winter activity it offers (e.g. ice-fishing)
                        (trail.activities || []).forEach(activity => {
                            if (this.activeFilters.includes(activity.type)) {
                                if (!this.overlayMarkers[activity.type]) this.overlayMarkers[activity.type] = [];
                                const marker = this.createTrailMarker(trail, activity);
                                if (marker) this.overlayMarkers[activity.type].push(marker);
                            }
                        });
                    }
                } else if (this.currentSeason === 'summer') {
                    if (this.activeFilters.includes('hiking')) {
                        if (!this.overlayMarkers['hiking']) this.overlayMarkers['hiking'] = [];
                        const displayActivity = this.getTrailDisplayActivity(trail);
                        const markerConfig = {
                            type: 'hiking',
                            icon: (displayActivity && displayActivity.icon) || '🥾',
                            icon_image_url: (displayActivity && displayActivity.icon_image_url) || null,
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

        // Unified marker color — same dark green on every map layer
        get markerColor() { return '#1B3935'; }

        _createMarkerEl(emoji, iconImageUrl = null) {
            const el = document.createElement('div');
            el.className = 'selectable-marker-el';
            el.dataset.emoji = emoji;
            el.style.cssText = `background-color:${this.markerColor};width:32px;height:32px;border-radius:50%;border:2px solid #ffffff;box-shadow:0 2px 8px rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;font-size:15px;cursor:pointer;line-height:1;overflow:hidden;`;
            if (iconImageUrl) {
                el.innerHTML = `<img src="${iconImageUrl}" alt="" style="width:22px;height:22px;object-fit:cover;border-radius:50%;">`;
            } else if (emoji === '🥾') {
                el.innerHTML = `<img src="{{ asset('images/hiking-boot.png') }}" alt="Hiking trail" style="width:21px;height:21px;display:block;object-fit:contain;">`;
            } else {
                el.textContent = emoji;
            }
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
            // Clear route line highlight
            if (this._selectedTrailId !== null) {
                try {
                    this.map.setFeatureState(
                        { source: 'trail-routes', id: this._selectedTrailId },
                        { selected: false }
                    );
                } catch (e) { /* ignore if source not ready */ }
                this._selectedTrailId = null;
            }
        }

        closeAllPanels() {
            document.getElementById('trail-info-panel')?.classList.add('hidden');
            this.closeBusinessPanel();
            this._clearSelection();
        }

        _isMobileViewport() {
            return window.matchMedia('(max-width: 1024px)').matches
                || window.matchMedia('(pointer: coarse)').matches
                || ('ontouchstart' in window)
                || /Mobi|Android|iPhone|iPad|iPod|Tablet/i.test(navigator.userAgent);
        }

        createTrailMarker(trail, activity) {
            const coords = this.sanitizeCoordinates(trail.coordinates);
            if (!coords) {
                console.warn('Invalid coordinates for trail:', trail.name);
                return null;
            }

            const isFishingLake = trail.location_type === 'fishing_lake';
            const emoji = isFishingLake ? '🐟' : (activity.icon || '📍');
            const iconImageUrl = isFishingLake ? null : (activity.icon_image_url || null);

            const el = this._createMarkerEl(emoji, iconImageUrl);
            el.dataset.trailId = trail.id;
            el.addEventListener('click', (e) => {
                e.stopPropagation();
                this._selectMarker(el, coords[0], coords[1]);
                this.focusOnTrail(trail, { activateLine: true });
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

        _showMobileCard({ imageUrl, placeholderIcon, placeholderBg, name, metaHtml, statsText, actionsHtml }) {
            const img = document.getElementById('mobile-trail-img');
            const placeholder = document.getElementById('mobile-trail-placeholder');
            if (imageUrl) {
                img.src = imageUrl;
                img.classList.remove('hidden');
                placeholder.classList.add('hidden');
            } else {
                img.classList.add('hidden');
                placeholder.classList.remove('hidden');
                placeholder.textContent = placeholderIcon || '📍';
                placeholder.style.background = placeholderBg || 'linear-gradient(135deg,#4b5563,#1f2937)';
            }
            document.getElementById('mobile-trail-name').textContent = name;
            document.getElementById('mobile-trail-diff-row').innerHTML = metaHtml || '';
            document.getElementById('mobile-trail-stats').textContent = statsText || '';
            document.getElementById('mobile-trail-actions').innerHTML = actionsHtml || '';
            document.getElementById('trail-info-panel')?.classList.add('hidden');
            document.getElementById('business-panel')?.classList.add('hidden');
            document.getElementById('mobile-trail-card').classList.remove('hidden');
        }

        showMobileHighlightCard(trail, highlight) {
            const btnClass = 'flex-1 flex items-center justify-center gap-1.5 py-2 px-2 rounded-lg text-xs font-semibold border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-700 transition-colors';
            const firstPhoto = highlight.media?.find(m => m.media_type === 'photo');
            const coordsJson = JSON.stringify(highlight.coordinates);
            const color = highlight.color || '#16a34a';
            this._showMobileCard({
                imageUrl: firstPhoto?.url || null,
                placeholderIcon: highlight.icon || '📍',
                placeholderBg: `linear-gradient(135deg,${color}cc,${color})`,
                name: highlight.name,
                metaHtml: `<span style="font-size:12px;font-weight:600;color:${color};">${highlight.icon || ''} ${escapeHtml(highlight.type.replace(/_/g, ' '))}</span><span style="color:#d1d5db;font-size:11px;">·</span><span style="font-size:12px;color:#6b7280;">on ${escapeHtml(trail.name)}</span>`,
                statsText: highlight.description || '',
                actionsHtml: `<button type="button" onclick="window.trailMap.viewHighlight(${trail.id},${coordsJson})" class="${btnClass}"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>Center</button><a href="/trails/${trail.id}" class="${btnClass}"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 9m0 8V9m0 0V7"/></svg>Full Trail</a>`,
            });
        }

        showMobileNetworkCard(network) {
            const btnClass = 'flex-1 flex items-center justify-center gap-1.5 py-2 px-2 rounded-lg text-xs font-semibold border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-700 transition-colors';
            const type = (network.type || '').toLowerCase();
            let icon = network.icon || '🏔️';
            if (!network.icon) {
                if (type.includes('ski') || type.includes('snow')) icon = '⛷️';
                else if (type.includes('hike') || type.includes('hiking')) icon = '🥾';
                else if (type.includes('bike') || type.includes('cycling')) icon = '🚵';
            }
            this._showMobileCard({
                imageUrl: network.image || null,
                placeholderIcon: icon,
                placeholderBg: 'linear-gradient(135deg,#14532d,#166534)',
                name: network.name || network.network_name || '',
                metaHtml: `<span style="font-size:12px;font-weight:600;color:#166534;">Trail Network</span>${network.trail_count ? `<span style="color:#d1d5db;font-size:11px;">·</span><span style="font-size:12px;color:#6b7280;">${network.trail_count} trails</span>` : ''}`,
                statsText: network.description || '',
                actionsHtml: `<a href="/trail-networks/${network.slug}" class="${btnClass}"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>View Network</a>`,
            });
        }

        showHighlightInfo(trail, highlight) {
            if (this._isMobileViewport()) {
                this.showMobileHighlightCard(trail, highlight);
                return;
            }
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

            const isMobile = this._isMobileViewport();

            // On desktop only: close business panel if it's covering the map.
            // On mobile we keep the bottom card open so the user can still see trail info.
            if (!isMobile) {
                document.getElementById('business-panel')?.classList.add('hidden');
            }

            // Fishing lakes are single points — center on the lake.
            if (trail.location_type === 'fishing_lake') {
                const coords = this.sanitizeCoordinates(trail.coordinates);
                if (coords) {
                    this.map.flyTo({ center: [coords[1], coords[0]], zoom: 15 });
                }
                return;
            }

            if (!trail.route_coordinates || trail.route_coordinates.length === 0) {
                this.showToast('Route data is not available for this trail.');
                return;
            }

            // Highlight the route line (same as PC)
            this.highlightTrailRoute(trailId, { showPanel: false });

            // Fit map to route bounds — on mobile add extra bottom padding for the card
            const sanitized = trail.route_coordinates
                .map(c => this.sanitizeCoordinates(c)).filter(c => c !== null);
            if (sanitized.length > 0) {
                const lngs = sanitized.map(c => c[1]);
                const lats = sanitized.map(c => c[0]);
                this.map.fitBounds([
                    [Math.min(...lngs), Math.min(...lats)],
                    [Math.max(...lngs), Math.max(...lats)]
                ], {
                    padding: isMobile
                        ? { top: 60, bottom: 200, left: 30, right: 30 }
                        : 60,
                    maxZoom: 13,
                });
            }

            // On mobile: ensure the card stays visible and the line is re-applied
            // after any touch events that may have bled through to the map canvas
            if (isMobile) {
                setTimeout(() => {
                    document.getElementById('mobile-trail-card')?.classList.remove('hidden');
                    this.map.setFeatureState(
                        { source: 'trail-routes', id: trailId },
                        { selected: true }
                    );
                }, 100);
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

        // items: array of { url, thumbnail_url?, media_type?, caption? } — shows a small hero
        // thumbnail, or (for multiple items) a row of small thumbnails with a "+N more" overlay
        // on the last one. Clicking any thumbnail opens the full-size facility media modal.
        _setMobileHero(items, name, cacheKey) {
            const hero = document.getElementById('mobile-trail-hero');
            const heroImg = document.getElementById('mobile-trail-hero-img');
            const heroGrid = document.getElementById('mobile-trail-hero-grid');

            if (!items) items = [];
            if (typeof items === 'string') items = items ? [{ url: items }] : [];
            items = items.filter(i => i && (i.url || i.thumbnail_url));

            if (items.length === 0) {
                hero.classList.add('hidden');
                heroImg.classList.add('hidden');
                heroGrid.classList.add('hidden');
                heroImg.src = '';
                heroGrid.innerHTML = '';
                return;
            }

            window._facilityMediaCache = window._facilityMediaCache || {};
            window._facilityMediaCache[cacheKey] = { name: name, media: items };
            hero.classList.remove('hidden');

            if (items.length === 1) {
                heroGrid.classList.add('hidden');
                heroGrid.innerHTML = '';
                heroImg.classList.remove('hidden');
                heroImg.src = items[0].thumbnail_url || items[0].url;
                heroImg.alt = name || '';
                heroImg.onclick = () => openFacilityMediaModal(cacheKey, 0);
                return;
            }

            heroImg.classList.add('hidden');
            heroImg.src = '';
            heroGrid.classList.remove('hidden');
            const maxVisible = 3;
            const remaining = items.length - maxVisible;
            heroGrid.innerHTML = items.slice(0, maxVisible).map((item, idx) => {
                const isVideo = item.media_type === 'video_url' || item.media_type === 'video';
                const thumbnailUrl = item.thumbnail_url || item.url;
                const overlay = (idx === maxVisible - 1 && remaining > 0) ? `<div class="facility-media-overlay">+${remaining} more</div>` : '';
                const videoBadge = isVideo ? '<div class="facility-video-badge">▶</div>' : '';
                return `<div class="facility-media-item" onclick="openFacilityMediaModal('${cacheKey}', ${idx})"><img src="${thumbnailUrl}" class="facility-media-thumbnail">${overlay}${videoBadge}</div>`;
            }).join('');
        }

        showMobileTrailCard(trail) {
            this._mobileCardTrailId = trail.id;
            const isFishingLake = trail.location_type === 'fishing_lake';
            const isNetworkTrail = !isFishingLake && !!trail.trail_network_id;
            const difficultyLabels = { 1: 'Very Easy', 2: 'Easy', 3: 'Moderate', 4: 'Hard', 5: 'Very Hard' };
            const difficultyColors = { 1: '#22C55E', 2: '#22C55E', 3: '#3B82F6', 4: '#EF4444', 5: '#EF4444' };

            // Thumbnail
            const img = document.getElementById('mobile-trail-img');
            const placeholder = document.getElementById('mobile-trail-placeholder');
            if (trail.preview_photo) {
                img.src = trail.preview_photo;
                img.classList.remove('hidden');
                placeholder.classList.add('hidden');
            } else {
                img.classList.add('hidden');
                placeholder.classList.remove('hidden');
                placeholder.textContent = isFishingLake ? '🐟' : '🥾';
                placeholder.style.background = isFishingLake
                    ? 'linear-gradient(135deg,#0369a1,#0ea5e9)'
                    : 'linear-gradient(135deg,#166534,#22c55e)';
            }

            // Gallery (below the info section) — excludes the featured photo shown beside the name
            const galleryItems = (trail.photos || []).filter(p => p.url !== trail.preview_photo && !p.is_featured);
            this._setMobileHero(galleryItems, trail.name, `trail-${trail.id}`);

            // Name
            document.getElementById('mobile-trail-name').textContent = trail.name;

            // Difficulty + activities (hiking) | Fish species (fishing lake)
            const diffRow = document.getElementById('mobile-trail-diff-row');
            if (!isFishingLake) {
                const diffLevel = Math.round(parseFloat(trail.difficulty));
                let html = '';
                if (diffLevel >= 1 && diffLevel <= 5) {
                    html += `<span style="width:9px;height:9px;border-radius:50%;background:${difficultyColors[diffLevel]};display:inline-block;flex-shrink:0;"></span><span style="font-size:12px;font-weight:600;color:#374151;">${difficultyLabels[diffLevel]}</span>`;
                }
                if (trail.activities && trail.activities.length > 0) {
                    trail.activities.forEach(a => {
                        if (html) html += `<span style="color:#d1d5db;font-size:11px;">·</span>`;
                        html += `<span style="font-size:12px;color:#6b7280;">${a.icon || ''} ${a.name}</span>`;
                    });
                }
                diffRow.innerHTML = html;
            } else if (trail.fish_species && trail.fish_species.length > 0) {
                diffRow.innerHTML = trail.fish_species
                    .map(s => `<span style="display:inline-flex;align-items:center;border-radius:999px;padding:1px 8px;font-size:11px;font-weight:500;background:#dbeafe;color:#1e40af;">${s}</span>`)
                    .join('');
            } else {
                diffRow.innerHTML = '';
            }

            // Stats: distance · elevation gain · time
            const statsEl = document.getElementById('mobile-trail-stats');
            if (isFishingLake) {
                statsEl.textContent = trail.fishing_distance_from_town ? `${trail.fishing_distance_from_town} km from Smithers` : '';
            } else {
                const parts = [];
                if (trail.distance) parts.push(`${parseFloat(trail.distance).toFixed(1)} km`);
                if (trail.elevation_gain) parts.push(`${trail.elevation_gain}m gain`);
                if (trail.estimated_time) {
                    const h = parseFloat(trail.estimated_time);
                    parts.push(`Est. ${Number.isInteger(h) ? h : h.toFixed(1)} hr`);
                }
                statsEl.textContent = parts.join(' · ');
            }

            // Action buttons
            const btnClass = 'flex-1 flex items-center justify-center gap-1.5 py-2 px-2 rounded-lg text-xs font-semibold border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-700 transition-colors';
            const actions = [];
            if (isFishingLake) {
                actions.push(`<button type="button" onclick="window.trailMap.viewRoute(${trail.id})" class="${btnClass}"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>Location</button>`);
            } else if (!isNetworkTrail) {
                actions.push(`<button type="button" onclick="window.trailMap.viewRoute(${trail.id})" class="${btnClass}"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 9m0 8V9m0 0V7"/></svg>Route</button>`);
                if (trail.route_coordinates && trail.route_coordinates.length > 1) {
                    actions.push(`<button type="button" onclick="window.trailMap.flyAlongTrail(${trail.id})" class="${btnClass}"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M13.49 5.48c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm-3.6 13.9 1-4.4 2.1 2v6h2v-7.5l-2.1-2 .6-3c1.3 1.5 3.3 2.5 5.5 2.5v-2c-1.9 0-3.5-1-4.3-2.4l-1-1.6c-.4-.6-1-1-1.7-1-.3 0-.5.1-.8.1l-5.2 2.2v4.7h2v-3.4l1.8-.7-1.6 8.1-4.9-1-.4 2 7 1.4z"/></svg>Fly Along</button>`);
                }
            }
            actions.push(`<a href="/trails/${trail.id}?from=map" class="${btnClass}"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Details</button>`);
            document.getElementById('mobile-trail-actions').innerHTML = actions.join('');

            document.getElementById('business-panel')?.classList.add('hidden');
            document.getElementById('trail-info-panel')?.classList.add('hidden');
            document.getElementById('mobile-trail-card').classList.remove('hidden');
        }

        showTrailInfo(trail) {
            // Clear ?trail= from the URL when the user moves to a different trail
            const urlTrailId = new URLSearchParams(window.location.search).get('trail');
            if (urlTrailId && String(urlTrailId) !== String(trail.id)) {
                history.replaceState(null, '', window.location.pathname);
            }

            if (this._isMobileViewport()) {
                this.showMobileTrailCard(trail);
                return;
            }


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
            const heroGradient = isFishingLake
                ? 'linear-gradient(135deg, #0369a1, #0ea5e9)'
                : 'linear-gradient(135deg, #166534, #22c55e)';
            const hero = imageUrl
                ? `<div class="biz-panel-hero"><img src="${imageUrl}" alt="${escapeHtml(trail.name)}"></div>`
                : `<div class="biz-panel-hero" style="background:${heroGradient};"><div class="biz-panel-hero-placeholder"><img src="/images/xplore-smithers-logo.png" alt="Xplore Smithers"></div></div>`;

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
                    const activityIconHtml = activity.icon_image_url
                        ? `<img src="${activity.icon_image_url}" style="width:14px;height:14px;object-fit:cover;border-radius:2px;vertical-align:middle;display:inline-block;">`
                        : (activity.icon || '');
                    metaParts.push(`<span class="biz-panel-dot">·</span><span style="font-size:12px;font-weight:600;background:${activity.color}20;color:${activity.color};padding:2px 8px;border-radius:999px;">${activityIconHtml} ${activity.name}</span>`);
                });
            }

            // Trail network badge — show parent network if this trail belongs to one
            let networkBadgeHTML = '';
            if (trail.trail_network_id) {
                const network = (this.networkData || []).find(n => n.id == trail.trail_network_id);
                if (network) {
                    networkBadgeHTML = `
                        <a href="/trail-networks/${network.slug}"
                            style="display:flex;align-items:center;gap:8px;padding:8px 12px;margin-bottom:14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;text-decoration:none;color:#166534;transition:background 0.15s;"
                            onmouseover="this.style.background='#dcfce7';" onmouseout="this.style.background='#f0fdf4';">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 9m0 8V9m0 0V7"/>
                            </svg>
                            <span style="font-size:12px;font-weight:600;flex:1;min-width:0;">
                                Part of <span style="font-weight:700;">${escapeHtml(network.name)}</span>
                            </span>
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;opacity:0.6;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>`;
                }
            }

            // Action buttons — green theme (border+shadow handled by CSS, icon/label color overrides)
            const trailActionIcon = `style="background:#16a34a;"`;
            const trailActionLabel = `style="color:#166534;"`;
            const trailActionBtn = ``;
            const actions = [];
            // Trails that belong to a network aren't drawn on the main map, so
            // View Route / Fly Along are no-ops — skip them. The network badge
            // above already links the user to the network's dedicated page.
            const isNetworkTrail = !isFishingLake && !!trail.trail_network_id;
            if (isFishingLake) {
                actions.push(`
                    <button onclick="window.trailMap.viewRoute(${trail.id})" class="biz-panel-action-btn" ${trailActionBtn}>
                        <div class="biz-panel-action-icon" ${trailActionIcon}>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <span class="biz-panel-action-label" ${trailActionLabel}>View Location</span>
                    </button>`);
            } else if (!isNetworkTrail) {
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
                <a href="/trails/${trail.id}?from=map" target="_blank" class="biz-panel-action-btn" ${trailActionBtn}>
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
                    ${networkBadgeHTML}
                    ${actions.length ? `<div class="biz-panel-actions">${actions.join('')}</div>` : ''}
                    <hr class="biz-panel-divider">
                    ${statsHTML}
                    ${trail.description ? `<p class="trail-desc-text" style="font-size:13px;color:#4b5563;line-height:1.6;margin:0 0 4px;">${escapeHtml(trail.description)}</p>` : ''}
                    ${trail.route_coordinates && trail.route_coordinates.length > 1 ? `
                    <hr class="biz-panel-divider">
                    <div style="margin-bottom:4px;">
                        <div style="font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:10px;">Elevation Profile</div>
                        <div id="panel-elev-wrap-${trail.id}" style="position:relative;background:#f9fafb;border-radius:10px;overflow:hidden;height:130px;border:1px solid #e5e7eb;">
                            <canvas id="panel-elev-canvas-${trail.id}" style="width:100%;height:100%;display:block;"></canvas>
                            <div id="panel-elev-loading-${trail.id}" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
                                <div style="width:20px;height:20px;border:2px solid #d1d5db;border-top-color:#2C5F5D;border-radius:50%;animation:spin 0.8s linear infinite;"></div>
                            </div>
                        </div>
                    </div>` : ''}
                    ${highlightsHTML}
                </div>
            `;

            panel.classList.remove('hidden');
            if (trail.route_coordinates && trail.route_coordinates.length > 1) {
                requestAnimationFrame(() => this.loadPanelElevation(trail));
            }
        }

        async loadPanelElevation(trail) {
            const canvas  = document.getElementById(`panel-elev-canvas-${trail.id}`);
            const loading = document.getElementById(`panel-elev-loading-${trail.id}`);
            if (!canvas) return;

            let coordinates = null;
            const hasElev = trail.route_coordinates[0].length >= 3;

            if (hasElev) {
                coordinates = trail.route_coordinates.map(c => [c[1], c[0], c[2]]);
            } else {
                try {
                    const res = await fetch('/api/elevation-profile', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                        body: JSON.stringify({ coordinates: trail.route_coordinates.map(c => [c[0], c[1]]) }),
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (data.geometry && data.geometry.coordinates) {
                            // API returns [lat, lng, elev] — convert to [lng, lat, elev] for GeoJSON
                            coordinates = data.geometry.coordinates.map(c => [c[1], c[0], c[2]]);
                        }
                    }
                } catch (e) { /* silent fail */ }
            }

            if (loading) loading.style.display = 'none';
            if (!coordinates || coordinates.length < 2) return;

            // Build display coords — mirror for out-and-back
            const isOutAndBack = trail.trail_type === 'out-and-back';
            const displayCoords = isOutAndBack
                ? [...coordinates, ...[...coordinates].reverse()]
                : coordinates;

            const elevations = displayCoords.map(c => c[2]).filter(e => e != null);
            if (elevations.length < 2) return;

            // Cumulative distance (km) at each display coordinate
            const toRad = d => d * Math.PI / 180;
            const distances = [0];
            for (let i = 1; i < displayCoords.length; i++) {
                const [lng1, lat1] = displayCoords[i - 1];
                const [lng2, lat2] = displayCoords[i];
                const dLat = toRad(lat2 - lat1), dLng = toRad(lng2 - lng1);
                const a = Math.sin(dLat / 2) ** 2 + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLng / 2) ** 2;
                distances.push(distances[i - 1] + 6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));
            }

            const wrap = document.getElementById(`panel-elev-wrap-${trail.id}`);
            canvas.width  = wrap ? wrap.offsetWidth  : 300;
            canvas.height = wrap ? wrap.offsetHeight : 130;

            const minE = Math.min(...elevations), maxE = Math.max(...elevations);
            const range = maxE - minE || 1;
            const PADDING_TOP = 16;

            // ── Draw chart (base + optional hover overlay) ──────────────────
            const drawChart = (hoverIdx = -1) => {
                const ctx = canvas.getContext('2d');
                const w = canvas.width, h = canvas.height;
                const drawH = h - PADDING_TOP;

                ctx.clearRect(0, 0, w, h);

                const grad = ctx.createLinearGradient(0, PADDING_TOP, 0, h);
                grad.addColorStop(0, 'rgba(44,95,93,0.18)');
                grad.addColorStop(1, 'rgba(44,95,93,0.02)');

                ctx.beginPath();
                ctx.strokeStyle = '#2C5F5D';
                ctx.lineWidth = 2;
                elevations.forEach((e, i) => {
                    const x = (i / (elevations.length - 1)) * w;
                    const y = PADDING_TOP + drawH - ((e - minE) / range) * drawH;
                    i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
                });
                ctx.stroke();
                ctx.lineTo(w, h); ctx.lineTo(0, h); ctx.closePath();
                ctx.fillStyle = grad;
                ctx.fill();

                if (hoverIdx >= 0 && hoverIdx < elevations.length) {
                    const e = elevations[hoverIdx];
                    const x = (hoverIdx / (elevations.length - 1)) * w;
                    const y = PADDING_TOP + drawH - ((e - minE) / range) * drawH;

                    // Dashed vertical line
                    ctx.beginPath();
                    ctx.strokeStyle = 'rgba(44,95,93,0.35)';
                    ctx.lineWidth = 1;
                    ctx.setLineDash([3, 3]);
                    ctx.moveTo(x, 0); ctx.lineTo(x, h);
                    ctx.stroke();
                    ctx.setLineDash([]);

                    // Circle on the line
                    ctx.beginPath();
                    ctx.arc(x, y, 5, 0, Math.PI * 2);
                    ctx.fillStyle = '#2C5F5D';
                    ctx.fill();
                    ctx.strokeStyle = '#fff';
                    ctx.lineWidth = 2;
                    ctx.stroke();

                    // Tooltip: Elevation + Distance
                    const elevLabel = 'Elevation: ' + Math.round(e) + 'm';
                    // const distLabel = 'Distance: ' + distances[hoverIdx].toFixed(1) + 'km';
                    ctx.font = 'bold 10px Inter, system-ui, sans-serif';
                    const boxW = ctx.measureText(elevLabel).width + 12;
                    const boxH = 18; // const boxH = 30;
                    let bx = x - boxW / 2;
                    bx = Math.max(2, Math.min(w - boxW - 2, bx));
                    const by = Math.max(2, y - boxH - 8);

                    ctx.fillStyle = 'rgba(17,24,39,0.82)';
                    ctx.beginPath();
                    ctx.rect(bx, by, boxW, boxH);
                    ctx.fill();

                    ctx.fillStyle = '#fff';
                    ctx.font = 'bold 10px Inter, system-ui, sans-serif';
                    ctx.fillText(elevLabel, bx + 6, by + 12);
                    // ctx.fillStyle = '#9ca3af';
                    // ctx.font = '10px Inter, system-ui, sans-serif';
                    // ctx.fillText(distLabel, bx + 6, by + 23);
                }
            };

            drawChart();


            // ── Mapbox hover point + progress line ───────────────────────────
            const HOVER_SOURCE = 'elev-hover-point';
            const HOVER_LAYER  = 'elev-hover-layer';

            const ensureHoverLayer = () => {
                if (!this.map.getSource(HOVER_SOURCE)) {
                    this.map.addSource(HOVER_SOURCE, {
                        type: 'geojson',
                        data: { type: 'FeatureCollection', features: [] },
                    });
                }
                if (!this.map.getLayer(HOVER_LAYER)) {
                    this.map.addLayer({
                        id: HOVER_LAYER,
                        type: 'circle',
                        source: HOVER_SOURCE,
                        paint: {
                            'circle-radius': 9,
                            'circle-color': '#fff',
                            'circle-stroke-width': 3,
                            'circle-stroke-color': '#2C5F5D',
                        },
                    });
                }
            };

            const setMapPoint = (lng, lat) => {
                try {
                    ensureHoverLayer();
                    this.map.getSource(HOVER_SOURCE).setData({
                        type: 'FeatureCollection',
                        features: [{ type: 'Feature', geometry: { type: 'Point', coordinates: [lng, lat] } }],
                    });
                } catch (err) {
                    console.error('[elev-scrub] setMapPoint failed:', err);
                }
            };

            const clearMapPoint = () => {
                try {
                    this.map.getSource(HOVER_SOURCE)?.setData({ type: 'FeatureCollection', features: [] });
                } catch (_) {}
                drawChart();
            };

            const getIdx = (clientX) => {
                const rect = canvas.getBoundingClientRect();
                const frac = Math.max(0, Math.min(1, (clientX - rect.left) / rect.width));
                return Math.round(frac * (elevations.length - 1));
            };

            const onScrub = (clientX) => {
                const idx = getIdx(clientX);
                drawChart(idx);
                const coord = displayCoords[idx];
                if (coord) setMapPoint(coord[0], coord[1]);
            };

            // canvas is fresh from innerHTML on every panel open — attach directly
            canvas.style.cursor = 'crosshair';
            canvas.addEventListener('mousemove', e => onScrub(e.clientX));
            canvas.addEventListener('mouseleave', clearMapPoint);
            canvas.addEventListener('touchmove', e => { e.preventDefault(); onScrub(e.touches[0].clientX); }, { passive: false });
            canvas.addEventListener('touchend', clearMapPoint);

            drawChart();
        }

        /* ─────────────────────────────────────────────────────────────────────
         * SMOOTHING DISABLED FOR TESTING (2026-05-14)
         * smoothCoordinates ran a moving-average pass over the polyline before
         * rendering, which shifted curves off the actual track. Disabled to
         * draw stored route_coordinates as-is. Restore by removing the
         * surrounding block-comment markers AND restoring the call sites at
         * buildRouteGeoJSON() and _flyAlongTrail().
         * ─────────────────────────────────────────────────────────────────────
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
        ──────────────────────────────────────────────────────────────────── */

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

        focusOnTrail(trail, { flyToTrail = false, activateLine = false } = {}) {
            if (!trail) { return; }

            // Highlight the marker
            const markerEl = document.querySelector(`.selectable-marker-el[data-trail-id="${trail.id}"]`);
            const coords = this.sanitizeCoordinates(trail.coordinates);
            if (markerEl && coords) {
                this._selectMarker(markerEl, coords[0], coords[1]);
            }

            // Activate the route line only when clicking directly on the map icon
            if (activateLine && trail.location_type !== 'fishing_lake') {
                try { this.highlightTrailRoute(trail.id, { showPanel: false }); } catch(e) {}
            }

            // Open the info panel
            this.showTrailInfo(trail);

            // Fly to start coordinates when triggered from the list
            if (flyToTrail && coords) {
                this.map.flyTo({ center: [coords[1], coords[0]], zoom: 12, duration: 800 });
            }
        }

        focusOnTrailById(trailId) {
            const trail = this.allTrails.find(t => t.id == trailId);
            if (!trail) { return; }
            this.focusOnTrail(trail, { activateLine: true });
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

        showMobileBusinessCard(business) {
            const btnClass = 'flex-1 flex items-center justify-center gap-1.5 py-2 px-2 rounded-lg text-xs font-semibold border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-700 transition-colors';
            const img = document.getElementById('mobile-trail-img');
            const placeholder = document.getElementById('mobile-trail-placeholder');
            if (business.photo_url) {
                img.src = business.photo_url;
                img.classList.remove('hidden');
                placeholder.classList.add('hidden');
            } else {
                img.classList.add('hidden');
                placeholder.classList.remove('hidden');
                placeholder.textContent = business.icon || '🏪';
                placeholder.style.background = 'linear-gradient(135deg,#1e40af,#3b82f6)';
            }
            this._setMobileHero([], business.name, `business-${business.id}`);
            document.getElementById('mobile-trail-name').textContent = business.name;
            const diffRow = document.getElementById('mobile-trail-diff-row');
            let typeHtml = `<span style="font-size:12px;font-weight:600;color:#2563eb;">${escapeHtml(business.business_type_label || '')}</span>`;
            if (business.price_range) typeHtml += `<span style="color:#d1d5db;font-size:11px;">·</span><span style="font-size:12px;color:#6b7280;">Approx ${escapeHtml(business.price_range)}</span>`;
            if (business.is_seasonal && business.season_open) typeHtml += `<span style="color:#d1d5db;font-size:11px;">·</span><span style="font-size:12px;color:#92400e;">🗓 ${escapeHtml(business.season_open)}</span>`;
            diffRow.innerHTML = typeHtml;
            document.getElementById('mobile-trail-stats').textContent = business.tagline || '';
            const actions = [];
            actions.push(`<button type="button" onclick="window.trailMap.map.flyTo({center:[${business.longitude},${business.latitude}],zoom:17,duration:800})" class="${btnClass}"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>Location</button>`);
            if (business.phone) actions.push(`<a href="tel:${business.phone}" class="${btnClass}"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>Call</a>`);
            if (business.website) actions.push(`<a href="${business.website}" target="_blank" rel="noopener" class="${btnClass}"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>Website</a>`);
            document.getElementById('mobile-trail-actions').innerHTML = actions.join('');
            document.getElementById('trail-info-panel')?.classList.add('hidden');
            document.getElementById('business-panel')?.classList.add('hidden');
            document.getElementById('mobile-trail-card').classList.remove('hidden');
        }

        showMobileFacilityCard(facility) {
            const btnClass = 'flex-1 flex items-center justify-center gap-1.5 py-2 px-2 rounded-lg text-xs font-semibold border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-700 transition-colors';
            const img = document.getElementById('mobile-trail-img');
            const placeholder = document.getElementById('mobile-trail-placeholder');
            let heroUrl = null;
            if (facility.media && facility.media.length > 0) {
                const first = facility.media.find(m => m.media_type !== 'video_url' && m.media_type !== 'video') || facility.media[0];
                heroUrl = first.url || first.thumbnail_url || null;
            }
            if (heroUrl) {
                img.src = heroUrl;
                img.classList.remove('hidden');
                placeholder.classList.add('hidden');
            } else {
                img.classList.add('hidden');
                placeholder.classList.remove('hidden');
                placeholder.textContent = facility.icon || '📍';
                placeholder.style.background = 'linear-gradient(135deg,#166534,#22c55e)';
            }
            const galleryItems = (facility.media && facility.media.length > 0)
                ? (heroUrl ? facility.media.slice(1) : facility.media)
                : [];
            this._setMobileHero(galleryItems, facility.name, facility.id);
            document.getElementById('mobile-trail-name').textContent = facility.name;
            document.getElementById('mobile-trail-diff-row').innerHTML = `<span style="font-size:12px;font-weight:600;color:#166534;">${escapeHtml(facility.facility_type_label || 'Facility')}</span>`;
            document.getElementById('mobile-trail-stats').textContent = facility.description || '';
            const actions = [`<a href="https://www.google.com/maps/search/?api=1&query=${facility.latitude},${facility.longitude}" target="_blank" rel="noopener" class="${btnClass}"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>Directions</a>`];
            document.getElementById('mobile-trail-actions').innerHTML = actions.join('');
            document.getElementById('trail-info-panel')?.classList.add('hidden');
            document.getElementById('business-panel')?.classList.add('hidden');
            document.getElementById('mobile-trail-card').classList.remove('hidden');
        }

        openBusinessPanel(business) {
            if (this._isMobileViewport()) {
                this.showMobileBusinessCard(business);
                return;
            }
            const panel = document.getElementById('business-panel');
            const content = document.getElementById('business-panel-content');
            if (!panel || !content) { return; }

            const hero = business.photo_url
                ? `<div class="biz-panel-hero"><img src="${business.photo_url}" alt="${business.name}"></div>`
                : `<div class="biz-panel-hero"><div class="biz-panel-hero-placeholder">${business.icon}</div></div>`;

            const metaParts = [`<span class="biz-panel-type">${business.business_type_label}</span>`];
            if (business.price_range) {
                metaParts.push(`<span class="biz-panel-dot">·</span><span class="biz-panel-price-badge">Approx ${escapeHtml(business.price_range)}</span>`);
            }
            if (business.is_seasonal && business.season_open) {
                metaParts.push(`<span class="biz-panel-dot">·</span><span class="biz-panel-seasonal-badge">🗓 ${business.season_open}</span>`);
            }

            const tagline = business.tagline
                ? `<p class="biz-panel-tagline">${business.tagline}</p>`
                : '';

            const description = business.description
                ? `<p class="biz-panel-description">${escapeHtml(business.description)}</p>`
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
            // View Location — flies the map to the business and (on mobile) closes the panel.
            actions.push(`
                <button type="button" onclick="window.trailMap.viewBusinessLocation(${business.id})" class="biz-panel-action-btn">
                    <div class="biz-panel-action-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <span class="biz-panel-action-label">View Location</span>
                </button>`);

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
                    ${description}
                    ${actions.length ? `<div class="biz-panel-actions">${actions.join('')}</div>` : ''}
                    ${infoRows.length ? `<hr class="biz-panel-divider">${infoRows.join('')}` : ''}
                </div>
            `;

            // Close trail panel if open
            document.getElementById('trail-info-panel')?.classList.add('hidden');

            panel.classList.remove('hidden');
        }

        viewBusinessLocation(businessId) {
            const business = (this.businessData || []).find(b => b.id == businessId);
            if (!business || business.latitude == null || business.longitude == null) return;

            // On mobile the business panel covers the map — close it so the
            // marker is actually visible after the flyTo animation. Desktop
            // keeps the panel open as a sidebar.
            if (this._isMobileViewport()) {
                this.closeBusinessPanel();
            }

            this.map.flyTo({ center: [business.longitude, business.latitude], zoom: 17 });
        }

        closeBusinessPanel() {
            document.getElementById('business-panel')?.classList.add('hidden');
            this._clearSelection();
        }

        openFacilityPanel(facility) {
            // Points of interest (facilities) are a Pro feature.
            if (!window.xsIsPro()) { window.xsRequirePro('poi'); return; }
            if (this._isMobileViewport()) {
                this.showMobileFacilityCard(facility);
                return;
            }
            const panel = document.getElementById('business-panel');
            const content = document.getElementById('business-panel-content');
            if (!panel || !content) { return; }

            // Hero: first photo if available, otherwise Xplore Smithers logo on a green gradient
            let heroUrl = null;
            if (facility.media && facility.media.length > 0) {
                const firstPhoto = facility.media.find(m => m.media_type !== 'video_url' && m.media_type !== 'video') || facility.media[0];
                heroUrl = firstPhoto.url || firstPhoto.thumbnail_url || null;
            }
            const hero = heroUrl
                ? `<div class="biz-panel-hero"><img src="${heroUrl}" alt="${escapeHtml(facility.name)}"></div>`
                : `<div class="biz-panel-hero" style="background:linear-gradient(135deg,#166534,#22c55e);"><div class="biz-panel-hero-placeholder"><img src="/images/xplore-smithers-logo.png" alt="Xplore Smithers"></div></div>`;

            const meta = `<span class="biz-panel-type">${facility.icon || '📍'} ${escapeHtml(facility.facility_type_label || 'Facility')}</span>`;

            const actions = `<div class="biz-panel-actions">
                <a href="https://www.google.com/maps/search/?api=1&query=${facility.latitude},${facility.longitude}" target="_blank" rel="noopener" class="biz-panel-action-btn">
                    <div class="biz-panel-action-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <span class="biz-panel-action-label">Directions</span>
                </a>
            </div>`;

            // Media gallery (skip the first item if it's already the hero)
            let mediaHTML = '';
            if (facility.media && facility.media.length > 0) {
                const skipFirst = !!heroUrl;
                const galleryItems = skipFirst ? facility.media.slice(1) : facility.media;
                if (galleryItems.length > 0) {
                    mediaHTML = `<hr class="biz-panel-divider">
                        <div class="facility-media-gallery" style="border-top:none;margin-top:0;padding-top:0;">
                            <p class="facility-media-count">${galleryItems.length} more ${galleryItems.length === 1 ? 'photo/video' : 'photos/videos'}</p>
                            <div class="facility-media-grid">`;
                    galleryItems.slice(0, 4).forEach((media, idx) => {
                        const realIndex = skipFirst ? idx + 1 : idx;
                        const isVideo = media.media_type === 'video_url' || media.media_type === 'video';
                        const thumbnailUrl = media.thumbnail_url || media.url;
                        const remaining = galleryItems.length - 4;
                        const overlay = (idx === 3 && remaining > 0) ? `<div class="facility-media-overlay">+${remaining} more</div>` : '';
                        const videoBadge = isVideo ? '<div class="facility-video-badge">▶</div>' : '';
                        mediaHTML += `<div class="facility-media-item" onclick="openFacilityMediaModal(${facility.id}, ${realIndex})"><img src="${thumbnailUrl}" class="facility-media-thumbnail">${overlay}${videoBadge}</div>`;
                    });
                    mediaHTML += `</div></div>`;
                }
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
                    <h2 class="biz-panel-name">${escapeHtml(facility.name)}</h2>
                    <div class="biz-panel-meta">${meta}</div>
                    ${facility.description ? `<p class="mobile-hide-desc" style="font-size:13px;color:#4b5563;line-height:1.6;margin:0 0 16px;">${escapeHtml(facility.description)}</p>` : ''}
                    ${actions}
                    ${mediaHTML}
                </div>
            `;

            document.getElementById('trail-info-panel')?.classList.add('hidden');
            panel.classList.remove('hidden');
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
                            this.focusOnTrail(trail, { flyToTrail: true, activateLine: true });
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
                    const el = this._createMarkerEl(facility.icon || '📍', facility.icon_image_url || null);

                    // Cache the full media list so the modal carousel can navigate it
                    if (facility.media && facility.media.length) {
                        window._facilityMediaCache[facility.id] = {
                            name: facility.name,
                            media: facility.media,
                        };
                    }

                    el.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this._selectMarker(el, facility.latitude, facility.longitude);
                        this.openFacilityPanel(facility);
                    });

                    const marker = new mapboxgl.Marker({ element: el, anchor: 'center' })
                        .setLngLat([facility.longitude, facility.latitude]);

                    if (this.showFacilities) { marker.addTo(this.map); }
                    this.facilityMarkers.push(marker);
                });
            } catch (error) {
                console.error('Error loading facilities:', error);
            }
        }

        renderFacilityMarkers() {
            (this.facilityMarkers || []).forEach(m => {
                if (this.showFacilities) {
                    m.addTo(this.map);
                } else {
                    m.remove();
                }
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

            const season = this.currentSeason;

            // When only Map Layer filters are active (businesses/facilities), hide all network markers
            const hasTrailFilters = advancedFilters.activities.length > 0 ||
                                    advancedFilters.features.length > 0 ||
                                    advancedFilters.trailType ||
                                    advancedFilters.duration ||
                                    advancedFilters.elevation;
            if (advancedFilters.layers.length > 0 && !hasTrailFilters) {
                return;
            }

            (this.networkData || []).forEach(network => {
                // Determine effective season — explicit season field wins,
                // otherwise infer from network type so nordic/downhill = winter,
                // mountain biking = summer.
                let networkSeason = network.season || 'both';
                if (networkSeason === 'both') {
                    const type = (network.type || '').toLowerCase();
                    if (type.includes('nordic') || type.includes('downhill') || type.includes('ski')) {
                        networkSeason = 'winter';
                    } else if (type.includes('mountain_bik') || type.includes('mountain-bik') || type === 'mountain_biking') {
                        networkSeason = 'summer';
                    }
                }
                if (networkSeason !== 'both' && networkSeason !== season) { return; }

                // When activities filter is active, hide network unless its type matches a selected activity.
                // Normalize to hyphen-separated so mountain_biking matches mountain-biking, etc.
                if (advancedFilters.activities.length > 0) {
                    const normalizedNetworkType = (network.type || '').toLowerCase().replace(/_/g, '-');
                    const matchesActivity = advancedFilters.activities.some(a => normalizedNetworkType.includes(a) || a.includes(normalizedNetworkType));
                    if (!matchesActivity) { return; }
                }

                // When features filter is active (but no activities), hide network markers
                // since trail networks don't have per-feature highlights
                if (advancedFilters.features.length > 0 && advancedFilters.activities.length === 0) {
                    return;
                }

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
            if (this._isMobileViewport()) {
                this.showMobileNetworkCard(network);
                return;
            }
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

                return `
                    <div class="trail-list-card" data-location-type="${trail.location_type}" ${accentStyle} onclick="window.trailMap.focusOnTrailById(${trail.id})">
                        ${imageUrl ?
                            `<img src="${imageUrl}" alt="${trail.name}" class="trail-list-image">` :
                            `<div class="trail-list-image-placeholder" style="background: ${trail.location_type === 'fishing_lake' ? 'linear-gradient(135deg, #0369a1, #0ea5e9)' : 'linear-gradient(135deg, #166534, #22c55e)'};">
                                <img src="/images/xplore-smithers-logo.png" alt="Xplore Smithers">
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
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium" style="background-color: ${activity.color}20; color: ${activity.color};">
                                            ${activity.icon_image_url ? `<img src="${activity.icon_image_url}" style="width:12px;height:12px;object-fit:cover;border-radius:2px;display:inline-block;">` : (activity.icon || '')} ${activity.name}
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
                .map(c => {
                    // Preserve elevation as 3rd element when present — used for the gradient
                    if (Array.isArray(c) && c.length >= 2 && isFinite(c[0]) && isFinite(c[1])) {
                        return c.length >= 3 && isFinite(c[2]) ? [c[0], c[1], c[2]] : [c[0], c[1], null];
                    }
                    const s = this.sanitizeCoordinates(c);
                    return s ? [s[0], s[1], null] : null;
                })
                .filter(c => c !== null);

            if (coords.length < 2) {
                this.showToast('Not enough route data for animation.');
                return;
            }

            // Smoothing disabled for testing — animate over raw coords
            const smoothed = coords;

            // Remember which trail is flying so we can re-highlight it on stop
            this._flyTrailId = trailId;

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

            // Hide the floating search bar and filter bar on mobile so the animation has a clear stage
            document.getElementById('mobile-search-bar')?.classList.add('hidden');
            document.getElementById('filter-bar')?.classList.add('max-md:hidden');

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

                if (this._hikerMarker) { this._hikerMarker.remove(); this._hikerMarker = null; }

                // Switch trail to green fly-along style
                this._activateFlyTrailLayers(trailId, smoothed);

                // ── Phase 1: top-down 2D overview (pitch 0°) ─────────────────
                const lngs = smoothed.map(c => c[1]);
                const lats = smoothed.map(c => c[0]);
                this.map.fitBounds(
                    [[Math.min(...lngs), Math.min(...lats)], [Math.max(...lngs), Math.max(...lats)]],
                    { padding: 80, maxZoom: 13, pitch: 0, bearing: 0, duration: 800 }
                );

                // ── Phase 2: tilt into 3D, zoom to trail start ────────────────
                // flyTo pitches from 0° → 60° and zooms in — the "drop into terrain" moment
                this._flyTimeout = setTimeout(() => {
                    this._flyTimeout = null;
                    if (!this._isFlying) return;

                    const initialBearing = this._getBearing(
                        smoothed[0],
                        smoothed[Math.min(30, smoothed.length - 1)]
                    );
                    this.map.flyTo({
                        center:   [smoothed[0][1], smoothed[0][0]],
                        zoom:     15.5,
                        pitch:    60,
                        bearing:  initialBearing,
                        duration: 1600,
                        easing:   t => t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t,
                    });

                    // ── Phase 3: trail-following loop starts after tilt completes
                    this._flyTimeout = setTimeout(() => {
                        this._flyTimeout = null;
                        if (!this._isFlying) return;
                        this._animateAlongTrail(smoothed, trail.elevation_gain);
                    }, 1800);
                }, 1000);
            };

            if (needsStyleSwitch) {
                // style.load fires as soon as the style JSON is applied — much faster than
                // 'idle' which waits for every satellite tile to finish rendering (1-3s extra)
                this.map.once('style.load', beginAnimation);
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

        _animateAlongTrail(rawCoords, trailElevGain = 0) {
            const coords = rawCoords
                .filter(c => Array.isArray(c) && c.length >= 2 && isFinite(c[0]) && isFinite(c[1]))
                .map(c => [c[0], c[1], (c.length >= 3 && isFinite(c[2])) ? c[2] : null]);

            if (coords.length < 2) {
                this.stopFlyAnimation();
                return;
            }

            const last = coords.length - 1;
            const toRad = d => d * Math.PI / 180;
            const haversine = (a, b) => {
                const R = 6371000;
                const dLat = toRad(b[0] - a[0]);
                const dLng = toRad(b[1] - a[1]);
                const s = Math.sin(dLat / 2) ** 2 + Math.cos(toRad(a[0])) * Math.cos(toRad(b[0])) * Math.sin(dLng / 2) ** 2;
                return 2 * R * Math.atan2(Math.sqrt(s), Math.sqrt(1 - s));
            };

            // Pre-compute cumulative distances and elevation gain per waypoint
            const cumDist     = [0];
            const cumElevGain = [0];
            for (let i = 0; i < last; i++) {
                cumDist.push(cumDist[i] + haversine(coords[i], coords[i + 1]));
                const rise = (coords[i + 1][2] != null && coords[i][2] != null)
                    ? Math.max(0, coords[i + 1][2] - coords[i][2])
                    : 0;
                cumElevGain.push(cumElevGain[i] + rise);
            }
            const totalDist = cumDist[last];

            const SPEED_MS    = 300;
            const DURATION_MS = (totalDist / SPEED_MS) * 1000;
            const flyZoom     = 15.5;
            const startTime   = performance.now();

            // Binary-search helper — returns index lo such that cumDist[lo] <= d
            const findSeg = (d) => {
                d = Math.max(0, Math.min(d, totalDist));
                let lo = 0, hi = last;
                while (lo < hi - 1) {
                    const mid = (lo + hi) >> 1;
                    if (cumDist[mid] <= d) { lo = mid; } else { hi = mid; }
                }
                return lo;
            };

            // Returns [lat, lng] at exact distance d along the trail
            const posAtDist = (d) => {
                const lo = findSeg(d);
                const segLen = cumDist[lo + 1] - cumDist[lo];
                const t = segLen > 0 ? (Math.max(0, Math.min(d, totalDist)) - cumDist[lo]) / segLen : 0;
                return [
                    coords[lo][0] + (coords[lo + 1][0] - coords[lo][0]) * t,
                    coords[lo][1] + (coords[lo + 1][1] - coords[lo][1]) * t,
                ];
            };

            // Returns cumulative elevation gain at distance d.
            // If per-point elevation data exists use it; otherwise fall back to
            // distributing the trail's known total elevation_gain proportionally
            // so the counter always shows a meaningful number.
            const totalElevGain = cumElevGain[last];
            const knownGain     = parseFloat(trailElevGain) || 0;
            const hasElevData   = totalElevGain > 0;

            const elevGainAtDist = hasElevData
                ? (d) => {
                    const lo     = findSeg(d);
                    const segLen = cumDist[lo + 1] - cumDist[lo];
                    const t      = segLen > 0 ? (Math.max(0, Math.min(d, totalDist)) - cumDist[lo]) / segLen : 0;
                    const segRise = (coords[lo + 1][2] != null && coords[lo][2] != null)
                        ? Math.max(0, coords[lo + 1][2] - coords[lo][2]) : 0;
                    return cumElevGain[lo] + segRise * t;
                }
                : (d) => (totalDist > 0 ? (d / totalDist) * knownGain : 0);

            // ── Phase 3 camera state ─────────────────────────────────────────
            // Position, bearing and zoom are all smoothed so zigzags produce no
            // visible camera movement — only genuine long turns register.
            const initPos  = posAtDist(0);
            let smoothLat  = initPos[0];
            let smoothLng  = initPos[1];
            let smoothBear = this._getBearing(coords[0], coords[Math.min(30, last)]);
            let smoothZoom = flyZoom;
            let prevTime   = null;
            const POS_HL     = 700;  // ms — heavy position smoothing, ultra-fluid camera glide
            const BEARING_HL = 6000; // ms — barely turns, only major bends register
            const ZOOM_HL    = 2500; // ms — imperceptibly slow zoom drift

            const distEl = document.getElementById('fly-stat-dist');
            const elevEl = document.getElementById('fly-stat-elev');

            const animate = (now) => {
                if (!this._isFlying) return;

                const dt            = prevTime !== null ? Math.min(now - prevTime, 100) : 16;
                prevTime            = now;
                const progress      = Math.max(0, Math.min((now - startTime) / DURATION_MS, 1));
                const distTravelled = progress * totalDist;

                // Exact trail tip (used for stats + progress fill)
                const [lat, lng] = posAtDist(distTravelled);

                // Smooth camera position — filters out rapid left/right zigzag panning
                const posAlpha = 1 - Math.pow(0.5, dt / POS_HL);
                smoothLat += (lat - smoothLat) * posAlpha;
                smoothLng += (lng - smoothLng) * posAlpha;

                // Bearing from 500 m behind → 1000 m ahead (1500 m window).
                // Zigzag legs are tiny vs this span so they don't affect the angle.
                const [behindLat, behindLng] = posAtDist(Math.max(0, distTravelled - 500));
                const [aheadLat,  aheadLng]  = posAtDist(distTravelled + 1000);
                const targetBear = this._getBearing([behindLat, behindLng], [aheadLat, aheadLng]);

                // Extremely slow bearing lerp — only genuine long bends register
                const bearAlpha = 1 - Math.pow(0.5, dt / BEARING_HL);
                const bearDelta = ((targetBear - smoothBear + 540) % 360) - 180;
                smoothBear = (smoothBear + bearDelta * bearAlpha + 360) % 360;

                // Zoom variation by elevation grade — smoothed so it never snaps
                const lo       = findSeg(distTravelled);
                const hi       = findSeg(Math.min(distTravelled + 150, totalDist));
                const rise     = (coords[hi][2] != null && coords[lo][2] != null)
                    ? Math.max(0, coords[hi][2] - coords[lo][2]) : 0;
                const run      = cumDist[hi] - cumDist[lo];
                const grade    = run > 0 ? (rise / run) * 100 : 0;
                const targetZoom = grade > 8 ? 16.5 : grade < 3 ? 14.5 : flyZoom;
                const zoomAlpha  = 1 - Math.pow(0.5, dt / ZOOM_HL);
                smoothZoom      += (targetZoom - smoothZoom) * zoomAlpha;

                this.map.jumpTo({
                    center:  [smoothLng, smoothLat],
                    bearing: smoothBear,
                    pitch:   55,
                    zoom:    smoothZoom,
                });

                // Progress fill: base trims back (unwalked), glow+progress grow forward (walked)
                if (this.map.getLayer('fly-draw-progress')) {
                    this.map.setPaintProperty('fly-draw-base',     'line-trim-offset', [0, progress]);
                    this.map.setPaintProperty('fly-draw-glow',     'line-trim-offset', [progress, 1]);
                    this.map.setPaintProperty('fly-draw-progress', 'line-trim-offset', [progress, 1]);
                }

                // Live stats counter
                if (distEl) distEl.textContent = (distTravelled / 1000).toFixed(1);
                if (elevEl) elevEl.textContent  = Math.round(elevGainAtDist(distTravelled));

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

            // Reset the fly button before the panel rebuild below — showTrailInfo()
            // renders its own fresh "Fly Along" button, and resetting first keeps it
            // from being clobbered with a mismatched style afterward.
            this._updateFlyButton(false);

            // Restore original trail colors, then re-highlight the trail line and reopen its info panel
            this._deactivateFlyTrailLayers();
            let flownTrail = null;
            if (this._flyTrailId != null) {
                const flownTrailId = this._flyTrailId;
                this._flyTrailId = null;
                try {
                    flownTrail = this.allTrails.find(t => t.id == flownTrailId);
                    this.highlightTrailRoute(flownTrailId, { showPanel: true });
                } catch (err) {
                    console.error('[fly-along] failed to reopen trail panel after stop:', err);
                }
            }

            const stopBtn = document.getElementById('fly-stop-overlay-btn');
            if (stopBtn) { stopBtn.classList.add('hidden'); }

            // Restore the floating search bar and filter bar on mobile
            document.getElementById('mobile-search-bar')?.classList.remove('hidden');
            document.getElementById('filter-bar')?.classList.remove('max-md:hidden');

            // Restore the trail list if we were the ones that collapsed it
            if (this._flyAutoCollapsed && window.trailListPanelApi) {
                window.trailListPanelApi.expand();
            }
            this._flyAutoCollapsed = false;

            // Stay on satellite after the flyover — do not restore the previous map type
            this._preFlyMapType = null;

            // Settle the camera back onto the trail (top-down, fitted to its bounds)
            // rather than leaving it wherever the flyover ended.
            const sanitized = (flownTrail?.route_coordinates || [])
                .map(c => this.sanitizeCoordinates(c)).filter(c => c !== null);
            if (sanitized.length > 0) {
                const lngs = sanitized.map(c => c[1]);
                const lats = sanitized.map(c => c[0]);
                this.map.fitBounds([
                    [Math.min(...lngs), Math.min(...lats)],
                    [Math.max(...lngs), Math.max(...lats)]
                ], { padding: 60, maxZoom: 13, pitch: 0, bearing: 0, duration: 1000 });
            } else {
                this.map.easeTo({ pitch: 0, bearing: 0, duration: 1000 });
            }
        }

        _activateFlyTrailLayers(trailId, coords) {
            if (!this.map.getSource('fly-draw')) { return; }

            // Load this trail's geometry into the dedicated lineMetrics source
            const mapboxCoords = coords.map(c =>
                c[2] != null ? [c[1], c[0], c[2]] : [c[1], c[0]]
            );
            this.map.getSource('fly-draw').setData({
                type: 'FeatureCollection',
                features: [{ type: 'Feature', properties: {}, geometry: { type: 'LineString', coordinates: mapboxCoords } }],
            });

            // Solid blue for the walking progress line
            const blueGradient = ['interpolate', ['linear'], ['line-progress'], 0, '#3b82f6', 1, '#3b82f6'];
            const blueGlow     = ['interpolate', ['linear'], ['line-progress'], 0, '#60a5fa', 1, '#60a5fa'];
            if (this.map.getLayer('fly-draw-progress')) {
                this.map.setPaintProperty('fly-draw-progress', 'line-gradient', blueGradient);
                this.map.setPaintProperty('fly-draw-glow',     'line-gradient', blueGlow);
            }

            // Reset trim offsets: base shows full trail, progress/glow start at nothing
            this.map.setPaintProperty('fly-draw-base',     'line-trim-offset', [0, 0]);
            this.map.setPaintProperty('fly-draw-glow',     'line-trim-offset', [0, 1]);
            this.map.setPaintProperty('fly-draw-progress', 'line-trim-offset', [0, 1]);
            this.map.setPaintProperty('fly-draw-base',     'line-opacity', 0.35);
            this.map.setPaintProperty('fly-draw-glow',     'line-opacity', 0.3);
            this.map.setPaintProperty('fly-draw-progress', 'line-opacity', 1);

            // Hide original trail layers
            this.map.setPaintProperty('trail-routes-line',    'line-opacity', 0);
            this.map.setPaintProperty('trail-routes-outline', 'line-opacity', 0);
            this.map.setPaintProperty('trail-routes-arrows',  'icon-opacity', 0);

            // Reduce terrain exaggeration — high values amplify camera altitude bounce on steep terrain
            if (this.map.getTerrain()) {
                this.map.setTerrain({ source: 'mapbox-dem', exaggeration: 0.5 });
            }

            // Reset and show stats overlay
            const distEl = document.getElementById('fly-stat-dist');
            const elevEl = document.getElementById('fly-stat-elev');
            if (distEl) distEl.textContent = '0.0';
            if (elevEl) elevEl.textContent = '0';
            document.getElementById('fly-stats-overlay')?.classList.remove('hidden');
        }

        _deactivateFlyTrailLayers() {
            if (!this.map.getSource('fly-draw')) { return; }
            ['fly-draw-base', 'fly-draw-glow', 'fly-draw-progress'].forEach(id => {
                if (this.map.getLayer(id)) {
                    this.map.setPaintProperty(id, 'line-opacity', 0);
                }
            });
            // Clear the source so no stale geometry lingers
            this.map.getSource('fly-draw').setData({ type: 'FeatureCollection', features: [] });

            // Restore original layer opacity expressions
            const lineOpacity = ['case', ['boolean', ['feature-state', 'selected'], false], 1, 0];
            this.map.setPaintProperty('trail-routes-line',    'line-opacity', lineOpacity);
            this.map.setPaintProperty('trail-routes-outline', 'line-opacity', lineOpacity);
            this.map.setPaintProperty('trail-routes-arrows',  'icon-opacity', lineOpacity);

            document.getElementById('fly-stats-overlay')?.classList.add('hidden');

            // Restore terrain exaggeration to normal
            if (this._is3D && this.map.getTerrain()) {
                this.map.setTerrain({ source: 'mapbox-dem', exaggeration: 1.5 });
            }
        }

        _buildElevationGradient(coords) {
            // Elevation grade → color: green (flat) → cyan (moderate) → blue (steep)
            const gradeColor = (pct) => {
                if (pct < 5)  return '#22c55e'; // green — gentle / flat
                if (pct < 12) return '#00cfff'; // cyan  — moderate climb
                return '#3b82f6';               // blue  — steep climb
            };

            const hasElev = coords.some(c => c[2] != null && isFinite(c[2]));
            if (!hasElev) {
                return ['interpolate', ['linear'], ['line-progress'], 0, '#22c55e', 1, '#3b82f6'];
            }

            const toRad = d => d * Math.PI / 180;
            const haversine = (a, b) => {
                const R = 6371000;
                const dLat = toRad(b[0] - a[0]);
                const dLng = toRad(b[1] - a[1]);
                const s = Math.sin(dLat / 2) ** 2 + Math.cos(toRad(a[0])) * Math.cos(toRad(b[0])) * Math.sin(dLng / 2) ** 2;
                return 2 * R * Math.atan2(Math.sqrt(s), Math.sqrt(1 - s));
            };

            const cumDist = [0];
            for (let i = 0; i < coords.length - 1; i++) {
                cumDist.push(cumDist[i] + haversine(coords[i], coords[i + 1]));
            }
            const totalDist = cumDist[coords.length - 1];
            if (totalDist === 0) {
                return ['interpolate', ['linear'], ['line-progress'], 0, '#22c55e', 1, '#3b82f6'];
            }

            // Build gradient stops — smooth grade over a ±5-point window to avoid flickering
            const WINDOW = 5;
            const stops = [];
            for (let i = 0; i < coords.length; i++) {
                const lo = Math.max(0, i - WINDOW);
                const hi = Math.min(coords.length - 1, i + WINDOW);
                let grade = 0;
                if (hi > lo && coords[hi][2] != null && coords[lo][2] != null) {
                    const rise = Math.max(0, coords[hi][2] - coords[lo][2]);
                    const run  = cumDist[hi] - cumDist[lo];
                    if (run > 0) grade = (rise / run) * 100;
                }
                const progress = cumDist[i] / totalDist;
                // Clamp to [0, 1] and deduplicate consecutive identical stops
                const p = Math.max(0, Math.min(1, progress));
                const color = gradeColor(grade);
                if (stops.length === 0 || stops[stops.length - 1] !== color || p === 1) {
                    if (stops.length > 0 && stops[stops.length - 2] === p) {
                        stops[stops.length - 1] = color; // overwrite same-progress duplicate
                    } else {
                        stops.push(p, color);
                    }
                }
            }
            // Guarantee first stop at 0 and last stop at 1 (interpolate requires them)
            if (stops[0] !== 0) stops.unshift(0, '#22c55e');
            if (stops[stops.length - 2] !== 1) stops.push(1, stops[stops.length - 1]);

            return ['interpolate', ['linear'], ['line-progress'], ...stops];
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
        document.getElementById('trail-info-panel')?.classList.add('hidden');
        closeMobileTrailCard();
        // Clear elevation hover marker from map
        try {
            window.trailMap?.map?.getSource('elev-hover-point')?.setData({ type: 'FeatureCollection', features: [] });
        } catch (_) {}
    }

    function closeMobileTrailCard() {
        document.getElementById('mobile-trail-card')?.classList.add('hidden');
        if (window.trailMap) {
            window.trailMap._clearSelection();
            window.trailMap._mobileCardTrailId = null;
        }
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

        // Deselect trail when clicking outside the map and outside interactive UI elements
        document.addEventListener('click', (e) => {
            if (!window.trailMap) return;

            // Ignore clicks inside the map canvas (handled by the Mapbox click handler)
            if (document.getElementById('main-map')?.contains(e.target)) return;
            // Ignore clicks on trail/business detail panels
            if (document.getElementById('trail-info-panel')?.contains(e.target)) return;
            if (document.getElementById('business-panel')?.contains(e.target)) return;
            // Ignore clicks on trail/business/network list cards (they open details)
            if (e.target.closest('.trail-list-card, .business-list-card')) return;
            // Ignore clicks on filter modals and chip bars
            if (e.target.closest('#all-filters-modal, #filter-bar')) return;
            // Ignore clicks on filter buttons themselves
            if (e.target.closest('.season-btn, .dist-chip, .diff-chip, .location-filter-btn, #all-filters-btn')) return;

            window.trailMap.closeAllPanels();
        });

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

        // Tour data cache
        let toursCache = null;

        async function loadToursData() {
            if (toursCache) { return toursCache; }
            try {
                const res = await fetch('/api/tours');
                toursCache = await res.json();
            } catch (e) {
                toursCache = [];
            }
            return toursCache;
        }

        function renderTourCards(tours) {
            const container = document.getElementById('trail-cards');
            const countEl = document.getElementById('trail-count');
            if (countEl) { countEl.textContent = tours.length; }
            if (!container) { return; }

            if (tours.length === 0) {
                container.innerHTML = `<div class="text-center py-8 text-gray-500"><p class="font-medium">No tours available</p></div>`;
                return;
            }

            container.innerHTML = tours.map(tour => `
                <a href="/tours/${tour.slug}" class="trail-list-card flex gap-3 items-center no-underline">
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg overflow-hidden bg-gradient-to-br from-blue-400 to-green-500">
                        ${tour.cover_image_url
                            ? `<img src="${escapeHtml(tour.cover_image_url)}" alt="${escapeHtml(tour.title)}" class="w-full h-full object-cover">`
                            : `<div class="w-full h-full flex items-center justify-center text-white text-lg">🗺️</div>`}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-sm text-gray-900 truncate">${escapeHtml(tour.title)}</div>
                        <div class="text-xs text-gray-500">${tour.stop_count} stop${tour.stop_count !== 1 ? 's' : ''}${tour.tagline ? ' · ' + escapeHtml(tour.tagline) : ''}</div>
                    </div>
                    <svg class="h-4 w-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            `).join('');
        }

        function refreshList() {
            const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
            if (!window.trailMap) { return; }

            const allTrails = window.trailMap.allTrails || window.trailMap.currentTrails || [];
            const allBusinesses = window.trailMap.businessData || [];

            if (activeLocationFilter === 'tour') {
                loadToursData().then(tours => {
                    const filtered = searchTerm
                        ? tours.filter(t => t.title.toLowerCase().includes(searchTerm))
                        : tours;
                    renderTourCards(filtered);
                });
            } else if (activeLocationFilter === 'business') {
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

        // Location type filter buttons — only narrow the search results panel.
        // Map markers are always shown regardless of which tab is active.
        document.querySelectorAll('.location-filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                activeLocationFilter = this.dataset.locationFilter;
                if (window.trailMap) {
                    window.trailMap.activeLocationFilter = activeLocationFilter;
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
                if (filter === 'tour') {
                    loadToursData().then(tours => {
                        const filtered = q ? tours.filter(t => t.title.toLowerCase().includes(q)) : tours;
                        const inner = document.getElementById('mobile-search-results-inner');
                        if (!inner) { return; }
                        if (filtered.length === 0) {
                            inner.innerHTML = '<div class="text-center py-8 text-gray-500"><p class="font-medium">No tours found</p></div>';
                            return;
                        }
                        inner.innerHTML = filtered.map(tour => `
                            <a href="/tours/${escapeHtml(tour.slug)}" class="trail-list-card flex gap-3 items-center no-underline">
                                <div class="flex-shrink-0 w-10 h-10 rounded-lg overflow-hidden bg-gradient-to-br from-blue-400 to-green-500">
                                    ${tour.cover_image_url
                                        ? `<img src="${escapeHtml(tour.cover_image_url)}" class="w-full h-full object-cover">`
                                        : `<div class="w-full h-full flex items-center justify-center text-lg">🗺️</div>`}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-sm text-gray-900 truncate">${escapeHtml(tour.title)}</div>
                                    <div class="text-xs text-gray-500">${tour.stop_count} stop${tour.stop_count !== 1 ? 's' : ''}</div>
                                </div>
                            </a>
                        `).join('');
                    });
                    return;
                } else if (filter === 'business') {
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
                    trailMap.focusOnTrail(trail, { flyToTrail: true, activateLine: true });
                }
            }, 1500);
        }
    }
</script>
@endpush
@endsection