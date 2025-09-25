<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trail;
use App\Models\TrailPhoto;
use App\Models\TrailHighlight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminTrailController extends Controller
{
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
            'directions' => 'nullable|string',
            'parking_info' => 'nullable|string',
            'safety_notes' => 'nullable|string',
            'is_featured' => 'boolean',
            'route_coordinates' => 'nullable|string', // Add this validation
            'waypoints' => 'nullable|string',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB
            'featured_photo_index' => 'nullable|integer|min:0',
        ]);

        $data = $request->all();
        $data['start_coordinates'] = [$request->start_lat, $request->start_lng];
        
        if ($request->end_lat && $request->end_lng) {
            $data['end_coordinates'] = [$request->end_lat, $request->end_lng];
        }

        // Handle route_coordinates properly
        if ($request->route_coordinates) {
            $data['route_coordinates'] = json_decode($request->route_coordinates, true);
        } else {
            $data['route_coordinates'] = null;
        }

        // Remove the string versions to avoid confusion
        unset($data['start_lat'], $data['start_lng'], $data['end_lat'], $data['end_lng']);

        $trail = Trail::create($data);

        if ($request->has('highlights_data')) {
            $highlightsData = json_decode($request->highlights_data, true);
            
            if (is_array($highlightsData)) {
                foreach ($highlightsData as $highlightData) {
                    $trail->highlights()->create([
                        'name' => $highlightData['name'],
                        'description' => $highlightData['description'] ?? null,
                        'type' => $highlightData['type'],
                        'coordinates' => $highlightData['coordinates'],
                        'icon' => $highlightData['icon'] ?? null,
                        'color' => $highlightData['color'] ?? '#10B981',
                        'sort_order' => $trail->highlights()->count(),
                    ]);
                }
            }
        }

        if ($request->hasFile('photos')) {
            $featuredIndex = $request->input('featured_photo_index', 0);
            
            foreach ($request->file('photos') as $index => $photo) {
                $filename = Str::random(40) . '.' . $photo->getClientOriginalExtension();
                $path = $photo->storeAs('trail-photos', $filename, 'public');

                TrailPhoto::create([
                    'trail_id' => $trail->id,
                    'filename' => $filename,
                    'original_name' => $photo->getClientOriginalName(),
                    'path' => $path,
                    'is_featured' => ($index == $featuredIndex),
                    'sort_order' => $index,
                ]);
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
        $trail->load(['photos', 'features', 'highlights']);
        return view('admin.trails.show', compact('trail'));
    }

    /**
     * Show the form for editing the specified trail
     */
    public function edit(Trail $trail)
    {
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
            'directions' => 'nullable|string',
            'parking_info' => 'nullable|string',
            'safety_notes' => 'nullable|string',
            'is_featured' => 'boolean',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240',
            'deleted_photos' => 'nullable|string',
            'featured_photo_id' => 'nullable|integer',
        ]);

        $data = $request->all();
        $data['start_coordinates'] = [$request->start_lat, $request->start_lng];
        
        if ($request->end_lat && $request->end_lng) {
            $data['end_coordinates'] = [$request->end_lat, $request->end_lng];
        } else {
            $data['end_coordinates'] = null;
        }

        $trail->update($data);

        if ($request->has('highlights_data')) {
            $newHighlights = json_decode($request->highlights_data, true);
            
            if (is_array($newHighlights)) {
                foreach ($newHighlights as $highlightData) {
                    $trail->highlights()->create([
                        'name' => $highlightData['name'],
                        'description' => $highlightData['description'] ?? null,
                        'type' => $highlightData['type'],
                        'coordinates' => $highlightData['coordinates'],
                        'icon' => $highlightData['icon'] ?? null,
                        'color' => $highlightData['color'] ?? '#10B981',
                        'sort_order' => $trail->highlights()->count(),
                    ]);
                }
            }
        }

        // Handle deleted highlights
        if ($request->has('deleted_highlights')) {
            $deletedIds = json_decode($request->deleted_highlights, true);
            
            if (is_array($deletedIds)) {
                $trail->highlights()->whereIn('id', $deletedIds)->delete();
            }
        }

        if ($request->has('deleted_photos')) {
            $deletedIds = json_decode($request->deleted_photos, true);
            if (is_array($deletedIds)) {
                foreach ($deletedIds as $photoId) {
                    $photo = TrailPhoto::find($photoId);
                    if ($photo && $photo->trail_id === $trail->id) {
                        Storage::delete($photo->path);
                        $photo->delete();
                    }
                }
            }
        }

        if ($request->hasFile('photos')) {
            $currentPhotoCount = $trail->photos()->count();
            $maxSort = $trail->photos()->max('sort_order') ?? -1;
            
            foreach ($request->file('photos') as $index => $photo) {
                if ($currentPhotoCount + $index + 1 > 5) break; // Enforce max 5 photos
                
                $filename = Str::random(40) . '.' . $photo->getClientOriginalExtension();
                $path = $photo->storeAs('trail-photos', $filename, 'public');

                TrailPhoto::create([
                    'trail_id' => $trail->id,
                    'filename' => $filename,
                    'original_name' => $photo->getClientOriginalName(),
                    'path' => $path,
                    'sort_order' => $maxSort + $index + 1,
                ]);
            }
        }

        // Update featured photo
        if ($request->has('featured_photo_id') && $request->featured_photo_id) {
            // Clear all featured flags
            $trail->photos()->update(['is_featured' => false]);
            
            // Set new featured photo
            $trail->photos()->where('id', $request->featured_photo_id)->update(['is_featured' => true]);
        }

        return redirect()->route('admin.trails.show', $trail)
            ->with('success', 'Trail updated successfully!');
    }

    /**
     * Remove the specified trail
     */
    public function destroy(Trail $trail)
    {
        // Delete associated photos from storage
        foreach ($trail->photos as $photo) {
            Storage::delete($photo->path);
        }

        $trail->delete();

        return redirect()->route('admin.trails.index')
            ->with('success', 'Trail deleted successfully!');
    }

    /**
     * Upload photos for a trail
     */
    public function uploadPhotos(Request $request, Trail $trail)
    {
        $request->validate([
            'photos' => 'required|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'captions' => 'nullable|array',
            'captions.*' => 'nullable|string|max:255',
        ]);

        $uploadedPhotos = [];

        foreach ($request->file('photos') as $index => $photo) {
            $filename = Str::random(40) . '.' . $photo->getClientOriginalExtension();
            $path = $photo->storeAs('trail-photos', $filename, 'public');

            $trailPhoto = TrailPhoto::create([
                'trail_id' => $trail->id,
                'filename' => $filename,
                'original_name' => $photo->getClientOriginalName(),
                'path' => $path,
                'caption' => $request->captions[$index] ?? null,
                'sort_order' => TrailPhoto::where('trail_id', $trail->id)->max('sort_order') + 1,
            ]);

            $uploadedPhotos[] = $trailPhoto;
        }

        return redirect()->route('admin.trails.show', $trail)
            ->with('success', count($uploadedPhotos) . ' photo(s) uploaded successfully!');
    }

    /**
     * Delete a photo
     */
    public function deletePhoto(TrailPhoto $photo)
    {
        Storage::delete($photo->path);
        $photo->delete();

        return back()->with('success', 'Photo deleted successfully!');
    }


    /**
     * Show highlights management page
     */
    public function highlights(Trail $trail)
    {
        $trail->load('highlights');
        
        $highlightTypes = [
            'viewpoint' => ['name' => 'Viewpoint', 'icon' => '👁️', 'color' => '#8B5CF6'],
            'waterfall' => ['name' => 'Waterfall', 'icon' => '💧', 'color' => '#3B82F6'],
            'summit' => ['name' => 'Summit', 'icon' => '⛰️', 'color' => '#EF4444'],
            'lake' => ['name' => 'Lake', 'icon' => '🏞️', 'color' => '#06B6D4'],
            'bridge' => ['name' => 'Bridge', 'icon' => '🌉', 'color' => '#F59E0B'],
            'wildlife' => ['name' => 'Wildlife Spot', 'icon' => '🦌', 'color' => '#10B981'],
            'camping' => ['name' => 'Camping Area', 'icon' => '⛺', 'color' => '#F97316'],
            'parking' => ['name' => 'Parking', 'icon' => '🅿️', 'color' => '#6B7280'],
            'picnic' => ['name' => 'Picnic Area', 'icon' => '🍽️', 'color' => '#84CC16'],
            'restroom' => ['name' => 'Restroom', 'icon' => '🚻', 'color' => '#14B8A6'],
            'danger' => ['name' => 'Hazard/Warning', 'icon' => '⚠️', 'color' => '#DC2626'],
            'photo_spot' => ['name' => 'Photo Spot', 'icon' => '📷', 'color' => '#EC4899'],
        ];
        
        return view('admin.trails.highlights', compact('trail', 'highlightTypes'));
    }

    /**
     * Store a new highlight
     */
    public function storeHighlight(Request $request, Trail $trail)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
        ]);

        $trail->highlights()->create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'coordinates' => [$request->lat, $request->lng],
            'icon' => $request->icon,
            'color' => $request->color,
            'sort_order' => $trail->highlights()->max('sort_order') + 1,
        ]);

        return redirect()->route('admin.trails.highlights', $trail)
            ->with('success', 'Highlight added successfully!');
    }

    /**
     * Update a highlight
     */
    public function updateHighlight(Request $request, Trail $trail, TrailHighlight $highlight)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
        ]);

        $highlight->update([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'coordinates' => [$request->lat, $request->lng],
            'icon' => $request->icon,
            'color' => $request->color,
        ]);

        return redirect()->route('admin.trails.highlights', $trail)
            ->with('success', 'Highlight updated successfully!');
    }

    /**
     * Delete a highlight
     */
    public function deleteHighlight(Trail $trail, TrailHighlight $highlight)
    {
        $highlight->delete();

        return redirect()->route('admin.trails.highlights', $trail)
            ->with('success', 'Highlight deleted successfully!');
    }
}