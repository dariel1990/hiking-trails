<?php

namespace Database\Seeders;

use App\Models\Town;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Search-facing copy for the town landing pages.
 *
 * Each town targets one primary query ("hiking trails <town> BC") and the
 * intro names real trails that exist on that town's page, so the copy stays
 * relevant to what a visitor actually finds when they scroll.
 *
 * Re-running this is safe: it only fills fields that are still empty, so
 * anything an admin has edited in /admin/towns is left alone. To regenerate a
 * field, clear it in admin first and run the seeder again.
 */
class TownSeoSeeder extends Seeder
{
    /**
     * Fields this seeder is allowed to populate.
     *
     * @var list<string>
     */
    private const FIELDS = ['seo_title', 'meta_description', 'intro', 'website_url', 'hero_image'];

    /**
     * Image extensions checked when looking for a town's hero photo.
     *
     * @var list<string>
     */
    private const HERO_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    public function run(): void
    {
        $filled = 0;
        $skipped = 0;
        $missingHeroes = [];

        foreach ($this->content() as $slug => $content) {
            $town = Town::firstWhere('slug', $slug);

            if (! $town) {
                $this->command?->warn("No town found for slug [{$slug}] - run TownSeeder first.");

                continue;
            }

            $content['hero_image'] = $this->findHeroImage($town);

            if ($content['hero_image'] === null) {
                $missingHeroes[] = $town->name;
            }

            $updates = [];

            foreach (self::FIELDS as $field) {
                if (blank($town->{$field}) && filled($content[$field] ?? null)) {
                    $updates[$field] = $content[$field];
                    $filled++;
                } elseif (filled($town->{$field})) {
                    $skipped++;
                }
            }

            if ($updates !== []) {
                $town->forceFill($updates)->save();
            }
        }

        $this->command?->info("Filled {$filled} empty field(s); left {$skipped} existing value(s) untouched.");

        if ($missingHeroes !== []) {
            $this->command?->warn(
                'No hero photo found for: '.implode(', ', $missingHeroes).'. '
                .'These pages fall back to a Mapbox terrain image. To use a photo, put it at '
                .'storage/app/public/towns/<slug>.jpg and re-run this seeder.'
            );
        }
    }

