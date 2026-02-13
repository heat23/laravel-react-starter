<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Admin Demo Seeder
 *
 * Generates a realistic admin environment for testing and demo purposes.
 * Idempotent: skips if >50 users already exist.
 *
 * Run via: php artisan db:seed --class=AdminDemoSeeder
 */
class AdminDemoSeeder extends Seeder
{
    private \Faker\Generator $faker;

    private static ?string $hashedPassword = null;

    public function run(): void
    {
        if (User::withTrashed()->count() > 50) {
            $this->command?->info('Skipping AdminDemoSeeder: more than 50 users already exist.');

            return;
        }

        $this->faker = fake();

        $this->command?->info('Seeding admin demo data...');

        $users = $this->seedUsers();
        $this->seedAuditLogs($users);
        $this->seedWebhooks($users);
        $this->seedNotifications($users);
        $this->seedPersonalAccessTokens($users);
        $this->seedSocialAccounts($users);
        $this->seedTwoFactorAuthentications($users);

        $this->command?->info('Admin demo data seeded successfully.');
    }

    /**
     * Create 100 users with a realistic mix of admin/regular, verified/unverified.
     *
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function seedUsers(): \Illuminate\Support\Collection
    {
        $this->command?->info('  Creating 100 users...');

        // Reuse a single hashed password for performance
        static::$hashedPassword ??= Hash::make('password');

        $signupSources = ['password', 'google', 'github'];

        $users = collect();

        // 5 admin users
        for ($i = 0; $i < 5; $i++) {
            $users->push(User::factory()->create([
                'name' => $this->faker->name(),
                'email' => 'admin' . ($i + 1) . '@demo.test',
                'password' => static::$hashedPassword,
                'is_admin' => true,
                'email_verified_at' => $this->faker->dateTimeBetween('-90 days', '-1 day'),
                'last_login_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
                'signup_source' => 'password',
            ]));
        }

        // 95 regular users with varied states
        for ($i = 0; $i < 95; $i++) {
            $isVerified = $this->faker->boolean(75); // 75% verified
            $hasLoggedIn = $this->faker->boolean(60); // 60% have logged in
            $createdAt = $this->faker->dateTimeBetween('-120 days', '-1 day');

            $users->push(User::factory()->create([
                'name' => $this->faker->name(),
                'email' => $this->faker->unique()->safeEmail(),
                'password' => static::$hashedPassword,
                'is_admin' => false,
                'email_verified_at' => $isVerified ? $this->faker->dateTimeBetween($createdAt, 'now') : null,
                'last_login_at' => $hasLoggedIn ? $this->faker->dateTimeBetween($createdAt, 'now') : null,
                'signup_source' => $this->faker->randomElement($signupSources),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]));
        }

        return $users;
    }

    /**
     * Create 500 audit log entries spread across the last 60 days.
     *
     * @param  \Illuminate\Support\Collection<int, User>  $users
     */
    private function seedAuditLogs(\Illuminate\Support\Collection $users): void
    {
        if (! Schema::hasTable('audit_logs')) {
            $this->command?->warn('  Skipping audit logs: table does not exist.');

            return;
        }

        $this->command?->info('  Creating 500 audit log entries...');

        $userIds = $users->pluck('id')->toArray();

        $events = [
            'login',
            'logout',
            'user.created',
            'user.updated',
            'user.deleted',
            'settings.updated',
            'password.changed',
            'password.reset_requested',
            'email.verification_sent',
            'email.verified',
            'token.created',
            'token.deleted',
            'webhook.created',
            'webhook.updated',
            'webhook.deleted',
            'subscription.created',
            'subscription.cancelled',
            'two_factor.enabled',
            'two_factor.disabled',
            'export.requested',
        ];

        $userAgents = [
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0',
        ];

        $rows = [];

        for ($i = 0; $i < 500; $i++) {
            $event = $this->faker->randomElement($events);
            $userId = $this->faker->randomElement($userIds);
            $createdAt = $this->faker->dateTimeBetween('-60 days', 'now');

            $metadata = $this->buildAuditMetadata($event);

            $rows[] = [
                'event' => $event,
                'user_id' => $userId,
                'ip' => $this->faker->ipv4(),
                'user_agent' => $this->faker->randomElement($userAgents),
                'metadata' => json_encode($metadata),
                'created_at' => $createdAt->format('Y-m-d H:i:s'),
            ];

            // Insert in batches of 100
            if (count($rows) >= 100) {
                DB::table('audit_logs')->insert($rows);
                $rows = [];
            }
        }

        if (count($rows) > 0) {
            DB::table('audit_logs')->insert($rows);
        }
    }

