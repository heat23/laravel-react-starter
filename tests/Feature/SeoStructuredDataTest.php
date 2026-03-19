<?php

it('includes SoftwareApplication JSON-LD schema on pages', function () {
    $response = $this->get('/');

    $response->assertOk();
    $content = $response->getContent();

    expect($content)->toContain('application/ld+json');
    expect($content)->toContain('"@type":"SoftwareApplication"');
    expect($content)->toContain('"@context":"https://schema.org"');
});

it('includes Organization JSON-LD schema on pages', function () {
    $response = $this->get('/');

    $response->assertOk();
    $content = $response->getContent();

    expect($content)->toContain('"@type":"Organization"');
});

it('includes FAQPage JSON-LD schema on homepage', function () {
    $response = $this->get('/');

    $response->assertOk();
    $content = $response->getContent();

    expect($content)->toContain('"@type":"FAQPage"');
});
