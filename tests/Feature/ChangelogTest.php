<?php

it('renders changelog page', function () {
    $response = $this->get('/changelog');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Changelog')
        ->has('entries')
    );
});

it('passes changelog entries as array props', function () {
    $response = $this->get('/changelog');

    $response->assertInertia(fn ($page) => $page
        ->component('Changelog')
        ->has('entries', 1)
    );
});