    /**
     * Build realistic metadata for a given audit event type.
     */
    private function buildAuditMetadata(string $event): array
    {
        return match ($event) {
            'login' => ['method' => $this->faker->randomElement(['password', 'social', 'remember_token'])],
            'logout' => ['reason' => $this->faker->randomElement(['manual', 'session_expired', 'forced'])],
            'user.created' => ['source' => $this->faker->randomElement(['registration', 'admin_invite', 'social_auth'])],
            'user.updated' => ['fields' => $this->faker->randomElements(['name', 'email', 'timezone', 'theme'], $this->faker->numberBetween(1, 3))],
            'user.deleted' => ['soft_delete' => true],
            'settings.updated' => ['key' => $this->faker->randomElement(['theme', 'timezone', 'language', 'notifications_enabled']), 'old_value' => 'previous', 'new_value' => 'updated'],
            'password.changed' => ['via' => $this->faker->randomElement(['settings', 'reset_link'])],
            'password.reset_requested' => ['email' => $this->faker->safeEmail()],
            'email.verification_sent' => ['attempts' => $this->faker->numberBetween(1, 3)],
            'email.verified' => ['method' => 'link_click'],
            'token.created' => ['token_name' => $this->faker->words(2, true), 'abilities' => ['*']],
            'token.deleted' => ['token_name' => $this->faker->words(2, true)],
            'webhook.created' => ['url' => $this->faker->url()],
            'webhook.updated' => ['fields' => $this->faker->randomElements(['url', 'events', 'active'], $this->faker->numberBetween(1, 2))],
            'webhook.deleted' => ['endpoint_id' => $this->faker->numberBetween(1, 20)],
            'subscription.created' => ['plan' => $this->faker->randomElement(['pro', 'team', 'enterprise'])],
            'subscription.cancelled' => ['reason' => $this->faker->randomElement(['too_expensive', 'not_using', 'switching_competitor', 'missing_features'])],
            'two_factor.enabled' => ['method' => 'totp'],
            'two_factor.disabled' => ['method' => 'totp'],
            'export.requested' => ['format' => 'csv', 'type' => $this->faker->randomElement(['users', 'audit_logs', 'invoices'])],
            default => [],
        };
    }

    /**
     * Create webhook endpoints and deliveries.
     *
     * @param  \Illuminate\Support\Collection<int, User>  $users
     */
    private function seedWebhooks(\Illuminate\Support\Collection $users): void
    {
        $hasEndpoints = Schema::hasTable('webhook_endpoints');
        $hasDeliveries = Schema::hasTable('webhook_deliveries');
        $hasIncoming = Schema::hasTable('incoming_webhooks');

        if (! $hasEndpoints && ! $hasDeliveries && ! $hasIncoming) {
            $this->command?->warn('  Skipping webhooks: tables do not exist.');

            return;
        }

        $userIds = $users->pluck('id')->toArray();

        if ($hasEndpoints) {
            $this->seedWebhookEndpoints($userIds);
        }

        if ($hasEndpoints && $hasDeliveries) {
            $this->seedWebhookDeliveries();
        }

        if ($hasIncoming) {
            $this->seedIncomingWebhooks();
        }
    }

