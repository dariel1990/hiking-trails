<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\FacilityMedia;
use App\Models\TrailNetwork;
use App\Services\ImageThumbnailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class FacilityController extends Controller
{
    /**
     * Display all facilities
     */
    public function index(Request $request)
    {
        $search = $request->string('search')->toString();
        $type = $request->string('type', 'all')->toString();

        $baseQuery = Facility::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            });

        // Counts reflect the full search-filtered set, not just the current page.
        $totalCount = (clone $baseQuery)->count();
        $activeCount = (clone $baseQuery)->where('is_active', true)->count();
        $inactiveCount = (clone $baseQuery)->where('is_active', false)->count();
        $withMediaCount = (clone $baseQuery)->has('media')->count();

        $typeCounts = (clone $baseQuery)
            ->select('facility_type', DB::raw('count(*) as aggregate'))
            ->groupBy('facility_type')
            ->pluck('aggregate', 'facility_type');

        $facilities = (clone $baseQuery)
            ->withCount('media')
            ->when($type !== 'all', fn ($q) => $q->where('facility_type', $type))
            ->orderBy('facility_type')
            ->orderBy('name')
            ->paginate(setting('admin_per_page'))
            ->withQueryString();

        return view('admin.facilities.index', compact(
            'facilities', 'totalCount', 'activeCount', 'inactiveCount', 'withMediaCount', 'typeCounts', 'search', 'type'
        ));
    }

    /**
     * Show the form for creating a new facility
     */
    public function create()
    {
        $trailNetworks = TrailNetwork::orderBy('network_name')->get(['id', 'network_name']);

        return view('admin.facilities.create', compact('trailNetworks'));
    }

    /**
     * Store a newly created facility
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'facility_type' => ['required', 'in:'.implode(',', array_keys(Facility::getFacilityTypes()))],
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:10',
            'icon_image' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'trail_network_id' => 'nullable|exists:trail_networks,id',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:51200',
            'video_urls' => 'nullable|array',
            'video_urls.*' => 'nullable|url|max:500',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['trail_network_id'] = $request->input('trail_network_id') ?: null;

        if (! empty($validated['icon_image'])) {
            if (! str_starts_with($validated['icon_image'], 'facility-icons/') || str_contains($validated['icon_image'], '..')) {
                abort(422, 'Invalid icon image.');
            }
            $validated['icon'] = null;
        }

        $facility = Facility::create($validated);

        $this->handleMediaUploads($request, $facility);
        $unsupported = $this->handleVideoUrls($request, $facility);

        $message = 'Facility added successfully.';

        if (! empty($unsupported)) {
            $message .= ' Skipped unsupported video URLs (only YouTube and Vimeo are supported): '.implode(', ', $unsupported);
        }

        return redirect()->route('admin.facilities.index')->with('success', $message);
    }

    /**
     * Show the form for editing a facility
     */
    public function edit(Facility $facility)
    {
        $facility->load(['media' => function ($query) {
            $query->orderBy('sort_order')->orderBy('created_at');
        }]);

        $trailNetworks = TrailNetwork::orderBy('network_name')->get(['id', 'network_name']);

        return view('admin.facilities.edit', compact('facility', 'trailNetworks'));
    }

    /**
     * Update the specified facility
     */
    public function update(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'facility_type' => ['required', 'in:'.implode(',', array_keys(Facility::getFacilityTypes()))],
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:10',
            'icon_image' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'trail_network_id' => 'nullable|exists:trail_networks,id',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:51200',
            'video_urls' => 'nullable|array',
            'video_urls.*' => 'nullable|url|max:500',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['trail_network_id'] = $request->input('trail_network_id') ?: null;

        if (! empty($validated['icon_image'])) {
            if (! str_starts_with($validated['icon_image'], 'facility-icons/') || str_contains($validated['icon_image'], '..')) {
                abort(422, 'Invalid icon image.');
            }
            $validated['icon'] = null;
        }

        $facility->update($validated);

        $this->handleMediaUploads($request, $facility);
        $unsupported = $this->handleVideoUrls($request, $facility);

        $message = 'Facility updated successfully.';

        if (! empty($unsupported)) {
            $message .= ' Skipped unsupported video URLs (only YouTube and Vimeo are supported): '.implode(', ', $unsupported);
        }

        return redirect()->route('admin.facilities.index')->with('success', $message);
    }

    /**
     * List previously uploaded facility icons for reuse.
     */
    public function listIcons(): JsonResponse
    {
        $files = Storage::disk('public')->files('facility-icons');

        $icons = collect($files)
            ->filter(fn ($f) => preg_match('/\.(png|jpg|jpeg|webp|gif)$/i', $f))
            ->map(fn ($f) => [
                'path' => $f,
                'url' => asset('storage/'.$f),
            ])
            ->values();

        return response()->json($icons);
    }

    /**
     * Upload a new facility icon and return its path + URL.
     */
    public function uploadIcon(Request $request): JsonResponse
    {
        $request->validate([
            'icon' => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
        ]);

        $path = $this->compressAndStoreIcon($request->file('icon'), 'facility-icons');

        return response()->json([
            'path' => $path,
            'url' => asset('storage/'.$path),
        ]);
    }

    /**
     * Delete a facility icon.
     */
    public function deleteIcon(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string',
            'force' => 'nullable|boolean',
        ]);

        $path = $request->string('path')->toString();

        if (! str_starts_with($path, 'facility-icons/') || str_contains($path, '..')) {
            abort(422, 'Invalid icon path.');
        }

        $inUse = Facility::where('icon_image', $path)->count();

        if ($inUse > 0 && ! $request->boolean('force')) {
            return response()->json(['deleted' => false, 'in_use' => $inUse]);
        }

        if ($inUse > 0) {
            // Clear the reference so these records fall back to their facility type's
            // stock icon instead of pointing at a now-deleted image.
            Facility::where('icon_image', $path)->update(['icon_image' => null]);
        }

        Storage::disk('public')->delete($path);

        return response()->json(['deleted' => true]);
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
                $compressed = $this->compressAndStorePhoto($photo, 'facilities/'.$facility->id.'/photos');

                FacilityMedia::create([
                    'facility_id' => $facility->id,
                    'media_type' => 'photo',
                    'file_path' => $compressed['path'],
                    'thumbnail_path' => $compressed['thumbnail_path'],
                    'caption' => $request->input("photo_captions.{$index}"),
                    'is_primary' => $index === 0 && ! $facility->media()->exists(),
                    'sort_order' => $facility->media()->count() + $index,
                ]);
            }
        }
    }

    /**
     * @return array{filename: string, path: string, thumbnail_path: string, file_size: int}
     */
    private function compressAndStorePhoto(UploadedFile $photo, string $directory): array
    {
        return app(ImageThumbnailService::class)->process($photo, $directory);
    }

    private function compressAndStoreIcon(UploadedFile $icon, string $directory): string
    {
        $manager = new ImageManager(new Driver);
        $image = $manager->read($icon->getRealPath());
        $image->scaleDown(width: 256, height: 256);

        $path = $directory.'/'.Str::random(40).'.webp';
        Storage::disk('public')->put($path, (string) $image->toWebp(90));

        return $path;
    }

    /**
     * Handle video URL additions
     *
     * @return array<int, string> List of URLs that were rejected because the provider is unsupported
     */
    private function handleVideoUrls(Request $request, Facility $facility): array
    {
        $videoUrls = $request->input('video_urls', []);
        $unsupported = [];

        foreach ($videoUrls as $index => $videoUrl) {
            $videoUrl = trim((string) $videoUrl);

            if (empty($videoUrl)) {
                continue;
            }

            $provider = FacilityMedia::detectVideoProvider($videoUrl);

            if (! $provider) {
                $unsupported[] = $videoUrl;

                continue;
            }

            FacilityMedia::create([
                'facility_id' => $facility->id,
                'media_type' => 'video_url',
                'url' => $videoUrl,
                'video_provider' => $provider,
                'caption' => $request->input("video_captions.{$index}"),
                'sort_order' => $facility->media()->count() + $index,
            ]);
        }

        return $unsupported;
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
