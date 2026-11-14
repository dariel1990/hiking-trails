<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventMedia;
use App\Models\Town;
use App\Services\ImageThumbnailService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * Sample events with complete details, for previewing the Events pages:
 * a Christmas parade with a drawn route and pinned stops, an activity map,
 * a multi-day route event, single-pin events and a simple event with no map,
 * each with a gallery of placeholder photos (and some YouTube videos).
 *
 * Not part of DatabaseSeeder. Run it on purpose:
 *   php artisan db:seed --class=EventSeeder
 *
 * Re-running it updates the same events (matched by slug).
 */
class EventSeeder extends Seeder
{
    /**
     * Downtown Smithers along Main Street, south-west (Highway 16) to north-east (Railway Ave).
     *
     * @var array<string, array{0: float, 1: float}> [lat, lng]
     */
    private const SPOTS = [
        'hwy16_main' => [54.7789, -127.1742],
        'main_1100' => [54.7793, -127.1729],
        'main_1150' => [54.7797, -127.1715],
        'main_1190' => [54.7802, -127.1706],
        'main_1230' => [54.7805, -127.1697],
        'bovill_square' => [54.7810, -127.1690],
        'main_1300' => [54.7817, -127.1677],
        'railway_main' => [54.7822, -127.1667],
        'broadway_main' => [54.7798, -127.1736],
        'central_park' => [54.7826, -127.1683],
    ];

    public function run(): void
    {
        $townId = Town::query()->where('slug', 'smithers-bc')->value('id');

        foreach ($this->events() as $data) {
            $stops = $data['stops'] ?? [];
            $palette = $data['palette'];
            $photos = $data['photos'] ?? [];
            $videos = $data['videos'] ?? [];
            unset($data['stops'], $data['palette'], $data['photos'], $data['videos']);

            $event = Event::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    ...$data,
                    'town_id' => $townId,
                    'cover_image' => $this->makeCover($data['slug'], $palette),
                    'source_id' => null,
                    'is_active' => true,
                ],
            );

            $event->stops()->delete();
            foreach (array_values($stops) as $order => $stop) {
                $event->stops()->create([...$stop, 'stop_order' => $order]);
            }

