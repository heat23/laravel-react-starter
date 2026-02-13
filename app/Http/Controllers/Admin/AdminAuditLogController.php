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
        $validated = $request->validated();
        $query = AuditLog::with('user');

        if (! empty($validated['event'])) {
            $query->byEvent($validated['event']);
        }

        if (! empty($validated['user_id'])) {
            $query->byUser((int) $validated['user_id']);
        }

        if (! empty($validated['from'])) {
            $query->where('created_at', '>=', Carbon::parse($validated['from'])->startOfDay());
        }

        if (! empty($validated['to'])) {
            $query->where('created_at', '<=', Carbon::parse($validated['to'])->endOfDay());
        }

        $logs = $query->latest()->paginate(config('features.admin.pagination.audit_logs', 50))->through(fn (AuditLog $log) => [
            'id' => $log->id,
            'event' => $log->event,
            'user_name' => $log->user?->name,
            'user_email' => $log->user?->email,
            'user_id' => $log->user_id,
            'ip' => $log->ip,
            'user_agent' => $log->user_agent,
            'metadata' => $log->metadata,
            'created_at' => $log->created_at?->toISOString(),
        ]);

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
            'auditLog' => [
                'id' => $auditLog->id,
                'event' => $auditLog->event,
                'user_id' => $auditLog->user_id,
                'user_name' => $auditLog->user?->name,
                'user_email' => $auditLog->user?->email,
                'ip' => $auditLog->ip,
                'user_agent' => $auditLog->user_agent,
                'metadata' => $auditLog->metadata,
                'created_at' => $auditLog->created_at?->toISOString(),
            ],
        ]);
    }

    public function export(AdminExportRequest $request): StreamedResponse
    {
        $validated = $request->validated();
        $query = AuditLog::with('user');

        if (! empty($validated['event'])) {
            $query->byEvent($validated['event']);
        }

        if (! empty($validated['user_id'])) {
            $query->byUser((int) $validated['user_id']);
        }

        if (! empty($validated['from'])) {
            $query->where('created_at', '>=', Carbon::parse($validated['from'])->startOfDay());
        }

        if (! empty($validated['to'])) {
            $query->where('created_at', '<=', Carbon::parse($validated['to'])->endOfDay());
        }

        $maxRows = 10000;

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
}
