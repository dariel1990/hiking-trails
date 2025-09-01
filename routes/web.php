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

// API Routes for map data
Route::get('/api/trails', [TrailController::class, 'apiIndex']);
Route::get('/api/trails/{trail}', [TrailController::class, 'apiShow']);

// ADMIN ROUTES
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest routes (login)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminController::class, 'loginForm'])->name('login');
        Route::post('/login', [AdminController::class, 'login'])->name('login.post');
    });
    
    // Protected admin routes
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // Trail management
        Route::resource('trails', AdminTrailController::class);
        Route::post('/trails/{trail}/photos', [AdminTrailController::class, 'uploadPhotos'])->name('trails.photos.store');
        Route::delete('/photos/{photo}', [AdminTrailController::class, 'deletePhoto'])->name('photos.delete');
    });
});