    /**
     * Create 20 webhook endpoints.
     *
     * @param  array<int>  $userIds
     */
    private function seedWebhookEndpoints(array $userIds): void
    {
        $this->command?->info('  Creating 20 webhook endpoints...');

        $webhookEvents = [
            ['user.created', 'user.updated'],
            ['user.created', 'user.deleted'],
            ['subscription.created', 'subscription.cancelled'],
            ['payment.succeeded', 'payment.failed'],
            ['invoice.created'],
            ['user.created', 'user.updated', 'user.deleted'],
            ['subscription.created', 'subscription.updated', 'subscription.cancelled'],
        ];

        $domains = [
            'hooks.example.com',
            'api.clientapp.io',
            'webhooks.myservice.dev',
            'notify.acmecorp.com',
            'integrations.startup.co',
        ];

        for ($i = 0; $i < 20; $i++) {
            $domain = $this->faker->randomElement($domains);
            $path = $this->faker->randomElement(['/webhook', '/api/hooks', '/v1/callbacks', '/events/receive', '/incoming']);
            $createdAt = $this->faker->dateTimeBetween('-60 days', '-1 day');

            DB::table('webhook_endpoints')->insert([
                'user_id' => $this->faker->randomElement($userIds),
                'url' => 'https://' . $domain . $path,
                'events' => json_encode($this->faker->randomElement($webhookEvents)),
                'secret' => encrypt(Str::random(32)),
                'description' => $this->faker->optional(0.7)->sentence(4),
                'active' => $this->faker->boolean(70), // 70% active
                'created_at' => $createdAt->format('Y-m-d H:i:s'),
                'updated_at' => $createdAt->format('Y-m-d H:i:s'),
                'deleted_at' => $this->faker->boolean(10) ? $this->faker->dateTimeBetween($createdAt, 'now')->format('Y-m-d H:i:s') : null,
            ]);
        }
    }

    /**
     * Create 200 webhook deliveries spread across the last 14 days.
     */
    private function seedWebhookDeliveries(): void
    {
        $this->command?->info('  Creating 200 webhook deliveries...');

        $endpointIds = DB::table('webhook_endpoints')->pluck('id')->toArray();

        if (empty($endpointIds)) {
            $this->command?->warn('  Skipping webhook deliveries: no endpoints found.');

            return;
        }

        $eventTypes = [
            'user.created',
            'user.updated',
            'user.deleted',
            'subscription.created',
            'subscription.cancelled',
            'payment.succeeded',
            'payment.failed',
            'invoice.created',
        ];

        $statuses = ['success', 'success', 'success', 'success', 'failed', 'pending']; // weighted toward success

        $rows = [];

        for ($i = 0; $i < 200; $i++) {
            $status = $this->faker->randomElement($statuses);
            $createdAt = $this->faker->dateTimeBetween('-14 days', 'now');
            $eventType = $this->faker->randomElement($eventTypes);

            $responseCode = match ($status) {
                'success' => $this->faker->randomElement([200, 201, 202, 204]),
                'failed' => $this->faker->randomElement([400, 401, 403, 404, 500, 502, 503]),
                default => null,
            };

            $responseBody = match ($status) {
                'success' => json_encode(['status' => 'ok']),
                'failed' => json_encode(['error' => $this->faker->randomElement([
                    'Internal Server Error',
                    'Unauthorized',
                    'Bad Request',
                    'Not Found',
                    'Service Unavailable',
                ])]),
                default => null,
            };

            $rows[] = [
                'webhook_endpoint_id' => $this->faker->randomElement($endpointIds),
                'uuid' => Str::uuid()->toString(),
                'event_type' => $eventType,
                'payload' => json_encode([
                    'event' => $eventType,
                    'data' => [
                        'id' => $this->faker->numberBetween(1, 1000),
                        'timestamp' => $createdAt->format('c'),
                    ],
                ]),
                'status' => $status,
                'response_code' => $responseCode,
                'response_body' => $responseBody,
                'attempts' => match ($status) {
                    'success' => $this->faker->numberBetween(1, 2),
                    'failed' => $this->faker->numberBetween(1, 5),
                    default => 0,
                },
                'delivered_at' => $status !== 'pending' ? $createdAt->format('Y-m-d H:i:s') : null,
                'created_at' => $createdAt->format('Y-m-d H:i:s'),
                'updated_at' => $createdAt->format('Y-m-d H:i:s'),
            ];

            // Insert in batches of 50
            if (count($rows) >= 50) {
                DB::table('webhook_deliveries')->insert($rows);
                $rows = [];
            }
        }

        if (count($rows) > 0) {
            DB::table('webhook_deliveries')->insert($rows);
        }
    }

