<?php

namespace App\Services;

class ProFeatureCatalog
{
    /**
     * The Free vs Pro feature comparison shown on the /pro page and the
     * upgrade modal. Kept in one place so both views stay in sync.
     *
     * @return array<int, array{label: string, sub: string, icon: string, free: array{available: bool, text: string}, pro: array{text: string, badge: ?string}}>
     */
    public static function all(): array
    {
        return [
            [
                'label' => 'Offline maps',
                'sub' => 'Access trail maps without service',
                'icon' => 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7',
                'free' => ['available' => false, 'text' => 'Not available'],
                'pro' => ['text' => 'Offline maps for any trail', 'badge' => 'Mobile app only'],
            ],
            [
                'label' => 'Points of interest',
                'sub' => 'Discover unique spots and hidden gems',
                'icon' => 'M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z',
                'free' => ['available' => false, 'text' => 'Not available'],
                'pro' => ['text' => 'Unlimited access', 'badge' => null],
            ],
            [
                'label' => 'Pro video content',
                'sub' => 'In-depth trail guides and adventure videos',
                'icon' => 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'free' => ['available' => false, 'text' => 'Not available'],
                'pro' => ['text' => 'Exclusive Pro videos', 'badge' => null],
            ],
            [
                'label' => 'GPX file download',
                'sub' => 'Download and export trail files',
                'icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4',
                'free' => ['available' => false, 'text' => 'Not available'],
                'pro' => ['text' => 'Download GPX files', 'badge' => null],
            ],
            [
                'label' => 'Support the adventure',
                'sub' => 'Your support helps keep our trails and content growing',
                'icon' => 'M11.049 2.927c.3-.921 1.604-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
                'free' => ['available' => true, 'text' => 'Community supported'],
                'pro' => ['text' => 'Support more trails & new features', 'badge' => null],
            ],
        ];
    }
}
