<?php

namespace Tests\Feature\Console;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataRetentionScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_prunes_audit_logs_older_than_given_days(): void
    {
        $user = User::factory()->create();

        // Old log beyond retention period
        $old = AuditLog::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subDays(100),
        ]);

        // Recent log within retention period
        $recent = AuditLog::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subDays(10),
        ]);

        $this->artisan('audit:prune', ['--days' => 90])
            ->assertSuccessful();

        $this->assertDatabaseMissing('audit_logs', ['id' => $old->id]);
        $this->assertDatabaseHas('audit_logs', ['id' => $recent->id]);
    }

    public function test_prunes_stale_webhook_deliveries(): void
    {
        config(['features.webhooks.enabled' => true]);

        $endpoint = WebhookEndpoint::factory()->create();

        $stale = WebhookDelivery::factory()->create([
            'webhook_endpoint_id' => $endpoint->id,
            'status' => 'pending',
            'created_at' => now()->subHours(2),
        ]);

        $fresh = WebhookDelivery::factory()->create([
            'webhook_endpoint_id' => $endpoint->id,
            'status' => 'pending',
            'created_at' => now(),
        ]);

        $this->artisan('webhooks:mark-abandoned', ['--hours' => 1])
            ->assertSuccessful();

        $this->assertDatabaseHas('webhook_deliveries', [
            'id' => $stale->id,
            'status' => 'abandoned',
        ]);

        $this->assertDatabaseHas('webhook_deliveries', [
            'id' => $fresh->id,
            'status' => 'pending',
        ]);
    }

    public function test_prunes_read_notifications(): void
    {
        $user = User::factory()->create();

        // Old read notification
        \DB::table('notifications')->insert([
            'id' => \Str::uuid()->toString(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode(['message' => 'old']),
            'read_at' => now()->subDays(40),
            'created_at' => now()->subDays(40),
            'updated_at' => now()->subDays(40),
        ]);

        // Recent read notification (within 30 days)
        \DB::table('notifications')->insert([
            'id' => \Str::uuid()->toString(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode(['message' => 'recent']),
            'read_at' => now()->subDays(5),
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        // Unread notification — should never be deleted
        \DB::table('notifications')->insert([
            'id' => \Str::uuid()->toString(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode(['message' => 'unread']),
            'read_at' => null,
            'created_at' => now()->subDays(100),
            'updated_at' => now()->subDays(100),
        ]);

        $this->artisan('prune-read-notifications', ['--days' => 30])
            ->assertSuccessful();

        $remaining = \DB::table('notifications')->get();
        $this->assertCount(2, $remaining); // recent read + unread
    }
}
