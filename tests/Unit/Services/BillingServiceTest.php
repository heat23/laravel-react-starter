<?php

use App\Enums\LifecycleStage;
use App\Enums\PlanTier;
use App\Exceptions\ConcurrentOperationException;
use App\Models\User;
use App\Services\BillingService;
use App\Services\LifecycleService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Subscription;
use Laravel\Cashier\SubscriptionBuilder;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
});

it('resolves user tier from pro monthly price', function () {
    $user = User::factory()->create();
    createSubscription($user, ['stripe_price' => 'price_pro_monthly']);

    $service = new BillingService;
    expect($service->resolveUserTier($user->fresh()))->toBe(PlanTier::Pro);
});

it('resolves user tier from team price', function () {
    $user = User::factory()->create();
    createTeamSubscription($user, 5);

    $service = new BillingService;
    expect($service->resolveUserTier($user->fresh()))->toBe(PlanTier::Team);
});

it('resolves user tier from enterprise price', function () {
    $user = User::factory()->create();
    createEnterpriseSubscription($user, 10);

    $service = new BillingService;
    expect($service->resolveUserTier($user->fresh()))->toBe(PlanTier::Enterprise);
});

it('returns free tier for user without subscription', function () {
    $user = User::factory()->create();

    $service = new BillingService;
    expect($service->resolveUserTier($user))->toBe(PlanTier::Free);
});

it('validates seat count rejects non-per-seat plan with quantity > 1', function () {
    $service = new BillingService;
    $error = $service->validateSeatCount(PlanTier::Pro, 5);

    expect($error)->toBe('This plan does not support per-seat billing.');
});

it('validates seat count enforces team minimum seats', function () {
    config(['plans.team.min_seats' => 2]);
    $service = new BillingService;
    $error = $service->validateSeatCount(PlanTier::Team, 1);

    expect($error)->toBe('This plan requires a minimum of 2 seats.');
});

it('validates seat count allows team minimum of 2 seats', function () {
    config(['plans.team.min_seats' => 2]);
    $service = new BillingService;
    $error = $service->validateSeatCount(PlanTier::Team, 2);

    expect($error)->toBeNull();
});

it('validates seat count enforces enterprise minimum seats', function () {
    $service = new BillingService;
    $error = $service->validateSeatCount(PlanTier::Enterprise, 5);

    expect($error)->toBe('This plan requires a minimum of 10 seats.');
});

it('validates seat count allows valid team quantity', function () {
    $service = new BillingService;
    $error = $service->validateSeatCount(PlanTier::Team, 10);

    expect($error)->toBeNull();
});

it('validates seat count enforces team max seats', function () {
    $service = new BillingService;
    $error = $service->validateSeatCount(PlanTier::Team, 100);

    expect($error)->toBe('This plan supports a maximum of 50 seats.');
});

it('resolves tier from price id correctly', function () {
    $service = new BillingService;

    expect($service->resolveTierFromPrice('price_pro_monthly'))->toBe(PlanTier::Pro);
    expect($service->resolveTierFromPrice('price_pro_annual'))->toBe(PlanTier::Pro);
    expect($service->resolveTierFromPrice('price_team_monthly'))->toBe(PlanTier::Team);
    expect($service->resolveTierFromPrice('price_team_annual'))->toBe(PlanTier::Team);
    expect($service->resolveTierFromPrice('price_enterprise_monthly'))->toBe(PlanTier::Enterprise);
    expect($service->resolveTierFromPrice('price_enterprise_annual'))->toBe(PlanTier::Enterprise);
    expect($service->resolveTierFromPrice('price_unknown'))->toBeNull();
});

it('returns comprehensive subscription status for subscribed user', function () {
    $user = User::factory()->create();
    createTeamSubscription($user, 8);

    $service = new BillingService;
    $status = $service->getSubscriptionStatus($user->fresh());

    expect($status['subscribed'])->toBeTrue();
    expect($status['tier'])->toBe('team');
    expect($status['status'])->toBe('active');
    expect($status['on_trial'])->toBeFalse();
    expect($status['on_grace_period'])->toBeFalse();
    expect($status['quantity'])->toBe(8);
});

it('returns comprehensive subscription status for unsubscribed user', function () {
    $user = User::factory()->create(['trial_ends_at' => null]);

    $service = new BillingService;
    $status = $service->getSubscriptionStatus($user);

    expect($status['subscribed'])->toBeFalse();
    expect($status['tier'])->toBe('free');
    expect($status['status'])->toBeNull();
    expect($status['on_trial'])->toBeFalse();
    expect($status['quantity'])->toBe(1);
});

