<?php

use App\Models\IncomingWebhook;
use App\Services\IncomingWebhookService;
use App\Webhooks\Dto\IncomingWebhookEvent;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    if (! Schema::hasTable('incoming_webhooks')) {
        Schema::create('incoming_webhooks', function ($table) {
            $table->id();
            $table->string('provider', 32);
            $table->string('external_id')->nullable();
            $table->string('event_type')->nullable();
            $table->json('payload');
            $table->string('status', 20)->default('received');
            $table->timestamps();
            $table->unique(['provider', 'external_id']);
            $table->index(['provider', 'status']);
        });
    }
});

it('stores webhook on first call and returns the model', function () {
    $service = new IncomingWebhookService;
    $event = new IncomingWebhookEvent('github', 'push', 'evt-001', ['ref' => 'main']);

    $result = $service->process($event);

    expect($result)->toBeInstanceOf(IncomingWebhook::class);
    expect(IncomingWebhook::count())->toBe(1);
    expect($result->external_id)->toBe('evt-001');
    expect($result->provider)->toBe('github');
    expect($result->status)->toBe('received');
});

it('same event dispatched twice returns null on second call and inserts only one row', function () {
    $service = new IncomingWebhookService;
    $event = new IncomingWebhookEvent('github', 'push', 'evt-dup-1', ['ref' => 'main']);

    $first = $service->process($event);
    $second = $service->process($event);

    expect($first)->toBeInstanceOf(IncomingWebhook::class);
    expect($second)->toBeNull();
    expect(IncomingWebhook::count())->toBe(1);
});

it('same externalId from different providers creates two rows', function () {
    $service = new IncomingWebhookService;

    $githubEvent = new IncomingWebhookEvent('github', 'push', 'shared-id-1', ['ref' => 'main']);
    $customEvent = new IncomingWebhookEvent('custom', 'data.synced', 'shared-id-1', ['payload' => 'x']);

    $githubResult = $service->process($githubEvent);
    $customResult = $service->process($customEvent);

    expect($githubResult)->toBeInstanceOf(IncomingWebhook::class);
    expect($customResult)->toBeInstanceOf(IncomingWebhook::class);
    expect(IncomingWebhook::count())->toBe(2);
    expect(IncomingWebhook::where('provider', 'github')->count())->toBe(1);
    expect(IncomingWebhook::where('provider', 'custom')->count())->toBe(1);
});
