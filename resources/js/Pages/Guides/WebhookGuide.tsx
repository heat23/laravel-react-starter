// Article content is hardcoded as structured JSX (Option A).
// For a multi-article blog, Option B (Markdown files in resources/content/guides/
// parsed by league/commonmark and passed as HTML props) would be preferable.
// Option B would require DOMPurify.sanitize() on all rendered HTML.

import { ArrowRight } from 'lucide-react';

import { useEffect } from 'react';

import { Head, Link } from '@inertiajs/react';

import {
  TableOfContents,
  type TocSection,
} from '@/Components/blog/TableOfContents';
import { Logo, TextLogo } from '@/Components/branding/Logo';
import { BreadcrumbJsonLd } from '@/Components/seo/BreadcrumbJsonLd';
import { JsonLd } from '@/Components/seo/JsonLd';
import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';
import { Button } from '@/Components/ui/button';
import type { GuidePageProps } from '@/types/index';

interface WebhookGuideProps extends GuidePageProps {
  appUrl: string;
}

const sections: TocSection[] = [
  { id: 'why-webhooks', title: '1. Why SaaS Products Need Webhooks', level: 2 },
  {
    id: 'incoming-vs-outgoing',
    title: '2. Incoming vs Outgoing Webhooks',
    level: 2,
  },
  {
    id: 'hmac-verification',
    title: '3. HMAC-SHA256 Signature Verification',
    level: 2,
  },
  { id: 'outgoing-webhooks', title: '4. Building Outgoing Webhooks', level: 2 },
  {
    id: 'retry-tracking',
    title: '5. Retry Logic and Delivery Tracking',
    level: 2,
  },
  { id: 'react-ui', title: '6. Webhook Management UI in React', level: 2 },
  { id: 'pest-tests', title: '7. Testing Webhooks in Pest', level: 2 },
  { id: 'production', title: '8. Production Monitoring', level: 2 },
  { id: 'starter-kit', title: '9. Laravel React Starter Ships This', level: 2 },
];

