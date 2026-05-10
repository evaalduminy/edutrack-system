<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ResearchController;
use App\Http\Controllers\Api\ArchiveController;
use App\Http\Controllers\Api\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — EduTrack & Archive System
|--------------------------------------------------------------------------
|
| All API routes are prefixed with /api/v1 and use Laravel Sanctum
| for authentication. Rate limiting is applied per group.
|
*/

// ─── API Version 1 ───
Route::prefix('v1')->group(function () {

    // ─── Public Routes (Guest) ───
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])
            ->middleware('throttle:5,1') // 5 attempts per minute
            ->name('api.auth.register');

        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:10,1') // Rate limiting for brute-force protection
            ->name('api.auth.login');
    });

    // ─── Protected Routes (Authenticated via Sanctum) ───
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::get('/auth/profile', [AuthController::class, 'profile'])->name('api.auth.profile');

        // Research CRUD
        Route::apiResource('research', ResearchController::class)->names([
            'index'   => 'api.research.index',
            'store'   => 'api.research.store',
            'show'    => 'api.research.show',
            'update'  => 'api.research.update',
            'destroy' => 'api.research.destroy',
        ]);
        Route::post('/research/check-duplicate', [ResearchController::class, 'checkDuplicate'])
            ->name('api.research.check-duplicate');

        // Archive
        Route::get('/archives', [ArchiveController::class, 'index'])->name('api.archives.index');
        Route::post('/archives', [ArchiveController::class, 'store'])->name('api.archives.store');
        Route::get('/archives/{archiveNumber}', [ArchiveController::class, 'show'])
            ->name('api.archives.show');

        // Thesis Upload (Queue Integration)
        Route::post('/thesis/upload', [\App\Http\Controllers\Api\ThesisUploadController::class, 'upload'])
            ->name('api.thesis.upload');

        // Dashboard (cached statistics)
        Route::get('/dashboard/stats', [DashboardController::class, 'index'])
            ->name('api.dashboard.stats');
        Route::post('/dashboard/refresh', [DashboardController::class, 'refresh'])
            ->name('api.dashboard.refresh');
    });
});
