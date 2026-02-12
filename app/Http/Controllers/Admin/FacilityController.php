<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\FacilityMedia;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    /**
     * Display all facilities
     */
    public function index()
    {
        $facilities = Facility::withCount('media')
            ->orderBy('facility_type')
            ->orderBy('name')
            ->get();

        return view('admin.facilities.index', compact('facilities'));
    }

    /**
     * Show the form for creating a new facility
     */
    public function create()
    {
        return view('admin.facilities.create');
    }

    /**
     * Store a newly created facility
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'facility_type' => 'required|in:parking,toilets,emergency_kit,lodge,viewpoint,info,picnic,water,shelter,camping_site',
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:10',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $facility = Facility::create($validated);

        // Handle media uploads
        $this->handleMediaUploads($request, $facility);

        // Handle video URLs
        $this->handleVideoUrls($request, $facility);

        return redirect()->route('admin.facilities.index')
            ->with('success', 'Facility added successfully.');
    }

    /**
     * Show the form for editing a facility
     */
    public function edit(Facility $facility)
    {
        $facility->load(['media' => function ($query) {
            $query->orderBy('sort_order')->orderBy('created_at');
        }]);

        return view('admin.facilities.edit', compact('facility'));
    }

    /**
     * Update the specified facility
     */
    public function update(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'facility_type' => 'required|in:parking,toilets,emergency_kit,lodge,viewpoint,info,picnic,water,shelter,camping_site',
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:10',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $facility->update($validated);

        // Handle media uploads
        $this->handleMediaUploads($request, $facility);

        // Handle video URLs
        $this->handleVideoUrls($request, $facility);

        return redirect()->route('admin.facilities.index')
            ->with('success', 'Facility updated successfully.');
    }

    /**
     * Remove the specified facility
     */
    public function destroy(Facility $facility)
    {
        // Media files will be deleted via model boot method
        $facility->delete();

        return redirect()->route('admin.facilities.index')
            ->with('success', 'Facility deleted successfully.');
    }

    /**
     * Handle media file uploads
     */
    private function handleMediaUploads(Request $request, Facility $facility): void
    {
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $photo) {
                $path = $photo->store('facilities/'.$facility->id.'/photos', 'public');

                FacilityMedia::create([
                    'facility_id' => $facility->id,
                    'media_type' => 'photo',
                    'file_path' => $path,
                    'caption' => $request->input("photo_captions.{$index}"),
                    'is_primary' => $index === 0 && ! $facility->media()->exists(),
                    'sort_order' => $facility->media()->count() + $index,
                ]);
            }
        }
    }

    /**
     * Handle video URL additions
     */
    private function handleVideoUrls(Request $request, Facility $facility): void
    {
        $videoUrls = $request->input('video_urls', []);

        foreach ($videoUrls as $index => $videoUrl) {
            $videoUrl = trim($videoUrl);

            if (empty($videoUrl)) {
                continue;
            }

            $provider = FacilityMedia::detectVideoProvider($videoUrl);

            if ($provider) {
                FacilityMedia::create([
                    'facility_id' => $facility->id,
                    'media_type' => 'video_url',
                    'url' => $videoUrl,
                    'video_provider' => $provider,
                    'caption' => $request->input("video_captions.{$index}"),
                    'sort_order' => $facility->media()->count() + $index,
                ]);
            }
        }
    }

    /**
     * Delete a media item
     */
    public function deleteMedia(Facility $facility, FacilityMedia $media)
    {
        // Ensure the media belongs to this facility
        if ($media->facility_id !== $facility->id) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Media not found.'], 404);
            }

            return redirect()->back()->with('error', 'Media not found.');
        }

        $media->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Media deleted successfully.']);
        }

        return redirect()->back()->with('success', 'Media deleted successfully.');
    }

    /**
     * Set media as primary
     */
    public function setPrimaryMedia(Facility $facility, FacilityMedia $media)
    {
        // Ensure the media belongs to this facility
        if ($media->facility_id !== $facility->id) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Media not found.'], 404);
            }

            return redirect()->back()->with('error', 'Media not found.');
        }

        // Update this media as primary
        $media->update(['is_primary' => true]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Primary media updated successfully.']);
        }

        return redirect()->back()->with('success', 'Primary media updated successfully.');
    }

    /**
     * Update media sort order
     */
    public function updateMediaOrder(Request $request, Facility $facility)
    {
        $request->validate([
            'media_order' => 'required|array',
            'media_order.*' => 'integer|exists:facility_media,id',
        ]);

        foreach ($request->input('media_order') as $index => $mediaId) {
            FacilityMedia::where('id', $mediaId)
                ->where('facility_id', $facility->id)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Update media caption
     */
    public function updateMediaCaption(Request $request, Facility $facility, FacilityMedia $media)
    {
        // Ensure the media belongs to this facility
        if ($media->facility_id !== $facility->id) {
            return redirect()->back()->with('error', 'Media not found.');
        }

        $request->validate([
            'caption' => 'nullable|string|max:255',
        ]);

        $media->update(['caption' => $request->input('caption')]);

        return redirect()->back()->with('success', 'Caption updated successfully.');
    }
}