    /**
     * Create 30 incoming webhooks from GitHub and Stripe.
     */
    private function seedIncomingWebhooks(): void
    {
        $this->command?->info('  Creating 30 incoming webhooks...');

        $githubEvents = ['push', 'pull_request.opened', 'pull_request.merged', 'issues.opened', 'issues.closed', 'release.published', 'workflow_run.completed'];
        $stripeEvents = ['checkout.session.completed', 'customer.subscription.created', 'customer.subscription.updated', 'customer.subscription.deleted', 'invoice.paid', 'invoice.payment_failed', 'payment_intent.succeeded'];
        $statuses = ['received', 'processing', 'processed', 'processed', 'processed', 'failed']; // weighted toward processed

        $rows = [];

        for ($i = 0; $i < 30; $i++) {
            $provider = $this->faker->randomElement(['github', 'stripe']);
            $eventType = $provider === 'github'
                ? $this->faker->randomElement($githubEvents)
                : $this->faker->randomElement($stripeEvents);

            $externalId = $provider === 'github'
                ? 'gh_' . Str::random(16)
                : 'evt_' . Str::random(24);

            $createdAt = $this->faker->dateTimeBetween('-30 days', 'now');

            $payload = $provider === 'github'
                ? [
                    'action' => explode('.', $eventType)[1] ?? 'triggered',
                    'repository' => ['full_name' => $this->faker->userName() . '/' . $this->faker->slug(2)],
                    'sender' => ['login' => $this->faker->userName()],
                ]
                : [
                    'id' => $externalId,
                    'object' => 'event',
                    'type' => $eventType,
                    'data' => ['object' => ['id' => 'sub_' . Str::random(14), 'customer' => 'cus_' . Str::random(14)]],
                ];

            $rows[] = [
                'provider' => $provider,
                'external_id' => $externalId,
                'event_type' => $eventType,
                'payload' => json_encode($payload),
                'status' => $this->faker->randomElement($statuses),
                'created_at' => $createdAt->format('Y-m-d H:i:s'),
                'updated_at' => $createdAt->format('Y-m-d H:i:s'),
            ];
        }

        DB::table('incoming_webhooks')->insert($rows);
    }

    /**
     * Create 50 notifications (mix of read and unread).
     *
     * @param  \Illuminate\Support\Collection<int, User>  $users
     */
    private function seedNotifications(\Illuminate\Support\Collection $users): void
    {
        if (! Schema::hasTable('notifications')) {
            $this->command?->warn('  Skipping notifications: table does not exist.');

            return;
        }

        $this->command?->info('  Creating 50 notifications...');

        $userIds = $users->pluck('id')->toArray();

        $notificationTypes = [
            'App\\Notifications\\WelcomeNotification',
            'App\\Notifications\\SubscriptionCreated',
            'App\\Notifications\\SubscriptionCancelled',
            'App\\Notifications\\PaymentSucceeded',
            'App\\Notifications\\PaymentFailed',
            'App\\Notifications\\SecurityAlert',
            'App\\Notifications\\NewFeatureAnnouncement',
            'App\\Notifications\\UsageLimitWarning',
            'App\\Notifications\\WeeklyReport',
            'App\\Notifications\\AccountVerified',
        ];

        $rows = [];

        for ($i = 0; $i < 50; $i++) {
            $type = $this->faker->randomElement($notificationTypes);
            $createdAt = $this->faker->dateTimeBetween('-30 days', 'now');
            $isRead = $this->faker->boolean(40); // 40% read

            $data = $this->buildNotificationData($type);

            $rows[] = [
                'id' => Str::uuid()->toString(),
                'type' => $type,
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $this->faker->randomElement($userIds),
                'data' => json_encode($data),
                'read_at' => $isRead ? $this->faker->dateTimeBetween($createdAt, 'now')->format('Y-m-d H:i:s') : null,
                'created_at' => $createdAt->format('Y-m-d H:i:s'),
                'updated_at' => $createdAt->format('Y-m-d H:i:s'),
            ];
        }

        DB::table('notifications')->insert($rows);
    }

