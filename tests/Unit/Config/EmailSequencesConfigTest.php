<?php

it('has the expected sequences defined', function () {
    $sequences = ['welcome_sequence', 'onboarding', 'dunning', 'win_back', 're_engagement', 'trial_nudge'];

    foreach ($sequences as $sequence) {
        expect(config("email-sequences.{$sequence}"))->toBeArray()->not->toBeEmpty();
    }
});

it('welcome_sequence steps have days and max_days keys', function () {
    $schedule = config('email-sequences.welcome_sequence');

    foreach ($schedule as $emailNumber => $step) {
        expect($step)->toHaveKeys(['days', 'max_days'])
            ->and($step['days'])->toBeInt()->toBeGreaterThanOrEqual(0)
            ->and($step['max_days'])->toBeInt()->toBeGreaterThan($step['days']);
    }
});

it('onboarding steps have days and max_days keys', function () {
    $schedule = config('email-sequences.onboarding');

    foreach ($schedule as $emailNumber => $step) {
        expect($step)->toHaveKeys(['days', 'max_days'])
            ->and($step['max_days'])->toBeGreaterThan($step['days']);
    }
});

it('dunning steps have days and max_days keys', function () {
    $schedule = config('email-sequences.dunning');

    foreach ($schedule as $emailNumber => $step) {
        expect($step)->toHaveKeys(['days', 'max_days'])
            ->and($step['max_days'])->toBeGreaterThan($step['days']);
    }
});

it('win_back steps have days and max_days keys', function () {
    $schedule = config('email-sequences.win_back');

    foreach ($schedule as $emailNumber => $step) {
        expect($step)->toHaveKeys(['days', 'max_days'])
            ->and($step['max_days'])->toBeGreaterThan($step['days']);
    }
});

it('re_engagement steps have days and max_days keys', function () {
    $schedule = config('email-sequences.re_engagement');

    foreach ($schedule as $emailNumber => $step) {
        expect($step)->toHaveKeys(['days', 'max_days'])
            ->and($step['max_days'])->toBeGreaterThan($step['days']);
    }
});

it('trial_nudge windows have window_start and window_end keys', function () {
    $schedule = config('email-sequences.trial_nudge');

    foreach ($schedule as $emailNumber => $window) {
        expect($window)->toHaveKeys(['window_start', 'window_end'])
            ->and($window['window_end'])->toBeGreaterThan($window['window_start']);
    }
});

it('trial_ending has a positive days_before_expiry value', function () {
    $daysBeforeExpiry = config('email-sequences.trial_ending.days_before_expiry');

    expect($daysBeforeExpiry)->toBeInt()->toBeGreaterThan(0);
});

it('welcome_sequence schedule is overridable via config()', function () {
    config(['email-sequences.welcome_sequence' => [
        2 => ['days' => 2, 'max_days' => 3],
    ]]);

    $schedule = config('email-sequences.welcome_sequence');

    expect($schedule)->toHaveKey(2)
        ->and($schedule[2]['days'])->toBe(2);
});
