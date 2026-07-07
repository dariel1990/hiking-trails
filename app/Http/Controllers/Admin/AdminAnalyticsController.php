<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Event;
use App\Models\Facility;
use App\Models\Subscription;
use App\Models\Tour;
use App\Models\Trail;
use App\Models\TrailMedia;
use App\Models\TrailNetwork;
use App\Models\TrailPhoto;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AdminAnalyticsController extends Controller
{
    /**
     * Baseline CAD price points used to estimate recurring revenue. Actual
     * charged amounts are region-dependent and not stored per subscription, so
     * these figures are clearly labelled as estimates in the UI.
     */
    private const MONTHLY_PRICE = 4.99;

    private const ANNUAL_PRICE = 39.99;

    /**
     * @var list<string>
     */
    private const MONTHLY_PRODUCT_IDS = [
        'xs_offline_monthly',
        'xs_pro_web_monthly',
    ];

    public function index(): View
    {
        return view('admin.analytics.index', [
            'subscriptions' => $this->subscriptionAnalytics(),
            'users' => $this->userAnalytics(),
            'content' => $this->contentAnalytics(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function subscriptionAnalytics(): array
    {
        $all = Subscription::query()->get(['user_id', 'status', 'platform', 'product_id', 'expires_at', 'created_at']);

        $entitled = $all->filter(fn (Subscription $s): bool => $s->isEntitled());

        $activeMonthly = $entitled->filter(
            fn (Subscription $s): bool => in_array($s->product_id, self::MONTHLY_PRODUCT_IDS, true)
        )->count();
        $activeAnnual = $entitled->count() - $activeMonthly;

        $mrr = ($activeMonthly * self::MONTHLY_PRICE) + ($activeAnnual * (self::ANNUAL_PRICE / 12));

        $totalUsers = User::count();
        $entitledUserCount = $entitled->pluck('user_id')->unique()->count();

        return [
            'total' => $all->count(),
            'active' => $entitled->count(),
            'android' => $entitled->where('platform', 'android')->count(),
            'web' => $entitled->where('platform', 'web')->count(),
            'active_monthly' => $activeMonthly,
            'active_annual' => $activeAnnual,
            'expiring_soon' => $entitled->filter(
                fn (Subscription $s): bool => $s->expires_at !== null
                    && $s->expires_at->lte(now()->addDays(7))
            )->count(),
            'mrr' => $mrr,
            'arr' => $mrr * 12,
            'by_status' => $this->countBy($all, 'status'),
            'by_platform' => $this->countBy($all, 'platform'),
            'by_product' => $this->countBy($all, 'product_id'),
            'conversion_rate' => $totalUsers > 0 ? round(($entitledUserCount / $totalUsers) * 100, 1) : 0.0,
            'new_by_month' => $this->countByMonth($all),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function userAnalytics(): array
    {
        $users = User::query()->get(['is_admin', 'created_at']);

        return [
            'total' => $users->count(),
            'admins' => $users->where('is_admin', true)->count(),
            'members' => $users->where('is_admin', false)->count(),
            'new_by_month' => $this->countByMonth($users),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contentAnalytics(): array
    {
        return [
            'trails_total' => Trail::count(),
            'trails' => Trail::where('location_type', 'trail')->count(),
            'fishing_lakes' => Trail::where('location_type', 'fishing_lake')->count(),
            'trails_active' => Trail::where('status', 'active')->count(),
            'trails_closed' => Trail::where('status', '!=', 'active')->count(),
            'trails_featured' => Trail::where('is_featured', true)->count(),
            'by_difficulty' => $this->trailDifficulty(),
            'by_source' => [
                'gpx' => Trail::where('data_source', 'gpx')->count(),
                'manual' => Trail::where('data_source', 'manual')->count(),
                'mixed' => Trail::where('data_source', 'mixed')->count(),
            ],
            'businesses' => Business::count(),
            'businesses_active' => Business::where('is_active', true)->count(),
            'businesses_featured' => Business::where('is_featured', true)->count(),
            'by_business_type' => $this->countBy(
                Business::query()->get(['business_type']),
                'business_type'
            ),
            'facilities' => Facility::count(),
            'tours' => Tour::count(),
            'events' => Event::count(),
            'networks' => TrailNetwork::count(),
            'photos_pending' => TrailPhoto::where('status', 'pending')->count(),
            'photos_approved' => TrailPhoto::where('status', 'approved')->count(),
            'media' => TrailMedia::count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function trailDifficulty(): array
    {
        $labels = [
            '1' => 'Easy',
            '2' => 'Moderate',
            '3' => 'Intermediate',
            '4' => 'Hard',
            '5' => 'Expert',
        ];

        $buckets = array_fill_keys(array_values($labels), 0);
        $buckets['Unrated'] = 0;

        Trail::query()->pluck('difficulty_level')->each(function ($level) use (&$buckets, $labels): void {
            if ($level === null || $level === '') {
                $buckets['Unrated']++;

                return;
            }

            $key = (string) (int) round((float) $level);
            $buckets[$labels[$key] ?? 'Unrated']++;
        });

        return $buckets;
    }

    /**
     * Count a collection grouped by an attribute, sorted by count descending.
     *
     * @param  Collection<int, Model>  $items
     * @return array<string, int>
     */
    private function countBy(Collection $items, string $attribute): array
    {
        return $items
            ->groupBy(fn ($item): string => (string) ($item->{$attribute} ?? 'unknown'))
            ->map->count()
            ->sortDesc()
            ->all();
    }

    /**
     * Count records per month for the trailing 12 months, keyed "Mon YY".
     * Grouping happens in PHP to stay database-driver agnostic.
     *
     * @param  Collection<int, Model>  $items
     * @return array<string, int>
     */
    private function countByMonth(Collection $items): array
    {
        $months = collect(range(11, 0))->mapWithKeys(function (int $ago): array {
            $label = now()->startOfMonth()->subMonths($ago)->format('M y');

            return [$label => 0];
        })->all();

        $earliest = now()->startOfMonth()->subMonths(11);

        $items->each(function ($item) use (&$months, $earliest): void {
            $created = $item->created_at;

            if (! $created instanceof Carbon || $created->lt($earliest)) {
                return;
            }

            $label = $created->format('M y');

            if (array_key_exists($label, $months)) {
                $months[$label]++;
            }
        });

        return $months;
    }
}
