<?php

namespace App\Http\Middleware;

use App\Http\Controllers\ChangelogController;
use App\Models\User;
use App\Models\UserSetting;
use App\Services\BillingService;
use App\Services\FeatureFlagService;
use App\Services\PlanLimitService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    public function __construct(
        private BillingService $billingService,
        private FeatureFlagService $featureFlagService,
    ) {}

    /**
     * Return approaching-limit flags for the user.
     * Only resources at ≥80% usage are returned.
     * Cached per-user for 60 seconds to minimise query overhead.
     *
     * @return array<string, array{current: int, limit: int, threshold: int}>
     */
    private function getLimitWarnings(User $user): array
    {
        return cache()->remember("user:{$user->id}:limit_warnings", 60, function () use ($user) {
            $planLimitService = app(PlanLimitService::class);
            $warnings = [];

            // API tokens
            if (config('features.api_tokens.enabled', true)) {
                $tokenLimit = $planLimitService->getLimit($user, 'api_tokens');
                if ($tokenLimit !== null && $tokenLimit > 0) {
                    $tokenCount = $user->tokens()->count();
                    $pct = ($tokenCount / $tokenLimit) * 100;
                    if ($pct >= 80) {
                        $warnings['api_tokens'] = [
                            'current' => $tokenCount,
                            'limit' => $tokenLimit,
                            'threshold' => $pct >= 100 ? 100 : 80,
                        ];
                    }
                }
            }

            // Webhook endpoints
            if (config('features.webhooks.enabled', false)) {
                $webhookLimit = $planLimitService->getLimit($user, 'webhook_endpoints');
                if ($webhookLimit !== null && $webhookLimit > 0) {
                    $webhookCount = $user->webhookEndpoints()->count();
                    $pct = ($webhookCount / $webhookLimit) * 100;
                    if ($pct >= 80) {
                        $warnings['webhook_endpoints'] = [
                            'current' => $webhookCount,
                            'limit' => $webhookLimit,
                            'threshold' => $pct >= 100 ? 100 : 80,
                        ];
                    }
                }
            }

            return $warnings;
        });
    }

    private function getPqlThreshold(User $user): ?int
    {
        $warnings = $this->getLimitWarnings($user);
        if (empty($warnings)) {
            return null;
        }
        $maxThreshold = max(array_column($warnings, 'threshold'));

        return $maxThreshold >= 80 ? $maxThreshold : null;
    }

    private function hasUnreadChangelog(object $user): bool
    {
        $latestVersion = ChangelogController::latestVersion();

        if (! $latestVersion) {
            return false;
        }

        $lastSeen = UserSetting::getValue($user->id, 'changelog_last_seen_version');

        return $lastSeen !== $latestVersion;
    }

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    /**
     * Returns the billing status string for the authenticated user's default subscription.
     * Returns 'past_due' or 'incomplete' when action is needed; null otherwise.
     */
    private function getBillingStatus(Request $request): ?string
    {
        $user = $request->user();

        if (! $user || ! config('features.billing.enabled', false)) {
            return null;
        }

        $user->loadMissing('subscriptions');
        $subscription = $user->subscription('default');

        if (! $subscription) {
            return null;
        }

        return match ($subscription->stripe_status) {
            'past_due' => 'past_due',
            'incomplete' => 'incomplete',
            default => null,
        };
    }

    public function share(Request $request): array
    {
        $user = $request->user();
        $features = $this->featureFlagService->resolveAll($user);

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at?->toISOString(),
                    'has_password' => $user->hasPassword(),
                    'is_admin' => $user->is_admin,
                    'is_super_admin' => $user->isSuperAdmin(),
                    'two_factor_enabled' => $features['two_factor']
                        ? $user->hasTwoFactorEnabled()
                        : false,
                    'subscription' => $features['billing'] ? function () use ($user) {
                        $user->loadMissing('subscriptions');
                        $status = $this->billingService->getSubscriptionStatus($user);

                        return [
                            'subscribed' => $status['subscribed'],
                            'tier' => $status['tier'],
                            'on_trial' => $status['on_trial'],
                            'on_grace_period' => $status['on_grace_period'],
                            'quantity' => $status['quantity'],
                        ];
                    } : null,
                ] : null,
                'impersonating' => fn () => $request->session()->has('admin_impersonating_from')
                    ? [
                        'admin_name' => $request->session()->get('admin_impersonating_name', 'Admin'),
                    ]
                    : null,
            ],
            'billing_status' => fn () => $this->getBillingStatus($request),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
                'new_registration' => fn () => $request->session()->get('new_registration'),
                'social_provider' => fn () => $request->session()->get('social_provider'),
                'upgrade_prompt' => fn () => $request->session()->get('upgrade_prompt'),
            ],
            'features' => [
                'billing' => $features['billing'],
                'socialAuth' => $features['social_auth'],
                'emailVerification' => $features['email_verification'],
                'apiTokens' => $features['api_tokens'],
                'userSettings' => $features['user_settings'],
                'notifications' => $features['notifications'],
                'onboarding' => $features['onboarding'],
                'apiDocs' => $features['api_docs'],
                'twoFactor' => $features['two_factor'],
                'webhooks' => $features['webhooks'],
                'admin' => $features['admin'],
            ],
            'notifications_unread_count' => fn () => $features['notifications'] && $user
                ? cache()->remember(
                    "user:{$user->id}:unread_notif_count",
                    60,
                    fn () => $user->unreadNotifications()->count()
                )
                : 0,
            // Active A/B experiment variants — keyed by experiment name.
            // Add experiment keys here as new tests are launched.
            'experiments' => $user ? fn () => [] : null,
            'has_unread_changelog' => $user ? fn () => $this->hasUnreadChangelog($user) : false,
            // PQL limit warnings — only computed when billing is enabled and user is authenticated.
            // Cached per-user with a 60-second TTL to avoid query overhead on every request.
            'limit_warnings' => $user && $features['billing'] ? fn () => $this->getLimitWarnings($user) : null,
            // Highest PQL threshold percentage (80 or 100) for any limit nearing its cap.
            // Used by frontend to surface upgrade prompts. Null when billing is off or no warnings.
            'pql_threshold' => $user && $features['billing'] ? fn () => $this->getPqlThreshold($user) : null,
        ];
    }
}
