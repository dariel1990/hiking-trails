<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessMedia;
use App\Models\FacilityMedia;
use App\Models\TrailMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->input('type', 'all');
        $source = $request->input('source', 'all');
        $search = $request->input('search');
        $perPage = 10;

        $trailQuery = TrailMedia::with(['trail', 'features'])
            ->when($type !== 'all', function ($q) use ($type) {
                if ($type === 'photo') {
                    $q->where('media_type', 'photo');
                } else {
                    $q->whereIn('media_type', ['video', 'video_url']);
                }
            })
            ->when($search, fn ($q) => $q->whereHas('trail', fn ($tq) => $tq->where('name', 'like', "%{$search}%")));

        $facilityQuery = FacilityMedia::with('facility')
            ->when($type !== 'all', function ($q) use ($type) {
                if ($type === 'photo') {
                    $q->where('media_type', 'photo');
                } else {
                    $q->where('media_type', 'video_url');
                }
            })
            ->when($search, fn ($q) => $q->whereHas('facility', fn ($fq) => $fq->where('name', 'like', "%{$search}%")));

        $businessQuery = BusinessMedia::with('business')
            ->when($type !== 'all', function ($q) use ($type) {
                if ($type === 'photo') {
                    $q->where('media_type', 'photo');
                } else {
                    $q->where('media_type', 'video_url');
                }
            })
            ->when($search, fn ($q) => $q->whereHas('business', fn ($bq) => $bq->where('name', 'like', "%{$search}%")));

        $items = collect();

        if (in_array($source, ['all', 'trail'])) {
            $items = $items->concat($trailQuery->get()->map(fn ($m) => ['media' => $m, 'source' => 'trail']));
        }
        if (in_array($source, ['all', 'facility'])) {
            $items = $items->concat($facilityQuery->get()->map(fn ($m) => ['media' => $m, 'source' => 'facility']));
        }
        if (in_array($source, ['all', 'business'])) {
            $items = $items->concat($businessQuery->get()->map(fn ($m) => ['media' => $m, 'source' => 'business']));
        }

        $items = $items->sortByDesc(fn ($item) => $item['media']->created_at)->values();

        $page = max((int) $request->input('page', 1), 1);
        $media = new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $stats = [
            'total' => TrailMedia::count() + FacilityMedia::count() + BusinessMedia::count(),
            'photos' => TrailMedia::where('media_type', 'photo')->count()
                + FacilityMedia::where('media_type', 'photo')->count()
                + BusinessMedia::where('media_type', 'photo')->count(),
            'videos' => TrailMedia::whereIn('media_type', ['video', 'video_url'])->count()
                + FacilityMedia::where('media_type', 'video_url')->count()
                + BusinessMedia::where('media_type', 'video_url')->count(),
            'trail_media' => TrailMedia::count(),
            'facility_media' => FacilityMedia::count(),
            'business_media' => BusinessMedia::count(),
        ];

        return view('admin.media.index', compact('media', 'stats', 'type', 'source', 'search'));
    }

    public function showTrailMedia(TrailMedia $media): View
    {
        $media->load(['trail', 'features.trail']);

        return view('admin.media.show', [
            'media' => $media,
            'mediaSource' => 'trail',
            'attachedTo' => $media->trail,
            'features' => $media->features,
        ]);
    }

    public function showFacilityMedia(FacilityMedia $media): View
    {
        $media->load('facility');

        return view('admin.media.show', [
            'media' => $media,
            'mediaSource' => 'facility',
            'attachedTo' => $media->facility,
            'features' => collect(),
        ]);
    }

    public function showBusinessMedia(BusinessMedia $media): View
    {
        $media->load('business');

        return view('admin.media.show', [
            'media' => $media,
            'mediaSource' => 'business',
            'attachedTo' => $media->business,
            'features' => collect(),
        ]);
    }

    public function destroyTrailMedia(TrailMedia $media): RedirectResponse
    {
        $trailName = $media->trail?->name ?? 'Unknown trail';
        $media->delete();

        return redirect()->route('admin.media.index')
            ->with('success', "Media deleted from \"{$trailName}\".");
    }

    public function destroyFacilityMedia(FacilityMedia $media): RedirectResponse
    {
        $facilityName = $media->facility?->name ?? 'Unknown facility';
        $media->delete();

        return redirect()->route('admin.media.index')
            ->with('success', "Media deleted from \"{$facilityName}\".");
    }

    public function destroyBusinessMedia(BusinessMedia $media): RedirectResponse
    {
        $businessName = $media->business?->name ?? 'Unknown business';
        $media->delete();

        return redirect()->route('admin.media.index')
            ->with('success', "Media deleted from \"{$businessName}\".");
    }
}