    /**
     * A town's hero photo, if one has actually been placed on disk.
     *
     * Photos cannot be seeded from code, so rather than storing a path that
     * would 404, this looks for a file at the conventional location and only
     * returns a path when the image is really there.
     */
    private function findHeroImage(Town $town): ?string
    {
        foreach (self::HERO_EXTENSIONS as $extension) {
            $path = "towns/{$town->slug}.{$extension}";

            if (Storage::disk('public')->exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Copy keyed by town slug.
     *
     * Titles stay under 60 characters and descriptions under 160 so neither is
     * truncated in results. Neither carries a trail count, which would go stale
     * as trails are added - the page falls back to live counts when a field is
     * left empty.
     *
     * @return array<string, array<string, string>>
     */
    private function content(): array
    {
        return [
            'smithers-bc' => [
                'seo_title' => 'Hiking Trails in Smithers, BC — Maps, Routes & Lakes',
                'meta_description' => 'Hiking trails around Smithers, BC — Glacier Gulch, Twin Falls, Crater Lake and the Hudson Bay Mountain alpine. Distance, elevation and trailhead maps.',
                'website_url' => 'https://www.smithers.ca',
                'intro' => <<<'TEXT'
                    Smithers sits in the middle of the Bulkley Valley, with Hudson Bay Mountain on one side and the Babine Range on the other. That geography is why the hiking here covers so much ground: you can walk a river flat in the morning and stand on a glacier moraine by mid-afternoon without driving more than twenty minutes from Main Street.

                    The best-known routes start close to town. Glacier Gulch climbs steeply to a viewpoint below the Kathlyn Glacier, and Twin Falls at its base is short enough for almost anyone. Round the Mountain traverses the alpine on Hudson Bay Mountain once the snow clears, usually from mid-July. For flatter ground, Crater Lake and the Perimeter Trail stay walkable through most of the season, and the Duthie network above town is the local mountain biking.

                    Several fishing lakes sit within reach, including Morin, Dennis, Hankin and Aldrich. Every route below lists its real distance and elevation gain, taken from recorded GPS tracks rather than estimates, so you can judge a trail before you drive to the trailhead.
                    TEXT,
            ],

            'telkwa-bc' => [
                'seo_title' => 'Hiking Trails in Telkwa, BC — Trails, Falls & Lakes',
                'meta_description' => 'Hiking trails around Telkwa, BC — Moose Mountain, Ganokwa Falls, the Bulkley River Trail and Tyhee Lake. Distance, elevation and trailhead maps.',
                'website_url' => 'https://www.telkwa.ca',
                'intro' => <<<'TEXT'
                    Telkwa sits where the Telkwa River runs into the Bulkley, about twelve kilometres south of Smithers on Highway 16. The village is small and so is the crowd on most of these trails, which is the main reason to walk here instead of closer to town.

                    Moose Mountain is the long day out, climbing to open ground with a view down the length of the valley. Ganokwa Falls and Canyon Creek Waterfall are both short walks to moving water. The Bulkley River Trail and the Aldermere loop stay flat and open earlier in spring, when higher routes are still holding snow, and Astlais Mountain is the summit push for anyone wanting elevation.

                    Tyhee Lake Provincial Park is a few minutes from the village and has the beach and campground most visitors are looking for. Round Lake and Llama Lake are quieter. Distances and elevation below come from recorded tracks, so a two-hour estimate means two hours.
                    TEXT,
            ],

            'houston-bc' => [
                'seo_title' => 'Hiking Trails in Houston, BC — Waterfalls & Lakes',
                'meta_description' => 'Hiking trails around Houston, BC — Aitken, Byman, Findlay and Dungate Falls, plus Buck Creek and nine fishing lakes. Distance, elevation and maps.',
                'website_url' => 'https://houston.ca',
                'intro' => <<<'TEXT'
                    Houston has more waterfall hikes than anywhere else along this stretch of Highway 16. Aitken, Byman, Findlay, Peacock, Dungate, Buck Creek and Equity Ice Falls are all within driving distance of town, and most are short enough to link two or three in a single day.

                    The self-guided waterfall tour connects the best of them in one route with driving directions between stops. Away from the falls, the Dunalter Lake Nature Walk and the Highschool Trail are easy and close in, while Sibola, Sweeney and Nanika Mountain are full days with real elevation behind them. Dina Lookout is the short climb with the biggest payoff for the effort.

                    Houston is a fishing town as much as a hiking one. Helen, Hidden, Klinger and Vallee Lakes are all mapped here, and the Morice River draws steelhead anglers from a long way off. Every route below lists distance, elevation gain and difficulty taken from recorded GPS data.
                    TEXT,
            ],

            'hazelton-bc' => [
                'seo_title' => 'Hiking Trails in Hazelton, BC — Ridges, Falls & Lakes',
                'meta_description' => 'Hiking trails around Hazelton, BC — Seaton Ridge, Kitwanga Mountain, New Hazelton Falls and Seeley Lake. Distance, elevation and trailhead maps.',
                'website_url' => 'https://newhazelton.ca',
                'intro' => <<<'TEXT'
                    The Hazeltons sit where the Bulkley runs into the Skeena, under the peaks of the Rocher de Boule range. It is steep country and the trails reflect it: several of the best routes here gain serious elevation over a short distance.

                    Seaton Ridge and Skilokis Ridge are the ridge walks people come for. Kitwanga Mountain is a shorter climb with a wide view over the Skeena Valley. Closer in, the New Hazelton Falls and Lookout Trail, the Cart Trail and the walk to Hospital Lake can all be started on foot from town, and Blue Lakes is an easy afternoon.

                    This is Gitxsan territory, and 'Ksan Historical Village in Old Hazelton is worth building into the same trip. Kitwancool Lake, Ross Lake and Seeley Lake Provincial Park cover the fishing and swimming. Distances and elevation below are taken from recorded GPS tracks.
                    TEXT,
            ],

            'burns-lake-bc' => [
                'seo_title' => 'Hiking Trails in Burns Lake, BC — Boer Mountain & Lakes',
                'meta_description' => 'Trails around Burns Lake, BC — the Boer Mountain network, Kager Lake, Eagle Creek Opal Beds and Francois Lake. Distance, elevation and trailhead maps.',
                'website_url' => 'https://www.burnslake.ca',
                'intro' => <<<'TEXT'
                    Burns Lake is best known for what happens on Boer Mountain. The network above Kager Lake — Full Boar, Porcupine Loop, Firecrew, Razorback and Charlotte's Web among them — was built for mountain bikes and is some of the better-known riding in northern British Columbia, but much of it walks well too.

                    For hiking specifically, the Burns Lake Provincial Park Loop and the Kager Lake trails are short and close to town. Eagle Creek Opal Beds is the unusual one, ending at a bed of opal-bearing rock. Guyishton Lake and Long Lake are longer and quieter, and the Walkadab Trail is worth the drive east.

                    This is the Lakes District and the water is the point. Francois Lake is one of the largest natural lakes in the province, with a free ferry crossing it through the day. Every route below lists distance and elevation gain from recorded GPS tracks.
                    TEXT,
            ],

            'stewart-bc' => [
                'seo_title' => 'Hiking Trails in Stewart, BC — Glaciers & Coast Routes',
                'meta_description' => 'Hiking trails around Stewart, BC — American Creek, Titan Trail, Ore Mountain and the estuary boardwalk, near Bear and Salmon Glaciers. Maps and elevation.',
                'website_url' => 'https://www.districtofstewart.com',
                'intro' => <<<'TEXT'
                    Stewart sits at the head of the Portland Canal at the end of Highway 37A, and it does not look like anywhere else on this site. The mountains drop straight to salt water and the snowfall here is among the heaviest in Canada.

                    The trail list is shorter than other towns because the terrain is severe, but the walks are good. The Boardwalk on the Estuary is flat, easy and the best place in town to watch for birds and bears. American Creek, Titan Trail and Ore Mountain climb into old mining ground above the valley. Rainey Creek and the Sluice Box are shorter, and Meziadin and Clements Lake are both paddles rather than hikes.

                    Most people come for the ice. Bear Glacier is visible from the highway on the drive in, and the Salmon Glacier road past Hyder reaches one of the largest road-accessible glaciers anywhere. Fish Creek, just across the Alaska border, is a bear-viewing platform run by the US Forest Service. Distances below come from recorded tracks.
                    TEXT,
            ],
        ];
    }
}
