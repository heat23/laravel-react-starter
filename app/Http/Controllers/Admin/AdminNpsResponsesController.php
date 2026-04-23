<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditEvent;
use App\Helpers\QueryHelper;
use App\Http\Controllers\Admin\Concerns\ListsAdminResources;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminNpsResponseExportRequest;
use App\Http\Requests\Admin\AdminNpsResponseIndexRequest;
use App\Models\NpsResponse;
use App\Services\AuditService;
use App\Support\CsvExport;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminNpsResponsesController extends Controller
{
    use ListsAdminResources;

    public function __construct(
        private AuditService $auditService,
    ) {}

    public function index(AdminNpsResponseIndexRequest $request): Response
    {
        $query = NpsResponse::with(['user' => fn ($q) => $q->withTrashed()]);

        if ($category = $request->validated('category')) {
            $query->when($category === 'promoter', fn ($q) => $q->where('score', '>=', 9))
                ->when($category === 'passive', fn ($q) => $q->whereBetween('score', [7, 8]))
                ->when($category === 'detractor', fn ($q) => $q->where('score', '<=', 6));
        }

        if ($trigger = $request->validated('survey_trigger')) {
            $query->where('survey_trigger', $trigger);
        }

        if ($search = $request->validated('search')) {
            $escaped = QueryHelper::escapeLike($search);
            $query->where(function ($q) use ($escaped) {
                $q->whereRaw("comment LIKE ? ESCAPE '|'", ["%{$escaped}%"])
                    ->orWhereHas('user', fn ($uq) => $uq
                        ->whereRaw("name LIKE ? ESCAPE '|'", ["%{$escaped}%"])
                        ->orWhereRaw("email LIKE ? ESCAPE '|'", ["%{$escaped}%"])
                    );
            });
        }

        $responses = $this->paginateAdminList($query, $request, ['score', 'created_at'], 'created_at', 'desc', config('pagination.admin.nps_responses', 50));

        // Single aggregation query replaces 3 separate count queries
        $stats = NpsResponse::selectRaw(
            'SUM(CASE WHEN score >= 9 THEN 1 ELSE 0 END) as promoters,
             SUM(CASE WHEN score BETWEEN 7 AND 8 THEN 1 ELSE 0 END) as passives,
             SUM(CASE WHEN score <= 6 THEN 1 ELSE 0 END) as detractors,
             COUNT(*) as total'
        )->first();

        $promoters = (int) ($stats->promoters ?? 0);
        $passives = (int) ($stats->passives ?? 0);
        $detractors = (int) ($stats->detractors ?? 0);
        $total = (int) ($stats->total ?? 0);
        $npsScore = $total > 0
            ? (int) round((($promoters - $detractors) / $total) * 100)
            : null;

        $surveyTriggers = NpsResponse::distinct()->orderBy('survey_trigger')->pluck('survey_trigger');

        return Inertia::render('App/Admin/NpsResponses/Index', [
            'responses' => $responses,
            'filters' => $request->only('category', 'survey_trigger', 'search', 'sort', 'dir'),
            'summary' => [
                'total' => $total,
                'promoters' => $promoters,
                'passives' => $passives,
                'detractors' => $detractors,
                'nps_score' => $npsScore,
            ],
            'surveyTriggers' => $surveyTriggers,
        ]);
    }

    public function export(AdminNpsResponseExportRequest $request): StreamedResponse
    {
        $this->auditService->log(AuditEvent::ADMIN_NPS_EXPORTED, [
            'filters' => $request->validated(),
        ]);

        $query = NpsResponse::with(['user' => fn ($q) => $q->withTrashed()])
            ->latest('created_at');

        if ($category = $request->validated('category')) {
            $query->when($category === 'promoter', fn ($q) => $q->where('score', '>=', 9))
                ->when($category === 'passive', fn ($q) => $q->whereBetween('score', [7, 8]))
                ->when($category === 'detractor', fn ($q) => $q->where('score', '<=', 6));
        }

        if ($trigger = $request->validated('survey_trigger')) {
            $query->where('survey_trigger', $trigger);
        }

        if ($search = $request->validated('search')) {
            $escaped = QueryHelper::escapeLike($search);
            $query->where(function ($q) use ($escaped) {
                $q->whereRaw("comment LIKE ? ESCAPE '|'", ["%{$escaped}%"])
                    ->orWhereHas('user', fn ($uq) => $uq
                        ->whereRaw("name LIKE ? ESCAPE '|'", ["%{$escaped}%"])
                        ->orWhereRaw("email LIKE ? ESCAPE '|'", ["%{$escaped}%"])
                    );
            });
        }

        $query->limit(config('pagination.export.max_rows', 10000));

        return (new CsvExport([
            'ID' => 'id',
            'Score' => 'score',
            'Category' => fn ($r) => $r->category,
            'Comment' => fn ($r) => $r->comment ?? '',
            'Survey Trigger' => 'survey_trigger',
            'User Name' => fn ($r) => $r->user !== null ? $r->user->name : '[Deleted User]',
            'User Email' => fn ($r) => $r->user !== null ? $r->user->email : '',
            'Created' => fn ($r) => $r->created_at?->toISOString() ?? '',
        ]))->filename('nps-responses-'.now()->format('Y-m-d').'.csv')
            ->fromQuery($query);
    }
}
