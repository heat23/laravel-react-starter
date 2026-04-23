<?php

namespace App\Services\Billing\Stats;

use Illuminate\Support\Facades\DB;

class DashboardStatsCalculator
{
    public function __construct(
        private MrrCalculator $mrr,
        private ChurnRateCalculator $churnRate,
        private TrialConversionCalculator $trialConversion,
        private ActivationRateCalculator $activationRate,
        private SignupConversionCalculator $signupConversion,
    ) {}

    /**
     * @return array{active_subscriptions: int, trialing: int, past_due: int, canceled: int, scheduled_cancellations: int, total_ever: int, mrr: float, churn_rate: float, trial_conversion_rate: float, activation_rate: float, activation_rate_all_time: float, signup_to_paid_conversion: float, cohort_conversion_30d: float, cached_at: string}
     */
    public function calculate(): array
    {
        return [
            'active_subscriptions' => DB::table('subscriptions')
                ->where('stripe_status', 'active')
                ->whereNull('ends_at')
                ->count(),
            'trialing' => DB::table('subscriptions')
                ->where('stripe_status', 'trialing')
                ->count(),
            'past_due' => DB::table('subscriptions')
                ->where('stripe_status', 'past_due')
                ->count(),
            // Only truly canceled (ends_at in the past); scheduled cancellations (future ends_at) are separate
            'canceled' => DB::table('subscriptions')
                ->whereNotNull('ends_at')
                ->where('ends_at', '<=', now())
                ->count(),
            // Still paying but scheduled to cancel (ends_at in the future, status still active)
            'scheduled_cancellations' => DB::table('subscriptions')
                ->whereNotNull('ends_at')
                ->where('ends_at', '>', now())
                ->where('stripe_status', 'active')
                ->count(),
            'total_ever' => DB::table('subscriptions')->count(),
            'mrr' => $this->mrr->calculate(),
            'churn_rate' => $this->churnRate->calculate(),
            'trial_conversion_rate' => $this->trialConversion->calculate(),
            'activation_rate' => $this->activationRate->calculate(),
            'activation_rate_all_time' => $this->activationRate->calculateAllTime(),
            'signup_to_paid_conversion' => $this->signupConversion->calculate(),
            'cohort_conversion_30d' => $this->signupConversion->calculateCohorted(),
            'cached_at' => now()->toISOString(),
        ];
    }
}
