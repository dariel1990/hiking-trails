@extends('layouts.admin')

@section('title', 'Edit Trail - ' . $trail->name)
@section('page-title', 'Edit Trail')

@section('content')
<div class="max-w-6xl mx-auto space-y-8">
    <!-- Header -->
    <div class="space-y-2">
        <div class="flex items-center gap-2 text-sm text-muted-foreground">
            <a href="{{ route('admin.trails.index') }}" class="hover:text-foreground transition-colors">Trails</a>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('admin.trails.show', $trail) }}" class="hover:text-foreground transition-colors">{{ $trail->name }}</a>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>Edit</span>
        </div>
        <div class="space-y-1">
            <h1 class="text-3xl font-bold tracking-tight">Edit Trail</h1>
            <p class="text-muted-foreground">Update trail information and route details.</p>
        </div>
    </div>

    <form action="{{ route('admin.trails.update', $trail) }}" method="POST" enctype="multipart/form-data" class="space-y-8" onsubmit="return window.trailBuilder.validateBeforeSubmit()">
        @csrf
        @method('PUT')

        <!-- Hidden inputs for tracking deletions -->
        <input type="hidden" name="deleted_photos" id="deleted-photos-input" value="">
        <input type="hidden" name="deleted_features" id="deleted-features-input" value="">

        <!-- Hidden input for video URLs (permanent in DOM) -->
        <input type="hidden" name="trail_video_urls_json" id="trail-video-urls-json" value="[]">

        <!-- Validation Errors Display -->
        @if ($errors->any())
            <div class="rounded-lg border-2 border-red-300 bg-red-50 p-6">
                <div class="flex items-start gap-3">
                    <svg class="h-6 w-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-red-900 mb-2">Please fix the following errors:</h3>
                        <ul class="list-disc list-inside space-y-1 text-sm text-red-800">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif
        
        <!-- Basic Information -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 space-y-6">
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold">Basic Information</h3>
                    <p class="text-sm text-muted-foreground">Essential details about the trail</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Trail Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" required value="{{ old('name', $trail->name) }}"
                               placeholder="Enter trail name"
                               class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 @error('name') border-red-300 @enderror">
                        @error('name')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Location
                        </label>
                        <input type="text" name="location" value="{{ old('location', $trail->location) }}"
                               placeholder="e.g., North Vancouver, BC"
                               class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 @error('location') border-red-300 @enderror">
                        @error('location')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2 space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Description <span class="text-red-500">*</span>
                        </label>
                        <textarea name="description" rows="4" required
                                  placeholder="Describe the trail, its features, and what hikers can expect..."
                                  class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 @error('description') border-red-300 @enderror">{{ old('description', $trail->description) }}</textarea>
                        @error('description')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Types -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 space-y-6">
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold">Activities & Features</h3>
                    <p class="text-sm text-muted-foreground">What activities are available on this trail?</p>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @php 
                        $trailActivities = $trail->activities->pluck('slug')->toArray();
                    @endphp
                    
                    <label class="flex items-start gap-3 p-4 rounded-lg border border-input bg-background hover:bg-accent hover:text-accent-foreground cursor-pointer transition-colors">
                        <input type="checkbox" name="activities[]" value="hiking" 
                            {{ in_array('hiking', old('activities', $trailActivities)) ? 'checked' : '' }}
                            class="mt-1 h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                        <div class="space-y-1">
                            <div class="text-sm font-medium">Hiking</div>
                            <div class="text-xs text-muted-foreground">Walking trails</div>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-4 rounded-lg border border-input bg-background hover:bg-accent hover:text-accent-foreground cursor-pointer transition-colors">
                        <input type="checkbox" name="activities[]" value="fishing" 
                            {{ in_array('fishing', old('activities', $trailActivities)) ? 'checked' : '' }}
                            class="mt-1 h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                        <div class="space-y-1">
                            <div class="text-sm font-medium">Fishing</div>
                            <div class="text-xs text-muted-foreground">Fishing spots</div>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-4 rounded-lg border border-input bg-background hover:bg-accent hover:text-accent-foreground cursor-pointer transition-colors">
                        <input type="checkbox" name="activities[]" value="camping" 
                            {{ in_array('camping', old('activities', $trailActivities)) ? 'checked' : '' }}
                            class="mt-1 h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                        <div class="space-y-1">
                            <div class="text-sm font-medium">Camping</div>
                            <div class="text-xs text-muted-foreground">Camping areas</div>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-4 rounded-lg border border-input bg-background hover:bg-accent hover:text-accent-foreground cursor-pointer transition-colors">
                        <input type="checkbox" name="activities[]" value="viewpoint" 
                            {{ in_array('viewpoint', old('activities', $trailActivities)) ? 'checked' : '' }}
                            class="mt-1 h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                        <div class="space-y-1">
                            <div class="text-sm font-medium">Viewpoints</div>
                            <div class="text-xs text-muted-foreground">Scenic overlooks</div>
                        </div>
                    </label>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                        Activity Notes
                    </label>
                    <textarea name="activity_notes" rows="2" 
                            placeholder="Additional notes about activities available on this trail..."
                            class="flex min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">{{ old('activity_notes', $trail->activities->first()->pivot->activity_notes ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Interactive Trail Builder with Tabs -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <!-- Header -->
            <div class="p-6 space-y-2 border-b">
                <h3 class="text-lg font-semibold">Interactive Trail Builder</h3>
                <p class="text-sm text-muted-foreground">Click on the map to create waypoints. Routes will automatically snap to walking paths and trails.</p>
            </div>
            
            <!-- Two Column Layout: Fixed Map (Left) + Tabbed Controls (Right) -->
            <div class="flex" style="height: 700px;">
                
                <!-- LEFT PANEL: Fixed Map -->
                <div class="w-1/2 border-r">
                    <div class="h-full flex flex-col">
                        <!-- Map Container -->
                        <div class="flex-1 p-6 relative">
                            <div id="trail-map" class="w-full h-full rounded-md border border-input bg-muted relative z-10"></div>
                            <!-- Map Style Selector - Top Right -->
                            <div class="absolute top-2 right-2 z-[99]">
                                <div class="relative">
                                    <!-- Toggle Button -->
                                    <button type="button" id="map-layers-toggle" class="bg-white rounded-lg shadow-lg p-2.5 mt-6 mr-6 hover:bg-gray-50 transition-colors border border-gray-200">
                                        <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 0v10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2z"/>
                                        </svg>
                                    </button>
                                    
                                    <!-- Dropdown Menu -->
                                    <div id="map-layers-dropdown" class="hidden absolute top-full right-0 mt-1 mr-6 bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden" style="min-width: 180px;">
                                        <div class="p-2">
                                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-2 py-1.5">Map Style</div>
                                            <div class="grid grid-cols-2 gap-2">
                                                <button type="button" class="map-layer-option-card active" data-map-type="outdoors">
                                                    <div class="map-layer-preview">
                                                        <img src="{{ asset('images/map-layers/outdoor.png') }}" 
                                                            alt="Outdoors" class="w-full h-full object-cover">
                                                    </div>
                                                    <span class="map-layer-label">Outdoors</span>
                                                    <svg class="map-layer-checkmark" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                </button>
                                                
                                                <button type="button" class="map-layer-option-card" data-map-type="satellite">
                                                    <div class="map-layer-preview">
                                                        <img src="{{ asset('images/map-layers/satellite.png') }}" 
                                                            alt="Satellite" class="w-full h-full object-cover">
                                                    </div>
                                                    <span class="map-layer-label">Satellite</span>
                                                    <svg class="map-layer-checkmark" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="map-status" class="text-xs text-muted-foreground mt-2">
                                <div class="flex items-center gap-2">
                                    <span class="inline-block w-2 h-2 bg-gray-400 rounded-full animate-pulse"></span>
                                    <span>Ready to create trail route</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Elevation Profile (if you have it) -->
                        <div class="border-t p-6 hidden" id="elevation-profile-container">
                            <h5 class="text-sm font-medium mb-3">Elevation Profile</h5>
                            <canvas id="elevation-chart" class="w-full" style="height: 120px;"></canvas>
                            <div class="grid grid-cols-4 gap-2 mt-3 text-xs">
                                <div>
                                    <span class="text-muted-foreground">Max:</span>
                                    <span id="max-elevation" class="font-medium">0m</span>
                                </div>
                                <div>
                                    <span class="text-muted-foreground">Min:</span>
                                    <span id="min-elevation" class="font-medium">0m</span>
                                </div>
                                <div>
                                    <span class="text-muted-foreground">Gain:</span>
                                    <span id="elevation-gain" class="font-medium text-green-600">0m</span>
                                </div>
                                <div>
                                    <span class="text-muted-foreground">Loss:</span>
                                    <span id="elevation-loss" class="font-medium text-red-600">0m</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- RIGHT PANEL: Tabbed Controls -->
                <div class="w-1/2 flex">
                    <!-- Tab Navigation (Vertical Icons) -->
                    <div class="w-16 border-r bg-muted/30 flex flex-col items-center py-4 space-y-2">
                        <button type="button" class="trail-tab active" data-tab="controls" title="Trail Controls">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                            </svg>
                        </button>
                        
                        <button type="button" class="trail-tab" data-tab="specifications" title="Trail Specifications">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </button>
                        
                        <button type="button" class="trail-tab" data-tab="gpx" title="Import GPX">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </button>
                        
                        <button type="button" class="trail-tab" data-tab="highlights" title="Points of Interest">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Tab Content Area (Scrollable) -->
                    <div class="flex-1 overflow-y-auto">
                        
                        <!-- TAB 1: Trail Controls -->
                        <div class="tab-content active" id="tab-controls">
                            <div class="p-6 space-y-4">
                                <h4 class="font-medium">Trail Creation Controls</h4>

                                <!-- Waypoint Toggle Button -->
                                <button type="button" id="toggle-waypoint-mode" 
                                        class="w-full inline-flex items-center justify-center rounded-md border-2 border-gray-300 bg-gray-50 text-gray-600 hover:bg-gray-100 h-12 px-4 py-2 text-sm font-semibold transition-all">
                                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                                    </svg>
                                    <span id="waypoint-mode-text">Start Adding Waypoints</span>
                                </button>
                                
                                <button type="button" id="toggle-routing" 
                                        class="w-full inline-flex items-center justify-center rounded-md bg-blue-600 text-white hover:bg-blue-700 h-10 px-4 py-2 text-sm font-medium">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    Smart Routing: ON
                                </button>
                                
                                <button type="button" id="undo-waypoint" 
                                        class="w-full inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 text-sm font-medium">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                    </svg>
                                    Undo Last Point
                                </button>
                                
                                <!-- Route Statistics Section -->
                                <div class="border-t pt-6 mt-6 space-y-4">
                                    <h4 class="font-medium">Route Statistics</h4>
                                    
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="rounded-lg bg-blue-50 border border-blue-200 p-3 space-y-1">
                                            <span class="text-xs text-blue-600 font-medium">Distance</span>
                                            <div id="route-distance" class="text-lg font-semibold text-blue-700">0.00 km</div>
                                        </div>
                                        <div class="rounded-lg bg-green-50 border border-green-200 p-3 space-y-1">
                                            <span class="text-xs text-green-600 font-medium">Elevation Gain</span>
                                            <div id="route-elevation" class="text-lg font-semibold text-green-700">0 m</div>
                                        </div>
                                        <div class="rounded-lg bg-purple-50 border border-purple-200 p-3 space-y-1">
                                            <span class="text-xs text-purple-600 font-medium">Est. Time</span>
                                            <div id="route-time" class="text-lg font-semibold text-purple-700">0.0 hrs</div>
                                        </div>
                                        <div class="rounded-lg bg-gray-50 border border-gray-200 p-3 space-y-1">
                                            <span class="text-xs text-gray-600 font-medium">Waypoints</span>
                                            <div id="waypoint-count-display" class="text-lg font-semibold text-gray-700">0</div>
                                        </div>
                                    </div>
                                    <div class="border-t pt-6 mt-6 space-y-4">
                                        <h5 class="text-sm font-medium text-muted-foreground">Manual Adjustments</h5>
                                        <!-- Editable Fields -->
                                        <div class="grid grid-cols-3 gap-3">
                                            <div class="space-y-1">
                                                <label class="text-xs font-medium">Distance (km)</label>
                                                <input type="number" name="distance_km" step="0.01" value="{{ old('distance_km', $trail->distance_km) }}"
                                                    class="flex h-9 w-full text-sm rounded-md border border-input bg-background px-3 py-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                                            </div>
                                            <div class="space-y-1">
                                                <label class="text-xs font-medium">Est. Time (hours)</label>
                                                <input type="number" name="estimated_time_hours" step="0.1" value="{{ old('estimated_time_hours', $trail->estimated_time_hours) }}"
                                                    class="flex h-9 w-full text-sm rounded-md border border-input bg-background px-3 py-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                                            </div>
                                            <div class="space-y-1">
                                                <label class="text-xs font-medium">Elevation Gain (m)</label>
                                                <input type="number" name="elevation_gain_m" step="1" value="{{ old('elevation_gain_m', $trail->elevation_gain_m) }}"
                                                    class="flex h-9 w-full text-sm rounded-md border border-input bg-background px-3 py-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- TAB 2: Trail Specifications -->
                        <div class="tab-content" id="tab-specifications">
                            <div class="p-6 space-y-4">
                                <h4 class="font-medium">Trail Specifications</h4>
                                
                                <div class="space-y-4">
                                    <div class="space-y-2">
                                        <label class="text-xs font-medium">Difficulty (1-5) *</label>
                                        <select name="difficulty_level" required 
                                                class="flex h-9 w-full text-sm rounded-md border border-input bg-background px-3 py-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                                            <option value="" {{ old('difficulty_level', $trail->difficulty_level) == '' ? 'selected' : '' }}>Auto-detect</option>
                                            <option value="1" {{ old('difficulty_level', $trail->difficulty_level) == '1' ? 'selected' : '' }}>1 - Very Easy</option>
                                            <option value="2" {{ old('difficulty_level', $trail->difficulty_level) == '2' ? 'selected' : '' }}>2 - Easy</option>
                                            <option value="3" {{ old('difficulty_level', $trail->difficulty_level) == '3' ? 'selected' : '' }}>3 - Moderate</option>
                                            <option value="4" {{ old('difficulty_level', $trail->difficulty_level) == '4' ? 'selected' : '' }}>4 - Hard</option>
                                            <option value="5" {{ old('difficulty_level', $trail->difficulty_level) == '5' ? 'selected' : '' }}>5 - Very Hard</option>
                                        </select>
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <label class="text-xs font-medium">Trail Type *</label>
                                        <select name="trail_type" required 
                                                class="flex h-9 w-full text-sm rounded-md border border-input bg-background px-3 py-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                                            <option value="">Select type</option>
                                            <option value="loop" {{ old('trail_type', $trail->trail_type) == 'loop' ? 'selected' : '' }}>Loop</option>
                                            <option value="out-and-back" {{ old('trail_type', $trail->trail_type) == 'out-and-back' ? 'selected' : '' }}>Out and Back</option>
                                            <option value="point-to-point" {{ old('trail_type', $trail->trail_type) == 'point-to-point' ? 'selected' : '' }}>Point to Point</option>
                                        </select>
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <label class="text-xs font-medium">Best Season</label>
                                        <select name="best_season" 
                                                class="flex h-9 w-full text-sm rounded-md border border-input bg-background px-3 py-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                                            <option value="">Any season</option>
                                            <option value="spring">Spring</option>
                                            <option value="summer">Summer</option>
                                            <option value="fall">Fall</option>
                                            <option value="winter">Winter</option>
                                        </select>
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Trail Status *</label>
                                        <select name="status" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                                            <option value="active" selected>Active - Open to public</option>
                                            <option value="closed">Closed - Temporarily unavailable</option>
                                            <option value="seasonal">Seasonal - Check conditions</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- TAB 3: GPX Import -->
                        <div class="tab-content" id="tab-gpx">
                            <div class="p-6 space-y-4">
                                <h4 class="font-medium">Import from GPX</h4>
                                <p class="text-xs text-muted-foreground">Upload a GPX file to automatically create the trail route</p>
                                
                                <input type="file" id="gpx-import" accept=".gpx"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                                
                                <input type="hidden" id="use_gpx_calculations" name="use_gpx_calculations" value="false">
                                
                                <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 space-y-2 mt-4">
                                    <h5 class="font-medium text-blue-800 text-sm">📍 GPX File Info</h5>
                                    <ul class="text-xs text-blue-700 space-y-1">
                                        <li>• Replaces any manually created route</li>
                                        <li>• Automatically calculates distance & elevation</li>
                                        <li>• Preserves elevation data if available</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <!-- TAB 4: Points of Interest -->
                        <div class="tab-content" id="tab-highlights">
                            <div class="p-6 space-y-6">
                                
                                <!-- Mode Toggle -->
                                <div class="space-y-3">
                                    <h4 class="font-medium">Highlight Mode</h4>
                                    <button type="button" id="toggle-highlight-mode" 
                                            class="w-full inline-flex items-center justify-center rounded-md border-2 border-gray-300 bg-gray-50 text-gray-600 hover:bg-gray-100 h-12 px-4 py-2 text-sm font-semibold transition-all">
                                        <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span id="highlight-mode-text">Start Adding Highlights</span>
                                    </button>
                                </div>
                                
                                <!-- Add Highlight Form -->
                                <div id="highlight-form-section" class="space-y-4 border-t pt-4">
                                    
                                    <h5 class="font-medium text-sm">New Highlight Details</h5>
                                    
                                    <div class="space-y-3">
                                        <div class="space-y-2">
                                            <label class="text-xs font-medium">Highlight Type *</label>
                                            <select id="highlight-type-select" class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                                                <option value="">Select type...</option>
                                                <option value="viewpoint" data-icon="👁️" data-color="#8B5CF6">👁️ Viewpoint</option>
                                                <option value="waterfall" data-icon="💧" data-color="#3B82F6">💧 Waterfall</option>
                                                <option value="summit" data-icon="⛰️" data-color="#10B981">⛰️ Summit</option>
                                                <option value="bridge" data-icon="🌉" data-color="#F59E0B">🌉 Bridge</option>
                                                <option value="lake" data-icon="🏞️" data-color="#06B6D4">🏞️ Lake</option>
                                                <option value="wildlife" data-icon="🦌" data-color="#84CC16">🦌 Wildlife Spot</option>
                                                <option value="camping" data-icon="⛺" data-color="#EF4444">⛺ Camping Area</option>
                                                <option value="shelter" data-icon="🏠" data-color="#6B7280">🏠 Shelter</option>
                                                <option value="forest" data-icon="🌲" data-color="#059669">🌲 Forest</option>
                                                <option value="parking" data-icon="🅿️" data-color="#8B5CF6">🅿️ Parking</option>
                                                <option value="restroom" data-icon="🚻" data-color="#EC4899">🚻 Restroom</option>
                                                <option value="picnic" data-icon="🍽️" data-color="#F97316">🍽️ Picnic Area</option>
                                                <option value="other" data-icon="📍" data-color="#6B7280">📍 Other</option>
                                            </select>
                                        </div>
                                        
                                        <div class="space-y-2">
                                            <label class="text-xs font-medium">Name *</label>
                                            <input type="text" id="highlight-name-input" placeholder="e.g., Eagle's Nest Viewpoint"
                                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                                        </div>
                                        
                                        <div class="space-y-2">
                                            <label class="text-xs font-medium">Description (Optional)</label>
                                            <textarea id="highlight-description-input" rows="2" placeholder="Brief description..."
                                                    class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"></textarea>
                                        </div>

                                        <div class="space-y-2">
                                            <label class="text-xs font-medium">Photo (Optional)</label>
                                            <input type="file" 
                                                id="highlight-media-input" 
                                                accept="image/*" 
                                                class="flex h-9 w-full text-sm rounded-md border border-input bg-background px-3 py-1 file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-primary-foreground hover:file:bg-primary/90">
                                            <p class="text-xs text-muted-foreground">Upload 1 photo for this feature</p>
                                            
                                            <!-- Preview container -->
                                            <div id="highlight-media-preview" class="hidden mt-2">
                                                <div class="relative w-full h-20 rounded-md border-2 border-dashed border-gray-300 bg-gray-50 flex items-center justify-center">
                                                    <span class="text-xs text-gray-500">Preview will appear here</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="space-y-2">
                                            <label class="text-xs font-medium">Video URL (Optional)</label>
                                            <input type="url" 
                                                id="highlight-video-url-input" 
                                                placeholder="https://www.youtube.com/watch?v=..." 
                                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm">
                                            <p class="text-xs text-muted-foreground">Add YouTube or Vimeo video link</p>
                                            
                                            <!-- Video Preview container -->
                                            <div id="highlight-video-preview" class="hidden mt-2">
                                                <div class="relative w-full rounded-md overflow-hidden border" style="padding-bottom: 56.25%;">
                                                    <iframe id="highlight-video-iframe" class="absolute top-0 left-0 w-full h-full" frameborder="0" allowfullscreen></iframe>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-3">
                                            <div class="space-y-2">
                                                <label class="text-xs font-medium">Icon</label>
                                                <input type="text" id="highlight-icon-input" placeholder="📍" maxlength="10"
                                                    class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm text-center text-xl">
                                            </div>
                                            <div class="space-y-2">
                                                <label class="text-xs font-medium">Color</label>
                                                <input type="color" id="highlight-color-input" value="#10B981"
                                                    class="flex h-9 w-full rounded-md border border-input bg-background px-2 py-1">
                                            </div>
                                        </div>

                                        <button type="button" id="add-highlight-btn" 
                                                class="w-full inline-flex items-center justify-center rounded-md bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-4 py-2 text-sm font-medium">
                                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                            </svg>
                                            Add Highlight
                                        </button>
                                    </div>
                                </div>

                                <!-- Instructions -->
                                <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 space-y-2">
                                    <h5 class="font-medium text-blue-800 text-sm">How to Add Highlights</h5>
                                    <ul class="text-xs text-blue-700 space-y-1">
                                        <li>• Click "Start Adding Highlights" to enable highlight mode</li>
                                        <li>• Select a highlight type from the dropdown</li>
                                        <li>• Click on the map to place the marker</li>
                                        <li>• Fill in the name and optional description</li>
                                        <li>• Click "Add Highlight" to save it</li>
                                        <li><strong>• To edit: Click the edit button (✏️), modify fields, or drag the marker to reposition</strong></li>
                                    </ul>
                                </div>

                                 <!-- Highlights List -->
                                <div class="border-t pt-4 space-y-3">
                                    <h5 class="font-medium text-sm">Added Highlights (<span id="highlights-count">0</span>)</h5>
                                    <div id="highlights-list" class="space-y-2 max-h-64 overflow-y-auto">
                                        <p class="text-sm text-muted-foreground text-center py-8">No highlights added yet</p>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Hidden input for highlights data -->
                        <input type="hidden" name="highlights_data" id="highlights-data-input" value="[]">
                        <input type="hidden" name="edited_highlights" id="edited-highlights-input" value="{}">
                        <input type="hidden" name="route_coordinates" id="route-coordinates-input" value="">
                        <input type="hidden" name="start_lat" id="start-lat-input" value="">
                        <input type="hidden" name="start_lng" id="start-lng-input" value="">
                        <input type="hidden" name="end_lat" id="end-lat-input" value="">
                        <input type="hidden" name="end_lng" id="end-lng-input" value="">
                        
                    </div>
                </div>
                
            </div>
        </div>

        <!-- Seasonal Information -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 space-y-6">
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold">Seasonal Information</h3>
                    <p class="text-sm text-muted-foreground">Trail conditions and recommendations throughout the year</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Spring -->
                    <div class="rounded-lg border border-input p-4 space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">Spring</span>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Trail Conditions</label>
                                <input type="text" name="seasonal[spring][conditions]" 
                                    placeholder="e.g., Muddy, Snow patches"
                                    value="{{ old('seasonal.spring.conditions', $springData->conditions ?? '') }}"
                                    class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm">
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                @php
                                    $springData = $trail->seasonalData->firstWhere('season', 'spring');
                                @endphp
                                <input type="checkbox" name="seasonal[spring][recommended]" value="1" 
                                    {{ old('seasonal.spring.recommended', $springData->recommended ?? true) ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                                <label class="text-sm font-medium">Recommended in Spring</label>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Notes</label>
                                <textarea name="seasonal[spring][notes]" rows="2" 
                                    placeholder="Special spring considerations..."
                                    class="flex min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm">{{ old('seasonal.spring.notes', $springData->notes ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Summer -->
                    <div class="rounded-lg border border-input p-4 space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">Summer</span>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Trail Conditions</label>
                                <input type="text" name="seasonal[summer][conditions]" 
                                       placeholder="e.g., Dry, Clear"
                                       value="{{ old('seasonal.summer.conditions') }}"
                                       class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                @php
                                    $summerData = $trail->seasonalData->firstWhere('season', 'summer');
                                @endphp
                                <input type="checkbox" name="seasonal[summer][recommended]" value="1" 
                                    {{ old('seasonal.summer.recommended', $summerData->recommended ?? true) ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                                <label class="text-sm font-medium">Recommended in Summer</label>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Notes</label>
                                <textarea name="seasonal[summer][notes]" rows="2" 
                                          placeholder="Special summer considerations..."
                                          class="flex min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">{{ old('seasonal.summer.notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Fall -->
                    <div class="rounded-lg border border-input p-4 space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">Fall</span>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Trail Conditions</label>
                                <input type="text" name="seasonal[fall][conditions]" 
                                       placeholder="e.g., Wet leaves, Early snow"
                                       value="{{ old('seasonal.fall.conditions') }}"
                                       class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                @php
                                    $fallData = $trail->seasonalData->firstWhere('season', 'fall');
                                @endphp
                                <input type="checkbox" name="seasonal[fall][recommended]" value="1" 
                                    {{ old('seasonal.fall.recommended', $fallData->recommended ?? true) ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                                <label class="text-sm font-medium">Recommended in Fall</label>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Notes</label>
                                <textarea name="seasonal[fall][notes]" rows="2" 
                                          placeholder="Special fall considerations..."
                                          class="flex min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">{{ old('seasonal.fall.notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Winter -->
                    <div class="rounded-lg border border-input p-4 space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">Winter</span>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Trail Conditions</label>
                                <input type="text" name="seasonal[winter][conditions]" 
                                       placeholder="e.g., Snow, Ice, Closed"
                                       value="{{ old('seasonal.winter.conditions') }}"
                                       class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                @php
                                    $winterData = $trail->seasonalData->firstWhere('season', 'winter');
                                @endphp
                                <input type="checkbox" name="seasonal[winter][recommended]" value="1" 
                                    {{ old('seasonal.winter.recommended', $winterData->recommended ?? false) ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                                <label class="text-sm font-medium">Recommended in Winter</label>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Notes</label>
                                <textarea name="seasonal[winter][notes]" rows="2" 
                                          placeholder="Special winter considerations..."
                                          class="flex min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">{{ old('seasonal.winter.notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 space-y-6">
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold">Additional Information</h3>
                    <p class="text-sm text-muted-foreground">Extra details and practical information for hikers</p>
                </div>
                
                <div class="space-y-6">
                    <div class="space-y-3">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Best Seasons
                        </label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            @php 
                                $seasons = ['Spring', 'Summer', 'Fall', 'Winter'];
                                $trailBestSeasons = is_array($trail->best_seasons) ? $trail->best_seasons : [];
                                // Normalize case for comparison
                                $trailBestSeasons = array_map('strtolower', $trailBestSeasons);
                            @endphp
                            
                            @foreach($seasons as $season)
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="best_seasons[]" value="{{ $season }}" 
                                        {{ in_array(strtolower($season), old('best_seasons', $trailBestSeasons)) ? 'checked' : '' }}
                                        class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                                    <span class="text-sm font-medium">{{ $season }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Directions to Trailhead
                        </label>
                        <textarea name="directions" rows="3" 
                                  placeholder="Detailed directions on how to reach the trailhead..."
                                  class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">{{ old('directions', $trail->directions) }}</textarea>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Parking Information
                        </label>
                        <textarea name="parking_info" rows="3" 
                                  placeholder="Parking availability, fees, restrictions, and tips..."
                                  class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">{{ old('parking_info', $trail->parking_info) }}</textarea>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Safety Notes & Warnings
                        </label>
                        <textarea name="safety_notes" rows="3" 
                                  placeholder="Important safety information, hazards, equipment recommendations..."
                                  class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">{{ old('safety_notes', $trail->safety_notes) }}</textarea>
                    </div>

                    <div class="flex items-center space-x-2">
                        <input type="checkbox" name="is_featured" value="1" 
                               {{ old('is_featured', $trail->is_featured) ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                        <label class="text-sm font-medium cursor-pointer">Feature this trail on homepage</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Photo Upload -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 space-y-6">
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold">Trail Media</h3>
                    <p class="text-sm text-muted-foreground">Upload up to 10 photos and video links. Drag to reorder, click star to set featured.</p>
                </div>
                
                <!-- Existing Media Display -->
                @if($trail->media && $trail->media->count() > 0)
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="font-medium text-sm">Current Media ({{ $trail->media->count() }})</h4>
                        <p class="text-xs text-muted-foreground">Manage your media below</p>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                        @foreach($trail->media as $media)
                        <div class="space-y-2" data-media-id="{{ $media->id }}">
                            @if($media->media_type === 'photo')
                                {{-- Photo Display --}}
                                <div class="relative group rounded-lg overflow-hidden border-2 {{ $media->is_featured ? 'border-yellow-400' : 'border-gray-200' }}">
                                    <img src="{{ asset('storage/' . $media->storage_path) }}" 
                                        alt="{{ $media->original_name }}" 
                                        class="w-full h-32 object-cover">
                                    
                                    @if($media->is_featured)
                                    <div class="absolute top-2 left-2">
                                        <span class="inline-flex items-center rounded-full bg-yellow-400 px-2 py-1 text-xs font-semibold text-yellow-900">
                                            ⭐ Featured
                                        </span>
                                    </div>
                                    @endif
                                </div>
                                
                            @elseif($media->media_type === 'video_url')
                                {{-- Video Display --}}
                                <div class="relative group rounded-lg overflow-hidden border-2 {{ $media->is_featured ? 'border-yellow-400' : 'border-gray-200' }} cursor-pointer"
                                    onclick="playExistingVideo('{{ $media->video_url }}')">
                                    <div class="w-full h-32 bg-gray-900 flex items-center justify-center">
                                        <svg class="w-12 h-12 text-white opacity-75" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                        </svg>
                                    </div>
                                    
                                    @if($media->is_featured)
                                    <div class="absolute top-2 left-2">
                                        <span class="inline-flex items-center rounded-full bg-yellow-400 px-2 py-1 text-xs font-semibold text-yellow-900">
                                            ⭐ Featured
                                        </span>
                                    </div>
                                    @endif
                                    
                                    <div class="absolute bottom-2 left-2">
                                        <span class="inline-flex items-center rounded bg-black bg-opacity-75 px-2 py-1 text-xs font-medium text-white">
                                            🎥 Video
                                        </span>
                                    </div>
                                    
                                    <!-- Play Button Overlay -->
                                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                        <div class="bg-white bg-opacity-90 rounded-full p-3 shadow-lg">
                                            <svg class="w-8 h-8 text-gray-900" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Buttons Below Media -->
                            <div class="flex gap-2">
                                @if($media->media_type === 'photo')
                                    @if(!$media->is_featured)
                                    <button type="button" 
                                            onclick="setFeaturedPhoto({{ $media->id }})"
                                            class="flex-1 px-3 py-1.5 text-xs font-medium rounded bg-gray-100 hover:bg-gray-200 text-gray-700">
                                        Set Featured
                                    </button>
                                    @else
                                    <button type="button" 
                                            class="flex-1 px-3 py-1.5 text-xs font-medium rounded bg-yellow-400 text-yellow-900 cursor-default">
                                        ⭐ Featured
                                    </button>
                                    @endif
                                @else
                                    <div class="flex-1"></div>
                                @endif
                                <button type="button" 
                                        onclick="deletePhoto({{ $media->id }})"
                                        class="px-3 py-1.5 text-xs font-medium rounded bg-red-100 hover:bg-red-200 text-red-700">
                                    Delete
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <div class="border-t pt-4 mt-4">
                        <p class="text-sm text-muted-foreground">
                            <span class="font-medium">Tip:</span> The featured media appears as the main image/video on the trail page.
                        </p>
                    </div>
                </div>
                @endif

                <!-- Upload New Media Section -->
                @if(!$trail->media || $trail->media->count() < 10)
                <div class="space-y-4 {{ $trail->media && $trail->media->count() > 0 ? 'border-t pt-6' : '' }}">
                    @if($trail->media && $trail->media->count() > 0)
                    <h4 class="font-medium text-sm">Upload Additional Media ({{ 10 - $trail->media->count() }} remaining)</h4>
                    @endif
                    
                    <!-- Drag & Drop Zone -->
                    <div id="photo-upload-zone" class="border-2 border-dashed border-input rounded-lg p-8 text-center hover:border-primary transition-colors cursor-pointer">
                        <input type="file" id="photo-input" name="photos[]" multiple accept="image/*" class="hidden" max="10">
                        <div id="upload-prompt">
                            <svg class="mx-auto h-12 w-12 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="mt-2 text-sm text-muted-foreground">
                                <span class="font-semibold text-primary">Click to upload</span> or drag and drop
                            </p>
                            <p class="text-xs text-muted-foreground mt-1">PNG, JPG, GIF up to 10MB (max 10 photos)</p>
                        </div>
                    </div>
                    
                    <!-- Video URL Input Section -->
                    <div class="space-y-3 mt-4 p-4 bg-gray-50 rounded-lg border">
                        <label class="text-sm font-medium">Add Video URL (Optional)</label>
                        <div class="flex gap-2">
                            <input type="url" 
                                id="trail-video-url-input" 
                                placeholder="https://www.youtube.com/watch?v=..." 
                                class="flex-1 h-10 rounded-md border border-input bg-background px-3 py-2 text-sm">
                            <button type="button" 
                                id="add-video-url-btn"
                                class="px-4 py-2 bg-primary text-primary-foreground rounded-md text-sm font-medium hover:bg-primary/90">
                                Add Video
                            </button>
                        </div>
                        <p class="text-xs text-muted-foreground">Add YouTube or Vimeo video links</p>
                    </div>
                    
                    <!-- Photo Preview Grid -->
                    <div id="photo-preview-grid" class="hidden">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-medium">New Media (<span id="photo-count">0</span>/10)</p>
                            <button type="button" id="clear-photos" class="text-sm text-red-600 hover:text-red-800">Clear All</button>
                        </div>
                        <div id="photo-previews" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                            <!-- Photo previews will be inserted here -->
                        </div>
                    </div>
                </div>
                @else
                <div class="border-t pt-6">
                    <div class="rounded-lg bg-yellow-50 border border-yellow-200 p-4 text-center">
                        <p class="text-sm text-yellow-800">
                            <span class="font-semibold">Maximum photos reached.</span> Delete existing photos to upload new ones.
                        </p>
                    </div>
                </div>
                @endif

                <!-- Hidden inputs for photo data -->
                <input type="hidden" name="featured_photo_index" id="featured-photo-index" value="-1">
                <input type="hidden" name="featured_photo_id" id="featured-photo-id" value="">
            </div>
        </div>

        <!-- Spacer for bottom padding (prevents form actions from covering content) -->
        <div class="h-16"></div>

        <!-- Form Actions -->
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg z-[101] flex items-center justify-between py-4 px-6">
            <a href="{{ route('admin.trails.index') }}" 
               class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Cancel
            </a>
            <button type="submit" 
                    class="inline-flex items-center justify-center rounded-md bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-8 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Update Trail
            </button>
        </div>
    </form>
    <div id="validation-modal" class="hidden fixed inset-0 bg-black/50 z-[100] flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full transform transition-all animate-modal-in">
            <div class="p-6">
                <!-- Icon -->
                <div id="modal-icon-container" class="mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4">
                    <!-- Icon will be inserted here -->
                </div>
                
                <!-- Content -->
                <div class="text-center">
                    <h3 id="modal-title" class="text-lg font-semibold text-gray-900 mb-2"></h3>
                    <p id="modal-message" class="text-sm text-gray-600 whitespace-pre-line"></p>
                </div>
                
                <!-- Actions -->
                <div id="modal-actions" class="mt-6 flex gap-3">
                    <!-- Buttons will be inserted here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Media Modal -->
    <div id="media-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-[102] flex items-center justify-center p-4">
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

@push('scripts')
<script>
    // Add this if you haven't already from Day 9
    function showNotification(title, message, type = 'info') {
        const colors = {
            success: { bg: 'bg-green-50', border: 'border-green-200', text: 'text-green-800', icon: '✓' },
            info: { bg: 'bg-blue-50', border: 'border-blue-200', text: 'text-blue-800', icon: 'ℹ' },
            warning: { bg: 'bg-amber-50', border: 'border-amber-200', text: 'text-amber-800', icon: '⚠' },
            error: { bg: 'bg-red-50', border: 'border-red-200', text: 'text-red-800', icon: '✕' }
        };
        
        const style = colors[type] || colors.info;
        
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-[60] ${style.bg} ${style.border} ${style.text} border rounded-lg p-4 shadow-lg max-w-md`;
        
        notification.innerHTML = `
            <div class="flex items-start gap-3">
                <span class="text-xl">${style.icon}</span>
                <div class="flex-1">
                    <div class="font-semibold text-sm">${title}</div>
                    <div class="text-xs mt-1">${message}</div>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => notification.remove(), 5000);
    }
    // Validation Modal System
    class ValidationModal {
        constructor() {
            this.modal = document.getElementById('validation-modal');
            this.iconContainer = document.getElementById('modal-icon-container');
            this.title = document.getElementById('modal-title');
            this.message = document.getElementById('modal-message');
            this.actions = document.getElementById('modal-actions');
        }

        show({ type = 'warning', title, message, buttons = [] }) {
            // Set icon based on type
            const icons = {
                warning: {
                    bg: 'bg-amber-100',
                    icon: `<svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>`
                },
                error: {
                    bg: 'bg-red-100',
                    icon: `<svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>`
                },
                info: {
                    bg: 'bg-blue-100',
                    icon: `<svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>`
                },
                success: {
                    bg: 'bg-green-100',
                    icon: `<svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>`
                }
            };

            const config = icons[type] || icons.warning;

            // Update icon
            this.iconContainer.className = `mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4 ${config.bg}`;
            this.iconContainer.innerHTML = config.icon;

            // Update content
            this.title.textContent = title;
            this.message.textContent = message;

            // Update buttons
            this.actions.innerHTML = buttons.map(btn => {
                const baseClasses = 'flex-1 inline-flex items-center justify-center rounded-md px-4 py-2.5 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2';
                const variantClasses = {
                    primary: 'bg-primary text-primary-foreground hover:bg-primary/90 focus:ring-primary',
                    secondary: 'border border-input bg-background hover:bg-accent hover:text-accent-foreground focus:ring-ring',
                    danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500'
                };
                
                const classes = `${baseClasses} ${variantClasses[btn.variant] || variantClasses.secondary}`;
                
                return `<button type="button" class="${classes}" data-action="${btn.action}">${btn.label}</button>`;
            }).join('');

            // Attach button handlers
            this.actions.querySelectorAll('button').forEach(btn => {
                btn.addEventListener('click', () => {
                    const action = btn.dataset.action;
                    const handler = buttons.find(b => b.action === action)?.handler;
                    if (handler) handler();
                    this.hide();
                });
            });

            // Show modal
            this.modal.classList.remove('hidden');
            
            // Trap focus in modal
            this.trapFocus();
        }

        hide() {
            this.modal.classList.add('hidden');
        }

        trapFocus() {
            // Close on Escape key
            const escHandler = (e) => {
                if (e.key === 'Escape') {
                    this.hide();
                    document.removeEventListener('keydown', escHandler);
                }
            };
            document.addEventListener('keydown', escHandler);

            // Close on backdrop click
            this.modal.addEventListener('click', (e) => {
                if (e.target === this.modal) {
                    this.hide();
                }
            }, { once: true });
        }
    }

    // Initialize modal
    const validationModal = new ValidationModal();
    // Backend GPX Preview Handler
    document.getElementById('gpx-file-backend')?.addEventListener('change', async function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        // Check file size (warn if > 5MB)
        if (file.size > 5 * 1024 * 1024) {
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            
            validationModal.show({
                type: 'warning',
                title: 'Large GPX File',
                message: `This GPX file is ${fileSize} MB.\n\nLarge files may take longer to process. Continue?`,
                buttons: [
                    {
                        label: 'Cancel',
                        variant: 'secondary',
                        action: 'cancel',
                        handler: () => {
                            document.getElementById('gpx-file-backend').value = '';
                        }
                    },
                    {
                        label: 'Continue',
                        variant: 'primary',
                        action: 'continue',
                        handler: () => {
                            // Process the file (re-trigger the handler logic)
                            processGPXFileManually(file);
                        }
                    }
                ]
            });
            
            return;
        }
        
        // Process the file
        await processGPXFileManually(file);
    });

    // Separate function for processing to avoid duplication
    async function processGPXFileManually(file) {
        const formData = new FormData();
        formData.append('gpx_file', file);
        formData.append('difficulty_level', document.querySelector('select[name="difficulty_level"]')?.value || 3);
        formData.append('_token', '{{ csrf_token() }}');
        
        const resultsDiv = document.getElementById('gpx-preview-results');
        resultsDiv.classList.remove('hidden');
        resultsDiv.innerHTML = `
            <div class="text-center py-4">
                <svg class="animate-spin h-8 w-8 text-green-600 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div class="text-sm text-green-600 font-medium">Analyzing GPX file...</div>
                <div class="text-xs text-gray-500 mt-1">This may take a few seconds</div>
            </div>
        `;
        
        try {
            const response = await fetch('{{ route("admin.trails.gpx.preview") }}', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                const data = result.data;
                
                // Build the results HTML
                resultsDiv.innerHTML = `
                    <div class="bg-white rounded border border-green-300 p-3 space-y-3">
                        <div class="text-sm font-medium text-green-900">Calculated Values:</div>
                        <div class="grid grid-cols-3 gap-2 text-xs">
                            <div>
                                <span class="text-green-700">Distance:</span>
                                <div class="font-bold text-green-900">${data.distance.toFixed(2)} km</div>
                            </div>
                            <div>
                                <span class="text-green-700">Elevation:</span>
                                <div class="font-bold text-green-900">${data.elevation} m</div>
                            </div>
                            <div>
                                <span class="text-green-700">Time:</span>
                                <div class="font-bold text-green-900">${data.time.toFixed(1)} hrs</div>
                            </div>
                        </div>
                        <button type="button" id="apply-gpx-values" 
                            class="w-full inline-flex items-center justify-center rounded-md bg-green-600 text-white hover:bg-green-700 h-10 px-4 py-2 text-sm font-medium">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Apply These Values
                        </button>
                    </div>
                `;

                // ✅ SINGLE button handler - attach AFTER HTML is created
                document.getElementById('apply-gpx-values').addEventListener('click', function() {
                    // Populate the editable input fields with CORRECT property names
                    const distanceInput = document.querySelector('input[name="distance_km"]');
                    const elevationInput = document.querySelector('input[name="elevation_gain_m"]');
                    const timeInput = document.querySelector('input[name="estimated_time_hours"]');
                    
                    if (distanceInput) {
                        distanceInput.value = data.distance.toFixed(2); // ✅ Use data.distance
                        distanceInput.classList.add('bg-green-50', 'border-green-400');
                    }
                    
                    if (elevationInput) {
                        elevationInput.value = data.elevation; // ✅ Use data.elevation
                        elevationInput.classList.add('bg-green-50', 'border-green-400');
                    }
                    
                    if (timeInput) {
                        timeInput.value = data.time.toFixed(2); // ✅ Use data.time
                        timeInput.classList.add('bg-green-50', 'border-green-400');
                    }
                    
                    // Show success notification
                    showNotification('✓ Values Applied', 'GPX calculations have been applied to the form fields. You can still edit them manually if needed.', 'success');
                    
                    // Visual feedback - button changes
                    this.textContent = '✓ Applied!';
                    this.classList.remove('bg-green-600', 'hover:bg-green-700');
                    this.classList.add('bg-green-800');
                    this.disabled = true;
                    
                    // Remove highlight after 2 seconds
                    setTimeout(() => {
                        distanceInput?.classList.remove('bg-green-50', 'border-green-400');
                        elevationInput?.classList.remove('bg-green-50', 'border-green-400');
                        timeInput?.classList.remove('bg-green-50', 'border-green-400');
                    }, 2000);
                });
                
                // Store data for later use
                window.gpxCalculatedData = data;

                // Also display the route on the map
                if (window.trailBuilder && data.coordinates) {
                    window.trailBuilder.clearRoute();
                    window.trailBuilder.displayGPXFromBackend(data.coordinates);
                }
                
            } else {
                resultsDiv.innerHTML = `
                    <div class="text-red-600 text-sm p-3 bg-red-50 rounded border border-red-200">
                        ${result.message || 'Error processing GPX file'}
                    </div>
                `;
            }
        } catch (error) {
            console.error('GPX Preview Error:', error);
            resultsDiv.innerHTML = `
                <div class="text-red-600 text-sm p-3 bg-red-50 rounded border border-red-200">
                    Error: ${error.message}
                </div>
            `;
        }
    }
    // Simplified TrailBuilder focused on import/display
    class TrailBuilder {
        constructor() {
            this.map = null;
            this.routeLayer = null;
            this.waypoints = [];
            this.routeSegments = [];
            this.totalDistance = 0;
            this.totalTime = 0;
            this.smartRouting = true;
            this.highlights = []; 
            this.highlightFiles = {};
            this.existingHighlights = @json($trail->features ?? []);
            this.deletedFeatures = [];
            this.highlightsLayer = null; 
            this.pendingHighlight = null; 
            this.waypointModeEnabled = false;
            this.highlightModeEnabled = false; 
            this.editingHighlightId = null; 
            this.highlightMarkers = {};  
            this.editedExistingHighlights = {};
            this.editingExistingHighlight = false;
            this.setupMediaPreview(); 
            this.prePopulateCoordinates();
            this.init();
        }

        // NEW: Add file preview when user selects a file
        setupMediaPreview() {
            const mediaInput = document.getElementById('highlight-media-input');
            const previewContainer = document.getElementById('highlight-media-preview');
            const videoUrlInput = document.getElementById('highlight-video-url-input');
            const videoPreview = document.getElementById('highlight-video-preview');
            const videoIframe = document.getElementById('highlight-video-iframe');
            
            // Photo preview
            if (mediaInput) {
                mediaInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        const file = this.files[0];
                        const reader = new FileReader();
                        
                        reader.onload = function(e) {
                            if (file.type.startsWith('image/')) {
                                previewContainer.innerHTML = `
                                    <div class="relative w-full h-20 rounded-md overflow-hidden border">
                                        <img src="${e.target.result}" alt="Preview" class="w-full h-full object-cover">
                                    </div>
                                `;
                                previewContainer.classList.remove('hidden');
                            }
                        }
                        
                        reader.readAsDataURL(file);
                    } else {
                        previewContainer.classList.add('hidden');
                    }
                });
            }
            
            // Video URL preview
            if (videoUrlInput) {
                videoUrlInput.addEventListener('input', function() {
                    const url = this.value.trim();
                    if (url) {
                        const embedUrl = window.trailBuilder.getVideoEmbedUrl(url);
                        if (embedUrl) {
                            videoIframe.src = embedUrl;
                            videoPreview.classList.remove('hidden');
                        } else {
                            videoPreview.classList.add('hidden');
                        }
                    } else {
                        videoPreview.classList.add('hidden');
                    }
                });
            }
        }

        getVideoEmbedUrl(url) {
            // YouTube
            let match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/);
            if (match) {
                return `https://www.youtube.com/embed/${match[1]}`;
            }
            
            // Vimeo
            match = url.match(/vimeo\.com\/(\d+)/);
            if (match) {
                return `https://player.vimeo.com/video/${match[1]}`;
            }
            
            return null;
        }

        // Decode Google's polyline algorithm
        decodePolyline(str, precision = 5) {
            let index = 0;
            let lat = 0;
            let lng = 0;
            const coordinates = [];
            let shift = 0;
            let result = 0;
            let byte = null;
            let latitude_change;
            let longitude_change;
            const factor = Math.pow(10, precision || 5);

            while (index < str.length) {
                // Decode latitude
                byte = null;
                shift = 0;
                result = 0;

                do {
                    byte = str.charCodeAt(index++) - 63;
                    result |= (byte & 0x1f) << shift;
                    shift += 5;
                } while (byte >= 0x20);

                latitude_change = ((result & 1) ? ~(result >> 1) : (result >> 1));

                // Decode longitude
                shift = 0;
                result = 0;

                do {
                    byte = str.charCodeAt(index++) - 63;
                    result |= (byte & 0x1f) << shift;
                    shift += 5;
                } while (byte >= 0x20);

                longitude_change = ((result & 1) ? ~(result >> 1) : (result >> 1));

                lat += latitude_change;
                lng += longitude_change;

                coordinates.push([lat / factor, lng / factor]);
            }

            return coordinates;
        }

        init() {
            const mapElement = document.getElementById('trail-map');
            
            if (!mapElement) {
                console.error('Map container #trail-map not found');
                return;
            }
            
            try {
                this.map = L.map('trail-map', {
                    maxZoom: 20,
                    minZoom: 5
                }).setView([8.4542, 124.6319], 13); // Cagayan de Oro coordinates
                
                // Define base layers for map styles
                this.baseLayers = {
                    'outdoors': L.tileLayer('https://{s}.tile-cyclosm.openstreetmap.fr/cyclosm/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap, CyclOSM',
                        maxZoom: 20
                    }),
                    'satellite': L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                        attribution: '© Esri, Maxar, Earthstar Geographics',
                        maxZoom: 18
                    })
                };

                // Track current map type
                this.currentMapType = 'outdoors';

                // Add default base layer
                this.baseLayers[this.currentMapType].addTo(this.map);

                this.routeLayer = L.layerGroup().addTo(this.map);
                this.highlightsLayer = L.layerGroup().addTo(this.map); 
                
                this.setupEventListeners();
                this.setupMapClicks();
                this.setupHighlightHandlers();
                this.loadExistingFeatures();
                
                console.log('Trail builder initialized successfully');
            } catch (error) {
                console.error('Error initializing map:', error);
            }
        }

        loadExistingFeatures() {
            if (!this.existingHighlights || this.existingHighlights.length === 0) {
                this.updateHighlightsList();
                return;
            }
            
            this.existingHighlights.forEach(feature => {
                // Handle coordinates properly
                let coords;
                if (Array.isArray(feature.coordinates)) {
                    coords = feature.coordinates;
                } else if (feature.coordinates && feature.coordinates.lat) {
                    coords = [feature.coordinates.lat, feature.coordinates.lng];
                } else {
                    console.warn('Invalid coordinates for feature:', feature);
                    return;
                }
                
                const marker = L.marker(coords, {
                    icon: L.divIcon({
                        html: `<div style="background-color: ${feature.color || '#6366f1'};" class="w-8 h-8 rounded-full border-2 border-white shadow-lg flex items-center justify-center text-white text-lg">${feature.icon || '📍'}</div>`,
                        className: 'custom-marker',
                        iconSize: [32, 32],
                        iconAnchor: [16, 16]
                    }),
                    draggable: false  // NEW: Start as NOT draggable (will enable when editing)
                }).addTo(this.highlightsLayer);
                
                // Build media thumbnails HTML for this feature
                let mediaThumbnailsHTML = '';
                if (feature.media && feature.media.length > 0) {
                    const thumbnails = feature.media.map((media) => {
                        if (media.media_type === 'photo' && media.storage_path) {
                            return `
                                <div class="inline-block cursor-pointer hover:opacity-80 transition-opacity" 
                                     onclick="event.stopPropagation(); openMediaModal('{{ asset('storage/') }}/${media.storage_path}', 'photo', '${feature.name}')">
                                    <img src="{{ asset('storage/') }}/${media.storage_path}" 
                                         alt="${feature.name}"
                                         class="w-14 h-14 object-cover rounded border border-gray-200"
                                         title="Click to view">
                                </div>
                            `;
                        } else if (media.media_type === 'video_url' && media.video_url) {
                            let thumbUrl = '';
                            if (media.video_provider === 'youtube') {
                                const match = media.video_url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/);
                                if (match) thumbUrl = `https://img.youtube.com/vi/${match[1]}/mqdefault.jpg`;
                            }
                            
                            const thumbContent = thumbUrl 
                                ? `<img src="${thumbUrl}" class="w-14 h-14 object-cover rounded border border-gray-200">`
                                : `<div class="w-14 h-14 bg-gray-800 flex items-center justify-center rounded border border-gray-200">
                                       <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                           <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                       </svg>
                                   </div>`;
                            
                            return `
                                <div class="inline-block cursor-pointer hover:opacity-80 transition-opacity relative" 
                                     onclick="event.stopPropagation(); openMediaModal('${media.video_url}', 'video', '${feature.name}')">
                                    ${thumbContent}
                                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                        <div class="bg-white bg-opacity-90 rounded-full p-1 shadow">
                                            <svg class="w-3 h-3 text-gray-900" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                        return '';
                    }).filter(Boolean).join('');
                    
                    if (thumbnails) {
                        mediaThumbnailsHTML = `
                            <div class="flex flex-wrap gap-1.5 pt-2 border-t border-gray-100">
                                ${thumbnails}
                            </div>
                        `;
                    }
                }
                
                marker.bindPopup(`
                    <div class="min-w-[200px] space-y-2">
                        <div class="flex items-start gap-2">
                            <div style="background-color: ${feature.color || '#6366f1'};" class="w-6 h-6 rounded-md flex items-center justify-center text-white text-sm flex-shrink-0 mt-0.5">
                                ${feature.icon || '📍'}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-sm text-gray-900 leading-tight">${feature.name}</h4>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-700 mt-1 capitalize">
                                    ${feature.feature_type.replace(/_/g, ' ')}
                                </span>
                            </div>
                        </div>
                        ${feature.description ? `
                            <p class="text-xs text-gray-600 leading-relaxed border-t border-gray-100 pt-2">
                                ${feature.description}
                            </p>
                        ` : ''}
                        ${mediaThumbnailsHTML}
                    </div>
                `);

                marker.highlightId = feature.id;
                marker.isExisting = true;
                
                // NEW: Store marker reference for easy access
                this.highlightMarkers[`existing_${feature.id}`] = marker;
                
                // NEW: Add drag event listener (will only work when draggable is enabled)
                marker.on('dragend', (e) => {
                    const newLatLng = e.target.getLatLng();
                    this.updateExistingHighlightCoordinates(feature.id, newLatLng);
                });
            });
            
            this.updateHighlightsList();
        }

        deleteExistingFeature(featureId) {
            if (!confirm('Delete this feature?')) return;
            
            this.deletedFeatures.push(featureId);
            document.getElementById('deleted-features-input').value = JSON.stringify(this.deletedFeatures);
            
            // Remove from list
            this.existingHighlights = this.existingHighlights.filter(f => f.id !== featureId);
            
            // Reload map
            this.highlightsLayer.clearLayers();
            this.loadExistingFeatures();
            this.updateHighlightsList();
        }

        // NEW: Pre-populate coordinates from existing trail data
        prePopulateCoordinates() {
            const existingTrail = @json($trail);
            
            if (existingTrail && existingTrail.start_coordinates) {
                const startLatInput = document.getElementById('start-lat-input');
                const startLngInput = document.getElementById('start-lng-input');
                
                // Only set if elements exist
                if (startLatInput && startLngInput) {
                    const startCoords = existingTrail.start_coordinates;
                    startLatInput.value = startCoords[0] || startCoords.lat || '';
                    startLngInput.value = startCoords[1] || startCoords.lng || '';
                    console.log('Pre-populated start coords:', startCoords);
                }
            }
            
            if (existingTrail && existingTrail.end_coordinates) {
                const endLatInput = document.getElementById('end-lat-input');
                const endLngInput = document.getElementById('end-lng-input');
                
                // Only set if elements exist
                if (endLatInput && endLngInput) {
                    const endCoords = existingTrail.end_coordinates;
                    endLatInput.value = endCoords[0] || endCoords.lat || '';
                    endLngInput.value = endCoords[1] || endCoords.lng || '';
                    console.log('Pre-populated end coords:', endCoords);
                }
            }
            
            // Populate route coordinates if they exist
            if (existingTrail && existingTrail.route_coordinates) {
                const routeInput = document.getElementById('route-coordinates-input');
                
                if (routeInput) {
                    const routeCoords = typeof existingTrail.route_coordinates === 'string' 
                        ? JSON.parse(existingTrail.route_coordinates) 
                        : existingTrail.route_coordinates;
                    
                    if (Array.isArray(routeCoords) && routeCoords.length > 0) {
                        routeInput.value = JSON.stringify(routeCoords);
                        console.log('Pre-populated route with', routeCoords.length, 'points');
                    }
                }
            }
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
            document.querySelectorAll('.map-layer-option-card').forEach(btn => {
                if (btn.dataset.mapType === mapType) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
            
            // Close dropdown
            const dropdown = document.getElementById('map-layers-dropdown');
            if (dropdown) {
                dropdown.classList.add('hidden');
            }
        }

        setupEventListeners() {
            // Keep existing GPX import functionality
            const gpxInput = document.getElementById('gpx-import');
            if (gpxInput) {
                gpxInput.addEventListener('change', (e) => {
                    this.importGPX(e.target.files[0]);
                });
            }

            // Add clear route button
            this.addClearButton();

            document.getElementById('toggle-routing')?.addEventListener('click', () => {
                this.toggleRouting();
            });
            
            document.getElementById('undo-waypoint')?.addEventListener('click', () => {
                this.undoLastWaypoint();
            });

            document.getElementById('optimize-route')?.addEventListener('click', () => {
                this.optimizeRoute();
            });

            // Elevation and export controls
            document.getElementById('load-elevation')?.addEventListener('click', () => {
                this.loadElevationProfile();
            });

            // Map layer controls
            const layersToggle = document.getElementById('map-layers-toggle');
            const layersDropdown = document.getElementById('map-layers-dropdown');
            
            if (layersToggle && layersDropdown) {
                // Toggle dropdown on button click
                layersToggle.addEventListener('click', (e) => {
                    e.stopPropagation();
                    layersDropdown.classList.toggle('hidden');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', (e) => {
                    if (!layersDropdown.contains(e.target) && !layersToggle.contains(e.target)) {
                        layersDropdown.classList.add('hidden');
                    }
                });

                // Layer option clicks
                document.querySelectorAll('.map-layer-option-card').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const mapType = btn.dataset.mapType;
                        if (mapType) {
                            this.switchMapType(mapType);
                        }
                    });
                });
            }
        }

        setupMapClicks() {
            this.map.on('click', (e) => {
                // Check which mode is active
                if (this.highlightModeEnabled) {
                    // Highlight mode - place highlight marker
                    const highlightType = document.getElementById('highlight-type-select')?.value;
                    if (!highlightType) {
                        showToast('Please select a highlight type first', 'warning');
                        return;
                    }
                    this.placeHighlightMarker(e.latlng.lat, e.latlng.lng);
                } else if (this.waypointModeEnabled) {
                    // Waypoint mode - add waypoint
                    this.addWaypoint(e.latlng.lat, e.latlng.lng);
                } else {
                    // No mode enabled
                    showToast('Please enable waypoint or highlight mode first', 'warning');
                }
            });
        }

        enableWaypointMode() {
            this.waypointModeEnabled = true;
            const mapElement = document.getElementById('trail-map');
            if (mapElement) {
                mapElement.classList.add('waypoint-mode');
                mapElement.classList.remove('waypoint-disabled');
            }
        }

        disableWaypointMode() {
            this.waypointModeEnabled = false;
            const mapElement = document.getElementById('trail-map');
            if (mapElement) {
                mapElement.classList.remove('waypoint-mode');
                mapElement.classList.add('waypoint-disabled');
            }
        }

        addClearButton() {
            const mapContainer = document.getElementById('trail-map');
            const clearBtn = document.createElement('button');
            clearBtn.innerHTML = 'Clear Route';
            clearBtn.className = 'absolute top-2 right-2 z-10 bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600';
            clearBtn.onclick = () => this.clearRoute();
            mapContainer.style.position = 'relative';
            mapContainer.appendChild(clearBtn);
        }

        async addWaypoint(lat, lng) {
            const waypoint = { lat, lng, id: Date.now() };
            this.waypoints.push(waypoint);

            // Add waypoint marker
            const marker = L.marker([lat, lng], {
                icon: this.createWaypointIcon(this.waypoints.length),
                draggable: true
            }).addTo(this.routeLayer);

            marker.waypointId = waypoint.id;
            marker.on('dragend', (e) => {
                this.updateWaypoint(waypoint.id, e.target.getLatLng());
            });

            // Calculate route to this waypoint if not the first
            if (this.waypoints.length > 1) {
                await this.calculateRouteSegment(
                    this.waypoints[this.waypoints.length - 2],
                    waypoint
                );
            }

            this.updateFormInputs();
        }

        updateTrailSpecifications() {
            // Auto-detect difficulty based on distance and elevation
            const difficulty = this.calculateDifficulty();
            const difficultySelect = document.querySelector('select[name="difficulty_level"]');
            
            // Only update if element exists and we have a valid difficulty
            if (difficultySelect && difficulty) {
                difficultySelect.value = difficulty;
            }
        }

        calculateDifficulty() {
            if (this.totalDistance === 0) return null;
            
            const elevationGainEl = document.getElementById('elevation-gain');
            const elevationGain = elevationGainEl ? parseInt(elevationGainEl.textContent) || 0 : 0;
            
            // Simple difficulty calculation
            let difficulty = 1;
            
            if (this.totalDistance > 15 || elevationGain > 800) difficulty = 5;
            else if (this.totalDistance > 10 || elevationGain > 500) difficulty = 4;
            else if (this.totalDistance > 5 || elevationGain > 300) difficulty = 3;
            else if (this.totalDistance > 2 || elevationGain > 100) difficulty = 2;
            
            return difficulty;
        }

        async calculateRouteSegment(startWaypoint, endWaypoint) {
            const mapStatus = document.getElementById('map-status');
            
            if (mapStatus) {
                mapStatus.textContent = 'Calculating route...';
                mapStatus.className = 'text-xs text-blue-600 animate-pulse';
            }

            try {
                // Skip routing if smart routing is off
                if (!this.smartRouting) {
                    this.displayStraightLine(startWaypoint, endWaypoint);
                    if (mapStatus) {
                        mapStatus.textContent = 'Route created with direct line';
                        mapStatus.className = 'text-xs text-muted-foreground';
                    }
                    return;
                }

                const response = await fetch('/api/calculate-route', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        start_lat: startWaypoint.lat,
                        start_lng: startWaypoint.lng,
                        end_lat: endWaypoint.lat,
                        end_lng: endWaypoint.lng
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    this.displayRouteSegment(data, startWaypoint.id, endWaypoint.id);
                    if (mapStatus) {
                        mapStatus.textContent = 'Route calculated successfully';
                        mapStatus.className = 'text-xs text-green-600';
                    }
                } else {
                    const errorData = await response.json();
                    console.warn('Route calculation failed:', errorData);
                    
                    if (mapStatus) {
                        mapStatus.textContent = 'Using direct line (routing failed)';
                        mapStatus.className = 'text-xs text-amber-600';
                    }
                    
                    // Fallback to straight line
                    this.displayStraightLine(startWaypoint, endWaypoint);
                }
            } catch (error) {
                console.error('Error calculating route:', error);
                
                if (mapStatus) {
                    mapStatus.textContent = 'Network error - using direct line';
                    mapStatus.className = 'text-xs text-red-600';
                }
                
                this.displayStraightLine(startWaypoint, endWaypoint);
            }

            // Reset status after 3 seconds
            setTimeout(() => {
                if (mapStatus) {
                    mapStatus.className = 'text-xs text-muted-foreground';
                    this.updateUI();
                }
            }, 3000);
        }

        async loadElevationProfile() {
            if (this.routeSegments.length === 0) {
                return; // Silent return instead of alert
            }

            const fullRoute = this.getFullRouteCoordinates();
            if (fullRoute.length < 2) return;

            try {
                const response = await fetch('/api/elevation-profile', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        coordinates: fullRoute
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    this.displayElevationProfile(data);
                } else {
                    console.warn('Failed to load elevation profile');
                    // Hide elevation section if it fails
                    const chart = document.getElementById('elevation-chart');
                    const stats = document.getElementById('elevation-stats');
                    if (chart) chart.classList.add('hidden');
                    if (stats) stats.classList.add('hidden');
                }
            } catch (error) {
                console.error('Error loading elevation profile:', error);
            }
        }

        displayElevationProfile(elevationData) {
            console.log('Elevation data received');
            
            const chart = document.getElementById('elevation-chart');
            const stats = document.getElementById('elevation-stats');
            const canvas = document.getElementById('elevation-canvas');
            
            if (!canvas || !elevationData.geometry) {
                console.error('Missing canvas or geometry:', { canvas: !!canvas, geometry: !!elevationData.geometry });
                return;
            }

            // Show chart and stats
            chart.classList.remove('hidden');
            stats.classList.remove('hidden');

            const coordinates = elevationData.geometry.coordinates;
            console.log('Processing', coordinates.length, 'elevation points');

            // Calculate elevation statistics
            const elevations = coordinates.map(coord => coord[2]); // Third element is elevation
            const maxElev = Math.max(...elevations);
            const minElev = Math.min(...elevations);
            const totalGain = this.calculateElevationGain(coordinates);
            const totalLoss = this.calculateElevationLoss(coordinates);

            // Update stats display
            document.getElementById('max-elevation').textContent = Math.round(maxElev);
            document.getElementById('min-elevation').textContent = Math.round(minElev);
            document.getElementById('elevation-gain').textContent = Math.round(totalGain);
            document.getElementById('elevation-loss').textContent = Math.round(totalLoss);

            // Draw the elevation chart
            this.drawElevationChart(canvas, coordinates);
            
            console.log('Elevation profile displayed successfully');

            // Add this at the end of displayElevationProfile method:
            const elevationGainInput = document.querySelector('input[name="elevation_gain_m"]');
            const elevationDisplay = document.getElementById('route-elevation');

            if (elevationGainInput) {
                elevationGainInput.value = Math.round(totalGain);
            }
            if (elevationDisplay) {
                elevationDisplay.textContent = `${Math.round(totalGain)} m`;
            }

            // Auto-update trail specifications
            this.updateTrailSpecifications();
        }

        drawElevationChart(canvas, coordinates) {
            const ctx = canvas.getContext('2d');
            const width = canvas.width = canvas.offsetWidth;
            const height = canvas.height = canvas.offsetHeight;

            ctx.clearRect(0, 0, width, height);

            if (coordinates.length < 2) {
                return;
            }

            const elevations = coordinates.map(coord => coord[2]); // Get elevation values
            const minElev = Math.min(...elevations);
            const maxElev = Math.max(...elevations);
            const elevRange = maxElev - minElev || 1;

            console.log(`Drawing chart: ${elevations.length} points, elevation range: ${minElev}m to ${maxElev}m`);

            // Draw elevation line
            ctx.beginPath();
            ctx.strokeStyle = '#3B82F6';
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
            ctx.fillStyle = 'rgba(59, 130, 246, 0.1)';
            ctx.fill();
        }

        calculateElevationGain(coordinates) {
            let gain = 0;
            for (let i = 1; i < coordinates.length; i++) {
                const diff = coordinates[i][2] - coordinates[i-1][2];
                if (diff > 0) {
                    gain += diff;
                }
            }
            return gain;
        }

        calculateElevationLoss(coordinates) {
            let loss = 0;
            for (let i = 1; i < coordinates.length; i++) {
                const diff = coordinates[i-1][2] - coordinates[i][2];
                if (diff > 0) {
                    loss += diff;
                }
            }
            return loss;
        }

        getFullRouteCoordinates() {
            const fullRoute = [];
            this.routeSegments.forEach(segment => {
                const coords = fullRoute.length > 0 ? segment.coordinates.slice(1) : segment.coordinates;
                fullRoute.push(...coords);
            });
            return fullRoute;
        }

        displayRouteSegment(routeData, startId, endId) {
            console.log('Route data received:', routeData);
            
            if (!routeData.features || !routeData.features[0]) {
                console.error('Invalid route data format');
                return;
            }

            const geometry = routeData.features[0].geometry;
            let coordinates;
            
            // Handle encoded polyline (string) or coordinate array
            if (typeof geometry === 'string') {
                // Decode polyline string
                console.log('Decoding polyline string');
                const decoded = this.decodePolyline(geometry);
                coordinates = decoded; // Already in [lat, lng] format
            } else if (geometry && geometry.coordinates) {
                // Handle GeoJSON LineString format
                coordinates = geometry.coordinates.map(coord => [coord[1], coord[0]]);
            } else {
                console.error('Unknown geometry format:', geometry);
                return;
            }
            
            console.log('Processed coordinates:', coordinates.length, 'points');
            
            // Create the route line
            const routeLine = L.polyline(coordinates, {
                color: '#10B981',  // Green color to indicate successful routing
                weight: 4,
                opacity: 0.8
            }).addTo(this.routeLayer);

            const distance = routeData.features[0].properties.segments[0].distance || 0;

            this.routeSegments.push({
                startId,
                endId,
                line: routeLine,
                coordinates,
                distance
            });

            this.updateStats();
        }

        displayStraightLine(startWaypoint, endWaypoint) {
            const coordinates = [[startWaypoint.lat, startWaypoint.lng], [endWaypoint.lat, endWaypoint.lng]];
            
            const routeLine = L.polyline(coordinates, {
                color: '#EF4444',
                weight: 4,
                opacity: 0.6,
                dashArray: '10, 5'
            }).addTo(this.routeLayer);

            this.routeSegments.push({
                startId: startWaypoint.id,
                endId: endWaypoint.id,
                line: routeLine,
                coordinates,
                distance: L.latLng(startWaypoint.lat, startWaypoint.lng).distanceTo(L.latLng(endWaypoint.lat, endWaypoint.lng))
            });

            this.updateStats();
        }

        createWaypointIcon(number) {
            return L.divIcon({
                html: `<div class="rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm border-2 border-white shadow-lg text-white bg-blue-500">${number}</div>`,
                className: 'custom-marker',
                iconSize: [32, 32],
                iconAnchor: [16, 16]
            });
        }

        clearRoute() {
            this.routeLayer.clearLayers();
            this.waypoints = [];
            this.routeSegments = [];
            this.totalDistance = 0;
            this.totalTime = 0;
            this.updateStats();
            this.updateFormInputs();
        }

        updateStats() {
            this.totalDistance = this.routeSegments.reduce((sum, segment) => sum + (segment.distance / 1000), 0);
            this.totalTime = (this.totalDistance / 4) * 60; // Assume 4 km/h walking speed

            // Update display cards
            const distanceEl = document.getElementById('route-distance');
            const timeEl = document.getElementById('route-time');
            const waypointCountEl = document.getElementById('waypoint-count-display');
            
            if (distanceEl) distanceEl.textContent = `${this.totalDistance.toFixed(2)} km`;
            if (timeEl) timeEl.textContent = `${(this.totalTime / 60).toFixed(1)} hrs`;
            if (waypointCountEl) waypointCountEl.textContent = this.waypoints.length;

            // Update input fields (these should match the display cards)
            const distanceInput = document.querySelector('input[name="distance_km"]');
            const timeInput = document.querySelector('input[name="estimated_time_hours"]');
            
            if (distanceInput) distanceInput.value = this.totalDistance.toFixed(2);
            if (timeInput) timeInput.value = (this.totalTime / 60).toFixed(1);

            // Auto-load elevation profile when we have route segments
            if (this.routeSegments.length > 0) {
                this.loadElevationProfile();
            }

            this.updateUI();
        }

        updateFormInputs() {
            // Combine all segment coordinates into one route
            const fullRoute = [];
            this.routeSegments.forEach(segment => {
                // Skip first coordinate of segments after the first to avoid duplication
                const coords = fullRoute.length > 0 ? segment.coordinates.slice(1) : segment.coordinates;
                fullRoute.push(...coords);
            });

            const routeInput = document.getElementById('route-coordinates-input');
            if (routeInput) {
                routeInput.value = JSON.stringify(fullRoute);
            }

            // Set start/end coordinates
            if (this.waypoints.length > 0) {
                const start = this.waypoints[0];
                const end = this.waypoints[this.waypoints.length - 1];

                const startLatInput = document.getElementById('start-lat-input');
                const startLngInput = document.getElementById('start-lng-input');
                const endLatInput = document.getElementById('end-lat-input');
                const endLngInput = document.getElementById('end-lng-input');

                if (startLatInput) startLatInput.value = start.lat;
                if (startLngInput) startLngInput.value = start.lng;
                if (endLatInput) endLatInput.value = end.lat;
                if (endLngInput) endLngInput.value = end.lng;
            }
        }

        async updateWaypoint(waypointId, newLatLng) {
            // Find and update the waypoint
            const waypoint = this.waypoints.find(w => w.id === waypointId);
            if (!waypoint) return;

            waypoint.lat = newLatLng.lat;
            waypoint.lng = newLatLng.lng;

            // Recalculate affected route segments
            await this.recalculateRoutesForWaypoint(waypointId);
            this.updateFormInputs();
        }

        async recalculateRoutesForWaypoint(waypointId) {
            const waypointIndex = this.waypoints.findIndex(w => w.id === waypointId);
            if (waypointIndex === -1) return;

            // Remove old segments involving this waypoint
            this.routeSegments = this.routeSegments.filter(segment => {
                if (segment.startId === waypointId || segment.endId === waypointId) {
                    this.routeLayer.removeLayer(segment.line);
                    return false;
                }
                return true;
            });

            // Recalculate segments
            const waypoint = this.waypoints[waypointIndex];
            
            // Route to previous waypoint
            if (waypointIndex > 0) {
                await this.calculateRouteSegment(this.waypoints[waypointIndex - 1], waypoint);
            }
            
            // Route to next waypoint
            if (waypointIndex < this.waypoints.length - 1) {
                await this.calculateRouteSegment(waypoint, this.waypoints[waypointIndex + 1]);
            }
        }

        undoLastWaypoint() {
            if (this.waypoints.length === 0) return;

            const lastWaypoint = this.waypoints.pop();
            
            // Remove marker
            this.routeLayer.eachLayer(layer => {
                if (layer.waypointId === lastWaypoint.id) {
                    this.routeLayer.removeLayer(layer);
                }
            });

            // Remove route segments involving this waypoint
            this.routeSegments = this.routeSegments.filter(segment => {
                if (segment.startId === lastWaypoint.id || segment.endId === lastWaypoint.id) {
                    this.routeLayer.removeLayer(segment.line);
                    return false;
                }
                return true;
            });

            this.updateStats();
            this.updateFormInputs();
            this.updateUI();
        }

        toggleRouting() {
            this.smartRouting = !this.smartRouting;
            const button = document.getElementById('toggle-routing');
            const status = document.getElementById('routing-status');
            
            if (this.smartRouting) {
                button.innerHTML = '<svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>Smart Routing: ON';
                button.className = 'w-full inline-flex items-center justify-center rounded-md bg-blue-600 text-white hover:bg-blue-700 h-10 px-4 py-2 text-sm font-medium';
                status.textContent = 'Smart';
            } else {
                button.innerHTML = '<svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>Smart Routing: OFF';
                button.className = 'w-full inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 text-sm font-medium';
                status.textContent = 'Direct';
            }
        }

        updateUI() {
            const waypointCount = document.getElementById('waypoint-count');
            const mapStatus = document.getElementById('map-status');
            
            if (waypointCount) {
                waypointCount.textContent = this.waypoints.length;
            }
            
            if (mapStatus) {
                if (this.waypoints.length === 0) {
                    mapStatus.textContent = 'Click on the map to add your first waypoint';
                } else if (this.waypoints.length === 1) {
                    mapStatus.textContent = 'Add another waypoint to create a route';
                } else {
                    mapStatus.textContent = `Route created with ${this.waypoints.length} waypoints`;
                }
            }
        }

        transferHighlightFileToForm(mediaInput, index) {
            if (!mediaInput.files || !mediaInput.files[0]) return;
            
            const file = mediaInput.files[0];
            this.highlightFiles[index] = file;
            
            // Get the trail form (not the logout form!)
            const form = mediaInput.closest('form');
            
            // Remove any existing input with this name
            const existingInput = form.querySelector(`input[name="highlight_media_${index}"]`);
            if (existingInput) {
                existingInput.remove();
            }
            
            // Get the parent container of the current input
            const inputParent = mediaInput.parentElement;
            
            // Create a NEW input for the UI
            const newInput = document.createElement('input');
            newInput.type = 'file';
            newInput.id = 'highlight-media-input';
            newInput.accept = 'image/*';
            newInput.className = mediaInput.className;
            
            // Replace the current input with the new one in the UI
            inputParent.insertBefore(newInput, mediaInput);
            
            // Take the OLD input (which has the file) and move it to the form
            mediaInput.removeAttribute('id');
            mediaInput.name = `highlight_media_${index}`;
            mediaInput.className = 'highlight-media-input';
            mediaInput.style.display = 'none';
            
            // Append to the correct form
            form.appendChild(mediaInput);
        }

        transferHighlightVideoToForm(videoUrl, index) {
            if (!videoUrl) return;

            const form = document.querySelector('form[action*="trails"]') || document.querySelector('form');
            if (!form) return;

            // Remove existing input for this index if present
            const existing = form.querySelector(`input[name="highlight_video_url_${index}"]`);
            if (existing) existing.remove();

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `highlight_video_url_${index}`;
            input.value = videoUrl;
            input.className = 'highlight-video-input';
            form.appendChild(input);
        }

        // NEW: Prepare media files for submission
        prepareMediaFilesForSubmission() {
            // Files and videos are already transferred when highlights are added
            // Just update the highlights data JSON to include the indices
            this.updateHighlightsInput();
            
            // Log for debugging
            const form = document.querySelector('form[action*="trails"]');
            const highlightMediaInputs = form.querySelectorAll('.highlight-media-input');
            const highlightVideoInputs = form.querySelectorAll('.highlight-video-input');
            
            console.log('Media files prepared:', highlightMediaInputs.length, 'photos,', highlightVideoInputs.length, 'videos');
        }

        validateBeforeSubmit() {
            this.prepareMediaFilesForSubmission();
            const issues = this.validateRoute();

            const startLatInput = document.getElementById('start-lat-input');
            const startLngInput = document.getElementById('start-lng-input');

            if (!startLatInput.value || !startLngInput.value) {
                const existingTrail = @json($trail);
                if (existingTrail && existingTrail.start_coordinates) {
                    const startCoords = existingTrail.start_coordinates;
                    startLatInput.value = startCoords[0] || startCoords.lat || '';
                    startLngInput.value = startCoords[1] || startCoords.lng || '';
                }
            }
            
            if (issues.length > 0) {
                const message = 'Route validation issues:\n' + issues.map(issue => '• ' + issue).join('\n') + 
                            '\n\nDo you want to submit anyway?';
                return confirm(message);
            }
            
            return true;
        }

        optimizeRoute() {
            if (this.waypoints.length < 3) return;

            // Simple optimization - remove waypoints that are too close together
            const minDistance = 50; // meters
            let optimized = [this.waypoints[0]]; // Always keep first waypoint

            for (let i = 1; i < this.waypoints.length - 1; i++) {
                const prev = optimized[optimized.length - 1];
                const current = this.waypoints[i];
                const distance = L.latLng(prev.lat, prev.lng).distanceTo(L.latLng(current.lat, current.lng));
                
                if (distance >= minDistance) {
                    optimized.push(current);
                }
            }

            // Always keep last waypoint
            if (this.waypoints.length > 1) {
                optimized.push(this.waypoints[this.waypoints.length - 1]);
            }

            if (optimized.length !== this.waypoints.length) {
                this.waypoints = optimized;
                this.rebuildRoute();
            }
        }

        async rebuildRoute() {
            // Clear existing route
            this.routeLayer.clearLayers();
            this.routeSegments = [];

            // Rebuild markers
            this.waypoints.forEach((waypoint, index) => {
                const marker = L.marker([waypoint.lat, waypoint.lng], {
                    icon: this.createWaypointIcon(index + 1),
                    draggable: true
                }).addTo(this.routeLayer);

                marker.waypointId = waypoint.id;
                marker.on('dragend', (e) => {
                    this.updateWaypoint(waypoint.id, e.target.getLatLng());
                });
            });

            // Rebuild route segments
            for (let i = 0; i < this.waypoints.length - 1; i++) {
                await this.calculateRouteSegment(this.waypoints[i], this.waypoints[i + 1]);
            }

            this.updateStats();
            this.updateFormInputs();
        }

        validateRoute() {
            const issues = [];

            if (this.waypoints.length < 2) {
                issues.push('Route needs at least 2 waypoints');
            }

            if (this.totalDistance < 0.1) {
                issues.push('Route is too short (minimum 0.1km)');
            }

            if (this.totalDistance > 50) {
                issues.push('Route is very long (over 50km) - consider breaking into sections');
            }

            const duplicateWaypoints = this.findDuplicateWaypoints();
            if (duplicateWaypoints.length > 0) {
                issues.push(`${duplicateWaypoints.length} waypoints are too close together`);
            }

            return issues;
        }

        findDuplicateWaypoints() {
            const duplicates = [];
            const minDistance = 10; // meters

            for (let i = 0; i < this.waypoints.length; i++) {
                for (let j = i + 1; j < this.waypoints.length; j++) {
                    const distance = L.latLng(this.waypoints[i].lat, this.waypoints[i].lng)
                        .distanceTo(L.latLng(this.waypoints[j].lat, this.waypoints[j].lng));
                    
                    if (distance < minDistance) {
                        duplicates.push({ i, j, distance });
                    }
                }
            }

            return duplicates;
        }

        createWaypointsFromGPX(coordinates) {
            // Sample waypoints from the GPX track (take every 10th point or so)
            const sampleRate = Math.max(1, Math.floor(coordinates.length / 20)); // Max 20 waypoints
            
            for (let i = 0; i < coordinates.length; i += sampleRate) {
                const coord = coordinates[i];
                const waypoint = { lat: coord[0], lng: coord[1], id: Date.now() + i };
                this.waypoints.push(waypoint);
                
                // Add waypoint marker
                const marker = L.marker([coord[0], coord[1]], {
                    icon: this.createWaypointIcon(this.waypoints.length),
                    draggable: true
                }).addTo(this.routeLayer);
                
                marker.waypointId = waypoint.id;
                marker.on('dragend', (e) => {
                    this.updateWaypoint(waypoint.id, e.target.getLatLng());
                });
            }
            
            // Always include the last point
            if (coordinates.length > 1) {
                const lastCoord = coordinates[coordinates.length - 1];
                const lastWaypoint = { lat: lastCoord[0], lng: lastCoord[1], id: Date.now() + coordinates.length };
                this.waypoints.push(lastWaypoint);
                
                const marker = L.marker([lastCoord[0], lastCoord[1]], {
                    icon: this.createWaypointIcon(this.waypoints.length),
                    draggable: true
                }).addTo(this.routeLayer);
                
                marker.waypointId = lastWaypoint.id;
                marker.on('dragend', (e) => {
                    this.updateWaypoint(lastWaypoint.id, e.target.getLatLng());
                });
            }
        }

        displayGPXRoute(coordinates) {
            // Display the full GPX route as one segment
            const routeLine = L.polyline(coordinates, {
                color: '#10B981',  // Green color 
                weight: 4,
                opacity: 0.8
            }).addTo(this.routeLayer);
            
            // Calculate distance
            let distance = 0;
            for (let i = 1; i < coordinates.length; i++) {
                distance += L.latLng(coordinates[i-1]).distanceTo(L.latLng(coordinates[i]));
            }
            
            // Store as one large segment
            this.routeSegments.push({
                startId: this.waypoints[0]?.id,
                endId: this.waypoints[this.waypoints.length - 1]?.id,
                line: routeLine,
                coordinates: coordinates,
                distance: distance
            });
            
            // Fit map to route
            this.map.fitBounds(routeLine.getBounds(), { padding: [20, 20] });
            
            // Update stats
            this.updateStats();
        }

        loadExistingRoute(coordinates) {
            if (!coordinates || coordinates.length < 2) {
                console.warn('No valid route coordinates to load');
                return;
            }
            
            console.log('Loading existing route:', coordinates.length, 'points');
            
            // Clear any existing route first
            this.clearRoute();
            
            // Display the route
            this.displayGPXRoute(coordinates);
            
            // Create waypoints from the route
            this.createWaypointsFromGPX(coordinates);
            
            // Fit map to route bounds
            const bounds = L.latLngBounds(coordinates);
            this.map.fitBounds(bounds, { padding: [50, 50] });
            
            // Update the UI
            this.updateFormInputs();
            this.updateStats();
        }

        displayGPXFromBackend(coordinates) {
            // Method specifically for backend GPX data
            // Coordinates come from backend in [lat, lng] format
            
            if (!coordinates || coordinates.length < 2) {
                console.error('Invalid coordinates from backend');
                return;
            }
            
            console.log('Displaying backend GPX with', coordinates.length, 'points');
            
            // Create waypoints from coordinates (sample key points)
            this.createWaypointsFromGPX(coordinates);
            
            // Display the full route
            this.displayGPXRoute(coordinates);
            
            // Update form inputs
            this.updateFormInputs();
            
            // Update map status
            const mapStatus = document.getElementById('map-status');
            if (mapStatus) {
                mapStatus.textContent = `GPX loaded: ${coordinates.length} points`;
                mapStatus.className = 'text-xs text-green-600';
            }
        }

        // Keep existing GPX import method
        async importGPX(file) {
            if (!file) return;

            try {
                const text = await file.text();
                const parser = new DOMParser();
                const gpx = parser.parseFromString(text, 'text/xml');
                
                // Extract full track (not just waypoints)
                const tracks = gpx.querySelectorAll('trk');
                if (tracks.length === 0) {
                    alert('No tracks found in GPX file');
                    return;
                }

                // Clear existing route first
                this.clearRoute();
                
                // Process all track segments
                tracks.forEach(track => {
                    const segments = track.querySelectorAll('trkseg');
                    segments.forEach(segment => {
                        const points = segment.querySelectorAll('trkpt');
                        const coordinates = [];
                        
                        points.forEach(point => {
                            const lat = parseFloat(point.getAttribute('lat'));
                            const lon = parseFloat(point.getAttribute('lon'));
                            if (!isNaN(lat) && !isNaN(lon)) {
                                coordinates.push([lat, lon]);
                            }
                        });

                        if (coordinates.length > 1) {
                            // Create waypoints from GPX coordinates (sample key points)
                            this.createWaypointsFromGPX(coordinates);
                            
                            // Display the full route directly
                            this.displayGPXRoute(coordinates);
                        }
                    });
                });

                this.updateFormInputs();
                
            } catch (error) {
                console.error('Error importing GPX:', error);
                alert('Error importing GPX file. Please check the file format.');
            }
        }

        setupHighlightHandlers() {
            // Type selector change
            const typeSelect = document.getElementById('highlight-type-select');
            if (typeSelect) {
                typeSelect.addEventListener('change', (e) => {
                    const selectedOption = e.target.options[e.target.selectedIndex];
                    const icon = selectedOption.dataset.icon;
                    const color = selectedOption.dataset.color;
                    
                    if (icon) document.getElementById('highlight-icon-input').value = icon;
                    if (color) document.getElementById('highlight-color-input').value = color;
                });
            }

            // Add highlight button
            const addBtn = document.getElementById('add-highlight-btn');
            if (addBtn) {
                addBtn.addEventListener('click', () => this.addHighlightToList());
            }

            // Map click for highlights - override when in highlight mode
            const originalMapClick = this.map._events.click[0].fn;
            this.map.off('click');
            
            this.map.on('click', (e) => {
                const highlightType = document.getElementById('highlight-type-select').value;
                
                if (highlightType) {
                    // Highlight mode - place highlight marker
                    this.placeHighlightMarker(e.latlng.lat, e.latlng.lng);
                } else {
                    // Normal mode - add waypoint
                    originalMapClick.call(this, e);
                }
            });
        }

        placeHighlightMarker(lat, lng) {
            // Remove previous pending highlight
            if (this.pendingHighlight) {
                this.highlightsLayer.removeLayer(this.pendingHighlight);
            }

            const color = document.getElementById('highlight-color-input').value || '#10B981';
            const icon = document.getElementById('highlight-icon-input').value || '📍';

            this.pendingHighlight = L.marker([lat, lng], {
                icon: L.divIcon({
                    html: `<div style="background-color: ${color};" class="w-8 h-8 rounded-full border-2 border-white shadow-lg flex items-center justify-center text-white text-lg">${icon}</div>`,
                    className: 'custom-marker',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                })
            }).addTo(this.highlightsLayer);

            this.pendingHighlight.bindPopup('Click "Add Highlight" to save').openPopup();
            this.pendingHighlight.coordinates = [lat, lng];
        }

        addHighlightToList() {
            const type = document.getElementById('highlight-type-select').value;
            const name = document.getElementById('highlight-name-input').value;
            const description = document.getElementById('highlight-description-input').value;
            const icon = document.getElementById('highlight-icon-input').value;
            const color = document.getElementById('highlight-color-input').value;
            const mediaInput = document.getElementById('highlight-media-input');

            // Validation
            if (!type || !name) {
                alert('Please select a type and enter a name');
                return;
            }

            // CHECK IF EDITING OR ADDING NEW
            if (this.editingHighlightId !== null) {
                // Check if editing existing (from database) or new (not yet saved)
                if (this.editingExistingHighlight) {
                    // UPDATE EXISTING DATABASE HIGHLIGHT
                    this.updateExistingDatabaseHighlight();
                } else {
                    // UPDATE NEW HIGHLIGHT
                    this.updateExistingHighlight();
                }
            } else {
                // ADD NEW HIGHLIGHT
                if (!this.pendingHighlight) {
                    alert('Please click on the map to place the highlight');
                    return;
                }

                const videoUrlInput = document.getElementById('highlight-video-url-input');
                const videoUrl = videoUrlInput ? videoUrlInput.value.trim() : '';

                const highlightIndex = this.highlights.length; // Get index before adding

                const highlight = {
                    id: Date.now(),
                    type: type,
                    name: name,
                    description: description,
                    icon: icon,
                    color: color,
                    coordinates: this.pendingHighlight.coordinates,
                    mediaFile: mediaInput.files[0] || null,
                    videoUrl: videoUrl || null,
                    mediaIndex: highlightIndex,
                    videoIndex: highlightIndex
                };

                // Transfer file immediately if exists
                if (mediaInput.files[0]) {
                    this.transferHighlightFileToForm(mediaInput, highlightIndex);
                }

                // Transfer video URL immediately if exists
                if (videoUrl) {
                    this.transferHighlightVideoToForm(videoUrl, highlightIndex);
                }

                this.highlights.push(highlight);

                // Remove pending marker
                if (this.pendingHighlight) {
                    this.highlightsLayer.removeLayer(this.pendingHighlight);
                    this.pendingHighlight = null;
                }

                // Add permanent DRAGGABLE marker
                const marker = L.marker(highlight.coordinates, {
                    icon: L.divIcon({
                        html: `<div style="background-color: ${highlight.color};" class="w-8 h-8 rounded-full border-2 border-white shadow-lg flex items-center justify-center text-white text-lg">${highlight.icon}</div>`,
                        className: 'custom-marker',
                        iconSize: [32, 32],
                        iconAnchor: [16, 16]
                    }),
                    draggable: true  // Make marker draggable
                }).addTo(this.highlightsLayer);

                marker.bindPopup(`<b>${highlight.name}</b><br>${highlight.description || ''}`);
                marker.highlightId = highlight.id;

                // Store marker reference for easy access
                this.highlightMarkers[highlight.id] = marker;

                // Add drag event listener to update coordinates
                marker.on('dragend', (e) => {
                    const newLatLng = e.target.getLatLng();
                    this.updateHighlightCoordinates(highlight.id, newLatLng);
                });

                showToast('Highlight added successfully!', 'success');
            }

            // Update UI
            this.updateHighlightsList();
            this.updateHighlightsInput();
            this.resetHighlightForm();
        }

        updateHighlightsList() {
            const listContainer = document.getElementById('highlights-list');
            const countSpan = document.getElementById('highlights-count');
            
            if (!listContainer) return;

            countSpan.textContent = this.existingHighlights.length + this.highlights.length;

            // Get existing highlights (features) with media display
            const existingHTML = this.existingHighlights.map(h => `
                <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                    <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white shrink-0">
                        ${h.icon || '📍'}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h5 class="font-medium text-sm">${h.name}</h5>
                        <p class="text-xs text-muted-foreground capitalize">${(h.feature_type || '').replace('_', ' ')}</p>
                        ${h.description ? `<p class="text-xs text-gray-600 mt-1">${h.description}</p>` : ''}
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <button type="button" onclick="window.trailBuilder.editExistingHighlight(${h.id})" class="text-blue-600 hover:text-blue-800" title="Edit highlight">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <button type="button" onclick="window.trailBuilder.removeExistingHighlight(${h.id})" class="text-red-500 hover:text-red-700" title="Delete highlight">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            `).join('');

            // Get new highlights with media support
            const newHighlightsHTML = this.highlights.map(h => `
                <div class="flex items-start gap-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                    <div style="background-color: ${h.color};" class="w-8 h-8 rounded-full flex items-center justify-center text-white shrink-0">
                        ${h.icon}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h5 class="font-medium text-sm">${h.name}</h5>
                        <p class="text-xs text-muted-foreground capitalize">${h.type.replace('_', ' ')}</p>
                        ${h.description ? `<p class="text-xs text-gray-600 mt-1">${h.description}</p>` : ''}
                        ${h.mediaFile ? `
                            <div class="mt-2 flex items-center gap-2 text-xs text-green-600 bg-green-50 px-2 py-1 rounded">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="truncate">${h.mediaFile.name}</span>
                            </div>
                        ` : ''}
                    </div>
                    <button type="button" onclick="window.trailBuilder.editHighlight(${h.id})" class="text-blue-600 hover:text-blue-800" title="Edit highlight">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    <button type="button" onclick="window.trailBuilder.removeHighlight(${h.id})" class="text-red-500 hover:text-red-700" title="Delete highlight">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            `).join('');

            if (existingHTML === '' && newHighlightsHTML === '') {
                listContainer.innerHTML = '<p class="text-sm text-muted-foreground text-center py-8">No highlights added yet</p>';
            } else {
                listContainer.innerHTML = existingHTML + newHighlightsHTML;
            }
        }

        removeHighlight(id) {
            this.highlights = this.highlights.filter(h => h.id !== id);
            
            // Remove marker from map using our marker map
            const marker = this.highlightMarkers[id];
            if (marker) {
                this.highlightsLayer.removeLayer(marker);
                delete this.highlightMarkers[id];
            }
            
            // If we were editing this highlight, reset form
            if (this.editingHighlightId === id && !this.editingExistingHighlight) {
                this.resetHighlightForm();
            }

            this.updateHighlightsList();
            this.updateHighlightsInput();
            showToast('Highlight removed', 'info');
        }

        removeExistingHighlight(id) {
            if (!confirm('Are you sure you want to delete this highlight? This action cannot be undone.')) {
                return;
            }
            
            // Remove from existingHighlights array
            this.existingHighlights = this.existingHighlights.filter(h => h.id !== id);
            
            // Remove marker from map
            const marker = this.highlightMarkers[`existing_${id}`];
            if (marker) {
                this.highlightsLayer.removeLayer(marker);
                delete this.highlightMarkers[`existing_${id}`];
            }
            
            // Remove from edited list if it was being edited
            if (this.editedExistingHighlights[id]) {
                delete this.editedExistingHighlights[id];
                this.updateEditedExistingHighlightsInput();
            }
            
            // Add to deleted features list (for form submission)
            const deletedInput = document.getElementById('deleted-features-input');
            if (deletedInput) {
                let deletedIds = [];
                try {
                    deletedIds = JSON.parse(deletedInput.value) || [];
                } catch (e) {
                    deletedIds = [];
                }
                
                if (!deletedIds.includes(id)) {
                    deletedIds.push(id);
                }
                
                deletedInput.value = JSON.stringify(deletedIds);
            }
            
            // If we were editing this highlight, reset form
            if (this.editingHighlightId === id && this.editingExistingHighlight) {
                this.resetHighlightForm();
            }
            
            this.updateHighlightsList();
            showToast('Existing highlight will be deleted when you save the form', 'info');
        }

        updateHighlightCoordinates(id, latlng) {
            // Find and update the highlight's coordinates
            const highlight = this.highlights.find(h => h.id === id);
            if (!highlight) return;
            
            // Update coordinates
            highlight.coordinates = [latlng.lat, latlng.lng];
            
            // Update hidden input
            this.updateHighlightsInput();
            
            // Show success message
            showToast('Marker position updated', 'success');
        }

        updateExistingHighlightCoordinates(id, latlng) {
            // Find the existing highlight and update its coordinates
            const highlight = this.existingHighlights.find(h => h.id === id);
            if (!highlight) return;
            
            // Update coordinates in the existingHighlights array
            highlight.coordinates = [latlng.lat, latlng.lng];
            
            // Track this as an edited existing highlight
            if (!this.editedExistingHighlights) {
                this.editedExistingHighlights = {};
            }
            
            // Store the updated data
            this.editedExistingHighlights[id] = {
                id: id,
                coordinates: [latlng.lat, latlng.lng],
                // We'll add more fields when user clicks "Update"
            };
            
            // Update hidden input
            this.updateEditedExistingHighlightsInput();
            
            showToast('Existing highlight position updated', 'success');
        }

        editHighlight(id) {
            // Find the highlight in the array
            const highlight = this.highlights.find(h => h.id === id);
            if (!highlight) return;
            
            // Set editing mode
            this.editingHighlightId = id;
            this.editingExistingHighlight = false; 
            
            // Populate the form with existing data
            document.getElementById('highlight-type-select').value = highlight.type;
            document.getElementById('highlight-name-input').value = highlight.name;
            document.getElementById('highlight-description-input').value = highlight.description || '';
            document.getElementById('highlight-icon-input').value = highlight.icon;
            document.getElementById('highlight-color-input').value = highlight.color;
            
            // Change button text and style to "Update"
            const addBtn = document.getElementById('add-highlight-btn');
            addBtn.innerHTML = `
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Update Highlight
            `;
            addBtn.classList.remove('bg-primary', 'hover:bg-primary/90');
            addBtn.classList.add('bg-green-600', 'hover:bg-green-700');
            
            // Scroll to form
            document.getElementById('highlight-form-section').scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
            
            // Highlight the marker on map (visual feedback)
            const marker = this.highlightMarkers[id];
            if (marker) {
                marker.openPopup();
                this.map.setView(marker.getLatLng(), this.map.getZoom());
            }
            
            // Show toast notification
            showToast('Editing highlight. You can drag the marker to reposition it.', 'info');
        }

        editExistingHighlight(id) {
            // Find the existing highlight in the array
            const highlight = this.existingHighlights.find(h => h.id === id);
            if (!highlight) return;
            
            // Set editing mode for existing highlight
            this.editingHighlightId = id;
            this.editingExistingHighlight = true;  // Flag to know we're editing existing, not new
            
            // Populate the form with existing data
            document.getElementById('highlight-type-select').value = highlight.feature_type || highlight.type;
            document.getElementById('highlight-name-input').value = highlight.name;
            document.getElementById('highlight-description-input').value = highlight.description || '';
            document.getElementById('highlight-icon-input').value = highlight.icon || '📍';
            document.getElementById('highlight-color-input').value = highlight.color || '#6366f1';
            
            // Change button text and style to "Update"
            const addBtn = document.getElementById('add-highlight-btn');
            addBtn.innerHTML = `
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Update Existing Highlight
            `;
            addBtn.classList.remove('bg-primary', 'hover:bg-primary/90');
            addBtn.classList.add('bg-green-600', 'hover:bg-green-700');
            
            // Make the marker draggable
            const marker = this.highlightMarkers[`existing_${id}`];
            if (marker) {
                marker.dragging.enable();
                marker.openPopup();
                this.map.setView(marker.getLatLng(), this.map.getZoom());
            }
            
            // Scroll to form
            document.getElementById('highlight-form-section').scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
            
            showToast('Editing existing highlight. Drag the marker to reposition it.', 'info');
        }

        updateExistingHighlight() {
            const id = this.editingHighlightId;
            const highlight = this.highlights.find(h => h.id === id);
            if (!highlight) return;
            
            // Update highlight data
            highlight.type = document.getElementById('highlight-type-select').value;
            highlight.name = document.getElementById('highlight-name-input').value;
            highlight.description = document.getElementById('highlight-description-input').value;
            highlight.icon = document.getElementById('highlight-icon-input').value;
            highlight.color = document.getElementById('highlight-color-input').value;
            
            // Handle new media upload (if user changed it)
            const mediaInput = document.getElementById('highlight-media-input');
            if (mediaInput.files[0]) {
                highlight.mediaFile = mediaInput.files[0];
            }
            // Transfer file input into form so the backend receives it on submit
            if (mediaInput && mediaInput.files[0]) {
                const highlightIndex = this.highlights.length;
                this.transferHighlightFileToForm(mediaInput, highlightIndex);
                // store index reference on the highlight for server-side mapping
                highlight.mediaIndex = highlightIndex;
            }
            
            // Handle video URL
            const videoUrlInput = document.getElementById('highlight-video-url-input');
            if (videoUrlInput) {
                highlight.videoUrl = videoUrlInput.value.trim() || null;
            }

            // Transfer video URL into form as hidden input so backend can read it
            if (videoUrlInput && videoUrlInput.value.trim()) {
                const highlightIndex = this.highlights.length;
                this.transferHighlightVideoToForm(videoUrlInput.value.trim(), highlightIndex);
                highlight.videoIndex = highlightIndex;
            }
            
            // Update marker appearance on map
            const marker = this.highlightMarkers[id];
            if (marker) {
                marker.setIcon(L.divIcon({
                    html: `<div style="background-color: ${highlight.color};" class="w-8 h-8 rounded-full border-2 border-white shadow-lg flex items-center justify-center text-white text-lg">${highlight.icon}</div>`,
                    className: 'custom-marker',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                }));
                
                // Update popup
                marker.bindPopup(`<b>${highlight.name}</b><br>${highlight.description || ''}`);
            }
            
            // Clear editing mode
            this.editingHighlightId = null;
            
            showToast('Highlight updated successfully!', 'success');
        }

        updateExistingDatabaseHighlight() {
            const id = this.editingHighlightId;
            const highlight = this.existingHighlights.find(h => h.id === id);
            if (!highlight) return;
            
            // Get form values
            const type = document.getElementById('highlight-type-select').value;
            const name = document.getElementById('highlight-name-input').value;
            const description = document.getElementById('highlight-description-input').value;
            const icon = document.getElementById('highlight-icon-input').value;
            const color = document.getElementById('highlight-color-input').value;

            // Handle new media upload (if user changed it)
            const mediaInput = document.getElementById('highlight-media-input');
            if (mediaInput.files[0]) {
                highlight.mediaFile = mediaInput.files[0];
            }
            
            // Handle video URL
            const videoUrlInput = document.getElementById('highlight-video-url-input');
            if (videoUrlInput) {
                highlight.videoUrl = videoUrlInput.value.trim() || null;
            }
            
            // Update the highlight in the existingHighlights array
            highlight.feature_type = type;
            highlight.name = name;
            highlight.description = description;
            highlight.icon = icon;
            highlight.color = color;

            const highlightIndex = this.highlights.length;
            
            // Store in editedExistingHighlights for form submission
            this.editedExistingHighlights[id] = {
                id: id,
                feature_type: type,
                name: name,
                description: description,
                icon: icon,
                color: color,
                coordinates: highlight.coordinates,
                videoUrl: highlight.videoUrl,
                mediaIndex: highlightIndex,  // Set index immediately
                videoIndex: highlightIndex   // Set index immediately
            };

            console.log(this.editedExistingHighlights);

            // IMPORTANT: Transfer the actual file input to the form
            if (mediaInput.files[0]) {
                this.transferHighlightFileToForm(mediaInput, highlightIndex);
            }
            
            // Update marker appearance on map
            const marker = this.highlightMarkers[`existing_${id}`];
            if (marker) {
                marker.setIcon(L.divIcon({
                    html: `<div style="background-color: ${color};" class="w-8 h-8 rounded-full border-2 border-white shadow-lg flex items-center justify-center text-white text-lg">${icon}</div>`,
                    className: 'custom-marker',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                }));
                
                // Update popup
                marker.bindPopup(`
                    <div class="min-w-[200px] space-y-2">
                        <div class="flex items-start gap-2">
                            <div style="background-color: ${color || '#6366f1'};" class="w-6 h-6 rounded-md flex items-center justify-center text-white text-sm flex-shrink-0 mt-0.5">
                                ${icon || '📍'}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-sm text-gray-900 leading-tight">${name}</h4>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-700 mt-1 capitalize">
                                    ${type.replace(/_/g, ' ')}
                                </span>
                            </div>
                        </div>
                        ${description ? `
                            <p class="text-xs text-gray-600 leading-relaxed border-t border-gray-100 pt-2">
                                ${description}
                            </p>
                        ` : ''}
                    </div>
                `);
                
                // Disable dragging again
                marker.dragging.disable();
            }
            
            // Update hidden input
            this.updateEditedExistingHighlightsInput();
            
            // Clear editing mode
            this.editingHighlightId = null;
            this.editingExistingHighlight = false;
            
            showToast('Existing highlight updated! Save the form to apply changes.', 'success');
        }

        resetHighlightForm() {
            // Clear all form fields
            document.getElementById('highlight-type-select').value = '';
            document.getElementById('highlight-name-input').value = '';
            document.getElementById('highlight-description-input').value = '';
            document.getElementById('highlight-icon-input').value = '';
            document.getElementById('highlight-color-input').value = '#10B981';
            document.getElementById('highlight-media-input').value = '';
            
            const mediaPreview = document.getElementById('highlight-media-preview');
            if (mediaPreview) mediaPreview.classList.add('hidden');
            
            const videoUrlInput = document.getElementById('highlight-video-url-input');
            if (videoUrlInput) {
                videoUrlInput.value = '';
                const videoPreview = document.getElementById('highlight-video-preview');
                if (videoPreview) videoPreview.classList.add('hidden');
            }
            
            // Reset button to "Add" mode
            const addBtn = document.getElementById('add-highlight-btn');
            addBtn.innerHTML = `
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Highlight
            `;
            addBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
            addBtn.classList.add('bg-primary', 'hover:bg-primary/90');
            
            // NEW: If we were editing an existing highlight, disable marker dragging
            if (this.editingExistingHighlight && this.editingHighlightId !== null) {
                const marker = this.highlightMarkers[`existing_${this.editingHighlightId}`];
                if (marker && marker.dragging) {
                    marker.dragging.disable();
                }
            }

            // Clear editing mode
            this.editingHighlightId = null;
            this.editingExistingHighlight = false;
        }

        updateHighlightsInput() {
            const input = document.getElementById('highlights-data-input');
            if (input) {
                input.value = JSON.stringify(this.highlights);
            }
        }
        
        updateEditedExistingHighlightsInput() {
            const input = document.getElementById('edited-highlights-input');
            if (input) {
                input.value = JSON.stringify(this.editedExistingHighlights);
            }
        }
    }

    // Photo Upload Handler
    class PhotoUploadManager {
        constructor() {
            this.uploadZone = document.getElementById('photo-upload-zone');
            this.fileInput = document.getElementById('photo-input');
            this.previewGrid = document.getElementById('photo-preview-grid');
            this.previewsContainer = document.getElementById('photo-previews');
            this.uploadPrompt = document.getElementById('upload-prompt');
            this.photoCount = document.getElementById('photo-count');
            this.photos = [];
            this.videos = [];
            
            // Initialize missing properties
            this.existingPhotos = @json($trail->media ?? []); // Load existing photos from backend
            this.deletedPhotos = [];
            this.maxPhotos = 10;
            this.maxTotal = 10; // Total media items (photos + videos)
            
            // NEW: Check if there's already a featured photo in existing photos
            const hasFeaturedPhoto = this.existingPhotos.some(photo => photo.is_featured);
            // Default to -1 (no new photo selected). Existing featured photos (from DB) are tracked separately.
            this.featuredIndex = -1; // -1 means no new photo is featured
            this.existingHasFeatured = hasFeaturedPhoto;
            
            // Only initialize if all required elements exist
            if (this.uploadZone && this.fileInput) {
                this.init();
            } else {
                console.warn('Photo upload manager: Required elements not found');
            }
        }

        hasFeaturedPhoto() {
            // Check if any existing photo is featured
            const existingFeatured = this.existingPhotos.some(photo => photo.is_featured);
            // Check if any new photo is featured
            const newFeatured = this.featuredIndex !== -1;
            
            return existingFeatured || newFeatured;
        }

        init() {
            // Check if elements exist before adding listeners
            if (!this.uploadZone || !this.fileInput) {
                console.warn('Photo upload elements not found. Photo upload disabled.');
                return;
            }
            
            // Click to upload
            this.uploadZone.addEventListener('click', (e) => {
                if (e.target.closest('#clear-photos')) return;
                if (this.fileInput) {
                    this.fileInput.click();
                }
            });
            
            // File input change
            if (this.fileInput) {
                this.fileInput.addEventListener('change', (e) => this.handleFiles(e.target.files));
            }
            
            // Drag and drop
            this.uploadZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                this.uploadZone.classList.add('border-primary', 'bg-primary/5');
            });
            
            this.uploadZone.addEventListener('dragleave', (e) => {
                e.preventDefault();
                this.uploadZone.classList.remove('border-primary', 'bg-primary/5');
            });
            
            this.uploadZone.addEventListener('drop', (e) => {
                e.preventDefault();
                this.uploadZone.classList.remove('border-primary', 'bg-primary/5');
                this.handleFiles(e.dataTransfer.files);
            });
            
            // Clear all button
            const clearBtn = document.getElementById('clear-photos');
            if (clearBtn) {
                clearBtn.addEventListener('click', () => this.clearAll());
            }
            
            // Add video URL button
            const addVideoBtn = document.getElementById('add-video-url-btn');
            const videoUrlInput = document.getElementById('trail-video-url-input');
            
            if (addVideoBtn && videoUrlInput) {
                addVideoBtn.addEventListener('click', () => {
                    const url = videoUrlInput.value.trim();
                    if (url) {
                        this.addVideoUrl(url);
                        videoUrlInput.value = '';
                    } else {
                        alert('Please enter a valid video URL');
                    }
                });
                
                // Allow Enter key to add video
                videoUrlInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        addVideoBtn.click();
                    }
                });
            }
        }

        addVideoUrl(url) {
            const totalMedia = this.existingPhotos.length + this.photos.length + this.videos.length;
            
            if (totalMedia >= this.maxTotal) {
                alert(`Maximum of ${this.maxTotal} media items (photos + videos) reached`);
                return;
            }
            
            // Validate URL format
            const embedUrl = this.getVideoEmbedUrl(url);
            if (!embedUrl) {
                alert('Please enter a valid YouTube or Vimeo URL');
                return;
            }
            
            const video = {
                url: url,
                embedUrl: embedUrl,
                id: Date.now() + Math.random(),
                type: 'video'
            };
            
            this.videos.push(video);
            this.render();
        }

        getVideoEmbedUrl(url) {
            // YouTube
            let match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/);
            if (match) {
                return `https://www.youtube.com/embed/${match[1]}`;
            }
            
            // Vimeo
            match = url.match(/vimeo\.com\/(\d+)/);
            if (match) {
                return `https://player.vimeo.com/video/${match[1]}`;
            }
            
            return null;
        }

        removeVideo(id) {
            const index = this.videos.findIndex(v => v.id === id);
            if (index === -1) return;
            
            this.videos.splice(index, 1);
            this.render();
        }

        playVideo(embedUrl) {
            // Create modal
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50';
            modal.innerHTML = `
                <div class="relative w-full max-w-4xl mx-4">
                    <button onclick="this.closest('.fixed').remove()" 
                        class="absolute -top-10 right-0 text-white hover:text-gray-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    <div class="relative" style="padding-bottom: 56.25%;">
                        <iframe src="${embedUrl}" 
                            class="absolute top-0 left-0 w-full h-full rounded-lg" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                        </iframe>
                    </div>
                </div>
            `;
            
            // Close on click outside
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.remove();
                }
            });
            
            // Close on Escape key
            const escHandler = (e) => {
                if (e.key === 'Escape') {
                    modal.remove();
                    document.removeEventListener('keydown', escHandler);
                }
            };
            document.addEventListener('keydown', escHandler);
            
            document.body.appendChild(modal);
        }

        handleFiles(files) {
            const totalPhotos = this.existingPhotos.length + this.photos.length;
            const remainingSlots = this.maxPhotos - totalPhotos;
            
            if (files.length > remainingSlots) {
                alert(`You can only upload ${remainingSlots} more photo(s). Maximum is ${this.maxPhotos} photos total.`);
                return;
            }

            Array.from(files).forEach(file => {
                if (!file.type.startsWith('image/')) {
                    alert(`${file.name} is not an image file`);
                    return;
                }

                if (file.size > 10 * 1024 * 1024) {
                    alert(`${file.name} is too large. Maximum size is 10MB`);
                    return;
                }

                this.addPhoto(file);
            });
        }

       renderExistingPhotos() {
            if (!this.existingPhotos || this.existingPhotos.length === 0) return;
            
            const previewGrid = document.getElementById('photo-preview-grid');
            const previewsContainer = document.getElementById('photo-previews');
            
            if (!previewsContainer) return;
            
            previewGrid.classList.remove('hidden');
            
            const existingHTML = this.existingPhotos.map(media => {
                // Fix photo URL - TrailMedia uses storage_path instead of path
                const photoUrl = media.url || `/storage/${media.storage_path}`;
                
                return `
                    <div class="relative group photo-preview-item" data-photo-id="${media.id}">
                        <img src="${photoUrl}" alt="${media.caption || 'Trail photo'}" 
                            onerror="this.src='/images/placeholder.jpg'" 
                            class="w-full h-32 object-cover rounded-lg border-2 ${media.is_featured ? 'border-yellow-400' : 'border-gray-200'}">
                        
                        ${media.is_featured ? `
                            <div class="absolute top-2 left-2">
                                <span class="inline-flex items-center rounded-full bg-yellow-400 px-2 py-1 text-xs font-medium text-yellow-900">
                                    ⭐ Featured
                                </span>
                            </div>
                        ` : ''}
                        
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-200 rounded-lg flex items-center justify-center gap-2">
                            <button type="button" onclick="window.photoManager.deleteExistingPhoto(${media.id})" 
                                    class="opacity-0 group-hover:opacity-100 transition-opacity bg-red-600 text-white rounded-full p-2 hover:bg-red-700"
                                    title="Delete photo">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
            
            previewsContainer.innerHTML = existingHTML;
        }

        deleteExistingPhoto(photoId) {
            if (!confirm('Delete this photo? This cannot be undone.')) return;
            
            // Add to deleted list
            this.deletedPhotos.push(photoId);
            document.getElementById('deleted-photos-input').value = JSON.stringify(this.deletedPhotos);
            
            // Remove from array
            this.existingPhotos = this.existingPhotos.filter(p => p.id !== photoId);
            
            // Just hide the element visually (don't re-render)
            const mediaElements = document.querySelectorAll(`[data-media-id="${photoId}"]`);
            mediaElements.forEach(el => {
                el.style.transition = 'opacity 0.3s';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 300);
            });
            
            // Update the new photos preview only
            this.render();
        }

        canAddMore() {
            const totalPhotos = this.existingPhotos.length + this.photos.length;
            return totalPhotos < this.maxPhotos;
        }

        addPhoto(file) {
            const reader = new FileReader();
            
            reader.onload = (e) => {
                const photo = {
                    file: file,
                    dataUrl: e.target.result,
                    id: Date.now() + Math.random()
                };

                this.photos.push(photo);

                // Auto-feature the first newly added photo only if there is no featured photo already
                if (this.featuredIndex === -1 && !this.hasFeaturedPhoto()) {
                    // featuredIndex is the index within this.photos
                    this.featuredIndex = this.photos.length - 1;
                }

                this.render();
            };

            reader.readAsDataURL(file);
        }

        removePhoto(index) {
            if (index < 0 || index >= this.photos.length) return;

            this.photos.splice(index, 1);
            
            // Adjust featured index if needed
            if (this.featuredIndex === index) {
                // If we're deleting the featured photo, reset to -1 (no featured)
                this.featuredIndex = -1;
            } else if (this.featuredIndex > index) {
                // If featured photo is after the deleted one, adjust the index
                this.featuredIndex--;
            }

            this.render();
        }

        setFeatured(index) {
            // index refers to position inside this.photos (new uploads)
            if (index < 0 || index >= this.photos.length) {
                // Trying to feature a non-photo (video) - prevent this
                alert('Videos cannot be set as featured. Please choose a photo.');
                return;
            }

            // If there is already a featured photo persisted on the trail, require removal first
            const existingFeatured = this.existingPhotos.some(p => p.is_featured);
            if (existingFeatured) {
                showToast('Remove the current featured photo first', 'warning');
                return;
            }

            this.featuredIndex = index;
            this.render();
        }

        clearAll() {
            if (this.photos.length === 0) return;
            
            if (confirm('Remove all photos?')) {
                this.photos = [];
                this.featuredIndex = 0;
                this.render();
            }
        }

        render() {
            const previewGrid = document.getElementById('photo-preview-grid');
            const previewsContainer = document.getElementById('photo-previews');
            const photoCount = document.getElementById('photo-count');
            const uploadPrompt = document.getElementById('upload-prompt');
            const uploadZone = document.getElementById('photo-upload-zone');

            // Safety check - if essential elements don't exist, don't render
            if (!previewsContainer) {
                console.warn('Preview container not found, skipping render');
                return;
            }

            const totalMedia = this.photos.length + this.videos.length;

            // Update count
            if (photoCount) {
                photoCount.textContent = totalMedia;
            }

            // Show/hide sections
            if (totalMedia > 0) {
                if (previewGrid) previewGrid.classList.remove('hidden');
                if (uploadPrompt) uploadPrompt.classList.add('hidden');
                if (uploadZone) uploadZone.classList.add('border-solid');
            } else {
                if (previewGrid) previewGrid.classList.add('hidden');
                if (uploadPrompt) uploadPrompt.classList.remove('hidden');
                if (uploadZone) uploadZone.classList.remove('border-solid');
            }

            // Combine photos and videos for rendering
            const allMedia = [];
            
            // Add photos
            this.photos.forEach((photo, index) => {
                allMedia.push({
                    type: 'photo',
                    data: photo,
                    index: index,
                    globalIndex: index
                });
            });
            
            // Add videos
            this.videos.forEach((video, index) => {
                allMedia.push({
                    type: 'video',
                    data: video,
                    index: index,
                    globalIndex: this.photos.length + index
                });
            });

            // Render previews
            if (!previewsContainer) {
                console.warn('Preview container not found');
                return;
            }

            // Render previews
            previewsContainer.innerHTML = allMedia.map((item) => {
                if (item.type === 'photo') {
                    const photo = item.data;
                    const index = item.index;
                    const globalIndex = item.globalIndex;
                    
                    return `
                        <div class="relative group photo-preview-item" data-photo-id="${photo.id}">
                            <img src="${photo.dataUrl}" alt="Preview ${index + 1}" 
                                class="w-full h-32 object-cover rounded-lg border-2 ${globalIndex === this.featuredIndex ? 'border-yellow-400' : 'border-gray-200'}">
                            
                            ${globalIndex === this.featuredIndex ? `
                                <div class="absolute top-2 left-2">
                                    <span class="inline-flex items-center rounded-full bg-yellow-400 px-2 py-1 text-xs font-medium text-yellow-900">
                                        ⭐ Featured
                                    </span>
                                </div>
                            ` : ''}
                            
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-200 rounded-lg flex items-center justify-center gap-2">
                                <button type="button" onclick="window.photoManager.setFeatured(${globalIndex})" 
                                    class="opacity-0 group-hover:opacity-100 bg-yellow-400 hover:bg-yellow-500 text-yellow-900 p-2 rounded-full transition-all">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </button>
                                <button type="button" onclick="window.photoManager.removePhoto(${index})" 
                                    class="opacity-0 group-hover:opacity-100 bg-red-500 hover:bg-red-600 text-white p-2 rounded-full transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    `;
                } else {
                    // Video
                    const video = item.data;
                    const globalIndex = item.globalIndex;
                    
                    return `
                        <div class="relative group photo-preview-item" data-video-id="${video.id}">
                            <div class="w-full h-32 bg-gray-900 rounded-lg border-2 ${globalIndex === this.featuredIndex ? 'border-yellow-400' : 'border-gray-200'} flex items-center justify-center cursor-pointer"
                                onclick="window.photoManager.playVideo('${video.embedUrl.replace(/'/g, "\\'")}')">
                                <svg class="w-12 h-12 text-white opacity-75" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                </svg>
                            </div>
                            
                            ${globalIndex === this.featuredIndex ? `
                                <div class="absolute top-2 left-2">
                                    <span class="inline-flex items-center rounded-full bg-yellow-400 px-2 py-1 text-xs font-medium text-yellow-900">
                                        ⭐ Featured
                                    </span>
                                </div>
                            ` : ''}
                            
                            <div class="absolute bottom-2 left-2">
                                <span class="inline-flex items-center rounded bg-black bg-opacity-75 px-2 py-1 text-xs font-medium text-white">
                                    🎥 Video
                                </span>
                            </div>
                            
                            <!-- Play Button Overlay -->
                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                <div class="bg-white bg-opacity-90 rounded-full p-3 shadow-lg">
                                    <svg class="w-8 h-8 text-gray-900" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                                    </svg>
                                </div>
                            </div>
                            
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-200 rounded-lg flex items-center justify-center gap-2">
                                <!-- Videos cannot be featured. Show warning when attempted -->
                                <button type="button" onclick="event.stopPropagation(); alert('Videos cannot be set as featured. Please select a photo instead.');" 
                                    class="opacity-0 group-hover:opacity-100 bg-yellow-200 text-yellow-900 p-2 rounded-full transition-all z-10 cursor-not-allowed" title="Videos cannot be featured">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </button>
                                <button type="button" onclick="event.stopPropagation(); window.photoManager.removeVideo('${video.id}')" 
                                    class="opacity-0 group-hover:opacity-100 bg-red-500 hover:bg-red-600 text-white p-2 rounded-full transition-all z-10">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    `;
                }
            }).join('');

            // Update DataTransfer for photos
            const dataTransfer = new DataTransfer();
            this.photos.forEach(photo => {
                dataTransfer.items.add(photo.file);
            });

            const photoInput = document.getElementById('photo-input');
            if (photoInput) {
                photoInput.files = dataTransfer.files;
            }

            // Update featured index
            const featuredInput = document.getElementById('featured-photo-index');
            if (featuredInput) {
                featuredInput.value = this.featuredIndex;
            }
            
            // Create hidden inputs for video URLs
            this.updateVideoUrlInputs();
        }

        updateVideoUrlInputs() {
            console.log('🔄 updateVideoUrlInputs called (edit page)');
            console.log('📹 Videos to add:', this.videos);
            
            const form = document.querySelector('form');
            
            if (!form) {
                console.error('❌ Form not found');
                return;
            }
            
            console.log('✅ Form found');
            
            // METHOD 1: Update the permanent hidden input (JSON format)
            const jsonInput = document.getElementById('trail-video-urls-json');
            if (jsonInput) {
                const videoUrls = this.videos.map(v => v.url);
                jsonInput.value = JSON.stringify(videoUrls);
                console.log('✅ Updated trail-video-urls-json:', jsonInput.value);
            }
            
            // METHOD 2: Also create individual hidden inputs as backup
            // Remove old video URL inputs
            const oldInputs = form.querySelectorAll('input[name="trail_video_urls[]"]');
            console.log('🗑️ Removing old inputs:', oldInputs.length);
            oldInputs.forEach(input => input.remove());
            
            console.log('➕ Adding new video URL inputs. Total videos:', this.videos.length);
            
            // Add new video URL inputs
            this.videos.forEach((video, index) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `trail_video_urls[]`;
                input.value = video.url;
                input.className = 'video-url-input';
                form.appendChild(input);
                
                console.log(`✅ Added video URL input [${index}]:`, video.url);
            });
            
            // Verify inputs were added
            const addedInputs = form.querySelectorAll('input[name="trail_video_urls[]"]');
            console.log('✅ Total video URL inputs in form:', addedInputs.length);
        }

        setupDragAndDrop() {
            const items = document.querySelectorAll('.photo-preview-item');
            let draggedItem = null;

            items.forEach(item => {
                item.addEventListener('dragstart', (e) => {
                    draggedItem = item;
                    item.classList.add('opacity-50');
                });

                item.addEventListener('dragend', (e) => {
                    item.classList.remove('opacity-50');
                });

                item.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    const afterElement = this.getDragAfterElement(e.clientX);
                    const container = document.getElementById('photo-previews');
                    
                    if (afterElement == null) {
                        container.appendChild(draggedItem);
                    } else {
                        container.insertBefore(draggedItem, afterElement);
                    }
                });

                item.addEventListener('drop', (e) => {
                    e.preventDefault();
                    this.reorderPhotos();
                });
            });
        }

        getDragAfterElement(x) {
            const draggableElements = [...document.querySelectorAll('.photo-preview-item:not(.opacity-50)')];

            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = x - box.left - box.width / 2;

                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        reorderPhotos() {
            const items = document.querySelectorAll('.photo-preview-item');
            const newOrder = [];
            
            items.forEach(item => {
                const id = parseFloat(item.dataset.photoId);
                const photo = this.photos.find(p => p.id === id);
                if (photo) newOrder.push(photo);
            });

            this.photos = newOrder;
            this.render();
        }

        updateFormData() {
            // Create a new FileList-like object
            const dataTransfer = new DataTransfer();
            
            this.photos.forEach(photo => {
                dataTransfer.items.add(photo.file);
            });

            const photoInput = document.getElementById('photo-input');
            photoInput.files = dataTransfer.files;

            // Update featured index
            document.getElementById('featured-photo-index').value = this.featuredIndex;
        }
    }

    // Initialize photo manager
    window.photoManager = new PhotoUploadManager();

    // Initialize trail builder when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        window.trailBuilder = new TrailBuilder();

        // Load existing trail route if available
        @if($trail->route_coordinates && count($trail->route_coordinates) > 0)
            const existingRoute = @json($trail->route_coordinates);
            console.log('Loading existing route with', existingRoute.length, 'points');
            
            if (existingRoute && existingRoute.length > 1) {
                // Display the route on the map
                window.trailBuilder.displayGPXRoute(existingRoute);
                
                // Create waypoints from the route
                window.trailBuilder.createWaypointsFromGPX(existingRoute);
                
                // Fit map to route bounds
                const bounds = L.latLngBounds(existingRoute);
                window.trailBuilder.map.fitBounds(bounds, { padding: [50, 50] });
            }
        @endif

        @if($trail->start_coordinates)
            // Set start coordinates
            document.getElementById('start-lat').value = {{ $trail->start_coordinates[0] ?? 0 }};
            document.getElementById('start-lng').value = {{ $trail->start_coordinates[1] ?? 0 }};
        @endif

        @if($trail->end_coordinates)
            // Set end coordinates
            document.getElementById('end-lat').value = {{ $trail->end_coordinates[0] ?? 0 }};
            document.getElementById('end-lng').value = {{ $trail->end_coordinates[1] ?? 0 }};
        @endif

        // ADD THIS: Two-way sync between input fields and display cards
        const distanceInput = document.querySelector('input[name="distance_km"]');
        const timeInput = document.querySelector('input[name="estimated_time_hours"]');
        const elevationInput = document.querySelector('input[name="elevation_gain_m"]');
        
        // When user manually edits distance input, update display card
        if (distanceInput) {
            distanceInput.addEventListener('input', function() {
                const distanceDisplay = document.getElementById('route-distance');
                if (distanceDisplay) {
                    distanceDisplay.textContent = `${parseFloat(this.value || 0).toFixed(2)} km`;
                }
            });
        }
        
        // When user manually edits time input, update display card
        if (timeInput) {
            timeInput.addEventListener('input', function() {
                const timeDisplay = document.getElementById('route-time');
                if (timeDisplay) {
                    timeDisplay.textContent = `${parseFloat(this.value || 0).toFixed(1)} hrs`;
                }
            });
        }
        
        // When user manually edits elevation input, update display card
        if (elevationInput) {
            elevationInput.addEventListener('input', function() {
                const elevationDisplay = document.getElementById('route-elevation');
                if (elevationDisplay) {
                    elevationDisplay.textContent = `${parseInt(this.value || 0)} m`;
                }
            });
        }
    });

    // Waypoint Mode Toggle
    let waypointModeEnabled = false;
    const toggleWaypointBtn = document.getElementById('toggle-waypoint-mode');
    const waypointModeText = document.getElementById('waypoint-mode-text');
    const trailMap = document.getElementById('trail-map');

    if (toggleWaypointBtn) {
        toggleWaypointBtn.addEventListener('click', function() {
            if (!window.trailBuilder) {
                showToast('Trail builder not initialized', 'error');
                return;
            }
            
            // Toggle the mode
            window.trailBuilder.waypointModeEnabled = !window.trailBuilder.waypointModeEnabled;
            
            if (window.trailBuilder.waypointModeEnabled) {
                // Enable waypoint mode
                this.classList.add('active');
                waypointModeText.textContent = 'Stop Adding Waypoints';
                window.trailBuilder.enableWaypointMode();
                showToast('Click on the map to add waypoints', 'success');
                
            } else {
                // Disable waypoint mode
                this.classList.remove('active');
                waypointModeText.textContent = 'Start Adding Waypoints';
                window.trailBuilder.disableWaypointMode();
                showToast('Waypoint mode disabled', 'info');
            }
        });
    }

    // Simple toast notification function
    function showToast(message, type = 'info') {
        const colors = {
            success: 'bg-green-500',
            info: 'bg-blue-500',
            warning: 'bg-amber-500',
            error: 'bg-red-500'
        };
        
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg text-sm font-medium z-50 animate-slide-up`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(20px)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Enhanced form validation before submit
    document.querySelector('form').addEventListener('submit', function(e) {
        const distanceInput = document.querySelector('input[name="distance_km"]');
        const elevationInput = document.querySelector('input[name="elevation_gain_m"]');
        const timeInput = document.querySelector('input[name="estimated_time_hours"]');
        const difficultySelect = document.querySelector('select[name="difficulty_level"]');
        const trailTypeSelect = document.querySelector('select[name="trail_type"]');
        
        // Check if required route data exists
        if (!distanceInput.value || parseFloat(distanceInput.value) === 0) {
            e.preventDefault();
            
            validationModal.show({
                type: 'warning',
                title: 'Route Required',
                message: 'Please create a trail route or upload a GPX file before submitting.\n\nYou need to either:\n• Click on the map to create waypoints\n• Upload a GPX file',
                buttons: [
                    {
                        label: 'Got it',
                        variant: 'primary',
                        action: 'confirm',
                        handler: () => {
                            document.getElementById('trail-map').scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                ]
            });
            
            return false;
        }

        // Check if difficulty is selected
        if (!difficultySelect.value) {
            e.preventDefault();
            
            validationModal.show({
                type: 'warning',
                title: 'Trail Difficulty Required',
                message: 'Please select a difficulty level for the trail (1-5).',
                buttons: [
                    {
                        label: 'Got it',
                        variant: 'primary',
                        action: 'confirm',
                        handler: () => {
                            // Switch to specifications tab
                            document.querySelector('[data-tab="specifications"]').click();
                            difficultySelect.focus();
                        }
                    }
                ]
            });
            
            return false;
        }

        // Check if trail type is selected
        if (!trailTypeSelect.value) {
            e.preventDefault();
            
            validationModal.show({
                type: 'warning',
                title: 'Trail Type Required',
                message: 'Please select a trail type (Loop, Out and Back, or Point to Point).',
                buttons: [
                    {
                        label: 'Got it',
                        variant: 'primary',
                        action: 'confirm',
                        handler: () => {
                            // Switch to specifications tab
                            document.querySelector('[data-tab="specifications"]').click();
                            trailTypeSelect.focus();
                        }
                    }
                ]
            });
            
            return false;
        }

        // Validate elevation if distance exists
        if (distanceInput.value && (!elevationInput.value || parseFloat(elevationInput.value) === 0)) {
            e.preventDefault();
            
            validationModal.show({
                type: 'warning',
                title: 'Unusual Elevation',
                message: 'Elevation gain is 0 meters. This is unusual for most trails.\n\nDo you want to continue anyway?',
                buttons: [
                    {
                        label: 'Cancel',
                        variant: 'secondary',
                        action: 'cancel',
                        handler: () => {
                            elevationInput.focus();
                        }
                    },
                    {
                        label: 'Continue Anyway',
                        variant: 'primary',
                        action: 'continue',
                        handler: () => {
                            // Bypass validation and submit
                            const form = document.querySelector('form');
                            // Remove the event listener temporarily
                            const newForm = form.cloneNode(true);
                            form.parentNode.replaceChild(newForm, form);
                            // Submit the form
                            newForm.submit();
                        }
                    }
                ]
            });
            
            return false;
        }
    });

    // Tab Switching Logic
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.trail-tab');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                // Remove active class from all tabs and contents
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                
                // Add active class to clicked tab and corresponding content
                this.classList.add('active');
                document.getElementById('tab-' + tabId).classList.add('active');
            });
        });
    });

    // Highlight Mode Toggle
    const toggleHighlightBtn = document.getElementById('toggle-highlight-mode');
    const highlightModeText = document.getElementById('highlight-mode-text');
    let highlightModeEnabled = false;

    if (toggleHighlightBtn) {
        toggleHighlightBtn.addEventListener('click', function() {
            if (!window.trailBuilder) {
                showToast('Trail builder not initialized', 'error');
                return;
            }
            
            highlightModeEnabled = !highlightModeEnabled;
            
            if (highlightModeEnabled) {
                // Enable highlight mode
                this.classList.add('active');
                highlightModeText.textContent = 'Stop Adding Highlights';
                
                // Disable waypoint mode if active
                if (window.trailBuilder.waypointModeEnabled) {
                    document.getElementById('toggle-waypoint-mode')?.click();
                }
                
                window.trailBuilder.highlightModeEnabled = true;
                const mapElement = document.getElementById('trail-map');
                if (mapElement) {
                    mapElement.classList.add('highlight-mode');
                    mapElement.classList.remove('waypoint-mode', 'waypoint-disabled');
                }
                
                showToast('Click on the map to place highlight markers', 'success');
                
            } else {
                // Disable highlight mode
                this.classList.remove('active');
                highlightModeText.textContent = 'Start Adding Highlights';
                
                window.trailBuilder.highlightModeEnabled = false;
                const mapElement = document.getElementById('trail-map');
                if (mapElement) {
                    mapElement.classList.remove('highlight-mode');
                }
                
                // Clear pending highlight if exists
                if (window.trailBuilder.pendingHighlight) {
                    window.trailBuilder.highlightsLayer.removeLayer(window.trailBuilder.pendingHighlight);
                    window.trailBuilder.pendingHighlight = null;
                }
                
                showToast('Highlight mode disabled', 'info');
            }
        });
    }

    // Type selector updates icon and color
    const highlightTypeSelect = document.getElementById('highlight-type-select');
    if (highlightTypeSelect) {
        highlightTypeSelect.addEventListener('change', (e) => {
            const selectedOption = e.target.options[e.target.selectedIndex];
            const icon = selectedOption.dataset.icon;
            const color = selectedOption.dataset.color;
            
            if (icon) document.getElementById('highlight-icon-input').value = icon;
            if (color) document.getElementById('highlight-color-input').value = color;
        });
    }

    // Track photos to delete
    let photosToDelete = [];

    // Remove existing photo
    function removeExistingPhoto(photoId) {
        if (confirm('Are you sure you want to remove this photo? This action will be permanent when you save.')) {
            // Add to deletion list
            if (!photosToDelete.includes(photoId)) {
                photosToDelete.push(photoId);
            }
            
            // Update hidden input
            const deletedPhotosInput = document.getElementById('deleted-photos-input');
            if (deletedPhotosInput) {
                deletedPhotosInput.value = JSON.stringify(photosToDelete);
            }
            
            // Hide the photo element with animation
            const photoElement = document.querySelector(`[data-photo-id="${photoId}"]`);
            if (photoElement) {
                photoElement.style.transition = 'opacity 0.3s, transform 0.3s';
                photoElement.style.opacity = '0';
                photoElement.style.transform = 'scale(0.8)';
                
                setTimeout(() => {
                    photoElement.style.display = 'none';
                }, 300);
            }
            
            // Show success message
            showToast('Photo marked for deletion. Save changes to confirm.', 'warning');
        }
    }

    // Set featured photo
    function setFeaturedPhoto(photoId) {
        // Update hidden input for featured photo
        const featuredInput = document.getElementById('featured-photo-id');
        if (featuredInput) {
            featuredInput.value = photoId;
        }
        
        // Visual feedback - remove all featured styling
        document.querySelectorAll('[data-photo-id]').forEach(el => {
            el.classList.remove('border-yellow-400');
            el.classList.add('border-gray-200');
            
            // Remove featured badges
            const badge = el.querySelector('.bg-yellow-400');
            if (badge && badge.textContent.includes('Featured')) {
                badge.remove();
            }
            
            // Show "Set Featured" button on other photos
            const setButton = el.querySelector('button[onclick*="setFeaturedPhoto"]');
            if (setButton) {
                setButton.style.display = 'block';
            }
        });
        
        // Add featured styling to selected photo
        const selectedPhoto = document.querySelector(`[data-photo-id="${photoId}"]`);
        if (selectedPhoto) {
            selectedPhoto.classList.remove('border-gray-200');
            selectedPhoto.classList.add('border-yellow-400');
            
            // Add featured badge
            const badge = document.createElement('div');
            badge.className = 'absolute top-2 left-2 bg-yellow-400 text-yellow-900 text-xs font-semibold px-2 py-1 rounded shadow-md';
            badge.innerHTML = '⭐ Featured';
            selectedPhoto.querySelector('.aspect-square').appendChild(badge);
            
            // Hide the "Set Featured" button on this photo
            const setButton = selectedPhoto.querySelector('button[onclick*="setFeaturedPhoto"]');
            if (setButton) {
                setButton.style.display = 'none';
            }
        }
        
        showToast('Photo set as featured! Save changes to confirm.', 'success');
    }

    // Show toast notification
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white text-sm font-medium z-50 animate-slide-up ${
            type === 'success' ? 'bg-green-500' : 
            type === 'warning' ? 'bg-yellow-500' : 
            'bg-red-500'
        }`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.transition = 'opacity 0.3s, transform 0.3s';
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    function deletePhoto(photoId) {
        // Don't show confirm here, let deleteExistingPhoto handle it
        if (window.photoManager) {
            window.photoManager.deleteExistingPhoto(photoId);
        }
    }

    function playExistingVideo(videoUrl) {
        // Convert video URL to embed URL
        let embedUrl = '';
        
        // YouTube
        const youtubeMatch = videoUrl.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/);
        if (youtubeMatch) {
            embedUrl = `https://www.youtube.com/embed/${youtubeMatch[1]}`;
        }
        
        // Vimeo
        const vimeoMatch = videoUrl.match(/vimeo\.com\/(\d+)/);
        if (vimeoMatch) {
            embedUrl = `https://player.vimeo.com/video/${vimeoMatch[1]}`;
        }
        
        if (!embedUrl) return;
        
        // Create modal
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4';
        modal.innerHTML = `
            <div class="relative w-full max-w-4xl">
                <button onclick="this.closest('.fixed').remove()" 
                    class="absolute -top-10 right-0 text-white hover:text-gray-300 bg-gray-900 bg-opacity-75 rounded-full p-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <div class="relative bg-black rounded-lg overflow-hidden" style="padding-bottom: 56.25%;">
                    <iframe src="${embedUrl}" 
                        class="absolute top-0 left-0 w-full h-full" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
                </div>
            </div>
        `;
        
        // Close on click outside
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });
        
        // Close on Escape key
        const escHandler = (e) => {
            if (e.key === 'Escape') {
                modal.remove();
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);
        
        document.body.appendChild(modal);
    }

    // Media Modal Functions
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
    document.addEventListener('DOMContentLoaded', function() {
        const mediaModal = document.getElementById('media-modal');
        if (mediaModal) {
            mediaModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeMediaModal();
                }
            });
        }
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('media-modal');
                if (modal && !modal.classList.contains('hidden')) {
                    closeMediaModal();
                }
            }
        });
    });

    
</script>

<style>
/* shadcn/ui color variables */
:root {
  --background: 0 0% 100%;
  --foreground: 222.2 84% 4.9%;
  --card: 0 0% 100%;
  --card-foreground: 222.2 84% 4.9%;
  --popover: 0 0% 100%;
  --popover-foreground: 222.2 84% 4.9%;
  --primary: 221.2 83.2% 53.3%;
  --primary-foreground: 210 40% 98%;
  --secondary: 210 40% 96%;
  --secondary-foreground: 222.2 84% 4.9%;
  --muted: 210 40% 96%;
  --muted-foreground: 215.4 16.3% 46.9%;
  --accent: 210 40% 96%;
  --accent-foreground: 222.2 84% 4.9%;
  --destructive: 0 84.2% 60.2%;
  --destructive-foreground: 210 40% 98%;
  --border: 214.3 31.8% 91.4%;
  --input: 214.3 31.8% 91.4%;
  --ring: 221.2 83.2% 53.3%;
  --radius: 0.5rem;
}

.dark {
  --background: 222.2 84% 4.9%;
  --foreground: 210 40% 98%;
  --card: 222.2 84% 4.9%;
  --card-foreground: 210 40% 98%;
  --popover: 222.2 84% 4.9%;
  --popover-foreground: 210 40% 98%;
  --primary: 217.2 91.2% 59.8%;
  --primary-foreground: 222.2 84% 4.9%;
  --secondary: 217.2 32.6% 17.5%;
  --secondary-foreground: 210 40% 98%;
  --muted: 217.2 32.6% 17.5%;
  --muted-foreground: 215 20.2% 65.1%;
  --accent: 217.2 32.6% 17.5%;
  --accent-foreground: 210 40% 98%;
  --destructive: 0 62.8% 30.6%;
  --destructive-foreground: 210 40% 98%;
  --border: 217.2 32.6% 17.5%;
  --input: 217.2 32.6% 17.5%;
  --ring: 224.3 76.3% 94.1%;
}

/* Apply shadcn/ui color classes */
.bg-background { background-color: hsl(var(--background)); }
.text-foreground { color: hsl(var(--foreground)); }
.bg-card { background-color: hsl(var(--card)); }
.text-card-foreground { color: hsl(var(--card-foreground)); }
.bg-popover { background-color: hsl(var(--popover)); }
.text-popover-foreground { color: hsl(var(--popover-foreground)); }
.bg-primary { background-color: hsl(var(--primary)); }
.text-primary-foreground { color: hsl(var(--primary-foreground)); }
.bg-primary\/90 { background-color: hsl(var(--primary) / 0.9); }
.bg-secondary { background-color: hsl(var(--secondary)); }
.text-secondary-foreground { color: hsl(var(--secondary-foreground)); }
.bg-muted { background-color: hsl(var(--muted)); }
.text-muted-foreground { color: hsl(var(--muted-foreground)); }
.bg-accent { background-color: hsl(var(--accent)); }
.text-accent-foreground { color: hsl(var(--accent-foreground)); }
.hover\:bg-accent:hover { background-color: hsl(var(--accent)); }
.hover\:text-accent-foreground:hover { color: hsl(var(--accent-foreground)); }
.border-border { border-color: hsl(var(--border)); }
.border-input { border-color: hsl(var(--input)); }
.ring-ring { --tw-ring-color: hsl(var(--ring)); }
.ring-offset-background { --tw-ring-offset-color: hsl(var(--background)); }

.photo-preview-item {
    transition: transform 0.2s, opacity 0.2s;
}

.photo-preview-item:hover {
    transform: translateY(-2px);
}

#photo-upload-zone {
    transition: all 0.3s ease;
}

#photo-upload-zone:hover {
    background-color: rgba(59, 130, 246, 0.02);
}

@keyframes modal-in {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(-20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.animate-modal-in {
    animation: modal-in 0.2s ease-out;
}

/* Modal backdrop blur effect */
.backdrop-blur-sm {
    backdrop-filter: blur(4px);
}

/* Tab Navigation Styling */
.trail-tab {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 0.5rem;
    color: hsl(var(--muted-foreground));
    transition: all 0.2s ease;
    cursor: pointer;
    border: none;
    background: transparent;
}

.trail-tab:hover {
    background-color: hsl(var(--accent));
    color: hsl(var(--accent-foreground));
}

.trail-tab.active {
    background-color: hsl(var(--primary));
    color: hsl(var(--primary-foreground));
    box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
}

/* Tooltip on hover */
.trail-tab::after {
    content: attr(title);
    position: absolute;
    left: 100%;
    margin-left: 12px;
    padding: 6px 12px;
    background-color: hsl(var(--popover));
    color: hsl(var(--popover-foreground));
    border: 1px solid hsl(var(--border));
    border-radius: 0.375rem;
    font-size: 0.75rem;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease;
    z-index: 50;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
}

.trail-tab:hover::after {
    opacity: 1;
}

/* Tab Content */
.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

/* Waypoint Toggle Button States */
#toggle-waypoint-mode {
    position: relative;
}

#toggle-waypoint-mode.active {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-color: #059669;
    color: white;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

#toggle-waypoint-mode.active:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
}

/* Map cursor states */
#trail-map.waypoint-mode {
    cursor: crosshair !important;
}

#trail-map.waypoint-disabled {
    cursor: not-allowed !important;
}

/* Pulse animation for active button */
#toggle-waypoint-mode.active::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: inherit;
    border-radius: inherit;
    opacity: 0;
    animation: pulse-ring 2s infinite;
}

@keyframes pulse-ring {
    0% {
        opacity: 0.6;
        transform: scale(1);
    }
    100% {
        opacity: 0;
        transform: scale(1.05);
    }
}

/* Toast animation */
@keyframes slide-up {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-slide-up {
    animation: slide-up 0.3s ease-out;
    transition: all 0.3s ease;
}

/* Highlight Mode Button */
#toggle-highlight-mode.active {
    background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);
    border-color: #7C3AED;
    color: white;
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
}

