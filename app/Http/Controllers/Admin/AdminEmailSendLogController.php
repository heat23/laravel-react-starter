<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditEvent;
use App\Helpers\QueryHelper;
use App\Http\Controllers\Admin\Concerns\ListsAdminResources;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminEmailSendLogExportRequest;
use App\Http\Requests\Admin\AdminEmailSendLogIndexRequest;
use App\Models\EmailSendLog;
use App\Services\AuditService;
use App\Support\CsvExport;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminEmailSendLogController extends Controller
{
    use ListsAdminResources;

    public function __construct(
        private AuditService $auditService,
    ) {}

    public function index(AdminEmailSendLogIndexRequest $request): Response
    {
        $query = EmailSendLog::with(['user' => fn ($q) => $q->withTrashed()]);

        if ($sequenceType = $request->validated('sequence_type')) {
            $query->where('sequence_type', $sequenceType);
        }

        if ($search = $request->validated('search')) {
            $escaped = QueryHelper::escapeLike($search);
            $query->whereHas('user', fn ($q) => $q
                ->whereRaw("name LIKE ? ESCAPE '|'", ["%{$escaped}%"])
                ->orWhereRaw("email LIKE ? ESCAPE '|'", ["%{$escaped}%"])
            );
        }

        $logs = $this->paginateAdminList($query, $request, ['sent_at', 'sequence_type', 'email_number'], 'sent_at', 'desc', config('pagination.admin.email_send_logs', 50));

        $sequenceTypes = EmailSendLog::distinct()->orderBy('sequence_type')->pluck('sequence_type');

        return Inertia::render('Admin/EmailSendLogs/Index', [
            'logs' => $logs,
            'sequenceTypes' => $sequenceTypes,
            'filters' => $request->only('search', 'sequence_type', 'sort', 'dir'),
        ]);
    }

    public function export(AdminEmailSendLogExportRequest $request): StreamedResponse
    {
        $this->auditService->log(AuditEvent::ADMIN_EMAIL_SEND_LOGS_EXPORTED, [
            'filters' => $request->validated(),
        ]);

        $query = EmailSendLog::with(['user' => fn ($q) => $q->withTrashed()])
            ->orderBy('sent_at', 'desc');

        if ($sequenceType = $request->validated('sequence_type')) {
            $query->where('sequence_type', $sequenceType);
        }

        if ($search = $request->validated('search')) {
            $escaped = QueryHelper::escapeLike($search);
            $query->whereHas('user', fn ($q) => $q
                ->whereRaw("name LIKE ? ESCAPE '|'", ["%{$escaped}%"])
                ->orWhereRaw("email LIKE ? ESCAPE '|'", ["%{$escaped}%"])
            );
        }

        $query->limit(config('pagination.export.max_rows', 10000));

        return (new CsvExport([
            'ID' => 'id',
            'User Name' => fn ($log) => $log->user !== null ? $log->user->name : '[Deleted User]',
            'User Email' => fn ($log) => $log->user !== null ? $log->user->email : '',
            'Sequence Type' => 'sequence_type',
            'Email Number' => 'email_number',
            'Sent At' => fn ($log) => $log->sent_at?->toISOString() ?? '',
        ]))->filename('email-send-logs-'.now()->format('Y-m-d').'.csv')
            ->fromQuery($query);
    }
}
