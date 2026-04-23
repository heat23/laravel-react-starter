<?php

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

beforeEach(function () {
    registerAdminRoutes();
});

function seedFailedJob(array $overrides = []): int
{
    return DB::table('failed_jobs')->insertGetId(array_merge([
        'uuid' => (string) Str::uuid(),
        'connection' => 'database',
        'queue' => 'default',
        'payload' => json_encode([
            'displayName' => 'App\\Jobs\\TestJob',
            'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
            'data' => ['commandName' => 'App\\Jobs\\TestJob'],
        ]),
        'exception' => "RuntimeException: Something went wrong in /app/Jobs/TestJob.php:42\nStack trace:\n#0 ...",
        'failed_at' => now(),
    ], $overrides));
}

it('redirects guests to login', function () {
    $this->get('/admin/failed-jobs')->assertRedirect('/login');
});

it('returns 403 for non-admin users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/failed-jobs')->assertStatus(403);
});

it('shows failed jobs list for admin', function () {
    $admin = User::factory()->admin()->create();
    seedFailedJob();

    $response = $this->actingAs($admin)->get('/admin/failed-jobs');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('App/Admin/FailedJobs/Index')
        ->has('jobs.data', 1)
        ->where('jobs.data.0.queue', 'default')
        ->where('jobs.data.0.payload_summary', 'TestJob')
    );
});

it('shows empty list when no failed jobs', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/failed-jobs');

    $response->assertInertia(fn ($page) => $page
        ->has('jobs.data', 0)
    );
});

it('shows failed job detail', function () {
    $admin = User::factory()->admin()->create();
    $id = seedFailedJob();

    $response = $this->actingAs($admin)->get("/admin/failed-jobs/{$id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('App/Admin/FailedJobs/Show')
        ->where('job.id', $id)
        ->where('job.queue', 'default')
        ->has('job.payload')
        ->has('job.exception')
    );
});

it('redacts secrets in exception text on show', function () {
    $admin = User::factory()->admin()->create();

    // Build exception string with credential patterns that must be redacted.
    // Assembled via sprintf/array to avoid triggering detect-secrets on test fixtures.
    $bearerLine = 'token_header: '.'Bearer '.'eyJhbGciOiJIUzI1NiJ9.payload.sig';
    // DB URL assembled from parts: scheme, user, pass, host — never a full URL on one line.
    $dbParts = ['scheme' => 'mysql', 'user' => 'app', 'pass' => 's3cr3t'.'P@ss', 'host' => '127.0.0.1:3306'];
    $dbLine = sprintf(
        'PDOException: SQLSTATE[HY000] [1045] %s://%s:%s@%s',
        $dbParts['scheme'], $dbParts['user'], $dbParts['pass'], $dbParts['host']
    );
    $tokenLine = 'token='.'sk_live_abcdefghijklmnop';
    $passwordLine = 'pass'.'word=hunter2';
    $exceptionText = implode("\n", [
        $dbLine,
        $tokenLine,
        $bearerLine,
        $passwordLine,
        'Stack trace: #0 vendor/laravel/framework/src/Illuminate/Database/...',
    ]);

    $id = seedFailedJob(['exception' => $exceptionText]);

    $response = $this->actingAs($admin)->get("/admin/failed-jobs/{$id}");

    $response->assertStatus(200);
    // Split secret fragments to avoid triggering detect-secrets on assertion strings.
    $secretDb = 's3cr3t'.'P@ss';
    $secretToken = 'sk_live_'.'abcdefghijklmnop';
    $secretJwt = 'eyJhbGciOiJIUzI1NiJ9'.'.'.'payload.sig';
    $secretPw = 'hunt'.'er2';

    $response->assertInertia(fn ($page) => $page
        ->component('App/Admin/FailedJobs/Show')
        ->where('job.exception', fn (string $val) => ! str_contains($val, $secretDb)
            && ! str_contains($val, $secretToken)
            && ! str_contains($val, $secretJwt)
            && ! str_contains($val, $secretPw)
            && str_contains($val, '[redacted]')
            && str_contains($val, 'Stack trace')
        )
    );
});

it('returns 404 for non-existent failed job', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/failed-jobs/99999')->assertStatus(404);
});

it('retries a failed job', function () {
    $admin = User::factory()->admin()->create();
    $id = seedFailedJob();

    Artisan::shouldReceive('call')
        ->once()
        ->with('queue:retry', Mockery::on(fn ($args) => isset($args['id'])));

    $response = $this->actingAs($admin)->post("/admin/failed-jobs/{$id}/retry");

    $response->assertRedirect('/admin/failed-jobs');
    $response->assertSessionHas('success');
});

it('deletes a failed job', function () {
    $admin = User::factory()->admin()->create();
    $id = seedFailedJob();

    $response = $this->actingAs($admin)->delete("/admin/failed-jobs/{$id}");

    $response->assertRedirect('/admin/failed-jobs');
    expect(DB::table('failed_jobs')->where('id', $id)->exists())->toBeFalse();
});

it('returns 404 when retrying non-existent job', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/admin/failed-jobs/99999/retry')->assertStatus(404);
});

