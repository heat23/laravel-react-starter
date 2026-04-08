<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AnalyticsEvent;
use App\Helpers\QueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminBulkFeedbackRequest;
use App\Http\Requests\Admin\AdminFeedbackExportRequest;
use App\Http\Requests\Admin\AdminFeedbackIndexRequest;
use App\Http\Requests\Admin\AdminUpdateFeedbackRequest;
use App\Models\Feedback;
use App\Services\AuditService;
use App\Services\CacheInvalidationManager;
use App\Support\CsvExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminFeedbackController extends Controller
{
    public function __construct(
        private AuditService $auditService,
        private CacheInvalidationManager $cacheManager,
    ) {}

    public function index(AdminFeedbackIndexRequest $request): Response
    {
        $allowedSorts = ['created_at', 'priority', 'status', 'type'];
        $sort = in_array($request->validated('sort'), $allowedSorts, true) ? $request->validated('sort') : 'created_at';
        $dir = ($request->validated('dir') ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query = Feedback::with(['user' => fn ($q) => $q->withTrashed()])
            ->orderBy($sort, $dir);

        if ($request->validated('type')) {
            $query->byType($request->validated('type'));
        }

        if ($request->validated('status')) {
            $query->byStatus($request->validated('status'));
        }

        if ($search = $request->validated('search')) {
            $escaped = QueryHelper::escapeLike($search);
            $query->where(function ($q) use ($escaped) {
                $q->whereRaw("message LIKE ? ESCAPE '|'", ["%{$escaped}%"])
                    ->orWhereHas('user', fn ($uq) => $uq->whereRaw("name LIKE ? ESCAPE '|'", ["%{$escaped}%"])
                        ->orWhereRaw("email LIKE ? ESCAPE '|'", ["%{$escaped}%"]));
            });
        }

        $perPage = (int) ($request->validated('per_page') ?? config('pagination.admin.feedback', 50));
        $feedback = $query->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Feedback/Index', [
            'feedback' => $feedback,
            'filters' => array_merge(
                $request->only('type', 'status', 'search', 'sort', 'dir'),
                ['per_page' => (string) $perPage]
            ),
            'counts' => [
                'open' => Feedback::byStatus('open')->count(),
                'in_review' => Feedback::byStatus('in_review')->count(),
                'resolved' => Feedback::byStatus('resolved')->count(),
            ],
        ]);
    }

    public function show(Feedback $feedback): Response
    {
        $feedback->load(['user' => fn ($q) => $q->withTrashed()]);

        return Inertia::render('Admin/Feedback/Show', [
            'feedback' => $feedback,
        ]);
    }

    public function update(AdminUpdateFeedbackRequest $request, Feedback $feedback): RedirectResponse
    {
        $validated = $request->validated();

        if (isset($validated['status'])) {
            if ($validated['status'] === 'resolved' && $feedback->status !== 'resolved') {
                $validated['resolved_at'] = now();
            } elseif ($validated['status'] !== 'resolved' && $feedback->status === 'resolved') {
                $validated['resolved_at'] = null;
            }
        }

        $feedback->update($validated);

        $this->auditService->log(AnalyticsEvent::ADMIN_FEEDBACK_UPDATED, [
            'feedback_id' => $feedback->id,
            'changes' => array_keys($validated),
        ]);

        $this->cacheManager->invalidateDashboard();

        return back()->with('success', 'Feedback updated.');
    }

    public function bulkUpdate(AdminBulkFeedbackRequest $request): RedirectResponse
    {
        $ids = $request->validated('ids');
        $action = $request->validated('action');

        $count = 0;
        DB::transaction(function () use ($ids, $action, &$count) {
            $feedbacks = Feedback::whereIn('id', $ids)->lockForUpdate()->get();
            foreach ($feedbacks as $item) {
                if ($action === 'delete') {
                    $item->delete();
                } elseif ($action === 'resolve') {
                    $item->update([
                        'status' => 'resolved',
                        'resolved_at' => now(),
                    ]);
                } elseif ($action === 'decline') {
                    $item->update(['status' => 'declined']);
                }
                $count++;
            }
        });

        $this->auditService->log(AnalyticsEvent::ADMIN_FEEDBACK_BULK_UPDATED, [
            'ids' => $ids,
            'action' => $action,
            'count' => $count,
        ]);

        $this->cacheManager->invalidateDashboard();

        return back()->with('success', "Bulk {$action} applied to {$count} item(s).");
    }

    public function destroy(Feedback $feedback): RedirectResponse
    {
        $this->auditService->log(AnalyticsEvent::ADMIN_FEEDBACK_DELETED, [
            'feedback_id' => $feedback->id,
            'type' => $feedback->type,
            'status' => $feedback->status,
        ]);

        $feedback->delete();

        $this->cacheManager->invalidateDashboard();

        return redirect()->route('admin.feedback.index')->with('success', 'Feedback deleted.');
    }

    public function export(AdminFeedbackExportRequest $request): StreamedResponse
    {
        $this->auditService->log(AnalyticsEvent::ADMIN_FEEDBACK_EXPORTED, [
            'filters' => $request->validated(),
        ]);

        $query = Feedback::with(['user' => fn ($q) => $q->withTrashed()])
            ->latest();

        if ($request->validated('type')) {
            $query->byType($request->validated('type'));
        }

        if ($request->validated('status')) {
            $query->byStatus($request->validated('status'));
        }

        if ($search = $request->validated('search')) {
            $escaped = QueryHelper::escapeLike($search);
            $query->where(function ($q) use ($escaped) {
                $q->whereRaw("message LIKE ? ESCAPE '|'", ["%{$escaped}%"])
                    ->orWhereHas('user', fn ($uq) => $uq->whereRaw("name LIKE ? ESCAPE '|'", ["%{$escaped}%"])
                        ->orWhereRaw("email LIKE ? ESCAPE '|'", ["%{$escaped}%"]));
            });
        }

        $query->limit(config('pagination.export.max_rows', 10000));

        return (new CsvExport([
            'ID' => 'id',
            'Type' => 'type',
            'Status' => 'status',
            'Priority' => 'priority',
            'Message' => 'message',
            'User Name' => fn ($f) => $f->user !== null ? $f->user->name : 'Guest',
            'User Email' => fn ($f) => $f->user !== null ? $f->user->email : '',
            'Admin Notes' => 'admin_notes',
            'Resolved At' => fn ($f) => $f->resolved_at?->toISOString() ?? '',
            'Created' => fn ($f) => $f->created_at?->toISOString() ?? '',
        ]))->filename('feedback-'.now()->format('Y-m-d').'.csv')
            ->fromQuery($query);
    }
}
