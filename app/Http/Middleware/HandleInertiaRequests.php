<?php

namespace App\Http\Middleware;

use App\Services\BillingService;
use App\Services\FeatureFlagService;
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
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
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
        ];
    }
}
