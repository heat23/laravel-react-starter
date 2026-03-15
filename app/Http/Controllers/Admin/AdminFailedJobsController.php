<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminFailedJobIndexRequest;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
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

        return Inertia::render('Admin/FailedJobs/Index', ['jobs' => $jobs]);
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

        $audit->log('admin.failed_job.retry', [
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

        $audit->log('admin.failed_job.delete', [
            'job_id' => $job->id,
            'job_uuid' => $job->uuid,
            'job_name' => $this->extractJobName($job->payload),
        ]);

        return redirect()->route('admin.failed-jobs.index')
            ->with('success', 'Failed job deleted.');
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
