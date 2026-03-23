<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\QueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminEmailSendLogIndexRequest;
use App\Models\EmailSendLog;
use Inertia\Inertia;
use Inertia\Response;

class AdminEmailSendLogController extends Controller
{
    public function index(AdminEmailSendLogIndexRequest $request): Response
    {
        $allowedSorts = ['sent_at', 'sequence_type', 'email_number'];
        $sort = in_array($request->validated('sort'), $allowedSorts, true) ? $request->validated('sort') : 'sent_at';
        $dir = ($request->validated('dir') ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query = EmailSendLog::with(['user' => fn ($q) => $q->withTrashed()])
            ->orderBy($sort, $dir);

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

        $logs = $query->paginate(config('pagination.admin.email_send_logs', 50))
            ->withQueryString();

        $sequenceTypes = EmailSendLog::distinct()->orderBy('sequence_type')->pluck('sequence_type');

        return Inertia::render('Admin/EmailSendLogs/Index', [
            'logs' => $logs,
            'sequenceTypes' => $sequenceTypes,
            'filters' => $request->only('search', 'sequence_type', 'sort', 'dir'),
        ]);
    }
}
