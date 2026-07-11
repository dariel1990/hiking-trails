<?php

use App\Models\Setting;

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
