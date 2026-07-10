<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBusinessRequest;
use App\Http\Requests\Admin\UpdateBusinessRequest;
use App\Models\Business;
use App\Models\BusinessMedia;
use App\Services\ImageThumbnailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class BusinessController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $businesses = Business::withCount('media')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('tagline', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->orderBy('business_type')
            ->orderBy('name')
            ->get();

        return view('admin.businesses.index', compact('businesses', 'search'));
    }

    public function create(): View
    {
        return view('admin.businesses.create');
    }

    public function store(StoreBusinessRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_seasonal'] = $request->boolean('is_seasonal');
        $validated = $this->sanitizeIconImage($validated);

        $business = Business::create($validated);

        $this->handleMediaUploads($request, $business);
        $unsupported = $this->handleVideoUrls($request, $business);

        $message = 'Business added successfully.';

        if (! empty($unsupported)) {
            $message .= ' Skipped unsupported video URLs (only YouTube and Vimeo are supported): '.implode(', ', $unsupported);
        }

        return redirect()->route('admin.businesses.index')->with('success', $message);
    }

    public function edit(Business $business): View
    {
        $business->load(['media' => function ($query) {
            $query->orderBy('sort_order')->orderBy('created_at');
        }]);

        return view('admin.businesses.edit', compact('business'));
    }

    public function update(UpdateBusinessRequest $request, Business $business): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_seasonal'] = $request->boolean('is_seasonal');
        $validated = $this->sanitizeIconImage($validated);

        $business->update($validated);

        $this->handleMediaUploads($request, $business);
        $unsupported = $this->handleVideoUrls($request, $business);

        $message = 'Business updated successfully.';

        if (! empty($unsupported)) {
            $message .= ' Skipped unsupported video URLs (only YouTube and Vimeo are supported): '.implode(', ', $unsupported);
        }

        return redirect()->route('admin.businesses.index')->with('success', $message);
    }

    public function destroy(Business $business): RedirectResponse
    {
        $business->delete();

        return redirect()->route('admin.businesses.index')
            ->with('success', 'Business deleted successfully.');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string', 'in:delete,activate,deactivate'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:businesses,id'],
        ]);

        $ids = $validated['ids'];
        $count = count($ids);
        $message = '';

        switch ($validated['action']) {
            case 'delete':
                Business::query()->whereIn('id', $ids)->get()->each->delete();
                $message = "{$count} business(es) deleted successfully.";
                break;
            case 'activate':
                Business::query()->whereIn('id', $ids)->update(['is_active' => true]);
                $message = "{$count} business(es) marked active.";
                break;
            case 'deactivate':
                Business::query()->whereIn('id', $ids)->update(['is_active' => false]);
                $message = "{$count} business(es) marked inactive.";
                break;
        }

        return redirect()->route('admin.businesses.index')->with('success', $message);
    }

    /**
     * List previously uploaded business icons for reuse.
     */
    public function listIcons(): JsonResponse
    {
        $files = Storage::disk('public')->files('business-icons');

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
     * Upload a new business icon and return its path + URL.
     */
    public function uploadIcon(Request $request): JsonResponse
    {
        $request->validate([
            'icon' => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
        ]);

        $path = $this->compressAndStoreIcon($request->file('icon'), 'business-icons');

        return response()->json([
            'path' => $path,
            'url' => asset('storage/'.$path),
        ]);
    }

    /**
     * Delete a business icon.
     */
    public function deleteIcon(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string',
            'force' => 'nullable|boolean',
        ]);

        $path = $request->string('path')->toString();

        if (! str_starts_with($path, 'business-icons/') || str_contains($path, '..')) {
            abort(422, 'Invalid icon path.');
        }

        $inUse = Business::where('icon_image', $path)->count();

        if ($inUse > 0 && ! $request->boolean('force')) {
            return response()->json(['deleted' => false, 'in_use' => $inUse]);
        }

        if ($inUse > 0) {
            // Clear the reference so these records fall back to their business type's
            // stock icon instead of pointing at a now-deleted image.
            Business::where('icon_image', $path)->update(['icon_image' => null]);
        }

        Storage::disk('public')->delete($path);

        return response()->json(['deleted' => true]);
    }

    /**
     * Ensure icon_image, when set, points at an uploaded business icon and clears
     * the emoji icon override so the image takes precedence.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function sanitizeIconImage(array $validated): array
    {
        if (! empty($validated['icon_image'])) {
            if (! str_starts_with($validated['icon_image'], 'business-icons/') || str_contains($validated['icon_image'], '..')) {
                abort(422, 'Invalid icon image.');
            }
            $validated['icon'] = null;
        }

        return $validated;
    }

    public function toggleActive(Business $business): JsonResponse
    {
        $business->update(['is_active' => ! $business->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $business->is_active,
        ]);
    }

    public function deleteMedia(Business $business, BusinessMedia $media): mixed
    {
        if ($media->business_id !== $business->id) {
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

    public function setPrimaryMedia(Business $business, BusinessMedia $media): mixed
    {
        if ($media->business_id !== $business->id) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Media not found.'], 404);
            }

            return redirect()->back()->with('error', 'Media not found.');
        }

        $media->update(['is_primary' => true]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Primary media updated successfully.']);
        }

        return redirect()->back()->with('success', 'Primary media updated successfully.');
    }

    public function updateMediaOrder(Request $request, Business $business): JsonResponse
    {
        $request->validate([
            'media_order' => 'required|array',
            'media_order.*' => 'integer|exists:business_media,id',
        ]);

        foreach ($request->input('media_order') as $index => $mediaId) {
            BusinessMedia::where('id', $mediaId)
                ->where('business_id', $business->id)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    public function updateMediaCaption(Request $request, Business $business, BusinessMedia $media): RedirectResponse
    {
        if ($media->business_id !== $business->id) {
            return redirect()->back()->with('error', 'Media not found.');
        }

        $request->validate(['caption' => 'nullable|string|max:255']);
        $media->update(['caption' => $request->input('caption')]);

        return redirect()->back()->with('success', 'Caption updated successfully.');
    }

    private function handleMediaUploads(Request $request, Business $business): void
    {
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $photo) {
                $compressed = $this->compressAndStorePhoto($photo, 'businesses/'.$business->id.'/photos');

                BusinessMedia::create([
                    'business_id' => $business->id,
                    'media_type' => 'photo',
                    'file_path' => $compressed['path'],
                    'thumbnail_path' => $compressed['thumbnail_path'],
                    'caption' => $request->input("photo_captions.{$index}"),
                    'is_primary' => $index === 0 && ! $business->media()->exists(),
                    'sort_order' => $business->media()->count() + $index,
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
     * @return array<int, string> URLs rejected because the provider is unsupported
     */
    private function handleVideoUrls(Request $request, Business $business): array
    {
        $videoUrls = $request->input('video_urls', []);
        $unsupported = [];

        foreach ($videoUrls as $index => $videoUrl) {
            $videoUrl = trim((string) $videoUrl);

            if (empty($videoUrl)) {
                continue;
            }

            $provider = BusinessMedia::detectVideoProvider($videoUrl);

            if (! $provider) {
                $unsupported[] = $videoUrl;

                continue;
            }

            BusinessMedia::create([
                'business_id' => $business->id,
                'media_type' => 'video_url',
                'url' => $videoUrl,
                'video_provider' => $provider,
                'caption' => $request->input("video_captions.{$index}"),
                'sort_order' => $business->media()->count() + $index,
            ]);
        }

        return $unsupported;
    }
}
