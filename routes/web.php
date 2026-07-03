<?php

use App\Http\Controllers\Admin\ActivityTypeController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminSubscriptionController;
use App\Http\Controllers\Admin\AdminTourController;
use App\Http\Controllers\Admin\AdminTrailController;
use App\Http\Controllers\Admin\AdminTrailNetworkController;
use App\Http\Controllers\Admin\AdminTrailPhotoController;
use App\Http\Controllers\Admin\BusinessController;
use App\Http\Controllers\Admin\CarouselSlideController;
use App\Http\Controllers\Admin\FacilityController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\TrailHighlightController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\WebAuthController;
use App\Http\Controllers\Auth\WebGoogleAuthController;
use App\Http\Controllers\BusinessPublicController;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Subscription\StripeWebhookController;
use App\Http\Controllers\Subscription\WebSubscriptionController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\TrailController;
use App\Http\Controllers\TrailNetworkController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

// Android App Links — Digital Asset Links file
// Verifies that this domain authorizes the Android app to handle deep links
Route::get('/.well-known/assetlinks.json', function () {
    $package = config('services.android_app.package_name');
    $fingerprints = config('services.android_app.sha256_fingerprints', []);

    if (empty($package) || empty($fingerprints)) {
        return response()->json([], 404);
    }

    return response()->json([[
        'relation' => ['delegate_permission/common.handle_all_urls'],
        'target' => [
            'namespace' => 'android_app',
            'package_name' => $package,
            'sha256_cert_fingerprints' => $fingerprints,
        ],
    ]]);
});

// PUBLIC ROUTES (No authentication required)
Route::get('/', [TrailController::class, 'home'])->name('home');
Route::get('/trails', [TrailController::class, 'index'])->name('trails.index');
Route::get('/fishing-lakes', [TrailController::class, 'fishingLakes'])->name('fishing-lakes.index');
Route::get('/trails/{trail}', [TrailController::class, 'show'])->name('trails.show');
Route::get('/map', [TrailController::class, 'map'])->name('map');
Route::get('/map-v2', [TrailController::class, 'mapV2'])->name('map.v2');

Route::get('/privacy-policy', fn () => view('privacy-policy'))->name('privacy-policy');
Route::get('/terms-and-conditions', fn () => view('terms-and-conditions'))->name('terms');

// Minimal video-only YouTube player for the mobile app's in-app Custom Tab. Path is kept
// OUTSIDE the app's deep-link prefixes so it opens in the browser tab, not the native app.
Route::get('/app/video/youtube/{id}', function (string $id) {
    abort_unless(preg_match('/^[A-Za-z0-9_-]{6,15}$/', $id), 404);

    return view('embed-player', ['videoId' => $id]);
})->name('app.video.youtube');

// Web user authentication — email/password (session-based)
Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');
Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
Route::post('/register', [WebAuthController::class, 'register'])->name('register.post');

// Password reset — request a link, then set a new password
Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:6,1')->name('password.email');
Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
Route::post('/reset-password', [NewPasswordController::class, 'store'])->middleware('throttle:6,1')->name('password.store');

// XploreSmithers Pro — web subscriptions (Stripe)
Route::get('/pro', [WebSubscriptionController::class, 'show'])->name('pro.show');
Route::middleware('auth')->group(function () {
    Route::post('/pro/checkout', [WebSubscriptionController::class, 'checkout'])->name('pro.checkout');
    Route::get('/pro/success', [WebSubscriptionController::class, 'success'])->name('pro.success');
    Route::get('/pro/cancel', [WebSubscriptionController::class, 'cancel'])->name('pro.cancel');
    Route::get('/pro/billing', [WebSubscriptionController::class, 'portal'])->name('pro.portal');
});
// Stripe server-to-server webhook (no CSRF, no auth — verified by signature)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');

