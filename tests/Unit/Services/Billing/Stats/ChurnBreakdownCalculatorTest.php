<?php

use App\Models\User;
use App\Services\Billing\Stats\ChurnBreakdownCalculator;
use Illuminate\Support\Facades\DB;

it('returns voluntary and involuntary integer counts', function () {
    $calculator = app(ChurnBreakdownCalculator::class);
    $result = $calculator->calculate();

    expect($result)->toHaveKeys(['voluntary', 'involuntary']);
    expect($result['voluntary'])->toBeInt();
    expect($result['involuntary'])->toBeInt();
});

it('classifies voluntary churn from canonical event name', function () {
    $user = User::factory()->create();

    $before = app(ChurnBreakdownCalculator::class)->calculate();

    DB::table('audit_logs')->insert([
        'event' => 'subscription.canceled',
        'user_id' => $user->id,
        'ip' => '127.0.0.1',
        'metadata' => json_encode(['churn_type' => 'voluntary']),
        'created_at' => now(),
    ]);

    $after = app(ChurnBreakdownCalculator::class)->calculate();

    expect($after['voluntary'])->toBe($before['voluntary'] + 1);
    expect($after['involuntary'])->toBe($before['involuntary']);
});

it('classifies involuntary churn from legacy stripe event name', function () {
    $user = User::factory()->create();

    $before = app(ChurnBreakdownCalculator::class)->calculate();

    DB::table('audit_logs')->insert([
        'event' => 'stripe.subscription.deleted',
        'user_id' => $user->id,
        'ip' => '127.0.0.1',
        'metadata' => json_encode(['churn_type' => 'involuntary']),
        'created_at' => now(),
    ]);

    $after = app(ChurnBreakdownCalculator::class)->calculate();

    expect($after['involuntary'])->toBe($before['involuntary'] + 1);
    expect($after['voluntary'])->toBe($before['voluntary']);
});
