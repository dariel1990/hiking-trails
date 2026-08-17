<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReviewPromptEvent;
use App\Models\Trail;
use App\Models\TrailVisit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class DeviceAnalyticsController extends Controller
{
    public function index(): View
    {
        $visits = TrailVisit::query()->get(['trail_id', 'device_type', 'platform', 'browser']);

        // whereHas rather than having('visits_count', '>', 0): SQLite rejects a
        // HAVING clause on a query with no GROUP BY, which MySQL tolerates.
        $topTrails = Trail::query()
            ->withCount('visits')
            ->whereHas('visits')
            ->orderByDesc('visits_count')
            ->limit(10)
            ->get(['id', 'name', 'location_type']);

        $reviewEvents = ReviewPromptEvent::query()->get(['channel', 'action']);
        $promptsShown = $reviewEvents->where('action', 'shown')->count();
        $reviewClicks = $reviewEvents->where('action', 'review_clicked')->count();

        return view('admin.device-analytics.index', [
            'totalVisits' => $visits->count(),
            'trailsWithVisits' => $visits->pluck('trail_id')->unique()->count(),
            'byDeviceType' => $this->countBy($visits, 'device_type'),
            'byBrowser' => $this->countBy($visits, 'browser'),
            'byPlatform' => $this->countBy($visits, 'platform'),
            'topTrails' => $topTrails,
            'promptsShown' => $promptsShown,
            'reviewClicks' => $reviewClicks,
            'feedbackClicks' => $reviewEvents->where('action', 'feedback_clicked')->count(),
            'reviewClickRate' => $promptsShown > 0 ? round($reviewClicks / $promptsShown * 100, 1) : 0.0,
            'reviewsByChannel' => $this->countBy($reviewEvents->where('action', 'review_clicked'), 'channel'),
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
