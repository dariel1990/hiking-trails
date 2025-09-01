<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trail;
use App\Models\TrailPhoto;
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
        ]);

        $data = $request->all();
        $data['start_coordinates'] = [$request->start_lat, $request->start_lng];
        
        if ($request->end_lat && $request->end_lng) {
            $data['end_coordinates'] = [$request->end_lat, $request->end_lng];
        }

        $trail = Trail::create($data);

        return redirect()->route('admin.trails.show', $trail)
            ->with('success', 'Trail created successfully!');
    }

    /**
     * Display the specified trail
     */
    public function show(Trail $trail)
    {
        $trail->load(['photos', 'features']);
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
        ]);

        $data = $request->all();
        $data['start_coordinates'] = [$request->start_lat, $request->start_lng];
        
        if ($request->end_lat && $request->end_lng) {
            $data['end_coordinates'] = [$request->end_lat, $request->end_lng];
        } else {
            $data['end_coordinates'] = null;
        }

        $trail->update($data);

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
}