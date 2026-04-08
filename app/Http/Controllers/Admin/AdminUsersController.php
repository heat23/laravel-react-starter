<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AnalyticsEvent;
use App\Helpers\QueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminBulkDeactivateRequest;
use App\Http\Requests\Admin\AdminBulkRestoreRequest;
use App\Http\Requests\Admin\AdminCreateUserRequest;
use App\Http\Requests\Admin\AdminSendPasswordResetRequest;
use App\Http\Requests\Admin\AdminToggleActiveRequest;
use App\Http\Requests\Admin\AdminToggleAdminRequest;
use App\Http\Requests\Admin\AdminUpdateUserRequest;
use App\Http\Requests\Admin\AdminUserExportRequest;
use App\Http\Requests\Admin\AdminUserIndexRequest;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\UserStageHistory;
use App\Services\AuditService;
use App\Services\CacheInvalidationManager;
use App\Services\EngagementScoringService;
use App\Support\CsvExport;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminUsersController extends Controller
{
    public function __construct(
        private AuditService $auditService,
        private CacheInvalidationManager $cacheManager,
        private EngagementScoringService $engagementService,
    ) {}

    public function create(): Response
    {
        return Inertia::render('Admin/Users/Create', [
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

        $this->auditService->log(AnalyticsEvent::ADMIN_USER_CREATED, [
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
        $validated = $request->validated();

        $query = $this->buildUserQuery($validated)
            ->withCount('tokens', 'settings', 'webhookEndpoints');

        $perPage = (int) ($validated['per_page'] ?? config('pagination.admin.users', 25));
        $users = $query->paginate($perPage);

        $engagementScores = $this->engagementService->scoreBatch($users->getCollection());

        $users->through(fn (User $user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => $user->is_admin,
            'email_verified_at' => $user->email_verified_at?->toISOString(),
            'last_login_at' => $user->last_login_at?->toISOString(),
            'created_at' => $user->created_at?->toISOString(),
            'tokens_count' => $user->tokens_count,
            'deleted_at' => $user->deleted_at?->toISOString(),
            'engagement_score' => $engagementScores[$user->id] ?? 0,
        ]);

        return Inertia::render('Admin/Users/Index', [
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

        $this->auditService->log(AnalyticsEvent::ADMIN_USER_VIEWED, [
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

        $this->auditService->log(AnalyticsEvent::ADMIN_USER_UPDATED, [
            'target_user_id' => $user->id,
            'changes' => ['before' => $before, 'after' => $after],
        ]);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    public function toggleAdmin(AdminToggleAdminRequest $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Cannot change own admin status.');
        }

        if ($user->is_admin && User::where('is_admin', true)->whereNull('deleted_at')->count() <= 2) {
            return back()->with('error', 'Cannot remove admin status. At least two admin accounts must exist.');
        }

        $wasAdmin = $user->is_admin;
        $user->is_admin = ! $user->is_admin;
        $user->save();

        $this->cacheManager->invalidateDashboard();

        $this->auditService->log(AnalyticsEvent::ADMIN_TOGGLE_ADMIN, [
            'target_user_id' => $user->id,
            'target_email' => $user->email,
            'changes' => ['is_admin' => ['from' => $wasAdmin, 'to' => $user->is_admin]],
        ]);

        $name = e($user->name);

        return back()->with('success', $user->is_admin ? "Made {$name} an admin." : "Removed admin from {$name}.");
    }

    public function bulkDeactivate(AdminBulkDeactivateRequest $request): RedirectResponse
    {
        $adminId = $request->user()->id;
        $ids = collect($request->input('ids'))->reject(fn ($id) => (int) $id === $adminId);

        $users = User::whereIn('id', $ids)->whereNull('deleted_at')->where('is_admin', false)->get();

        $count = DB::transaction(function () use ($users) {
            $deactivated = 0;
            foreach ($users as $user) {
                $user->delete();
                $this->auditService->log(AnalyticsEvent::ADMIN_USER_DEACTIVATED, [
                    'target_user_id' => $user->id,
                    'target_email' => $user->email,
                    'bulk' => true,
                    'changes' => ['active' => ['from' => true, 'to' => false]],
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

    public function bulkRestore(AdminBulkRestoreRequest $request): RedirectResponse
    {
        $ids = collect($request->input('ids'));

        $users = User::onlyTrashed()->whereIn('id', $ids)->get();

        $count = DB::transaction(function () use ($users) {
            $restored = 0;
            foreach ($users as $user) {
                $user->restore();
                $this->auditService->log(AnalyticsEvent::ADMIN_USER_RESTORED, [
                    'target_user_id' => $user->id,
                    'target_email' => $user->email,
                    'bulk' => true,
                    'changes' => ['active' => ['from' => false, 'to' => true]],
                ]);
                $restored++;
            }

            return $restored;
        });

        foreach ($users as $user) {
            $this->invalidateUserCaches($user);
        }

        return back()->with('success', "Restored {$count} user(s).");
    }

    public function toggleActive(AdminToggleActiveRequest $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Cannot deactivate own account.');
        }

        $name = e($user->name);
        $wasActive = ! $user->trashed();

        if ($user->trashed()) {
            $user->restore();
            $this->auditService->log(AnalyticsEvent::ADMIN_USER_RESTORED, [
                'target_user_id' => $user->id,
                'target_email' => $user->email,
                'changes' => ['active' => ['from' => false, 'to' => true]],
            ]);

            $this->invalidateUserCaches($user);

            return back()->with('success', "Restored {$name}.");
        }

        $user->delete();
        $this->auditService->log(AnalyticsEvent::ADMIN_USER_DEACTIVATED, [
            'target_user_id' => $user->id,
            'target_email' => $user->email,
            'changes' => ['active' => ['from' => true, 'to' => false]],
        ]);

        $this->invalidateUserCaches($user);

        return back()->with('success', "Deactivated {$name}.");
    }

    public function export(AdminUserExportRequest $request): StreamedResponse
    {
        $this->auditService->log(AnalyticsEvent::ADMIN_USERS_EXPORTED, [
            'filters' => $request->validated(),
        ]);

        $query = $this->buildUserQuery($request->validated())
            ->limit(config('pagination.export.max_rows', 10000));

        return (new CsvExport([
            'ID' => 'id',
            'Name' => 'name',
            'Email' => 'email',
            'Admin' => fn ($u) => $u->is_admin ? 'Yes' : 'No',
            'Verified' => fn ($u) => $u->email_verified_at ? 'Yes' : 'No',
            'Last Login' => fn ($u) => $u->last_login_at?->toISOString() ?? '',
            'Created' => fn ($u) => $u->created_at?->toISOString() ?? '',
            'Status' => fn ($u) => $u->deleted_at ? 'Deactivated' : 'Active',
        ]))->filename('users-'.now()->format('Y-m-d').'.csv')
            ->fromQuery($query);
    }

    public function sendPasswordReset(AdminSendPasswordResetRequest $request, User $user): RedirectResponse
    {
        if (! $user->hasPassword()) {
            return back()->with('error', 'User has no password (OAuth-only account).');
        }

        /** @var PasswordBroker $broker */
        $broker = Password::broker();
        $token = $broker->createToken($user);
        $user->sendPasswordResetNotification($token);

        $this->auditService->log(AnalyticsEvent::ADMIN_PASSWORD_RESET_SENT, [
            'target_user_id' => $user->id,
            'target_email' => $user->email,
        ]);

        return back()->with('success', 'Password reset email sent.');
    }

    /** @return Builder<User> */
    private function buildUserQuery(array $validated): Builder
    {
        $status = $validated['status'] ?? 'all';
        $query = match ($status) {
            'active' => User::query(),
            'deactivated' => User::onlyTrashed(),
            default => User::withTrashed(),
        };

        if (! empty($validated['search'])) {
            $query->where(function ($q) use ($validated) {
                QueryHelper::whereLike($q, 'name', $validated['search']);
                QueryHelper::whereLike($q, 'email', $validated['search'], 'or');
            });
        }

        if (isset($validated['admin']) && $validated['admin'] !== '') {
            $query->where('is_admin', (bool) $validated['admin']);
        }

        if (isset($validated['verified']) && $validated['verified'] !== '') {
            if ((int) $validated['verified'] === 1) {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        $allowedSorts = ['name', 'email', 'created_at', 'last_login_at', 'is_admin'];
        $sort = in_array($validated['sort'] ?? null, $allowedSorts, true) ? $validated['sort'] : 'created_at';
        $dir = ($validated['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $dir);

        return $query;
    }

    private function invalidateUserCaches(User $user): void
    {
        $this->cacheManager->invalidateUser($user->id);
    }
}
