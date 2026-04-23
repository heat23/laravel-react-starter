<?php

// Inherited middleware: auth, verified, admin, throttle:60,1,admin:
// Permission: admin (read/write); super_admin (delete); feature-gated for indexnow

use App\Http\Controllers\Admin\AdminIndexNowController;
use App\Http\Controllers\Admin\AdminRoadmapController;
use Illuminate\Support\Facades\Route;

// Roadmap Management — export/create before {roadmapEntry} wildcard
Route::get('/roadmap/export', [AdminRoadmapController::class, 'export'])
    ->middleware('throttle:admin-write')
    ->name('roadmap.export');
Route::get('/roadmap', [AdminRoadmapController::class, 'index'])
    ->middleware('throttle:admin-read')
    ->name('roadmap.index');
Route::get('/roadmap/create', [AdminRoadmapController::class, 'create'])
    ->middleware('throttle:admin-read')
    ->name('roadmap.create');
Route::post('/roadmap', [AdminRoadmapController::class, 'store'])
    ->middleware('throttle:admin-write')
    ->name('roadmap.store');
Route::post('/roadmap/reorder', [AdminRoadmapController::class, 'reorder'])
    ->middleware('throttle:admin-write')
    ->name('roadmap.reorder');
Route::patch('/roadmap/{roadmapEntry}', [AdminRoadmapController::class, 'update'])
    ->middleware('throttle:admin-write')
    ->name('roadmap.update');
Route::delete('/roadmap/{roadmapEntry}', [AdminRoadmapController::class, 'destroy'])
    ->middleware(['throttle:admin-write', 'super_admin'])
    ->name('roadmap.destroy');

if (config('features.indexnow.enabled')) {
    Route::get('/indexnow', AdminIndexNowController::class)
        ->middleware('throttle:admin-read')
        ->name('indexnow.index');
    Route::get('/indexnow/{submission}', [AdminIndexNowController::class, 'show'])
        ->middleware('throttle:admin-read')
        ->name('indexnow.show');
    Route::post('/indexnow/{submission}/retry', [AdminIndexNowController::class, 'retry'])
        ->middleware(['throttle:admin-write', 'super_admin'])
        ->name('indexnow.retry');
}
