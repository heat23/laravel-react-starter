<?php

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    // Clear the prefixed rate limiter key matching AppServiceProvider's consent-store definition
    RateLimiter::clear('consent-store|127.0.0.1');
});

it('records consent for a guest user', function () {
    Log::spy();

    $response = $this->postJson('/api/consent', [
        'categories' => [
            'necessary' => true,
            'analytics' => false,
            'marketing' => false,
        ],
    ]);

    $response->assertOk()
        ->assertJson(['success' => true]);

    Log::shouldHaveReceived('info')
        ->once()
        ->withArgs(function (string $message, array $context) {
            return $message === 'cookie_consent_recorded'
                && $context['categories']['necessary'] === true
                && $context['categories']['analytics'] === false
                && $context['categories']['marketing'] === false
                && $context['ip'] === '127.0.0.1'
                && $context['user_agent'] !== null;
        });
});

it('records consent with optional version and timestamp', function () {
    Log::spy();

    $response = $this->postJson('/api/consent', [
        'categories' => [
            'necessary' => true,
            'analytics' => true,
            'marketing' => false,
        ],
        'version' => '1.0',
        'timestamp' => '2026-04-01T12:00:00Z',
    ]);

    $response->assertOk()
        ->assertJson(['success' => true]);

    Log::shouldHaveReceived('info')
        ->once()
        ->withArgs(function (string $message, array $context) {
            return $message === 'cookie_consent_recorded'
                && $context['version'] === '1.0'
                && $context['timestamp'] === '2026-04-01T12:00:00Z';
        });
});

it('persists analytics consent setting for authenticated users', function () {
    Log::spy();
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/consent', [
            'categories' => [
                'necessary' => true,
                'analytics' => true,
                'marketing' => false,
            ],
        ])
        ->assertOk();

    expect($user->fresh()->getSetting(AuditService::ANALYTICS_CONSENT_KEY))->toBeTrue();
});

it('persists analytics decline for authenticated users', function () {
    Log::spy();
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/consent', [
            'categories' => [
                'necessary' => true,
                'analytics' => false,
                'marketing' => false,
            ],
        ])
        ->assertOk();

    expect($user->fresh()->getSetting(AuditService::ANALYTICS_CONSENT_KEY))->toBeFalse();
});

it('does not persist settings for guest users', function () {
    Log::spy();

    $this->postJson('/api/consent', [
        'categories' => [
            'necessary' => true,
            'analytics' => true,
            'marketing' => true,
        ],
    ])->assertOk();

    $this->assertDatabaseCount('user_settings', 0);
});

it('fails validation when categories.necessary is missing', function () {
    $response = $this->postJson('/api/consent', [
        'categories' => [
            'analytics' => true,
            'marketing' => false,
        ],
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['categories.necessary']);
});

it('fails validation when categories.necessary is false', function () {
    $response = $this->postJson('/api/consent', [
        'categories' => [
            'necessary' => false,
            'analytics' => true,
            'marketing' => false,
        ],
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['categories.necessary']);
});

it('fails validation when categories.necessary is integer zero', function () {
    $response = $this->postJson('/api/consent', [
        'categories' => [
            'necessary' => 0,
            'analytics' => true,
            'marketing' => false,
        ],
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['categories.necessary']);
});

it('fails validation when categories.necessary is string zero', function () {
    $response = $this->postJson('/api/consent', [
        'categories' => [
            'necessary' => '0',
            'analytics' => true,
            'marketing' => false,
        ],
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['categories.necessary']);
});

it('fails validation when categories.analytics is missing', function () {
    $response = $this->postJson('/api/consent', [
        'categories' => [
            'necessary' => true,
            'marketing' => false,
        ],
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['categories.analytics']);
});

it('fails validation when categories.marketing is missing', function () {
    $response = $this->postJson('/api/consent', [
        'categories' => [
            'necessary' => true,
            'analytics' => true,
        ],
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['categories.marketing']);
});

it('fails validation when categories contain non-boolean strings', function () {
    $response = $this->postJson('/api/consent', [
        'categories' => [
            'necessary' => 'invalid',
            'analytics' => 'no',
            'marketing' => 'maybe',
        ],
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'categories.necessary',
            'categories.analytics',
            'categories.marketing',
        ]);
});

it('rejects accepted-like string "yes" for necessary because boolean rule requires strict type', function () {
    $response = $this->postJson('/api/consent', [
        'categories' => [
            'necessary' => 'yes',
            'analytics' => true,
            'marketing' => false,
        ],
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['categories.necessary']);
});

it('accepts integer 1 for necessary and logs canonical boolean true', function () {
    Log::spy();

    $this->postJson('/api/consent', [
        'categories' => [
            'necessary' => 1,
            'analytics' => false,
            'marketing' => false,
        ],
    ])->assertOk();

    Log::shouldHaveReceived('info')
        ->once()
        ->withArgs(function (string $message, array $context) {
            return $message === 'cookie_consent_recorded'
                && $context['categories']['necessary'] === true;
        });
});

it('fails validation when version format is invalid', function () {
    $response = $this->postJson('/api/consent', [
        'categories' => [
            'necessary' => true,
            'analytics' => true,
            'marketing' => false,
        ],
        'version' => 'abc!@#',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['version']);
});

it('fails validation when timestamp format is invalid', function () {
    $response = $this->postJson('/api/consent', [
        'categories' => [
            'necessary' => true,
            'analytics' => true,
            'marketing' => false,
        ],
        'timestamp' => 'not-a-timestamp',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['timestamp']);
});

it('fails validation when version exceeds max length', function () {
    $response = $this->postJson('/api/consent', [
        'categories' => [
            'necessary' => true,
            'analytics' => true,
            'marketing' => false,
        ],
        'version' => '1.2.3.4.5.6.7.8.9.100',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['version']);
});

it('fails validation with empty request body', function () {
    $response = $this->postJson('/api/consent', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'categories.necessary',
            'categories.analytics',
            'categories.marketing',
        ]);
});

it('is rate limited to 10 requests per minute', function () {
    for ($i = 0; $i < 10; $i++) {
        $this->postJson('/api/consent', [
            'categories' => [
                'necessary' => true,
                'analytics' => false,
                'marketing' => false,
            ],
        ])->assertOk();
    }

    $response = $this->postJson('/api/consent', [
        'categories' => [
            'necessary' => true,
            'analytics' => false,
            'marketing' => false,
        ],
    ]);

    $response->assertStatus(429);
});

it('logs null for version and timestamp when not provided', function () {
    Log::spy();

    $this->postJson('/api/consent', [
        'categories' => [
            'necessary' => true,
            'analytics' => true,
            'marketing' => true,
        ],
    ])->assertOk();

    Log::shouldHaveReceived('info')
        ->once()
        ->withArgs(function (string $message, array $context) {
            return $message === 'cookie_consent_recorded'
                && $context['version'] === null
                && $context['timestamp'] === null;
        });
});
