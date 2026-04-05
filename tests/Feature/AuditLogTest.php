<?php

use App\Jobs\DispatchAnalyticsEvent;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\Queue;

test('audit log model scopes', function () {
    $user = User::factory()->create();

    AuditLog::factory()->create(['event' => 'auth.login', 'user_id' => $user->id]);
    AuditLog::factory()->create(['event' => 'auth.logout', 'user_id' => $user->id]);
    AuditLog::factory()->create(['event' => 'auth.login', 'user_id' => null]);

    $this->assertCount(2, AuditLog::byUser($user->id)->get());
    $this->assertCount(2, AuditLog::byEvent('auth.login')->get());
    $this->assertCount(3, AuditLog::recent(30)->get());
});

test('audit log metadata is cast to array', function () {
    $log = AuditLog::factory()->create(['metadata' => ['key' => 'value']]);

    $this->assertIsArray($log->fresh()->metadata);
    $this->assertEquals('value', $log->fresh()->metadata['key']);
});

test('audit service persists login to database', function () {
    $user = User::factory()->create();

    $service = new AuditService;
    $service->logLogin($user);

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'auth.login',
        'user_id' => $user->id,
    ]);

    // Email is intentionally excluded from login metadata (PII cleanup — see commit 2da3a16).
    // Empty metadata is stored as null (PersistAuditLog uses [] ?: null), so assert no email key.
    $log = AuditLog::first();
    $this->assertNull($log->metadata);
});

test('audit service persists logout to database', function () {
    $user = User::factory()->create();

    $service = new AuditService;
    $service->logLogout($user);

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'auth.logout',
        'user_id' => $user->id,
    ]);
});

test('audit service persists registration to database', function () {
    $user = User::factory()->create(['signup_source' => 'github']);

    $service = new AuditService;
    $service->logRegistration($user);

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'auth.register',
        'user_id' => $user->id,
    ]);

    $log = AuditLog::first();
    $this->assertEquals('github', $log->metadata['signup_source']);
});

test('audit service generic log', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $service = new AuditService;
    $service->log('settings.updated', ['setting' => 'theme', 'value' => 'dark']);

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'settings.updated',
        'user_id' => $user->id,
    ]);

    $log = AuditLog::first();
    $this->assertEquals('theme', $log->metadata['setting']);
});

test('recent scope excludes old records', function () {
    AuditLog::factory()->create([
        'created_at' => now()->subDays(60),
    ]);

    AuditLog::factory()->create([
        'created_at' => now()->subDays(10),
    ]);

    $this->assertCount(1, AuditLog::recent(30)->get());
});

test('audit log survives user deletion', function () {
    $user = User::factory()->create();
    AuditLog::factory()->create(['user_id' => $user->id, 'event' => 'auth.login']);

    $user->forceDelete();

    $log = AuditLog::first();
    $this->assertNotNull($log);
    $this->assertNull($log->user_id);
});

// GA4 forwarding tests (ANA-010)
test('audit service dispatches GA4 job for forwarded lifecycle event', function () {
    Queue::fake();

    $user = User::factory()->create();
    $this->actingAs($user);

    $service = new AuditService;
    $service->log('subscription.created', ['plan' => 'pro']);

    Queue::assertPushed(DispatchAnalyticsEvent::class, function ($job) use ($user) {
        return $job->eventName === 'subscription.created'
            && $job->params === ['plan' => 'pro']
            && $job->userId === $user->id;
    });
});

test('audit service does not dispatch GA4 job for non-forwarded event', function () {
    Queue::fake();

    $user = User::factory()->create();
    $this->actingAs($user);

    $service = new AuditService;
    $service->log('settings.updated', ['key' => 'theme']);

    Queue::assertNotPushed(DispatchAnalyticsEvent::class);
});

test('audit service does not dispatch GA4 job for forwarded event with no user', function () {
    Queue::fake();

    // No actingAs — anonymous user
    $service = new AuditService;
    $service->log('subscription.created', ['plan' => 'pro']);

    Queue::assertNotPushed(DispatchAnalyticsEvent::class);
});

