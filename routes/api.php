<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ConfigController;
use App\Http\Controllers\Api\V1\BannerController;
use App\Http\Controllers\Api\V1\ServiceCategoryController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\VersionController;
use App\Http\Controllers\Api\V1\NavigationController;
use App\Http\Controllers\Api\V1\OnboardingController;
use App\Http\Controllers\Api\V1\MediaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API V1 Routes
Route::prefix('v1')->group(function () {
    
    // Authentication routes (public with rate limiting)
    Route::middleware('throttle:auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });
    
    // Public routes
    Route::get('/config', [ConfigController::class, 'index']);
    Route::get('/config/{key}', [ConfigController::class, 'show']);
    Route::get('/theme', [ConfigController::class, 'theme']);
    
    Route::get('/banners', [BannerController::class, 'index']);
    Route::get('/banners/{id}', [BannerController::class, 'show']);
    
    Route::get('/categories', [ServiceCategoryController::class, 'index']);
    Route::get('/categories/{id}', [ServiceCategoryController::class, 'show']);
    
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/featured', [ServiceController::class, 'featured']);
    Route::get('/services/{id}', [ServiceController::class, 'show']);
    
    Route::get('/pages/{slug}', [PageController::class, 'show']);
    Route::get('/faqs', [PageController::class, 'faqs']);
    
    Route::get('/navigation', [NavigationController::class, 'tree']);
    Route::get('/onboarding', [OnboardingController::class, 'screens']);
    
    Route::post('/version-check', [VersionController::class, 'check']);
    
    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        // User routes
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        
        // Media routes with upload rate limiting
        Route::middleware('throttle:uploads')->group(function () {
            Route::post('/media/avatar', [MediaController::class, 'uploadAvatar']);
            Route::delete('/media/avatar', [MediaController::class, 'deleteAvatar']);
        });
        Route::get('/media/user', [MediaController::class, 'getUserMedia']);
        
        // Bookings routes (to be implemented)
        // Route::resource('bookings', BookingController::class);
    });
});
