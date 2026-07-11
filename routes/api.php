<?php

use App\Http\Controllers\Api\AppTokenController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\EntitlementController;
use App\Http\Controllers\Api\EventImportController;
use App\Http\Controllers\Api\TrailPhotoController;
use App\Http\Controllers\TrailController;
use App\Http\Controllers\TrailNetworkController;
use App\Http\Middleware\VerifyAppKey;
use App\Models\Business;
use App\Models\Facility;
use App\Models\Tour;
use App\Models\TrailNetwork;
use App\Services\RouteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/**
 * Decode a Google-encoded polyline string into [[lng, lat], ...] pairs (Mapbox-ready).
 * ORS returns encoded polyline with 1e5 precision by default.
 *
 * Guarded with function_exists so `route:cache` (which loads the route files)
 * does not fatal with "Cannot redeclare decodePolyline()".
 */
if (! function_exists('decodePolyline')) {
    function decodePolyline(string $encoded): array
    {
        $index = 0;
        $lat = 0;
        $lng = 0;
        $coordinates = [];
        $len = strlen($encoded);

        while ($index < $len) {
            $shift = 0;
            $result = 0;
            do {
                $b = ord($encoded[$index++]) - 63;
                $result |= ($b & 0x1F) << $shift;
                $shift += 5;
            } while ($b >= 0x20);
            $lat += ($result & 1) ? ~($result >> 1) : ($result >> 1);

            $shift = 0;
            $result = 0;
            do {
                $b = ord($encoded[$index++]) - 63;
                $result |= ($b & 0x1F) << $shift;
                $shift += 5;
            } while ($b >= 0x20);
            $lng += ($result & 1) ? ~($result >> 1) : ($result >> 1);

            $coordinates[] = [round($lng / 1e5, 6), round($lat / 1e5, 6)];
        }

        return $coordinates;
    }
}

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
 * App-token issuance — exempt from VerifyAppKey (this IS the bootstrap step).
 * Rate-limited tightly: a genuine install only needs one token; high volume = abuse.
 */
Route::post('/auth/app-token', [AppTokenController::class, 'issue'])
    ->withoutMiddleware(VerifyAppKey::class)
    ->middleware('throttle:10,60');

/*
 * Event import — receives scraped events pushed from a trusted machine
 * (see `events:scrape --push`). Authenticated by its own bearer token
 * (EVENTS_IMPORT_TOKEN), so the app-key middleware is excluded.
 */
Route::post('/events/import', [EventImportController::class, 'store'])
    ->withoutMiddleware(VerifyAppKey::class)
    ->middleware('throttle:10,60');

/*
 * XploreSmithers Android app — auth, entitlements & Play billing.
 * Contract: see "Laravel Backend — Auth & Subscription API Spec".
 */
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:300,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:300,1');
    Route::post('/google', [AuthController::class, 'googleSignIn'])->middleware('throttle:300,1');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:300,1');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:300,1');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me', [AuthController::class, 'updateProfile']);
    Route::post('/me/avatar', [AuthController::class, 'updateAvatar']);
    Route::put('/me/password', [AuthController::class, 'updatePassword']);
    Route::delete('/me', [AuthController::class, 'deleteAccount']);
    Route::get('/me/entitlements', [EntitlementController::class, 'show']);

    // Phase B (deferred) — wiring in place, Google verification stubbed.
    Route::post('/billing/verify-purchase', [BillingController::class, 'verifyPurchase'])
        ->middleware('throttle:300,1');
});

// Google Real-Time Developer Notifications (Pub/Sub push) — public, secret-gated.
Route::post('/billing/rtdn', [BillingController::class, 'rtdn']);

