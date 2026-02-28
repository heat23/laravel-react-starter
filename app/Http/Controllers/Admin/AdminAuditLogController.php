<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminCacheKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminAuditLogIndexRequest;
use App\Http\Requests\Admin\AdminExportRequest;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAuditLogController extends Controller
{
    public function index(AdminAuditLogIndexRequest $request): Response
    {
        $query = $this->applyFilters(AuditLog::with('user'), $request->validated());

        $logs = $query->latest()->paginate(config('pagination.admin.audit_logs', 50))->through(fn (AuditLog $log) => $log->toDetailArray());

        $eventTypes = Cache::remember(AdminCacheKey::AUDIT_EVENT_TYPES->value, AdminCacheKey::DEFAULT_TTL, function () {
            return AuditLog::distinct()->pluck('event')->sort()->values();
        });

        return Inertia::render('Admin/AuditLogs/Index', [
            'logs' => $logs,
            'eventTypes' => $eventTypes,
            'filters' => $request->only('event', 'user_id', 'from', 'to'),
        ]);
    }

    public function show(AuditLog $auditLog): Response
    {
        $auditLog->load('user');

        return Inertia::render('Admin/AuditLogs/Show', [
            'auditLog' => $auditLog->toDetailArray(),
        ]);
    }

    public function export(AdminExportRequest $request): StreamedResponse
    {
        $query = $this->applyFilters(AuditLog::with('user'), $request->validated());

        $maxRows = config('pagination.export.max_rows', 10000);

        return response()->streamDownload(function () use ($query, $maxRows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Event', 'User Email', 'IP', 'Metadata', 'Date']);

            $exported = 0;
            foreach ($query->lazyByIdDesc(500) as $log) {
                if ($exported >= $maxRows) {
                    break;
                }
                fputcsv($handle, array_map(
                    fn ($v) => is_string($v) && isset($v[0]) && in_array($v[0], ['=', '+', '-', '@', "\t", "\r"])
                        ? "'".$v
                        : $v,
                    [
                        $log->id,
                        $log->event,
                        $log->user?->email ?? 'System',
                        $log->ip,
                        json_encode($log->metadata),
                        $log->created_at?->toISOString(),
                    ]
                ));
                $exported++;
            }

            fclose($handle);
        }, 'audit-logs-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
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

        return $query;
    }
}
