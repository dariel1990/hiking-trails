<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trail;
use App\Models\TrailVisit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class DeviceAnalyticsController extends Controller
{
    public function index(): View
    {
        $visits = TrailVisit::query()->get(['trail_id', 'device_type', 'platform', 'browser']);

        $topTrails = Trail::query()
            ->withCount('visits')
            ->having('visits_count', '>', 0)
            ->orderByDesc('visits_count')
            ->limit(10)
            ->get(['id', 'name', 'location_type']);

        return view('admin.device-analytics.index', [
            'totalVisits' => $visits->count(),
            'trailsWithVisits' => $visits->pluck('trail_id')->unique()->count(),
            'byDeviceType' => $this->countBy($visits, 'device_type'),
            'byBrowser' => $this->countBy($visits, 'browser'),
            'byPlatform' => $this->countBy($visits, 'platform'),
            'topTrails' => $topTrails,
        ]);
    }

    /**
     * Count a collection grouped by an attribute, sorted by count descending.
     *
     * @return array<string, int>
     */
    private function countBy(Collection $items, string $attribute): array
    {
        return $items
            ->groupBy(fn ($item): string => (string) ($item->{$attribute} ?: 'Unknown'))
            ->map->count()
            ->sortDesc()
            ->all();
    }
}