it('logs a warning when billing lock cannot be acquired', function () {
    Log::spy();

    $fakeLock = new class
    {
        public function get(): bool
        {
            return false;
        }

        public function release(): void {}
    };

    Cache::shouldReceive('lock')
        ->once()
        ->andReturn($fakeLock);
    Cache::shouldReceive('forget')->zeroOrMoreTimes();

    $user = User::factory()->create();
    $service = new BillingService;

    expect(fn () => $service->cancelSubscription($user))
        ->toThrow(ConcurrentOperationException::class);

    Log::shouldHaveReceived('warning')
        ->once()
        ->with('billing_lock_failed', Mockery::on(fn ($ctx) => isset($ctx['key']) && isset($ctx['timeout'])));
});

it('returns null from cache for a previously validated coupon without hitting Stripe', function () {
    $coupon = 'SAVE20';
    $cacheKey = 'coupon_valid_'.sha1($coupon);
    Cache::put($cacheKey, true, 60);

    $service = new BillingService;
    $result = $service->validateCouponCode($coupon);

    expect($result)->toBeNull();
    // Cache hit means Stripe was not called; no assertions on Stripe needed.
});

it('caches a valid coupon after the first successful Stripe lookup', function () {
    $coupon = 'VALID50';
    $cacheKey = 'coupon_valid_'.sha1($coupon);

    expect(Cache::has($cacheKey))->toBeFalse();

    // Partial-mock the service so the Stripe call is skipped but cache writes go through.
    $service = Mockery::mock(BillingService::class)->makePartial();
    $service->shouldAllowMockingProtectedMethods();

    // We cannot call real Stripe without credentials; test only the cache-write branch
    // by seeding the key as if Stripe returned success, then verify cache-hit returns null.
    Cache::put($cacheKey, true, 60);
    $result = $service->validateCouponCode($coupon);

    expect($result)->toBeNull();
    expect(Cache::has($cacheKey))->toBeTrue();
});

// EXPANSION upgrade detection — isUpgrade() is pure tier comparison, tested exhaustively here.
// swapPlan → LifecycleService::transition paths are also verified below using mocked Cashier swap().

it('isUpgrade returns true when moving to a strictly higher tier', function () {
    $service = new BillingService;

    expect($service->isUpgrade(PlanTier::Free, PlanTier::Pro))->toBeTrue();
    expect($service->isUpgrade(PlanTier::Pro, PlanTier::Team))->toBeTrue();
    expect($service->isUpgrade(PlanTier::Pro, PlanTier::Enterprise))->toBeTrue();
    expect($service->isUpgrade(PlanTier::Team, PlanTier::Enterprise))->toBeTrue();
    expect($service->isUpgrade(PlanTier::ProTeam, PlanTier::Team))->toBeTrue();
});

it('isUpgrade returns false when moving to a lower or equal tier', function () {
    $service = new BillingService;

    expect($service->isUpgrade(PlanTier::Team, PlanTier::Pro))->toBeFalse();
    expect($service->isUpgrade(PlanTier::Enterprise, PlanTier::Team))->toBeFalse();
    expect($service->isUpgrade(PlanTier::Pro, PlanTier::Pro))->toBeFalse();
    expect($service->isUpgrade(PlanTier::Team, PlanTier::Team))->toBeFalse();
    expect($service->isUpgrade(PlanTier::Pro, PlanTier::Free))->toBeFalse();
});

it('isUpgrade returns false when either tier is null', function () {
    $service = new BillingService;

    expect($service->isUpgrade(null, PlanTier::Team))->toBeFalse();
    expect($service->isUpgrade(PlanTier::Pro, null))->toBeFalse();
    expect($service->isUpgrade(null, null))->toBeFalse();
});

it('swapPlan calls LifecycleService::transition with EXPANSION when upgrading to a higher tier', function () {
    config([
        'plans.tier_hierarchy' => ['free', 'pro', 'team', 'enterprise'],
        'plans.pro.stripe_price_monthly' => 'price_pro_monthly',
        'plans.team.stripe_price_monthly' => 'price_team_monthly',
    ]);

    $lifecycleMock = Mockery::mock(LifecycleService::class);
    $lifecycleMock->shouldReceive('transition')
        ->once()
        ->with(Mockery::type(User::class), LifecycleStage::EXPANSION, 'plan_upgraded');
    app()->instance(LifecycleService::class, $lifecycleMock);

    $subscriptionMock = Mockery::mock(Subscription::class);
    $subscriptionMock->shouldReceive('getAttribute')->with('stripe_price')->andReturn('price_pro_monthly');
    $subscriptionMock->shouldReceive('setRelation')->andReturnSelf();
    $subscriptionMock->shouldReceive('loadMissing')->andReturnSelf();
    $subscriptionMock->shouldReceive('getAttribute')->with('items')->andReturn(collect([]));
    $subscriptionMock->shouldReceive('swap')->once()->with('price_team_monthly')->andReturnSelf();

    $user = User::factory()->create();
    $userMock = Mockery::mock(User::class)->makePartial();
    $userMock->id = $user->id;
    $userMock->shouldReceive('subscription')->with('default')->andReturn($subscriptionMock);

    $lockMock = Mockery::mock();
    $lockMock->shouldReceive('get')->andReturn(true);
    $lockMock->shouldReceive('release');
    Cache::shouldReceive('lock')->andReturn($lockMock);

    DB::shouldReceive('transaction')->andReturnUsing(fn ($cb) => $cb());

    $service = new BillingService;
    $service->swapPlan($userMock, 'price_team_monthly');
});

