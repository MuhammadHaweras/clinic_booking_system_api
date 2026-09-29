<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Provider\ProviderApplicationController;
use App\Http\Controllers\Provider\ProviderProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// -------------------------------------------------------------------------
// Public auth routes
// -------------------------------------------------------------------------

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// -------------------------------------------------------------------------
// Authenticated routes
// -------------------------------------------------------------------------

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());

    Route::post('/logout', [AuthController::class, 'logout']);

    // -------------------------------------------------------------------------
    // Provider onboarding — submit an application
    // -------------------------------------------------------------------------

    Route::post('/provider/apply', [ProviderApplicationController::class, 'store'])
        ->name('provider.apply');

    // View own application status (provider / applicant)
    Route::get('/provider/me', [ProviderProfileController::class, 'me'])
        ->name('provider.me');

    // -------------------------------------------------------------------------
    // Admin — manage provider applications
    // -------------------------------------------------------------------------

    Route::prefix('admin')->name('admin.')->group(function () {
        // List all applications (optional ?status= filter)
        Route::get('/provider-applications', [ProviderProfileController::class, 'index'])
            ->name('provider-applications.index');

        // View a specific application
        Route::get('/provider-applications/{providerProfile}', [ProviderProfileController::class, 'show'])
            ->name('provider-applications.show');

        // Approve or reject an application
        Route::post('/provider-applications/{providerProfile}/review', [ProviderProfileController::class, 'review'])
            ->name('provider-applications.review');
    });
});
