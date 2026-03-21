<?php

use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdminBillingController;
use App\Http\Controllers\Admin\AdminCacheController;
use App\Http\Controllers\Admin\AdminConfigController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDataHealthController;
use App\Http\Controllers\Admin\AdminFailedJobsController;
use App\Http\Controllers\Admin\AdminFeatureFlagController;
use App\Http\Controllers\Admin\AdminFeedbackController;
use App\Http\Controllers\Admin\AdminHealthController;
use App\Http\Controllers\Admin\AdminImpersonationController;
use App\Http\Controllers\Admin\AdminNotificationsController;
use App\Http\Controllers\Admin\AdminScheduleController;
use App\Http\Controllers\Admin\AdminSessionsController;
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
        Route::get('/users/export', [AdminUsersController::class, 'export'])
            ->middleware('throttle:10,1')
            ->name('users.export');
        Route::get('/users/create', [AdminUsersController::class, 'create'])
            ->name('users.create');
        Route::post('/users', [AdminUsersController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('users.store');
        Route::get('/users', [AdminUsersController::class, 'index'])
            ->name('users.index');
        Route::get('/users/{user}', [AdminUsersController::class, 'show'])
            ->withTrashed()
            ->middleware('throttle:30,1')
            ->name('users.show');
        Route::patch('/users/{user}', [AdminUsersController::class, 'update'])
            ->withTrashed()
            ->middleware('throttle:10,1')
            ->name('users.update');
        Route::patch('/users/{user}/toggle-admin', [AdminUsersController::class, 'toggleAdmin'])
            ->middleware(['throttle:10,1', 'super_admin'])
            ->name('users.toggle-admin');
        Route::patch('/users/{user}/toggle-active', [AdminUsersController::class, 'toggleActive'])
            ->withTrashed()
            ->middleware('throttle:10,1')
            ->name('users.toggle-active');
        Route::post('/users/{user}/send-password-reset', [AdminUsersController::class, 'sendPasswordReset'])
            ->middleware('throttle:5,1')
            ->name('users.send-password-reset');

        // Bulk Actions
        Route::post('/users/bulk-deactivate', [AdminUsersController::class, 'bulkDeactivate'])
            ->middleware('throttle:10,1')
            ->name('users.bulk-deactivate');

        // Impersonation — super_admin only
        Route::post('/users/{user}/impersonate', [AdminImpersonationController::class, 'start'])
            ->withTrashed()
            ->middleware(['throttle:5,1', 'super_admin'])
            ->name('users.impersonate');

        // Health Status
        Route::get('/health', AdminHealthController::class)->name('health');

        // Config Viewer
        Route::get('/config', AdminConfigController::class)->name('config');

        // Feedback Inbox
        Route::get('/feedback', [AdminFeedbackController::class, 'index'])->name('feedback.index');
        Route::get('/feedback/{feedback}', [AdminFeedbackController::class, 'show'])->name('feedback.show');
        Route::patch('/feedback/{feedback}', [AdminFeedbackController::class, 'update'])
            ->middleware('throttle:30,1')
            ->name('feedback.update');
        Route::delete('/feedback/{feedback}', [AdminFeedbackController::class, 'destroy'])
            ->middleware(['throttle:10,1', 'super_admin'])
            ->name('feedback.destroy');

        // Audit Logs
        Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/export', [AdminAuditLogController::class, 'export'])
            ->middleware('throttle:10,1')
            ->name('audit-logs.export');
        Route::get('/audit-logs/{auditLog}', [AdminAuditLogController::class, 'show'])->name('audit-logs.show');

        // System Info
        Route::get('/system', AdminSystemController::class)->name('system');

        // Failed Jobs Management
        Route::get('/failed-jobs', [AdminFailedJobsController::class, 'index'])->name('failed-jobs.index');
        Route::get('/failed-jobs/{id}', [AdminFailedJobsController::class, 'show'])->name('failed-jobs.show');
        Route::post('/failed-jobs/{id}/retry', [AdminFailedJobsController::class, 'retry'])
            ->middleware('throttle:10,1')
            ->name('failed-jobs.retry');
        Route::delete('/failed-jobs/{id}', [AdminFailedJobsController::class, 'destroy'])
            ->middleware('throttle:10,1')
            ->name('failed-jobs.destroy');

        // Data Health Checks
        Route::get('/data-health', [AdminDataHealthController::class, 'index'])->name('data-health.index');

        // Feature Flags Management
        Route::get('/feature-flags', [AdminFeatureFlagController::class, 'index'])->name('feature-flags.index');
        Route::patch('/feature-flags/{flag}', [AdminFeatureFlagController::class, 'updateGlobal'])
            ->where('flag', '[a-z_]+')
            ->middleware(['throttle:30,1', 'super_admin'])
            ->name('feature-flags.update-global');
        Route::delete('/feature-flags/{flag}', [AdminFeatureFlagController::class, 'removeGlobal'])
            ->where('flag', '[a-z_]+')
            ->middleware(['throttle:30,1', 'super_admin'])
            ->name('feature-flags.remove-global');
        Route::get('/feature-flags/{flag}/users', [AdminFeatureFlagController::class, 'getTargetedUsers'])
            ->where('flag', '[a-z_]+')
            ->name('feature-flags.users');
        Route::post('/feature-flags/{flag}/users', [AdminFeatureFlagController::class, 'addUserOverride'])
            ->where('flag', '[a-z_]+')
            ->middleware(['throttle:30,1', 'super_admin'])
            ->name('feature-flags.add-user');
        Route::delete('/feature-flags/{flag}/users/{user}', [AdminFeatureFlagController::class, 'removeUserOverride'])
            ->where('flag', '[a-z_]+')
            ->middleware(['throttle:30,1', 'super_admin'])
            ->name('feature-flags.remove-user');
        Route::delete('/feature-flags/{flag}/users', [AdminFeatureFlagController::class, 'removeAllUserOverrides'])
            ->where('flag', '[a-z_]+')
            ->middleware(['throttle:30,1', 'super_admin'])
            ->name('feature-flags.remove-all-users');
        Route::get('/feature-flags/search-users', [AdminFeatureFlagController::class, 'searchUsers'])
            ->name('feature-flags.search-users');

        // Session Manager
        Route::get('/sessions', [AdminSessionsController::class, 'index'])->name('sessions.index');
        Route::delete('/sessions/{userId}', [AdminSessionsController::class, 'destroy'])
            ->middleware(['throttle:10,1', 'super_admin'])
            ->name('sessions.destroy');

        // Cache Management
        Route::get('/cache', [AdminCacheController::class, 'index'])->name('cache.index');
        Route::post('/cache/flush', [AdminCacheController::class, 'flush'])
            ->middleware(['throttle:10,1', 'super_admin'])
            ->name('cache.flush');

        // Schedule Monitor
        Route::get('/schedule', AdminScheduleController::class)->name('schedule');

        // Feature-gated admin sections (auth via group middleware on line 21)
        if (config('features.billing.enabled')) {
            Route::get('/billing', [AdminBillingController::class, 'dashboard'])->name('billing.dashboard');
            Route::get('/billing/subscriptions/export', [AdminBillingController::class, 'export'])
                ->middleware('throttle:10,1')
                ->name('billing.subscriptions.export');
            Route::get('/billing/subscriptions', [AdminBillingController::class, 'subscriptions'])->name('billing.subscriptions');
            Route::get('/billing/subscriptions/{subscription}', [AdminBillingController::class, 'show'])->name('billing.show');
        }

        if (config('features.webhooks.enabled')) {
            Route::get('/webhooks', AdminWebhooksController::class)->name('webhooks');
            Route::get('/webhooks/endpoints', [AdminWebhooksController::class, 'endpoints'])->name('webhooks.endpoints');
            Route::patch('/webhooks/endpoints/{id}/restore', [AdminWebhooksController::class, 'restoreEndpoint'])
                ->middleware('throttle:10,1')
                ->name('webhooks.endpoints.restore');
        }

        if (config('features.api_tokens.enabled')) {
            Route::get('/tokens', AdminTokensController::class)->name('tokens');
            Route::get('/tokens/list', [AdminTokensController::class, 'index'])->name('tokens.index');
            Route::delete('/tokens/{id}', [AdminTokensController::class, 'revoke'])
                ->middleware('throttle:10,1')
                ->name('tokens.revoke');
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
