<?php

namespace App\Listeners;

use App\Services\PlanLimitService;
use Illuminate\Auth\Events\Registered;

class StartUserTrial
{
    public function __construct(private PlanLimitService $planLimitService) {}

    public function handle(Registered $event): void
    {
        if (! $this->planLimitService->isTrialEnabled()) {
            return;
        }

        $this->planLimitService->startTrial($event->user);
    }
}