            $this->seedGallery($event, $palette, $photos, $videos);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function events(): array
    {
        return [
            [
                'title' => 'Smithers Christmas Truck Parade',
                'slug' => config('app.christmas_parade_slug'),
                'tagline' => 'Dozens of trucks and floats lit up with thousands of lights roll down Main Street.',
                'description' => "Bundle up and line Main Street for the brightest night of the year! Local businesses, farms and families decorate their trucks, tractors and floats with thousands of Christmas lights and drive the parade route through downtown Smithers.\n\nBring a non-perishable food item for the food bank drop-off, grab a hot chocolate, and stay to the end — Santa rides in the very last truck and stops at Central Park for photos.\n\nMain Street is closed to traffic from 5:00 PM. Please keep children back from the curb while the trucks pass.",
                'event_date' => $this->next(12, 5),
                'event_time' => '17:30',
                'end_time' => '19:00',
                'venue' => 'Main Street',
                'location' => 'Downtown Smithers, BC',
                'organizer' => 'Smithers Christmas Parade Committee',
                'category' => 'Holiday',
                'external_url' => 'https://xploresmithers.com/events-in-smithers',
                'theme' => Event::THEME_CHRISTMAS,
                'icon' => '🚚',
                'is_featured' => true,
                'route_coordinates' => $this->route(['hwy16_main', 'main_1100', 'main_1150', 'main_1190', 'main_1230', 'bovill_square', 'main_1300', 'railway_main', 'central_park']),
                'route_distance_km' => 0.9,
                'palette' => ['#0F1B2D', '#1E5B3A', '#B3202A'],
                'photos' => ['Trucks lined up on Highway 16 before the start', 'The lead truck turning onto Main Street', 'Crowds at Bovill Square', 'A logging truck covered in lights', 'Food bank volunteers collecting donations', 'Santa waving from the last truck'],
                'videos' => [['https://youtu.be/UAg9ijcBJWY', 'Highlights from last year'], ['https://www.youtube.com/watch?v=mvczV0VFUK4', 'Driving the route at night']],
                'stops' => [
                    $this->pin('hwy16_main', 'Parade Start', '🚦', '5:30 PM', 'Trucks line up on Highway 16 and turn onto Main Street.', 'Main St & Highway 16'),
                    $this->pin('main_1150', 'Food Bank Drop-off', '🥫', '5:00 – 7:00 PM', 'Volunteers collect non-perishable food and cash donations for the Smithers Food Bank.', 'In front of Mills, 1156 Main St'),
                    $this->pin('main_1230', 'Hot Chocolate Station', '☕', '5:00 – 7:00 PM', 'Free hot chocolate and candy canes while supplies last.', '1230 Main St'),
                    $this->pin('bovill_square', 'Best Viewing: Bovill Square', '📸', '5:45 PM', 'The widest stretch of the route, with room for strollers and wheelchairs. Trucks slow down here for photos.', 'Main St & 1st Ave'),
                    $this->pin('railway_main', 'Parade Finish', '🏁', '6:30 PM', 'The last truck turns at Railway Avenue.', 'Main St & Railway Ave'),
                    $this->pin('central_park', 'Photos with Santa', '🎅', '6:30 – 7:30 PM', 'Santa arrives on the last truck and sets up in Central Park for family photos.', 'Central Park'),
                    $this->roaming('Candy Cane Elves', '🍬', 'Elves walk the route handing out candy canes before the parade starts.'),
                ],
            ],
            [
                'title' => 'Christmas in the Valley',
                'slug' => 'christmas-in-the-valley',
                'tagline' => 'A weekend of wagon rides, carollers, crafts and Santa all over downtown.',
                'description' => "Downtown Smithers turns into a winter village for the weekend. Hop on a horse-drawn wagon, shop the craft market, warm up by the fire pit and visit Santa's workshop.\n\nMost activities are free. Tap an activity on the map to see where and when it happens.",
                'event_date' => $this->next(12, 12),
                'end_date' => $this->next(12, 13),
                'event_time' => '12:00',
                'end_time' => '16:00',
                'venue' => 'Downtown Smithers',
                'location' => 'Main Street, Smithers, BC',
                'organizer' => 'Downtown Smithers Business Association',
                'category' => 'Holiday',
                'external_url' => 'https://xploresmithers.com/events-in-smithers',
                'theme' => Event::THEME_CHRISTMAS,
                'icon' => '🎄',
                'palette' => ['#13283f', '#7f1720', '#D4A017'],
                'photos' => ['Wagon rides down Main Street', 'Carollers outside Bugwood Beans', "Santa's workshop in Bovill Square", 'Handmade ornaments at the craft market'],
                'videos' => [['https://www.youtube.com/watch?v=zwwOznxS884', 'A walk through the weekend']],
                'stops' => [
                    $this->pin('main_1190', 'Horse-drawn Wagon Rides', '🐴', '12:00 – 4:00 PM', 'Free wagon rides around downtown. Rides leave every 15 minutes.', 'Pick-up at 1191 Main St'),
                    $this->pin('bovill_square', "Santa's Workshop", '🎅', '1:00 – 3:00 PM', 'Write a letter to Santa and get your photo taken.', 'Bovill Square'),
                    $this->pin('main_1300', 'Craft Market', '🛍️', '12:00 – 4:00 PM', 'Over 30 local makers selling gifts, baking and art.', 'Main St & Railway Ave'),
                    $this->pin('broadway_main', 'Kids Craft Corner', '🎨', '12:00 – 3:00 PM', 'Make ornaments and cards to take home.', 'Broadway Ave & Main St'),
                    $this->pin('main_1230', 'Fire Pit & Marshmallows', '🔥', '12:00 – 4:00 PM', 'Roast a marshmallow and warm up.', 'Outside Kitchen Works'),
                    $this->roaming('Carollers', '🎶', 'Carollers stroll Main Street singing holiday classics.'),
                    $this->roaming('Popcorn Cart', '🍿', 'Look for the red cart for free popcorn.'),
                ],
            ],
            [
                'title' => 'Winter Lights Walk',
                'slug' => 'winter-lights-walk',
                'tagline' => 'A self-guided evening walk past the best-lit storefronts downtown.',
                'description' => "Follow the route on the map any evening from late November into the new year. Storefronts and Bovill Square are decorated with lights, and the walk takes about 20 minutes at an easy pace.\n\nVote for your favourite window at the Bovill Square ballot box.",
                'event_date' => $this->next(11, 28),
                'end_date' => $this->next(11, 28)->copy()->addDays(36),
                'event_time' => '16:30',
                'end_time' => '22:00',
                'venue' => 'Downtown Smithers',
                'location' => 'Main Street, Smithers, BC',
                'organizer' => 'Town of Smithers',
                'category' => 'Community',
                'theme' => Event::THEME_STANDARD,
                'icon' => '✨',
                'route_coordinates' => $this->route(['broadway_main', 'main_1150', 'main_1190', 'main_1230', 'bovill_square', 'main_1300']),
                'route_distance_km' => 0.5,
                'palette' => ['#1d4644', '#2C5F5D', '#e8a33d'],
                'photos' => ['Storefront lights on Main Street', 'The Bovill Square tree', 'Last year\'s winning window'],
                'stops' => [
                    $this->pin('broadway_main', 'Start: Broadway & Main', '🚶', null, 'Begin at the corner and head up Main Street.', null),
                    $this->pin('bovill_square', 'Ballot Box', '🗳️', null, "Vote for your favourite window. Winners are announced on New Year's Day.", 'Bovill Square'),
                    $this->pin('main_1300', 'Finish', '🏁', null, 'The walk ends near Railway Avenue.', null),
                ],
            ],
            [
                'title' => 'Holiday Farmers\' Market',
                'slug' => 'holiday-farmers-market',
                'tagline' => 'Local food, baking and handmade gifts under one roof.',
                'description' => "The Bulkley Valley Farmers' Market moves indoors for its holiday edition. Stock up on local meat, preserves, baking and handmade gifts.\n\nCash and cards accepted at most tables.",
                'event_date' => $this->next(11, 29),
                'event_time' => '10:00',
                'end_time' => '14:00',
                'venue' => 'Smithers Curling Club',
                'location' => '3953 Railway Ave, Smithers, BC',
                'organizer' => "Bulkley Valley Farmers' Market",
                'category' => 'Market',
                'external_url' => 'https://xploresmithers.com/events-in-smithers',
                'theme' => Event::THEME_STANDARD,
                'icon' => '🛍️',
                'palette' => ['#3f2a14', '#a8641f', '#e8a33d'],
                'photos' => ['Fresh baking', 'Local preserves', 'Handmade gifts'],
            ],
            [
                'title' => 'Hudson Bay Mountain Opening Day',
                'slug' => 'hudson-bay-mountain-opening-day',
                'tagline' => 'First chair of the season, with live music and a pancake breakfast.',
                'description' => "Celebrate the start of ski season on Hudson Bay Mountain. The first chair goes up at 9:00 AM, followed by a pancake breakfast in the lodge and live music on the deck all afternoon.\n\nDress warmly and check road conditions before driving up.",
                'event_date' => $this->next(12, 19),
                'event_time' => '09:00',
                'end_time' => '16:00',
                'venue' => 'Hudson Bay Mountain Resort',
                'location' => 'Hudson Bay Mountain Rd, Smithers, BC',
                'organizer' => 'Hudson Bay Mountain Resort',
                'category' => 'Outdoors',
                'theme' => Event::THEME_STANDARD,
                'icon' => '⛷️',
                'palette' => ['#0f2a44', '#3b6e9e', '#e5eef7'],
                'photos' => ['First chair of the season', 'Fresh powder on the upper runs'],
                'videos' => [['https://www.youtube.com/watch?v=avphbNbJ8NY', null]],
                'stops' => [
                    ['latitude' => 54.8015, 'longitude' => -127.2864, 'is_roaming' => false, 'stop_label' => 'Main Lodge', 'icon' => '🏔️', 'time_label' => '9:00 AM – 4:00 PM', 'description' => 'Pancake breakfast 8:30 – 10:30 AM, live music from noon.', 'notes' => 'Parking fills up early; carpool if you can.'],
                ],
            ],
            [
                'title' => "New Year's Eve Fireworks",
                'slug' => 'new-years-eve-fireworks',
                'tagline' => 'Ring in the new year with fireworks over downtown.',
                'description' => "Join neighbours in Bovill Square for live music and a fireworks show at 9:00 PM — early enough for the whole family.\n\nHot drinks available by donation.",
                'event_date' => $this->next(12, 31),
                'event_time' => '20:00',
                'end_time' => '21:30',
                'venue' => 'Bovill Square',
                'location' => 'Main St & 1st Ave, Smithers, BC',
                'organizer' => 'Town of Smithers',
                'category' => 'Community',
                'theme' => Event::THEME_STANDARD,
                'icon' => '🎆',
                'palette' => ['#111827', '#3b1d5e', '#e8a33d'],
                'photos' => ['Fireworks over Bovill Square', 'Families watching from Main Street'],
                'stops' => [
                    $this->pin('bovill_square', 'Fireworks Viewing', '🎆', '9:00 PM', 'Fireworks are launched from behind the square.', 'Bovill Square'),
                ],
            ],
        ];
    }