// Public routes — called by both the website JS map and the Android app.
// VerifyAppKey is excluded: the website map JS never sends X-App-Key.
Route::withoutMiddleware(VerifyAppKey::class)->group(function () {
    // Community trail photo submission — rate-limited, gated by reCAPTCHA + admin moderation.
    Route::post('/trail-photos', [TrailPhotoController::class, 'store'])
        ->middleware('throttle:5,60');

    // Public list of approved photos for a trail (powers the carousel on the trail details page).
    Route::get('/trail-photos', [TrailPhotoController::class, 'index']);

    // Map data — trails
    Route::get('/trails', [TrailController::class, 'apiIndex']);
    Route::get('/hiking-trails', [TrailController::class, 'apiHikingTrails']);
    Route::get('/fishing-lakes', [TrailController::class, 'apiFishingLakes']);
    Route::get('/trails/{trail}', [TrailController::class, 'apiShow']);
    Route::get('/highlights', [TrailNetworkController::class, 'trailHighlights']);

    // Trail Networks
    Route::get('/trail-networks', function () {
        $networks = TrailNetwork::where('is_active', true)
            ->with(['trails' => function ($q) {
                $q->select('id', 'trail_network_id', 'name', 'difficulty_level', 'distance_km', 'status')
                    ->whereIn('status', ['active', 'seasonal'])
                    ->orderBy('difficulty_level');
            }])
            ->get()
            ->filter(fn ($n) => $n->trails->isNotEmpty())
            ->values()
            ->map(fn ($network) => [
                'id' => $network->id,
                'name' => $network->network_name,
                'slug' => $network->slug,
                'type' => $network->type,
                'season' => $network->season,
                'icon' => $network->icon,
                'image' => $network->image ? asset('storage/'.$network->image) : null,
                'description' => $network->description,
                'latitude' => $network->latitude ? (float) $network->latitude : null,
                'longitude' => $network->longitude ? (float) $network->longitude : null,
                'website_url' => $network->website_url,
                'trail_count' => $network->trails->count(),
                'trails' => $network->trails->map(fn ($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'difficulty' => $t->difficulty_level,
                    'distance' => $t->distance_km,
                ])->values(),
            ]);

        return response()->json($networks);
    });

    // Routing & elevation (used by the website map)
    Route::post('/calculate-route', function (Request $request) {
        $request->validate([
            'start_lat' => 'required|numeric',
            'start_lng' => 'required|numeric',
            'end_lat' => 'required|numeric',
            'end_lng' => 'required|numeric',
        ]);

        // Add debugging
        Log::info('Route calculation request:', $request->all());

        $routeService = new RouteService;
        $route = $routeService->calculateRoute(
            $request->start_lat,
            $request->start_lng,
            $request->end_lat,
            $request->end_lng
        );

        if (! $route) {
            Log::error('Route calculation failed for coordinates', $request->all());

            return response()->json([
                'error' => 'Unable to calculate route',
                'debug' => [
                    'api_key_set' => ! empty(config('services.openrouteservice.api_key')),
                    'coordinates' => $request->all(),
                ],
            ], 400);
        }

        Log::info('Route calculation successful');

        return response()->json($route);
    });

    Route::post('/elevation-profile', function (Request $request) {
        $request->validate([
            'coordinates' => 'required|array',
            'coordinates.*' => 'required|array|min:2',
        ]);

        $routeService = new RouteService;
        $elevation = $routeService->getElevationProfile($request->coordinates);

        if (! $elevation) {
            return response()->json(['error' => 'Unable to get elevation profile'], 400);
        }

        return response()->json($elevation);
    });

    // Facilities
    Route::get('/facilities', function () {
        $facilities = Facility::where('is_active', true)
            ->whereNull('trail_network_id')
            ->with(['media' => function ($query) {
                $query->orderBy('sort_order')->orderBy('created_at');
            }])
            ->get();

        return $facilities->map(function ($facility) {
            return [
                'id' => $facility->id,
                'name' => $facility->name,
                'facility_type' => $facility->facility_type,
                'facility_type_label' => $facility->facility_type_label,
                'description' => $facility->description,
                'latitude' => $facility->latitude,
                'longitude' => $facility->longitude,
                'icon' => $facility->icon,
                'icon_image_url' => $facility->icon_image ? asset('storage/'.$facility->icon_image) : null,
                'media_count' => $facility->media_count,
                'media' => $facility->media->map(function ($media) {
                    // For photos: use file_path directly with asset() like admin blade
                    $photoUrl = $media->file_path ? asset('storage/'.$media->file_path) : ($media->url ?? null);

                    // For videos: use url field directly
                    $videoUrl = $media->url ?? null;

                    return [
                        'id' => $media->id,
                        'media_type' => $media->media_type,
                        'url' => $media->media_type === 'photo' ? $photoUrl : $videoUrl,
                        'thumbnail_url' => $media->media_type === 'photo' ? $photoUrl : $media->thumbnail_url,
                        'embed_url' => $media->embed_url,
                        'caption' => $media->caption,
                        'is_primary' => $media->is_primary,
                        'video_provider' => $media->video_provider,
                    ];
                }),
            ];
        });
    });

    // Tours — public listing for map sidebar
    Route::get('/tours', function () {
        return Tour::active()
            ->withCount('stops')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(fn ($tour) => [
                'id' => $tour->id,
                'title' => $tour->title,
                'slug' => $tour->slug,
                'tagline' => $tour->tagline,
                'tour_type' => $tour->tour_type,
                'duration_estimate' => $tour->duration_estimate,
                'difficulty_summary' => $tour->difficulty_summary,
                'stop_count' => $tour->stops_count,
                'cover_image_url' => $tour->cover_image_url,
            ]);
    });

    // Tour driving route — multi-stop ORS calculation (used by admin compute button)
    Route::post('/tour-route', function (Request $request) {
        $request->validate([
            'waypoints' => 'required|array|min:2',
            'waypoints.*' => 'required|array|min:2',
        ]);

        $apiKey = config('services.openrouteservice.api_key');
        if (empty($apiKey)) {
            return response()->json(['error' => 'Route service not configured'], 503);
        }

        // DB format [lat, lng] → ORS format [lng, lat]
        $coordinates = array_map(
            fn ($wp) => [(float) $wp[1], (float) $wp[0]],
            $request->input('waypoints')
        );

        try {
            $orsResponse = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => $apiKey,
                'Content-Type' => 'application/json; charset=utf-8',
            ])->timeout(15)->post('https://api.openrouteservice.org/v2/directions/driving-car', [
                'coordinates' => $coordinates,
                'instructions' => false,
            ]);

            if (! $orsResponse->successful()) {
                $err = $orsResponse->json('error.message') ?? 'Unable to calculate driving route';

                return response()->json(['error' => $err], 400);
            }

            $data = $orsResponse->json();
            $encodedGeometry = $data['routes'][0]['geometry'] ?? '';
            $totalDistance = ($data['routes'][0]['summary']['distance'] ?? 0) / 1000;

            // Decode Google-format encoded polyline → [[lng, lat], ...] for Mapbox
            $lineCoords = decodePolyline($encodedGeometry);

            return response()->json([
                'coordinates' => $lineCoords,
                'total_km' => round($totalDistance, 1),
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Route calculation failed: '.$e->getMessage()], 500);
        }
    });

    // Businesses
    Route::get('/businesses', function () {
        $type = request('type');

        $businesses = Business::active()
            ->with(['media' => function ($query) {
                $query->orderBy('sort_order')->orderBy('created_at');
            }])
            ->when($type, fn ($q) => $q->where('business_type', $type))
            ->orderBy('name')
            ->get();

        return $businesses->map(function (Business $business) {
            $primaryMedia = $business->media
                ->firstWhere('is_primary', true)
                ?? $business->media->firstWhere('media_type', 'photo');
            $photoUrl = $primaryMedia && $primaryMedia->file_path
                ? asset('storage/'.$primaryMedia->file_path)
                : ($primaryMedia->url ?? null);

            $videos = $business->media
                ->filter(fn ($m) => $m->media_type !== 'photo')
                ->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'media_type' => $m->media_type,
                        'url' => $m->url,
                        'video_url' => $m->url,
                        'thumbnail_url' => $m->thumbnail_url,
                        'embed_url' => $m->embed_url,
                        'caption' => $m->caption,
                        'is_primary' => $m->is_primary,
                        'video_provider' => $m->video_provider,
                    ];
                })->values();

            $media = $business->media->map(function ($m) {
                $photoUrl = $m->file_path ? asset('storage/'.$m->file_path) : ($m->url ?? null);
                $videoUrl = $m->url ?? null;

                return [
                    'id' => $m->id,
                    'media_type' => $m->media_type,
                    'url' => $m->media_type === 'photo' ? $photoUrl : $videoUrl,
                    'thumbnail_url' => $m->media_type === 'photo' ? $photoUrl : $m->thumbnail_url,
                    'embed_url' => $m->embed_url,
                    'caption' => $m->caption,
                    'is_primary' => $m->is_primary,
                    'video_provider' => $m->video_provider,
                ];
            })->values();

            return [
                'id' => $business->id,
                'name' => $business->name,
                'slug' => $business->slug,
                'business_type' => $business->business_type,
                'business_type_label' => $business->business_type_label,
                'description' => $business->description,
                'tagline' => $business->tagline,
                'address' => $business->address,
                'latitude' => $business->latitude,
                'longitude' => $business->longitude,
                'phone' => $business->phone,
                'email' => $business->email,
                'website' => $business->website,
                'price_range' => $business->price_range,
                'is_seasonal' => $business->is_seasonal,
                'season_open' => $business->season_open,
                'icon' => $business->icon,
                'icon_image_url' => $business->icon_image_url,
                'is_featured' => $business->is_featured,
                'photo_url' => $photoUrl,
                'videos' => $videos,
                'media' => $media,
            ];
        });
    });
});
