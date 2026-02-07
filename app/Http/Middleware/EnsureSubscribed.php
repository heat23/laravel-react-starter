<?php

namespace App\Http\Middleware;

use App\Services\BillingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscribed
{
    public function __construct(
        private BillingService $billingService,
    ) {}

    /**
     * Handle an incoming request.
     *
     * Usage:
     *   middleware('subscribed')        — any active subscription
     *   middleware('subscribed:team')   — team tier or above
     *   middleware('subscribed:enterprise') — enterprise only
     */
    public function handle(Request $request, Closure $next, ?string $minimumTier = null): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->denyAccess($request);
        }

        $user->loadMissing('subscriptions');

        if (! $user->subscribed('default')) {
            return $this->denyAccess($request);
        }

        if ($minimumTier) {
            $userTier = $this->billingService->resolveUserTier($user);
            $hierarchy = config('plans.tier_hierarchy', ['free', 'pro', 'team', 'enterprise']);

            $userLevel = array_search($userTier, $hierarchy, true);
            $requiredLevel = array_search($minimumTier, $hierarchy, true);

            if ($userLevel === false || $requiredLevel === false || $userLevel < $requiredLevel) {
                return $this->denyAccess($request, "This feature requires a {$minimumTier} plan or higher.");
            }
        }

        return $next($request);
    }

    private function denyAccess(Request $request, string $message = 'Active subscription required.'): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        session()->put('url.intended', $request->fullUrl());

        return redirect()->route('pricing')->with('error', $message);
    }
}
