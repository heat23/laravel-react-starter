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
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_hsts_header_is_present_in_production(): void
    {
        $this->app['env'] = 'production';

        $response = $this->get('/');

        $hsts = $response->headers->get('Strict-Transport-Security');
        $this->assertNotNull($hsts, 'HSTS header should be present in production');
        $this->assertStringContainsString('max-age=31536000', $hsts);
        $this->assertStringContainsString('includeSubDomains', $hsts);
    }

    public function test_hsts_header_is_absent_in_non_production(): void
    {
        $this->app['env'] = 'local';

        $response = $this->get('/');

        $this->assertFalse(
            $response->headers->has('Strict-Transport-Security'),
            'HSTS header should not be sent outside production'
        );
    }

    public function test_csp_frame_src_allows_stripe_domains_when_billing_enabled(): void
    {
        config(['security.csp.enabled' => true, 'security.csp.report_only' => false, 'features.billing.enabled' => true]);

        $response = $this->get('/');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp);
        // frame-src should allow js.stripe.com for Stripe payment element iframes
        $this->assertStringContainsString("frame-src 'self' https://js.stripe.com", $csp);
        $this->assertStringNotContainsString("frame-src 'none'", $csp);
        // hooks.stripe.com is a webhook relay, not an iframe origin — must not be in frame-src
        $this->assertStringNotContainsString('https://hooks.stripe.com', $csp);
    }

    public function test_csp_frame_src_is_none_when_billing_disabled(): void
    {
        config(['security.csp.enabled' => true, 'security.csp.report_only' => false, 'features.billing.enabled' => false]);

        $response = $this->get('/');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp);
        // When billing is disabled no Stripe iframes are needed — lock down framing completely
        $this->assertStringContainsString("frame-src 'none'", $csp);
        $this->assertStringNotContainsString('https://js.stripe.com', $csp);
        $this->assertStringNotContainsString('https://hooks.stripe.com', $csp);
    }

    public function test_csp_script_src_excludes_stripe_js_when_billing_disabled(): void
    {
        config(['security.csp.enabled' => true, 'security.csp.report_only' => false, 'features.billing.enabled' => false]);

        $response = $this->get('/');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp);
        $parts = explode(';', $csp);
        $scriptSrc = collect($parts)->first(fn ($p) => str_contains(trim($p), 'script-src'));
        $this->assertNotNull($scriptSrc, 'script-src directive must be present');
        // Stripe JS must not appear in script-src when billing is disabled
        $this->assertStringNotContainsString('https://js.stripe.com', $scriptSrc);
    }

    public function test_csp_connect_src_includes_stripe_api_when_billing_enabled(): void
    {
        config(['security.csp.enabled' => true, 'security.csp.report_only' => false, 'features.billing.enabled' => true]);

        $response = $this->get('/');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp);
        $parts = explode(';', $csp);
        $connectSrc = collect($parts)->first(fn ($p) => str_contains(trim($p), 'connect-src'));
        $this->assertNotNull($connectSrc, 'connect-src directive must be present');
        // Stripe.js API calls + Radar fraud-signal telemetry domains
        $this->assertStringContainsString('https://api.stripe.com', $connectSrc);
        $this->assertStringContainsString('https://m.stripe.com', $connectSrc);
        $this->assertStringContainsString('https://m.stripe.network', $connectSrc);
    }

    public function test_csp_connect_src_excludes_stripe_api_when_billing_disabled(): void
    {
        config(['security.csp.enabled' => true, 'security.csp.report_only' => false, 'features.billing.enabled' => false]);

        $response = $this->get('/');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp);
        $this->assertStringNotContainsString('https://api.stripe.com', $csp);
    }

    public function test_csp_script_src_allows_stripe_js_when_billing_enabled(): void
    {
        config(['security.csp.enabled' => true, 'security.csp.report_only' => false, 'features.billing.enabled' => true]);

        $response = $this->get('/');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp);
        $this->assertStringContainsString('script-src', $csp);
        // script-src should include js.stripe.com for billing
        $parts = explode(';', $csp);
        $scriptSrc = collect($parts)->first(fn ($p) => str_contains(trim($p), 'script-src'));
        $this->assertNotNull($scriptSrc);
        $this->assertStringContainsString('https://js.stripe.com', $scriptSrc);
    }
}
