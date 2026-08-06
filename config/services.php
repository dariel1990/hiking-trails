<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'app_api_key' => env('APP_API_KEY'),

    'geo_override_country' => env('GEO_OVERRIDE_COUNTRY'),

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'openrouteservice' => [
        'api_key' => env('OPENROUTESERVICE_API_KEY'),
        'base_url' => 'https://api.openrouteservice.org',
    ],

    'mapbox' => [
        'access_token' => env('MAPBOX_ACCESS_TOKEN'),
    ],

    'google_play' => [
        'package_name' => env('GOOGLE_PLAY_PACKAGE_NAME', 'com.xploresmithers.app'),
        'service_account_json' => env('GOOGLE_PLAY_SERVICE_ACCOUNT_JSON'),
        'rtdn_token' => env('GOOGLE_PLAY_RTDN_TOKEN'),
        // Comma-separated Play Console offer ids that represent a free trial, so
        // an incoming subscription can be flagged is_trial. Play returns no
        // boolean for this.
        'trial_offer_ids' => env('GOOGLE_PLAY_TRIAL_OFFER_IDS', ''),
        // Play Integrity API reuses the same service account JSON above.
        // Before deploying, enable "Play Integrity API" for the linked Google Cloud
        // project in Play Console → Setup → API access, then grant the service
        // account the "Play Integrity API" role.
        'integrity_enabled' => env('PLAY_INTEGRITY_ENABLED', false),
    ],

    'app_store' => [
        // App Store Connect → Users and Access → Integrations → In-App Purchase
        // key. private_key is a path (absolute or relative to base_path) to the
        // downloaded .p8 file.
        'issuer_id' => env('APP_STORE_ISSUER_ID'),
        'key_id' => env('APP_STORE_KEY_ID'),
        'private_key' => env('APP_STORE_PRIVATE_KEY'),
        'bundle_id' => env('APP_STORE_BUNDLE_ID', 'com.xploresmithers.app'),
    ],

    'apple' => [
        // Sign in with Apple (native app flow): the identity token's `aud`
        // claim is the iOS bundle id. Distinct from the app_store billing keys.
        'signin_bundle_id' => env('APPLE_SIGNIN_BUNDLE_ID', 'com.xploresmithers.app'),
    ],

    'events_import' => [
        // Shared secret for POST /api/events/import (set on both environments).
        'token' => env('EVENTS_IMPORT_TOKEN'),
        // Where `events:scrape --push` sends the scraped events (local only).
        'push_url' => env('EVENTS_PUSH_URL'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'prices' => [
            'monthly' => env('STRIPE_PRICE_MONTHLY'),
            'annual' => env('STRIPE_PRICE_ANNUAL'),
        ],
        'trial_days' => (int) env('STRIPE_TRIAL_DAYS', 7),

        'enabled' => (bool) env('STRIPE_ENABLED', false),
    ],

    'google' => [
        'web_client_id' => env('GOOGLE_WEB_CLIENT_ID'),
        'client_id' => env('GOOGLE_WEB_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'google_analytics' => [
        'measurement_id' => env('GOOGLE_ANALYTICS_MEASUREMENT_ID'),
    ],

    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
        'min_score' => (float) env('RECAPTCHA_MIN_SCORE', 0.5),
        'verify_url' => env('RECAPTCHA_VERIFY_URL', 'https://www.google.com/recaptcha/api/siteverify'),
    ],

    'android_app' => [
        'package_name' => env('ANDROID_APP_PACKAGE_NAME'),
        'play_store_url' => env('ANDROID_APP_PLAY_STORE_URL'),
        'sha256_fingerprints' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('ANDROID_APP_SHA256_FINGERPRINTS', ''))
        ))),
    ],

    'ios_app' => [
        // Public App Store listing used by the marketing badges. Distinct from
        // the `app_store` block above, which holds In-App Purchase credentials.
        'app_store_url' => env('IOS_APP_STORE_URL'),
    ],

];
