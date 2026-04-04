<?php

namespace Tests\Feature\Console;

use App\Models\User;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class EnforceGracePeriodTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['features.billing.enabled' => true]);
        config(['billing.grace_period_days' => 7]);
        ensureCashierTablesExist();
    }

    public function test_skips_when_billing_disabled(): void
    {
        config(['features.billing.enabled' => false]);

        $this->artisan('billing:enforce-grace-period')
            ->assertSuccessful()
            ->expectsOutput('Billing feature is disabled.');
    }

    public function test_cancels_subscription_past_grace_period(): void
    {
        $user = User::factory()->create();
        createSubscription($user, [
            'stripe_status' => 'past_due',
            'past_due_since' => now()->subDays(8), // exceeds 7-day grace period
        ]);

        $mock = Mockery::mock(BillingService::class);
        $mock->shouldReceive('cancelSubscription')
            ->once()
            ->with(Mockery::on(fn ($u) => $u->id === $user->id), true)
            ->andReturn($user->subscription('default'));

        $this->app->instance(BillingService::class, $mock);

        $this->artisan('billing:enforce-grace-period')
            ->assertSuccessful()
            ->expectsOutput('Grace period enforcement complete: 1 cancelled, 0 failed.');
    }

    public function test_does_not_cancel_subscription_within_grace_period(): void
    {
        $user = User::factory()->create();
        createSubscription($user, [
            'stripe_status' => 'past_due',
            'past_due_since' => now()->subDays(3), // within 7-day grace period
        ]);

        $mock = Mockery::mock(BillingService::class);
        $mock->shouldReceive('cancelSubscription')->never();

        $this->app->instance(BillingService::class, $mock);

        $this->artisan('billing:enforce-grace-period')
            ->assertSuccessful()
            ->expectsOutput('Grace period enforcement complete: 0 cancelled, 0 failed.');
    }

    public function test_does_not_cancel_active_subscriptions(): void
    {
        $user = User::factory()->create();
        createSubscription($user, [
            'stripe_status' => 'active',
            'past_due_since' => now()->subDays(10),
        ]);

        $mock = Mockery::mock(BillingService::class);
        $mock->shouldReceive('cancelSubscription')->never();

        $this->app->instance(BillingService::class, $mock);

        $this->artisan('billing:enforce-grace-period')
            ->assertSuccessful()
            ->expectsOutput('Grace period enforcement complete: 0 cancelled, 0 failed.');
    }

    public function test_does_not_cancel_subscription_with_null_past_due_since(): void
    {
        $user = User::factory()->create();
        createSubscription($user, [
            'stripe_status' => 'past_due',
            'past_due_since' => null,
        ]);

        $mock = Mockery::mock(BillingService::class);
        $mock->shouldReceive('cancelSubscription')->never();

        $this->app->instance(BillingService::class, $mock);

        $this->artisan('billing:enforce-grace-period')
            ->assertSuccessful()
            ->expectsOutput('Grace period enforcement complete: 0 cancelled, 0 failed.');
    }

    public function test_continues_processing_after_individual_failure(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        createSubscription($user1, [
            'stripe_status' => 'past_due',
            'past_due_since' => now()->subDays(10),
        ]);
        createSubscription($user2, [
            'stripe_status' => 'past_due',
            'past_due_since' => now()->subDays(10),
        ]);

        $cancelCallCount = 0;
        $mock = Mockery::mock(BillingService::class);
        $mock->shouldReceive('cancelSubscription')
            ->twice()
            ->andReturnUsing(function () use (&$cancelCallCount) {
                $cancelCallCount++;
                // Throw on the first call; succeed on the second
                if ($cancelCallCount === 1) {
                    throw new \RuntimeException('Stripe API error');
                }

                return null;
            });

        $this->app->instance(BillingService::class, $mock);

        $this->artisan('billing:enforce-grace-period')->assertSuccessful();

        // Both subscriptions were attempted (command does not abort on first failure)
        $this->assertSame(2, $cancelCallCount);
    }

    public function test_respects_configurable_grace_period_days(): void
    {
        config(['billing.grace_period_days' => 14]);

        $user = User::factory()->create();
        createSubscription($user, [
            'stripe_status' => 'past_due',
            'past_due_since' => now()->subDays(10), // within 14-day grace period
        ]);

        $mock = Mockery::mock(BillingService::class);
        $mock->shouldReceive('cancelSubscription')->never();

        $this->app->instance(BillingService::class, $mock);

        $this->artisan('billing:enforce-grace-period')
            ->assertSuccessful()
            ->expectsOutput('Grace period enforcement complete: 0 cancelled, 0 failed.');
    }
}
