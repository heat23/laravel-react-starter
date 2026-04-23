<?php

it('renders roadmap page', function () {
    $response = $this->get('/roadmap');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Public/Roadmap')
        ->has('entries')
    );
});