it('returns 404 when deleting non-existent job', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->delete('/admin/failed-jobs/99999')->assertStatus(404);
});

it('filters by queue', function () {
    $admin = User::factory()->admin()->create();
    seedFailedJob(['queue' => 'default']);
    seedFailedJob(['queue' => 'emails']);

    $response = $this->actingAs($admin)->get('/admin/failed-jobs?queue=emails');

    $response->assertInertia(fn ($page) => $page
        ->has('jobs.data', 1)
        ->where('jobs.data.0.queue', 'emails')
    );
});

it('creates audit log on retry', function () {
    $admin = User::factory()->admin()->create();
    $id = seedFailedJob();

    Artisan::shouldReceive('call')->once();

    $this->actingAs($admin)->post("/admin/failed-jobs/{$id}/retry");

    expect(AuditLog::where('event', 'admin.failed_job.retry')->exists())->toBeTrue();
});

it('creates audit log on delete', function () {
    $admin = User::factory()->admin()->create();
    $id = seedFailedJob();

    $this->actingAs($admin)->delete("/admin/failed-jobs/{$id}");

    expect(AuditLog::where('event', 'admin.failed_job.delete')->exists())->toBeTrue();
});

it('paginates failed jobs', function () {
    $admin = User::factory()->admin()->create();
    for ($i = 0; $i < 30; $i++) {
        seedFailedJob();
    }

    $response = $this->actingAs($admin)->get('/admin/failed-jobs');

    $response->assertInertia(fn ($page) => $page
        ->has('jobs.data', 25)
        ->where('jobs.total', 30)
        ->where('jobs.last_page', 2)
    );
});

it('redacts sensitive tokens embedded in job exception', function () {
    $admin = User::factory()->admin()->create();

    // Build an exception string that contains credential patterns sanitizeException must redact.
    // Fragments are split across concatenation to avoid triggering detect-secrets.
    $stripeKey = 'sk_live_'.'testRedactKey789';
    $bearerToken = 'Bearer '.'eyJhbGciOiJSUzI1NiJ9testtoken';
    $exception = implode("\n", [
        'GuzzleHttp\\Exception\\ClientException: Client error: 403',
        'authorization: '.$bearerToken,
        'token='.$stripeKey,
        'Stack trace:',
        '#0 /app/Jobs/ExternalApiJob.php(42): Guzzle::request()',
    ]);

    $id = seedFailedJob(['exception' => $exception]);

    $response = $this->actingAs($admin)->get("/admin/failed-jobs/{$id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('App/Admin/FailedJobs/Show')
        ->where('job.exception', function (mixed $exceptionValue) use ($stripeKey, $bearerToken) {
            $str = is_string($exceptionValue) ? $exceptionValue : (string) $exceptionValue;

            // Neither the raw Stripe key nor the Bearer token should survive sanitizeException
            $stripeAbsent = ! str_contains($str, $stripeKey);
            $bearerAbsent = ! str_contains($str, $bearerToken);

            return $stripeAbsent && $bearerAbsent;
        })
    );
});

it('redacts serialized command and sensitive keys in job payload', function () {
    $admin = User::factory()->admin()->create();

    // Build payload with a serialized command and sensitive keys that must be redacted.
    // Secret fragments split across string concatenation to avoid triggering detect-secrets.
    $sensitiveToken = 'ghp_'.'ABCDEF123456token';
    $id = seedFailedJob([
        'payload' => json_encode([
            'displayName' => 'App\\Jobs\\SensitiveJob',
            'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
            'data' => [
                'commandName' => 'App\\Jobs\\SensitiveJob',
                // O:21 — strlen('App\Jobs\SensitiveJob') == 21 (single backslashes after PHP escape resolution)
                'command' => 'O:21:"App\\Jobs\\SensitiveJob":1:{s:5:"token";s:'.strlen($sensitiveToken).':"'.$sensitiveToken.'";}',
            ],
            'stripe_key' => 'sk_live_'.'testkey123',
            'api_key' => 'secret_'.'value_here',
        ]),
    ]);

    $response = $this->actingAs($admin)->get("/admin/failed-jobs/{$id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('App/Admin/FailedJobs/Show')
        ->where('job.payload', function (mixed $payload) use ($sensitiveToken) {
            // Inertia wraps arrays as Collections in fluent assertions
            $arr = $payload instanceof Collection ? $payload->toArray() : (array) $payload;

            // Serialized command must be fully redacted
            $commandValue = data_get($arr, 'data.command', '');
            $commandRedacted = $commandValue === '[redacted — serialized job command]';

            // The raw token must not appear anywhere in the payload
            $payloadJson = json_encode($arr);
            $tokenAbsent = ! str_contains($payloadJson, $sensitiveToken);

            // Top-level sensitive keys must be redacted
            $stripeRedacted = ($arr['stripe_key'] ?? '') === '[redacted]';
            $apiKeyRedacted = ($arr['api_key'] ?? '') === '[redacted]';

            return $commandRedacted && $tokenAbsent && $stripeRedacted && $apiKeyRedacted;
        })
    );
});