    /**
     * Build realistic notification data based on type.
     */
    private function buildNotificationData(string $type): array
    {
        $shortType = class_basename($type);

        return match ($shortType) {
            'WelcomeNotification' => [
                'title' => 'Welcome to the platform!',
                'message' => 'Your account has been created successfully. Get started by exploring the dashboard.',
            ],
            'SubscriptionCreated' => [
                'title' => 'Subscription activated',
                'message' => 'Your ' . $this->faker->randomElement(['Pro', 'Team', 'Enterprise']) . ' plan is now active.',
                'plan' => $this->faker->randomElement(['pro', 'team', 'enterprise']),
            ],
            'SubscriptionCancelled' => [
                'title' => 'Subscription cancelled',
                'message' => 'Your subscription will remain active until the end of the billing period.',
                'ends_at' => $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            ],
            'PaymentSucceeded' => [
                'title' => 'Payment received',
                'message' => 'We received your payment of $' . $this->faker->randomFloat(2, 9.99, 299.99) . '.',
                'amount' => $this->faker->numberBetween(999, 29999),
                'currency' => 'usd',
            ],
            'PaymentFailed' => [
                'title' => 'Payment failed',
                'message' => 'We were unable to process your payment. Please update your payment method.',
                'amount' => $this->faker->numberBetween(999, 29999),
                'currency' => 'usd',
            ],
            'SecurityAlert' => [
                'title' => 'New sign-in detected',
                'message' => 'A new sign-in was detected from ' . $this->faker->city() . ', ' . $this->faker->country() . '.',
                'ip' => $this->faker->ipv4(),
                'location' => $this->faker->city() . ', ' . $this->faker->countryCode(),
            ],
            'NewFeatureAnnouncement' => [
                'title' => $this->faker->randomElement(['New: API tokens', 'New: Webhook support', 'New: Dark mode', 'New: CSV export', 'New: Two-factor auth']),
                'message' => 'Check out our latest feature in the settings page.',
            ],
            'UsageLimitWarning' => [
                'title' => 'Approaching usage limit',
                'message' => 'You have used ' . $this->faker->numberBetween(80, 95) . '% of your monthly quota.',
                'usage_percent' => $this->faker->numberBetween(80, 95),
            ],
            'WeeklyReport' => [
                'title' => 'Your weekly summary',
                'message' => 'Here is your activity summary for the past week.',
                'period_start' => now()->subWeek()->format('Y-m-d'),
                'period_end' => now()->format('Y-m-d'),
            ],
            'AccountVerified' => [
                'title' => 'Email verified',
                'message' => 'Your email address has been verified successfully.',
            ],
            default => [
                'title' => 'Notification',
                'message' => $this->faker->sentence(),
            ],
        };
    }

    /**
     * Create 30 personal access tokens.
     *
     * @param  \Illuminate\Support\Collection<int, User>  $users
     */
    private function seedPersonalAccessTokens(\Illuminate\Support\Collection $users): void
    {
        if (! Schema::hasTable('personal_access_tokens')) {
            $this->command?->warn('  Skipping personal access tokens: table does not exist.');

            return;
        }

        $this->command?->info('  Creating 30 personal access tokens...');

        $userIds = $users->pluck('id')->toArray();

        $tokenNames = [
            'CI/CD Pipeline',
            'Local Development',
            'Staging Deploy',
            'Monitoring Script',
            'Backup Automation',
            'API Integration',
            'Mobile App',
            'Desktop Client',
            'CLI Tool',
            'Cron Job',
            'Data Export Script',
            'Analytics Dashboard',
            'Webhook Handler',
            'Test Runner',
            'Sync Service',
        ];

        $abilitySets = [
            '["*"]',
            '["read"]',
            '["read","write"]',
            '["read","write","delete"]',
            '["read","export"]',
            '["webhooks:read","webhooks:write"]',
        ];

        $rows = [];

        for ($i = 0; $i < 30; $i++) {
            $createdAt = $this->faker->dateTimeBetween('-90 days', '-1 day');
            $hasBeenUsed = $this->faker->boolean(60); // 60% have been used
            $isExpired = $this->faker->boolean(15); // 15% expired

            $expiresAt = null;
            if ($isExpired) {
                $expiresAt = $this->faker->dateTimeBetween($createdAt, '-1 day')->format('Y-m-d H:i:s');
            } elseif ($this->faker->boolean(50)) {
                $expiresAt = $this->faker->dateTimeBetween('+1 day', '+365 days')->format('Y-m-d H:i:s');
            }

            $rows[] = [
                'tokenable_type' => 'App\\Models\\User',
                'tokenable_id' => $this->faker->randomElement($userIds),
                'name' => $this->faker->randomElement($tokenNames) . ' ' . $this->faker->numberBetween(1, 99),
                'token' => hash('sha256', Str::random(40)),
                'abilities' => $this->faker->randomElement($abilitySets),
                'last_used_at' => $hasBeenUsed ? $this->faker->dateTimeBetween($createdAt, 'now')->format('Y-m-d H:i:s') : null,
                'expires_at' => $expiresAt,
                'created_at' => $createdAt->format('Y-m-d H:i:s'),
                'updated_at' => $createdAt->format('Y-m-d H:i:s'),
            ];
        }

        DB::table('personal_access_tokens')->insert($rows);
    }

