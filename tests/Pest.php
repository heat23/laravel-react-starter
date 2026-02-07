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

        // Add unique constraint to prevent duplicate active subscriptions (SQLite partial index)
        // Uses billable_type/billable_id for forward compatibility
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('
                CREATE UNIQUE INDEX subscriptions_unique_active
                ON subscriptions (COALESCE(billable_id, user_id), type)
                WHERE ends_at IS NULL
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
