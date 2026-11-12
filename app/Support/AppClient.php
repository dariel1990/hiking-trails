<?php

namespace App\Support;

use Illuminate\Http\Request;

class AppClient
{
    /**
     * Whether the request comes from one of the native apps' WebViews.
     *
     * The apps recognise trail pages by the numeric /trails/{id} URL (favorites,
     * offline downloads, the native map), so app requests keep getting ID URLs
     * while browsers get slug URLs. This defers to visitor_in_native_app(),
     * which matches the apps' "XploreSmithersApp" user-agent token and falls
     * back to WebView fingerprints in case the token was not applied. Erring
     * towards "app" is safe: ID URLs always keep working.
     */
    public static function isApp(?Request $request = null): bool
    {
        $request ??= request();

        return visitor_in_native_app((string) $request->userAgent());
    }
}
