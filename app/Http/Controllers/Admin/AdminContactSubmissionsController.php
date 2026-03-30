<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AnalyticsEvent;
use App\Helpers\QueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminBulkContactSubmissionRequest;
use App\Http\Requests\Admin\AdminContactSubmissionExportRequest;
use App\Http\Requests\Admin\AdminContactSubmissionsIndexRequest;
use App\Http\Requests\Admin\AdminUpdateContactSubmissionRequest;
use App\Models\ContactSubmission;
use App\Services\AuditService;
use App\Support\CsvExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminContactSubmissionsController extends Controller
{
    public function __construct(
        private AuditService $auditService,
    ) {}

    public function index(AdminContactSubmissionsIndexRequest $request): Response
    {
        $allowedSorts = ['created_at', 'status', 'name', 'email'];
        $sort = in_array($request->validated('sort'), $allowedSorts, true) ? $request->validated('sort') : 'created_at';
        $dir = ($request->validated('dir') ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query = ContactSubmission::orderBy($sort, $dir);

        if ($request->validated('status')) {
            $query->byStatus($request->validated('status'));
        }

        if ($search = $request->validated('search')) {
            $escaped = QueryHelper::escapeLike($search);
            $query->where(function ($q) use ($escaped) {
                $q->whereRaw("name LIKE ? ESCAPE '|'", ["%{$escaped}%"])
                    ->orWhereRaw("email LIKE ? ESCAPE '|'", ["%{$escaped}%"])
                    ->orWhereRaw("subject LIKE ? ESCAPE '|'", ["%{$escaped}%"]);
            });
        }

        $submissions = $query->paginate(config('pagination.admin.contact_submissions', 50))
            ->withQueryString();

        return Inertia::render('Admin/ContactSubmissions/Index', [
            'submissions' => $submissions,
            'filters' => $request->only('status', 'search', 'sort', 'dir'),
            'counts' => [
                'new' => ContactSubmission::byStatus('new')->count(),
                'replied' => ContactSubmission::byStatus('replied')->count(),
                'spam' => ContactSubmission::byStatus('spam')->count(),
            ],
        ]);
    }

    public function show(ContactSubmission $contactSubmission): Response
    {
        return Inertia::render('Admin/ContactSubmissions/Show', [
            'submission' => $contactSubmission,
        ]);
    }

    public function update(AdminUpdateContactSubmissionRequest $request, ContactSubmission $contactSubmission): RedirectResponse
    {
        $newStatus = $request->validated('status');
        $data = ['status' => $newStatus];

        if ($newStatus === 'replied' && $contactSubmission->status !== 'replied') {
            $data['replied_at'] = now();
        } elseif ($newStatus !== 'replied') {
            $data['replied_at'] = null;
        }

        $contactSubmission->update($data);

        $this->auditService->log(AnalyticsEvent::ADMIN_CONTACT_SUBMISSION_UPDATED, [
            'submission_id' => $contactSubmission->id,
            'status' => $newStatus,
        ]);

        return back()->with('success', 'Submission updated.');
    }

    public function bulkUpdate(AdminBulkContactSubmissionRequest $request): RedirectResponse
    {
        $ids = $request->validated('ids');
        $action = $request->validated('action');

        $count = 0;
        DB::transaction(function () use ($ids, $action, &$count) {
            $submissions = ContactSubmission::whereIn('id', $ids)->lockForUpdate()->get();
            foreach ($submissions as $submission) {
                if ($action === 'delete') {
                    $submission->delete();
                } elseif ($action === 'spam') {
                    $submission->update(['status' => 'spam', 'replied_at' => null]);
                } elseif ($action === 'replied') {
                    $submission->update([
                        'status' => 'replied',
                        'replied_at' => $submission->replied_at ?? now(),
                    ]);
                }
                $count++;
            }
        });

        $this->auditService->log(AnalyticsEvent::ADMIN_CONTACT_SUBMISSION_BULK_UPDATED, [
            'ids' => $ids,
            'action' => $action,
            'count' => $count,
        ]);

        return back()->with('success', "Bulk {$action} applied to {$count} submission(s).");
    }

    public function destroy(ContactSubmission $contactSubmission): RedirectResponse
    {
        $this->auditService->log(AnalyticsEvent::ADMIN_CONTACT_SUBMISSION_DELETED, [
            'submission_id' => $contactSubmission->id,
            'email' => $contactSubmission->email,
        ]);

        $contactSubmission->delete();

        return redirect()->route('admin.contact-submissions.index')->with('success', 'Submission deleted.');
    }

    public function export(AdminContactSubmissionExportRequest $request): StreamedResponse
    {
        $this->auditService->log(AnalyticsEvent::ADMIN_CONTACT_SUBMISSIONS_EXPORTED, [
            'filters' => $request->validated(),
        ]);

        $query = ContactSubmission::latest();

        if ($request->validated('status')) {
            $query->byStatus($request->validated('status'));
        }

        if ($search = $request->validated('search')) {
            $escaped = QueryHelper::escapeLike($search);
            $query->where(function ($q) use ($escaped) {
                $q->whereRaw("name LIKE ? ESCAPE '|'", ["%{$escaped}%"])
                    ->orWhereRaw("email LIKE ? ESCAPE '|'", ["%{$escaped}%"])
                    ->orWhereRaw("subject LIKE ? ESCAPE '|'", ["%{$escaped}%"]);
            });
        }

        $query->limit(config('pagination.export.max_rows', 10000));

        return (new CsvExport([
            'ID' => 'id',
            'Name' => 'name',
            'Email' => 'email',
            'Subject' => 'subject',
            'Message' => 'message',
            'Status' => 'status',
            'Replied At' => fn ($s) => $s->replied_at?->toISOString() ?? '',
            'Created' => fn ($s) => $s->created_at?->toISOString() ?? '',
        ]))->filename('contact-submissions-'.now()->format('Y-m-d').'.csv')
            ->fromQuery($query);
    }
}
