<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Prevent Vite manifest lookups during testing
        // Tests should not require frontend assets to be built
        $this->withoutVite();
    }
}
