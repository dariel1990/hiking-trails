<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTourRequest;
use App\Http\Requests\Admin\UpdateTourRequest;
use App\Models\Tour;
use App\Models\TourStop;
use App\Models\Trail;
use App\Models\TrailFeature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class AdminTourController extends Controller
{
    public function index(): View
    {
        $tours = Tour::withCount('stops')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return view('admin.tours.index', compact('tours'));
    }

    public function create(): View
    {
        $availableTrails = Trail::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'start_coordinates', 'location_type']);

        $availableFeatures = TrailFeature::with('trail:id,name')
            ->whereNotNull('coordinates')
            ->orderBy('name')
            ->get(['id', 'trail_id', 'feature_type', 'name', 'coordinates']);

        return view('admin.tours.create', compact('availableTrails', 'availableFeatures'));
    }

    public function store(StoreTourRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['sort_order'] = $request->input('sort_order', 0);

        // Decode driving_route_coordinates from JSON string
        if (! empty($validated['driving_route_coordinates'])) {
            $decoded = json_decode($validated['driving_route_coordinates'], true);
            $validated['driving_route_coordinates'] = $decoded ?: null;
        } else {
            $validated['driving_route_coordinates'] = null;
        }

        unset($validated['cover_image'], $validated['stops']);

        $tour = Tour::create($validated);

        if ($request->hasFile('cover_image')) {
            $path = $this->compressAndStoreCoverImage($request->file('cover_image'), $tour);
            $tour->update(['cover_image' => $path]);
        }

        $this->syncStops($request, $tour);

        return redirect()->route('admin.tours.index')->with('success', 'Tour created successfully.');
    }

    public function edit(Tour $tour): View
    {
        $tour->load('stops.trail', 'stops.feature');

        $availableTrails = Trail::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'start_coordinates', 'location_type']);

        $availableFeatures = TrailFeature::with('trail:id,name')
            ->whereNotNull('coordinates')
            ->orderBy('name')
            ->get(['id', 'trail_id', 'feature_type', 'name', 'coordinates']);

        return view('admin.tours.edit', compact('tour', 'availableTrails', 'availableFeatures'));
    }

    public function update(UpdateTourRequest $request, Tour $tour): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['sort_order'] = $request->input('sort_order', 0);

        // Decode driving_route_coordinates from JSON string
        if (! empty($validated['driving_route_coordinates'])) {
            $decoded = json_decode($validated['driving_route_coordinates'], true);
            $validated['driving_route_coordinates'] = $decoded ?: $tour->driving_route_coordinates;
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

        return redirect()->route('admin.tours.index')->with('success', 'Tour updated successfully.');
    }

    public function destroy(Tour $tour): RedirectResponse
    {
        if ($tour->cover_image) {
            Storage::disk('public')->delete($tour->cover_image);
        }

        $tour->delete();

        return redirect()->route('admin.tours.index')->with('success', 'Tour deleted successfully.');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string', 'in:delete,activate,deactivate'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:tours,id'],
        ]);

        $ids = $validated['ids'];
        $count = count($ids);

        switch ($validated['action']) {
            case 'delete':
                Tour::query()->whereIn('id', $ids)->get()->each->delete();
                $message = "{$count} tour(s) deleted successfully.";
                break;
            case 'activate':
                Tour::query()->whereIn('id', $ids)->update(['is_active' => true]);
                $message = "{$count} tour(s) marked active.";
                break;
            case 'deactivate':
                Tour::query()->whereIn('id', $ids)->update(['is_active' => false]);
                $message = "{$count} tour(s) marked inactive.";
                break;
            default:
                $message = '';
        }

        return redirect()->route('admin.tours.index')->with('success', $message);
    }

    public function toggleActive(Tour $tour): JsonResponse
    {
        $tour->update(['is_active' => ! $tour->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $tour->is_active,
        ]);
    }

    private function compressAndStoreCoverImage(UploadedFile $file, Tour $tour): string
    {
        $manager = new ImageManager(new Driver);
        $image = $manager->read($file->getRealPath());
        $image->scaleDown(width: 1920, height: 1080);

        $path = 'tours/'.$tour->id.'/'.Str::random(40).'.webp';
        Storage::disk('public')->put($path, (string) $image->toWebp(85));

        return $path;
    }

    private function syncStops(Request $request, Tour $tour): void
    {
        TourStop::where('tour_id', $tour->id)->delete();

        $stops = $request->input('stops', []);
        foreach ($stops as $index => $stop) {
            if (empty($stop['trail_id'])) {
                continue;
            }

            TourStop::create([
                'tour_id' => $tour->id,
                'trail_id' => $stop['trail_id'],
                'trail_feature_id' => ! empty($stop['feature_id']) ? $stop['feature_id'] : null,
                'stop_order' => $index,
                'stop_label' => $stop['stop_label'] ?? null,
                'driving_notes' => $stop['driving_notes'] ?? null,
                'estimated_visit_time' => $stop['estimated_visit_time'] ?? null,
            ]);
        }
    }
}
