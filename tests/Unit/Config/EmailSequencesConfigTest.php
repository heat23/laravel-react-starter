<?php

it('has welcome_sequence defined', function () {
    expect(config('email-sequences.welcome_sequence'))->toBeArray()->not->toBeEmpty();
});

it('welcome_sequence steps have days and max_days keys', function () {
    $schedule = config('email-sequences.welcome_sequence');

    foreach ($schedule as $step) {
        expect($step)->toHaveKeys(['days', 'max_days'])
            ->and($step['days'])->toBeInt()->toBeGreaterThanOrEqual(0)
            ->and($step['max_days'])->toBeInt()->toBeGreaterThan($step['days']);
    }
});

it('welcome_sequence schedule is overridable via config()', function () {
    config(['email-sequences.welcome_sequence' => [
        2 => ['days' => 2, 'max_days' => 3],
    ]]);

    $schedule = config('email-sequences.welcome_sequence');

    expect($schedule)->toHaveKey(2)
        ->and($schedule[2]['days'])->toBe(2);
});
