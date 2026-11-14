<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTourRequest;
use App\Http\Requests\Admin\UpdateTourRequest;
use App\Models\Facility;
use App\Models\Tour;
use App\Models\TourStop;
use App\Models\Trail;
use App\Models\TrailFeature;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * Admin CRUD for tours. Events have their own admin (AdminEventController).
 */
class AdminTourController extends Controller
{
    /**
     * Labels and route names for the admin views.
     *
     * @return array{routePrefix: string, singular: string, plural: string, publicShowRoute: string, description: string, types: array<string, string>}
     */
    private function section(): array
    {
        return [
            'routePrefix' => 'admin.tours',
            'singular' => 'Tour',
            'plural' => 'Tours',
            'publicShowRoute' => 'tours.show',
            'description' => 'Manage themed tour packages that appear on the site',
            'types' => Tour::getTourTypes(),
        ];
    }

    /**
     * Regular tours (leftover rows of the old event type are not managed here).
     *
     * @return Builder<Tour>
     */
    private function sectionQuery(): Builder
    {
        return Tour::query()->tours();
    }

    public function index(): View
    {
        $tours = $this->sectionQuery()
            ->withCount('stops')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return view('admin.tours.index', ['tours' => $tours, 'section' => $this->section()]);
    }

    public function create(): View
    {
        return view('admin.tours.create', [...$this->formOptions(), 'section' => $this->section()]);
    }

    public function store(StoreTourRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['sort_order'] = $request->input('sort_order', 0);

        // Decode driving_route_coordinates from JSON string
        $decodedRoute = ! empty($validated['driving_route_coordinates'])
            ? json_decode($validated['driving_route_coordinates'], true)
            : null;
        $validated['driving_route_coordinates'] = is_array($decodedRoute) && $decodedRoute ? $decodedRoute : null;

        unset($validated['cover_image'], $validated['stops']);

        $tour = Tour::create($validated);

        if ($request->hasFile('cover_image')) {
            $path = $this->compressAndStoreCoverImage($request->file('cover_image'), $tour);
            $tour->update(['cover_image' => $path]);
        }

        $this->syncStops($request, $tour);

        return redirect()->route($this->section()['routePrefix'].'.index')
            ->with('success', $this->section()['singular'].' created successfully.');
    }

    public function edit(Tour $tour): View
    {
        abort_if($tour->isLegacyEvent(), 404);

        $tour->load('stops.trail', 'stops.feature', 'stops.facility');

        return view('admin.tours.edit', [...$this->formOptions(), 'tour' => $tour, 'section' => $this->section()]);
    }

    public function update(UpdateTourRequest $request, Tour $tour): RedirectResponse
    {
        abort_if($tour->isLegacyEvent(), 404);

        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['sort_order'] = $request->input('sort_order', 0);

        // Decode driving_route_coordinates from JSON string. An explicit "[]"
        // (route cleared in the editor) removes the route; a blank or invalid
        // value keeps the existing one.
        $decodedRoute = ! empty($validated['driving_route_coordinates'])
            ? json_decode($validated['driving_route_coordinates'], true)
            : null;

        if (is_array($decodedRoute)) {
            $validated['driving_route_coordinates'] = $decodedRoute ?: null;
        } else {
            $validated['driving_route_coordinates'] = $tour->driving_route_coordinates;
        }

        unset($validated['cover_image'], $validated['stops']);

        if ($request->hasFile('cover_image')) {
            // Delete old cover image
            if ($tour->cover_image) {
                Storage::disk('public')->delete($tour->cover_image);
            }
            $validated['cover_image'] = $this->compressAndStoreCoverImage($request->file('cover_image'), $tour);
        }

        $tour->update($validated);
        $this->syncStops($request, $tour);

        return redirect()->route($this->section()['routePrefix'].'.index')
            ->with('success', $this->section()['singular'].' updated successfully.');
    }

