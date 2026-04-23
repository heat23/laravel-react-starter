<?php

namespace App\Http\Controllers\Admin\Users;

use App\Enums\AuditEvent;
use App\Http\Controllers\Admin\Users\Concerns\BuildsUserQuery;
use App\Http\Controllers\Admin\Users\Concerns\InvalidatesUserCaches;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminBulkDeactivateRequest;
use App\Http\Requests\Admin\AdminBulkRestoreRequest;
use App\Http\Requests\Admin\AdminUserExportRequest;
use App\Models\User;
use App\Services\AuditService;
use App\Services\CacheInvalidationManager;
use App\Support\CsvExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminUserBulkController extends Controller
{
    use BuildsUserQuery;
    use InvalidatesUserCaches;

    public function __construct(
        private AuditService $auditService,
        private CacheInvalidationManager $cacheManager,
    ) {}

    public function bulkDeactivate(AdminBulkDeactivateRequest $request): RedirectResponse
    {
        $adminId = $request->user()->id;
        $ids = collect($request->input('ids'))->reject(fn ($id) => (int) $id === $adminId);

        $users = User::whereIn('id', $ids)->whereNull('deleted_at')->where('is_admin', false)->get();

        $count = DB::transaction(function () use ($users) {
            $deactivated = 0;
            foreach ($users as $user) {
                $user->delete();
                $this->auditService->log(AuditEvent::ADMIN_USER_DEACTIVATED, [
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
                $this->auditService->log(AuditEvent::ADMIN_USER_RESTORED, [
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

    public function export(AdminUserExportRequest $request): StreamedResponse
    {
        $this->auditService->log(AuditEvent::ADMIN_USERS_EXPORTED, [
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
}
