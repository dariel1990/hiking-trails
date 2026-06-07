<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Subscription Features Master Switch
    |--------------------------------------------------------------------------
    |
    | When disabled, all web Pro features (GPX download, POI, videos) are
    | unlocked for every visitor and the "Go Pro" / paywall UI is hidden.
    | This is a web-only switch; the Android app keeps its own Google Play
    | entitlement logic regardless of this value.
    |
    */

    'enabled' => filter_var(env('SUBSCRIPTIONS_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

];