    /**
     * Remove this tour's cover image without requiring a replacement upload.
     */
    public function deleteCoverImage(Tour $tour): JsonResponse
    {
        if ($tour->cover_image) {
            Storage::disk('public')->delete($tour->cover_image);
            $tour->update(['cover_image' => null]);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Tour $tour): RedirectResponse
    {
        abort_if($tour->isLegacyEvent(), 404);

        if ($tour->cover_image) {
            Storage::disk('public')->delete($tour->cover_image);
        }
        $this->deleteStopImages($tour);

        $tour->delete();

        return redirect()->route($this->section()['routePrefix'].'.index')
            ->with('success', $this->section()['singular'].' deleted successfully.');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string', 'in:delete,activate,deactivate'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:tours,id'],
        ]);

        // Only act on items of this section, whatever ids were posted.
        $query = fn () => $this->sectionQuery()->whereIn('id', $validated['ids']);
        $count = $query()->count();
        $noun = strtolower($this->section()['singular']).'(s)';

        switch ($validated['action']) {
            case 'delete':
                $query()->get()->each(function (Tour $tour) {
                    if ($tour->cover_image) {
                        Storage::disk('public')->delete($tour->cover_image);
                    }
                    $this->deleteStopImages($tour);
                    $tour->delete();
                });
                $message = "{$count} {$noun} deleted successfully.";
                break;
            case 'activate':
                $query()->update(['is_active' => true]);
                $message = "{$count} {$noun} marked active.";
                break;
            case 'deactivate':
                $query()->update(['is_active' => false]);
                $message = "{$count} {$noun} marked inactive.";
                break;
            default:
                $message = '';
        }

        return redirect()->route($this->section()['routePrefix'].'.index')->with('success', $message);
    }

    public function toggleActive(Tour $tour): JsonResponse
    {
        $tour->update(['is_active' => ! $tour->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $tour->is_active,
        ]);
    }

    /**
     * Trails, trail features and facilities offered as stops in the form.
     *
     * @return array{availableTrails: Collection<int, Trail>, availableFeatures: Collection<int, TrailFeature>, availableFacilities: Collection<int, Facility>}
     */
    private function formOptions(): array
    {
        return [
            'availableTrails' => Trail::where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'start_coordinates', 'location_type']),
            'availableFeatures' => TrailFeature::with('trail:id,name')
                ->whereNotNull('coordinates')
                ->orderBy('name')
                ->get(['id', 'trail_id', 'feature_type', 'name', 'coordinates']),
            'availableFacilities' => Facility::active()
                ->orderBy('name')
                ->get(['id', 'name', 'facility_type', 'latitude', 'longitude']),
        ];
    }

    private function compressAndStoreCoverImage(UploadedFile $file, Tour $tour): string
    {
        return $this->compressAndStoreImage($file, 'tours/'.$tour->id, 1920, 1080);
    }

    private function compressAndStoreImage(UploadedFile $file, string $directory, int $maxWidth, int $maxHeight): string
    {
        $manager = new ImageManager(new Driver);
        $image = $manager->read($file->getRealPath());
        $image->scaleDown(width: $maxWidth, height: $maxHeight);

        $path = $directory.'/'.Str::random(40).'.webp';
        Storage::disk('public')->put($path, (string) $image->toWebp(85));

        return $path;
    }

    /**
     * Delete the photos of every stop on the tour (used when the tour is deleted).
     */
    private function deleteStopImages(Tour $tour): void
    {
        $images = TourStop::where('tour_id', $tour->id)->whereNotNull('image')->pluck('image')->all();

        if ($images) {
            Storage::disk('public')->delete($images);
        }
    }

    /**
     * Stops are re-created on every save, so a stop's current photo comes back
     * as `existing_image`. It is only kept when it really belongs to this tour.
     *
     * @param  array<string, mixed>  $stop
     * @param  list<string>  $previousImages
     */
    private function resolveStopImage(Request $request, int|string $index, array $stop, Tour $tour, array $previousImages): ?string
    {
        $upload = $request->file("stops.{$index}.image");
        if ($upload instanceof UploadedFile) {
            return $this->compressAndStoreImage($upload, 'tours/'.$tour->id.'/stops', 1600, 1600);
        }

        if (filter_var($stop['remove_image'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return null;
        }

        $existing = $stop['existing_image'] ?? null;

        return $existing && in_array($existing, $previousImages, true) ? $existing : null;
    }

    private function syncStops(Request $request, Tour $tour): void
    {
        $previousImages = TourStop::where('tour_id', $tour->id)->whereNotNull('image')->pluck('image')->all();
        $keptImages = [];

        TourStop::where('tour_id', $tour->id)->delete();

        $stops = $request->input('stops', []);
        foreach ($stops as $index => $stop) {
            $isLinked = ! empty($stop['trail_id']) || ! empty($stop['facility_id']);
            $isPinned = ! $isLinked
                && isset($stop['latitude'], $stop['longitude'])
                && $stop['latitude'] !== ''
                && $stop['longitude'] !== '';
            $isRoaming = ! $isLinked && ! $isPinned
                && filter_var($stop['is_roaming'] ?? false, FILTER_VALIDATE_BOOLEAN);

            if (! $isLinked && ! $isPinned && ! $isRoaming) {
                continue;
            }

            $image = $this->resolveStopImage($request, $index, $stop, $tour, $previousImages);
            if ($image) {
                $keptImages[] = $image;
            }

            TourStop::create([
                'tour_id' => $tour->id,
                'trail_id' => ! empty($stop['trail_id']) ? $stop['trail_id'] : null,
                'trail_feature_id' => ! empty($stop['feature_id']) ? $stop['feature_id'] : null,
                'facility_id' => ! empty($stop['facility_id']) ? $stop['facility_id'] : null,
                'latitude' => $isPinned ? (float) $stop['latitude'] : null,
                'longitude' => $isPinned ? (float) $stop['longitude'] : null,
                'is_roaming' => $isRoaming,
                'stop_order' => $index,
                'stop_label' => $stop['stop_label'] ?? null,
                'icon' => filled($stop['icon'] ?? null) ? trim($stop['icon']) : null,
                'icon_image' => filled($stop['icon_image'] ?? null) ? $stop['icon_image'] : null,
                'description' => $stop['description'] ?? null,
                'image' => $image,
                'driving_notes' => $stop['driving_notes'] ?? null,
                'estimated_visit_time' => $stop['estimated_visit_time'] ?? null,
            ]);
        }

        $orphanedImages = array_diff($previousImages, $keptImages);
        if ($orphanedImages) {
            Storage::disk('public')->delete(array_values($orphanedImages));
        }
    }
}
