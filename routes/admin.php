<?php

use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdminBillingController;
use App\Http\Controllers\Admin\AdminConfigController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminFeatureFlagController;
use App\Http\Controllers\Admin\AdminHealthController;
use App\Http\Controllers\Admin\AdminImpersonationController;
use App\Http\Controllers\Admin\AdminNotificationsController;
use App\Http\Controllers\Admin\AdminSocialAuthController;
use App\Http\Controllers\Admin\AdminSystemController;
use App\Http\Controllers\Admin\AdminTokensController;
use App\Http\Controllers\Admin\AdminTwoFactorController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Admin\AdminWebhooksController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin', 'throttle:60,1'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Metrics Dashboard (landing page)
        Route::get('/', AdminDashboardController::class)->name('dashboard');

        // Users Management
        Route::get('/users', [AdminUsersController::class, 'index'])
            ->name('users.index');
        Route::get('/users/{user}', [AdminUsersController::class, 'show'])
            ->withTrashed()
            ->middleware('throttle:30,1')
            ->name('users.show');
        Route::patch('/users/{user}/toggle-admin', [AdminUsersController::class, 'toggleAdmin'])
            ->middleware('throttle:10,1')
            ->name('users.toggle-admin');
        Route::patch('/users/{user}/toggle-active', [AdminUsersController::class, 'toggleActive'])
            ->withTrashed()
            ->middleware('throttle:10,1')
            ->name('users.toggle-active');

        // Bulk Actions
        Route::post('/users/bulk-deactivate', [AdminUsersController::class, 'bulkDeactivate'])
            ->middleware('throttle:10,1')
            ->name('users.bulk-deactivate');

        // Impersonation
        Route::post('/users/{user}/impersonate', [AdminImpersonationController::class, 'start'])
            ->withTrashed()
            ->middleware('throttle:5,1')
            ->name('users.impersonate');

        // Health Status
        Route::get('/health', AdminHealthController::class)->name('health');

        // Config Viewer
        Route::get('/config', AdminConfigController::class)->name('config');

        // Audit Logs
        Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/export', [AdminAuditLogController::class, 'export'])
            ->middleware('throttle:10,1')
            ->name('audit-logs.export');
        Route::get('/audit-logs/{auditLog}', [AdminAuditLogController::class, 'show'])->name('audit-logs.show');

        // System Info
        Route::get('/system', AdminSystemController::class)->name('system');

        // Feature Flags Management
        Route::get('/feature-flags', [AdminFeatureFlagController::class, 'index'])->name('feature-flags.index');
        Route::patch('/feature-flags/{flag}', [AdminFeatureFlagController::class, 'updateGlobal'])
            ->where('flag', '[a-z_]+')
            ->middleware('throttle:30,1')
            ->name('feature-flags.update-global');
        Route::delete('/feature-flags/{flag}', [AdminFeatureFlagController::class, 'removeGlobal'])
            ->where('flag', '[a-z_]+')
            ->middleware('throttle:30,1')
            ->name('feature-flags.remove-global');
        Route::get('/feature-flags/{flag}/users', [AdminFeatureFlagController::class, 'getTargetedUsers'])
            ->where('flag', '[a-z_]+')
            ->name('feature-flags.users');
        Route::post('/feature-flags/{flag}/users', [AdminFeatureFlagController::class, 'addUserOverride'])
            ->where('flag', '[a-z_]+')
            ->middleware('throttle:30,1')
            ->name('feature-flags.add-user');
        Route::delete('/feature-flags/{flag}/users/{user}', [AdminFeatureFlagController::class, 'removeUserOverride'])
            ->where('flag', '[a-z_]+')
            ->middleware('throttle:30,1')
            ->name('feature-flags.remove-user');
        Route::delete('/feature-flags/{flag}/users', [AdminFeatureFlagController::class, 'removeAllUserOverrides'])
            ->where('flag', '[a-z_]+')
            ->middleware('throttle:30,1')
            ->name('feature-flags.remove-all-users');
        Route::get('/feature-flags/search-users', [AdminFeatureFlagController::class, 'searchUsers'])
            ->name('feature-flags.search-users');

        // Feature-gated admin sections
        if (config('features.billing.enabled')) {
            Route::get('/billing', [AdminBillingController::class, 'dashboard'])->name('billing.dashboard');
            Route::get('/billing/subscriptions', [AdminBillingController::class, 'subscriptions'])->name('billing.subscriptions');
            Route::get('/billing/subscriptions/{subscription}', [AdminBillingController::class, 'show'])->name('billing.show');
        }

        if (config('features.webhooks.enabled')) {
            Route::get('/webhooks', AdminWebhooksController::class)->name('webhooks');
        }

        if (config('features.api_tokens.enabled')) {
            Route::get('/tokens', AdminTokensController::class)->name('tokens');
        }

        if (config('features.social_auth.enabled')) {
            Route::get('/social-auth', AdminSocialAuthController::class)->name('social-auth');
        }

        if (config('features.notifications.enabled')) {
            Route::get('/notifications', AdminNotificationsController::class)->name('notifications');
        }

        if (config('features.two_factor.enabled')) {
            Route::get('/two-factor', AdminTwoFactorController::class)->name('two-factor');
        }
    });

// Stop impersonation — outside admin middleware because impersonated user is not admin
Route::middleware(['auth'])
    ->post('/admin/impersonate/stop', [AdminImpersonationController::class, 'stop'])
    ->name('admin.impersonation.stop');