// User settings — profile, account, subscription
Route::middleware('auth')->prefix('settings')->name('settings.')->group(function () {
    Route::redirect('/', '/settings/profile');
    Route::get('/profile', [SettingsController::class, 'profile'])->name('profile');
    Route::put('/profile', [SettingsController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/avatar', [SettingsController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profile/avatar', [SettingsController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
    Route::get('/account', [SettingsController::class, 'account'])->name('account');
    Route::put('/account', [SettingsController::class, 'updateAccount'])->name('account.update');
    Route::delete('/account', [SettingsController::class, 'destroyAccount'])->name('account.destroy');
    Route::post('/account/google/disconnect', [SettingsController::class, 'disconnectGoogle'])->name('account.google.disconnect');
    Route::get('/subscription', [SettingsController::class, 'subscription'])->name('subscription');
});

// Web user authentication — Google OAuth redirect flow (session-based)
Route::get('/auth/google/redirect', [WebGoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [WebGoogleAuthController::class, 'callback'])->name('google.callback');
Route::post('/logout', [WebGoogleAuthController::class, 'logout'])->name('logout');

// Public Business Routes
Route::get('/businesses', [BusinessPublicController::class, 'index'])->name('businesses.public.index');
Route::get('/businesses/{business:slug}', [BusinessPublicController::class, 'show'])->name('businesses.public.show');

// Public Tours Routes
Route::get('/tours', [TourController::class, 'index'])->name('tours.index');
Route::get('/tours/{tour:slug}', [TourController::class, 'show'])->name('tours.show');

// Public Trail Networks Routes
Route::get('/trail-networks', [TrailNetworkController::class, 'index'])
    ->name('trail-networks.index');

Route::get('/trail-networks/{slug}', [TrailNetworkController::class, 'show'])
    ->name('trail-networks.show');

// ADMIN ROUTES
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.login');
    })->name('index');
    Route::get('/login', [AdminController::class, 'loginForm'])->name('login');
    Route::post('/login', [AdminController::class, 'login'])->name('login.post');

    // Protected admin routes
    Route::middleware(['auth', 'admin', 'throttle:300,1'])->group(function () {
        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // User management
        Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        // Subscription management
        Route::get('subscriptions', [AdminSubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('subscriptions/{subscription}', [AdminSubscriptionController::class, 'show'])->name('subscriptions.show');
        Route::post('subscriptions/{subscription}/cancel', [AdminSubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
        Route::post('subscriptions/{subscription}/extend', [AdminSubscriptionController::class, 'extend'])->name('subscriptions.extend');

        // Trail management
        Route::post('/trails/bulk-action', [AdminTrailController::class, 'bulkAction'])->name('trails.bulk-action');
        Route::get('/trails/feature-icons', [AdminTrailController::class, 'listFeatureIcons'])->name('trails.feature-icons');
        Route::post('/trails/feature-icons/upload', [AdminTrailController::class, 'uploadFeatureIcon'])->name('trails.feature-icons.upload');
        Route::delete('/trails/feature-icons', [AdminTrailController::class, 'deleteFeatureIcon'])->name('trails.feature-icons.delete');
        Route::resource('trails', AdminTrailController::class);

        // Trail Highlights Management (read/update/delete only — created via the trail builder)
        Route::resource('highlights', TrailHighlightController::class)
            ->only(['index', 'edit', 'update', 'destroy'])
            ->parameters(['highlights' => 'highlight']);

        Route::get('/activity-types/icons', [ActivityTypeController::class, 'listIcons'])->name('activity-types.icons');
        Route::post('/activity-types/icons/upload', [ActivityTypeController::class, 'uploadIcon'])->name('activity-types.icons.upload');
        Route::delete('/activity-types/icons', [ActivityTypeController::class, 'deleteIcon'])->name('activity-types.icons.delete');
        Route::resource('activity-types', ActivityTypeController::class);
        Route::patch('/trails/{trail}/toggle-featured', [AdminTrailController::class, 'toggleFeatured'])->name('trails.toggle-featured')->middleware('throttle:300,1');
        Route::patch('/trails/{trail}/toggle-status', [AdminTrailController::class, 'toggleStatus'])->name('trails.toggle-status')->middleware('throttle:300,1');

        // Add the trail networks routes here
        Route::resource('trail-networks', AdminTrailNetworkController::class)
            ->names([
                'index' => 'trail-networks.index',
                'create' => 'trail-networks.create',
                'store' => 'trail-networks.store',
                'show' => 'trail-networks.show',
                'edit' => 'trail-networks.edit',
                'update' => 'trail-networks.update',
                'destroy' => 'trail-networks.destroy',
            ]);

        // Carousel Slides Management
        Route::get('carousel', [CarouselSlideController::class, 'index'])->name('carousel.index');
        Route::post('carousel', [CarouselSlideController::class, 'store'])->name('carousel.store');
        Route::post('carousel/import', [CarouselSlideController::class, 'import'])->name('carousel.import');
        Route::patch('carousel/{carousel}', [CarouselSlideController::class, 'update'])->name('carousel.update');
        Route::delete('carousel/{carousel}', [CarouselSlideController::class, 'destroy'])->name('carousel.destroy');

        // Facilities Management (Standalone - Global)
        Route::get('/facilities/icons', [FacilityController::class, 'listIcons'])->name('facilities.icons');
        Route::post('/facilities/icons/upload', [FacilityController::class, 'uploadIcon'])->name('facilities.icons.upload');
        Route::delete('/facilities/icons', [FacilityController::class, 'deleteIcon'])->name('facilities.icons.delete');
        Route::resource('facilities', FacilityController::class)
            ->names([
                'index' => 'facilities.index',
                'create' => 'facilities.create',
                'store' => 'facilities.store',
                'edit' => 'facilities.edit',
                'update' => 'facilities.update',
                'destroy' => 'facilities.destroy',
            ]);

        // Facility Media Management Routes
        Route::delete('/facilities/{facility}/media/{media}', [FacilityController::class, 'deleteMedia'])
            ->name('facilities.media.delete');
        Route::patch('/facilities/{facility}/media/{media}/primary', [FacilityController::class, 'setPrimaryMedia'])
            ->name('facilities.media.primary');
        Route::post('/facilities/{facility}/media/order', [FacilityController::class, 'updateMediaOrder'])
            ->name('facilities.media.order');
        Route::patch('/facilities/{facility}/media/{media}/caption', [FacilityController::class, 'updateMediaCaption'])
            ->name('facilities.media.caption');
        // Businesses Management
        Route::post('/businesses/bulk-action', [BusinessController::class, 'bulkAction'])
            ->name('businesses.bulk-action');
        Route::patch('/businesses/{business}/toggle-active', [BusinessController::class, 'toggleActive'])
            ->name('businesses.toggle-active');
        Route::resource('businesses', BusinessController::class)
            ->names([
                'index' => 'businesses.index',
                'create' => 'businesses.create',
                'store' => 'businesses.store',
                'edit' => 'businesses.edit',
                'update' => 'businesses.update',
                'destroy' => 'businesses.destroy',
            ]);

        Route::delete('/businesses/{business}/media/{media}', [BusinessController::class, 'deleteMedia'])
            ->name('businesses.media.delete');
        Route::patch('/businesses/{business}/media/{media}/primary', [BusinessController::class, 'setPrimaryMedia'])
            ->name('businesses.media.primary');
        Route::post('/businesses/{business}/media/order', [BusinessController::class, 'updateMediaOrder'])
            ->name('businesses.media.order');
        Route::patch('/businesses/{business}/media/{media}/caption', [BusinessController::class, 'updateMediaCaption'])
            ->name('businesses.media.caption');

        // Tours Management
        Route::post('/tours/bulk-action', [AdminTourController::class, 'bulkAction'])
            ->name('tours.bulk-action');
        Route::patch('/tours/{tour}/toggle-active', [AdminTourController::class, 'toggleActive'])
            ->name('tours.toggle-active');
        Route::resource('tours', AdminTourController::class)
            ->names([
                'index' => 'tours.index',
                'create' => 'tours.create',
                'store' => 'tours.store',
                'edit' => 'tours.edit',
                'update' => 'tours.update',
                'destroy' => 'tours.destroy',
            ]);

        // Community trail photos — moderation
        Route::get('/trail-photos', [AdminTrailPhotoController::class, 'index'])->name('trail-photos.index');
        Route::patch('/trail-photos/{trailPhoto}', [AdminTrailPhotoController::class, 'update'])->name('trail-photos.update');
        Route::delete('/trail-photos/{trailPhoto}', [AdminTrailPhotoController::class, 'destroy'])->name('trail-photos.destroy');
        Route::post('/trail-photos/bulk', [AdminTrailPhotoController::class, 'bulkUpdate'])->name('trail-photos.bulk');

        // Global Media Library
        Route::get('/media', [MediaController::class, 'index'])->name('media.index');
        Route::get('/media/trail/{media}', [MediaController::class, 'showTrailMedia'])->name('media.trail.show');
        Route::get('/media/facility/{media}', [MediaController::class, 'showFacilityMedia'])->name('media.facility.show');
        Route::delete('/media/trail/{media}', [MediaController::class, 'destroyTrailMedia'])->name('media.trail.destroy');
        Route::delete('/media/facility/{media}', [MediaController::class, 'destroyFacilityMedia'])->name('media.facility.destroy');
        Route::get('/media/business/{media}', [MediaController::class, 'showBusinessMedia'])->name('media.business.show');
        Route::delete('/media/business/{media}', [MediaController::class, 'destroyBusinessMedia'])->name('media.business.destroy');

        // GPX API endpoints
        Route::post('/trails/gpx/preview', [AdminTrailController::class, 'previewGpx'])
            ->name('trails.gpx.preview');
        Route::post('/trails/{trail}/gpx/compare', [AdminTrailController::class, 'compareGpx'])
            ->name('trails.gpx.compare');
    });

});

// Events Routes (Public - No admin middleware)
Route::get('/events', [EventsController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventsController::class, 'show'])->name('events.show');
Route::get('/events/{event}/calendar', [EventsController::class, 'downloadCalendar'])->name('events.calendar');
Route::get('/events/{event}/details', [EventsController::class, 'getEventDetails'])->name('events.details');

// Utility — clear cached config (useful on hosts without CLI access)
// Route::get('/config-clear', function () {
//     Artisan::call('config:clear');

//     return Artisan::output();
// })->name('config.clear');

// // Utility — run only the subscriptions table migration
// Route::get('/migrate-subscriptions', function () {
//     Artisan::call('migrate', [
//         '--path' => 'database/migrations/2026_05_18_144124_create_subscriptions_table.php',
//         '--force' => true,
//     ]);

//     return Artisan::output();
// })->name('migrate.subscriptions');

// Utility — run the trail-network sponsorship migrations (remove after deploy)
Route::get('/migrate-sponsorships', function () {
    Artisan::call('migrate', [
        '--path' => 'database/migrations/2026_05_20_000908_create_trail_network_sponsors_table.php',
        '--force' => true,
    ]);
    $createOutput = Artisan::output();

    Artisan::call('migrate', [
        '--path' => 'database/migrations/2026_05_20_001536_backfill_hudson_bay_mountain_sponsorship.php',
        '--force' => true,
    ]);
    $backfillOutput = Artisan::output();

    return nl2br(e($createOutput."\n".$backfillOutput));
})->name('migrate.sponsorships');

// Utility — run the carousel slides + google_id migrations (remove after deploy)
Route::get('/migrate-carousel-google', function () {
    Artisan::call('migrate', [
        '--path' => 'database/migrations/2026_06_03_000001_create_carousel_slides_table.php',
        '--force' => true,
    ]);
    $carouselOutput = Artisan::output();

    Artisan::call('migrate', [
        '--path' => 'database/migrations/2026_06_04_005841_add_google_id_to_users_table.php',
        '--force' => true,
    ]);
    $googleIdOutput = Artisan::output();

    return nl2br(e($carouselOutput."\n".$googleIdOutput));
})->name('migrate.carousel-google');

Route::get('/test-mail', function () {
    try {
        Mail::raw('Test Email', function ($message) {
            $message->to('darielbongabong90@gmail.com')
                ->subject('SMTP Test');
        });

        return 'Success';
    } catch (Exception $e) {
        return [
            'error' => $e->getMessage(),
            'trace' => get_class($e),
        ];
    }
});

Route::get('/mail-test-config', function () {
    return [
        'host' => config('mail.mailers.smtp.host'),
        'port' => config('mail.mailers.smtp.port'),
        'user' => config('mail.mailers.smtp.username'),
        'encryption' => config('mail.mailers.smtp.encryption'),
    ];
});

Route::get('/mail-env', function () {
    return [
        'MAIL_HOST' => env('MAIL_HOST'),
        'MAIL_PORT' => env('MAIL_PORT'),
        'MAIL_USERNAME' => env('MAIL_USERNAME'),
        'MAIL_PASSWORD_EXISTS' => ! empty(env('MAIL_PASSWORD')),
        'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION'),
    ];
});

Route::get('/mail-config-full', function () {
    return config('mail.mailers.smtp');
});
