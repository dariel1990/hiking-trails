<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ActivityTypeController extends Controller
{
    /**
     * Display a listing of activity types
     */
    public function index(Request $request)
    {
        $query = ActivityType::withCount('trails');

        // Search filter
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        // Season filter
        if ($request->season) {
            $query->where('season_applicable', $request->season);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $activityTypes = $query->orderBy('name')->paginate(15);

        return view('admin.activity-types.index', compact('activityTypes'));
    }

    /**
     * Show the form for creating a new activity type
     */
    public function create()
    {
        return view('admin.activity-types.create');
    }

    /**
     * Store a newly created activity type
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:activity_types,slug',
            'icon' => 'nullable|string|max:10',
            'icon_image' => 'nullable|string|max:255',
            'color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string|max:500',
            'season_applicable' => 'required|in:summer,winter,both',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);
        $validated['is_active'] = $request->has('is_active') ? true : false;

        if (! empty($validated['icon_image'])) {
            if (! str_starts_with($validated['icon_image'], 'activity-type-icons/') || str_contains($validated['icon_image'], '..')) {
                abort(422, 'Invalid icon image.');
            }
            $validated['icon'] = null;
        }

        ActivityType::create($validated);

        return redirect()->route('admin.activity-types.index')
            ->with('success', 'Activity type created successfully!');
    }

    /**
     * Show the form for editing the specified activity type
     */
    public function edit(ActivityType $activityType)
    {
        $activityType->loadCount('trails');

        return view('admin.activity-types.edit', compact('activityType'));
    }

    /**
     * Update the specified activity type
     */
    public function update(Request $request, ActivityType $activityType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:activity_types,slug,'.$activityType->id,
            'icon' => 'nullable|string|max:10',
            'icon_image' => 'nullable|string|max:255',
            'color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string|max:500',
            'season_applicable' => 'required|in:summer,winter,both',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);
        $validated['is_active'] = $request->has('is_active') ? true : false;

        if (! empty($validated['icon_image'])) {
            if (! str_starts_with($validated['icon_image'], 'activity-type-icons/') || str_contains($validated['icon_image'], '..')) {
                abort(422, 'Invalid icon image.');
            }
            $validated['icon'] = null;
        }

        $activityType->update($validated);

        return redirect()->route('admin.activity-types.index')
            ->with('success', 'Activity type updated successfully!');
    }

    /**
     * List previously uploaded activity type icons for reuse.
     */
    public function listIcons(): JsonResponse
    {
        $files = Storage::disk('public')->files('activity-type-icons');

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
     * Upload a new activity type icon and return its path + URL.
     */
    public function uploadIcon(Request $request): JsonResponse
    {
        $request->validate([
            'icon' => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
        ]);

        $path = $request->file('icon')->store('activity-type-icons', 'public');

        return response()->json([
            'path' => $path,
            'url' => asset('storage/'.$path),
        ]);
    }

    /**
     * Delete an activity type icon.
     */
    public function deleteIcon(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string',
            'force' => 'nullable|boolean',
        ]);

        $path = $request->string('path')->toString();

        if (! str_starts_with($path, 'activity-type-icons/') || str_contains($path, '..')) {
            abort(422, 'Invalid icon path.');
        }

        $inUse = ActivityType::where('icon_image', $path)->count();

        if ($inUse > 0 && ! $request->boolean('force')) {
            return response()->json(['deleted' => false, 'in_use' => $inUse]);
        }

        if ($inUse > 0) {
            // Clear the reference so these records fall back to the stock icon
            // instead of pointing at a now-deleted image.
            ActivityType::where('icon_image', $path)->update(['icon_image' => null]);
        }

        Storage::disk('public')->delete($path);

        return response()->json(['deleted' => true]);
    }

    /**
     * Remove the specified activity type
     */
    public function destroy(ActivityType $activityType)
    {
        $trailCount = $activityType->trails()->count();

        if ($trailCount > 0) {
            return redirect()->route('admin.activity-types.index')
                ->with('error', "Cannot delete '{$activityType->name}'. It is currently used in {$trailCount} trail(s).");
        }

        $activityType->delete();

        return redirect()->route('admin.activity-types.index')
            ->with('success', 'Activity type deleted successfully!');
    }
}
