<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trail;
use App\Models\TrailMedia;
use App\Services\GpxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\TrailFeature;

use Exception;

class AdminTrailController extends Controller
{
    protected $gpxService;

    public function __construct(GpxService $gpxService)
    {
        $this->gpxService = $gpxService;
    }

    /**
     * Display a listing of trails
     */
    public function index(Request $request)
    {
        $query = Trail::query();

        if ($request->search) {
            $query->search($request->search);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $trails = $query->latest()->paginate(15);

        return view('admin.trails.index', compact('trails'));
    }

    /**
     * Show the form for creating a new trail
     */
    public function create()
    {
        return view('admin.trails.create');
    }

    /**
     * Store a newly created trail
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'nullable|string|max:255',
            'difficulty_level' => 'required|numeric|between:1,5',
            'distance_km' => 'required|numeric|min:0',
            'elevation_gain_m' => 'required|integer|min:0',
            'estimated_time_hours' => 'required|numeric|min:0',
            'trail_type' => 'required|in:loop,out-and-back,point-to-point',
            'start_lat' => 'required|numeric|between:-90,90',
            'start_lng' => 'required|numeric|between:-180,180',
            'end_lat' => 'nullable|numeric|between:-90,90',
            'end_lng' => 'nullable|numeric|between:-180,180',
            'status' => 'required|in:active,closed,seasonal',
            'best_seasons' => 'nullable|array',
            'best_seasons.*' => 'string|in:Spring,Summer,Fall,Winter',
            'directions' => 'nullable|string',
            'parking_info' => 'nullable|string',
            'safety_notes' => 'nullable|string',
            'is_featured' => 'boolean',
            'route_coordinates' => 'nullable|string',
            'waypoints' => 'nullable|string',
            'photos' => 'nullable|array|max:10',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240',
            'trail_video_urls' => 'nullable|array',
            'trail_video_urls.*' => 'nullable|url|max:500',
            'highlight_media_*' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:10240',
            'highlight_video_url_*' => 'nullable|url|max:500',
            'gpx_file' => 'nullable|file|mimes:gpx,xml|max:10240',
            'activities' => 'nullable|array',
            'activities.*' => 'string',
            'activity_notes' => 'nullable|string|max:1000',
            'seasonal' => 'nullable|array',
            'seasonal.*.conditions' => 'nullable|string|max:255',
            'seasonal.*.recommended' => 'nullable',
            'seasonal.*.notes' => 'nullable|string|max:1000',
        ]);

        // Prepare trail data
        $data = $request->only([
            'name', 'description', 'location', 'difficulty_level', 
            'distance_km', 'elevation_gain_m', 'estimated_time_hours', 
            'trail_type', 'status', 'best_seasons', 'directions', 
            'parking_info', 'safety_notes', 'is_featured'
        ]);

        // Set coordinates
        $data['start_coordinates'] = [$request->start_lat, $request->start_lng];
        
        if ($request->end_lat && $request->end_lng) {
            $data['end_coordinates'] = [$request->end_lat, $request->end_lng];
        } else {
            $data['end_coordinates'] = null;
        }

        // Handle route coordinates
        if ($request->has('route_coordinates')) {
            $data['route_coordinates'] = json_decode($request->route_coordinates);
        }

        // Handle GPX file upload
        if ($request->hasFile('gpx_file')) {
            try {
                $gpxFile = $request->file('gpx_file');
                $filename = Str::random(40) . '.gpx';
                $path = $gpxFile->storeAs('gpx', $filename, 'public');
                $data['gpx_file_path'] = $path;

                // If user wants to use GPX calculations
                if ($request->input('use_gpx_calculations') === 'true') {
                    $fullPath = storage_path('app/public/' . $path);
                    $gpxData = $this->gpxService->calculateAllFromGpx($fullPath, $request->difficulty_level);
                    
                    // Override form values with GPX calculations
                    $data['distance_km'] = $gpxData['distance'];
                    $data['elevation_gain_m'] = $gpxData['elevation'];
                    $data['estimated_time_hours'] = $gpxData['time'];
                    $data['route_coordinates'] = $gpxData['coordinates'];
                    
                    // Store GPX calculated values
                    $data['gpx_calculated_distance'] = $gpxData['distance'];
                    $data['gpx_calculated_elevation'] = $gpxData['elevation'];
                    $data['gpx_calculated_time'] = $gpxData['time'];
                    $data['data_source'] = 'gpx';
                    $data['gpx_uploaded_at'] = now();
                }
            } catch (Exception $e) {
                return back()
                    ->withInput()
                    ->withErrors(['gpx_file' => 'Error processing GPX file: ' . $e->getMessage()]);
            }
        }

        // Create the trail
        $trail = Trail::create($data);

        // Handle Activities
        if ($request->has('activities') && is_array($request->activities)) {
            foreach ($request->activities as $activityValue) {
                $activityType = \App\Models\ActivityType::firstOrCreate(
                    ['slug' => $activityValue],
                    ['name' => ucfirst($activityValue)]
                );
                
                $trail->activities()->attach($activityType->id, [
                    'activity_notes' => $request->activity_notes,
                ]);
            }
        }

        // Handle Seasonal Data
        if ($request->has('seasonal') && is_array($request->seasonal)) {
            foreach ($request->seasonal as $season => $seasonData) {
                if (!empty($seasonData['conditions']) || !empty($seasonData['notes'])) {
                    \App\Models\SeasonalTrailData::create([
                        'trail_id' => $trail->id,
                        'season' => $season,
                        'conditions' => $seasonData['conditions'] ?? null,
                        'recommended' => isset($seasonData['recommended']) && $seasonData['recommended'] == '1',
                        'notes' => $seasonData['notes'] ?? null,
                    ]);
                }
            }
        }

        // Handle photos (Trail-level media)
        if ($request->hasFile('photos')) {
            $featuredPhotoIndex = (int) $request->input('featured_photo_index', 0);
            
            foreach ($request->file('photos') as $index => $photo) {
                $filename = Str::random(40) . '.' . $photo->getClientOriginalExtension();
                $path = $photo->storeAs('trail-photos', $filename, 'public');

                TrailMedia::create([
                    'trail_id' => $trail->id,
                    'media_type' => 'photo',
                    'filename' => $filename,
                    'original_name' => $photo->getClientOriginalName(),
                    'storage_path' => $path,
                    'file_size' => $photo->getSize(),
                    'mime_type' => $photo->getMimeType(),
                    'sort_order' => $index,
                    'is_featured' => $index === $featuredPhotoIndex,
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        // Handle new video URLs - support both JSON and array format
        $videoUrls = null;

        // Try JSON format first (from permanent hidden input)
        if ($request->has('trail_video_urls_json') && !empty($request->input('trail_video_urls_json'))) {
            $videoUrls = json_decode($request->input('trail_video_urls_json'), true);
            \Log::info('Video URLs from JSON:', ['urls' => $videoUrls]);
        }

        // Fallback to array format
        if (empty($videoUrls) && $request->has('trail_video_urls')) {
            $videoUrls = $request->input('trail_video_urls');
            \Log::info('Video URLs from array:', ['urls' => $videoUrls]);
        }

        // Save video URLs to TrailMedia table
        if (!empty($videoUrls) && is_array($videoUrls)) {
            $photoCount = $request->hasFile('photos') ? count($request->file('photos')) : 0;
            $featuredPhotoIndex = (int) $request->input('featured_photo_index', 0);
            
            foreach ($videoUrls as $index => $videoUrl) {
                if (!empty($videoUrl)) {
                    $sortOrder = $photoCount + $index;
                    
                    TrailMedia::create([
                        'trail_id' => $trail->id,
                        'media_type' => 'video_url',
                        'video_url' => $videoUrl,
                        'video_provider' => $this->detectVideoProvider($videoUrl),
                        'sort_order' => $sortOrder,
                        'is_featured' => $sortOrder === $featuredPhotoIndex,
                        'uploaded_by' => auth()->id(),
                    ]);
                    
                    \Log::info('Video URL saved:', [
                        'trail_id' => $trail->id,
                        'url' => $videoUrl,
                        'sort_order' => $sortOrder
                    ]);
                }
            }
        }

        // Handle features/highlights with optional media
        if ($request->has('highlights_data')) {
            $highlightsData = json_decode($request->highlights_data, true);
            
            if (is_array($highlightsData)) {
                foreach ($highlightsData as $index => $highlightData) {
                    // Create the feature
                    $feature = $trail->features()->create([
                        'feature_type' => $highlightData['type'],
                        'name' => $highlightData['name'],
                        'description' => $highlightData['description'] ?? null,
                        'coordinates' => $highlightData['coordinates'],
                    ]);
                    
                    // Handle photo file if exists for this highlight
                    if (isset($highlightData['mediaIndex']) && $request->hasFile("highlight_media_{$highlightData['mediaIndex']}")) {
                        $mediaFile = $request->file("highlight_media_{$highlightData['mediaIndex']}");
                        
                        // Generate filename and store (photos only)
                        $filename = Str::random(40) . '.' . $mediaFile->getClientOriginalExtension();
                        $path = $mediaFile->storeAs('trail-photos', $filename, 'public');
                        
                        // Create media record
                        $media = TrailMedia::create([
                            'trail_id' => $trail->id,
                            'media_type' => 'photo',
                            'filename' => $filename,
                            'original_name' => $mediaFile->getClientOriginalName(),
                            'storage_path' => $path,
                            'file_size' => $mediaFile->getSize(),
                            'mime_type' => $mediaFile->getMimeType(),
                            'sort_order' => 0,
                            'is_featured' => false,
                            'uploaded_by' => auth()->id(),
                        ]);
                        
                        // Link media to feature
                        $feature->media()->attach($media->id, [
                            'is_primary' => true,
                            'sort_order' => 0,
                        ]);
                        
                        // Update cached count
                        $feature->updateMediaCount();
                    }
                    
                    // Handle video URL if exists for this highlight
                    if (isset($highlightData['videoIndex']) && $request->has("highlight_video_url_{$highlightData['videoIndex']}")) {
                        $videoUrl = $request->input("highlight_video_url_{$highlightData['videoIndex']}");
                        
                        if (!empty($videoUrl)) {
                            // Create media record for video URL
                            $media = TrailMedia::create([
                                'trail_id' => $trail->id,
                                'media_type' => 'video_url', // Changed
                                'video_url' => $videoUrl,
                                'video_provider' => $this->detectVideoProvider($videoUrl),
                                'sort_order' => 0,
                                'is_featured' => false,
                                'uploaded_by' => auth()->id(),
                            ]);
                            
                            // Link media to feature
                            $feature->media()->attach($media->id, [
                                'is_primary' => true,
                                'sort_order' => 0,
                            ]);
                            
                            // Update cached count
                            $feature->updateMediaCount();
                        }
                    }
                }
            }
        }

        return redirect()->route('admin.trails.show', $trail)
            ->with('success', 'Trail created successfully!');
    }
    /**
     * Display the specified trail
     */
    public function show(Trail $trail)
    {
        $trail->load(['media', 'features.media']);
        return view('admin.trails.show', compact('trail'));
    }

    /**
     * Show the form for editing the specified trail
     */
    public function edit(Trail $trail)
    {
        $trail->load(['features' => function($query) {
            $query->with('media'); // Ensure media is loaded
        }, 'media']);
        
        // Transform media to include full URL
        $trail->media->transform(function($media) {
            if ($media->storage_path) {
                $media->url = Storage::url($media->storage_path);
            }
            return $media;
        });

        foreach ($trail->features as $feature) {
            if ($feature->media) {
                $feature->media->transform(function($media) {
                    if ($media->storage_path) {
                        $media->url = Storage::url($media->storage_path);
                    }
                    return $media;
                });
            }
        }
        
        // Ensure route_coordinates is an array
        if ($trail->route_coordinates && is_string($trail->route_coordinates)) {
            $trail->route_coordinates = json_decode($trail->route_coordinates, true);
        }
        
        return view('admin.trails.edit', compact('trail'));
    }

    /**
     * Update the specified trail
     */
    public function update(Request $request, Trail $trail)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'nullable|string|max:255',
            'difficulty_level' => 'required|numeric|between:1,5',
            'distance_km' => 'required|numeric|min:0',
            'elevation_gain_m' => 'required|integer|min:0',
            'estimated_time_hours' => 'required|numeric|min:0',
            'trail_type' => 'required|in:loop,out-and-back,point-to-point',
            'start_lat' => 'required|numeric|between:-90,90',
            'start_lng' => 'required|numeric|between:-180,180',
            'end_lat' => 'nullable|numeric|between:-90,90',
            'end_lng' => 'nullable|numeric|between:-180,180',
            'status' => 'required|in:active,closed,seasonal',
            'best_seasons' => 'nullable|array',
            'best_seasons.*' => 'string|in:Spring,Summer,Fall,Winter',
            'directions' => 'nullable|string',
            'parking_info' => 'nullable|string',
            'safety_notes' => 'nullable|string',
            'is_featured' => 'boolean',
            'photos' => 'nullable|array|max:10',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240',
            'trail_video_urls' => 'nullable|array',
            'trail_video_urls.*' => 'nullable|url|max:500',
            'highlight_media_*' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:10240',
            'highlight_video_url_*' => 'nullable|url|max:500',
            'deleted_photos' => 'nullable|string',
            'deleted_features' => 'nullable|string',
            'featured_photo_id' => 'nullable|integer',
            'gpx_file' => 'nullable|file|mimes:gpx,xml|max:10240',
            'gpx_action' => 'nullable|in:keep_manual,use_gpx',
            'activities' => 'nullable|array',
            'activities.*' => 'string',
            'activity_notes' => 'nullable|string|max:1000',
            'seasonal' => 'nullable|array',
            'seasonal.*.conditions' => 'nullable|string|max:255',
            'seasonal.*.recommended' => 'nullable',
            'seasonal.*.notes' => 'nullable|string|max:1000',
        ]);

        // Prepare trail data
        $data = $request->only([
            'name', 'description', 'location', 'difficulty_level', 
            'distance_km', 'elevation_gain_m', 'estimated_time_hours', 
            'trail_type', 'status', 'best_seasons', 'directions', 
            'parking_info', 'safety_notes', 'is_featured'
        ]);

        // Set coordinates
        $data['start_coordinates'] = [$request->start_lat, $request->start_lng];
        
        if ($request->end_lat && $request->end_lng) {
            $data['end_coordinates'] = [$request->end_lat, $request->end_lng];
        } else {
            $data['end_coordinates'] = null;
        }

        // Handle route coordinates
        if ($request->has('route_coordinates')) {
            $data['route_coordinates'] = json_decode($request->route_coordinates);
        }

        // Handle GPX file re-upload
        if ($request->hasFile('gpx_file')) {
            try {
                // Delete old GPX file if exists
                if ($trail->gpx_file_path) {
                    Storage::disk('public')->delete($trail->gpx_file_path);
                }

                $gpxFile = $request->file('gpx_file');
                $filename = Str::random(40) . '.gpx';
                $path = $gpxFile->storeAs('gpx', $filename, 'public');
                $data['gpx_file_path'] = $path;

                $fullPath = storage_path('app/public/' . $path);
                $gpxData = $this->gpxService->calculateAllFromGpx($fullPath, $request->difficulty_level);

                // Check user's choice: keep manual values or use new GPX
                if ($request->input('gpx_action') === 'use_gpx') {
                    $data['distance_km'] = $gpxData['distance'];
                    $data['elevation_gain_m'] = $gpxData['elevation'];
                    $data['estimated_time_hours'] = $gpxData['time'];
                    $data['route_coordinates'] = $gpxData['coordinates'];
                    $data['data_source'] = 'gpx';
                } else {
                    $data['data_source'] = 'mixed';
                }

                // Always store GPX calculated values for reference
                $data['gpx_calculated_distance'] = $gpxData['distance'];
                $data['gpx_calculated_elevation'] = $gpxData['elevation'];
                $data['gpx_calculated_time'] = $gpxData['time'];
                $data['gpx_uploaded_at'] = now();
                
            } catch (Exception $e) {
                return back()
                    ->withInput()
                    ->withErrors(['gpx_file' => 'Error processing GPX file: ' . $e->getMessage()]);
            }
        }

        // Update the trail
        $trail->update($data);

        // Update Activities
        $trail->activities()->detach(); // Remove all existing
        
        if ($request->has('activities') && is_array($request->activities)) {
            foreach ($request->activities as $activityValue) {
                $activityType = \App\Models\ActivityType::firstOrCreate(
                    ['slug' => $activityValue],
                    ['name' => ucfirst($activityValue)]
                );
                
                $trail->activities()->attach($activityType->id, [
                    'activity_notes' => $request->activity_notes,
                ]);
            }
        }

        // Update Seasonal Data
        $trail->seasonalData()->delete(); // Remove all existing
        
        if ($request->has('seasonal') && is_array($request->seasonal)) {
            foreach ($request->seasonal as $season => $seasonData) {
                if (!empty($seasonData['conditions']) || !empty($seasonData['notes'])) {
                    \App\Models\SeasonalTrailData::create([
                        'trail_id' => $trail->id,
                        'season' => $season,
                        'conditions' => $seasonData['conditions'] ?? null,
                        'recommended' => isset($seasonData['recommended']) && $seasonData['recommended'] == '1',
                        'notes' => $seasonData['notes'] ?? null,
                    ]);
                }
            }
        }

        // Handle new highlights/features
        if ($request->has('highlights_data')) {
            $newHighlights = json_decode($request->highlights_data, true);
            
            if (is_array($newHighlights)) {
                foreach ($newHighlights as $index => $highlightData) {
                    // Create the feature
                    $feature = $trail->features()->create([
                        'feature_type' => $highlightData['type'],
                        'name' => $highlightData['name'],
                        'description' => $highlightData['description'] ?? null,
                        'coordinates' => $highlightData['coordinates'],
                    ]);
                    
                    // Handle photo file if exists for this highlight
                    if (isset($highlightData['mediaIndex']) && $request->hasFile("highlight_media_{$highlightData['mediaIndex']}")) {
                        $mediaFile = $request->file("highlight_media_{$highlightData['mediaIndex']}");
                        
                        // Generate filename and store (photos only)
                        $filename = Str::random(40) . '.' . $mediaFile->getClientOriginalExtension();
                        $path = $mediaFile->storeAs('trail-photos', $filename, 'public');
                        
                        // Create media record
                        $media = TrailMedia::create([
                            'trail_id' => $trail->id,
                            'media_type' => 'photo',
                            'filename' => $filename,
                            'original_name' => $mediaFile->getClientOriginalName(),
                            'storage_path' => $path,
                            'file_size' => $mediaFile->getSize(),
                            'mime_type' => $mediaFile->getMimeType(),
                            'sort_order' => 0,
                            'is_featured' => false,
                            'uploaded_by' => auth()->id(),
                        ]);
                        
                        // Link media to feature
                        $feature->media()->attach($media->id, [
                            'is_primary' => true,
                            'sort_order' => 0,
                        ]);
                        
                        // Update cached count
                        $feature->updateMediaCount();
                    }
                    
                    // Handle video URL if exists for this highlight
                    if (isset($highlightData['videoIndex']) && $request->has("highlight_video_url_{$highlightData['videoIndex']}")) {
                         $videoUrl = $request->input("highlight_video_url_{$highlightData['videoIndex']}");
                        
                        if (!empty($videoUrl)) {
                            // Create media record for video URL
                            $media = TrailMedia::create([
                                'trail_id' => $trail->id,
                                'media_type' => 'video_url', // Changed
                                'video_url' => $videoUrl,
                                'video_provider' => $this->detectVideoProvider($videoUrl),
                                'sort_order' => 0,
                                'is_featured' => false,
                                'uploaded_by' => auth()->id(),
                            ]);
                            
                            // Link media to feature
                            $feature->media()->attach($media->id, [
                                'is_primary' => true,
                                'sort_order' => 0,
                            ]);
                            
                            // Update cached count
                            $feature->updateMediaCount();
                        }
                    }
                }
            }
        }

        // Handle deleted features
        if ($request->has('deleted_features')) {
            $deletedIds = json_decode($request->deleted_features, true);
            
            if (is_array($deletedIds)) {
                $trail->features()->whereIn('id', $deletedIds)->delete();
            }
        }

        // Handle deleted photos
        if ($request->has('deleted_photos')) {
            $deletedIds = json_decode($request->deleted_photos, true);
            
            if (is_array($deletedIds)) {
                foreach ($deletedIds as $photoId) {
                    $photo = TrailMedia::find($photoId);
                    if ($photo && $photo->trail_id === $trail->id) {
                        // Delete from storage
                        Storage::disk('public')->delete($photo->storage_path);
                        // Delete record
                        $photo->delete();
                    }
                }
            }
        }

        // Handle new photos
        if ($request->hasFile('photos')) {
            // Get the current max sort order
            $maxSortOrder = $trail->media()->max('sort_order') ?? -1;
            
            foreach ($request->file('photos') as $index => $photo) {
                $filename = Str::random(40) . '.' . $photo->getClientOriginalExtension();
                $path = $photo->storeAs('trail-photos', $filename, 'public');

                TrailMedia::create([
                    'trail_id' => $trail->id,
                    'media_type' => 'photo',
                    'filename' => $filename,
                    'original_name' => $photo->getClientOriginalName(),
                    'storage_path' => $path,
                    'file_size' => $photo->getSize(),
                    'mime_type' => $photo->getMimeType(),
                    'sort_order' => $maxSortOrder + $index + 1,
                    'is_featured' => false, // Don't auto-feature new photos
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        // Handle new video URLs - support both JSON and array format
        $videoUrls = null;

        // Try JSON format first (from permanent hidden input)
        if ($request->has('trail_video_urls_json') && !empty($request->input('trail_video_urls_json'))) {
            $videoUrls = json_decode($request->input('trail_video_urls_json'), true);
            \Log::info('Video URLs from JSON (update):', ['urls' => $videoUrls]);
        }

        // Fallback to array format
        if (empty($videoUrls) && $request->has('trail_video_urls')) {
            $videoUrls = $request->input('trail_video_urls');
            \Log::info('Video URLs from array (update):', ['urls' => $videoUrls]);
        }

        // Save new video URLs to TrailMedia table
        if (!empty($videoUrls) && is_array($videoUrls)) {
            $maxSortOrder = $trail->media()->max('sort_order') ?? -1;
            $photoCount = $request->hasFile('photos') ? count($request->file('photos')) : 0;
            
            foreach ($videoUrls as $index => $videoUrl) {
                if (!empty($videoUrl)) {
                    TrailMedia::create([
                        'trail_id' => $trail->id,
                        'media_type' => 'video_url',
                        'video_url' => $videoUrl,
                        'video_provider' => $this->detectVideoProvider($videoUrl),
                        'sort_order' => $maxSortOrder + $photoCount + $index + 1,
                        'is_featured' => false,
                        'uploaded_by' => auth()->id(),
                    ]);
                    
                    \Log::info('Video URL saved (update):', [
                        'trail_id' => $trail->id,
                        'url' => $videoUrl,
                        'sort_order' => $maxSortOrder + $photoCount + $index + 1
                    ]);
                }
            }
        }

        // Update featured photo if specified
        if ($request->has('featured_photo_id') && $request->featured_photo_id) {
            // Remove featured status from all photos
            $trail->media()->update(['is_featured' => false]);
            
            // Set new featured photo
            $trail->media()->where('id', $request->featured_photo_id)->update(['is_featured' => true]);
        }

        // Update featured photo if specified
        if ($request->has('featured_photo_id') && $request->featured_photo_id) {
            // Remove featured status from all photos
            $trail->media()->update(['is_featured' => false]);
            
            // Set new featured photo
            $trail->media()->where('id', $request->featured_photo_id)->update(['is_featured' => true]);
        }

        return redirect()->route('admin.trails.show', $trail)
            ->with('success', 'Trail updated successfully!');
    }

    /**
     * Remove the specified trail
     */
    public function destroy(Trail $trail)
    {
        // Delete associated media from storage
        foreach ($trail->media as $media) {
            if ($media->storage_path) {
                Storage::disk('public')->delete($media->storage_path);
            }
        }

        // Delete GPX file if exists
        if ($trail->gpx_file_path) {
            Storage::disk('public')->delete($trail->gpx_file_path);
        }

        $trail->delete();

        return redirect()->route('admin.trails.index')
            ->with('success', 'Trail deleted successfully!');
    }

    // ============================================
    // NEW: GPX-RELATED API METHODS
    // ============================================

    /**
     * Preview GPX calculations before saving
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function previewGpx(Request $request)
    {
        $request->validate([
            'gpx_file' => 'required|file|mimes:gpx,xml|max:10240',
            'difficulty_level' => 'required|numeric|between:1,5',
        ]);

        try {
            $gpxFile = $request->file('gpx_file');
            
            // Use public storage path which is guaranteed to work
            $filename = 'temp_' . uniqid() . '.gpx';
            $gpxFile->storeAs('gpx/temp', $filename, 'public');
            $fullPath = storage_path('app/public/gpx/temp/' . $filename);
            
            // Debug: Check if file exists
            if (!file_exists($fullPath)) {
                \Log::error('GPX file not found at: ' . $fullPath);
                throw new Exception('Failed to save GPX file. Please check storage permissions.');
            }

            // Calculate values
            $gpxData = $this->gpxService->calculateAllFromGpx($fullPath, $request->difficulty_level);

            // Get statistics  
            $stats = $this->gpxService->getGpxStatistics($fullPath);

            // Clean up temp file
            Storage::disk('public')->delete('gpx/temp/' . $filename);

            return response()->json([
                'success' => true,
                'data' => [
                    'distance' => $gpxData['distance'],
                    'elevation' => $gpxData['elevation'],
                    'time' => $gpxData['time'],
                    'coordinates' => $gpxData['coordinates'],
                    'point_count' => $gpxData['point_count'],
                    'stats' => $stats,
                ],
            ]);
        } catch (Exception $e) {
            \Log::error('GPX Preview Error: ' . $e->getMessage());
            
            // Provide user-friendly error messages
            $userMessage = $e->getMessage();
            
            if (str_contains($userMessage, 'No tracks found')) {
                $userMessage = 'No valid GPS tracks found in this GPX file. Please ensure your GPX file contains track data.';
            } elseif (str_contains($userMessage, 'Failed to save')) {
                $userMessage = 'Unable to save the GPX file. Please check storage permissions or try again.';
            } elseif (str_contains($userMessage, 'parse')) {
                $userMessage = 'Unable to read this GPX file. Please ensure it\'s a valid GPX format.';
            }
            
            return response()->json([
                'success' => false,
                'message' => $userMessage,
            ], 422);
        }
    }

    /**
     * Compare new GPX with existing trail data
     * 
     * @param Request $request
     * @param Trail $trail
     * @return \Illuminate\Http\JsonResponse
     */
    public function compareGpx(Request $request, Trail $trail)
    {
        $request->validate([
            'gpx_file' => 'required|file|mimes:gpx,xml|max:10240',
        ]);

        try {
            $gpxFile = $request->file('gpx_file');
            
            // Use public storage path
            $filename = 'temp_' . uniqid() . '.gpx';
            $gpxFile->storeAs('gpx/temp', $filename, 'public');
            $fullPath = storage_path('app/public/gpx/temp/' . $filename);
            
            if (!file_exists($fullPath)) {
                throw new Exception('Failed to save GPX file. Please check storage permissions.');
            }

            // Calculate new values
            $newGpxData = $this->gpxService->calculateAllFromGpx($fullPath, $trail->difficulty_level);

            // Clean up temp file
            Storage::disk('public')->delete('gpx/temp/' . $filename);

            // Rest of the comparison logic...
            $currentData = [
                'distance' => $trail->distance_km,
                'elevation' => $trail->elevation_gain_m,
                'time' => $trail->estimated_time_hours,
            ];

            $comparison = [
                'distance' => [
                    'old' => $currentData['distance'],
                    'new' => $newGpxData['distance'],
                    'diff' => round($newGpxData['distance'] - $currentData['distance'], 2),
                    'diff_percent' => $currentData['distance'] > 0 
                        ? round((($newGpxData['distance'] - $currentData['distance']) / $currentData['distance']) * 100, 1)
                        : 0,
                ],
                'elevation' => [
                    'old' => $currentData['elevation'],
                    'new' => $newGpxData['elevation'],
                    'diff' => $newGpxData['elevation'] - $currentData['elevation'],
                    'diff_percent' => $currentData['elevation'] > 0
                        ? round((($newGpxData['elevation'] - $currentData['elevation']) / $currentData['elevation']) * 100, 1)
                        : 0,
                ],
                'time' => [
                    'old' => $currentData['time'],
                    'new' => $newGpxData['time'],
                    'diff' => round($newGpxData['time'] - $currentData['time'], 2),
                    'diff_percent' => $currentData['time'] > 0
                        ? round((($newGpxData['time'] - $currentData['time']) / $currentData['time']) * 100, 1)
                        : 0,
                ],
            ];

            return response()->json([
                'success' => true,
                'comparison' => $comparison,
                'has_manual_overrides' => $trail->data_source === 'mixed',
                'current_source' => $trail->data_source,
            ]);
        } catch (Exception $e) {
            \Log::error('GPX Compare Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // /**
    //  * Show media management page
    //  */
    // public function mediaManagement(Trail $trail)
    // {
    //     $trail->load([
    //         'media' => function($query) {
    //             $query->orderBy('sort_order')->orderBy('created_at', 'desc');
    //         },
    //         'features.media'
    //     ]);
        
    //     return view('admin.trails.media-management', compact('trail'));
    // }

    // /**
    //  * Upload media (photos or videos)
    //  */
    // public function uploadMedia(Request $request, Trail $trail)
    // {
    //     try {
    //         $request->validate([
    //             'media_type' => 'required|in:photo,video,video_url',
    //             'file' => 'required_if:media_type,photo,video|file|mimes:jpg,jpeg,png,gif,mp4,mov,avi|max:51200',
    //             'video_url' => 'required_if:media_type,video_url|url',
    //             'caption' => 'nullable|string|max:500',
    //             'coordinates' => 'nullable|array',
    //         ]);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed',
    //             'errors' => $e->errors()
    //         ], 422);
    //     }

    //     try {
    //         $data = [
    //             'trail_id' => $trail->id,
    //             'media_type' => $request->media_type,
    //             'caption' => $request->caption,
    //             'coordinates' => $request->coordinates,
    //             'sort_order' => $trail->media()->max('sort_order') + 1,
    //             'uploaded_by' => auth()->id(),
    //         ];

    //         // Handle file upload
    //         if ($request->hasFile('file')) {
    //             $file = $request->file('file');
    //             $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
                
    //             $folder = $request->media_type === 'photo' ? 'trail-photos' : 'trail-videos';
    //             $path = $file->storeAs($folder, $filename, 'public');

    //             $data['filename'] = $filename;
    //             $data['original_name'] = $file->getClientOriginalName();
    //             $data['storage_path'] = $path;
    //             $data['file_size'] = $file->getSize();
    //             $data['mime_type'] = $file->getMimeType();
    //         }

    //         // Handle external video URL
    //         if ($request->media_type === 'video_url') {
    //             $data['video_url'] = $request->video_url;
    //             $data['video_provider'] = $this->detectVideoProvider($request->video_url);
    //         }

    //         $media = TrailMedia::create($data);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Media uploaded successfully!',
    //             'media' => $media->load('features'),
    //         ]);
            
    //     } catch (\Exception $e) {
    //         \Log::error('Media upload error: ' . $e->getMessage());
            
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Upload failed: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    // /**
    //  * Link media to a feature
    //  */
    // public function linkMediaToFeature(Trail $trail, TrailMedia $media, TrailFeature $feature)
    // {
    //     // Check if already linked
    //     if ($media->features->contains($feature->id)) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Media is already linked to this feature.'
    //         ], 400);
    //     }

    //     // Check limits
    //     if ($media->isPhoto() && $feature->hasReachedPhotoLimit()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'This feature has reached the maximum of 10 photos.'
    //         ], 400);
    //     }

    //     if ($media->isVideo() && $feature->hasReachedVideoLimit()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'This feature has reached the maximum of 1 video.'
    //         ], 400);
    //     }

    //     // Link media to feature
    //     $nextSortOrder = $feature->media()->max('trail_feature_media.sort_order') + 1;
    //     $isPrimary = $feature->media()->count() === 0; // First media is primary

    //     $feature->media()->attach($media->id, [
    //         'is_primary' => $isPrimary,
    //         'sort_order' => $nextSortOrder,
    //     ]);

    //     // Update cached count
    //     $feature->updateMediaCount();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Media linked to feature successfully!',
    //         'feature' => $feature->load('media'),
    //     ]);
    // }

    // /**
    //  * Unlink media from a feature
    //  */
    // public function unlinkMediaFromFeature(Trail $trail, TrailMedia $media, TrailFeature $feature)
    // {
    //     $feature->media()->detach($media->id);
        
    //     // Update cached count
    //     $feature->updateMediaCount();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Media unlinked from feature successfully!',
    //     ]);
    // }

    // /**
    //  * Update media details
    //  */
    // public function updateMedia(Request $request, Trail $trail, TrailMedia $media)
    // {
    //     $request->validate([
    //         'caption' => 'nullable|string|max:500',
    //         'description' => 'nullable|string|max:1000',
    //         'is_featured' => 'boolean',
    //         'sort_order' => 'integer|min:0',
    //     ]);

    //     // If setting as featured, remove featured status from all other trail media
    //     if ($request->has('is_featured') && $request->is_featured) {
    //         $trail->media()->update(['is_featured' => false]);
    //     }

    //     $media->update($request->only(['caption', 'description', 'is_featured', 'sort_order']));

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Media updated successfully!',
    //         'media' => $media,
    //     ]);
    // }

    // /**
    //  * Delete media
    //  */
    // public function deleteMedia(Trail $trail, TrailMedia $media)
    // {
    //     // Detach from all features first
    //     $media->features()->detach();
        
    //     // Delete the media record (this will trigger the model's boot method to delete files)
    //     $media->delete();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Media deleted successfully!',
    //     ]);
    // }

    /**
     * Detect video provider from URL
     */
    private function detectVideoProvider(string $url): string
    {
        if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
            return 'youtube';
        }
        if (str_contains($url, 'vimeo.com')) {
            return 'vimeo';
        }
        return 'other';
    }
}