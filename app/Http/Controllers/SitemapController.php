<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Tour;
use App\Models\Town;
use App\Models\Trail;
use App\Models\TrailNetwork;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    private const CACHE_MINUTES = 60;

    public function __invoke(): Response
    {
        $urls = Cache::remember('sitemap:urls', now()->addMinutes(self::CACHE_MINUTES), fn () => $this->buildUrls());

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }

    /**
     * @return list<array{loc: string, lastmod: string|null, changefreq: string, priority: string}>
     */
    private function buildUrls(): array
    {
        $urls = [
            $this->url(route('home'), priority: '1.0', changefreq: 'daily'),
            $this->url(route('towns.index'), priority: '0.9', changefreq: 'weekly'),
            $this->url(route('trails.index'), priority: '0.9', changefreq: 'weekly'),
            $this->url(route('fishing-lakes.index'), priority: '0.8', changefreq: 'weekly'),
            $this->url(route('tours.index'), priority: '0.7', changefreq: 'weekly'),
            $this->url(route('businesses.public.index'), priority: '0.7', changefreq: 'weekly'),
            $this->url(route('trail-networks.index'), priority: '0.7', changefreq: 'weekly'),
            $this->url(route('map'), priority: '0.8', changefreq: 'weekly'),
            $this->url(route('events.index'), priority: '0.6', changefreq: 'daily'),
        ];

        /**
         * Town pages are the highest-value landing pages, but a town with no
         * trails is left out entirely rather than submitted as a thin page.
         */
        foreach (Town::active()->ordered()->get() as $town) {
            if (! $town->shouldIndex()) {
                continue;
            }

            $urls[] = $this->url(route('towns.show', $town), $town->updated_at?->toAtomString(), '0.9', 'weekly');
        }

        Trail::query()
            ->whereIn('status', ['active', 'seasonal'])
            ->whereDoesntHave('trailNetwork', fn ($q) => $q->where('is_active', false))
            ->select('id', 'updated_at')
            ->orderBy('id')
            ->chunk(500, function ($trails) use (&$urls) {
                foreach ($trails as $trail) {
                    $urls[] = $this->url(route('trails.show', $trail->id), $trail->updated_at?->toAtomString(), '0.7');
                }
            });

        foreach (Business::query()->where('is_active', true)->get(['slug', 'updated_at']) as $business) {
            $urls[] = $this->url(route('businesses.public.show', $business->slug), $business->updated_at?->toAtomString(), '0.6');
        }

        foreach (Tour::active()->get(['slug', 'updated_at']) as $tour) {
            $urls[] = $this->url(route('tours.show', $tour->slug), $tour->updated_at?->toAtomString(), '0.6');
        }

        foreach (TrailNetwork::query()->where('is_active', true)->get(['slug', 'updated_at']) as $network) {
            $urls[] = $this->url(route('trail-networks.show', $network->slug), $network->updated_at?->toAtomString(), '0.6');
        }

        return $urls;
    }

    /**
     * @return array{loc: string, lastmod: string|null, changefreq: string, priority: string}
     */
    private function url(string $loc, ?string $lastmod = null, string $priority = '0.5', string $changefreq = 'monthly'): array
    {
        return compact('loc', 'lastmod', 'changefreq', 'priority');
    }
}
