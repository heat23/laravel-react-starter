<?php

namespace App\Support\Billing;

use App\Enums\PlanTier;

/**
 * Result DTO returned by PlanLimitService::canPerform().
 *
 * Replaces the previous bool return + session() side-effect pattern.
 * Controllers read the DTO and decide how to present limit enforcement
 * to the user (session flash, JSON response, etc.).
 */
readonly class PlanLimitResult
{
    public function __construct(
        public bool $allowed,
        public ?string $reason = null,
        public ?PlanTier $upgradeTier = null,
        public ?string $userMessage = null,
    ) {}
}
