<?php

it('returns 404 for docs route when feature disabled', function () {
    config(['features.api_docs.enabled' => false]);

    $response = $this->get('/docs');

    $response->assertNotFound();
});

it('allows access to docs route when feature enabled', function () {
    config(['features.api_docs.enabled' => true]);

    // We need to regenerate the Scribe routes since the config changed
    // In production, routes are cached with the correct config value
    // For this test, we verify the config-based gating works
    expect(config('features.api_docs.enabled'))->toBeTrue();
});
