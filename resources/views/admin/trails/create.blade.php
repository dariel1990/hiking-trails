@extends('layouts.admin')

@section('title', 'Add New Trail')
@section('page-title', 'Add New Trail')

@section('content')
<div class="max-w-6xl mx-auto">
    <form action="{{ route('admin.trails.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        
        <!-- Basic Information -->
        <div class="admin-card">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Basic Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Trail Name *</label>
                        <input type="text" name="name" required value="{{ old('name') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 @error('name') border-red-300 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                        <input type="text" name="location" value="{{ old('location') }}"
                               placeholder="e.g., North Vancouver, BC"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 @error('location') border-red-300 @enderror">
                        @error('location')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                        <textarea name="description" rows="4" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 @error('description') border-red-300 @enderror">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Trail Details -->
        <div class="admin-card">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Trail Details</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Difficulty Level (1-5) *</label>
                        <select name="difficulty_level" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 @error('difficulty_level') border-red-300 @enderror">
                            <option value="">Select difficulty</option>
                            <option value="1" {{ old('difficulty_level') == '1' ? 'selected' : '' }}>1 - Very Easy</option>
                            <option value="2" {{ old('difficulty_level') == '2' ? 'selected' : '' }}>2 - Easy</option>
                            <option value="3" {{ old('difficulty_level') == '3' ? 'selected' : '' }}>3 - Moderate</option>
                            <option value="4" {{ old('difficulty_level') == '4' ? 'selected' : '' }}>4 - Hard</option>
                            <option value="5" {{ old('difficulty_level') == '5' ? 'selected' : '' }}>5 - Very Hard</option>
                        </select>
                        @error('difficulty_level')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Distance (km) *</label>
                        <input type="number" name="distance_km" step="0.1" required value="{{ old('distance_km') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 @error('distance_km') border-red-300 @enderror">
                        @error('distance_km')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Elevation Gain (m) *</label>
                        <input type="number" name="elevation_gain_m" required value="{{ old('elevation_gain_m') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 @error('elevation_gain_m') border-red-300 @enderror">
                        @error('elevation_gain_m')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estimated Time (hours) *</label>
                        <input type="number" name="estimated_time_hours" step="0.5" required value="{{ old('estimated_time_hours') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 @error('estimated_time_hours') border-red-300 @enderror">
                        @error('estimated_time_hours')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Trail Type *</label>
                        <select name="trail_type" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 @error('trail_type') border-red-300 @enderror">
                            <option value="">Select type</option>
                            <option value="loop" {{ old('trail_type') == 'loop' ? 'selected' : '' }}>Loop</option>
                            <option value="out-and-back" {{ old('trail_type') == 'out-and-back' ? 'selected' : '' }}>Out and Back</option>
                            <option value="point-to-point" {{ old('trail_type') == 'point-to-point' ? 'selected' : '' }}>Point to Point</option>
                        </select>
                        @error('trail_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                        <select name="status" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 @error('status') border-red-300 @enderror">
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="closed" {{ old('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                            <option value="seasonal" {{ old('status') == 'seasonal' ? 'selected' : '' }}>Seasonal</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Types -->
        <div class="admin-card">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Activity Types</h3>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <label class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="activities[]" value="hiking" 
                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded" checked>
                        <span class="ml-3 text-sm text-gray-700">
                            <div class="font-medium">🥾 Hiking</div>
                            <div class="text-xs text-gray-500">Walking trails</div>
                        </span>
                    </label>

                    <label class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="activities[]" value="fishing" 
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <span class="ml-3 text-sm text-gray-700">
                            <div class="font-medium">🎣 Fishing</div>
                            <div class="text-xs text-gray-500">Fishing spots</div>
                        </span>
                    </label>

                    <label class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="activities[]" value="camping" 
                               class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
                        <span class="ml-3 text-sm text-gray-700">
                            <div class="font-medium">⛺ Camping</div>
                            <div class="text-xs text-gray-500">Camping areas</div>
                        </span>
                    </label>

                    <label class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="activities[]" value="viewpoint" 
                               class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                        <span class="ml-3 text-sm text-gray-700">
                            <div class="font-medium">👁️ Viewpoints</div>
                            <div class="text-xs text-gray-500">Scenic overlooks</div>
                        </span>
                    </label>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Activity Notes</label>
                    <textarea name="activity_notes" rows="2" placeholder="Additional notes about activities available on this trail..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">{{ old('activity_notes') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Location Coordinates -->
        <div class="admin-card">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Trail Coordinates</h3>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <div class="space-y-4">
                            <div>
                                <h4 class="font-medium text-gray-900 mb-3">Start Point *</h4>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-sm text-gray-700 mb-1">Latitude</label>
                                        <input type="number" name="start_lat" step="any" required value="{{ old('start_lat', '49.2827') }}"
                                               placeholder="49.2827"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 @error('start_lat') border-red-300 @enderror">
                                        @error('start_lat')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-700 mb-1">Longitude</label>
                                        <input type="number" name="start_lng" step="any" required value="{{ old('start_lng', '-122.7927') }}"
                                               placeholder="-122.7927"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 @error('start_lng') border-red-300 @enderror">
                                        @error('start_lng')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h4 class="font-medium text-gray-900 mb-3">End Point (optional)</h4>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-sm text-gray-700 mb-1">Latitude</label>
                                        <input type="number" name="end_lat" step="any" value="{{ old('end_lat') }}"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 @error('end_lat') border-red-300 @enderror">
                                        @error('end_lat')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-700 mb-1">Longitude</label>
                                        <input type="number" name="end_lng" step="any" value="{{ old('end_lng') }}"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 @error('end_lng') border-red-300 @enderror">
                                        @error('end_lng')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Trail Route (GPX)</label>
                                <input type="file" name="gpx_file" accept=".gpx"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <p class="text-xs text-gray-500 mt-1">Upload GPX file to automatically plot the trail route</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div id="trail-map" class="w-full h-80 border border-gray-300 rounded-md"></div>
                        <p class="mt-2 text-sm text-gray-500">
                            <strong>Instructions:</strong><br>
                            • Click once to set start point (green marker)<br>
                            • Click again to set end point (red marker)<br>
                            • Click start marker again to reset both points
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seasonal Information -->
        <div class="admin-card">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Seasonal Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Spring -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <span class="text-lg mr-2">🌸</span>
                            <h4 class="font-medium text-gray-900">Spring</h4>
                        </div>
                        
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Trail Conditions</label>
                                <input type="text" name="seasonal[spring][conditions]" 
                                       placeholder="e.g., Muddy, Snow patches"
                                       value="{{ old('seasonal.spring.conditions') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="seasonal[spring][recommended]" value="1" 
                                           {{ old('seasonal.spring.recommended', true) ? 'checked' : '' }}
                                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-700">Recommended in Spring</span>
                                </label>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea name="seasonal[spring][notes]" rows="2" 
                                          placeholder="Special spring considerations..."
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">{{ old('seasonal.spring.notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Summer -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <span class="text-lg mr-2">☀️</span>
                            <h4 class="font-medium text-gray-900">Summer</h4>
                        </div>
                        
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Trail Conditions</label>
                                <input type="text" name="seasonal[summer][conditions]" 
                                       placeholder="e.g., Dry, Clear"
                                       value="{{ old('seasonal.summer.conditions') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="seasonal[summer][recommended]" value="1" 
                                           {{ old('seasonal.summer.recommended', true) ? 'checked' : '' }}
                                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-700">Recommended in Summer</span>
                                </label>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea name="seasonal[summer][notes]" rows="2" 
                                          placeholder="Special summer considerations..."
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">{{ old('seasonal.summer.notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Fall -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <span class="text-lg mr-2">🍂</span>
                            <h4 class="font-medium text-gray-900">Fall</h4>
                        </div>
                        
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Trail Conditions</label>
                                <input type="text" name="seasonal[fall][conditions]" 
                                       placeholder="e.g., Wet leaves, Early snow"
                                       value="{{ old('seasonal.fall.conditions') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="seasonal[fall][recommended]" value="1" 
                                           {{ old('seasonal.fall.recommended', true) ? 'checked' : '' }}
                                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-700">Recommended in Fall</span>
                                </label>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea name="seasonal[fall][notes]" rows="2" 
                                          placeholder="Special fall considerations..."
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">{{ old('seasonal.fall.notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Winter -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <span class="text-lg mr-2">❄️</span>
                            <h4 class="font-medium text-gray-900">Winter</h4>
                        </div>
                        
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Trail Conditions</label>
                                <input type="text" name="seasonal[winter][conditions]" 
                                       placeholder="e.g., Snow, Ice, Closed"
                                       value="{{ old('seasonal.winter.conditions') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="seasonal[winter][recommended]" value="1" 
                                           {{ old('seasonal.winter.recommended', false) ? 'checked' : '' }}
                                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-700">Recommended in Winter</span>
                                </label>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea name="seasonal[winter][notes]" rows="2" 
                                          placeholder="Special winter considerations..."
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">{{ old('seasonal.winter.notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="admin-card">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Additional Information</h3>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Best Seasons</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            @php $seasons = ['Spring', 'Summer', 'Fall', 'Winter']; @endphp
                            @foreach($seasons as $season)
                                <label class="flex items-center">
                                    <input type="checkbox" name="best_seasons[]" value="{{ $season }}" 
                                           {{ in_array($season, old('best_seasons', ['Spring', 'Summer', 'Fall'])) ? 'checked' : '' }}
                                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-700">{{ $season }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Directions to Trailhead</label>
                        <textarea name="directions" rows="3" placeholder="How to get to the trailhead..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">{{ old('directions') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Parking Information</label>
                        <textarea name="parking_info" rows="3" placeholder="Parking availability, fees, restrictions..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">{{ old('parking_info') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Safety Notes & Warnings</label>
                        <textarea name="safety_notes" rows="3" placeholder="Important safety information, hazards, equipment needed..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">{{ old('safety_notes') }}</textarea>
                    </div>

                    <div class="flex items-center space-x-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_featured" value="1" 
                                   {{ old('is_featured') ? 'checked' : '' }}
                                   class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Feature this trail on homepage</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Photo Upload -->
        <div class="admin-card">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Photos</h3>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload Trail Photos</label>
                    <input type="file" name="photos[]" multiple accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <p class="text-sm text-gray-500 mt-1">Select multiple photos. First photo will be used as featured image.</p>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-3 pb-6">
            <a href="{{ route('admin.trails.index') }}" class="admin-button-secondary">Cancel</a>
            <button type="submit" class="admin-button-primary">Create Trail</button>
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
            
            document.querySelector('input[name="start_lat"]').value = '';
            document.querySelector('input[name="start_lng"]').value = '';
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
@endpush
@endsection