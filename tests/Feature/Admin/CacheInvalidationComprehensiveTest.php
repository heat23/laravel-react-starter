<?php

use App\Enums\AdminCacheKey;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    config(['features.two_factor.enabled' => true]);

    if (! \Schema::hasTable('two_factor_authentications')) {
        \Schema::create('two_factor_authentications', function ($table) {
            $table->id();
            $table->morphs('authenticatable');
            $table->text('shared_secret');
            $table->timestampTz('enabled_at')->nullable();
            $table->string('label');
            $table->unsignedTinyInteger('digits')->default(6);
            $table->unsignedTinyInteger('seconds')->default(30);
            $table->unsignedTinyInteger('window')->default(1);
            $table->string('algorithm', 16)->default('sha1');
            $table->json('recovery_codes')->nullable();
            $table->timestampTz('recovery_codes_generated_at')->nullable();
            $table->timestamps();
        });
    }
});

it('invalidates two-factor stats cache when 2FA is disabled', function () {
    Cache::put(AdminCacheKey::TWO_FACTOR_STATS->value, ['stale' => true], 300);
    Cache::put(AdminCacheKey::DASHBOARD_STATS->value, ['stale' => true], 300);

    $user = User::factory()->create();
    $user->createTwoFactorAuth();
    $user->enableTwoFactorAuth();

    $response = $this->actingAs($user)
        ->delete('/settings/security/disable', [
            'password' => 'password',
        ]);

    $response->assertRedirect();

    expect(Cache::get(AdminCacheKey::TWO_FACTOR_STATS->value))->toBeNull();
    expect(Cache::get(AdminCacheKey::DASHBOARD_STATS->value))->toBeNull();
});
