<?php

use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdminBillingController;
use App\Http\Controllers\Admin\AdminCacheController;
use App\Http\Controllers\Admin\AdminConfigController;
use App\Http\Controllers\Admin\AdminContactSubmissionsController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDataHealthController;
use App\Http\Controllers\Admin\AdminEmailSendLogController;
use App\Http\Controllers\Admin\AdminFailedJobsController;
use App\Http\Controllers\Admin\AdminFeatureFlagController;
use App\Http\Controllers\Admin\AdminFeedbackController;
use App\Http\Controllers\Admin\AdminHealthController;
use App\Http\Controllers\Admin\AdminImpersonationController;
use App\Http\Controllers\Admin\AdminNotificationsController;
use App\Http\Controllers\Admin\AdminNpsResponsesController;
use App\Http\Controllers\Admin\AdminProductAnalyticsController;
use App\Http\Controllers\Admin\AdminRoadmapController;
use App\Http\Controllers\Admin\AdminScheduleController;
use App\Http\Controllers\Admin\AdminSessionsController;
use App\Http\Controllers\Admin\AdminSocialAuthController;
use App\Http\Controllers\Admin\AdminSystemController;
use App\Http\Controllers\Admin\AdminTokensController;
use App\Http\Controllers\Admin\AdminTwoFactorController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Admin\AdminWebhooksController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin', 'throttle:60,1,admin:'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Metrics Dashboard (landing page)
        Route::get('/', AdminDashboardController::class)->middleware('throttle:30,1')->name('dashboard');

        // Product Analytics
        Route::get('/analytics', AdminProductAnalyticsController::class)->middleware('throttle:30,1')->name('analytics');

        // Users Management
        Route::get('/users/export', [AdminUsersController::class, 'export'])
            ->middleware('throttle:10,1')
            ->name('users.export');
        Route::get('/users/create', [AdminUsersController::class, 'create'])
            ->middleware('throttle:30,1')
            ->name('users.create');
        Route::post('/users', [AdminUsersController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('users.store');
        Route::get('/users', [AdminUsersController::class, 'index'])
            ->middleware('throttle:30,1')
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
            ->middleware(['throttle:10,1', 'super_admin'])
            ->name('users.toggle-active');
        Route::post('/users/{user}/send-password-reset', [AdminUsersController::class, 'sendPasswordReset'])
            ->middleware('throttle:5,1')
            ->name('users.send-password-reset');

        // Bulk Actions
        Route::post('/users/bulk-deactivate', [AdminUsersController::class, 'bulkDeactivate'])
            ->middleware(['throttle:10,1', 'super_admin'])
            ->name('users.bulk-deactivate');
        Route::post('/users/bulk-restore', [AdminUsersController::class, 'bulkRestore'])
            ->middleware(['throttle:10,1', 'super_admin'])
            ->name('users.bulk-restore');

        // Impersonation — super_admin only
        Route::post('/users/{user}/impersonate', [AdminImpersonationController::class, 'start'])
            ->withTrashed()
            ->middleware(['throttle:5,1', 'super_admin'])
            ->name('users.impersonate');

        // Health Status
        Route::get('/health', AdminHealthController::class)->middleware('throttle:30,1')->name('health');

        // Config Viewer
        Route::get('/config', AdminConfigController::class)->middleware('throttle:30,1')->name('config');

        // Feedback Inbox — bulk/export routes before {feedback} wildcard
        Route::get('/feedback/export', [AdminFeedbackController::class, 'export'])
            ->middleware('throttle:10,1')
            ->name('feedback.export');
        Route::post('/feedback/bulk-update', [AdminFeedbackController::class, 'bulkUpdate'])
            ->middleware('throttle:10,1')
            ->name('feedback.bulk-update');
        Route::get('/feedback', [AdminFeedbackController::class, 'index'])->name('feedback.index');
        Route::get('/feedback/{feedback}', [AdminFeedbackController::class, 'show'])->name('feedback.show');
        Route::patch('/feedback/{feedback}', [AdminFeedbackController::class, 'update'])
            ->middleware('throttle:30,1')
            ->name('feedback.update');
        Route::delete('/feedback/{feedback}', [AdminFeedbackController::class, 'destroy'])
            ->middleware(['throttle:10,1', 'super_admin'])
            ->name('feedback.destroy');

        // Contact Submissions — export/bulk before {contactSubmission} wildcard
        Route::get('/contact-submissions/export', [AdminContactSubmissionsController::class, 'export'])
            ->middleware('throttle:10,1')
            ->name('contact-submissions.export');
        Route::post('/contact-submissions/bulk-update', [AdminContactSubmissionsController::class, 'bulkUpdate'])
            ->middleware('throttle:30,1')
            ->name('contact-submissions.bulk-update');
        Route::get('/contact-submissions', [AdminContactSubmissionsController::class, 'index'])->name('contact-submissions.index');
        Route::get('/contact-submissions/{contactSubmission}', [AdminContactSubmissionsController::class, 'show'])->name('contact-submissions.show');
        Route::patch('/contact-submissions/{contactSubmission}', [AdminContactSubmissionsController::class, 'update'])
            ->middleware('throttle:30,1')
            ->name('contact-submissions.update');
        Route::delete('/contact-submissions/{contactSubmission}', [AdminContactSubmissionsController::class, 'destroy'])
            ->middleware(['throttle:10,1', 'super_admin'])
            ->name('contact-submissions.destroy');

        // NPS Responses — export before index to avoid prefix collision
        Route::get('/nps-responses/export', [AdminNpsResponsesController::class, 'export'])
            ->middleware('throttle:10,1')
            ->name('nps-responses.export');
        Route::get('/nps-responses', [AdminNpsResponsesController::class, 'index'])->name('nps-responses.index');

        // Email Send Logs — export before index to avoid prefix collision
        Route::get('/email-send-logs/export', [AdminEmailSendLogController::class, 'export'])
            ->middleware('throttle:10,1')
            ->name('email-send-logs.export');
        Route::get('/email-send-logs', [AdminEmailSendLogController::class, 'index'])->name('email-send-logs.index');

        // Audit Logs
        Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/export', [AdminAuditLogController::class, 'export'])
            ->middleware('throttle:10,1')
            ->name('audit-logs.export');
        Route::get('/audit-logs/{auditLog}', [AdminAuditLogController::class, 'show'])->name('audit-logs.show');

        // System Info — restricted to super_admin: exposes exact package versions, PHP version, OS, DB version
        Route::get('/system', AdminSystemController::class)->middleware('super_admin')->name('system');

        // Failed Jobs Management — bulk routes first to avoid {id} wildcard capture
        Route::get('/failed-jobs', [AdminFailedJobsController::class, 'index'])->name('failed-jobs.index');
        Route::post('/failed-jobs/bulk-retry', [AdminFailedJobsController::class, 'bulkRetry'])
            ->middleware('throttle:10,1')
            ->name('failed-jobs.bulk-retry');
        Route::delete('/failed-jobs/bulk', [AdminFailedJobsController::class, 'bulkDelete'])
            ->middleware('throttle:10,1')
            ->name('failed-jobs.bulk-destroy');
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

        // Roadmap Management — export/create before {roadmapEntry} wildcard
        Route::get('/roadmap/export', [AdminRoadmapController::class, 'export'])
            ->middleware('throttle:10,1')
            ->name('roadmap.export');
        Route::get('/roadmap', [AdminRoadmapController::class, 'index'])->name('roadmap.index');
        Route::get('/roadmap/create', [AdminRoadmapController::class, 'create'])->name('roadmap.create');
        Route::post('/roadmap', [AdminRoadmapController::class, 'store'])->middleware('throttle:30,1')->name('roadmap.store');
        Route::post('/roadmap/reorder', [AdminRoadmapController::class, 'reorder'])->middleware('throttle:30,1')->name('roadmap.reorder');
        Route::patch('/roadmap/{roadmapEntry}', [AdminRoadmapController::class, 'update'])->middleware('throttle:30,1')->name('roadmap.update');
        Route::delete('/roadmap/{roadmapEntry}', [AdminRoadmapController::class, 'destroy'])->middleware(['throttle:10,1', 'super_admin'])->name('roadmap.destroy');

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
            Route::get('/webhooks/incoming', [AdminWebhooksController::class, 'incomingWebhooks'])->name('webhooks.incoming');
            Route::get('/webhooks/endpoints', [AdminWebhooksController::class, 'endpoints'])->name('webhooks.endpoints');
            Route::patch('/webhooks/endpoints/{id}/restore', [AdminWebhooksController::class, 'restoreEndpoint'])
                ->middleware(['throttle:10,1', 'super_admin'])
                ->name('webhooks.endpoints.restore');
            Route::get('/webhooks/deliveries/{id}', [AdminWebhooksController::class, 'showDelivery'])->name('webhooks.deliveries.show');
        }

        if (config('features.api_tokens.enabled')) {
            Route::get('/tokens', AdminTokensController::class)->name('tokens');
            Route::get('/tokens/export', [AdminTokensController::class, 'export'])
                ->middleware('throttle:10,1')
                ->name('tokens.export');
            Route::get('/tokens/list', [AdminTokensController::class, 'index'])->name('tokens.index');
            Route::delete('/tokens/{id}', [AdminTokensController::class, 'revoke'])
                ->middleware(['throttle:10,1', 'super_admin'])
                ->name('tokens.revoke');
        }

        if (config('features.social_auth.enabled')) {
            Route::get('/social-auth', AdminSocialAuthController::class)->name('social-auth');
        }

        if (config('features.notifications.enabled')) {
            Route::get('/notifications', AdminNotificationsController::class)->name('notifications');
            Route::post('/notifications/send', [AdminNotificationsController::class, 'send'])
                ->middleware(['throttle:10,1', 'super_admin'])
                ->name('notifications.send');
        }

        if (config('features.two_factor.enabled')) {
            Route::get('/two-factor', AdminTwoFactorController::class)->name('two-factor');
        }
    });

// Stop impersonation — outside admin middleware because impersonated user is not admin
Route::middleware(['auth', 'throttle:10,1'])
    ->post('/admin/impersonate/stop', [AdminImpersonationController::class, 'stop'])
    ->name('admin.impersonation.stop');
