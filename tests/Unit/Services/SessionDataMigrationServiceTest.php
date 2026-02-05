<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\SessionDataMigrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionDataMigrationServiceTest extends TestCase
{
    use RefreshDatabase;

    private SessionDataMigrationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SessionDataMigrationService();
    }

    // ============================================
    // hasSessionData() tests
    // ============================================

    public function test_has_session_data_returns_false_by_default(): void
    {
        // The placeholder implementation always returns false
        $this->assertFalse($this->service->hasSessionData());
    }

    public function test_has_session_data_returns_false_even_when_session_has_data(): void
    {
        // Even with data in session, placeholder returns false
        session(['anonymous_data' => ['item1', 'item2']]);

        $this->assertFalse($this->service->hasSessionData());
    }

    // ============================================
    // getSessionDataSummary() tests
    // ============================================

    public function test_get_session_data_summary_returns_null_when_no_data(): void
    {
        $summary = $this->service->getSessionDataSummary();

        $this->assertNull($summary);
    }

    public function test_get_session_data_summary_returns_null_when_has_session_data_false(): void
    {
        // hasSessionData() returns false, so summary should be null
        session(['anonymous_data' => ['item1']]);

        $summary = $this->service->getSessionDataSummary();

        $this->assertNull($summary);
    }

    // ============================================
    // migrateSessionData() tests
    // ============================================

    public function test_migrate_session_data_returns_not_migrated_when_no_data(): void
    {
        $user = User::factory()->create();

        $result = $this->service->migrateSessionData($user);

        $this->assertFalse($result['migrated']);
    }

    public function test_migrate_session_data_returns_zero_items_count_when_no_data(): void
    {
        $user = User::factory()->create();

        $result = $this->service->migrateSessionData($user);

        $this->assertEquals(0, $result['items_count']);
    }

    public function test_migrate_session_data_returns_zero_project_items_when_no_data(): void
    {
        $user = User::factory()->create();

        $result = $this->service->migrateSessionData($user);

        $this->assertEquals(0, $result['project_items']);
    }

    public function test_migrate_session_data_returns_correct_structure(): void
    {
        $user = User::factory()->create();

        $result = $this->service->migrateSessionData($user);

        $this->assertArrayHasKey('migrated', $result);
        $this->assertArrayHasKey('items_count', $result);
        $this->assertArrayHasKey('project_items', $result);
    }

    public function test_migrate_session_data_does_not_migrate_when_has_session_data_false(): void
    {
        // Even with session data, placeholder hasSessionData returns false
        session(['anonymous_data' => ['item1', 'item2']]);

        $user = User::factory()->create();
        $result = $this->service->migrateSessionData($user);

        $this->assertFalse($result['migrated']);
        $this->assertEquals(0, $result['items_count']);
    }

    // ============================================
    // clearSessionData() tests
    // ============================================

    public function test_clear_session_data_forgets_session_key(): void
    {
        session(['anonymous_data' => ['item1', 'item2']]);
        $this->assertTrue(session()->has('anonymous_data'));

        $this->service->clearSessionData();

        $this->assertFalse(session()->has('anonymous_data'));
    }

    public function test_clear_session_data_handles_empty_session(): void
    {
        // Should not throw when clearing non-existent key
        $this->assertFalse(session()->has('anonymous_data'));

        $this->service->clearSessionData();

        $this->assertFalse(session()->has('anonymous_data'));
    }

    public function test_clear_session_data_only_clears_specific_key(): void
    {
        session([
            'anonymous_data' => ['item1'],
            'other_data' => ['other_item'],
        ]);

        $this->service->clearSessionData();

        $this->assertFalse(session()->has('anonymous_data'));
        $this->assertTrue(session()->has('other_data'));
    }

    // ============================================
    // Session key tests
    // ============================================

    public function test_session_key_defaults_to_anonymous_data(): void
    {
        // Test that the protected $sessionKey property has the expected default value
        // by testing clearSessionData's behavior
        session(['anonymous_data' => 'test']);

        $this->service->clearSessionData();

        $this->assertFalse(session()->has('anonymous_data'));
    }

    // ============================================
    // Edge case tests
    // ============================================

    public function test_migrate_session_data_with_different_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $result1 = $this->service->migrateSessionData($user1);
        $result2 = $this->service->migrateSessionData($user2);

        // Both should return the same structure (placeholder behavior)
        $this->assertEquals($result1, $result2);
    }

    public function test_service_can_be_instantiated(): void
    {
        $service = new SessionDataMigrationService();

        $this->assertInstanceOf(SessionDataMigrationService::class, $service);
    }

    public function test_multiple_clear_calls_are_safe(): void
    {
        session(['anonymous_data' => 'test']);

        $this->service->clearSessionData();
        $this->service->clearSessionData();
        $this->service->clearSessionData();

        // Should not throw and session should be clear
        $this->assertFalse(session()->has('anonymous_data'));
    }
}
