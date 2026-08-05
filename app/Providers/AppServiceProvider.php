<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(300)->by($request->user()?->id ?: $request->ip());
        });

        // Public content endpoints (no app key, called by the website map JS).
        // Named limiters keep their own cache keys, so these stack safely with
        // any per-route throttle (e.g. /trail-photos' own 5-per-hour limit).
        RateLimiter::for('public-api', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        // OpenRouteService proxies — stricter, they spend the paid ORS quota.
        RateLimiter::for('ors', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });
    }
}