    /**
     * The next date with this month and day (this year, or next year if it has passed).
     */
    private function next(int $month, int $day): Carbon
    {
        $date = Carbon::create(now()->year, $month, $day)->startOfDay();

        return $date->isPast() && ! $date->isToday() ? $date->addYear() : $date;
    }

    /**
     * @param  list<string>  $spots
     * @return list<array{0: float, 1: float}> [[lng, lat], ...] as the map expects
     */
    private function route(array $spots): array
    {
        return array_map(fn (string $spot) => [self::SPOTS[$spot][1], self::SPOTS[$spot][0]], $spots);
    }

    /**
     * @return array<string, mixed>
     */
    private function pin(string $spot, string $label, string $icon, ?string $time, string $description, ?string $notes): array
    {
        return [
            'latitude' => self::SPOTS[$spot][0],
            'longitude' => self::SPOTS[$spot][1],
            'is_roaming' => false,
            'stop_label' => $label,
            'icon' => $icon,
            'time_label' => $time,
            'description' => $description,
            'notes' => $notes,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function roaming(string $label, string $icon, string $description): array
    {
        return [
            'latitude' => null,
            'longitude' => null,
            'is_roaming' => true,
            'stop_label' => $label,
            'icon' => $icon,
            'description' => $description,
        ];
    }

    /**
     * Replace the event's gallery with placeholder photos and the given videos.
     *
     * @param  array{0: string, 1: string, 2: string}  $palette
     * @param  list<string>  $photoCaptions
     * @param  list<array{0: string, 1: string|null}>  $videos  [url, caption]
     */
    private function seedGallery(Event $event, array $palette, array $photoCaptions, array $videos): void
    {
        $event->media()->get()->each->delete();
        $disk = Storage::disk('public');
        $order = 0;

        foreach ($photoCaptions as $i => $caption) {
            $tempPath = 'events/seed/tmp-'.$event->slug.'-'.$i.'.webp';
            $disk->put($tempPath, $this->placeholderImage($event->slug.'-'.$i, $this->shiftPalette($palette, $i)));
            $processed = app(ImageThumbnailService::class)->process($tempPath, 'events/'.$event->id.'/gallery');
            $disk->delete($tempPath);

            $event->media()->create([
                'media_type' => EventMedia::TYPE_PHOTO,
                'file_path' => $processed['path'],
                'thumbnail_path' => $processed['thumbnail_path'],
                'caption' => $caption,
                'sort_order' => $order++,
            ]);
        }

        foreach ($videos as [$url, $caption]) {
            $event->media()->create([
                'media_type' => EventMedia::TYPE_VIDEO,
                'url' => $url,
                'video_provider' => EventMedia::parseVideo($url)['provider'],
                'caption' => $caption,
                'sort_order' => $order++,
            ]);
        }
    }

    /**
     * Rotate the palette so each gallery photo looks a little different.
     *
     * @param  array{0: string, 1: string, 2: string}  $palette
     * @return array{0: string, 1: string, 2: string}
     */
    private function shiftPalette(array $palette, int $step): array
    {
        return match ($step % 3) {
            0 => $palette,
            1 => [$palette[1], $palette[0], $palette[2]],
            default => [$palette[0], $palette[2], $palette[1]],
        };
    }

    /**
     * A placeholder cover stored on the public disk.
     *
     * @param  array{0: string, 1: string, 2: string}  $palette  Hex colours
     */
    private function makeCover(string $slug, array $palette): string
    {
        $path = 'events/seed/'.$slug.'.webp';
        Storage::disk('public')->put($path, $this->placeholderImage($slug, $palette));

        return $path;
    }

    /**
     * A diagonal three-colour gradient with soft light dots, as WebP bytes.
     *
     * @param  array{0: string, 1: string, 2: string}  $palette  Hex colours
     */
    private function placeholderImage(string $seed, array $palette): string
    {
        $width = 1600;
        $height = 900;
        [$from, $to, $glow] = array_map(fn (string $hex) => sscanf($hex, '#%02x%02x%02x'), $palette);

        $image = (new ImageManager(new Driver))->create($width, $height);

        for ($y = 0; $y < $height; $y += 6) {
            $t = $y / $height;
            $color = sprintf('rgb(%d, %d, %d)',
                (int) ($from[0] + ($to[0] - $from[0]) * $t),
                (int) ($from[1] + ($to[1] - $from[1]) * $t),
                (int) ($from[2] + ($to[2] - $from[2]) * $t));
            $image->drawRectangle(0, $y, fn ($rectangle) => $rectangle->size($width, 6)->background($color));
        }

        mt_srand(crc32($seed));
        for ($i = 0; $i < 70; $i++) {
            $radius = mt_rand(3, 22);
            $alpha = mt_rand(15, 55) / 100;
            $image->drawCircle(mt_rand(0, $width), mt_rand(0, $height), fn ($circle) => $circle
                ->radius($radius)
                ->background(sprintf('rgba(%d, %d, %d, %.2f)', $glow[0], $glow[1], $glow[2], $alpha)));
        }
        mt_srand();

        return (string) $image->toWebp(80);
    }
}
