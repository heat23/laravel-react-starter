<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminCacheKey;
use App\Enums\AuditEvent;
use App\Helpers\QueryHelper;
use App\Http\Controllers\Admin\Concerns\ListsAdminResources;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminAuditLogIndexRequest;
use App\Http\Requests\Admin\AdminExportRequest;
use App\Models\AuditLog;
use App\Services\AuditService;
use App\Support\CsvExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAuditLogController extends Controller
{
    use ListsAdminResources;

    public function __construct(
        private AuditService $auditService,
    ) {}

    public function index(AdminAuditLogIndexRequest $request): Response
    {
        $validated = $request->validated();
        $query = $this->applyFilters(AuditLog::with('user'), $validated);

        $perPage = (int) ($request->validated('per_page') ?? config('pagination.admin.audit_logs', 50));
        $logs = $this->paginateAdminList($query, $request, ['event', 'created_at'], 'created_at', 'desc', $perPage)
            ->through(fn (AuditLog $log) => $log->toDetailArray());

        $eventTypes = Cache::remember(AdminCacheKey::AUDIT_EVENT_TYPES->value, AdminCacheKey::DEFAULT_TTL, function () {
            return AuditLog::distinct()->pluck('event')->sort()->values();
        });

        return Inertia::render('App/Admin/AuditLogs/Index', [
            'logs' => $logs,
            'eventTypes' => $eventTypes,
            'filters' => array_merge(
                $request->only('event', 'user_id', 'from', 'to', 'ip', 'search', 'sort', 'dir'),
                ['per_page' => (string) $perPage]
            ),
        ]);
    }

    public function show(AuditLog $auditLog): Response
    {
        $auditLog->load('user');

        return Inertia::render('App/Admin/AuditLogs/Show', [
            'auditLog' => $auditLog->toDetailArray(),
        ]);
    }

    /**
     * Export audit logs as a CSV file.
     *
     * Security note: The metadata column is intentionally exported in full.
     * Audit log exports are compliance artifacts — stripping fields would
     * produce incomplete records unsuitable for incident investigation or
     * regulatory review. Access is already restricted to super-admin users
     * who can view the same data via the UI. The metadata may contain IP
     * addresses, user-agent strings, and before/after state diffs; this is
     * by design and must not be filtered or truncated.
     */
    public function export(AdminExportRequest $request): StreamedResponse
    {
        $this->auditService->log(AuditEvent::ADMIN_AUDIT_LOGS_EXPORTED, [
            'filters' => $request->validated(),
        ]);

        $query = $this->applyFilters(AuditLog::with('user'), $request->validated());

        $maxRows = config('pagination.export.max_rows', 10000);
        $items = $query->orderByDesc('id')->lazy(500)->take($maxRows);

        return (new CsvExport([
            'ID' => fn (AuditLog $log) => (string) $log->id,
            'Event' => fn (AuditLog $log) => $log->event,
            'User Email' => fn (AuditLog $log) => $log->user?->email ?? 'System',
            'IP' => fn (AuditLog $log) => $log->ip,
            'Metadata' => fn (AuditLog $log) => json_encode($log->metadata),
            'Date' => fn (AuditLog $log) => $log->created_at?->toISOString(),
        ]))
            ->filename('audit-logs-'.now()->format('Y-m-d').'.csv')
            ->fromCollection($items);
    }

    private function applyFilters($query, array $filters)
    {
        if (! empty($filters['event'])) {
            $query->byEvent($filters['event']);
        }

        if (! empty($filters['user_id'])) {
            $query->byUser((int) $filters['user_id']);
        }

        if (! empty($filters['from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['from'])->startOfDay());
        }

        if (! empty($filters['to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['to'])->endOfDay());
        }

        if (! empty($filters['ip'])) {
            $query->where('ip', $filters['ip']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                QueryHelper::whereLike($q, 'metadata', $search, 'and');
                QueryHelper::whereLike($q, 'event', $search, 'or');
            });
        }

        return $query;
    }
}
