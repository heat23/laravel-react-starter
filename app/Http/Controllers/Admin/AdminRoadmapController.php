<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AnalyticsEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminStoreRoadmapRequest;
use App\Http\Requests\Admin\AdminUpdateRoadmapRequest;
use App\Models\RoadmapEntry;
use App\Services\AuditService;
use App\Support\CsvExport;
use Illuminate\Http\RedirectResponse;
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
        $this->auditService->log(AnalyticsEvent::ADMIN_ROADMAP_EXPORTED, []);

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

    public function index(): Response
    {
        $entries = RoadmapEntry::withCount('feedbackSubmissions')
            ->orderBy('display_order')
            ->orderBy('status')
            ->get();

        return Inertia::render('Admin/Roadmap/Index', [
            'entries' => $entries,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Roadmap/Create');
    }

    public function store(AdminStoreRoadmapRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['title']);

        $entry = RoadmapEntry::create($validated);

        $this->auditService->log(AnalyticsEvent::ADMIN_ROADMAP_ENTRY_CREATED, [
            'entry_id' => $entry->id,
            'title' => $entry->title,
            'status' => $entry->status,
        ]);

        return redirect()->route('admin.roadmap.index')->with('success', 'Roadmap entry created.');
    }

    public function update(AdminUpdateRoadmapRequest $request, RoadmapEntry $roadmapEntry): RedirectResponse
    {
        $validated = $request->validated();

        $roadmapEntry->update($validated);

        $this->auditService->log(AnalyticsEvent::ADMIN_ROADMAP_ENTRY_UPDATED, [
            'entry_id' => $roadmapEntry->id,
            'title' => $roadmapEntry->title,
            'changes' => array_keys($validated),
        ]);

        return back()->with('success', 'Roadmap entry updated.');
    }

    public function destroy(RoadmapEntry $roadmapEntry): RedirectResponse
    {
        $this->auditService->log(AnalyticsEvent::ADMIN_ROADMAP_ENTRY_DELETED, [
            'entry_id' => $roadmapEntry->id,
            'title' => $roadmapEntry->title,
        ]);

        $roadmapEntry->delete();

        return redirect()->route('admin.roadmap.index')->with('success', 'Roadmap entry deleted.');
    }
}
