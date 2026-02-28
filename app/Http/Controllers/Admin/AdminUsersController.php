<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminUserIndexRequest;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditService;
use App\Services\CacheInvalidationManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminUsersController extends Controller
{
    public function __construct(
        private AuditService $auditService,
        private CacheInvalidationManager $cacheManager,
    ) {}

    public function index(AdminUserIndexRequest $request): Response
    {
        $validated = $request->validated();
        $query = User::withTrashed()->withCount('tokens');

        if (! empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (isset($validated['admin']) && $validated['admin'] !== '') {
            $query->where('is_admin', (bool) $validated['admin']);
        }

        $allowedSorts = ['name', 'email', 'created_at', 'last_login_at', 'is_admin'];
        $sort = in_array($validated['sort'] ?? null, $allowedSorts, true) ? $validated['sort'] : 'created_at';
        $dir = ($validated['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $dir);

        $users = $query->paginate(config('pagination.admin.users', 25))->through(fn (User $user) => [
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

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only('search', 'admin', 'sort', 'dir'),
        ]);
    }

    public function show(User $user): Response
    {
        $user->loadCount('tokens');

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
                    'stripe_status' => $sub->stripe_status,
                    'stripe_price' => $sub->stripe_price,
                    'quantity' => $sub->quantity,
                    'trial_ends_at' => $sub->trial_ends_at?->toISOString(),
                ];
            }
        }

        return Inertia::render('Admin/Users/Show', [
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
        ]);
    }

    public function toggleAdmin(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Cannot change own admin status.');
        }

        $user->is_admin = ! $user->is_admin;
        $user->save();

        $this->cacheManager->invalidateDashboard();

        $this->auditService->log('admin.toggle_admin', [
            'target_user_id' => $user->id,
            'target_email' => $user->email,
            'is_admin' => $user->is_admin,
        ]);

        $name = e($user->name);

        return back()->with('success', $user->is_admin ? "Made {$name} an admin." : "Removed admin from {$name}.");
    }

    public function bulkDeactivate(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['integer', 'exists:users,id'],
        ]);

        $adminId = $request->user()->id;
        $ids = collect($request->input('ids'))->reject(fn ($id) => (int) $id === $adminId);

        $users = User::whereIn('id', $ids)->whereNull('deleted_at')->where('is_admin', false)->get();

        $count = DB::transaction(function () use ($users) {
            $deactivated = 0;
            foreach ($users as $user) {
                $user->delete();
                $this->auditService->log('admin.user_deactivated', [
                    'target_user_id' => $user->id,
                    'target_email' => $user->email,
                    'bulk' => true,
                ]);
                $deactivated++;
            }

            return $deactivated;
        });

        foreach ($users as $user) {
            $this->invalidateUserCaches($user);
        }

        return back()->with('success', "Deactivated {$count} user(s).");
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Cannot deactivate own account.');
        }

        $name = e($user->name);

        if ($user->trashed()) {
            $user->restore();
            $this->auditService->log('admin.user_restored', [
                'target_user_id' => $user->id,
                'target_email' => $user->email,
            ]);

            $this->invalidateUserCaches($user);

            return back()->with('success', "Restored {$name}.");
        }

        $user->delete();
        $this->auditService->log('admin.user_deactivated', [
            'target_user_id' => $user->id,
            'target_email' => $user->email,
        ]);

        $this->invalidateUserCaches($user);

        return back()->with('success', "Deactivated {$name}.");
    }

    private function invalidateUserCaches(User $user): void
    {
        $this->cacheManager->invalidateUser($user->id);
    }
}
