<?php

use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\IndexNowKeyFileController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

Route::get('/favicon.ico', function () {
    $path = public_path('favicon.ico');
    abort_unless(file_exists($path), 404);

    return response()->file($path, [
        'Cache-Control' => 'public, max-age=604800, immutable',
    ]);
})->name('favicon');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/llms.txt', [SeoController::class, 'llms'])->name('llms');

if (config('features.indexnow.enabled', false)) {
    Route::get('/{key}.txt', IndexNowKeyFileController::class)
        ->where('key', '[A-Za-z0-9\-]{8,128}')
        ->name('indexnow.key');
}

Route::get('/health', HealthCheckController::class)->name('health');