    /**
     * Create 10 social accounts if the table exists.
     *
     * @param  \Illuminate\Support\Collection<int, User>  $users
     */
    private function seedSocialAccounts(\Illuminate\Support\Collection $users): void
    {
        if (! Schema::hasTable('social_accounts')) {
            $this->command?->info('  Skipping social accounts: table does not exist.');

            return;
        }

        $this->command?->info('  Creating 10 social accounts...');

        // Pick 10 unique users for social accounts
        $socialUsers = $users->random(min(10, $users->count()));

        $rows = [];
        $usedProviderIds = [];

        foreach ($socialUsers as $user) {
            $provider = $this->faker->randomElement(['google', 'github']);
            $providerId = $this->faker->unique()->numerify('##########');

            $rows[] = [
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_id' => $providerId,
                'token' => encrypt(Str::random(64)),
                'refresh_token' => $provider === 'google' ? encrypt(Str::random(64)) : null,
                'token_expires_at' => $provider === 'google'
                    ? $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d H:i:s')
                    : null,
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $user->created_at->format('Y-m-d H:i:s'),
            ];
        }

        DB::table('social_accounts')->insert($rows);
    }

    /**
     * Create 15 two_factor_authentications records if the table exists.
     *
     * @param  \Illuminate\Support\Collection<int, User>  $users
     */
    private function seedTwoFactorAuthentications(\Illuminate\Support\Collection $users): void
    {
        if (! Schema::hasTable('two_factor_authentications')) {
            $this->command?->info('  Skipping two-factor authentications: table does not exist.');

            return;
        }

        $this->command?->info('  Creating 15 two-factor authentication records...');

        // Pick 15 verified users for 2FA
        $verifiedUsers = $users->filter(fn (User $u) => $u->email_verified_at !== null);
        $tfaUsers = $verifiedUsers->random(min(15, $verifiedUsers->count()));

        $rows = [];

        foreach ($tfaUsers as $user) {
            $isEnabled = $this->faker->boolean(80); // 80% have it enabled
            $createdAt = $this->faker->dateTimeBetween($user->created_at, 'now');

            $rows[] = [
                'authenticatable_type' => 'App\\Models\\User',
                'authenticatable_id' => $user->id,
                'shared_secret' => encrypt(Str::random(32)),
                'enabled_at' => $isEnabled ? $createdAt->format('Y-m-d H:i:s') : null,
                'label' => $user->email,
                'digits' => 6,
                'seconds' => 30,
                'window' => 0,
                'algorithm' => 'sha1',
                'recovery_codes' => $isEnabled ? encrypt(json_encode([
                    ['code' => Str::random(10), 'used_at' => null],
                    ['code' => Str::random(10), 'used_at' => null],
                    ['code' => Str::random(10), 'used_at' => null],
                    ['code' => Str::random(10), 'used_at' => null],
                    ['code' => Str::random(10), 'used_at' => null],
                    ['code' => Str::random(10), 'used_at' => null],
                    ['code' => Str::random(10), 'used_at' => null],
                    ['code' => Str::random(10), 'used_at' => null],
                ])) : null,
                'recovery_codes_generated_at' => $isEnabled ? $createdAt->format('Y-m-d H:i:s') : null,
                'safe_devices' => null,
                'created_at' => $createdAt->format('Y-m-d H:i:s'),
                'updated_at' => $createdAt->format('Y-m-d H:i:s'),
            ];
        }

        DB::table('two_factor_authentications')->insert($rows);
    }
}
