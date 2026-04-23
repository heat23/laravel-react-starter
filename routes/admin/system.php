<?php

// Inherited middleware: auth, verified, admin, throttle:60,1,admin:
// Permission: admin (read); super_admin (destructive/sensitive); various feature flags

use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdminCacheController;
use App\Http\Controllers\Admin\AdminConfigController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDataHealthController;
use App\Http\Controllers\Admin\AdminFailedJobsController;
use App\Http\Controllers\Admin\AdminFeatureFlagController;
use App\Http\Controllers\Admin\AdminHealthController;
use App\Http\Controllers\Admin\AdminNotificationsController;
use App\Http\Controllers\Admin\AdminScheduleController;
use App\Http\Controllers\Admin\AdminSocialAuthController;
use App\Http\Controllers\Admin\AdminSystemController;
use App\Http\Controllers\Admin\AdminTwoFactorController;
use Illuminate\Support\Facades\Route;

// Metrics Dashboard (landing page)
Route::get('/', AdminDashboardController::class)->middleware('throttle:admin-read')->name('dashboard');

// Health Status
Route::get('/health', AdminHealthController::class)->middleware('throttle:admin-read')->name('health');

// Config Viewer
Route::get('/config', AdminConfigController::class)->middleware('throttle:admin-read')->name('config');

// System Info — restricted to super_admin: exposes exact package versions, PHP version, OS, DB version
Route::get('/system', AdminSystemController::class)->middleware(['throttle:admin-read', 'super_admin'])->name('system');

// Failed Jobs Management — bulk routes first to avoid {id} wildcard capture
Route::get('/failed-jobs', [AdminFailedJobsController::class, 'index'])
    ->middleware('throttle:admin-read')
    ->name('failed-jobs.index');
Route::post('/failed-jobs/bulk-retry', [AdminFailedJobsController::class, 'bulkRetry'])
    ->middleware('throttle:admin-write')
    ->name('failed-jobs.bulk-retry');
Route::delete('/failed-jobs/bulk', [AdminFailedJobsController::class, 'bulkDelete'])
    ->middleware('throttle:admin-write')
    ->name('failed-jobs.bulk-destroy');
Route::get('/failed-jobs/{id}', [AdminFailedJobsController::class, 'show'])
    ->middleware('throttle:admin-read')
    ->name('failed-jobs.show');
Route::post('/failed-jobs/{id}/retry', [AdminFailedJobsController::class, 'retry'])
    ->middleware('throttle:admin-write')
    ->name('failed-jobs.retry');
Route::delete('/failed-jobs/{id}', [AdminFailedJobsController::class, 'destroy'])
    ->middleware('throttle:admin-write')
    ->name('failed-jobs.destroy');

// Data Health Checks
Route::get('/data-health', [AdminDataHealthController::class, 'index'])
    ->middleware('throttle:admin-read')
    ->name('data-health.index');

// Feature Flags Management
Route::get('/feature-flags', [AdminFeatureFlagController::class, 'index'])
    ->middleware('throttle:admin-read')
    ->name('feature-flags.index');
Route::patch('/feature-flags/{flag}', [AdminFeatureFlagController::class, 'updateGlobal'])
    ->where('flag', '[a-z_]+')
    ->middleware(['throttle:admin-write', 'super_admin'])
    ->name('feature-flags.update-global');
Route::delete('/feature-flags/{flag}', [AdminFeatureFlagController::class, 'removeGlobal'])
    ->where('flag', '[a-z_]+')
    ->middleware(['throttle:admin-write', 'super_admin'])
    ->name('feature-flags.remove-global');
Route::get('/feature-flags/{flag}/users', [AdminFeatureFlagController::class, 'getTargetedUsers'])
    ->where('flag', '[a-z_]+')
    ->middleware('throttle:admin-read')
    ->name('feature-flags.users');
Route::post('/feature-flags/{flag}/users', [AdminFeatureFlagController::class, 'addUserOverride'])
    ->where('flag', '[a-z_]+')
    ->middleware(['throttle:admin-write', 'super_admin'])
    ->name('feature-flags.add-user');
Route::delete('/feature-flags/{flag}/users/{user}', [AdminFeatureFlagController::class, 'removeUserOverride'])
    ->where('flag', '[a-z_]+')
    ->middleware(['throttle:admin-write', 'super_admin'])
    ->name('feature-flags.remove-user');
Route::delete('/feature-flags/{flag}/users', [AdminFeatureFlagController::class, 'removeAllUserOverrides'])
    ->where('flag', '[a-z_]+')
    ->middleware(['throttle:admin-write', 'super_admin'])
    ->name('feature-flags.remove-all-users');
Route::get('/feature-flags/search-users', [AdminFeatureFlagController::class, 'searchUsers'])
    ->middleware('throttle:admin-read')
    ->name('feature-flags.search-users');

// Cache Management
Route::get('/cache', [AdminCacheController::class, 'index'])
    ->middleware('throttle:admin-read')
    ->name('cache.index');
Route::post('/cache/flush', [AdminCacheController::class, 'flush'])
    ->middleware(['throttle:admin-write', 'super_admin'])
    ->name('cache.flush');

// Schedule Monitor
Route::get('/schedule', AdminScheduleController::class)
    ->middleware('throttle:admin-read')
    ->name('schedule');

// Audit Logs
Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])
    ->middleware('throttle:admin-read')
    ->name('audit-logs.index');
Route::get('/audit-logs/export', [AdminAuditLogController::class, 'export'])
    ->middleware('throttle:admin-write')
    ->name('audit-logs.export');
Route::get('/audit-logs/{auditLog}', [AdminAuditLogController::class, 'show'])
    ->middleware('throttle:admin-read')
    ->name('audit-logs.show');

if (config('features.social_auth.enabled')) {
    Route::get('/social-auth', AdminSocialAuthController::class)
        ->middleware('throttle:admin-read')
        ->name('social-auth');
}

if (config('features.notifications.enabled')) {
    Route::get('/notifications', AdminNotificationsController::class)
        ->middleware('throttle:admin-read')
        ->name('notifications');
    Route::post('/notifications/send', [AdminNotificationsController::class, 'send'])
        ->middleware(['throttle:admin-write', 'super_admin'])
        ->name('notifications.send');
}

if (config('features.two_factor.enabled')) {
    Route::get('/two-factor', AdminTwoFactorController::class)
        ->middleware('throttle:admin-read')
        ->name('two-factor');
}
