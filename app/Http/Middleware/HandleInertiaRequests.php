<?php

namespace App\Http\Middleware;

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
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'email_verified_at' => $request->user()->email_verified_at?->toISOString(),
                    'has_password' => $request->user()->hasPassword(),
                    'two_factor_enabled' => config('features.two_factor.enabled', false)
                        ? $request->user()->hasTwoFactorEnabled()
                        : false,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],
            'features' => [
                'billing' => config('features.billing.enabled', false),
                'socialAuth' => config('features.social_auth.enabled', false),
                'emailVerification' => config('features.email_verification.enabled', true),
                'apiTokens' => config('features.api_tokens.enabled', true),
                'userSettings' => config('features.user_settings.enabled', true),
                'notifications' => config('features.notifications.enabled', false),
                'onboarding' => config('features.onboarding.enabled', false),
                'apiDocs' => config('features.api_docs.enabled', false),
                'twoFactor' => config('features.two_factor.enabled', false),
                'webhooks' => config('features.webhooks.enabled', false),
            ],
            'notifications_unread_count' => fn () => config('features.notifications.enabled', false) && $request->user()
                ? cache()->remember(
                    "user:{$request->user()->id}:unread_notif_count",
                    60,
                    fn () => $request->user()->unreadNotifications()->count()
                )
                : 0,
        ];
    }
}
