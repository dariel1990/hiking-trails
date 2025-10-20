<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrailController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminTrailController;

// PUBLIC ROUTES (No authentication required)
Route::get('/', [TrailController::class, 'home'])->name('home');
Route::get('/trails', [TrailController::class, 'index'])->name('trails.index');
Route::get('/trails/{trail}', [TrailController::class, 'show'])->name('trails.show');
Route::get('/map', [TrailController::class, 'map'])->name('map');

// ADMIN ROUTES
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest routes (login)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminController::class, 'loginForm'])->name('login');
        Route::post('/login', [AdminController::class, 'login'])->name('login.post');
    });
    
    // Protected admin routes
    Route::middleware(['auth', 'admin', 'throttle:10,1'])->group(function () {
        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // Trail management
        Route::resource('trails', AdminTrailController::class);
        Route::post('/trails/{trail}/photos', [AdminTrailController::class, 'uploadPhotos'])->name('trails.photos.store');
        Route::delete('/photos/{photo}', [AdminTrailController::class, 'deletePhoto'])->name('photos.delete');

        // Media Management Routes
        // Route::get('/trails/{trail}/media', [AdminTrailController::class, 'mediaManagement'])->name('trails.media');
        // Route::post('/trails/{trail}/media/upload', [AdminTrailController::class, 'uploadMedia'])->name('trails.media.upload');
        // Route::post('/trails/{trail}/media/{media}/link/{feature}', [AdminTrailController::class, 'linkMediaToFeature'])->name('admin.trails.media.link');
        // Route::delete('/trails/{trail}/media/{media}/link/{feature}', [AdminTrailController::class, 'unlinkMediaFromFeature'])->name('admin.trails.media.unlink');
        // Route::put('/trails/{trail}/media/{media}', [AdminTrailController::class, 'updateMedia'])->name('trails.media.update');
        // Route::delete('/trails/{trail}/media/{media}', [AdminTrailController::class, 'deleteMedia'])->name('trails.media.delete');

        // GPX API endpoints (add these)
        Route::post('/trails/gpx/preview', [AdminTrailController::class, 'previewGpx'])
            ->name('trails.gpx.preview');
        Route::post('/trails/{trail}/gpx/compare', [AdminTrailController::class, 'compareGpx'])
            ->name('trails.gpx.compare');
    });

   
});