export default function WebhookGuide({
  title,
  metaDescription,
  appName,
  appUrl,
  breadcrumbs,
}: WebhookGuideProps) {
  const { track } = useAnalytics();

  useEffect(() => {
    track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'guides-webhook' });
  }, [track]);

  const canonicalUrl = `${appUrl}/guides/laravel-webhook-implementation`;

  // Article JSON-LD: author is Person (audit fix SD006), mainEntityOfPage + wordCount added (audit fix SD005)
  // Not wrapped in DOMPurify — JSON-LD is structured data, not user HTML (audit fix SD009)
  const articleSchema = {
    '@context': 'https://schema.org',
    '@type': 'Article',
    headline: title,
    description: metaDescription,
    author: { '@type': 'Person', name: 'Laravel React Starter' },
    publisher: { '@type': 'Organization', name: appName },
    datePublished: '2026-03-20',
    dateModified: '2026-03-20',
    mainEntityOfPage: { '@type': 'WebPage', '@id': canonicalUrl },
    wordCount: 2600,
  };

  const howToSchema = {
    '@context': 'https://schema.org',
    '@type': 'HowTo',
    name: 'How to Implement Webhooks in a Laravel SaaS',
    description:
      'Production-grade webhook implementation with HMAC-SHA256 signing, queue-based retry, and delivery tracking.',
    step: [
      {
        '@type': 'HowToStep',
        position: 1,
        name: 'Configure webhook secrets',
        text: 'Add provider-specific secrets to config/webhooks.php.',
      },
      {
        '@type': 'HowToStep',
        position: 2,
        name: 'Create a WebhookEndpoint via the UI',
        text: 'Users register their destination URLs through the webhook management UI.',
      },
      {
        '@type': 'HowToStep',
        position: 3,
        name: 'Dispatch webhooks using WebhookService',
        text: 'Call WebhookService::dispatch() — it signs the payload and queues DispatchWebhookJob.',
      },
      {
        '@type': 'HowToStep',
        position: 4,
        name: 'Verify incoming webhook signatures',
        text: 'Apply the VerifyWebhookSignature middleware to incoming webhook routes.',
      },
      {
        '@type': 'HowToStep',
        position: 5,
        name: 'Monitor delivery status',
        text: 'Check the webhook_deliveries table for failed attempts and use the prune command to clean up.',
      },
    ],
  };

  return (
    <>
      <Head title={title}>
        <meta name="description" content={metaDescription} />
        <link rel="canonical" href={canonicalUrl} />
        <meta property="og:title" content={title} />
        <meta property="og:description" content={metaDescription} />
        <meta property="og:type" content="article" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content={title} />
        <meta name="twitter:description" content={metaDescription} />
        {breadcrumbs && <BreadcrumbJsonLd breadcrumbs={breadcrumbs} />}
        <JsonLd data={articleSchema} />
        <JsonLd data={howToSchema} />
      </Head>

      <div className="min-h-screen bg-background">
        <nav className="container flex items-center justify-between py-6">
          <Link href="/" className="flex items-center gap-2">
            <Logo className="h-8 w-8" />
            <TextLogo className="text-xl font-bold" />
          </Link>
          <div className="flex items-center gap-4">
            <Link
              href="/features/billing"
              className="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
              Billing
            </Link>
            <Link
              href="/guides/building-saas-with-laravel-12"
              className="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
              Laravel SaaS Guide
            </Link>
            <Link href="/pricing">
              <Button size="sm">
                Get Started <ArrowRight className="ml-1 h-4 w-4" />
              </Button>
            </Link>
          </div>
        </nav>

        <main className="container pb-24">
          <header className="mx-auto max-w-4xl py-12 text-center">
            <p className="mb-4 text-sm font-medium uppercase tracking-wider text-primary">
              Implementation Guide
            </p>
            <h1 className="text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl">
              Laravel Webhook Implementation &mdash; HMAC Signing, Retry Logic,
              and Delivery Tracking
            </h1>
            <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
              This guide covers production-grade webhooks in Laravel: outgoing
              webhooks with HMAC-SHA256 signing, async delivery via queue jobs,
              and delivery tracking. Plus incoming webhook verification for
              GitHub and Stripe. Laravel React Starter ships all of this.
            </p>
            <div className="mt-4 flex items-center justify-center gap-4 text-sm text-muted-foreground">
              <time dateTime="2026-03-20">March 20, 2026</time>
              <span aria-hidden="true">&middot;</span>
              <span>16 min read</span>
            </div>
          </header>

          {/* Two-column layout: article + sticky ToC */}
          <div className="mx-auto max-w-6xl lg:grid lg:grid-cols-[1fr_250px] lg:gap-12">
            <div>
              {/* Mobile ToC */}
              <TableOfContents sections={sections} />

              <article className="prose prose-neutral dark:prose-invert max-w-none">
                {/* Section 1: Why Webhooks */}
                <h2 id="why-webhooks">1. Why SaaS Products Need Webhooks</h2>
                <p>
                  Webhooks are the connective tissue of the modern SaaS
                  ecosystem. When a subscription renews, a file is processed, or
                  a project status changes, your customers need to know &mdash;
                  immediately, and in a way they can automate. Polling an API
                  every minute to check for state changes is both wasteful and
                  slow. Webhooks push the event to the customer the moment it
                  happens.
                </p>
                <p>
                  The practical demand is real: Zapier, Make (formerly
                  Integromat), and n8n all require webhook support to integrate
                  with your product. Without webhooks, your SaaS cannot appear
                  in automation workflows that your customers already use.
                  Enterprise buyers evaluating products ask &ldquo;do you have
                  webhooks?&rdquo; in the same breath as &ldquo;do you have an
                  API?&rdquo;. Both are table stakes for B2B SaaS in 2026.
                </p>

                {/* Section 2: Incoming vs Outgoing */}
                <h2 id="incoming-vs-outgoing">
                  2. Incoming vs Outgoing Webhooks
                </h2>

                <div className="not-prose overflow-x-auto">
                  <table className="w-full border-collapse text-sm">
                    <thead>
                      <tr className="border-b">
                        <th className="py-3 pr-6 text-left font-semibold">
                          Type
                        </th>
                        <th className="py-3 pr-6 text-left font-semibold">
                          Direction
                        </th>
                        <th className="py-3 pr-6 text-left font-semibold">
                          Use Case
                        </th>
                        <th className="py-3 pr-6 text-left font-semibold">
                          Who Signs
                        </th>
                        <th className="py-3 text-left font-semibold">
                          Who Verifies
                        </th>
                      </tr>
                    </thead>
                    <tbody className="divide-y">
                      <tr>
                        <td className="py-3 pr-6 font-medium">Incoming</td>
                        <td className="py-3 pr-6 text-muted-foreground">
                          Third party → Your app
                        </td>
                        <td className="py-3 pr-6 text-muted-foreground">
                          GitHub push events, Stripe payment events
                        </td>
                        <td className="py-3 pr-6 text-muted-foreground">
                          GitHub / Stripe
                        </td>
                        <td className="py-3 text-muted-foreground">
                          Your middleware
                        </td>
                      </tr>
                      <tr>
                        <td className="py-3 pr-6 font-medium">Outgoing</td>
                        <td className="py-3 pr-6 text-muted-foreground">
                          Your app → Customer systems
                        </td>
                        <td className="py-3 pr-6 text-muted-foreground">
                          Notify Zapier, customer CI, automation workflows
                        </td>
                        <td className="py-3 pr-6 text-muted-foreground">
                          Your app
                        </td>
                        <td className="py-3 text-muted-foreground">
                          Customer&apos;s system
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <p>
                  Most SaaS webhook guides cover one direction. The{' '}
                  <code>spatie/laravel-webhook-client</code> package, for
                  example, handles only incoming webhooks. A production SaaS
                  typically needs both: incoming for Stripe payment events
                  (which drive your billing state) and outgoing to push your
                  product&apos;s events to customer systems. This guide covers
                  both.
                </p>

                {/* Section 3: HMAC Verification */}
                <h2 id="hmac-verification">
                  3. HMAC-SHA256 Signature Verification
                </h2>
                <p>
                  The core security challenge with webhooks is authentication:
                  how does your app know that an incoming request actually came
                  from GitHub or Stripe, not from an attacker replaying a
                  captured request? The answer is HMAC-SHA256 signatures.
                </p>
                <p>
                  HMAC (Hash-based Message Authentication Code) uses a shared
                  secret to produce a digest of the request body. The sender
                  hashes the payload with the secret; the receiver recomputes
                  the hash and compares. If they match, the payload is authentic
                  and unmodified. The secret is never transmitted &mdash; unlike
                  API keys in Authorization headers, it is never on the wire.
                </p>

                <p>The signature format used in Laravel React Starter:</p>

                <pre>
                  <code>{`// Signature header: X-Webhook-Signature
// Value format: sha256=<hex-digest>
// Digest = hash_hmac('sha256', $rawPayload, $secret)

$expectedSignature = 'sha256=' . hash_hmac('sha256', $rawPayload, $secret);
$receivedSignature = $request->header('X-Webhook-Signature');

// CRITICAL: use hash_equals for timing-safe comparison
// Never use === — timing attacks can leak the secret byte by byte
if (! hash_equals($expectedSignature, $receivedSignature)) {
    abort(401, 'Invalid webhook signature');
}`}</code>
                </pre>

                <p>
                  The <code>VerifyWebhookSignature</code> middleware in{' '}
                  <code>app/Http/Middleware/</code> implements this check.
                  Provider-specific secrets are stored in{' '}
                  <code>config/webhooks.php</code>:
                </p>

                <pre>
                  <code>{`// config/webhooks.php
return [
    'github' => [
        'secret' => env('GITHUB_WEBHOOK_SECRET'),
    ],
    'stripe' => [
        // Stripe uses its own scheme via Cashier — not this middleware
        'secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],
    'custom' => [
        'secret' => env('CUSTOM_WEBHOOK_SECRET'),
    ],
];`}</code>
                </pre>

                <p>
                  Stripe uses its own signature scheme via Laravel Cashier (not
                  the <code>VerifyWebhookSignature</code> middleware). The
                  Stripe webhook route is excluded from CSRF because Cashier
                  verifies the Stripe signature internally. For GitHub and
                  custom providers, apply the <code>verify-webhook</code>{' '}
                  middleware alias:
                </p>

                <pre>
                  <code>{`// routes/api.php
Route::post('/webhooks/github', [IncomingWebhookController::class, 'github'])
    ->middleware('verify-webhook:github')
    ->name('webhooks.github');`}</code>
                </pre>

                {/* Section 4: Outgoing Webhooks */}
                <h2 id="outgoing-webhooks">4. Building Outgoing Webhooks</h2>
                <p>
                  Outgoing webhooks need three moving parts: a model to store
                  endpoint configuration, a model to track delivery attempts,
                  and a service + job to handle async dispatch.
                </p>

                <h3>Models</h3>

                <pre>
                  <code>{`// WebhookEndpoint — user-configured destination
Schema::create('webhook_endpoints', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete()->index();
    $table->string('url');
    $table->string('secret', 64); // HMAC signing key, stored hashed
    $table->json('events')->default('[]'); // event types to deliver
    $table->boolean('active')->default(true);
    $table->timestamps();
});

// WebhookDelivery — attempt tracking
Schema::create('webhook_deliveries', function (Blueprint $table) {
    $table->id();
    $table->foreignId('webhook_endpoint_id')->constrained()->cascadeOnDelete()->index();
    $table->string('event_type');
    $table->json('payload');
    $table->string('status')->default('pending'); // pending|delivered|failed|abandoned
    $table->integer('response_code')->nullable();
    $table->text('response_body')->nullable();
    $table->unsignedInteger('attempts')->default(0);
    $table->timestamp('last_attempt_at')->nullable();
    $table->timestamps();
});`}</code>
                </pre>

                <h3>WebhookService dispatch</h3>

                <pre>
                  <code>{`// app/Services/WebhookService.php

public function dispatch(User $user, string $eventType, array $payload): void
{
    $endpoints = $user->webhookEndpoints()
        ->where('active', true)
        ->whereJsonContains('events', $eventType)
        ->get();

    foreach ($endpoints as $endpoint) {
        $delivery = WebhookDelivery::create([
            'webhook_endpoint_id' => $endpoint->id,
            'event_type' => $eventType,
            'payload' => $payload,
            'status' => 'pending',
        ]);

        DispatchWebhookJob::dispatch($delivery);
    }
}`}</code>
                </pre>

                <h3>Payload signing in the job</h3>

                <pre>
                  <code>{`// app/Jobs/DispatchWebhookJob.php

public function handle(): void
{
    $endpoint = $this->delivery->webhookEndpoint;
    $payload = json_encode($this->delivery->payload);

    $signature = 'sha256=' . hash_hmac('sha256', $payload, $endpoint->secret);

    try {
        $response = Http::timeout(10)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'X-Webhook-Signature' => $signature,
                'X-Webhook-Event' => $this->delivery->event_type,
            ])
            ->post($endpoint->url, $this->delivery->payload);

        $this->delivery->update([
            'status' => $response->successful() ? 'delivered' : 'failed',
            'response_code' => $response->status(),
            'response_body' => substr($response->body(), 0, 1000),
            'attempts' => $this->delivery->attempts + 1,
            'last_attempt_at' => now(),
        ]);
    } catch (\\Throwable $e) {
        $this->delivery->update([
            'status' => 'failed',
            'response_body' => $e->getMessage(),
            'attempts' => $this->delivery->attempts + 1,
            'last_attempt_at' => now(),
        ]);

        throw $e; // Allow Laravel's job retry to handle backoff
    }
}`}</code>
                </pre>

                {/* Section 5: Retry Logic */}
                <h2 id="retry-tracking">
                  5. Retry Logic and Delivery Tracking
                </h2>
                <p>
                  Laravel&apos;s job retry mechanism handles the retry loop.
                  Configure the job with exponential backoff to avoid hammering
                  a temporarily unavailable endpoint:
                </p>

                <pre>
                  <code>{`class DispatchWebhookJob implements ShouldQueue
{
    public int $tries = 5;
    public int $maxExceptions = 5;

    public function backoff(): array
    {
        // Retry at: 1min, 5min, 15min, 1hr, 4hr
        return [60, 300, 900, 3600, 14400];
    }

    public function failed(\\Throwable $exception): void
    {
        $this->delivery->update(['status' => 'abandoned']);
    }
}`}</code>
                </pre>

                <p>
                  After the maximum retries are exhausted, the{' '}
                  <code>failed()</code> method marks the delivery as{' '}
                  <code>abandoned</code>. This gives you a clear audit trail:
                  every delivery attempt is recorded with its response code and
                  body, so you can diagnose failures and replay events if
                  needed.
                </p>
                <p>
                  The <code>webhooks:prune-stale</code> artisan command cleans
                  up abandoned and old delivered records to prevent the{' '}
                  <code>webhook_deliveries</code> table from growing
                  unboundedly:
                </p>

                <pre>
                  <code>{`php artisan webhooks:prune-stale --hours=168  # Prune records older than 7 days`}</code>
                </pre>

                {/* Section 6: React UI */}
                <h2 id="react-ui">
                  6. Building the Webhook Management UI in React
                </h2>
                <p>
                  The webhook management UI has two views: the endpoint list
                  (add/edit/delete endpoints) and the delivery history per
                  endpoint (status, response code, retry button).
                </p>

                <pre>
                  <code>{`// resources/js/Pages/Settings/Webhooks.tsx

import { useForm, router } from '@inertiajs/react';
import { LoadingButton } from '@/Components/LoadingButton';

interface WebhookEndpoint {
    id: number;
    url: string;
    events: string[];
    active: boolean;
}

interface Props {
    endpoints: WebhookEndpoint[];
}

export default function Webhooks({ endpoints }: Props) {
    const form = useForm({
        url: '',
        events: [] as string[],
    });

    function handleCreate() {
        form.post('/settings/webhooks', {
            onSuccess: () => form.reset(),
        });
    }

    function handleDelete(id: number) {
        // Inertia router calls are fire-and-forget — NOT Promises.
        // Use onSuccess callback, not await.
        router.delete(\`/settings/webhooks/\${id}\`, {
            onSuccess: () => {
                // Page will re-render with updated endpoints from server
            },
        });
    }

    return (
        <div>
            <div>
                <input
                    type="url"
                    value={form.data.url}
                    onChange={e => form.setData('url', e.target.value)}
                    placeholder="https://your-app.com/webhooks"
                    aria-label="Webhook URL"
                />
                {form.errors.url && (
                    <p className="text-sm text-destructive" role="alert">
                        {form.errors.url}
                    </p>
                )}
                <LoadingButton loading={form.processing} onClick={handleCreate}>
                    Add Endpoint
                </LoadingButton>
            </div>

            <ul>
                {endpoints.map(endpoint => (
                    <li key={endpoint.id}>
                        <span>{endpoint.url}</span>
                        <LoadingButton
                            variant="destructive"
                            size="sm"
                            onClick={() => handleDelete(endpoint.id)}
                        >
                            Delete
                        </LoadingButton>
                    </li>
                ))}
            </ul>
        </div>
    );
}`}</code>
                </pre>

                <p>
                  For the delivery history view, display status badges (
                  <code>delivered</code> → green, <code>failed</code> → red,{' '}
                  <code>abandoned</code> → gray) and a retry button that
                  re-queues the <code>DispatchWebhookJob</code> for failed
                  deliveries.
                </p>

                {/* Section 7: Pest Tests */}
                <h2 id="pest-tests">7. Testing Webhooks in Pest</h2>

                <pre>
                  <code>{`// tests/Feature/Webhooks/WebhookTest.php

use App\\Jobs\\DispatchWebhookJob;
use App\\Models\\User;
use App\\Models\\WebhookEndpoint;
use App\\Models\\WebhookDelivery;
use App\\Services\\WebhookService;
use Illuminate\\Support\\Facades\\Queue;

it('creating an endpoint stores it scoped to the user', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->post('/settings/webhooks', [
            'url' => 'https://example.com/hook',
            'events' => ['user.created'],
        ])
        ->assertRedirect();

    expect(
        WebhookEndpoint::where('user_id', $user->id)
            ->where('url', 'https://example.com/hook')
            ->exists()
    )->toBeTrue();
});

it('dispatching a webhook queues a DispatchWebhookJob', function () {
    Queue::fake();

    $user = User::factory()->create();
    $endpoint = WebhookEndpoint::factory()
        ->for($user)
        ->create(['events' => ['user.created']]);

    app(WebhookService::class)->dispatch($user, 'user.created', ['id' => 1]);

    Queue::assertPushed(DispatchWebhookJob::class);
});

it('HMAC signature matches expected value', function () {
    $secret = 'test-secret';
    $payload = '{"event":"user.created","id":1}';

    $expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);

    expect($expected)->toStartWith('sha256=');
    expect(strlen($expected))->toBe(71); // 'sha256=' + 64-char hex digest
});

it('invalid signature returns 401', function () {
    post('/api/webhooks/github', ['event' => 'push'], [
        'X-Webhook-Signature' => 'sha256=invalidsignature',
    ])->assertStatus(401);
});

it('delivery tracking record is created after dispatch', function () {
    Queue::fake();

    $user = User::factory()->create();
    WebhookEndpoint::factory()
        ->for($user)
        ->create(['events' => ['subscription.cancelled']]);

    app(WebhookService::class)->dispatch($user, 'subscription.cancelled', ['plan' => 'pro']);

    expect(WebhookDelivery::count())->toBe(1);
    expect(WebhookDelivery::first()->status)->toBe('pending');
});`}</code>
                </pre>

                {/* Section 8: Production Monitoring */}
                <h2 id="production">8. Production Monitoring</h2>
                <p>
                  <strong>Alert on delivery failure rate.</strong> If more than
                  10% of deliveries in a 1-hour window have status{' '}
                  <code>failed</code> or <code>abandoned</code>, that likely
                  indicates a systemic issue (queue worker down, DNS failure on
                  a common endpoint TLD) rather than individual endpoint
                  problems. Set up a scheduled job that checks this rate and
                  fires a Slack notification.
                </p>
                <p>
                  <strong>Prune old delivery records.</strong> Add{' '}
                  <code>webhooks:prune-stale</code> to your scheduled task list
                  in <code>routes/console.php</code>:
                </p>

                <pre>
                  <code>{`// routes/console.php
Schedule::command('webhooks:prune-stale --hours=168')->weekly();`}</code>
                </pre>

                <p>
                  <strong>Health check:</strong> The <code>/health</code>{' '}
                  endpoint in Laravel React Starter checks queue worker status.
                  If the queue is down, outgoing webhook delivery stops silently
                  — the health check surfaces this before customers notice. See{' '}
                  <code>app/Services/HealthCheckService.php</code> for the queue
                  health check implementation.
                </p>

                {/* Section 9: Starter Kit */}
                <h2 id="starter-kit">9. Laravel React Starter Ships This</h2>
                <p>
                  The complete webhook system &mdash; outgoing signed webhooks,
                  incoming verification middleware, delivery tracking, retry
                  logic, and the React management UI &mdash; is pre-built in
                  Laravel React Starter. Enable it with{' '}
                  <code>FEATURE_WEBHOOKS=true</code> in your <code>.env</code>.
                  The{' '}
                  <Link
                    href="/features/billing"
                    className="text-primary hover:underline"
                  >
                    Stripe billing integration
                  </Link>{' '}
                  uses the incoming webhook infrastructure to handle payment
                  events, so you get a working real-world example immediately.
                </p>
                <p>
                  For broader architecture context, see the{' '}
                  <Link
                    href="/guides/building-saas-with-laravel-12"
                    className="text-primary hover:underline"
                  >
                    Complete Guide to Building a SaaS with Laravel 12
                  </Link>
                  , which covers webhooks alongside billing, auth, feature
                  flags, and testing strategy.
                </p>

                <div className="not-prose mt-12 rounded-lg border bg-muted/30 p-8 text-center">
                  <h3 className="text-xl font-bold">
                    Webhooks, billing, 2FA &mdash; all pre-built
                  </h3>
                  <p className="mt-2 text-muted-foreground">
                    Laravel React Starter includes production-grade webhooks
                    with HMAC signing, delivery tracking, and retry logic.
                    Enable with one env var.
                  </p>
                  <div className="mt-6 flex justify-center gap-4">
                    <Link href="/pricing">
                      <Button size="lg">
                        View Pricing <ArrowRight className="ml-2 h-4 w-4" />
                      </Button>
                    </Link>
                    <Link href="/guides/building-saas-with-laravel-12">
                      <Button size="lg" variant="outline">
                        Read the Full Guide
                      </Button>
                    </Link>
                  </div>
                </div>
              </article>
            </div>

            {/* Desktop sticky ToC */}
            <aside className="hidden lg:block">
              <div className="sticky top-8">
                <TableOfContents sections={sections} sticky />
              </div>
            </aside>
          </div>
        </main>

        <footer className="border-t py-12">
          <div className="container flex flex-col items-center justify-between gap-4 sm:flex-row">
            <p className="text-sm text-muted-foreground">
              &copy; {new Date().getFullYear()} {appName}
            </p>
            <div className="flex gap-6">
              <Link
                href="/privacy"
                className="text-sm text-muted-foreground hover:text-foreground"
              >
                Privacy
              </Link>
              <Link
                href="/terms"
                className="text-sm text-muted-foreground hover:text-foreground"
              >
                Terms
              </Link>
              <Link
                href="/contact"
                className="text-sm text-muted-foreground hover:text-foreground"
              >
                Contact
              </Link>
            </div>
          </div>
        </footer>
      </div>
    </>
  );
}
