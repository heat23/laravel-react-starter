<?php

namespace App\Http\Controllers\Admin\Users;

use App\Enums\AuditEvent;
use App\Http\Controllers\Admin\Users\Concerns\InvalidatesUserCaches;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminToggleActiveRequest;
use App\Http\Requests\Admin\AdminToggleAdminRequest;
use App\Models\User;
use App\Services\AuditService;
use App\Services\CacheInvalidationManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class AdminUserPrivilegeController extends Controller
{
    use InvalidatesUserCaches;

    public function __construct(
        private AuditService $auditService,
        private CacheInvalidationManager $cacheManager,
    ) {}

    public function toggleAdmin(AdminToggleAdminRequest $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Cannot change own admin status.');
        }

        $wasAdmin = $user->is_admin;

        $blocked = DB::transaction(function () use ($user, $wasAdmin) {
            $adminCount = User::where('is_admin', true)->whereNull('deleted_at')->lockForUpdate()->count();

            if ($wasAdmin && $adminCount <= 2) {
                return true;
            }

            $user->is_admin = ! $wasAdmin;
            $user->save();

            return false;
        });

        if ($blocked) {
            return back()->with('error', 'Cannot remove admin status. At least two admin accounts must exist.');
        }

        $this->cacheManager->invalidateDashboard();

        $this->auditService->log(AuditEvent::ADMIN_TOGGLE_ADMIN, [
            'target_user_id' => $user->id,
            'target_email' => $user->email,
            'changes' => ['is_admin' => ['from' => $wasAdmin, 'to' => $user->is_admin]],
        ]);

        $name = e($user->name);

        return back()->with('success', $user->is_admin ? "Made {$name} an admin." : "Removed admin from {$name}.");
    }

    public function toggleActive(AdminToggleActiveRequest $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Cannot deactivate own account.');
        }

        $name = e($user->name);

        if ($user->trashed()) {
            $user->restore();
            $this->auditService->log(AuditEvent::ADMIN_USER_RESTORED, [
                'target_user_id' => $user->id,
                'target_email' => $user->email,
                'changes' => ['active' => ['from' => false, 'to' => true]],
            ]);

            $this->invalidateUserCaches($user);

            return back()->with('success', "Restored {$name}.");
        }

        $user->delete();
        $this->auditService->log(AuditEvent::ADMIN_USER_DEACTIVATED, [
            'target_user_id' => $user->id,
            'target_email' => $user->email,
            'changes' => ['active' => ['from' => true, 'to' => false]],
        ]);

        $this->invalidateUserCaches($user);

        return back()->with('success', "Deactivated {$name}.");
    }
}
