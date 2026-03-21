<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_scrubs_ip_and_user_agent_from_audit_logs_on_account_deletion(): void
    {
        $user = User::factory()->create();
        $knownIp = '192.168.1.100';
        $knownUa = 'Mozilla/5.0 TestBrowser';

        AuditLog::factory()->count(3)->create([
            'user_id' => $user->id,
            'ip' => $knownIp,
            'user_agent' => $knownUa,
        ]);

        $user->purgePersonalData();

        // User is gone
        $this->assertDatabaseMissing('users', ['id' => $user->id]);

        // No audit log retains the original IP
        $this->assertEquals(0, AuditLog::where('ip', $knownIp)->count());

        // The 3 records exist but with null PII
        $this->assertEquals(3, AuditLog::withoutGlobalScopes()
            ->whereNull('user_id')
            ->whereNull('ip')
            ->whereNull('user_agent')
            ->count());
    }
}
