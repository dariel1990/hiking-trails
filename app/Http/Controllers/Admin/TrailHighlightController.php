<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trail;
use App\Models\TrailFeature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
            ->paginate(20)
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

        return redirect()->route('admin.highlights.index')
            ->with('success', 'Highlight updated successfully.');
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
}
