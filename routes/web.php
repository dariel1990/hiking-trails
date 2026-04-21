<?php

use App\Http\Controllers\Admin\ActivityTypeController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminTrailController;
use App\Http\Controllers\Admin\AdminTrailNetworkController;
use App\Http\Controllers\Admin\BusinessController;
use App\Http\Controllers\Admin\FacilityController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\BusinessPublicController;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\TrailController;
use App\Http\Controllers\TrailNetworkController;
use Illuminate\Support\Facades\Route;

// PUBLIC ROUTES (No authentication required)
Route::get('/', [TrailController::class, 'home'])->name('home');
Route::get('/trails', [TrailController::class, 'index'])->name('trails.index');
Route::get('/trails/{trail}', [TrailController::class, 'show'])->name('trails.show');
Route::get('/map', [TrailController::class, 'map'])->name('map');
Route::get('/map-v2', [TrailController::class, 'mapV2'])->name('map.v2');

Route::get('/privacy-policy', fn () => view('privacy-policy'))->name('privacy-policy');

// Public Business Routes
Route::get('/businesses', [BusinessPublicController::class, 'index'])->name('businesses.public.index');
Route::get('/businesses/{business:slug}', [BusinessPublicController::class, 'show'])->name('businesses.public.show');

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
    Route::middleware(['auth', 'admin', 'throttle:10,1'])->group(function () {
        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // User management
        Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        // Trail management
        Route::resource('trails', AdminTrailController::class);
        Route::resource('activity-types', ActivityTypeController::class);
        Route::patch('/trails/{trail}/toggle-featured', [AdminTrailController::class, 'toggleFeatured'])->name('trails.toggle-featured')->middleware('throttle:10,1');

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

        // Facilities Management (Standalone - Global)
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

Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

// Events Routes (Public - No admin middleware)
Route::get('/events', [EventsController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventsController::class, 'show'])->name('events.show');
Route::get('/events/{event}/calendar', [EventsController::class, 'downloadCalendar'])->name('events.calendar');
Route::get('/events/{event}/details', [EventsController::class, 'getEventDetails'])->name('events.details');
