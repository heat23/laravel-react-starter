<?php

use App\Models\User;
use App\Notifications\ReEngagementNotification;

it('email 3 contains safe data message', function () {
    $notification = new ReEngagementNotification(3);
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    $body = collect($mailMessage->introLines)->join(' ');

    expect($body)->toContain('Your data is safe');
});

it('email 3 does not reference account deletion', function () {
    $notification = new ReEngagementNotification(3);
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    $body = collect($mailMessage->introLines)->join(' ');

    expect($body)->not->toContain('deleted')
        ->and($body)->not->toContain('deletion')
        ->and($body)->not->toContain('removed');
});

it('email 3 subject mentions what changed', function () {
    $notification = new ReEngagementNotification(3);
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->subject)->toContain('changed');
});

it('email 1 CTA links to dashboard for free users', function () {
    $notification = new ReEngagementNotification(1, isPaidUser: false);
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->actionUrl)->toContain(route('dashboard'));
});

it('email 1 CTA links to billing index for paid users', function () {
    $notification = new ReEngagementNotification(1, isPaidUser: true);
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->actionUrl)->toBe(route('billing.index'));
});

it('email 2 CTA links to billing portal for paid users', function () {
    $notification = new ReEngagementNotification(2, isPaidUser: true);
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->actionUrl)->toBe(route('billing.portal'));
});

it('email 3 uses generic fallback changelog text when none provided', function () {
    config(['app.changelog_item' => null]);

    $notification = new ReEngagementNotification(3);
    $user = User::factory()->create();

    $mailMessage = $notification->toMail($user);

    $body = collect($mailMessage->introLines)->join(' ');

    expect($body)
        ->not->toContain('smarter activity tracking')
        ->toContain('recent improvements');
});

it('includes correct database payload', function () {
    $notification = new ReEngagementNotification(3, isPaidUser: true);
    $user = User::factory()->create();

    $data = $notification->toArray($user);

    expect($data)->toBe([
        'type' => 're_engagement_3',
        'email_number' => 3,
        'is_paid' => true,
        'user_score' => 0,
        'campaign_variant' => 'account_status',
    ]);
});

it('toArray stores user_score for in-app display', function () {
    $notification = new ReEngagementNotification(1, isPaidUser: false, userScore: 75);
    $user = User::factory()->create();

    $data = $notification->toArray($user);

    expect($data['user_score'])->toBe(75);
});

it('toArray stores upgrade_cta variant for high-score users on emails 1 and 2', function () {
    foreach ([1, 2] as $emailNumber) {
        $notification = new ReEngagementNotification($emailNumber, userScore: 60);
        $user = User::factory()->create();

        $data = $notification->toArray($user);

        expect($data['campaign_variant'])->toBe('upgrade_cta');
    }
});

it('toArray stores standard variant names for low-score users', function () {
    $cases = [
        [1, 'gentle_check_in'],
        [2, 'feedback_request'],
        [3, 'account_status'],
        [4, 'value_tip'],
    ];

    foreach ($cases as [$emailNumber, $expectedVariant]) {
        $notification = new ReEngagementNotification($emailNumber, userScore: 0);
        $user = User::factory()->create();

        $data = $notification->toArray($user);

        expect($data['campaign_variant'])->toBe($expectedVariant);
    }
});

it('toArray uses standard variant for email 3 and 4 even when score is high', function () {
    foreach ([3 => 'account_status', 4 => 'value_tip'] as $emailNumber => $expectedVariant) {
        $notification = new ReEngagementNotification($emailNumber, userScore: 90);
        $user = User::factory()->create();

        $data = $notification->toArray($user);

        expect($data['campaign_variant'])->toBe($expectedVariant);
    }
});

it('emailNumber 5 uses gentle_check_in default campaign variant', function () {
    $notification = new ReEngagementNotification(5, isPaidUser: false, userScore: 0);
    $user = User::factory()->create();

    $data = $notification->toArray($user);

    expect($data['campaign_variant'])->toBe('gentle_check_in');
});

it('userScore 59 does not trigger upgrade_cta campaign variant', function () {
    $notification = new ReEngagementNotification(1, isPaidUser: false, userScore: 59);
    $user = User::factory()->create();

    $data = $notification->toArray($user);

    expect($data['campaign_variant'])->toBe('gentle_check_in');
});

it('userScore 60 triggers upgrade_cta campaign variant for email 1', function () {
    $notification = new ReEngagementNotification(1, isPaidUser: false, userScore: 60);
    $user = User::factory()->create();

    $data = $notification->toArray($user);

    expect($data['campaign_variant'])->toBe('upgrade_cta');
});
