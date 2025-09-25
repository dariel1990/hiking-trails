<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Trail;

class EnhancedTrailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trails = [
            [
                'name' => 'Angels Landing Trail',
                'description' => 'One of the most famous and thrilling hikes in America, featuring chains for the final ascent to spectacular panoramic views of Zion Canyon. This challenging trail requires permits and is not for those afraid of heights.',
                'location' => 'Springdale, Utah',
                'difficulty_level' => 5.0,
                'distance_km' => 4.1,
                'elevation_gain_m' => 150,
                'estimated_time_hours' => 3.5,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [37.26910, -112.94705],
                'end_coordinates' => [37.26904, -112.94765],
                'route_coordinates' => [
                    [37.26910, -112.94705], // Grotto trailhead
                    [37.26850, -112.94750], // River crossing
                    [37.26800, -112.94780], // West Rim trail junction
                    [37.26950, -112.94850], // Scout Lookout
                    [37.26904, -112.94765]  // Angels Landing summit
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Summer', 'Fall'],
                'directions' => 'Take the Zion Canyon Shuttle to the Grotto stop. The trailhead is across the road from the shuttle stop.',
                'parking_info' => 'No parking at trailhead. Must use Zion Canyon Shuttle from Visitor Center.',
                'safety_notes' => 'PERMIT REQUIRED. Chains section is extremely dangerous in wet conditions. Not recommended for children or those with fear of heights.',
                'is_featured' => true,
            ],
            [
                'name' => 'Emerald Lake Trail',
                'description' => 'Beautiful alpine lake trail in Rocky Mountain National Park featuring three pristine lakes with stunning mountain reflections and cascading waterfalls.',
                'location' => 'Estes Park, Colorado',
                'difficulty_level' => 3.0,
                'distance_km' => 3.1,
                'elevation_gain_m' => 66,
                'estimated_time_hours' => 2.0,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [40.31089, -105.64450],
                'end_coordinates' => [40.31150, -105.64680],
                'route_coordinates' => [
                    [40.31089, -105.64450], // Bear Lake trailhead
                    [40.31100, -105.64500], // Nymph Lake
                    [40.31120, -105.64590], // Dream Lake
                    [40.31150, -105.64680]  // Emerald Lake
                ],
                'status' => 'active',
                'best_seasons' => ['Summer', 'Fall'],
                'directions' => 'From Estes Park, take Bear Lake Road to Bear Lake. Parking fills early - consider shuttle.',
                'parking_info' => 'Limited parking at Bear Lake. Shuttle available from park & ride during peak season.',
                'safety_notes' => 'Trail can be icy in winter and early spring. Wildlife present - follow food storage regulations.',
                'is_featured' => true,
            ],
            [
                'name' => 'Delicate Arch Trail',
                'description' => 'Iconic trail to Utah\'s most famous natural arch, featured on the state license plate. The trail crosses slickrock terrain to one of the world\'s most recognizable geological formations.',
                'location' => 'Moab, Utah',
                'difficulty_level' => 3.0,
                'distance_km' => 3.1,
                'elevation_gain_m' => 57,
                'estimated_time_hours' => 2.0,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [38.73661, -109.52005],
                'end_coordinates' => [38.74395, -109.49926],
                'route_coordinates' => [
                    [38.73661, -109.52005], // Delicate Arch trailhead
                    [38.73800, -109.51500], // Slickrock section
                    [38.74200, -109.50500], // Final approach
                    [38.74395, -109.49926]  // Delicate Arch viewpoint
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Fall', 'Winter'],
                'directions' => 'From Moab, take US-191 north 5 miles, turn right on Delicate Arch Road, follow to parking area.',
                'parking_info' => 'Large parking area at trailhead. Can fill during peak season.',
                'safety_notes' => 'No shade on trail. Bring plenty of water. Slickrock can be slippery when wet.',
                'is_featured' => true,
            ],
            [
                'name' => 'Old Rag Mountain Loop',
                'description' => 'Virginia\'s most popular and challenging hike featuring a thrilling rock scramble to panoramic summit views. This classic Shenandoah trail combines forest hiking with technical rock climbing.',
                'location' => 'Syria, Virginia',
                'difficulty_level' => 5.0,
                'distance_km' => 8.6,
                'elevation_gain_m' => 225,
                'estimated_time_hours' => 6.0,
                'trail_type' => 'loop',
                'start_coordinates' => [38.55156, -78.29245],
                'end_coordinates' => [38.55156, -78.29245],
                'route_coordinates' => [
                    [38.55156, -78.29245], // Old Rag parking
                    [38.55500, -78.29800], // Ridge trail
                    [38.56200, -78.30100], // Rock scramble section
                    [38.56789, -78.30456], // Old Rag summit
                    [38.56500, -78.29500], // Saddle trail descent
                    [38.55800, -78.29200], // Fire road
                    [38.55156, -78.29245]  // Back to parking
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Fall'],
                'directions' => 'From Sperryville, take Route 231 south, turn right on Route 602, follow to parking area.',
                'parking_info' => 'Limited parking fills very early on weekends. Carpooling strongly recommended.',
                'safety_notes' => 'Rock scramble section requires use of hands. Not suitable for dogs. Parking lot closes at dark.',
                'is_featured' => true,
            ],
            [
                'name' => 'Alum Cave Trail to Mount LeConte',
                'description' => 'Tennessee\'s premier mountain hike featuring unique geological formations, cable handholds, and the highest peak accessible by trail in Great Smoky Mountains National Park.',
                'location' => 'Gatlinburg, Tennessee',
                'difficulty_level' => 5.0,
                'distance_km' => 10.7,
                'elevation_gain_m' => 279,
                'estimated_time_hours' => 6.5,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [35.61367, -83.44456],
                'end_coordinates' => [35.65456, -83.43234],
                'route_coordinates' => [
                    [35.61367, -83.44456], // Alum Cave trailhead
                    [35.62100, -83.44600], // Arch Rock
                    [35.63200, -83.44200], // Alum Cave Bluffs
                    [35.64500, -83.43800], // The Eye of the Needle
                    [35.65200, -83.43400], // Cliff Tops
                    [35.65456, -83.43234]  // LeConte Lodge
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Summer', 'Fall'],
                'directions' => 'From Gatlinburg, take US-441 south 8.7 miles to Alum Cave parking area on left.',
                'parking_info' => 'Limited roadside parking. Arrives early or use Gatlinburg trolley system.',
                'safety_notes' => 'Cable sections can be icy. Weather changes rapidly at elevation. Hypothermia risk year-round.',
                'is_featured' => true,
            ],
            [
                'name' => 'Vernal and Nevada Falls via the Mist Trail',
                'description' => 'Yosemite\'s most popular waterfall hike featuring granite steps alongside thundering cascades, with spectacular views from the top of both falls.',
                'location' => 'Yosemite Valley, California',
                'difficulty_level' => 3.0,
                'distance_km' => 6.4,
                'elevation_gain_m' => 204,
                'estimated_time_hours' => 4.0,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [37.73123, -119.55456],
                'end_coordinates' => [37.72890, -119.53789],
                'route_coordinates' => [
                    [37.73123, -119.55456], // Happy Isles trailhead
                    [37.73000, -119.54800], // Footbridge
                    [37.72950, -119.54200], // Vernal Falls base
                    [37.72900, -119.54000], // Top of Vernal Falls
                    [37.72890, -119.53789]  // Top of Nevada Falls
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Summer', 'Fall'],
                'directions' => 'Take Yosemite Valley shuttle to Happy Isles stop #16.',
                'parking_info' => 'No parking at trailhead. Use Valley shuttle system from day-use parking areas.',
                'safety_notes' => 'Granite steps are extremely slippery when wet. Deaths occur regularly from falls.',
                'is_featured' => true,
            ],
            [
                'name' => 'The Zion Narrows Riverside Walk',
                'description' => 'Paved trail along the Virgin River leading to the entrance of the famous Zion Narrows, suitable for all ages with interpretive exhibits.',
                'location' => 'Springdale, Utah',
                'difficulty_level' => 1.0,
                'distance_km' => 1.8,
                'elevation_gain_m' => 19,
                'estimated_time_hours' => 1.0,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [37.28567, -112.94789],
                'end_coordinates' => [37.28945, -112.95123],
                'route_coordinates' => [
                    [37.28567, -112.94789], // Temple of Sinawava
                    [37.28700, -112.94900], // Riverside walk
                    [37.28850, -112.95000], // Hanging gardens
                    [37.28945, -112.95123]  // Narrows entrance
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Summer', 'Fall', 'Winter'],
                'directions' => 'Take Zion Canyon Shuttle to Temple of Sinawava (last stop).',
                'parking_info' => 'No parking at trailhead. Must use shuttle system from Visitor Center.',
                'safety_notes' => 'Paved and accessible trail. Flash flood danger beyond this point in the Narrows.',
                'is_featured' => false,
            ],
            [
                'name' => 'Sky Pond via Glacier Gorge Trail',
                'description' => 'Spectacular alpine lake beneath dramatic cliff faces and waterfalls in Rocky Mountain National Park, requiring boulder scrambling for the final approach.',
                'location' => 'Estes Park, Colorado',
                'difficulty_level' => 5.0,
                'distance_km' => 8.1,
                'elevation_gain_m' => 164,
                'estimated_time_hours' => 5.0,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [40.31089, -105.64450],
                'end_coordinates' => [40.29567, -105.66789],
                'route_coordinates' => [
                    [40.31089, -105.64450], // Bear Lake trailhead
                    [40.30500, -105.65200], // Alberta Falls
                    [40.30000, -105.65800], // The Loch
                    [40.29700, -105.66400], // Lake of Glass
                    [40.29567, -105.66789]  // Sky Pond
                ],
                'status' => 'active',
                'best_seasons' => ['Summer', 'Fall'],
                'directions' => 'From Estes Park, take Bear Lake Road to Bear Lake parking area.',
                'parking_info' => 'Very limited parking. Shuttle recommended during peak season.',
                'safety_notes' => 'Final approach requires boulder scrambling. Can be dangerous when wet or icy.',
                'is_featured' => false,
            ],
            [
                'name' => 'Gem Lake Trail',
                'description' => 'Popular Rocky Mountain foothills hike to a unique lake formed in granite bedrock, offering great views with moderate effort.',
                'location' => 'Glen Haven, Colorado',
                'difficulty_level' => 3.0,
                'distance_km' => 3.1,
                'elevation_gain_m' => 92,
                'estimated_time_hours' => 2.0,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [40.41234, -105.39876],
                'end_coordinates' => [40.42456, -105.38567],
                'route_coordinates' => [
                    [40.41234, -105.39876], // Gem Lake trailhead
                    [40.41500, -105.39500], // Switchbacks through forest
                    [40.42000, -105.39000], // Boulder field
                    [40.42456, -105.38567]  // Gem Lake
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Summer', 'Fall'],
                'directions' => 'From Estes Park, take Devil\'s Gulch Road north to Glen Haven, continue to trailhead.',
                'parking_info' => 'Small parking area at trailhead. Popular on weekends.',
                'safety_notes' => 'Trail can be muddy in spring. Watch for rattlesnakes in warmer months.',
                'is_featured' => false,
            ],
            [
                'name' => 'Upper Yosemite Falls Trail',
                'description' => 'Strenuous climb to the top of North America\'s tallest waterfall with incredible valley views, featuring granite switchbacks and creek crossings.',
                'location' => 'Yosemite Valley, California',
                'difficulty_level' => 5.0,
                'distance_km' => 7.2,
                'elevation_gain_m' => 295,
                'estimated_time_hours' => 6.0,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [37.75456, -119.59678],
                'end_coordinates' => [37.75934, -119.59789],
                'route_coordinates' => [
                    [37.75456, -119.59678], // Yosemite Falls trailhead
                    [37.75600, -119.59500], // Switchbacks begin
                    [37.75800, -119.59200], // Columbia Rock
                    [37.75900, -119.59000], // Steep granite section
                    [37.75934, -119.59789]  // Top of Upper Falls
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Summer', 'Fall'],
                'directions' => 'Park at Yosemite Falls parking area or take Valley shuttle to stop #7.',
                'parking_info' => 'Limited parking. Use Valley shuttle system during peak season.',
                'safety_notes' => 'Very strenuous. Bring plenty of water. Trail can be icy in winter/spring.',
                'is_featured' => false,
            ],
            [
                'name' => 'Avalanche Lake via Trail of the Cedars',
                'description' => 'Glacier National Park gem featuring ancient cedars, gorge views, and pristine alpine lake surrounded by cascading waterfalls and towering peaks.',
                'location' => 'Lake McDonald, Montana',
                'difficulty_level' => 3.0,
                'distance_km' => 5.7,
                'elevation_gain_m' => 69,
                'estimated_time_hours' => 3.0,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [48.67234, -113.81567],
                'end_coordinates' => [48.68456, -113.83789],
                'route_coordinates' => [
                    [48.67234, -113.81567], // Trail of Cedars trailhead
                    [48.67300, -113.81700], // Boardwalk through cedars
                    [48.67500, -113.82200], // Avalanche Creek
                    [48.68000, -113.83000], // Steep section
                    [48.68456, -113.83789]  // Avalanche Lake
                ],
                'status' => 'active',
                'best_seasons' => ['Summer', 'Fall'],
                'directions' => 'From West Glacier, take Going-to-the-Sun Road 16 miles to Avalanche Creek area.',
                'parking_info' => 'Large parking area but fills early in summer. Arrive before 9 AM.',
                'safety_notes' => 'Bear country - make noise and carry bear spray. Trail may be snow-covered until July.',
                'is_featured' => false,
            ],
            [
                'name' => 'Chimney Tops Trail',
                'description' => 'Steep, rocky climb to distinctive twin peaks offering panoramic views of the Smoky Mountains. The trail features a challenging final scramble over bare rock.',
                'location' => 'Gatlinburg, Tennessee',
                'difficulty_level' => 5.0,
                'distance_km' => 3.6,
                'elevation_gain_m' => 120,
                'estimated_time_hours' => 3.0,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [35.63456, -83.49123],
                'end_coordinates' => [35.64789, -83.49567],
                'route_coordinates' => [
                    [35.63456, -83.49123], // Chimney Tops trailhead
                    [35.63800, -83.49300], // Creek crossing
                    [35.64200, -83.49400], // Steep switchbacks
                    [35.64600, -83.49500], // Rock scramble begins
                    [35.64789, -83.49567]  // Chimney Tops summit
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Summer', 'Fall'],
                'directions' => 'From Gatlinburg, take US-441 south 6.8 miles to Chimney Tops parking area.',
                'parking_info' => 'Small parking area fills quickly. Arrive early morning for best chance.',
                'safety_notes' => 'Final scramble is dangerous - no guardrails. Not recommended for children or inexperienced hikers.',
                'is_featured' => false,
            ],
            [
                'name' => 'Grinnell Glacier Trail',
                'description' => 'Stunning Glacier National Park hike to an active glacier, featuring pristine lakes, wildflower meadows, and dramatic mountain scenery.',
                'location' => 'Babb, Montana',
                'difficulty_level' => 5.0,
                'distance_km' => 11.3,
                'elevation_gain_m' => 201,
                'estimated_time_hours' => 6.0,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [48.69123, -113.65456],
                'end_coordinates' => [48.75234, -113.72789],
                'route_coordinates' => [
                    [48.69123, -113.65456], // Many Glacier Hotel
                    [48.70000, -113.66000], // Swiftcurrent Lake
                    [48.71500, -113.68000], // Lake Josephine
                    [48.73000, -113.70000], // Upper Grinnell Lake
                    [48.75234, -113.72789]  // Grinnell Glacier
                ],
                'status' => 'active',
                'best_seasons' => ['Summer', 'Fall'],
                'directions' => 'From Babb, take Many Glacier Road 12 miles to Many Glacier Hotel.',
                'parking_info' => 'Limited parking at Many Glacier. Arrive very early during peak season.',
                'safety_notes' => 'Bear country - carry bear spray. Weather can change rapidly. Glacier area can be dangerous.',
                'is_featured' => false,
            ],
            [
                'name' => 'Half Dome Trail',
                'description' => 'Yosemite\'s most iconic and challenging hike featuring cables for the final ascent up the granite face to panoramic Sierra Nevada views.',
                'location' => 'Yosemite Valley, California',
                'difficulty_level' => 5.0,
                'distance_km' => 14.8,
                'elevation_gain_m' => 480,
                'estimated_time_hours' => 10.0,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [37.73123, -119.55456],
                'end_coordinates' => [37.74612, -119.53234],
                'route_coordinates' => [
                    [37.73123, -119.55456], // Happy Isles trailhead
                    [37.72900, -119.54000], // Top of Vernal Falls
                    [37.72890, -119.53789], // Top of Nevada Falls
                    [37.73500, -119.53000], // Little Yosemite Valley
                    [37.74200, -119.53100], // Sub Dome
                    [37.74612, -119.53234]  // Half Dome summit
                ],
                'status' => 'active',
                'best_seasons' => ['Summer', 'Fall'],
                'directions' => 'Take Yosemite Valley shuttle to Happy Isles stop #16.',
                'parking_info' => 'No parking at trailhead. Use Valley shuttle from day-use parking.',
                'safety_notes' => 'PERMIT REQUIRED. Cables extremely dangerous in wet conditions. Lightning risk above treeline.',
                'is_featured' => true,
            ],
            [
                'name' => 'The Beehive Loop Trail',
                'description' => 'Acadia National Park\'s most thrilling hike featuring iron rungs and ladders up steep granite cliffs with spectacular ocean and island views.',
                'location' => 'Bar Harbor, Maine',
                'difficulty_level' => 5.0,
                'distance_km' => 1.4,
                'elevation_gain_m' => 45,
                'estimated_time_hours' => 2.0,
                'trail_type' => 'loop',
                'start_coordinates' => [44.33234, -68.20567],
                'end_coordinates' => [44.33234, -68.20567],
                'route_coordinates' => [
                    [44.33234, -68.20567], // Bowl Trail/Sand Beach
                    [44.33400, -68.20600], // Iron rungs section
                    [44.33567, -68.20678], // The Beehive summit
                    [44.33500, -68.20500], // Bowl Trail descent
                    [44.33234, -68.20567]  // Back to start
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Summer', 'Fall'],
                'directions' => 'Take Island Explorer bus to Sand Beach or park at Sand Beach parking area.',
                'parking_info' => 'Sand Beach parking fills early. Use Island Explorer shuttle system.',
                'safety_notes' => 'Iron rungs and ladders - extremely dangerous in wet conditions. Not for those afraid of heights.',
                'is_featured' => false,
            ],
            [
                'name' => 'Vernal Falls',
                'description' => 'Classic Yosemite waterfall hike on granite steps with spectacular views of the 317-foot cascade, especially dramatic during spring snowmelt.',
                'location' => 'Yosemite Valley, California',
                'difficulty_level' => 3.0,
                'distance_km' => 3.7,
                'elevation_gain_m' => 124,
                'estimated_time_hours' => 3.0,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [37.73123, -119.55456],
                'end_coordinates' => [37.72900, -119.54000],
                'route_coordinates' => [
                    [37.73123, -119.55456], // Happy Isles trailhead
                    [37.73000, -119.54800], // Footbridge
                    [37.72950, -119.54200], // Base of Vernal Falls
                    [37.72900, -119.54000]  // Top of Vernal Falls
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Summer', 'Fall'],
                'directions' => 'Take Yosemite Valley shuttle to Happy Isles stop #16.',
                'parking_info' => 'No parking at trailhead. Use Valley shuttle system.',
                'safety_notes' => 'Granite steps extremely slippery when wet. Stay back from waterfall edge.',
                'is_featured' => false,
            ],
            [
                'name' => 'Navajo Loop and Queen\'s Garden Trail',
                'description' => 'Bryce Canyon\'s most popular hike descending into the colorful hoodoo forest with spectacular rock formations and narrow slot canyons.',
                'location' => 'Bryce, Utah',
                'difficulty_level' => 3.0,
                'distance_km' => 2.6,
                'elevation_gain_m' => 58,
                'estimated_time_hours' => 1.5,
                'trail_type' => 'loop',
                'start_coordinates' => [37.62789, -112.16234],
                'end_coordinates' => [37.62789, -112.16234],
                'route_coordinates' => [
                    [37.62789, -112.16234], // Sunset Point
                    [37.62600, -112.16300], // Navajo Loop descent
                    [37.62400, -112.16200], // Trail junction
                    [37.62500, -112.16100], // Queen's Garden
                    [37.62700, -112.16150], // Sunrise Point
                    [37.62789, -112.16234]  // Back to Sunset Point
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Summer', 'Fall'],
                'directions' => 'From Bryce Canyon Visitor Center, take shuttle to Sunset Point.',
                'parking_info' => 'Parking available at Sunset Point. Use shuttle during peak season.',
                'safety_notes' => 'Trail can be icy in winter and early spring. Stay on designated paths.',
                'is_featured' => false,
            ],
            [
                'name' => 'Skyline Trail',
                'description' => 'Mount Rainier\'s premier wildflower and glacier viewing trail through alpine meadows with spectacular views of the mountain and surrounding peaks.',
                'location' => 'Paradise, Washington',
                'difficulty_level' => 3.0,
                'distance_km' => 5.3,
                'elevation_gain_m' => 163,
                'estimated_time_hours' => 3.0,
                'trail_type' => 'loop',
                'start_coordinates' => [46.78567, -121.73456],
                'end_coordinates' => [46.78567, -121.73456],
                'route_coordinates' => [
                    [46.78567, -121.73456], // Paradise Visitor Center
                    [46.78800, -121.73600], // Glacier Vista
                    [46.79200, -121.74000], // Panorama Point
                    [46.79000, -121.74500], // Golden Gate
                    [46.78600, -121.74200], // Myrtle Falls
                    [46.78567, -121.73456]  // Back to Paradise
                ],
                'status' => 'active',
                'best_seasons' => ['Summer', 'Fall'],
                'directions' => 'From Longmire, take Paradise Road 19 miles to Paradise.',
                'parking_info' => 'Large parking area at Paradise but fills early on weekends.',
                'safety_notes' => 'Weather changes rapidly. Trail may be snow-covered until late July.',
                'is_featured' => false,
            ],
            [
                'name' => 'Devils Garden Loop Trail with 7 Arches',
                'description' => 'Arches National Park adventure featuring multiple natural arches including Landscape Arch, the longest natural arch in North America.',
                'location' => 'Thompson, Utah',
                'difficulty_level' => 5.0,
                'distance_km' => 7.5,
                'elevation_gain_m' => 99,
                'estimated_time_hours' => 4.0,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [38.79123, -109.59234],
                'end_coordinates' => [38.82456, -109.60789],
                'route_coordinates' => [
                    [38.79123, -109.59234], // Devils Garden trailhead
                    [38.79500, -109.59400], // Landscape Arch
                    [38.80000, -109.59800], // Wall Arch (collapsed)
                    [38.81000, -109.60200], // Partition Arch
                    [38.81500, -109.60400], // Navajo Arch
                    [38.82000, -109.60600], // Double O Arch
                    [38.82456, -109.60789]  // Dark Angel
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Fall', 'Winter'],
                'directions' => 'From Moab, take US-191 north 5 miles to Arches entrance, continue 18 miles to Devils Garden.',
                'parking_info' => 'Large parking area but fills during peak season. Arrive early.',
                'safety_notes' => 'No shade on trail. Slickrock can be slippery. Primitive trail beyond Landscape Arch.',
                'is_featured' => false,
            ],
            [
                'name' => 'Laurel Falls Trail',
                'description' => 'Great Smoky Mountains\' most popular waterfall trail featuring a paved path to an 80-foot cascade, perfect for families and accessible to all.',
                'location' => 'Gatlinburg, Tennessee',
                'difficulty_level' => 1.0,
                'distance_km' => 2.4,
                'elevation_gain_m' => 37,
                'estimated_time_hours' => 1.5,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [35.69456, -83.53789],
                'end_coordinates' => [35.70123, -83.54567],
                'route_coordinates' => [
                    [35.69456, -83.53789], // Laurel Falls parking
                    [35.69800, -83.54100], // Paved trail through forest
                    [35.70000, -83.54300], // Bridge crossing
                    [35.70123, -83.54567]  // Laurel Falls viewpoint
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Summer', 'Fall', 'Winter'],
                'directions' => 'From Gatlinburg, take US-441 south to Fighting Creek Gap, turn on Laurel Falls Road.',
                'parking_info' => 'Limited parking along road. Very crowded - arrive early or late in day.',
                'safety_notes' => 'Paved trail but can be crowded. Stay on trail and away from waterfall edge.',
                'is_featured' => false,
            ],
            [
                'name' => 'East Rim Trail to Observation Point',
                'description' => 'Spectacular Zion overlook providing bird\'s eye views of the canyon floor and Angels Landing from above, accessed via East Mesa trailhead.',
                'location' => 'Mount Carmel Junction, Utah',
                'difficulty_level' => 5.0,
                'distance_km' => 7.1,
                'elevation_gain_m' => 259,
                'estimated_time_hours' => 4.0,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [37.28123, -112.91456],
                'end_coordinates' => [37.26789, -112.94234],
                'route_coordinates' => [
                    [37.28123, -112.91456], // East Mesa trailhead
                    [37.27800, -112.92000], // Mesa traverse
                    [37.27500, -112.93000], // Canyon rim approach
                    [37.27000, -112.93800], // East Rim trail junction
                    [37.26789, -112.94234]  // Observation Point
                ],
                'status' => 'closed',
                'best_seasons' => ['Spring', 'Summer', 'Fall'],
                'directions' => 'From Mount Carmel Junction, take North Fork Road to East Mesa trailhead.',
                'parking_info' => 'Dirt road access - high clearance vehicle recommended.',
                'safety_notes' => 'TRAIL CURRENTLY CLOSED due to rockfall. Check park status before visiting.',
                'is_featured' => false,
            ],
            [
                'name' => 'Grotto Falls Trail',
                'description' => 'Unique Great Smoky Mountains trail that passes behind a 25-foot waterfall, offering the rare opportunity to walk behind falling water.',
                'location' => 'Gatlinburg, Tennessee',
                'difficulty_level' => 3.0,
                'distance_km' => 2.6,
                'elevation_gain_m' => 50,
                'estimated_time_hours' => 2.0,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [35.71234, -83.52456],
                'end_coordinates' => [35.70789, -83.53123],
                'route_coordinates' => [
                    [35.71234, -83.52456], // Grotto Falls trailhead
                    [35.71000, -83.52600], // Old roadbed section
                    [35.70800, -83.52900], // Creek crossing
                    [35.70789, -83.53123]  // Grotto Falls
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Summer', 'Fall'],
                'directions' => 'From Gatlinburg, take Roaring Fork Motor Nature Trail to Grotto Falls parking.',
                'parking_info' => 'Small parking area along Roaring Fork Motor Nature Trail.',
                'safety_notes' => 'Rocks behind waterfall can be slippery. Trail can be muddy after rain.',
                'is_featured' => false,
            ],
            [
                'name' => 'Rainbow Falls Trail',
                'description' => 'Challenging Great Smoky Mountains hike to an 80-foot waterfall that creates rainbows in the mist on sunny afternoons.',
                'location' => 'Gatlinburg, Tennessee',
                'difficulty_level' => 3.0,
                'distance_km' => 5.5,
                'elevation_gain_m' => 154,
                'estimated_time_hours' => 3.5,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [35.71456, -83.52234],
                'end_coordinates' => [35.73789, -83.50567],
                'route_coordinates' => [
                    [35.71456, -83.52234], // Rainbow Falls trailhead
                    [35.72000, -83.51800], // LeConte Creek
                    [35.72800, -83.51200], // Steep switchbacks
                    [35.73400, -83.50800], // Final approach
                    [35.73789, -83.50567]  // Rainbow Falls
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Summer', 'Fall'],
                'directions' => 'From Gatlinburg, take Cherokee Orchard Road to Rainbow Falls parking.',
                'parking_info' => 'Small parking area - fills quickly on weekends.',
                'safety_notes' => 'Steep and rocky trail. Ice formation in winter can be dangerous.',
                'is_featured' => false,
            ],
            [
                'name' => 'Chasm Lake',
                'description' => 'Spectacular Rocky Mountain National Park alpine lake nestled beneath the dramatic east face of Longs Peak, Colorado\'s highest summit.',
                'location' => 'Estes Park, Colorado',
                'difficulty_level' => 5.0,
                'distance_km' => 9.4,
                'elevation_gain_m' => 242,
                'estimated_time_hours' => 5.0,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [40.27234, -105.55567],
                'end_coordinates' => [40.25456, -105.61789],
                'route_coordinates' => [
                    [40.27234, -105.55567], // Longs Peak trailhead
                    [40.26800, -105.57000], // Alpine Brook
                    [40.26200, -105.59000], // Granite Pass
                    [40.25800, -105.60500], // Boulder field
                    [40.25456, -105.61789]  // Chasm Lake
                ],
                'status' => 'active',
                'best_seasons' => ['Summer', 'Fall'],
                'directions' => 'From Estes Park, take CO-7 south 9 miles to Longs Peak Road, continue to trailhead.',
                'parking_info' => 'Large parking area but fills early in summer. Arrive before dawn.',
                'safety_notes' => 'High altitude - watch for altitude sickness. Weather changes rapidly above treeline.',
                'is_featured' => false,
            ],
            [
                'name' => 'Zion Canyon Overlook Trail',
                'description' => 'Moderate Zion hike featuring slickrock terrain and a spectacular overlook of the main canyon and Pine Creek Canyon.',
                'location' => 'Hurricane, Utah',
                'difficulty_level' => 3.0,
                'distance_km' => 1.0,
                'elevation_gain_m' => 20,
                'estimated_time_hours' => 1.0,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [37.20567, -112.94789],
                'end_coordinates' => [37.20789, -112.95123],
                'route_coordinates' => [
                    [37.20567, -112.94789], // Canyon Overlook trailhead
                    [37.20650, -112.94850], // Slickrock section
                    [37.20700, -112.94950], // Pine tree area
                    [37.20789, -112.95123]  // Canyon overlook
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Summer', 'Fall', 'Winter'],
                'directions' => 'East side of Zion-Mount Carmel Tunnel on UT-9.',
                'parking_info' => 'Small parking area east of tunnel. Can fill during peak season.',
                'safety_notes' => 'Steep dropoffs with no guardrails. Slickrock can be slippery when wet.',
                'is_featured' => false,
            ],
            [
                'name' => 'Abrams Falls Trail',
                'description' => 'Great Smoky Mountains trail to the park\'s most voluminous waterfall, featuring a 20-foot cascade into a large swimming hole.',
                'location' => 'Townsend, Tennessee',
                'difficulty_level' => 3.0,
                'distance_km' => 4.9,
                'elevation_gain_m' => 58,
                'estimated_time_hours' => 3.0,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [35.60789, -83.75456],
                'end_coordinates' => [35.63123, -83.77234],
                'route_coordinates' => [
                    [35.60789, -83.75456], // Abrams Falls trailhead
                    [35.61500, -83.76000], // Abrams Creek crossing
                    [35.62200, -83.76500], // Forest section
                    [35.62800, -83.77000], // Final approach
                    [35.63123, -83.77234]  // Abrams Falls
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Summer', 'Fall'],
                'directions' => 'From Townsend, take Cades Cove Loop Road to Abrams Falls parking area.',
                'parking_info' => 'Parking area in Cades Cove - arrive early to avoid traffic.',
                'safety_notes' => 'Swimming not recommended due to strong undercurrents and cold water.',
                'is_featured' => false,
            ],
            [
                'name' => 'Clingmans Dome Observation Tower Trail',
                'description' => 'Paved trail to the highest point in Great Smoky Mountains National Park featuring a 54-foot observation tower with 360-degree views.',
                'location' => 'Bryson City, North Carolina',
                'difficulty_level' => 1.0,
                'distance_km' => 1.2,
                'elevation_gain_m' => 31,
                'estimated_time_hours' => 1.0,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [35.56456, -83.49789],
                'end_coordinates' => [35.56234, -83.49567],
                'route_coordinates' => [
                    [35.56456, -83.49789], // Clingmans Dome parking
                    [35.56350, -83.49700], // Paved trail
                    [35.56300, -83.49650], // Steep section
                    [35.56234, -83.49567]  // Observation tower
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Summer', 'Fall'],
                'directions' => 'From Gatlinburg, take US-441 south to Clingmans Dome Road, continue 7 miles to parking.',
                'parking_info' => 'Large parking area but very crowded. Road closed in winter.',
                'safety_notes' => 'Paved but steep trail. Can be very cold and windy at summit.',
                'is_featured' => false,
            ],
            [
                'name' => 'The Watchman Trail',
                'description' => 'Popular Zion National Park hike to a scenic overlook providing stunning views of Zion Canyon, the Virgin River, and the town of Springdale.',
                'location' => 'Springdale, Utah',
                'difficulty_level' => 3.0,
                'distance_km' => 3.1,
                'elevation_gain_m' => 60,
                'estimated_time_hours' => 2.0,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [37.19234, -112.98567],
                'end_coordinates' => [37.20456, -112.98789],
                'route_coordinates' => [
                    [37.19234, -112.98567], // Watchman trailhead
                    [37.19500, -112.98600], // Bridge crossing
                    [37.19800, -112.98650], // Switchbacks begin
                    [37.20200, -112.98700], // Final climb
                    [37.20456, -112.98789]  // Watchman overlook
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Summer', 'Fall', 'Winter'],
                'directions' => 'From Zion Canyon Visitor Center, walk or drive to Watchman Campground area.',
                'parking_info' => 'Parking at Watchman Campground or Visitor Center with short walk.',
                'safety_notes' => 'Exposed trail with little shade. Bring plenty of water in summer.',
                'is_featured' => false,
            ],
            [
                'name' => 'The Loch Lake Trail via Glacier Gorge Trail',
                'description' => 'Beautiful Rocky Mountain National Park lake hike through diverse ecosystems to a pristine alpine lake beneath towering peaks.',
                'location' => 'Estes Park, Colorado',
                'difficulty_level' => 3.0,
                'distance_km' => 5.4,
                'elevation_gain_m' => 100,
                'estimated_time_hours' => 3.0,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [40.31089, -105.64450],
                'end_coordinates' => [40.30234, -105.65789],
                'route_coordinates' => [
                    [40.31089, -105.64450], // Bear Lake trailhead
                    [40.30800, -105.64800], // Glacier Gorge junction
                    [40.30500, -105.65200], // Alberta Falls
                    [40.30300, -105.65500], // Icy Brook crossing
                    [40.30234, -105.65789]  // The Loch
                ],
                'status' => 'active',
                'best_seasons' => ['Summer', 'Fall'],
                'directions' => 'From Estes Park, take Bear Lake Road to Bear Lake parking area.',
                'parking_info' => 'Limited parking at Bear Lake. Use shuttle during peak season.',
                'safety_notes' => 'Trail can be icy early and late in season. Wildlife present.',
                'is_featured' => false,
            ],
            [
                'name' => 'Deer Mountain Trail',
                'description' => 'Moderate Rocky Mountain National Park summit hike offering panoramic views of the surrounding peaks and valleys with diverse wildlife viewing opportunities.',
                'location' => 'Estes Park, Colorado',
                'difficulty_level' => 3.0,
                'distance_km' => 5.6,
                'elevation_gain_m' => 130,
                'estimated_time_hours' => 3.5,
                'trail_type' => 'out-and-back',
                'start_coordinates' => [40.38234, -105.58567],
                'end_coordinates' => [40.39456, -105.59789],
                'route_coordinates' => [
                    [40.38234, -105.58567], // Deer Ridge Junction
                    [40.38500, -105.58800], // Aspen grove
                    [40.38800, -105.59100], // Switchbacks through forest
                    [40.39200, -105.59400], // Open meadows
                    [40.39456, -105.59789]  // Deer Mountain summit
                ],
                'status' => 'active',
                'best_seasons' => ['Spring', 'Summer', 'Fall'],
                'directions' => 'From Estes Park, take US-36 west 3 miles to Deer Ridge Junction trailhead.',
                'parking_info' => 'Roadside parking at Deer Ridge Junction along US-36.',
                'safety_notes' => 'Watch for elk and deer, especially during rutting season. Lightning risk on summit.',
                'is_featured' => false,
            ],
        ];

        foreach ($trails as $trail) {
            Trail::create($trail);
        }
    }
}
