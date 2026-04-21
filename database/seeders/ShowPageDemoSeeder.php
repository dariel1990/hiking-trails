<?php

namespace Database\Seeders;

use App\Models\SeasonalTrailData;
use App\Models\Trail;
use App\Models\TrailFeature;
use Illuminate\Database\Seeder;

class ShowPageDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedHikingTrail();
        $this->seedFishingLake();
    }

    private function seedHikingTrail(): void
    {
        $trail = Trail::create([
            'name' => 'Hudson Bay Mountain Summit Trail',
            'location_type' => 'trail',
            'geometry_type' => 'linestring',
            'location' => 'Smithers, British Columbia',
            'status' => 'active',
            'is_featured' => true,
            'difficulty_level' => 4,
            'trail_type' => 'out-and-back',
            'distance_km' => 14.2,
            'elevation_gain_m' => 1380,
            'estimated_time_hours' => 6.5,
            'best_seasons' => ['Summer', 'Fall'],
            'data_source' => 'manual',
            'description' => '<p>The <strong>Hudson Bay Mountain Summit Trail</strong> is one of the most rewarding hikes in the Bulkley Valley, offering breathtaking panoramic views of Smithers, the Bulkley River, and the surrounding mountain ranges on a clear day.</p><p>The trail begins at the Ski Hill base area and winds steadily upward through dense subalpine forest before opening into stunning alpine meadows carpeted with wildflowers in mid-summer. The final push to the summit involves some light scrambling over rocky terrain, but the effort is well worth the reward.</p><p>Expect the upper alpine section to be <strong>snow-covered well into June</strong> and again by October. Bears are frequently spotted in the berry patches along the mid-section — carry bear spray and make noise on the trail. The summit sits at approximately <strong>2,621 metres</strong> above sea level.</p>',
            'directions' => '<p>From downtown Smithers, head west on <strong>Highway 16</strong> for 1.5 km, then turn south on Hudson Bay Mountain Road. Follow the road for 4 km to the ski hill base parking lot.</p><p>The trailhead is marked at the east end of the parking area, just past the ski patrol cabin.</p>',
            'parking_info' => '<p><strong>Free parking</strong> available at the ski hill base lot (capacity ~40 vehicles). Overflow parking on the shoulder of Hudson Bay Mountain Road.</p><ul><li>No facilities at the trailhead</li><li>Nearest washrooms in Smithers</li></ul>',
            'safety_notes' => '<p>This is an <strong>alpine trail</strong> — weather can change rapidly. Always bring extra layers, rain gear, and sufficient water (no reliable water sources above treeline).</p><ul><li>Snow lingers on the upper trail well into June</li><li>Bears are active throughout summer and fall — carry bear spray and travel in groups</li><li>Cell service is limited above treeline</li><li>Register your hike with a friend or family member before departing</li></ul>',
            'start_coordinates' => [54.8012, -127.2847],
            'end_coordinates' => [54.7758, -127.2591],
            'route_coordinates' => [
                [54.8012, -127.2847],
                [54.7992, -127.2820],
                [54.7968, -127.2790],
                [54.7940, -127.2760],
                [54.7910, -127.2730],
                [54.7880, -127.2700],
                [54.7850, -127.2672],
                [54.7820, -127.2648],
                [54.7795, -127.2620],
                [54.7758, -127.2591],
            ],
        ]);

        TrailFeature::create([
            'trail_id' => $trail->id,
            'feature_type' => 'viewpoint',
            'name' => 'Alpine Meadow Viewpoint',
            'description' => 'A stunning open meadow at approximately 1,900 m elevation, bursting with Indian paintbrush and lupine in late July. Clear-day views extend east to the Babine Mountains and west toward the Coast Range. A natural flat rock shelf makes an ideal rest and lunch spot.',
            'coordinates' => ['lat' => 54.7870, 'lng' => -127.2688],
            'icon' => '👁️',
            'color' => '#8B5CF6',
        ]);

        TrailFeature::create([
            'trail_id' => $trail->id,
            'feature_type' => 'summit',
            'name' => 'Summit Cairn',
            'description' => 'The true summit of Hudson Bay Mountain at 2,621 m. A historic stone cairn marks the top. On clear days you can see the entire Bulkley Valley floor, Smithers townsite, Kathlyn Lake, and on exceptionally clear days, the distant peaks of Tweedsmuir Park to the southwest.',
            'coordinates' => ['lat' => 54.7758, 'lng' => -127.2591],
            'icon' => '⛰️',
            'color' => '#10B981',
        ]);

        foreach ($this->hikingSeasons() as $season) {
            SeasonalTrailData::create(array_merge(['trail_id' => $trail->id], $season));
        }
    }

    private function seedFishingLake(): void
    {
        $lake = Trail::create([
            'name' => 'Kathlyn Lake',
            'location_type' => 'fishing_lake',
            'geometry_type' => 'point',
            'trail_type' => 'point',
            'location' => 'Smithers, British Columbia',
            'status' => 'active',
            'is_featured' => true,
            'data_source' => 'manual',
            'description' => '<p><strong>Kathlyn Lake</strong> is a beloved local fishing and recreation destination sitting at the base of Hudson Bay Mountain, just minutes from downtown Smithers. With the glacier-capped mountain reflecting off its calm waters and the surrounding wetlands teeming with birdlife, Kathlyn Lake offers a peaceful escape that rewards both anglers and nature observers alike.</p><p>The lake is managed as a community fishery with <strong>regular stocking of rainbow trout</strong>, making it an ideal destination for families and beginner anglers. A well-maintained gravel path circles the lake, passing through cattail marshes and willow thickets frequented by moose, beaver, and a wide variety of migratory and resident waterfowl.</p><p>Fishing from the shore is productive at both the inlet creek on the north end and the dock area near the day-use park. <strong>Float tubes and non-motorized boats are permitted.</strong> The lake typically opens to ice-free fishing in late April or early May.</p>',
            'fishing_location' => 'Kathlyn Lake Day-Use Area, Hudson Bay Mountain Road, Smithers, BC',
            'fishing_distance_from_town' => '6 km west of Smithers town centre via Highway 16 and Hudson Bay Mountain Road',
            'fish_species' => ['Rainbow Trout', 'Dolly Varden', 'Mountain Whitefish'],
            'best_fishing_time' => 'Early morning (6–9 AM) and evening (5–8 PM). Overcast days tend to produce better surface action.',
            'best_fishing_season' => 'spring',
            'best_seasons' => ['Spring', 'Summer'],
            'directions' => '<p>From Smithers, drive west on <strong>Highway 16</strong> for approximately 3 km. Turn south (left) onto Hudson Bay Mountain Road.</p><p>Follow for 2.5 km — <strong>Kathlyn Lake Day-Use Area</strong> will be on your right, clearly signed. A second access point is available 500 m further along the road on the left side.</p>',
            'parking_info' => '<p>Paved day-use parking lot with space for approximately <strong>25 vehicles</strong>, including 2 accessible stalls.</p><ul><li>Picnic tables and pit toilets available</li><li>Small boat launch ramp at the main day-use area</li><li>No overnight camping permitted</li></ul>',
            'safety_notes' => '<p>Please observe all safety guidelines when using the lake.</p><ul><li><strong>No motorized boats</strong> permitted</li><li>Life jackets recommended for float tube and small boat users</li><li>Ice conditions vary — do not venture onto ice without verifying thickness with local conservation officers</li><li>Moose with calves frequent the north shoreline in spring — give them wide berth</li></ul>',
            'start_coordinates' => [54.8156, -127.2483],
            'end_coordinates' => [54.8156, -127.2483],
        ]);

        foreach ($this->fishingSeasons() as $season) {
            SeasonalTrailData::create(array_merge(['trail_id' => $lake->id], $season));
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function hikingSeasons(): array
    {
        return [
            [
                'season' => 'spring',
                'trail_conditions' => 'Snow-covered above 1,500 m well into May and June. Lower forest section may be muddy. Creek crossings running high from snowmelt.',
                'seasonal_notes' => 'Not recommended for inexperienced hikers. Microspikes or snowshoes may be required. Wildlife activity high as animals emerge from winter ranges.',
                'accessibility_changes' => 'Upper alpine section inaccessible without snowshoe or ski equipment until late May.',
                'seasonal_features' => 'Waterfalls at peak flow. Ptarmigan still in winter white plumage near treeline.',
                'recommended' => false,
            ],
            [
                'season' => 'summer',
                'trail_conditions' => 'Excellent. Dry, firm trail throughout. Alpine meadows in full bloom from mid-July to mid-August.',
                'seasonal_notes' => 'Best time to visit. Start early to avoid afternoon thunderstorms common in August. Berry patches attract bears — carry spray.',
                'accessibility_changes' => 'Fully accessible. All stream crossings safe.',
                'seasonal_features' => 'Wildflower bloom (July–August), marmot sightings near summit, hummingbirds in meadows.',
                'recommended' => true,
            ],
            [
                'season' => 'fall',
                'trail_conditions' => 'Good through September. First snowfall on summit typically in late September. Lower trail beautiful with autumn colour.',
                'seasonal_notes' => 'Bring microspikes from early October onward. Excellent berry picking in lower section. High bear activity near harvest.',
                'accessibility_changes' => 'Summit may require microspikes after mid-October.',
                'seasonal_features' => 'Spectacular fall foliage in birch and aspen zones. Grouse and ptarmigan hunting season begins.',
                'recommended' => true,
            ],
            [
                'season' => 'winter',
                'trail_conditions' => 'Deep snow from November through April. Lower section used for snowshoeing. Upper alpine is serious backcountry terrain with avalanche risk.',
                'seasonal_notes' => 'Backcountry ski and snowshoe use only. Avalanche training and equipment required above treeline.',
                'accessibility_changes' => 'Hiking trail inaccessible. Ski hill operates and provides lifts to mid-mountain.',
                'seasonal_features' => 'Backcountry skiing and snowshoeing. Winter wildlife tracking.',
                'recommended' => false,
            ],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function fishingSeasons(): array
    {
        return [
            [
                'season' => 'spring',
                'trail_conditions' => 'Ice-out typically late April to early May. Post-ice-out fishing is excellent as trout are aggressive and near the surface.',
                'seasonal_notes' => 'Best season for fishing. Bring layers — mornings are cold. Watch for moose cows with newborn calves along the north shore.',
                'accessibility_changes' => 'Lakeshore path may be muddy until late May. Boat launch accessible once ice is fully out.',
                'seasonal_features' => 'Post ice-out trout feeding frenzy. Waterfowl migration peak — excellent birding. Moose calves.',
                'recommended' => true,
            ],
            [
                'season' => 'summer',
                'trail_conditions' => 'Excellent. All facilities open. Warm water temperatures push trout deeper by mid-July — fish early morning or evening.',
                'seasonal_notes' => 'Popular family destination. Lake can be busy on weekends and holidays. Fishing best early and late in the day.',
                'accessibility_changes' => 'Fully accessible. All facilities operational.',
                'seasonal_features' => 'Swimming area (informal, no lifeguard), picnic facilities, birdwatching, beaver activity at dusk.',
                'recommended' => true,
            ],
            [
                'season' => 'fall',
                'trail_conditions' => 'Good through October. Cooler temperatures improve fishing as trout move to shallower water. Stunning mountain reflections on calm fall mornings.',
                'seasonal_notes' => 'Underrated fishing season. Less crowded than summer. First frost typically in late September.',
                'accessibility_changes' => 'Day-use facilities close after Thanksgiving weekend. Pit toilets remain open.',
                'seasonal_features' => 'Fall foliage surrounding lake. Waterfowl migration. Trout feeding actively before winter.',
                'recommended' => true,
            ],
            [
                'season' => 'winter',
                'trail_conditions' => 'Lake typically freezes December through March. Ice fishing possible once ice reaches safe thickness (minimum 10 cm for foot travel).',
                'seasonal_notes' => 'Contact BC Conservation Officer Service to confirm ice conditions before venturing out. Dress in layers — windchill on open ice can be extreme.',
                'accessibility_changes' => 'Vehicle access to day-use area not maintained in winter. Park on shoulder of Hudson Bay Mountain Road.',
                'seasonal_features' => 'Ice fishing for rainbow trout. Winter birdwatching. Occasional eagle sightings.',
                'recommended' => false,
            ],
        ];
    }
}
