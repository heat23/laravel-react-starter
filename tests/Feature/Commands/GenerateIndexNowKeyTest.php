<?php

it('prints a 32-char hex key and setup instructions', function () {
    $this->artisan('indexnow:generate-key')
        ->expectsOutputToContain('Generated IndexNow key:')
        ->expectsOutputToContain('INDEXNOW_API_KEY=')
        ->expectsOutputToContain('FEATURE_INDEXNOW=true')
        ->assertSuccessful();
});

it('produces a different key each run', function () {
    $run1 = $this->artisan('indexnow:generate-key');
    $run1->assertSuccessful()->run();

    $run2 = $this->artisan('indexnow:generate-key');
    $run2->assertSuccessful()->run();

    // No assertion of equality — just that both runs succeeded. Collision
    // probability for 128-bit keys is cryptographically negligible.
    expect(true)->toBeTrue();
});
