<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Billing Test Helpers
|--------------------------------------------------------------------------
|
| Helpers for creating subscription records and Stripe webhook payloads
| without hitting the Stripe API. Used across all billing tests.
|
*/

/**
 * Ensure Cashier tables exist for billing tests.
 * Call in beforeEach() of any billing test file.
 */
function ensureCashierTablesExist(): void
{
    // Drop and recreate to ensure schema matches latest version
    if (Schema::hasTable('subscriptions')) {
        // Check if it has the new columns - if not, drop and recreate
        if (! Schema::hasColumn('subscriptions', 'billable_type')) {
            // Drop foreign key from subscription_items first (MySQL requires this)
            if (Schema::hasTable('subscription_items')) {
                try {
                    Schema::table('subscription_items', function ($table) {
                        $table->dropForeign(['subscription_id']);
                    });
                } catch (\Illuminate\Database\QueryException $e) {
                    // Foreign key may not exist — safe to ignore
                }
                Schema::dropIfExists('subscription_items');
            }
            Schema::dropIfExists('subscriptions');
        }
    }

    if (! Schema::hasTable('subscriptions')) {
        Schema::create('subscriptions', function ($table) {
            $table->id();
            $table->string('billable_type')->nullable();
            $table->foreignId('billable_id')->nullable();
            $table->foreignId('user_id')->nullable(); // For Cashier compatibility (uses user_id, not polymorphic)
            $table->string('type');
            $table->string('stripe_id')->unique();
            $table->string('stripe_status');
            $table->string('stripe_price')->nullable();
            $table->integer('quantity')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedBigInteger('last_webhook_at')->nullable();
            $table->timestamps();
            $table->index(['billable_type', 'billable_id']);
            $table->index(['user_id', 'stripe_status']);
            $table->index('last_webhook_at');
        });

        // Add unique constraint to prevent duplicate active subscriptions
        // Uses billable_type/billable_id for forward compatibility
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('
                CREATE UNIQUE INDEX subscriptions_unique_active
                ON subscriptions (COALESCE(billable_id, user_id), type)
                WHERE ends_at IS NULL
            ');
        } elseif ($driver === 'mysql') {
            // MySQL doesn't support partial indexes or COALESCE in indexes
            // Use generated columns for both the user identifier and active flag
            DB::statement('
                ALTER TABLE subscriptions
                ADD COLUMN user_identifier BIGINT UNSIGNED GENERATED ALWAYS AS (IF(billable_id IS NOT NULL, billable_id, user_id)) STORED
            ');
            DB::statement('
                ALTER TABLE subscriptions
                ADD COLUMN active_flag TINYINT(1) GENERATED ALWAYS AS (IF(ends_at IS NULL, 1, NULL)) STORED
            ');
            DB::statement('
                CREATE UNIQUE INDEX subscriptions_unique_active
                ON subscriptions (user_identifier, type, active_flag)
            ');
        }
    }
    if (! Schema::hasTable('subscription_items')) {
        Schema::create('subscription_items', function ($table) {
            $table->id();
            $table->foreignId('subscription_id');
            $table->string('stripe_id')->unique();
            $table->string('stripe_product');
            $table->string('stripe_price');
            $table->integer('quantity')->nullable();
            $table->timestamps();
            $table->index(['subscription_id', 'stripe_price']);
            // Composite index for AdminBillingStatsService subquery optimization
            $table->index(['subscription_id', 'id'], 'subscription_items_subscription_id_id_index');
        });
    }
}

/**
 * Create a subscription record directly in the database.
 */
function createSubscription(\App\Models\User $user, array $overrides = []): \Laravel\Cashier\Subscription
{
    ensureCashierTablesExist();

    $subscription = \Laravel\Cashier\Subscription::create(array_merge([
        'billable_type' => \App\Models\User::class,
        'billable_id' => $user->id,
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_'.Illuminate\Support\Str::random(14),
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro_monthly',
        'quantity' => 1,
        'trial_ends_at' => null,
        'ends_at' => null,
    ], $overrides));

    $subscription->items()->create([
        'stripe_id' => 'si_'.Illuminate\Support\Str::random(14),
        'stripe_product' => 'prod_'.Illuminate\Support\Str::random(14),
        'stripe_price' => $overrides['stripe_price'] ?? 'price_pro_monthly',
        'quantity' => $overrides['quantity'] ?? 1,
    ]);

    return $subscription;
}

/**
 * Create a team-tier subscription.
 */
function createTeamSubscription(\App\Models\User $user, int $seats = 5, array $overrides = []): \Laravel\Cashier\Subscription
{
    return createSubscription($user, array_merge([
        'stripe_price' => 'price_team_monthly',
        'quantity' => $seats,
    ], $overrides));
}

/**
 * Create an enterprise-tier subscription.
 */
function createEnterpriseSubscription(\App\Models\User $user, int $seats = 10, array $overrides = []): \Laravel\Cashier\Subscription
{
    return createSubscription($user, array_merge([
        'stripe_price' => 'price_enterprise_monthly',
        'quantity' => $seats,
    ], $overrides));
}

/**
 * Register billing routes for tests.
 *
 * Routes behind `if (config('features.billing.enabled'))` in web.php are evaluated
 * at boot time. Setting config in beforeEach() happens after routes are registered,
 * so billing routes won't exist. This helper re-registers them for tests.
 */
function registerBillingRoutes(): void
{
    $router = app('router');

    // Public pricing page
    $router->get('/pricing', [\App\Http\Controllers\Billing\PricingController::class, '__invoke'])
        ->middleware('web')
        ->name('pricing');

    $router->middleware(['web', 'auth', 'verified'])->group(function () use ($router) {
        $router->get('/billing', [\App\Http\Controllers\Billing\BillingController::class, 'index'])->name('billing.index');
        $router->post('/billing/subscribe', [\App\Http\Controllers\Billing\SubscriptionController::class, 'subscribe'])->name('billing.subscribe');
        $router->post('/billing/cancel', [\App\Http\Controllers\Billing\SubscriptionController::class, 'cancel'])->name('billing.cancel');
        $router->post('/billing/resume', [\App\Http\Controllers\Billing\SubscriptionController::class, 'resume'])->name('billing.resume');
        $router->post('/billing/swap', [\App\Http\Controllers\Billing\SubscriptionController::class, 'swap'])->name('billing.swap');
        $router->post('/billing/quantity', [\App\Http\Controllers\Billing\SubscriptionController::class, 'updateQuantity'])->name('billing.quantity');
        $router->post('/billing/payment-method', [\App\Http\Controllers\Billing\SubscriptionController::class, 'updatePaymentMethod'])->name('billing.payment-method');
        $router->get('/billing/portal', [\App\Http\Controllers\Billing\SubscriptionController::class, 'portal'])->name('billing.portal');
    });

    // Stripe webhook (no auth - Cashier verifies signature)
    $router->post('/stripe/webhook', [\App\Http\Controllers\Billing\StripeWebhookController::class, 'handleWebhook'])
        ->middleware(['web', 'throttle:120,1'])
        ->name('cashier.webhook');

    // Refresh the route name lookup table so route() helper works
    $router->getRoutes()->refreshNameLookups();
    $router->getRoutes()->refreshActionLookups();
}

/*
|--------------------------------------------------------------------------
| Admin Test Helpers
|--------------------------------------------------------------------------
|
| Helper for registering admin routes in tests. Admin routes are behind
| a feature flag check at boot time, so they need manual registration.
|
*/

/**
 * Register admin routes for tests.
 *
 * Routes behind `if (config('features.admin.enabled'))` in web.php are evaluated
 * at boot time. Setting config in beforeEach() happens after routes are registered,
 * so admin routes won't exist. This helper re-registers them for tests.
 */
function registerAdminRoutes(): void
{
    config(['features.admin.enabled' => true]);

    $router = app('router');
    $needsRefresh = false;

    // Core admin routes (register once)
    if (! Route::has('admin.dashboard')) {
        $router->middleware(['web', 'auth', 'verified', 'admin'])
            ->prefix('admin')
            ->name('admin.')
            ->group(function () use ($router) {
                $router->get('/', [\App\Http\Controllers\Admin\AdminDashboardController::class, '__invoke'])->name('dashboard');

                $router->get('/users', [\App\Http\Controllers\Admin\AdminUsersController::class, 'index'])->name('users.index');
                $router->get('/users/{user}', [\App\Http\Controllers\Admin\AdminUsersController::class, 'show'])->withTrashed()->name('users.show');
                $router->patch('/users/{user}/toggle-admin', [\App\Http\Controllers\Admin\AdminUsersController::class, 'toggleAdmin'])->name('users.toggle-admin');
                $router->patch('/users/{user}/toggle-active', [\App\Http\Controllers\Admin\AdminUsersController::class, 'toggleActive'])->withTrashed()->name('users.toggle-active');
                $router->post('/users/bulk-deactivate', [\App\Http\Controllers\Admin\AdminUsersController::class, 'bulkDeactivate'])->name('users.bulk-deactivate');

                $router->post('/users/{user}/impersonate', [\App\Http\Controllers\Admin\AdminImpersonationController::class, 'start'])->withTrashed()->name('users.impersonate');

                $router->get('/health', [\App\Http\Controllers\Admin\AdminHealthController::class, '__invoke'])->name('health');
                $router->get('/config', [\App\Http\Controllers\Admin\AdminConfigController::class, '__invoke'])->name('config');

                $router->get('/audit-logs', [\App\Http\Controllers\Admin\AdminAuditLogController::class, 'index'])->name('audit-logs.index');
                $router->get('/audit-logs/export', [\App\Http\Controllers\Admin\AdminAuditLogController::class, 'export'])->name('audit-logs.export');
                $router->get('/audit-logs/{auditLog}', [\App\Http\Controllers\Admin\AdminAuditLogController::class, 'show'])->name('audit-logs.show');

                $router->get('/system', [\App\Http\Controllers\Admin\AdminSystemController::class, '__invoke'])->name('system');
            });

        // Stop impersonation — outside admin middleware
        $router->middleware(['web', 'auth'])
            ->post('/admin/impersonate/stop', [\App\Http\Controllers\Admin\AdminImpersonationController::class, 'stop'])
            ->name('admin.impersonation.stop');

        $needsRefresh = true;
    }

    // Feature-gated admin routes (each guarded independently so they can be
    // registered later when a test sets the feature flag after boot)
    if (config('features.billing.enabled') && ! Route::has('admin.billing.dashboard')) {
        $router->middleware(['web', 'auth', 'verified', 'admin'])
            ->prefix('admin')
            ->name('admin.')
            ->group(function () use ($router) {
                $router->get('/billing', [\App\Http\Controllers\Admin\AdminBillingController::class, 'dashboard'])->name('billing.dashboard');
                $router->get('/billing/subscriptions', [\App\Http\Controllers\Admin\AdminBillingController::class, 'subscriptions'])->name('billing.subscriptions');
                $router->get('/billing/subscriptions/{subscription}', [\App\Http\Controllers\Admin\AdminBillingController::class, 'show'])->name('billing.show');
            });
        $needsRefresh = true;
    }

    if (config('features.webhooks.enabled') && ! Route::has('admin.webhooks')) {
        $router->middleware(['web', 'auth', 'verified', 'admin'])
            ->prefix('admin')
            ->name('admin.')
            ->group(function () use ($router) {
                $router->get('/webhooks', [\App\Http\Controllers\Admin\AdminWebhooksController::class, '__invoke'])->name('webhooks');
            });
        $needsRefresh = true;
    }

    if (config('features.api_tokens.enabled') && ! Route::has('admin.tokens')) {
        $router->middleware(['web', 'auth', 'verified', 'admin'])
            ->prefix('admin')
            ->name('admin.')
            ->group(function () use ($router) {
                $router->get('/tokens', [\App\Http\Controllers\Admin\AdminTokensController::class, '__invoke'])->name('tokens');
            });
        $needsRefresh = true;
    }

    if (config('features.social_auth.enabled') && ! Route::has('admin.social-auth')) {
        $router->middleware(['web', 'auth', 'verified', 'admin'])
            ->prefix('admin')
            ->name('admin.')
            ->group(function () use ($router) {
                $router->get('/social-auth', [\App\Http\Controllers\Admin\AdminSocialAuthController::class, '__invoke'])->name('social-auth');
            });
        $needsRefresh = true;
    }

    if (config('features.notifications.enabled') && ! Route::has('admin.notifications')) {
        $router->middleware(['web', 'auth', 'verified', 'admin'])
            ->prefix('admin')
            ->name('admin.')
            ->group(function () use ($router) {
                $router->get('/notifications', [\App\Http\Controllers\Admin\AdminNotificationsController::class, '__invoke'])->name('notifications');
            });
        $needsRefresh = true;
    }

    if (config('features.two_factor.enabled') && ! Route::has('admin.two-factor')) {
        $router->middleware(['web', 'auth', 'verified', 'admin'])
            ->prefix('admin')
            ->name('admin.')
            ->group(function () use ($router) {
                $router->get('/two-factor', [\App\Http\Controllers\Admin\AdminTwoFactorController::class, '__invoke'])->name('two-factor');
            });
        $needsRefresh = true;
    }

    // Feature Flags (always available in admin)
    if (! Route::has('admin.feature-flags.index')) {
        $router->middleware(['web', 'auth', 'verified', 'admin'])
            ->prefix('admin')
            ->name('admin.')
            ->group(function () use ($router) {
                $router->get('/feature-flags', [\App\Http\Controllers\Admin\AdminFeatureFlagController::class, 'index'])->name('feature-flags.index');
                $router->patch('/feature-flags/{flag}', [\App\Http\Controllers\Admin\AdminFeatureFlagController::class, 'updateGlobal'])->name('feature-flags.update-global');
                $router->delete('/feature-flags/{flag}', [\App\Http\Controllers\Admin\AdminFeatureFlagController::class, 'removeGlobal'])->name('feature-flags.remove-global');
                $router->get('/feature-flags/{flag}/users', [\App\Http\Controllers\Admin\AdminFeatureFlagController::class, 'getTargetedUsers'])->name('feature-flags.users');
                $router->post('/feature-flags/{flag}/users', [\App\Http\Controllers\Admin\AdminFeatureFlagController::class, 'addUserOverride'])->name('feature-flags.add-user');
                $router->delete('/feature-flags/{flag}/users/{user}', [\App\Http\Controllers\Admin\AdminFeatureFlagController::class, 'removeUserOverride'])->name('feature-flags.remove-user');
                $router->delete('/feature-flags/{flag}/users', [\App\Http\Controllers\Admin\AdminFeatureFlagController::class, 'removeAllUserOverrides'])->name('feature-flags.remove-all-users');
                $router->get('/feature-flags/search-users', [\App\Http\Controllers\Admin\AdminFeatureFlagController::class, 'searchUsers'])->name('feature-flags.search-users');
            });
        $needsRefresh = true;
    }

    if ($needsRefresh) {
        $router->getRoutes()->refreshNameLookups();
        $router->getRoutes()->refreshActionLookups();
    }
}

/**
 * Make a GET request as an admin user with admin routes registered.
 */
function adminGet(string $uri, array $params = [], ?\App\Models\User $admin = null): \Illuminate\Testing\TestResponse
{
    registerAdminRoutes();
    $admin ??= \App\Models\User::factory()->admin()->create();

    $query = $params ? '?'.http_build_query($params) : '';

    return test()->actingAs($admin)->get($uri.$query);
}

/**
 * Make a PATCH request as an admin user with admin routes registered.
 */
function adminPatch(string $uri, array $data = [], ?\App\Models\User $admin = null): \Illuminate\Testing\TestResponse
{
    registerAdminRoutes();
    $admin ??= \App\Models\User::factory()->admin()->create();

    return test()->actingAs($admin)->patch($uri, $data);
}

/**
 * Make a POST request as an admin user with admin routes registered.
 */
function adminPost(string $uri, array $data = [], ?\App\Models\User $admin = null): \Illuminate\Testing\TestResponse
{
    registerAdminRoutes();
    $admin ??= \App\Models\User::factory()->admin()->create();

    return test()->actingAs($admin)->post($uri, $data);
}

/**
 * Make a DELETE request as an admin user with admin routes registered.
 */
function adminDelete(string $uri, ?\App\Models\User $admin = null): \Illuminate\Testing\TestResponse
{
    registerAdminRoutes();
    $admin ??= \App\Models\User::factory()->admin()->create();

    return test()->actingAs($admin)->delete($uri);
}

/**
 * Build an impersonation session array (encrypted admin ID).
 */
function impersonationSession(int $adminId, string $adminName = 'Admin'): array
{
    return [
        'admin_impersonating_from' => \Illuminate\Support\Facades\Crypt::encryptString((string) $adminId),
        'admin_impersonating_name' => $adminName,
    ];
}

/**
 * Build a Stripe webhook event payload.
 */
function createStripeWebhookPayload(string $eventType, array $objectData = [], ?string $eventId = null): array
{
    return [
        'id' => $eventId ?? 'evt_'.Illuminate\Support\Str::random(14),
        'type' => $eventType,
        'data' => [
            'object' => $objectData,
        ],
        'livemode' => false,
        'created' => now()->timestamp,
        'api_version' => '2023-10-16',
    ];
}

/*
|--------------------------------------------------------------------------
| Feature Flag Test Helpers
|--------------------------------------------------------------------------
|
| Helpers for testing feature flag overrides without hitting the database.
|
*/

/**
 * Ensure the feature_flag_overrides table exists for tests.
 */
function ensureFeatureFlagOverridesTableExists(): void
{
    if (! Schema::hasTable('feature_flag_overrides')) {
        Schema::create('feature_flag_overrides', function ($table) {
            $table->id();
            $table->string('flag', 64);
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->boolean('enabled');
            $table->timestamps();
            $table->unique(['flag', 'user_id']);
            $table->index(['user_id', 'flag']);
        });
    }
}

/**
 * Set a feature flag override for testing.
 *
 * @param  string  $flag  The feature flag key
 * @param  bool  $enabled  Whether to enable or disable
 * @param  int|null  $userId  User ID for per-user override (null for global)
 */
function setFeatureFlagOverride(string $flag, bool $enabled, ?int $userId = null): void
{
    ensureFeatureFlagOverridesTableExists();

    \App\Models\FeatureFlagOverride::updateOrCreate(
        ['flag' => $flag, 'user_id' => $userId],
        ['enabled' => $enabled]
    );

    // Clear cache
    Cache::forget(\App\Enums\AdminCacheKey::FEATURE_FLAGS_GLOBAL->value);
    if ($userId !== null) {
        Cache::forget(\App\Enums\AdminCacheKey::featureFlagsUser($userId));
    }
}

/**
 * Clear all feature flag overrides for testing.
 */
function clearFeatureFlagOverrides(): void
{
    if (Schema::hasTable('feature_flag_overrides')) {
        \App\Models\FeatureFlagOverride::query()->delete();
    }
    Cache::forget(\App\Enums\AdminCacheKey::FEATURE_FLAGS_GLOBAL->value);
}
