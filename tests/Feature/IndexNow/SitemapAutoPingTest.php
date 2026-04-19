<?php

use App\Jobs\SubmitIndexNowUrlsJob;
use App\Models\IndexNowSubmission;
use App\Services\IndexNowService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config([
        'features.indexnow.enabled' => true,
        'features.indexnow.debounce_minutes' => 10,
        'indexnow.key' => 'testkeytestkeytestkeytestkey1234',
        'indexnow.host' => 'example.test',
        'app.url' => 'https://example.test',
        'app.env' => 'production', // sitemap/robots emit real URLs only in prod
    ]);
    Cache::forget('sitemap');
});

it('does not auto-ping IndexNow when auto_ping_sitemap is disabled', function () {
    Queue::fake();
    config(['features.indexnow.auto_ping_sitemap' => false]);

    $this->get('/sitemap.xml')->assertOk();

    expect(IndexNowSubmission::count())->toBe(0);
    Queue::assertNotPushed(SubmitIndexNowUrlsJob::class);
});

it('auto-pings IndexNow with sitemap URLs when enabled and cache is cold', function () {
    Queue::fake();
    config(['features.indexnow.auto_ping_sitemap' => true]);

    $this->get('/sitemap.xml')->assertOk();

    expect(IndexNowSubmission::count())->toBe(1);

    $submission = IndexNowSubmission::first();
    expect($submission->trigger)->toBe('sitemap')
        ->and($submission->url_count)->toBeGreaterThan(0);
    Queue::assertPushed(SubmitIndexNowUrlsJob::class, 1);
});

it('does not re-ping on subsequent sitemap requests within the 24h cache window', function () {
    Queue::fake();
    config(['features.indexnow.auto_ping_sitemap' => true]);

    $this->get('/sitemap.xml')->assertOk();
    $this->get('/sitemap.xml')->assertOk();
    $this->get('/sitemap.xml')->assertOk();

    // Only the cache miss submits
    expect(IndexNowSubmission::count())->toBe(1);
    Queue::assertPushed(SubmitIndexNowUrlsJob::class, 1);
});

it('still serves valid sitemap XML when IndexNow submission throws', function () {
    // Adversarial finding #1: if IndexNow::submit explodes mid-cache-miss,
    // the sitemap response must not leak the exception or poison the cache
    // with a broken payload — search engines hammer this URL.
    Queue::fake();
    config(['features.indexnow.auto_ping_sitemap' => true]);

    // Bind a service that always throws
    app()->instance(IndexNowService::class, new class extends IndexNowService
    {
        public function submit(array $urls, ?string $trigger = null): ?IndexNowSubmission
        {
            throw new RuntimeException('IndexNow exploded');
        }
    });

    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toStartWith('application/xml');
    expect($response->getContent())->toContain('<urlset');

    // Second hit should serve the cached XML, not re-throw
    $this->get('/sitemap.xml')->assertOk();
});