#toggle-highlight-mode.active:hover {
    background: linear-gradient(135deg, #7C3AED 0%, #6D28D9 100%);
}

/* Highlight mode cursor */
#trail-map.highlight-mode {
    cursor: pointer !important;
}

/* Map Layer Selector Styles */
.map-layer-option-card {
    position: relative;
    cursor: pointer;
    border-radius: 0.375rem;
    overflow: hidden;
    transition: all 0.2s;
    border: 2px solid transparent;
    display: flex;
    flex-direction: column;
    align-items: center;
    background: white;
}

.map-layer-option-card:hover {
    border-color: #93C5FD;
}

.map-layer-option-card.active {
    border-color: #2563EB;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.map-layer-preview {
    width: 100%;
    height: 60px;
    border-radius: 0.25rem;
    overflow: hidden;
    position: relative;
}

.map-layer-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.map-layer-label {
    display: block;
    font-size: 0.7rem;
    font-weight: 500;
    color: #374151;
    text-align: center;
    margin-top: 0.375rem;
    padding: 0 0.25rem 0.375rem;
}

.map-layer-checkmark {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 18px;
    height: 18px;
    color: white;
    background-color: #2563EB;
    border-radius: 50%;
    padding: 2px;
    display: none;
}

.map-layer-option-card.active .map-layer-checkmark {
    display: block;
}

#map-layers-toggle {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

#map-layers-dropdown {
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
</style>
@endpush
@endsection