<?php

namespace App\Http\Controllers\Admin\Users;

use App\Enums\AuditEvent;
use App\Http\Controllers\Admin\Concerns\ListsAdminResources;
use App\Http\Controllers\Admin\Users\Concerns\BuildsUserQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminCreateUserRequest;
use App\Http\Requests\Admin\AdminUpdateUserRequest;
use App\Http\Requests\Admin\AdminUserIndexRequest;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\UserStageHistory;
use App\Services\AuditService;
use App\Services\CacheInvalidationManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserIdentityController extends Controller
{
    use BuildsUserQuery;
    use ListsAdminResources;

    public function __construct(
        private AuditService $auditService,
        private CacheInvalidationManager $cacheManager,
    ) {}

    public function create(): Response
    {
        return Inertia::render('App/Admin/Users/Create', [
            'isSuperAdmin' => auth()->user()?->isSuperAdmin() ?? false,
        ]);
    }

    public function store(AdminCreateUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if (! empty($validated['is_admin'])) {
            $user->is_admin = true;
            $user->email_verified_at = now();
            $user->save();
        }

        $this->auditService->log(AuditEvent::ADMIN_USER_CREATED, [
            'created_user_id' => $user->id,
            'created_email' => $user->email,
            'is_admin' => $user->is_admin,
        ]);

        $this->cacheManager->invalidateDashboard();

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} created successfully.");
    }

    public function index(AdminUserIndexRequest $request): Response
    {
        $query = $this->buildUserQuery($request->validated())
            ->withCount('tokens', 'settings', 'webhookEndpoints');

        $perPage = (int) ($request->validated('per_page') ?? config('pagination.admin.users', 25));
        $users = $this->paginateAdminList($query, $request, ['name', 'email', 'created_at', 'last_login_at', 'is_admin'], 'created_at', 'desc', $perPage);

        $users->through(fn ($user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => $user->is_admin,
            'email_verified_at' => $user->email_verified_at?->toISOString(),
            'last_login_at' => $user->last_login_at?->toISOString(),
            'created_at' => $user->created_at?->toISOString(),
            'tokens_count' => $user->tokens_count,
            'deleted_at' => $user->deleted_at?->toISOString(),
        ]);

        return Inertia::render('App/Admin/Users/Index', [
            'users' => $users,
            'filters' => array_merge(
                $request->only('search', 'admin', 'verified', 'status', 'sort', 'dir'),
                ['per_page' => (string) $perPage]
            ),
        ]);
    }

    public function show(User $user): Response
    {
        $user->loadCount('tokens');

        $this->auditService->log(AuditEvent::ADMIN_USER_VIEWED, [
            'target_user_id' => $user->id,
            'target_email' => $user->email,
        ]);

        $recentAuditLogs = AuditLog::byUser($user->id)
            ->latest()
            ->limit(config('pagination.admin.subscription_logs', 20))
            ->get()
            ->map(fn (AuditLog $log) => [
                'id' => $log->id,
                'event' => $log->event,
                'ip' => $log->ip,
                'created_at' => $log->created_at?->toISOString(),
                'metadata' => $log->metadata,
            ]);

        $subscription = null;
        if (config('features.billing.enabled')) {
            $user->loadMissing('subscriptions');
            $sub = $user->subscription();
            if ($sub) {
                $sub->load('owner', 'items.subscription');
                $subscription = [
                    'id' => $sub->id,
                    'stripe_status' => $sub->stripe_status,
                    'stripe_price' => $sub->stripe_price,
                    'quantity' => $sub->quantity,
                    'trial_ends_at' => $sub->trial_ends_at?->toISOString(),
                ];
            }
        }

        $stageHistory = UserStageHistory::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(config('pagination.admin.stage_history', 50))
            ->get()
            ->map(fn (UserStageHistory $h) => [
                'id' => $h->id,
                'from_stage' => $h->from_stage,
                'to_stage' => $h->to_stage,
                'reason' => $h->reason,
                'created_at' => $h->created_at->toISOString(),
            ]);

        return Inertia::render('App/Admin/Users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
                'email_verified_at' => $user->email_verified_at?->toISOString(),
                'last_login_at' => $user->last_login_at?->toISOString(),
                'created_at' => $user->created_at?->toISOString(),
                'signup_source' => $user->signup_source,
                'has_password' => $user->hasPassword(),
                'tokens_count' => $user->tokens_count,
                'deleted_at' => $user->deleted_at?->toISOString(),
            ],
            'recent_audit_logs' => $recentAuditLogs,
            'subscription' => $subscription,
            'stage_history' => $stageHistory,
        ]);
    }

    public function update(AdminUpdateUserRequest $request, User $user): RedirectResponse
    {
        abort_unless(! $user->trashed(), 403, 'Cannot update a deactivated user.');

        $before = ['name' => $user->name, 'email' => $user->email];
        $validated = $request->validated();
        $user->update($validated);
        $after = ['name' => $user->name, 'email' => $user->email];

        $this->auditService->log(AuditEvent::ADMIN_USER_UPDATED, [
            'target_user_id' => $user->id,
            'changes' => ['before' => $before, 'after' => $after],
        ]);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated successfully.');
    }
}