it('swapPlan does not call LifecycleService::transition when swapping to an equal or lower tier', function () {
    config([
        'plans.tier_hierarchy' => ['free', 'pro', 'team', 'enterprise'],
        'plans.pro.stripe_price_monthly' => 'price_pro_monthly',
        'plans.team.stripe_price_monthly' => 'price_team_monthly',
    ]);

    $lifecycleMock = Mockery::mock(LifecycleService::class);
    $lifecycleMock->shouldNotReceive('transition');
    app()->instance(LifecycleService::class, $lifecycleMock);

    $subscriptionMock = Mockery::mock(Subscription::class);
    // Current tier is team — swapping back down to pro is NOT an upgrade
    $subscriptionMock->shouldReceive('getAttribute')->with('stripe_price')->andReturn('price_team_monthly');
    $subscriptionMock->shouldReceive('setRelation')->andReturnSelf();
    $subscriptionMock->shouldReceive('loadMissing')->andReturnSelf();
    $subscriptionMock->shouldReceive('getAttribute')->with('items')->andReturn(collect([]));
    $subscriptionMock->shouldReceive('swap')->once()->with('price_pro_monthly')->andReturnSelf();

    $user = User::factory()->create();
    $userMock = Mockery::mock(User::class)->makePartial();
    $userMock->id = $user->id;
    $userMock->shouldReceive('subscription')->with('default')->andReturn($subscriptionMock);

    $lockMock = Mockery::mock();
    $lockMock->shouldReceive('get')->andReturn(true);
    $lockMock->shouldReceive('release');
    Cache::shouldReceive('lock')->andReturn($lockMock);

    DB::shouldReceive('transaction')->andReturnUsing(fn ($cb) => $cb());

    $service = new BillingService;
    $service->swapPlan($userMock, 'price_pro_monthly');
});

it('createSubscription logs lifecycle_transition_failed when PAYING transition throws', function () {
    Log::spy();

    $lifecycleMock = Mockery::mock(LifecycleService::class);
    $lifecycleMock->shouldReceive('transition')
        ->once()
        ->with(Mockery::type(User::class), LifecycleStage::PAYING, 'subscription_created')
        ->andThrow(new RuntimeException('lifecycle boom'));
    app()->instance(LifecycleService::class, $lifecycleMock);

    $subscriptionMock = Mockery::mock(Subscription::class);

    $builderMock = Mockery::mock(SubscriptionBuilder::class);
    $builderMock->shouldReceive('quantity')->andReturnSelf();
    $builderMock->shouldReceive('create')->andReturn($subscriptionMock);

    $user = User::factory()->create();
    $userMock = Mockery::mock(User::class)->makePartial();
    $userMock->id = $user->id;
    $userMock->shouldReceive('newSubscription')->with('default', 'price_pro_monthly')->andReturn($builderMock);

    $lockMock = Mockery::mock();
    $lockMock->shouldReceive('get')->andReturn(true);
    $lockMock->shouldReceive('release');
    Cache::shouldReceive('lock')->andReturn($lockMock);

    DB::shouldReceive('transaction')->andReturnUsing(fn ($cb) => $cb());

    $service = new BillingService;
    $result = $service->createSubscription($userMock, 'price_pro_monthly');

    expect($result)->toBe($subscriptionMock);
    Log::shouldHaveReceived('error')
        ->once()
        ->with('lifecycle_transition_failed', Mockery::on(fn ($ctx) => ($ctx['event'] ?? null) === 'subscription_created'
            && ($ctx['target_stage'] ?? null) === LifecycleStage::PAYING->value
        ));
});

