<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBusinessRequest;
use App\Http\Requests\Admin\UpdateBusinessRequest;
use App\Models\Business;
use App\Models\BusinessMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessController extends Controller
{
    public function index(): View
    {
        $businesses = Business::withCount('media')
            ->orderBy('business_type')
            ->orderBy('name')
            ->get();

        return view('admin.businesses.index', compact('businesses'));
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

    public function updateMediaOrder(Request $request, Business $business): \Illuminate\Http\JsonResponse
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
                $path = $photo->store('businesses/'.$business->id.'/photos', 'public');

                BusinessMedia::create([
                    'business_id' => $business->id,
                    'media_type' => 'photo',
                    'file_path' => $path,
                    'caption' => $request->input("photo_captions.{$index}"),
                    'is_primary' => $index === 0 && ! $business->media()->exists(),
                    'sort_order' => $business->media()->count() + $index,
                ]);
            }
        }
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
