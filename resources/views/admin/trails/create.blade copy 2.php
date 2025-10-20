@extends('layouts.admin')

@section('title', 'Add New Trail')
@section('page-title', 'Add New Trail')

@section('content')
<div class="max-w-6xl mx-auto space-y-8">
    <!-- Header -->
    <div class="space-y-2">
        <div class="flex items-center gap-2 text-sm text-muted-foreground">
            <a href="{{ route('admin.trails.index') }}" class="hover:text-foreground transition-colors">Trails</a>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>Create Trail</span>
        </div>
        <div class="space-y-1">
            <h1 class="text-3xl font-bold tracking-tight">Create New Trail</h1>
            <p class="text-muted-foreground">Add a new hiking trail to your collection with detailed information and coordinates.</p>
        </div>
    </div>

    <form action="{{ route('admin.trails.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8" onsubmit="return window.trailBuilder.validateBeforeSubmit()">
        @csrf
        
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
                        <input type="text" name="name" required value="{{ old('name') }}"
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
                        <input type="text" name="location" value="{{ old('location') }}"
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
                                  class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 @error('description') border-red-300 @enderror">{{ old('description') }}</textarea>
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
                    <label class="flex items-start gap-3 p-4 rounded-lg border border-input bg-background hover:bg-accent hover:text-accent-foreground cursor-pointer transition-colors">
                        <input type="checkbox" name="activities[]" value="hiking" 
                               class="mt-1 h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary" checked>
                        <div class="space-y-1">
                            <div class="text-sm font-medium">Hiking</div>
                            <div class="text-xs text-muted-foreground">Walking trails</div>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-4 rounded-lg border border-input bg-background hover:bg-accent hover:text-accent-foreground cursor-pointer transition-colors">
                        <input type="checkbox" name="activities[]" value="fishing" 
                               class="mt-1 h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                        <div class="space-y-1">
                            <div class="text-sm font-medium">Fishing</div>
                            <div class="text-xs text-muted-foreground">Fishing spots</div>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-4 rounded-lg border border-input bg-background hover:bg-accent hover:text-accent-foreground cursor-pointer transition-colors">
                        <input type="checkbox" name="activities[]" value="camping" 
                               class="mt-1 h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                        <div class="space-y-1">
                            <div class="text-sm font-medium">Camping</div>
                            <div class="text-xs text-muted-foreground">Camping areas</div>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-4 rounded-lg border border-input bg-background hover:bg-accent hover:text-accent-foreground cursor-pointer transition-colors">
                        <input type="checkbox" name="activities[]" value="viewpoint" 
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
                              class="flex min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">{{ old('activity_notes') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Trail Route Import -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 space-y-6">
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold">Interactive Trail Builder</h3>
                    <p class="text-sm text-muted-foreground">Click on the map to create waypoints. Routes will automatically snap to walking paths and trails.</p>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Controls Panel -->
                    <div class="space-y-6">
                        <div class="rounded-lg border border-input p-4 space-y-4">
                            <h4 class="font-medium">Trail Creation Controls</h4>
                            <div class="space-y-3">
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
                                 <button type="button" id="optimize-route" 
                                        class="w-full inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 text-sm font-medium">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    Optimize Route
                                </button>
                            </div>
                        </div>

                        <!-- Backend GPX Upload for Auto-Calculations -->
                        <div class="rounded-lg border border-green-200 bg-green-50 p-4 space-y-3">
                            <h4 class="font-medium text-green-900">Import from GPX (Optional)</h4>
                            <p class="text-xs text-green-700">Upload GPX to automatically calculate distance, elevation & time</p>
                            
                            <input type="file" id="gpx-file-backend" name="gpx_file" accept=".gpx"
                                class="flex h-10 w-full rounded-md border border-input bg-white px-3 py-2 text-sm">
                            
                            <!-- Preview Results -->
                            <div id="gpx-preview-results" class="hidden space-y-2">
                                <div class="text-sm font-medium text-green-900">Calculated Values:</div>
                                <div class="grid grid-cols-3 gap-2 text-xs">
                                    <div>
                                        <span class="text-green-700">Distance:</span>
                                        <div id="gpx-calc-distance" class="font-bold text-green-900"></div>
                                    </div>
                                    <div>
                                        <span class="text-green-700">Elevation:</span>
                                        <div id="gpx-calc-elevation" class="font-bold text-green-900"></div>
                                    </div>
                                    <div>
                                        <span class="text-green-700">Time:</span>
                                        <div id="gpx-calc-time" class="font-bold text-green-900"></div>
                                    </div>
                                </div>
                                <button type="button" id="apply-gpx-values" 
                                    class="w-full bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-md text-sm font-medium">
                                    ✓ Apply These Values
                                </button>
                            </div>
                        </div>

                        <div class="rounded-lg border border-input p-4 space-y-4 hidden">
                            <h4 class="font-medium">Import from GPX (Optional)</h4>
                            <input type="file" id="gpx-import" accept=".gpx"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            <input type="hidden" id="use_gpx_calculations" name="use_gpx_calculations" value="false">
                            <p class="text-xs text-muted-foreground">Or import an existing GPX file to override manual route creation</p>
                        </div>

                        <!-- Route Statistics -->
                        <div class="rounded-lg border border-input p-4 space-y-3">
                            <h4 class="font-medium">Route Statistics</h4>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div class="space-y-1">
                                    <span class="text-muted-foreground">Distance</span>
                                    <div id="route-distance" class="font-medium text-blue-600">Click map to start</div>
                                </div>
                                <div class="space-y-1">
                                    <span class="text-muted-foreground">Est. Time</span>
                                    <div id="route-time" class="font-medium text-green-600">-</div>
                                </div>
                                <div class="space-y-1">
                                    <span class="text-muted-foreground">Waypoints</span>
                                    <div id="waypoint-count" class="font-medium text-purple-600">0</div>
                                </div>
                                <div class="space-y-1">
                                    <span class="text-muted-foreground">Routing</span>
                                    <div id="routing-status" class="font-medium text-blue-600">Smart</div>
                                </div>
                            </div>
                        </div>

                        <!-- Elevation Profile -->
                        <div class="rounded-lg border border-input p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="font-medium">Elevation Profile</h4>
                                <button type="button" id="load-elevation" class="text-xs text-blue-600 hover:text-blue-800">
                                    Refresh Profile
                                </button>
                            </div>
                            <div id="elevation-chart" class="w-full h-32 bg-gray-50 rounded border hidden">
                                <canvas id="elevation-canvas" class="w-full h-full"></canvas>
                            </div>
                            <div id="elevation-stats" class="hidden text-xs text-muted-foreground grid grid-cols-2 gap-2">
                                <div>Max: <span id="max-elevation" class="font-medium">-</span>m</div>
                                <div>Min: <span id="min-elevation" class="font-medium">-</span>m</div>
                                <div>Gain: <span id="elevation-gain" class="font-medium">-</span>m</div>
                                <div>Loss: <span id="elevation-loss" class="font-medium">-</span>m</div>
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 space-y-2">
                            <h4 class="font-medium text-amber-800">How to Use</h4>
                            <ul class="text-xs text-amber-700 space-y-1">
                                <li>• Click anywhere on the map to add waypoints</li>
                                <li>• Routes automatically snap to walking paths</li>
                                <li>• Drag waypoint markers to adjust the route</li>
                                <li>• Use "Undo" to remove the last waypoint</li>
                                <li>• Toggle smart routing off for straight lines</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Map Display -->
                    <div class="space-y-4">
                        <div id="trail-map" class="w-full h-96 rounded-md border border-input bg-muted"></div>
                        <div id="map-status" class="text-xs text-muted-foreground">
                            <div class="flex items-center gap-2">
                                <span class="inline-block w-2 h-2 bg-gray-400 rounded-full animate-pulse"></span>
                                <span>Ready to create trail route</span>
                            </div>
                        </div>
                        <!-- Trail Specifications -->
                        <div class="border-t pt-4 space-y-4">
                            <h5 class="font-medium">Trail Specifications</h5>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-2">
                                    <label class="text-xs font-medium">Difficulty (1-5) *</label>
                                    <select name="difficulty_level" required class="flex h-8 w-full text-xs rounded border border-input bg-background px-2 py-1">
                                        <option value="">Auto-detect</option>
                                        <option value="1">1 - Very Easy</option>
                                        <option value="2">2 - Easy</option>
                                        <option value="3">3 - Moderate</option>
                                        <option value="4">4 - Hard</option>
                                        <option value="5">5 - Very Hard</option>
                                    </select>
                                </div>
                                
                                <div class="space-y-2">
                                    <label class="text-xs font-medium">Trail Type *</label>
                                    <select name="trail_type" required class="flex h-8 w-full text-xs rounded border border-input bg-background px-2 py-1">
                                        <option value="">Auto-detect</option>
                                        <option value="loop">Loop</option>
                                        <option value="out-and-back">Out and Back</option>
                                        <option value="point-to-point">Point to Point</option>
                                    </select>
                                </div>
                                
                                <div class="space-y-2">
                                    <label class="text-xs font-medium">Distance (km) *</label>
                                    <input type="number" name="distance_km" step="0.01" required 
                                        class="flex h-8 w-full text-xs rounded border border-input bg-gray-50 px-2 py-1">
                                </div>
                                
                                <div class="space-y-2">
                                    <label class="text-xs font-medium">Time (hours) *</label>
                                    <input type="number" name="estimated_time_hours" step="0.01" required 
                                        class="flex h-8 w-full text-xs rounded border border-input bg-gray-50 px-2 py-1">
                                </div>
                                
                                <div class="space-y-2">
                                    <label class="text-xs font-medium">Elevation Gain (m) *</label>
                                    <input type="number" name="elevation_gain_m" required 
                                        class="flex h-8 w-full text-xs rounded border border-input bg-gray-50 px-2 py-1">
                                </div>
                                
                                <div class="space-y-2">
                                    <label class="text-xs font-medium">Status *</label>
                                    <select name="status" required class="flex h-8 w-full text-xs rounded border border-input bg-background px-2 py-1">
                                        <option value="active">Active</option>
                                        <option value="closed">Closed</option>
                                        <option value="seasonal">Seasonal</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hidden Form Inputs -->
                <input type="hidden" name="route_coordinates" id="route-coordinates-input">
                <input type="hidden" name="start_lat" id="start-lat-input">
                <input type="hidden" name="start_lng" id="start-lng-input">
                <input type="hidden" name="end_lat" id="end-lat-input">
                <input type="hidden" name="end_lng" id="end-lng-input">
            </div>
        </div>

        <!-- Highlights & Points of Interest -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 space-y-6">
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold">Highlights & Points of Interest</h3>
                    <p class="text-sm text-muted-foreground">Add viewpoints, waterfalls, and other notable features along the trail</p>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Add Highlight Form -->
                    <div class="space-y-4">
                        <div class="rounded-lg border border-input p-4 space-y-4">
                            <h4 class="font-medium">Add Highlight</h4>
                            <p class="text-xs text-muted-foreground">Click on the map to place a highlight marker</p>
                            
                            <div id="highlight-form-section" class="space-y-3">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Highlight Type</label>
                                    <select id="highlight-type-select" class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm">
                                        <option value="">Select type...</option>
                                        <option value="viewpoint" data-icon="👁️" data-color="#8B5CF6">👁️ Viewpoint</option>
                                        <option value="waterfall" data-icon="💧" data-color="#3B82F6">💧 Waterfall</option>
                                        <option value="summit" data-icon="⛰️" data-color="#EF4444">⛰️ Summit</option>
                                        <option value="lake" data-icon="🏞️" data-color="#06B6D4">🏞️ Lake</option>
                                        <option value="bridge" data-icon="🌉" data-color="#F59E0B">🌉 Bridge</option>
                                        <option value="wildlife" data-icon="🦌" data-color="#10B981">🦌 Wildlife Spot</option>
                                        <option value="camping" data-icon="⛺" data-color="#F97316">⛺ Camping Area</option>
                                        <option value="parking" data-icon="🅿️" data-color="#6B7280">🅿️ Parking</option>
                                        <option value="picnic" data-icon="🍽️" data-color="#84CC16">🍽️ Picnic Area</option>
                                        <option value="restroom" data-icon="🚻" data-color="#14B8A6">🚻 Restroom</option>
                                        <option value="danger" data-icon="⚠️" data-color="#DC2626">⚠️ Hazard/Warning</option>
                                        <option value="photo_spot" data-icon="📷" data-color="#EC4899">📷 Photo Spot</option>
                                    </select>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Name</label>
                                    <input type="text" id="highlight-name-input" placeholder="e.g., Eagle's Nest Viewpoint"
                                        class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm">
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Description (optional)</label>
                                    <textarea id="highlight-description-input" rows="2" placeholder="Brief description..."
                                            class="flex min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm"></textarea>
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Icon (emoji)</label>
                                        <input type="text" id="highlight-icon-input" placeholder="🏔️" maxlength="10"
                                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Color</label>
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
                            <h4 class="font-medium text-blue-800">How to Add Highlights</h4>
                            <ul class="text-xs text-blue-700 space-y-1">
                                <li>• Select a highlight type from the dropdown</li>
                                <li>• Click on the trail map to place the marker</li>
                                <li>• Fill in the name and optional description</li>
                                <li>• Click "Add Highlight" to save</li>
                                <li>• Highlights will be saved when you create the trail</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Highlights List -->
                    <div class="space-y-4">
                        <div class="rounded-lg border border-input p-4">
                            <h4 class="font-medium mb-3">Added Highlights (<span id="highlights-count">0</span>)</h4>
                            <div id="highlights-list" class="space-y-2 max-h-96 overflow-y-auto">
                                <p class="text-sm text-muted-foreground text-center py-8">No highlights added yet</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hidden inputs for highlights data -->
                <input type="hidden" name="highlights_data" id="highlights-data-input" value="[]">
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
                                       value="{{ old('seasonal.spring.conditions') }}"
                                       class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <input type="checkbox" name="seasonal[spring][recommended]" value="1" 
                                       {{ old('seasonal.spring.recommended', true) ? 'checked' : '' }}
                                       class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                                <label class="text-sm font-medium">Recommended in Spring</label>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Notes</label>
                                <textarea name="seasonal[spring][notes]" rows="2" 
                                          placeholder="Special spring considerations..."
                                          class="flex min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">{{ old('seasonal.spring.notes') }}</textarea>
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
                                <input type="checkbox" name="seasonal[summer][recommended]" value="1" 
                                       {{ old('seasonal.summer.recommended', true) ? 'checked' : '' }}
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
                                <input type="checkbox" name="seasonal[fall][recommended]" value="1" 
                                       {{ old('seasonal.fall.recommended', true) ? 'checked' : '' }}
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
                                <input type="checkbox" name="seasonal[winter][recommended]" value="1" 
                                       {{ old('seasonal.winter.recommended', false) ? 'checked' : '' }}
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
                            @php $seasons = ['Spring', 'Summer', 'Fall', 'Winter']; @endphp
                            @foreach($seasons as $season)
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="best_seasons[]" value="{{ $season }}" 
                                           {{ in_array($season, old('best_seasons', ['Spring', 'Summer', 'Fall'])) ? 'checked' : '' }}
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
                                  class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">{{ old('directions') }}</textarea>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Parking Information
                        </label>
                        <textarea name="parking_info" rows="3" 
                                  placeholder="Parking availability, fees, restrictions, and tips..."
                                  class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">{{ old('parking_info') }}</textarea>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Safety Notes & Warnings
                        </label>
                        <textarea name="safety_notes" rows="3" 
                                  placeholder="Important safety information, hazards, equipment recommendations..."
                                  class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">{{ old('safety_notes') }}</textarea>
                    </div>

                    <div class="flex items-center space-x-2">
                        <input type="checkbox" name="is_featured" value="1" 
                               {{ old('is_featured') ? 'checked' : '' }}
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
                    <h3 class="text-lg font-semibold">Trail Photos</h3>
                    <p class="text-sm text-muted-foreground">Upload up to 5 images. Drag to reorder, click star to set featured image.</p>
                </div>
                
                <!-- Drag & Drop Zone -->
                <div id="photo-upload-zone" class="border-2 border-dashed border-input rounded-lg p-8 text-center hover:border-primary transition-colors cursor-pointer">
                    <input type="file" id="photo-input" name="photos[]" multiple accept="image/*" class="hidden" max="5">
                    <div id="upload-prompt">
                        <svg class="mx-auto h-12 w-12 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="mt-2 text-sm text-muted-foreground">
                            <span class="font-semibold text-primary">Click to upload</span> or drag and drop
                        </p>
                        <p class="text-xs text-muted-foreground mt-1">PNG, JPG, GIF up to 10MB (max 5 photos)</p>
                    </div>
                </div>

                <!-- Photo Preview Grid -->
                <div id="photo-preview-grid" class="hidden">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm font-medium">Uploaded Photos (<span id="photo-count">0</span>/5)</p>
                        <button type="button" id="clear-photos" class="text-sm text-red-600 hover:text-red-800">Clear All</button>
                    </div>
                    <div id="photo-previews" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                        <!-- Photo previews will be inserted here -->
                    </div>
                </div>

                <!-- Hidden inputs for photo data -->
                <input type="hidden" name="featured_photo_index" id="featured-photo-index" value="0">
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-between pt-6 border-t">
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
                Create Trail
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
            this.highlightsLayer = null; 
            this.pendingHighlight = null; 
            this.init();
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
            this.map = L.map('trail-map', {
                maxZoom: 20,  // Allow zooming to very detailed level
                minZoom: 5    // Prevent zooming out too far
            }).setView([49.2827, -122.7927], 13);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 20  // Support higher zoom levels
            }).addTo(this.map);

            this.routeLayer = L.layerGroup().addTo(this.map);
            this.highlightsLayer = L.layerGroup().addTo(this.map); 
            this.setupEventListeners();
            this.setupMapClicks();
            this.setupHighlightHandlers(); 
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
        }

        setupMapClicks() {
            this.map.on('click', (e) => {
                this.addWaypoint(e.latlng.lat, e.latlng.lng);
            });
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
            if (elevationGainInput) {
                elevationGainInput.value = Math.round(totalGain);
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

            const distanceEl = document.getElementById('route-distance');
            const timeEl = document.getElementById('route-time');
            
            if (distanceEl) distanceEl.textContent = `${this.totalDistance.toFixed(2)} km`;
            if (timeEl) timeEl.textContent = `${Math.round(this.totalTime)} min`;

            // Auto-populate form fields
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

        validateBeforeSubmit() {
            const issues = this.validateRoute();
            
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

            if (!type || !name || !this.pendingHighlight) {
                alert('Please select a type, enter a name, and click on the map to place the highlight');
                return;
            }

            const highlight = {
                id: Date.now(),
                type: type,
                name: name,
                description: description,
                icon: icon,
                color: color,
                coordinates: this.pendingHighlight.coordinates
            };

            this.highlights.push(highlight);
            this.updateHighlightsList();
            this.updateHighlightsInput();
            
            // Clear form
            document.getElementById('highlight-type-select').value = '';
            document.getElementById('highlight-name-input').value = '';
            document.getElementById('highlight-description-input').value = '';
            document.getElementById('highlight-icon-input').value = '';
            
            // Remove pending marker and add permanent one
            if (this.pendingHighlight) {
                this.highlightsLayer.removeLayer(this.pendingHighlight);
                this.pendingHighlight = null;
            }

            // Add permanent marker
            const marker = L.marker(highlight.coordinates, {
                icon: L.divIcon({
                    html: `<div style="background-color: ${highlight.color};" class="w-8 h-8 rounded-full border-2 border-white shadow-lg flex items-center justify-center text-white text-lg">${highlight.icon}</div>`,
                    className: 'custom-marker',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                })
            }).addTo(this.highlightsLayer);

            marker.bindPopup(`<b>${highlight.name}</b><br>${highlight.description || ''}`);
            marker.highlightId = highlight.id;
        }

        updateHighlightsList() {
            const listContainer = document.getElementById('highlights-list');
            const countSpan = document.getElementById('highlights-count');
            
            if (!listContainer) return;

            countSpan.textContent = this.highlights.length;

            if (this.highlights.length === 0) {
                listContainer.innerHTML = '<p class="text-sm text-muted-foreground text-center py-8">No highlights added yet</p>';
                return;
            }

            listContainer.innerHTML = this.highlights.map(h => `
                <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                    <div style="background-color: ${h.color};" class="w-8 h-8 rounded-full flex items-center justify-center text-white shrink-0">
                        ${h.icon}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h5 class="font-medium text-sm">${h.name}</h5>
                        <p class="text-xs text-muted-foreground capitalize">${h.type.replace('_', ' ')}</p>
                        ${h.description ? `<p class="text-xs text-gray-600 mt-1">${h.description}</p>` : ''}
                    </div>
                    <button onclick="window.trailBuilder.removeHighlight(${h.id})" class="text-red-500 hover:text-red-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            `).join('');
        }

        removeHighlight(id) {
            this.highlights = this.highlights.filter(h => h.id !== id);
            
            // Remove marker from map
            this.highlightsLayer.eachLayer(layer => {
                if (layer.highlightId === id) {
                    this.highlightsLayer.removeLayer(layer);
                }
            });

            this.updateHighlightsList();
            this.updateHighlightsInput();
        }

        updateHighlightsInput() {
            const input = document.getElementById('highlights-data-input');
            if (input) {
                input.value = JSON.stringify(this.highlights);
            }
        }
    }

    // Photo Upload Handler
    class PhotoUploadManager {
        constructor() {
            this.photos = [];
            this.maxPhotos = 5;
            this.featuredIndex = 0;
            this.init();
        }

        init() {
            const uploadZone = document.getElementById('photo-upload-zone');
            const photoInput = document.getElementById('photo-input');

            // Click to upload
            uploadZone.addEventListener('click', () => photoInput.click());

            // File input change
            photoInput.addEventListener('change', (e) => {
                this.handleFiles(e.target.files);
            });

            // Drag and drop
            uploadZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadZone.classList.add('border-primary', 'bg-primary/5');
            });

            uploadZone.addEventListener('dragleave', (e) => {
                e.preventDefault();
                uploadZone.classList.remove('border-primary', 'bg-primary/5');
            });

            uploadZone.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadZone.classList.remove('border-primary', 'bg-primary/5');
                this.handleFiles(e.dataTransfer.files);
            });

            // Clear all photos
            document.getElementById('clear-photos')?.addEventListener('click', () => {
                this.clearAll();
            });
        }

        handleFiles(files) {
            const remainingSlots = this.maxPhotos - this.photos.length;
            
            if (files.length > remainingSlots) {
                alert(`You can only upload ${remainingSlots} more photo(s). Maximum is ${this.maxPhotos} photos.`);
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

        addPhoto(file) {
            const reader = new FileReader();
            
            reader.onload = (e) => {
                const photo = {
                    file: file,
                    dataUrl: e.target.result,
                    id: Date.now() + Math.random()
                };

                this.photos.push(photo);
                this.render();
            };

            reader.readAsDataURL(file);
        }

        removePhoto(id) {
            const index = this.photos.findIndex(p => p.id === id);
            if (index === -1) return;

            this.photos.splice(index, 1);
            
            // Adjust featured index if needed
            if (this.featuredIndex >= this.photos.length) {
                this.featuredIndex = Math.max(0, this.photos.length - 1);
            }

            this.render();
        }

        setFeatured(index) {
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

            // Update count
            photoCount.textContent = this.photos.length;

            // Show/hide sections
            if (this.photos.length > 0) {
                previewGrid.classList.remove('hidden');
                uploadPrompt.classList.add('hidden');
                uploadZone.classList.add('border-solid');
            } else {
                previewGrid.classList.add('hidden');
                uploadPrompt.classList.remove('hidden');
                uploadZone.classList.remove('border-solid');
            }

            // Render previews
            previewsContainer.innerHTML = this.photos.map((photo, index) => `
                <div class="relative group photo-preview-item" data-photo-id="${photo.id}" draggable="true">
                    <img src="${photo.dataUrl}" alt="Preview ${index + 1}" 
                        class="w-full h-32 object-cover rounded-lg border-2 ${index === this.featuredIndex ? 'border-yellow-400' : 'border-gray-200'}">
                    
                    <!-- Featured Badge -->
                    ${index === this.featuredIndex ? `
                        <div class="absolute top-2 left-2">
                            <span class="inline-flex items-center rounded-full bg-yellow-400 px-2 py-1 text-xs font-medium text-yellow-900">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                Featured
                            </span>
                        </div>
                    ` : ''}
                    
                    <!-- Controls overlay -->
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-200 rounded-lg flex items-center justify-center gap-2">
                        <button type="button" onclick="window.photoManager.setFeatured(${index})" 
                                class="opacity-0 group-hover:opacity-100 transition-opacity bg-white rounded-full p-2 hover:bg-yellow-100"
                                title="Set as featured">
                            <svg class="w-4 h-4 ${index === this.featuredIndex ? 'text-yellow-500' : 'text-gray-600'}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </button>
                        <button type="button" onclick="window.photoManager.removePhoto('${photo.id}')" 
                                class="opacity-0 group-hover:opacity-100 transition-opacity bg-white rounded-full p-2 hover:bg-red-100"
                                title="Remove photo">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Drag handle -->
                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity cursor-move">
                        <svg class="w-5 h-5 text-white drop-shadow-lg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                        </svg>
                    </div>
                </div>
            `).join('');

            // Setup drag and drop reordering
            this.setupDragAndDrop();

            // Update form data
            this.updateFormData();
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
    });

    // Enhanced form validation before submit
    document.querySelector('form').addEventListener('submit', function(e) {
        const distanceInput = document.querySelector('input[name="distance_km"]');
        const elevationInput = document.querySelector('input[name="elevation_gain_m"]');
        const timeInput = document.querySelector('input[name="estimated_time_hours"]');
        
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
                            const submitBtn = form.querySelector('button[type="submit"]');
                            
                            // Temporarily remove the submit handler
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
</style>
@endpush
@endsection