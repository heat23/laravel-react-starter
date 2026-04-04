<?php

use App\Listeners\StartUserTrial;
use App\Models\User;
use App\Services\PlanLimitService;
use Illuminate\Auth\Events\Registered;

it('starts a trial when trials are enabled', function () {
    // Use make() — listener never persists the user itself, so no DB needed
    $user = User::factory()->make();
    $event = new Registered($user);

    $service = Mockery::mock(PlanLimitService::class);
    $service->shouldReceive('isTrialEnabled')->once()->andReturn(true);
    $service->shouldReceive('startTrial')->once()->with($user);

    $listener = new StartUserTrial($service);
    $listener->handle($event);
});

it('skips trial when trials are disabled', function () {
    $user = User::factory()->make();
    $event = new Registered($user);

    $service = Mockery::mock(PlanLimitService::class);
    $service->shouldReceive('isTrialEnabled')->once()->andReturn(false);
    $service->shouldNotReceive('startTrial');

    $listener = new StartUserTrial($service);
    $listener->handle($event);
});
