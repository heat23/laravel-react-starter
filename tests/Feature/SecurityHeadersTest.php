<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_enforcing_csp_header_in_production_environment(): void
    {
        // Simulate production environment where CSP_REPORT_ONLY defaults to false
        config(['security.csp.enabled' => true, 'security.csp.report_only' => false]);

        $response = $this->get('/');

        $response->assertHeader('Content-Security-Policy');
        $this->assertFalse(
            $response->headers->has('Content-Security-Policy-Report-Only'),
            'Enforcing CSP should not send report-only header'
        );
    }

    public function test_sends_report_only_csp_header_in_local_environment(): void
    {
        // Simulate local environment where CSP_REPORT_ONLY defaults to true
        config(['security.csp.enabled' => true, 'security.csp.report_only' => true]);

        $response = $this->get('/');

        $response->assertHeader('Content-Security-Policy-Report-Only');
        $this->assertFalse(
            $response->headers->has('Content-Security-Policy'),
            'Report-only mode should not send enforcing CSP header'
        );
    }

    public function test_security_headers_are_present_on_responses(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}