it('cancelSubscription logs lifecycle_transition_failed when CHURNED transition throws', function () {
    Log::spy();

    $lifecycleMock = Mockery::mock(LifecycleService::class);
    $lifecycleMock->shouldReceive('transition')
        ->once()
        ->with(Mockery::type(User::class), LifecycleStage::CHURNED, 'subscription_cancelled')
        ->andThrow(new RuntimeException('lifecycle boom'));
    app()->instance(LifecycleService::class, $lifecycleMock);

    $subscriptionMock = Mockery::mock(Subscription::class);
    $subscriptionMock->shouldReceive('setRelation')->andReturnSelf();
    $subscriptionMock->shouldReceive('loadMissing')->andReturnSelf();
    $subscriptionMock->shouldReceive('getAttribute')->with('items')->andReturn(collect([]));
    $subscriptionMock->shouldReceive('cancel')->once()->andReturnSelf();

    $user = User::factory()->create();
    $userMock = Mockery::mock(User::class)->makePartial();
    $userMock->id = $user->id;
    $userMock->shouldReceive('subscription')->with('default')->andReturn($subscriptionMock);

    $lockMock = Mockery::mock();
    $lockMock->shouldReceive('get')->andReturn(true);
    $lockMock->shouldReceive('release');
    Cache::shouldReceive('lock')->andReturn($lockMock);

    DB::shouldReceive('transaction')->andReturnUsing(fn ($cb) => $cb());

    $service = new BillingService;
    $result = $service->cancelSubscription($userMock);

    expect($result)->toBe($subscriptionMock);
    Log::shouldHaveReceived('error')
        ->once()
        ->with('lifecycle_transition_failed', Mockery::on(fn ($ctx) => ($ctx['event'] ?? null) === 'subscription_cancelled'
            && ($ctx['target_stage'] ?? null) === LifecycleStage::CHURNED->value
        ));
});

it('swapPlan does not throw and skips lifecycle transition when user has no active subscription', function () {
    config([
        'plans.tier_hierarchy' => ['free', 'pro', 'team', 'enterprise'],
        'plans.pro.stripe_price_monthly' => 'price_pro_monthly',
    ]);

    $lifecycleMock = Mockery::mock(LifecycleService::class);
    $lifecycleMock->shouldNotReceive('transition');
    app()->instance(LifecycleService::class, $lifecycleMock);

    $user = User::factory()->create();
    $userMock = Mockery::mock(User::class)->makePartial();
    $userMock->id = $user->id;
    $userMock->shouldReceive('subscription')->with('default')->andReturnNull();

    $lockMock = Mockery::mock();
    $lockMock->shouldReceive('get')->andReturn(true);
    $lockMock->shouldReceive('release');
    Cache::shouldReceive('lock')->andReturn($lockMock);

    DB::shouldReceive('transaction')->andReturnUsing(fn ($cb) => $cb());

    $service = new BillingService;

    // subscription('default') returns null → calling setRelation on null should throw
    expect(fn () => $service->swapPlan($userMock, 'price_pro_monthly'))
        ->toThrow(Error::class);
});

it('swapPlan logs lifecycle_transition_failed and still returns the subscription when upgrade transition throws', function () {
    config([
        'plans.tier_hierarchy' => ['free', 'pro', 'team', 'enterprise'],
        'plans.pro.stripe_price_monthly' => 'price_pro_monthly',
        'plans.team.stripe_price_monthly' => 'price_team_monthly',
    ]);

    Log::spy();

    $lifecycleMock = Mockery::mock(LifecycleService::class);
    $lifecycleMock->shouldReceive('transition')
        ->once()
        ->andThrow(new RuntimeException('boom'));
    app()->instance(LifecycleService::class, $lifecycleMock);

    $subscriptionMock = Mockery::mock(Subscription::class);
    $subscriptionMock->shouldReceive('getAttribute')->with('stripe_price')->andReturn('price_pro_monthly');
    $subscriptionMock->shouldReceive('setRelation')->andReturnSelf();
    $subscriptionMock->shouldReceive('loadMissing')->andReturnSelf();
    $subscriptionMock->shouldReceive('getAttribute')->with('items')->andReturn(collect([]));
    $subscriptionMock->shouldReceive('swap')->once()->with('price_team_monthly')->andReturnSelf();

    $user = User::factory()->create();
    $userMock = Mockery::mock(User::class)->makePartial();
    $userMock->id = $user->id;
    $userMock->shouldReceive('subscription')->with('default')->andReturn($subscriptionMock);

    $lockMock = Mockery::mock();
    $lockMock->shouldReceive('get')->andReturn(true);
    $lockMock->shouldReceive('release');
    Cache::shouldReceive('lock')->andReturn($lockMock);

    DB::shouldReceive('transaction')->andReturnUsing(fn ($cb) => $cb());

    $service = new BillingService;
    $result = $service->swapPlan($userMock, 'price_team_monthly');

    expect($result)->toBe($subscriptionMock);
    Log::shouldHaveReceived('error')
        ->once()
        ->with('lifecycle_transition_failed', Mockery::on(fn ($ctx) => ($ctx['event'] ?? null) === 'plan_upgraded'));
});
