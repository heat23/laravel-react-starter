<?php

it('includes WebApplication JSON-LD schema on pages', function () {
    $response = $this->get('/');

    $response->assertOk();
    $content = $response->getContent();

    expect($content)->toContain('application/ld+json');
    expect($content)->toContain('"@type":"WebApplication"');
    expect($content)->toContain('"@context":"https://schema.org"');
});
