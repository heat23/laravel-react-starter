<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminCacheKey;
use App\Enums\AnalyticsEvent;
use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\CacheInvalidationManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class AdminCacheController extends Controller
{
    public function __construct(
        private CacheInvalidationManager $cacheManager,
        private AuditService $auditService,
    ) {}

    public function index(): Response
    {
        $cacheKeys = collect(AdminCacheKey::cases())->map(fn ($key) => [
            'key' => $key->value,
            'name' => $key->name,
            'exists' => Cache::has($key->value),
        ]);

        return Inertia::render('Admin/Cache/Index', [
            'cacheKeys' => $cacheKeys,
            'scopes' => ['all', 'billing', 'tokens', 'webhooks', 'two_factor', 'social_auth', 'dashboard'],
        ]);
    }

    public function flush(Request $request): RedirectResponse
    {
        $request->validate([
            'scope' => ['required', 'string', 'in:all,billing,tokens,webhooks,two_factor,social_auth,dashboard'],
        ]);

        $scope = $request->input('scope');

        match ((string) $scope) {
            'all' => AdminCacheKey::flushAll(),
            'billing' => $this->cacheManager->invalidateBilling(),
            'tokens' => $this->cacheManager->invalidateTokens(),
            'webhooks' => $this->cacheManager->invalidateWebhooks(),
            'two_factor' => $this->cacheManager->invalidateTwoFactor(),
            'social_auth' => $this->cacheManager->invalidateSocialAuth(),
            default => $this->cacheManager->invalidateDashboard(),
        };

        $this->auditService->log(AnalyticsEvent::ADMIN_CACHE_FLUSHED, [
            'scope' => $scope,
        ]);

        return redirect()->route('admin.cache.index')
            ->with('success', "Cache scope '{$scope}' flushed successfully.");
    }
}
