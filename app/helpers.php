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

if (! function_exists('visitor_mobile_platform')) {
    /**
     * The visitor's mobile platform from the User-Agent: 'ios', 'android',
     * or null for desktop and anything unrecognised. Used to show only the
     * relevant app-store badge on phones while desktops see both.
     *
     * Note: iPadOS 13+ Safari reports a Macintosh User-Agent by default, so
     * those iPads fall through to null and are offered both stores.
     */
    function visitor_mobile_platform(?string $userAgent = null): ?string
    {
        $agent = $userAgent ?? (string) request()->userAgent();

        if (preg_match('/iPhone|iPad|iPod/i', $agent)) {
            return 'ios';
        }

        if (preg_match('/Android/i', $agent)) {
            return 'android';
        }

        return null;
    }
}

if (! function_exists('visitor_in_third_party_browser')) {
    /**
     * Whether the visitor is inside someone else's in-app browser — the
     * Instagram/Facebook bio-link browser, TikTok, Snapchat and friends.
     * These are WebViews, but their users have not installed our app.
     */
    function visitor_in_third_party_browser(?string $userAgent = null): bool
    {
        $agent = $userAgent ?? (string) request()->userAgent();

        return (bool) preg_match(
            '/Instagram|FBAN|FBAV|FB_IAB|FBIOS|Messenger|TikTok|musical_ly|BytedanceWebview|Snapchat|LinkedInApp|Pinterest|WhatsApp|Twitter|Line\/|MicroMessenger|GSA\//i',
            $agent
        );
    }
}

if (! function_exists('visitor_in_native_app')) {
    /**
     * Whether the page is being rendered inside the native app's WebView,
     * where promoting the app store makes no sense.
     *
     * Android WebViews append "; wv)" to the User-Agent — Chrome and Firefox
     * on Android don't. iOS WKWebViews omit the "Safari/" token that mobile
     * Safari always sends. Either platform may also identify itself with an
     * explicit "XploreSmithers" token if the app sets a custom User-Agent.
     *
     * Third-party in-app browsers (Instagram, Facebook, TikTok, …) are also
     * WebViews and would otherwise match both heuristics, hiding the store
     * badges from exactly the visitors arriving from a social bio link — so
     * they are excluded unless the app's own token is present.
     */
    function visitor_in_native_app(?string $userAgent = null): bool
    {
        $agent = $userAgent ?? (string) request()->userAgent();

        if (stripos($agent, 'XploreSmithers') !== false) {
            return true;
        }

        if (visitor_in_third_party_browser($agent)) {
            return false;
        }

        if (str_contains($agent, '; wv)')) {
            return true;
        }

        return visitor_mobile_platform($agent) === 'ios' && ! str_contains($agent, 'Safari/');
    }
}
