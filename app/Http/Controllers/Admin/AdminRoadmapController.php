<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditEvent;
use App\Helpers\QueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminReorderRoadmapRequest;
use App\Http\Requests\Admin\AdminRoadmapIndexRequest;
use App\Http\Requests\Admin\AdminStoreRoadmapRequest;
use App\Http\Requests\Admin\AdminUpdateRoadmapRequest;
use App\Models\RoadmapEntry;
use App\Services\AuditService;
use App\Support\CsvExport;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminRoadmapController extends Controller
{
    public function __construct(
        private AuditService $auditService,
    ) {}

    public function export(): StreamedResponse
    {
        $this->auditService->log(AuditEvent::ADMIN_ROADMAP_EXPORTED, []);

        $query = RoadmapEntry::withCount('feedbackSubmissions')
            ->orderBy('status')
            ->orderBy('display_order')
            ->limit(config('pagination.export.max_rows', 10000));

        return (new CsvExport([
            'ID' => 'id',
            'Title' => 'title',
            'Slug' => 'slug',
            'Status' => 'status',
            'Description' => fn ($e) => $e->description ?? '',
            'Display Order' => 'display_order',
            'Upvotes' => 'feedback_submissions_count',
            'Created' => fn ($e) => $e->created_at?->toISOString() ?? '',
            'Updated' => fn ($e) => $e->updated_at?->toISOString() ?? '',
        ]))->filename('roadmap-'.now()->format('Y-m-d').'.csv')
            ->fromQuery($query);
    }

    public function index(AdminRoadmapIndexRequest $request): Response
    {
        $validated = $request->validated();

        $query = RoadmapEntry::withCount('feedbackSubmissions')
            ->orderBy('display_order')
            ->orderBy('status');

        if ($search = $validated['search'] ?? null) {
            $query->where(function ($q) use ($search) {
                QueryHelper::whereLike($q, 'title', $search);
                QueryHelper::whereLike($q, 'description', $search, 'or');
            });
        }

        if ($status = $validated['status'] ?? null) {
            $query->where('status', $status);
        }

        $entries = $query->paginate(config('pagination.admin.roadmap', 100));
        $filters = array_filter($validated, fn ($v) => $v !== null);

        return Inertia::render('App/Admin/Roadmap/Index', [
            'entries' => $entries,
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('App/Admin/Roadmap/Create');
    }

    public function store(AdminStoreRoadmapRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $baseSlug = Str::slug($validated['title']);
        $counter = 1;
        $entry = null;
        do {
            $validated['slug'] = $counter === 1 ? $baseSlug : $baseSlug.'-'.($counter - 1);
            try {
                $entry = RoadmapEntry::create($validated);
            } catch (UniqueConstraintViolationException) {
                $counter++;
            }
        } while ($entry === null && $counter <= 100);

        if ($entry === null) {
            return back()->withErrors(['title' => 'Could not generate a unique slug. Please use a different title.']);
        }

        $this->auditService->log(AuditEvent::ADMIN_ROADMAP_ENTRY_CREATED, [
            'entry_id' => $entry->id,
            'title' => $entry->title,
            'status' => $entry->status,
        ]);

        return redirect()->route('admin.roadmap.index')->with('success', 'Roadmap entry created.');
    }

    public function reorder(AdminReorderRoadmapRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            foreach ($validated['items'] as $item) {
                RoadmapEntry::where('id', $item['id'])->update([
                    'status' => $item['status'],
                    'display_order' => $item['display_order'],
                ]);
            }
        });

        $this->auditService->log(AuditEvent::ADMIN_ROADMAP_ENTRY_UPDATED, [
            'action' => 'reorder',
            'count' => count($validated['items']),
        ]);

        return back();
    }

    public function update(AdminUpdateRoadmapRequest $request, RoadmapEntry $roadmapEntry): RedirectResponse
    {
        $validated = $request->validated();

        $roadmapEntry->update($validated);

        $this->auditService->log(AuditEvent::ADMIN_ROADMAP_ENTRY_UPDATED, [
            'entry_id' => $roadmapEntry->id,
            'title' => $roadmapEntry->title,
            'changes' => array_keys($validated),
        ]);

        return back()->with('success', 'Roadmap entry updated.');
    }

    public function destroy(RoadmapEntry $roadmapEntry): RedirectResponse
    {
        $this->auditService->log(AuditEvent::ADMIN_ROADMAP_ENTRY_DELETED, [
            'entry_id' => $roadmapEntry->id,
            'title' => $roadmapEntry->title,
        ]);

        $roadmapEntry->delete();

        return redirect()->route('admin.roadmap.index')->with('success', 'Roadmap entry deleted.');
    }
}
