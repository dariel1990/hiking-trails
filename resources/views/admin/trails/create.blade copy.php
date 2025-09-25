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

        <!-- Location Coordinates -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 space-y-6">
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold">Location & Coordinates</h3>
                    <p class="text-sm text-muted-foreground">Set the trail start and end points on the map</p>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="space-y-6">
                        <!-- Start Point -->
                        <div class="space-y-4">
                            <h4 class="font-medium">Start Point <span class="text-red-500">*</span></h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-muted-foreground">Latitude</label>
                                    <input type="number" name="start_lat" step="any" required value="{{ old('start_lat', '49.2827') }}"
                                           placeholder="49.2827"
                                           class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 @error('start_lat') border-red-300 @enderror">
                                    @error('start_lat')
                                        <p class="text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-muted-foreground">Longitude</label>
                                    <input type="number" name="start_lng" step="any" required value="{{ old('start_lng', '-122.7927') }}"
                                           placeholder="-122.7927"
                                           class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 @error('start_lng') border-red-300 @enderror">
                                    @error('start_lng')
                                        <p class="text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- End Point -->
                        <div class="space-y-4">
                            <h4 class="font-medium">End Point <span class="text-xs text-muted-foreground font-normal">(optional)</span></h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-muted-foreground">Latitude</label>
                                    <input type="number" name="end_lat" step="any" value="{{ old('end_lat') }}"
                                           class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 @error('end_lat') border-red-300 @enderror">
                                    @error('end_lat')
                                        <p class="text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-muted-foreground">Longitude</label>
                                    <input type="number" name="end_lng" step="any" value="{{ old('end_lng') }}"
                                           class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 @error('end_lng') border-red-300 @enderror">
                                    @error('end_lng')
                                        <p class="text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- GPX Upload -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                Trail Route (GPX)
                            </label>
                            <div class="grid w-full max-w-sm items-center gap-1.5">
                                <input type="file" name="gpx_file" accept=".gpx"
                                       class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                <p class="text-xs text-muted-foreground">Upload GPX file to automatically plot the trail route</p>
                            </div>
                        </div>
                    </div>

                    <!-- Map -->
                    <div class="space-y-4">
                        <div id="trail-map" class="w-full h-80 rounded-md border border-input bg-muted"></div>
                        <div class="rounded-md bg-muted p-3">
                            <p class="text-sm font-medium mb-2">Map Instructions:</p>
                            <ul class="text-xs text-muted-foreground space-y-1">
                                <li>• Click once to set start point (green marker)</li>
                                <li>• Click again to set end point (red marker)</li>
                                <li>• Click start marker again to reset both points</li>
                            </ul>
                        </div>
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
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize map
        const map = L.map('trail-map').setView([49.2827, -122.7927], 10);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        let startMarker = null;
        let endMarker = null;

        // Create custom icons
        const startIcon = L.divIcon({
            html: '<div class="bg-green-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm border-2 border-white shadow-lg">S</div>',
            className: 'custom-marker',
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });

        const endIcon = L.divIcon({
            html: '<div class="bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm border-2 border-white shadow-lg">E</div>',
            className: 'custom-marker',
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });

        // Handle map clicks
        map.on('click', function(e) {
            const lat = e.latlng.lat.toFixed(6);
            const lng = e.latlng.lng.toFixed(6);
            
            if (!startMarker) {
                // Set start point
                startMarker = L.marker([lat, lng], { icon: startIcon }).addTo(map);
                startMarker.bindPopup('<b>Trail Start</b><br>Click to reset both markers').openPopup();
                
                document.querySelector('input[name="start_lat"]').value = lat;
                document.querySelector('input[name="start_lng"]').value = lng;

                // Add click handler to start marker for reset
                startMarker.on('click', function(e) {
                    e.originalEvent.stopPropagation();
                    resetMarkers();
                });

            } else if (!endMarker) {
                // Set end point
                endMarker = L.marker([lat, lng], { icon: endIcon }).addTo(map);
                endMarker.bindPopup('<b>Trail End</b>').openPopup();
                
                document.querySelector('input[name="end_lat"]').value = lat;
                document.querySelector('input[name="end_lng"]').value = lng;
            } else {
                // If both markers exist, reset and set new start
                resetMarkers();
                startMarker = L.marker([lat, lng], { icon: startIcon }).addTo(map);
                startMarker.bindPopup('<b>Trail Start</b><br>Click to reset both markers').openPopup();
                
                document.querySelector('input[name="start_lat"]').value = lat;
                document.querySelector('input[name="start_lng"]').value = lng;

                startMarker.on('click', function(e) {
                    e.originalEvent.stopPropagation();
                    resetMarkers();
                });
            }
        });

        function resetMarkers() {
            if (startMarker) {
                map.removeLayer(startMarker);
                startMarker = null;
            }
            if (endMarker) {
                map.removeLayer(endMarker);
                endMarker = null;
            }
            
            document.querySelector('input[name="start_lat"]').value = '49.2827';
            document.querySelector('input[name="start_lng"]').value = '-122.7927';
            document.querySelector('input[name="end_lat"]').value = '';
            document.querySelector('input[name="end_lng"]').value = '';
        }

        // Set markers from existing values if available
        const startLat = document.querySelector('input[name="start_lat"]').value;
        const startLng = document.querySelector('input[name="start_lng"]').value;
        if (startLat && startLng) {
            startMarker = L.marker([startLat, startLng], { icon: startIcon }).addTo(map);
            startMarker.bindPopup('<b>Trail Start</b><br>Click to reset both markers');
            
            startMarker.on('click', function(e) {
                e.originalEvent.stopPropagation();
                resetMarkers();
            });
        }

        const endLat = document.querySelector('input[name="end_lat"]').value;
        const endLng = document.querySelector('input[name="end_lng"]').value;
        if (endLat && endLng) {
            endMarker = L.marker([endLat, endLng], { icon: endIcon }).addTo(map);
            endMarker.bindPopup('<b>Trail End</b>');
        }

        // Handle coordinate input changes
        document.querySelectorAll('input[name="start_lat"], input[name="start_lng"]').forEach(input => {
            input.addEventListener('change', updateStartMarker);
        });

        document.querySelectorAll('input[name="end_lat"], input[name="end_lng"]').forEach(input => {
            input.addEventListener('change', updateEndMarker);
        });

        function updateStartMarker() {
            const lat = document.querySelector('input[name="start_lat"]').value;
            const lng = document.querySelector('input[name="start_lng"]').value;
            
            if (lat && lng) {
                if (startMarker) map.removeLayer(startMarker);
                startMarker = L.marker([lat, lng], { icon: startIcon }).addTo(map);
                startMarker.bindPopup('<b>Trail Start</b><br>Click to reset both markers');
                
                startMarker.on('click', function(e) {
                    e.originalEvent.stopPropagation();
                    resetMarkers();
                });
                
                map.setView([lat, lng], 13);
            }
        }

        function updateEndMarker() {
            const lat = document.querySelector('input[name="end_lat"]').value;
            const lng = document.querySelector('input[name="end_lng"]').value;
            
            if (lat && lng) {
                if (endMarker) map.removeLayer(endMarker);
                endMarker = L.marker([lat, lng], { icon: endIcon }).addTo(map);
                endMarker.bindPopup('<b>Trail End</b>');
            }
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
</style>
@endpush
@endsection