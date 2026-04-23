<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminBulkDeleteFailedJobRequest;
use App\Http\Requests\Admin\AdminBulkRetryFailedJobRequest;
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

        $queues = DB::table('failed_jobs')
            ->distinct()
            ->orderBy('queue')
            ->pluck('queue')
            ->values()
            ->toArray();

        return Inertia::render('App/Admin/FailedJobs/Index', [
            'jobs' => $jobs,
            'queues' => $queues,
            'filters' => ['queue' => $request->validated('queue')],
        ]);
    }

    public function show(int $id): Response
    {
        $job = DB::table('failed_jobs')->where('id', $id)->first();

        abort_unless((bool) $job, 404);

        return Inertia::render('App/Admin/FailedJobs/Show', [
            'job' => [
                'id' => $job->id,
                'uuid' => $job->uuid,
                'connection' => $job->connection,
                'queue' => $job->queue,
                'payload_summary' => $this->extractJobName($job->payload),
                'payload' => $this->sanitizePayload($job->payload),
                'exception' => $this->sanitizeException($job->exception),
                'failed_at' => $job->failed_at,
            ],
        ]);
    }

    public function retry(int $id, AuditService $audit): RedirectResponse
    {
        $job = DB::table('failed_jobs')->where('id', $id)->first();

        abort_unless((bool) $job, 404);

        Artisan::call('queue:retry', ['id' => [$job->uuid]]);

        $audit->log(AuditEvent::ADMIN_FAILED_JOB_RETRY, [
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

        $audit->log(AuditEvent::ADMIN_FAILED_JOB_DELETE, [
            'job_id' => $job->id,
            'job_uuid' => $job->uuid,
            'job_name' => $this->extractJobName($job->payload),
        ]);

        return redirect()->route('admin.failed-jobs.index')
            ->with('success', 'Failed job deleted.');
    }

    public function bulkRetry(AdminBulkRetryFailedJobRequest $request, AuditService $audit): RedirectResponse
    {
        $validated = $request->validated();

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

        $audit->log(AuditEvent::ADMIN_FAILED_JOB_BULK_RETRY, [
            'count' => $retried,
            'requested' => count($validated['ids']),
        ]);

        return redirect()->route('admin.failed-jobs.index')
            ->with('success', "{$retried} job(s) queued for retry.");
    }

    public function bulkDelete(AdminBulkDeleteFailedJobRequest $request, AuditService $audit): RedirectResponse
    {
        $validated = $request->validated();

        $deleted = DB::table('failed_jobs')->whereIn('uuid', $validated['ids'])->delete();

        $audit->log(AuditEvent::ADMIN_FAILED_JOB_BULK_DELETE, [
            'count' => $deleted,
            'requested' => count($validated['ids']),
        ]);

        return redirect()->route('admin.failed-jobs.index')
            ->with('success', "{$deleted} failed job(s) deleted.");
    }

    /**
     * Sanitize exception text before display.
     *
     * Exception messages can embed database passwords (PDO DSN errors),
     * API keys, Bearer tokens, or Stripe-style secrets. Redact common
     * credential patterns so they never appear in the admin UI.
     */
    private function sanitizeException(string $exception): string
    {
        $patterns = [
            // Key=value or key: value credential patterns
            '/\b(password|token|secret|api[_\-]?key|authorization|credential|stripe[_\-]?key|webhook[_\-]?secret|signing[_\-]?secret|access[_\-]?key|auth)\s*[=:]\s*\S+/i',
            // Bearer / Basic auth header values
            '/\b(Bearer|Basic)\s+\S+/i',
            // Database DSN with embedded password: driver://user:password@host
            '/(\w+):\/\/([^:\/]+):([^@]+)@/i',
            // Stripe-style API keys: sk_live_xxx, pk_test_xxx, rk_live_xxx
            '/\b(sk|pk|rk)_(live|test)_\w+/i',
        ];

        foreach ($patterns as $pattern) {
            $exception = preg_replace($pattern, '[redacted]', $exception) ?? $exception;
        }

        return $exception;
    }

    private function extractJobName(string $payload): string
    {
        $data = json_decode($payload, true);

        if (! is_array($data)) {
            return 'Unknown';
        }

        return class_basename($data['displayName'] ?? $data['job'] ?? 'Unknown');
    }

    /**
     * Sanitize a raw job payload before display.
     *
     * The serialized command in data.command may contain model attributes,
     * API keys, tokens, or other secrets. We redact it entirely and also
     * recursively redact any keys whose names suggest sensitive data.
     *
     * @return array<string, mixed>
     */
    private function sanitizePayload(string $payload): array
    {
        $data = json_decode($payload, true);

        if (! is_array($data)) {
            return ['error' => 'Invalid payload format'];
        }

        // The serialized PHP object in data.command can contain arbitrary
        // model attributes including API keys, passwords, and tokens.
        if (isset($data['data']['command'])) {
            $data['data']['command'] = '[redacted — serialized job command]';
        }

        return $this->redactSensitiveKeys($data);
    }

    /**
     * Recursively redact values for keys that match known secret patterns.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function redactSensitiveKeys(array $data): array
    {
        $sensitivePatterns = [
            'password', 'token', 'secret', 'key', 'api_key', 'apikey',
            'authorization', 'credential', 'private', 'access_key', 'auth',
            'stripe', 'webhook_secret', 'signing_secret',
        ];

        foreach ($data as $k => $v) {
            $lower = strtolower((string) $k);
            foreach ($sensitivePatterns as $pattern) {
                if (str_contains($lower, $pattern)) {
                    $data[$k] = '[redacted]';

                    continue 2;
                }
            }

            if (is_array($v)) {
                $data[$k] = $this->redactSensitiveKeys($v);
            }
        }

        return $data;
    }
}
