<?php

/*
|--------------------------------------------------------------------------
| Global Settings Registry
|--------------------------------------------------------------------------
|
| Single source of truth for admin-editable global settings. The admin
| settings form, validation rules, value casting, and database seeding
| are all derived from these definitions. To add a new setting, add one
| entry to 'definitions' — it will appear on /admin/settings automatically.
|
| Definition shape:
|   'group'   => key of 'groups' below (tab on the settings page)
|   'label'   => field label shown in the admin form
|   'type'    => cast type: string | int | float | bool | json
|   'input'   => form control: text | textarea | number | toggle | url | email | json | time
|   'default' => fallback value used when no row exists in the database
|   'rules'   => Laravel validation rules applied on save
|   'hint'    => optional helper text shown under the field
*/

return [

    'groups' => [
        'branding' => [
            'label' => 'Branding',
            'description' => 'Site name, logos, taglines and SEO text shown across the public site.',
        ],
        'contact' => [
            'label' => 'Contact & Social',
            'description' => 'Social media links, support URL and contact details. Blank social links are hidden from the footer.',
        ],
        'map' => [
            'label' => 'Map',
            'description' => 'Default camera position and terrain for the interactive and home-page maps.',
        ],
        'subscriptions' => [
            'label' => 'Subscriptions',
            'description' => 'Pro subscription switch, display pricing, trial and notification settings.',
        ],
        'content' => [
            'label' => 'Content & Limits',
            'description' => 'Page sizes for public and admin listings, and visitor photo upload limits.',
        ],
        'routing' => [
            'label' => 'GPX & Routing',
            'description' => 'Hiking time estimation constants and GPX housekeeping.',
        ],
        'system' => [
            'label' => 'System',
            'description' => 'Developer alerts and the events scraper.',
        ],
    ],

    'definitions' => [

        // Branding
        'site_name' => [
            'group' => 'branding',
            'label' => 'Site name',
            'type' => 'string',
            'input' => 'text',
            'default' => 'Xplore Smithers',
            'rules' => ['required', 'string', 'max:120'],
            'hint' => 'Shown in the site header next to the logo.',
        ],
        'tagline' => [
            'group' => 'branding',
            'label' => 'Tagline',
            'type' => 'string',
            'input' => 'text',
            'default' => 'Discover Smithers BC',
            'rules' => ['nullable', 'string', 'max:160'],
            'hint' => 'Small text under the site name in the header.',
        ],
        'header_logo_path' => [
            'group' => 'branding',
            'label' => 'Header logo path',
            'type' => 'string',
            'input' => 'text',
            'default' => 'images/xplore-smithers-logo.png',
            'rules' => ['required', 'string', 'max:255'],
            'hint' => 'Path relative to the public directory.',
        ],
        'footer_logo_path' => [
            'group' => 'branding',
            'label' => 'Footer logo path',
            'type' => 'string',
            'input' => 'text',
            'default' => 'images/logo.png',
            'rules' => ['required', 'string', 'max:255'],
            'hint' => 'Path relative to the public directory. Also used as the favicon.',
        ],
        'footer_brand_name' => [
            'group' => 'branding',
            'label' => 'Footer brand name',
            'type' => 'string',
            'input' => 'text',
            'default' => 'Trail Finder',
            'rules' => ['required', 'string', 'max:120'],
        ],
        'footer_tagline' => [
            'group' => 'branding',
            'label' => 'Footer tagline',
            'type' => 'string',
            'input' => 'text',
            'default' => 'Ethical Adventures',
            'rules' => ['nullable', 'string', 'max:160'],
        ],
        'footer_mission_text' => [
            'group' => 'branding',
            'label' => 'Footer mission statement',
            'type' => 'string',
            'input' => 'textarea',
            'default' => "We inspire connection through trail discovery. Experience nature in a way that's real, mindful, and unforgettable while promoting respectful, sustainable tourism.",
            'rules' => ['nullable', 'string', 'max:1000'],
        ],
        'default_page_title' => [
            'group' => 'branding',
            'label' => 'Default page title',
            'type' => 'string',
            'input' => 'text',
            'default' => 'Trail Finder - Discover Ethical Adventures',
            'rules' => ['required', 'string', 'max:255'],
            'hint' => 'Browser-tab / SEO title used when a page does not set its own.',
        ],
        'meta_description' => [
            'group' => 'branding',
            'label' => 'Meta description',
            'type' => 'string',
            'input' => 'textarea',
            'default' => 'Discover amazing hiking trails while promoting respectful, sustainable tourism. Explore with purpose, travel with respect.',
            'rules' => ['nullable', 'string', 'max:320'],
            'hint' => 'Default SEO description for public pages.',
        ],
        'meta_keywords' => [
            'group' => 'branding',
            'label' => 'Meta keywords',
            'type' => 'string',
            'input' => 'text',
            'default' => 'hiking trails, sustainable tourism, ethical adventures, trail finder, outdoor exploration',
            'rules' => ['nullable', 'string', 'max:500'],
        ],
        'copyright_text' => [
            'group' => 'branding',
            'label' => 'Copyright text',
            'type' => 'string',
            'input' => 'text',
            'default' => 'Trail Finder.',
            'rules' => ['nullable', 'string', 'max:255'],
            'hint' => 'Rendered after the © year in the footer.',
        ],
        'town_name' => [
            'group' => 'branding',
            'label' => 'Town name',
            'type' => 'string',
            'input' => 'text',
            'default' => 'Smithers',
            'rules' => ['required', 'string', 'max:120'],
            'hint' => 'Used in labels such as "12 km from Smithers" on the map.',
        ],

        // Contact & Social
        'social_youtube_url' => [
            'group' => 'contact',
            'label' => 'YouTube URL',
            'type' => 'string',
            'input' => 'url',
            'default' => 'https://youtube.com/@xploresmithers?si=Q9jtjqElsvfcigNH',
            'rules' => ['nullable', 'url', 'max:500'],
            'hint' => 'Leave blank to hide the icon in the footer.',
        ],
        'social_instagram_url' => [
            'group' => 'contact',
            'label' => 'Instagram URL',
            'type' => 'string',
            'input' => 'url',
            'default' => 'https://www.instagram.com/xploresmithers?igsh=Y3huYTRtM243cTdi',
            'rules' => ['nullable', 'url', 'max:500'],
            'hint' => 'Leave blank to hide the icon in the footer.',
        ],
        'social_tiktok_url' => [
            'group' => 'contact',
            'label' => 'TikTok URL',
            'type' => 'string',
            'input' => 'url',
            'default' => 'https://www.tiktok.com/@xploresmithers?_t=ZS-90lIATag1Ld&_r=1',
            'rules' => ['nullable', 'url', 'max:500'],
            'hint' => 'Leave blank to hide the icon in the footer.',
        ],
        'social_facebook_url' => [
            'group' => 'contact',
            'label' => 'Facebook URL',
            'type' => 'string',
            'input' => 'url',
            'default' => 'https://www.facebook.com/share/1C9Q3PAT7i/',
            'rules' => ['nullable', 'url', 'max:500'],
            'hint' => 'Leave blank to hide the icon in the footer.',
        ],
        'support_donation_url' => [
            'group' => 'contact',
            'label' => 'Support / donation URL',
            'type' => 'string',
            'input' => 'url',
            'default' => 'https://xploresmithers.com/support/',
            'rules' => ['nullable', 'url', 'max:500'],
        ],
        'main_site_url' => [
            'group' => 'contact',
            'label' => 'Main site URL',
            'type' => 'string',
            'input' => 'url',
            'default' => 'https://xploresmithers.com/',
            'rules' => ['nullable', 'url', 'max:500'],
            'hint' => 'The header logo links here.',
        ],
        'contact_address' => [
            'group' => 'contact',
            'label' => 'Contact address',
            'type' => 'string',
            'input' => 'textarea',
            'default' => 'Smithers, British Columbia, Canada',
            'rules' => ['nullable', 'string', 'max:500'],
            'hint' => 'Shown on the privacy policy page.',
        ],

        // Map
        'map_default_lat' => [
            'group' => 'map',
            'label' => 'Default latitude',
            'type' => 'float',
            'input' => 'number',
            'default' => 54.7804,
            'rules' => ['required', 'numeric', 'between:-90,90'],
        ],
        'map_default_lng' => [
            'group' => 'map',
            'label' => 'Default longitude',
            'type' => 'float',
            'input' => 'number',
            'default' => -127.1698,
            'rules' => ['required', 'numeric', 'between:-180,180'],
        ],
        'map_default_zoom' => [
            'group' => 'map',
            'label' => 'Main map zoom',
            'type' => 'int',
            'input' => 'number',
            'default' => 10,
            'rules' => ['required', 'integer', 'between:1,22'],
        ],
        'home_map_zoom' => [
            'group' => 'map',
            'label' => 'Home hero map zoom',
            'type' => 'int',
            'input' => 'number',
            'default' => 9,
            'rules' => ['required', 'integer', 'between:1,22'],
            'hint' => 'The home hero map shares the default center above with its own zoom.',
        ],
        'map_terrain_exaggeration' => [
            'group' => 'map',
            'label' => '3D terrain exaggeration',
            'type' => 'float',
            'input' => 'number',
            'default' => 1.5,
            'rules' => ['required', 'numeric', 'between:0,10'],
        ],

        // Subscriptions
        'subscriptions_enabled' => [
            'group' => 'subscriptions',
            'label' => 'Subscriptions enabled',
            'type' => 'bool',
            'input' => 'toggle',
            'default' => true,
            'rules' => ['boolean'],
            'hint' => 'When off, all web Pro features are unlocked for every visitor and paywall UI is hidden. Overrides the SUBSCRIPTIONS_ENABLED env value.',
        ],
        'regional_pricing' => [
            'group' => 'subscriptions',
            'label' => 'Regional pricing (JSON)',
            'type' => 'json',
            'input' => 'json',
            'default' => [
                'CA' => ['currency' => 'CAD', 'symbol' => '$', 'monthly' => '4.99', 'annual' => '39.99'],
                'US' => ['currency' => 'USD', 'symbol' => '$', 'monthly' => '3.99', 'annual' => '31.99'],
                'PH' => ['currency' => 'PHP', 'symbol' => '₱', 'monthly' => '240', 'annual' => '1,950'],
                'GB' => ['currency' => 'GBP', 'symbol' => '£', 'monthly' => '2.99', 'annual' => '24.99'],
                'AU' => ['currency' => 'AUD', 'symbol' => '$', 'monthly' => '5.99', 'annual' => '47.99'],
            ],
            'rules' => ['required', 'json'],
            'hint' => 'Display prices per country code. Each entry needs currency, symbol, monthly and annual. Stripe charges the configured Stripe Price — these values are what visitors see.',
        ],
        'default_country' => [
            'group' => 'subscriptions',
            'label' => 'Default pricing country',
            'type' => 'string',
            'input' => 'text',
            'default' => 'CA',
            'rules' => ['required', 'string', 'size:2'],
            'hint' => 'Two-letter country code used when a visitor\'s country is not in the pricing table.',
        ],
        'trial_days' => [
            'group' => 'subscriptions',
            'label' => 'Trial days',
            'type' => 'int',
            'input' => 'number',
            'default' => 7,
            'rules' => ['required', 'integer', 'between:0,90'],
        ],
        'grace_period_days' => [
            'group' => 'subscriptions',
            'label' => 'Grace period (days)',
            'type' => 'int',
            'input' => 'number',
            'default' => 7,
            'rules' => ['required', 'integer', 'between:0,60'],
            'hint' => 'Window used for "expiring soon" admin reports.',
        ],
        'owner_notification_email' => [
            'group' => 'subscriptions',
            'label' => 'Owner notification email',
            'type' => 'string',
            'input' => 'email',
            'default' => 'thomcamus@gmail.com',
            'rules' => ['nullable', 'email', 'max:255'],
            'hint' => 'Receives new-subscription and subscription-event notifications.',
        ],

        // Content & Limits
        'trails_per_page' => [
            'group' => 'content',
            'label' => 'Trails per page',
            'type' => 'int',
            'input' => 'number',
            'default' => 9,
            'rules' => ['required', 'integer', 'between:1,100'],
        ],
        'businesses_per_page' => [
            'group' => 'content',
            'label' => 'Businesses per page (filtered)',
            'type' => 'int',
            'input' => 'number',
            'default' => 6,
            'rules' => ['required', 'integer', 'between:1,100'],
        ],
        'businesses_per_page_all' => [
            'group' => 'content',
            'label' => 'Businesses per page (all)',
            'type' => 'int',
            'input' => 'number',
            'default' => 12,
            'rules' => ['required', 'integer', 'between:1,100'],
        ],
        'events_per_page' => [
            'group' => 'content',
            'label' => 'Events per page',
            'type' => 'int',
            'input' => 'number',
            'default' => 8,
            'rules' => ['required', 'integer', 'between:1,100'],
        ],
        'admin_per_page' => [
            'group' => 'content',
            'label' => 'Admin list rows per page',
            'type' => 'int',
            'input' => 'number',
            'default' => 20,
            'rules' => ['required', 'integer', 'between:5,200'],
            'hint' => 'Applies to all admin list pages.',
        ],
        'featured_trail_count' => [
            'group' => 'content',
            'label' => 'Featured trails on home page',
            'type' => 'int',
            'input' => 'number',
            'default' => 3,
            'rules' => ['required', 'integer', 'between:1,12'],
        ],
        'photo_upload_max_kb' => [
            'group' => 'content',
            'label' => 'Visitor photo upload limit (KB)',
            'type' => 'int',
            'input' => 'number',
            'default' => 8192,
            'rules' => ['required', 'integer', 'between:256,102400'],
            'hint' => 'Maximum size for visitor-submitted trail photos.',
        ],
        'photo_daily_limit' => [
            'group' => 'content',
            'label' => 'Visitor photo submissions per day',
            'type' => 'int',
            'input' => 'number',
            'default' => 5,
            'rules' => ['required', 'integer', 'between:1,100'],
        ],

        // GPX & Routing
        'naismith_base_speed_kmh' => [
            'group' => 'routing',
            'label' => 'Hiking base speed (km/h)',
            'type' => 'float',
            'input' => 'number',
            'default' => 5.0,
            'rules' => ['required', 'numeric', 'between:1,15'],
            'hint' => 'Naismith\'s Rule: walking speed on flat terrain used for time estimates.',
        ],
        'naismith_climb_rate_m_per_hr' => [
            'group' => 'routing',
            'label' => 'Climb rate (m of gain per hour)',
            'type' => 'int',
            'input' => 'number',
            'default' => 600,
            'rules' => ['required', 'integer', 'between:100,2000'],
            'hint' => 'Naismith\'s Rule: one extra hour per this many meters of elevation gain.',
        ],
        'temp_gpx_max_age_hours' => [
            'group' => 'routing',
            'label' => 'Temp GPX cleanup age (hours)',
            'type' => 'int',
            'input' => 'number',
            'default' => 1,
            'rules' => ['required', 'integer', 'between:1,168'],
            'hint' => 'GPX preview uploads older than this are deleted by the hourly cleanup task.',
        ],

        // System
        'developer_email' => [
            'group' => 'system',
            'label' => 'Developer alert email',
            'type' => 'string',
            'input' => 'email',
            'default' => null,
            'rules' => ['nullable', 'email', 'max:255'],
            'hint' => 'Failure alerts from scheduled tasks (event scraper, subscription expiry, GPX cleanup) are emailed only to this address. Leave blank to disable alerts.',
        ],
        'events_scraper_base_url' => [
            'group' => 'system',
            'label' => 'Events scraper URL',
            'type' => 'string',
            'input' => 'url',
            'default' => 'https://smithersevents.com',
            'rules' => ['required', 'url', 'max:500'],
        ],
        'events_scrape_time' => [
            'group' => 'system',
            'label' => 'Events scrape time (UTC)',
            'type' => 'string',
            'input' => 'time',
            'default' => '02:00',
            'rules' => ['required', 'date_format:H:i'],
            'hint' => 'Picked up on the next scheduler run when using cron; a long-running schedule:work process needs a restart.',
        ],
    ],
];
