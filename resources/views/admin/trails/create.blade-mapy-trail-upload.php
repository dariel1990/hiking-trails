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

    <form action="{{ route('admin.trails.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
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

        <!-- Trail Details -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 space-y-6">
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold">Trail Specifications</h3>
                    <p class="text-sm text-muted-foreground">Technical details and difficulty metrics</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Difficulty Level <span class="text-red-500">*</span>
                        </label>
                        <select name="difficulty_level" required
                                class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 @error('difficulty_level') border-red-300 @enderror">
                            <option value="">Select difficulty</option>
                            <option value="1" {{ old('difficulty_level') == '1' ? 'selected' : '' }}>1 - Very Easy</option>
                            <option value="2" {{ old('difficulty_level') == '2' ? 'selected' : '' }}>2 - Easy</option>
                            <option value="3" {{ old('difficulty_level') == '3' ? 'selected' : '' }}>3 - Moderate</option>
                            <option value="4" {{ old('difficulty_level') == '4' ? 'selected' : '' }}>4 - Hard</option>
                            <option value="5" {{ old('difficulty_level') == '5' ? 'selected' : '' }}>5 - Very Hard</option>
                        </select>
                        @error('difficulty_level')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Distance (km) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="distance_km" step="0.1" required value="{{ old('distance_km') }}"
                               placeholder="0.0"
                               class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 @error('distance_km') border-red-300 @enderror">
                        @error('distance_km')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Elevation Gain (m) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="elevation_gain_m" required value="{{ old('elevation_gain_m') }}"
                               placeholder="0"
                               class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 @error('elevation_gain_m') border-red-300 @enderror">
                        @error('elevation_gain_m')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Estimated Time (hours) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="estimated_time_hours" step="0.5" required value="{{ old('estimated_time_hours') }}"
                               placeholder="0.0"
                               class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 @error('estimated_time_hours') border-red-300 @enderror">
                        @error('estimated_time_hours')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Trail Type <span class="text-red-500">*</span>
                        </label>
                        <select name="trail_type" required
                                class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 @error('trail_type') border-red-300 @enderror">
                            <option value="">Select type</option>
                            <option value="loop" {{ old('trail_type') == 'loop' ? 'selected' : '' }}>Loop</option>
                            <option value="out-and-back" {{ old('trail_type') == 'out-and-back' ? 'selected' : '' }}>Out and Back</option>
                            <option value="point-to-point" {{ old('trail_type') == 'point-to-point' ? 'selected' : '' }}>Point to Point</option>
                        </select>
                        @error('trail_type')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select name="status" required
                                class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 @error('status') border-red-300 @enderror">
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="closed" {{ old('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                            <option value="seasonal" {{ old('status') == 'seasonal' ? 'selected' : '' }}>Seasonal</option>
                        </select>
                        @error('status')
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
                    <h3 class="text-lg font-semibold">Trail Route Import</h3>
                    <p class="text-sm text-muted-foreground">Create your trail using Mapy.cz or similar tools, then upload the GPX file here.</p>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Import Controls -->
                    <div class="space-y-6">
                        <div class="rounded-lg border border-input p-4 space-y-4">
                            <h4 class="font-medium">Step 1: Create Trail Externally</h4>
                            <div class="space-y-2">
                                <a href="https://mapy.cz/turisticka" target="_blank" 
                                class="inline-flex items-center justify-center rounded-md bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 text-sm font-medium">
                                    Open Mapy.cz Trail Planner
                                </a>
                                <p class="text-xs text-muted-foreground">Use the route planning tool to create your trail, then export as GPX</p>
                            </div>
                        </div>

                        <div class="rounded-lg border border-input p-4 space-y-4">
                            <h4 class="font-medium">Step 2: Upload GPX File</h4>
                            <input type="file" id="gpx-import" accept=".gpx"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        </div>

                        <!-- Route Statistics -->
                        <div class="rounded-lg border border-input p-4 space-y-3">
                            <h4 class="font-medium">Imported Route Info</h4>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div class="space-y-1">
                                    <span class="text-muted-foreground">Distance</span>
                                    <div id="route-distance" class="font-medium">No route loaded</div>
                                </div>
                                <div class="space-y-1">
                                    <span class="text-muted-foreground">Est. Time</span>
                                    <div id="route-time" class="font-medium">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Map Display -->
                    <div class="space-y-4">
                        <div id="trail-map" class="w-full h-96 rounded-md border border-input bg-muted"></div>
                        <p class="text-xs text-muted-foreground">Import a GPX file to see the trail route here</p>
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
                    <p class="text-sm text-muted-foreground">Upload images to showcase the trail</p>
                </div>
                
                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                        Upload Photos
                    </label>
                    <div class="grid w-full items-center gap-1.5">
                        <input type="file" name="photos[]" multiple accept="image/*"
                               class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                        <p class="text-xs text-muted-foreground">Select multiple photos. First photo will be used as featured image.</p>
                    </div>
                </div>
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
</div>

@push('scripts')
<script>
    // Simplified TrailBuilder focused on import/display
    class TrailBuilder {
        constructor() {
            this.map = null;
            this.routeLayer = null;
            this.routeCoordinates = [];
            this.totalDistance = 0;
            this.totalTime = 0;
            this.init();
        }

        init() {
            this.map = L.map('trail-map').setView([49.2827, -122.7927], 13);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(this.map);

            this.routeLayer = L.layerGroup().addTo(this.map);
            this.setupEventListeners();
        }

        setupEventListeners() {
            // Focus on GPX import
            const gpxInput = document.getElementById('gpx-import');
            if (gpxInput) {
                gpxInput.addEventListener('change', (e) => {
                    this.importGPX(e.target.files[0]);
                });
            }
        }

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

                this.routeLayer.clearLayers();
                
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
                            this.displayRoute(coordinates);
                            this.calculateStats(coordinates);
                        }
                    });
                });

                this.updateFormInputs();
                
            } catch (error) {
                console.error('Error importing GPX:', error);
                alert('Error importing GPX file. Please check the file format.');
            }
        }

        createIcon(text, color) {
            return L.divIcon({
                html: `<div class="rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm border-2 border-white shadow-lg text-white" style="background-color: ${color}">${text}</div>`,
                className: 'custom-marker',
                iconSize: [32, 32],
                iconAnchor: [16, 16]
            });
        }

        updateRouteStats() {
            const distanceEl = document.getElementById('route-distance');
            const timeEl = document.getElementById('route-time');
            
            if (distanceEl) distanceEl.textContent = `${this.totalDistance.toFixed(2)} km`;
            if (timeEl) timeEl.textContent = `${Math.round(this.totalTime)} min`;
        }

        updateFormInputs() {
            const routeInput = document.getElementById('route-coordinates-input');
            if (routeInput && this.routeCoordinates) {
                // Ensure we're storing the actual array, not a stringified version
                routeInput.value = JSON.stringify(this.routeCoordinates);
            }

            // Set start/end coordinates
            if (this.routeCoordinates && this.routeCoordinates.length > 0) {
                const start = this.routeCoordinates[0];
                const end = this.routeCoordinates[this.routeCoordinates.length - 1];

                const startLatInput = document.getElementById('start-lat-input');
                const startLngInput = document.getElementById('start-lng-input');
                const endLatInput = document.getElementById('end-lat-input');
                const endLngInput = document.getElementById('end-lng-input');

                if (startLatInput) startLatInput.value = start[0];
                if (startLngInput) startLngInput.value = start[1];
                if (endLatInput) endLatInput.value = end[0];
                if (endLngInput) endLngInput.value = end[1];
            }
        }

        displayRoute(coordinates) {
            this.routeCoordinates = coordinates;

            const routeLine = L.polyline(coordinates, {
                color: '#3B82F6',
                weight: 4,
                opacity: 0.8
            }).addTo(this.routeLayer);

            // Add markers for start/end
            const start = coordinates[0];
            const end = coordinates[coordinates.length - 1];

            L.marker(start, {
                icon: this.createIcon('S', '#10B981')
            }).addTo(this.routeLayer).bindPopup('Trail Start');

            L.marker(end, {
                icon: this.createIcon('E', '#EF4444')
            }).addTo(this.routeLayer).bindPopup('Trail End');

            // Fit map to route
            this.map.fitBounds(routeLine.getBounds(), { padding: [20, 20] });
        }

        calculateStats(coordinates) {
            // Calculate distance
            let distance = 0;
            for (let i = 1; i < coordinates.length; i++) {
                distance += L.latLng(coordinates[i-1]).distanceTo(L.latLng(coordinates[i]));
            }
            
            this.totalDistance = distance / 1000; // Convert to km
            this.totalTime = (this.totalDistance / 4) * 60; // Assume 4 km/h

            // Update UI
            this.updateRouteStats();
            
            // Auto-populate form fields
            const distanceInput = document.querySelector('input[name="distance_km"]');
            const timeInput = document.querySelector('input[name="estimated_time_hours"]');
            
            if (distanceInput) distanceInput.value = this.totalDistance.toFixed(2);
            if (timeInput) timeInput.value = (this.totalTime / 60).toFixed(1);
        }
    }

    // Initialize trail builder when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        window.trailBuilder = new TrailBuilder();
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
</style>
@endpush
@endsection