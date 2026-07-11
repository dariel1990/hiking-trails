<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trail;
use App\Models\TrailFeature;
use App\Models\TrailMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class TrailHighlightController extends Controller
{
    /**
     * Display a listing of all trail highlights across every trail.
     */
    public function index(Request $request): View
    {
        $highlights = TrailFeature::query()
            ->with('trail:id,name')
            ->withCount('media')
            ->when($request->input('search'), function ($query, string $search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhereHas('trail', function ($trailQuery) use ($search): void {
                            $trailQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->input('feature_type'), function ($query, string $type): void {
                $query->where('feature_type', $type);
            })
            ->when($request->input('trail'), function ($query, string $trailId): void {
                $query->where('trail_id', $trailId);
            })
            ->orderBy('trail_id')
            ->orderBy('name')
            ->paginate(setting('admin_per_page'))
            ->withQueryString();

        $trails = Trail::orderBy('name')->get(['id', 'name']);
        $featureTypes = TrailFeature::getFeatureTypes();

        return view('admin.highlights.index', compact('highlights', 'trails', 'featureTypes'));
    }

    /**
     * Show the form for editing the specified highlight.
     */
    public function edit(TrailFeature $highlight): View
    {
        $highlight->load(['trail:id,name', 'media' => function ($query): void {
            $query->orderBy('trail_feature_media.sort_order')->orderBy('trail_feature_media.created_at');
        }]);

        $featureTypes = TrailFeature::getFeatureTypes();

        return view('admin.highlights.edit', compact('highlight', 'featureTypes'));
    }

    /**
     * Update the specified highlight.
     */
    public function update(Request $request, TrailFeature $highlight): RedirectResponse
    {
        $validated = $request->validate([
            'feature_type' => ['required', 'in:'.implode(',', array_keys(TrailFeature::getFeatureTypes()))],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:10'],
            'icon_image' => ['nullable', 'string', 'max:500'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:51200'],
        ], [
            'color.regex' => 'The color must be a valid hex value (e.g. #3B82F6).',
        ]);

        $highlight->update([
            'feature_type' => $validated['feature_type'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?: null,
            'icon_image' => $validated['icon_image'] ?: null,
            'color' => $validated['color'] ?: null,
            'coordinates' => [(float) $validated['latitude'], (float) $validated['longitude']],
        ]);

        if ($request->hasFile('photos')) {
            $hasPrimary = $highlight->primaryMedia() !== null;

            foreach ($request->file('photos') as $photo) {
                if ($highlight->hasReachedPhotoLimit()) {
                    break;
                }

                $compressed = $this->compressAndStorePhoto($photo, 'trail-photos');

                $media = TrailMedia::create([
                    'trail_id' => $highlight->trail_id,
                    'media_type' => 'photo',
                    'filename' => $compressed['filename'],
                    'original_name' => $photo->getClientOriginalName(),
                    'storage_path' => $compressed['path'],
                    'file_size' => $compressed['file_size'],
                    'mime_type' => 'image/webp',
                    'uploaded_by' => auth()->id(),
                ]);

                $highlight->media()->attach($media->id, [
                    'is_primary' => ! $hasPrimary,
                    'sort_order' => $highlight->media()->count(),
                ]);

                $hasPrimary = true;
            }

            $highlight->updateMediaCount();
        }

        $this->ensureHighlightHasFeaturedPhoto($highlight);

        return redirect()->route('admin.highlights.index')
            ->with('success', 'Highlight updated successfully.');
    }

    /**
     * Guarantee the highlight has an explicit featured (primary) photo — e.g. after the
     * previously featured photo was deleted. Falls back to the first remaining photo so
     * the highlight's hero image on /map never silently disappears.
     */
    private function ensureHighlightHasFeaturedPhoto(TrailFeature $highlight): void
    {
        $hasPrimary = $highlight->media()->wherePivot('is_primary', true)->exists();

        if ($hasPrimary) {
            return;
        }

        $firstPhoto = $highlight->photos()->first();

        if ($firstPhoto) {
            $highlight->media()->updateExistingPivot($firstPhoto->id, ['is_primary' => true]);
        }
    }

    /**
     * Compress an uploaded photo to WebP and store it on the public disk.
     *
     * @return array{filename: string, path: string, file_size: int}
     */
    private function compressAndStorePhoto(UploadedFile $photo, string $directory): array
    {
        $manager = new ImageManager(new Driver);
        $image = $manager->read($photo->getRealPath());
        $image->scaleDown(width: 1920, height: 1080);

        $filename = Str::random(40).'.webp';
        $path = $directory.'/'.$filename;
        $webpData = (string) $image->toWebp(85);

        Storage::disk('public')->put($path, $webpData);

        return ['filename' => $filename, 'path' => $path, 'file_size' => strlen($webpData)];
    }

    /**
     * Remove the specified highlight.
     */
    public function destroy(TrailFeature $highlight): RedirectResponse
    {
        $highlight->delete();

        return redirect()->route('admin.highlights.index')
            ->with('success', 'Highlight deleted successfully.');
    }

    /**
     * Mark the given media item as the featured (primary) photo for this highlight.
     */
    public function setFeaturedMedia(TrailFeature $highlight, TrailMedia $media): JsonResponse
    {
        if (! $highlight->media()->where('trail_media.id', $media->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Media not found.'], 404);
        }

        $highlight->media()->updateExistingPivot(
            $highlight->media()->pluck('trail_media.id'),
            ['is_primary' => false]
        );
        $highlight->media()->updateExistingPivot($media->id, ['is_primary' => true]);

        return response()->json(['success' => true, 'message' => 'Featured photo updated successfully.']);
    }

    /**
     * Remove a media item from this highlight — deleting it entirely if it isn't
     * shared with any other highlight, otherwise just unlinking it from this one.
     */
    public function deleteMedia(TrailFeature $highlight, TrailMedia $media): JsonResponse
    {
        if (! $highlight->media()->where('trail_media.id', $media->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Media not found.'], 404);
        }

        if ($media->features()->count() <= 1) {
            $media->delete();
        } else {
            $highlight->media()->detach($media->id);
        }

        $highlight->updateMediaCount();
        $this->ensureHighlightHasFeaturedPhoto($highlight);

        return response()->json(['success' => true, 'message' => 'Media deleted successfully.']);
    }
}