test('audit service dispatches GA4 job for all forwarded event types', function (string $event) {
    Queue::fake();

    $user = User::factory()->create();
    $this->actingAs($user);

    $service = new AuditService;
    $service->log($event, []);

    Queue::assertPushed(DispatchAnalyticsEvent::class, function ($job) use ($event) {
        return $job->eventName === $event;
    });
})->with([
    'auth.register',
    'auth.login',
    'auth.social_login',
    'onboarding.completed',
    'subscription.created',
    'subscription.canceled',
    'trial.started',
    'limit.threshold_50',
    'limit.threshold_80',
    'limit.threshold_100',
    'billing.payment_failed',
    'billing.payment_method_updated',
]);

// ANA-007: payment failure and recovery events forwarded to GA4
test('audit service dispatches GA4 job for billing.payment_failed', function () {
    Queue::fake();

    $user = User::factory()->create();
    $this->actingAs($user);

    $service = new AuditService;
    $service->log('billing.payment_failed', ['plan' => 'pro', 'amount' => 2900]);

    Queue::assertPushed(DispatchAnalyticsEvent::class, function ($job) use ($user) {
        return $job->eventName === 'billing.payment_failed'
            && $job->params === ['plan' => 'pro', 'amount' => 2900]
            && $job->userId === $user->id;
    });
});

test('audit service dispatches GA4 job for billing.payment_method_updated (recovery signal)', function () {
    Queue::fake();

    $user = User::factory()->create();
    $this->actingAs($user);

    $service = new AuditService;
    $service->log('billing.payment_method_updated', []);

    Queue::assertPushed(DispatchAnalyticsEvent::class, function ($job) use ($user) {
        return $job->eventName === 'billing.payment_method_updated'
            && $job->userId === $user->id;
    });
});

// ANA-013: consent gate — server-side GA4 forwarding

test('audit service does not dispatch GA4 when user has explicitly declined analytics consent', function () {
    Queue::fake();

    $user = User::factory()->create();
    $user->setSetting(AuditService::ANALYTICS_CONSENT_KEY, false);
    $this->actingAs($user);

    $service = new AuditService;
    $service->log('subscription.created', ['plan' => 'pro']);

    Queue::assertNotPushed(DispatchAnalyticsEvent::class);
});

test('audit service dispatches GA4 when user has explicitly granted analytics consent', function () {
    Queue::fake();

    $user = User::factory()->create();
    $user->setSetting(AuditService::ANALYTICS_CONSENT_KEY, true);
    $this->actingAs($user);

    $service = new AuditService;
    $service->log('subscription.created', ['plan' => 'pro']);

    Queue::assertPushed(DispatchAnalyticsEvent::class, function ($job) use ($user) {
        return $job->eventName === 'subscription.created' && $job->userId === $user->id;
    });
});

test('audit service dispatches GA4 when user has no analytics consent setting (legitimate interest)', function () {
    Queue::fake();

    // No setSetting call — consent key is absent (null)
    $user = User::factory()->create();
    $this->actingAs($user);

    $service = new AuditService;
    $service->log('auth.login', []);

    // null consent → legitimate interest → dispatch proceeds
    Queue::assertPushed(DispatchAnalyticsEvent::class, function ($job) use ($user) {
        return $job->eventName === 'auth.login' && $job->userId === $user->id;
    });
});

// ANA-014: ConsentController persists analytics consent setting
test('consent endpoint persists analytics decline for authenticated user', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->postJson(route('consent.store'), [
        'categories' => [
            'necessary' => true,
            'analytics' => false,
            'marketing' => false,
        ],
    ])->assertJson(['success' => true]);

    // Verify the setting round-trips as a native PHP boolean false (not string or int)
    $consent = $user->fresh()->getSetting(AuditService::ANALYTICS_CONSENT_KEY);
    expect($consent)->toBeFalse();
});

test('consent endpoint persists analytics grant for authenticated user', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->postJson(route('consent.store'), [
        'categories' => [
            'necessary' => true,
            'analytics' => true,
            'marketing' => true,
        ],
    ])->assertJson(['success' => true]);

    $consent = $user->fresh()->getSetting(AuditService::ANALYTICS_CONSENT_KEY);
    expect($consent)->toBeTrue();
});

test('consent endpoint succeeds without persisting for guest users', function () {
    $this->postJson(route('consent.store'), [
        'categories' => [
            'necessary' => true,
            'analytics' => false,
            'marketing' => false,
        ],
    ])->assertJson(['success' => true]);
});
