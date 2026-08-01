<?php

use App\Models\Setting;
use App\Services\ThemeService;

if (! function_exists('setting')) {
    /**
     * Get a global setting: database value, then $default, then the
     * registry default from config/settings.php. Safe to call before
     * the database is available (returns the fallback).
     */
    function setting(string $key, mixed $default = null): mixed
    {
        try {
            return Setting::get($key, $default);
        } catch (Throwable) {
            return $default ?? config("settings.definitions.{$key}.default");
        }
    }
}

if (! function_exists('theme_color')) {
    /**
     * Resolved theme hex for a palette shade (e.g. theme_color('forest', 600)).
     * For JS contexts (Mapbox paint, canvas) where CSS var() can't be used.
     */
    function theme_color(string $family, int $shade): string
    {
        return app(ThemeService::class)->colorHex($family, $shade);
    }
}

if (! function_exists('theme_color_rgb')) {
    /**
     * Resolved theme shade as "R, G, B" for legacy rgba(...) syntax in JS.
     */
    function theme_color_rgb(string $family, int $shade): string
    {
        return app(ThemeService::class)->colorRgb($family, $shade);
    }
}

if (! function_exists('subscriptions_enabled')) {
    /**
     * Whether web Pro subscription gating is active. The admin setting
     * overrides the SUBSCRIPTIONS_ENABLED env-backed config default.
     */
    function subscriptions_enabled(): bool
    {
        return (bool) setting('subscriptions_enabled', config('subscriptions.enabled'));
    }
}
