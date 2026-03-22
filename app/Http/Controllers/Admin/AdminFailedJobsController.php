<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AnalyticsEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminFailedJobIndexRequest;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminFailedJobsController extends Controller
{
    public function index(AdminFailedJobIndexRequest $request): Response
    {
        $query = DB::table('failed_jobs')->latest('failed_at');

        if ($request->validated('queue')) {
            $query->where('queue', $request->validated('queue'));
        }

        $jobs = $query
            ->paginate(config('pagination.admin.failed_jobs', 25))
            ->through(fn ($job) => [
                'id' => $job->id,
                'uuid' => $job->uuid,
                'connection' => $job->connection,
                'queue' => $job->queue,
                'payload_summary' => $this->extractJobName($job->payload),
                'failed_at' => $job->failed_at,
                'exception_summary' => Str::limit($job->exception, 200),
            ]);

        $queues = DB::table('failed_jobs')
            ->distinct()
            ->orderBy('queue')
            ->pluck('queue')
            ->values()
            ->toArray();

        return Inertia::render('Admin/FailedJobs/Index', [
            'jobs' => $jobs,
            'queues' => $queues,
            'filters' => ['queue' => $request->validated('queue')],
        ]);
    }

    public function show(int $id): Response
    {
        $job = DB::table('failed_jobs')->where('id', $id)->first();

        abort_unless((bool) $job, 404);

        return Inertia::render('Admin/FailedJobs/Show', [
            'job' => [
                'id' => $job->id,
                'uuid' => $job->uuid,
                'connection' => $job->connection,
                'queue' => $job->queue,
                'payload_summary' => $this->extractJobName($job->payload),
                'payload' => $job->payload,
                'exception' => $job->exception,
                'failed_at' => $job->failed_at,
            ],
        ]);
    }

    public function retry(int $id, AuditService $audit): RedirectResponse
    {
        $job = DB::table('failed_jobs')->where('id', $id)->first();

        abort_unless((bool) $job, 404);

        Artisan::call('queue:retry', ['id' => [$job->uuid]]);

        $audit->log(AnalyticsEvent::ADMIN_FAILED_JOB_RETRY, [
            'job_id' => $job->id,
            'job_uuid' => $job->uuid,
            'job_name' => $this->extractJobName($job->payload),
        ]);

        return redirect()->route('admin.failed-jobs.index')
            ->with('success', 'Job queued for retry.');
    }

    public function destroy(int $id, AuditService $audit): RedirectResponse
    {
        $job = DB::table('failed_jobs')->where('id', $id)->first();

        abort_unless((bool) $job, 404);

        DB::table('failed_jobs')->where('id', $id)->delete();

        $audit->log(AnalyticsEvent::ADMIN_FAILED_JOB_DELETE, [
            'job_id' => $job->id,
            'job_uuid' => $job->uuid,
            'job_name' => $this->extractJobName($job->payload),
        ]);

        return redirect()->route('admin.failed-jobs.index')
            ->with('success', 'Failed job deleted.');
    }

    public function bulkRetry(Request $request, AuditService $audit): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'max:100'],
            'ids.*' => ['required', 'string'],
        ]);

        $retried = 0;
        foreach ($validated['ids'] as $uuid) {
            try {
                if (Artisan::call('queue:retry', ['id' => [$uuid]]) === 0) {
                    $retried++;
                }
            } catch (\Throwable) {
                // Skip UUIDs that no longer exist or cause errors
            }
        }

        $audit->log(AnalyticsEvent::ADMIN_FAILED_JOB_BULK_RETRY, [
            'count' => $retried,
            'requested' => count($validated['ids']),
        ]);

        return redirect()->route('admin.failed-jobs.index')
            ->with('success', "{$retried} job(s) queued for retry.");
    }

    public function bulkDelete(Request $request, AuditService $audit): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'max:100'],
            'ids.*' => ['required', 'string'],
        ]);

        $deleted = DB::table('failed_jobs')->whereIn('uuid', $validated['ids'])->delete();

        $audit->log(AnalyticsEvent::ADMIN_FAILED_JOB_BULK_DELETE, [
            'count' => $deleted,
            'requested' => count($validated['ids']),
        ]);

        return redirect()->route('admin.failed-jobs.index')
            ->with('success', "{$deleted} failed job(s) deleted.");
    }

    private function extractJobName(string $payload): string
    {
        $data = json_decode($payload, true);

        if (! is_array($data)) {
            return 'Unknown';
        }

        return class_basename($data['displayName'] ?? $data['job'] ?? 'Unknown');
    }
}
