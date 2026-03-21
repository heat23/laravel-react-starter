import { ArrowRight } from 'lucide-react';

import { useEffect } from 'react';

import { Head, Link } from '@inertiajs/react';

import { TableOfContents, type TocSection } from '@/Components/blog/TableOfContents';
import { Logo, TextLogo } from '@/Components/branding/Logo';
import { BreadcrumbJsonLd } from '@/Components/seo/BreadcrumbJsonLd';
import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';
import { Button } from '@/Components/ui/button';
import type { GuidePageProps } from '@/types/index';

const sections: TocSection[] = [
    { id: 'install-cashier', title: '1. Install and Configure Laravel Cashier', level: 2 },
    { id: 'plan-configuration', title: '2. Plan Configuration — Don\u2019t Hardcode Prices', level: 2 },
    { id: 'subscription-form', title: '3. The Subscription Form — React + TypeScript', level: 2 },
    { id: 'race-conditions', title: '4. Preventing Race Conditions with Redis Locks', level: 2 },
    { id: 'webhook-handling', title: '5. Webhook Handling — The Events That Matter', level: 2 },
    { id: 'payment-succeeded', title: 'invoice.payment_succeeded', level: 3 },
    { id: 'payment-failed', title: 'invoice.payment_failed', level: 3 },
    { id: 'subscription-deleted', title: 'customer.subscription.deleted', level: 3 },
    { id: 'subscription-updated', title: 'customer.subscription.updated', level: 3 },
    { id: 'dunning', title: '6. Dunning — Recovering Failed Payments', level: 2 },
    { id: 'testing-billing', title: '7. Testing Billing Code', level: 2 },
];

export default function StripeBillingGuide({ title, metaDescription, appName, breadcrumbs }: GuidePageProps) {
    const { track } = useAnalytics();

    useEffect(() => {
        track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'guides-stripe-billing' });
    }, [track]);

    const articleSchema = JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'Article',
        headline: title,
        description: metaDescription,
        author: { '@type': 'Organization', name: appName },
        publisher: { '@type': 'Organization', name: appName },
        datePublished: '2026-03-19',
        dateModified: '2026-03-19',
    });

    return (
        <>
            <Head title={title}>
                <meta name="description" content={metaDescription} />
                <meta property="og:title" content={title} />
                <meta property="og:description" content={metaDescription} />
                <meta property="og:type" content="article" />
                <meta name="twitter:card" content="summary_large_image" />
                <meta name="twitter:title" content={title} />
                <meta name="twitter:description" content={metaDescription} />
                {breadcrumbs && <BreadcrumbJsonLd breadcrumbs={breadcrumbs} />}
                <script
                    type="application/ld+json"
                    dangerouslySetInnerHTML={{ __html: articleSchema.replace(/<\/script>/gi, '<\\/script>') }}
                />
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
                            href="/features/feature-flags"
                            className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                        >
                            Feature Flags
                        </Link>
                        <Link
                            href="/pricing"
                            className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                        >
                            Pricing
                        </Link>
                    </div>
                </nav>

                <main className="container pb-24">
                    <header className="mx-auto max-w-4xl py-12 text-center">
                        <p className="mb-4 text-sm font-medium uppercase tracking-wider text-primary">
                            Tutorial
                        </p>
                        <h1 className="text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl">
                            Laravel Stripe Billing: Subscriptions, Webhooks, and Race Conditions
                        </h1>
                        <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                            How to implement production-grade Stripe billing in Laravel 12: subscriptions
                            with Cashier, webhook handling, race condition prevention with Redis locks,
                            and dunning emails.
                        </p>
                        <div className="mt-4 flex items-center justify-center gap-4 text-sm text-muted-foreground">
                            <time dateTime="2026-03-19">March 19, 2026</time>
                            <span aria-hidden="true">&middot;</span>
                            <span>12 min read</span>
                        </div>
                    </header>

                    <div className="mx-auto max-w-6xl lg:grid lg:grid-cols-[1fr_250px] lg:gap-12">
                        <div>
                            <TableOfContents sections={sections} />

                            <article className="prose prose-neutral dark:prose-invert max-w-none">
                                <p>
                                    Most Laravel + Stripe tutorials stop at{' '}
                                    <code>$user-&gt;newSubscription(&apos;default&apos;, $priceId)-&gt;create($paymentMethodId)</code>.
                                    That gets you a subscription in dev. In production, you&apos;ll hit: concurrent requests creating
                                    duplicate subscriptions, incomplete payments from 3D Secure cards, failed payment recovery, and
                                    webhook signature validation. This guide covers the full implementation &mdash; including the
                                    problems most tutorials skip.
                                </p>
                                <p>
                                    We&apos;ll walk through seven areas of a production billing system, from initial Cashier setup
                                    through dunning and testing. If you&apos;re building a SaaS on Laravel and want billing that
                                    actually works under real-world conditions, this is the guide for you. For the broader picture
                                    of building a full SaaS, see our{' '}
                                    <Link href="/guides/building-saas-with-laravel-12" className="text-primary hover:underline">
                                        complete Laravel SaaS guide
                                    </Link>.
                                </p>

                                {/* Section 1 */}
                                <h2 id="install-cashier">1. Install and Configure Laravel Cashier</h2>
                                <p>
                                    Laravel Cashier is the official billing package for Stripe. It wraps the Stripe PHP SDK with
                                    Eloquent models for subscriptions, payment methods, and invoices. Start by installing it:
                                </p>
                                <pre><code>{`composer require laravel/cashier
php artisan cashier:install
php artisan migrate`}</code></pre>
                                <p>
                                    The <code>cashier:install</code> command publishes migrations that add Stripe-related columns
                                    to your <code>users</code> table (<code>stripe_id</code>, <code>pm_type</code>,{' '}
                                    <code>pm_last_four</code>, <code>trial_ends_at</code>) and creates the{' '}
                                    <code>subscriptions</code> and <code>subscription_items</code> tables.
                                </p>
                                <p>Add the <code>Billable</code> trait to your User model:</p>
                                <pre><code>{`use Laravel\\Cashier\\Billable;

class User extends Authenticatable
{
    use Billable;
}`}</code></pre>
                                <p>Configure your <code>.env</code> with Stripe keys:</p>
                                <pre><code>{`STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...`}</code></pre>
                                <p>
                                    In your Stripe dashboard, create a webhook endpoint pointing to{' '}
                                    <code>https://yourapp.com/stripe/webhook</code>. Select these events:{' '}
                                    <code>customer.subscription.created</code>, <code>invoice.payment_failed</code>,{' '}
                                    <code>invoice.payment_succeeded</code>, and <code>customer.subscription.deleted</code>.
                                    The webhook secret (<code>whsec_...</code>) is displayed after you save the endpoint.
                                </p>
                                <p>
                                    Keep test-mode and live-mode keys in separate <code>.env</code> files &mdash; never
                                    mix them. Your local <code>.env</code> uses <code>pk_test_</code>/<code>sk_test_</code>
                                    keys; your production server uses <code>pk_live_</code>/<code>sk_live_</code>. Cashier
                                    doesn&apos;t care which environment you&apos;re in &mdash; it uses whatever keys you give it.
                                </p>
                                <p>
                                    <em>
                                        Note: the{' '}
                                        <Link href="/" className="text-primary hover:underline">
                                            Laravel React Starter
                                        </Link>{' '}
                                        has all of this pre-configured behind a feature flag. Set{' '}
                                        <code>FEATURE_BILLING=true</code> in your <code>.env</code> and the entire billing
                                        surface (routes, controllers, webhooks) activates. This section is for developers
                                        building from scratch.
                                    </em>
                                </p>

                                {/* Section 2 */}
                                <h2 id="plan-configuration">2. Plan Configuration &mdash; Don&apos;t Hardcode Prices</h2>
                                <p>
                                    The most common billing anti-pattern is hardcoding Stripe Price IDs in controllers:
                                </p>
                                <pre><code>{`// ❌ Don't do this
$user->newSubscription('default', 'price_1abc123')->create($paymentMethodId);`}</code></pre>
                                <p>
                                    This breaks when you add annual pricing, change plans, or use different Stripe accounts
                                    for staging. The correct approach is a <code>config/plans.php</code> file with a typed
                                    structure per plan:
                                </p>
                                <pre><code>{`// config/plans.php
return [
    'free' => [
        'name' => 'Free',
        'monthly_price_id' => null,
        'annual_price_id' => null,
        'features' => ['5 projects', '100 API calls/day'],
    ],
    'pro' => [
        'name' => 'Pro',
        'monthly_price_id' => env('STRIPE_PRO_MONTHLY_PRICE_ID'),
        'annual_price_id' => env('STRIPE_PRO_ANNUAL_PRICE_ID'),
        'features' => ['Unlimited projects', '10,000 API calls/day', 'Priority support'],
    ],
    'team' => [
        'name' => 'Team',
        'monthly_price_id' => env('STRIPE_TEAM_MONTHLY_PRICE_ID'),
        'annual_price_id' => env('STRIPE_TEAM_ANNUAL_PRICE_ID'),
        'features' => ['Everything in Pro', 'Team billing', '3-50 seats'],
        'min_seats' => 3,
        'max_seats' => 50,
    ],
];`}</code></pre>
                                <p>
                                    Now your controller reads <code>config(&apos;plans.pro.monthly_price_id&apos;)</code>.
                                    Change the plan in one place and it propagates everywhere &mdash; pricing page, subscription
                                    creation, plan comparison logic. Stripe Price IDs are static configuration values, not
                                    database records. They belong in config, not in a <code>plans</code> table.
                                </p>

                                {/* Section 3 */}
                                <h2 id="subscription-form">3. The Subscription Form &mdash; React + TypeScript</h2>
                                <p>
                                    The subscription flow has three steps: collect a payment method from the user, send it
                                    to your Laravel backend, and create the subscription via Cashier. Here&apos;s how each
                                    step works with React and Inertia.
                                </p>
                                <p>
                                    First, collect the payment method using <code>@stripe/react-stripe-js</code>. The{' '}
                                    <code>PaymentElement</code> (or <code>CardElement</code>) handles card input, validation,
                                    and 3D Secure challenges:
                                </p>
                                <pre><code>{`import { PaymentElement, useStripe, useElements } from '@stripe/react-stripe-js';

function SubscriptionForm({ planId }: { planId: string }) {
    const stripe = useStripe();
    const elements = useElements();

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!stripe || !elements) return;

        const { paymentMethod, error } = await stripe.createPaymentMethod({
            elements,
        });

        if (error) {
            // Show error to user
            return;
        }

        // Send to Laravel backend
        router.post('/billing/subscribe', {
            payment_method_id: paymentMethod.id,
            plan: planId,
        }, {
            onSuccess: () => {
                // Redirect handled by server
            },
            onError: (errors) => {
                // Show validation errors
            },
        });
    };

    return (
        <form onSubmit={handleSubmit}>
            <PaymentElement />
            <button type="submit" disabled={!stripe}>Subscribe</button>
        </form>
    );
}`}</code></pre>
                                <p>
                                    Notice the Inertia <code>router.post()</code> call. This is a critical pattern: Inertia&apos;s
                                    router methods are fire-and-forget &mdash; they return <code>undefined</code>, not a Promise.
                                    If you <code>await router.post()</code>, the <code>await</code> resolves immediately, before
                                    the server responds. Always use <code>onSuccess</code> and <code>onError</code> callbacks.
                                    This is documented in{' '}
                                    <Link href="/guides/building-saas-with-laravel-12" className="text-primary hover:underline">
                                        the SaaS guide&apos;s frontend section
                                    </Link>{' '}
                                    and is one of the most common Inertia mistakes.
                                </p>
                                <p>
                                    On the server side, the controller validates the request, calls{' '}
                                    <code>BillingService::subscribe()</code>, and handles three outcomes: success (redirect to
                                    dashboard), payment action required (return a Stripe payment page URL for 3D Secure), or error
                                    (return validation errors). The <code>BillingService</code> wraps the Cashier call with a Redis
                                    lock &mdash; which brings us to the next section.
                                </p>

                                {/* Section 4 */}
                                <h2 id="race-conditions">4. Preventing Race Conditions with Redis Locks</h2>
                                <p>
                                    This is the production problem that most billing tutorials skip entirely. Here&apos;s the
                                    scenario: a user double-clicks the &ldquo;Subscribe&rdquo; button. Two HTTP requests hit your
                                    endpoint within milliseconds of each other. Both pass validation. Both call Cashier&apos;s{' '}
                                    <code>newSubscription()-&gt;create()</code>.
                                </p>
                                <p>
                                    Cashier&apos;s <code>create()</code> method is <strong>not idempotent</strong> &mdash; it calls
                                    the Stripe API to create a new subscription every time. Now you have two active subscriptions,
                                    two charges on the customer&apos;s card, and a support ticket. Stripe doesn&apos;t prevent
                                    duplicate subscriptions because from their perspective, you asked for two subscriptions and
                                    they delivered.
                                </p>
                                <p>
                                    The solution is a Redis lock acquired before any Stripe API call. If the lock is already held
                                    (another request is in progress for this user), the second request is rejected immediately with
                                    a clear error message:
                                </p>
                                <pre><code>{`use Illuminate\\Support\\Facades\\Cache;

$lock = Cache::lock("billing:{$user->id}", 35);

if (! $lock->get()) {
    throw new ConcurrentOperationException(
        'A billing operation is already in progress. Please wait and try again.'
    );
}

try {
    $subscription = $user->newSubscription('default', $priceId)
        ->create($paymentMethodId);
} finally {
    $lock->release();
}`}</code></pre>
                                <p>
                                    The 35-second timeout accounts for the maximum expected Stripe API latency. Most Stripe calls
                                    complete in 1&ndash;3 seconds, but under load or during Stripe incidents, latency can spike.
                                    35 seconds gives enough headroom without holding the lock so long that a crashed process blocks
                                    future operations indefinitely. The <code>finally</code> block ensures the lock is always
                                    released, even if the Stripe call throws an exception.
                                </p>
                                <p>
                                    This pattern applies to every subscription mutation, not just creation. Plan swaps, cancellations,
                                    quantity updates, and payment method changes all hit the Stripe API and are all vulnerable to
                                    the same double-request problem. Wrap all of them in the same lock:
                                </p>
                                <pre><code>{`// In BillingService
public function cancel(User $user): void
{
    $lock = Cache::lock("billing:{$user->id}", 35);
    if (! $lock->get()) {
        throw new ConcurrentOperationException();
    }

    try {
        $subscription = $user->subscription('default');
        $subscription->load('owner', 'items.subscription');
        $subscription->cancel();
    } finally {
        $lock->release();
    }
}`}</code></pre>
                                <p>
                                    Note the <code>load(&apos;owner&apos;, &apos;items.subscription&apos;)</code> call. This is
                                    another production gotcha: Cashier internally accesses <code>$subscription-&gt;owner</code> and{' '}
                                    <code>$subscription-&gt;items-&gt;subscription</code> during cancellation. Without eager
                                    loading, each access triggers a lazy query. You&apos;ll get N+1 problems at best, and{' '}
                                    <code>Attempt to read property &quot;stripe_id&quot; on null</code> at worst &mdash; if
                                    the relationship resolver runs during a race condition.
                                </p>
                                <p>
                                    <em>
                                        The{' '}
                                        <Link href="/features/billing" className="text-primary hover:underline">
                                            Laravel React Starter&apos;s billing system
                                        </Link>{' '}
                                        implements this lock pattern on every subscription mutation in{' '}
                                        <code>BillingService</code>. If you&apos;re using the starter, you get this
                                        protection out of the box.
                                    </em>
                                </p>

                                {/* Section 5 */}
                                <h2 id="webhook-handling">5. Webhook Handling &mdash; The Events That Matter</h2>
                                <p>
                                    Stripe communicates subscription state changes via webhooks. Your application needs to handle
                                    four critical events to keep local state in sync with Stripe.
                                </p>

                                <h3 id="payment-succeeded">invoice.payment_succeeded</h3>
                                <p>
                                    Sent when a subscription payment succeeds. Cashier automatically updates the local subscription
                                    status, so your handler only needs to do application-specific work: log the payment for your
                                    audit trail, update any cached billing stats, and optionally send a receipt email. For most
                                    applications, Cashier&apos;s built-in handling is sufficient &mdash; you only need a custom
                                    handler if you have business logic beyond what Cashier does.
                                </p>

                                <h3 id="payment-failed">invoice.payment_failed</h3>
                                <p>
                                    Sent when Stripe attempts a payment and it fails. This is your cue to notify the user.
                                    Cashier marks the subscription as <code>past_due</code>, but it doesn&apos;t send user
                                    notifications &mdash; that&apos;s your responsibility. Queue a notification email with a link
                                    to your billing portal where they can update their payment method:
                                </p>
                                <pre><code>{`// In your StripeWebhookController
protected function handleInvoicePaymentFailed(array $payload): void
{
    $stripeId = $payload['data']['object']['customer'];
    $user = User::where('stripe_id', $stripeId)->first();

    if ($user) {
        $user->notify(new PaymentFailedNotification());
        AuditService::log('billing.payment_failed', $user);
    }
}`}</code></pre>

                                <h3 id="subscription-deleted">customer.subscription.deleted</h3>
                                <p>
                                    Sent when a subscription is cancelled by Stripe &mdash; not by the user. This happens after
                                    the grace period expires or when Stripe gives up retrying failed payments. Cashier handles
                                    the local subscription status update, but you should revoke access to paid features
                                    immediately and log the event. This is distinct from user-initiated cancellation, where
                                    access typically continues until the billing period ends.
                                </p>

                                <h3 id="subscription-updated">customer.subscription.updated</h3>
                                <p>
                                    Sent when the subscription changes: plan swap, quantity change, trial start or end. If you
                                    cache the user&apos;s plan tier locally (for performance), this webhook is where you sync it.
                                    Cashier updates the <code>subscriptions</code> table, but any application-level caching
                                    (dashboard stats, plan limits) needs manual invalidation.
                                </p>

                                <p>
                                    <strong>Webhook security:</strong> Every incoming webhook must be verified before processing.
                                    Stripe signs each webhook payload with your webhook secret. Cashier&apos;s built-in controller
                                    handles this automatically using <code>Webhook::constructEvent($payload, $signature, $secret)</code>.
                                    If signature verification fails, Cashier returns a 400 response and your handler is never called.
                                    Never bypass this verification, even in testing &mdash; use Stripe&apos;s test webhook payloads
                                    instead.
                                </p>

                                {/* Section 6 */}
                                <h2 id="dunning">6. Dunning &mdash; Recovering Failed Payments</h2>
                                <p>
                                    Dunning is the process of recovering failed subscription payments. Stripe has built-in retry
                                    logic (typically 4 attempts over 7 days), but relying solely on Stripe&apos;s emails leaves
                                    money on the table. Stripe&apos;s dunning emails are transactional and generic &mdash; your
                                    own branded emails, with your product&apos;s context and a direct support contact, convert
                                    significantly better.
                                </p>
                                <p>Here&apos;s the complete dunning flow:</p>
                                <ol>
                                    <li>
                                        Stripe attempts payment, it fails, and sends the{' '}
                                        <code>invoice.payment_failed</code> webhook.
                                    </li>
                                    <li>
                                        Your webhook handler marks the subscription as <code>incomplete</code> or{' '}
                                        <code>past_due</code> and logs the event.
                                    </li>
                                    <li>
                                        A scheduled artisan command (<code>subscriptions:check-incomplete</code>) runs every
                                        30 minutes via cron.
                                    </li>
                                    <li>
                                        The command finds subscriptions in failed states and sends reminder emails at the
                                        1-hour and 12-hour marks after the initial failure.
                                    </li>
                                    <li>
                                        Each reminder includes an &ldquo;Update payment method&rdquo; link that takes the user
                                        to the Stripe Customer Portal, where they can fix their card without you building a
                                        payment form.
                                    </li>
                                    <li>
                                        After Stripe&apos;s configured retry period (typically 4 attempts over 7 days), the
                                        subscription is cancelled and the <code>customer.subscription.deleted</code> webhook
                                        fires.
                                    </li>
                                </ol>
                                <p>
                                    The <code>subscriptions:check-incomplete</code> command logic is straightforward: find
                                    subscriptions where <code>stripe_status</code> is <code>incomplete</code> or{' '}
                                    <code>past_due</code> and <code>updated_at</code> falls within the reminder windows
                                    (between 1&ndash;2 hours ago for the first reminder, 11&ndash;13 hours for the second).
                                    This window-based approach is simpler and more reliable than tracking &ldquo;reminder
                                    sent&rdquo; flags:
                                </p>
                                <pre><code>{`// In CheckIncompleteSubscriptions command
$subscriptions = Subscription::whereIn('stripe_status', ['incomplete', 'past_due'])
    ->where(function ($query) {
        // 1-hour reminder window
        $query->whereBetween('updated_at', [
            now()->subHours(2),
            now()->subHour(),
        ])
        // 12-hour reminder window
        ->orWhereBetween('updated_at', [
            now()->subHours(13),
            now()->subHours(11),
        ]);
    })
    ->with('owner')
    ->get();

foreach ($subscriptions as $subscription) {
    $subscription->owner->notify(new IncompletePaymentReminder($subscription));
}`}</code></pre>

                                {/* Section 7 */}
                                <h2 id="testing-billing">7. Testing Billing Code</h2>
                                <p>
                                    Billing code is high-stakes &mdash; bugs mean incorrect charges, lost revenue, or angry
                                    customers. Here&apos;s how to test each layer:
                                </p>
                                <p>
                                    <strong>Mock Stripe responses</strong> using Laravel&apos;s <code>Http::fake()</code> or
                                    Cashier&apos;s test mode. Never hit the real Stripe API in automated tests &mdash; it&apos;s
                                    slow, rate-limited, and creates real (test-mode) data you have to clean up.
                                </p>
                                <p>
                                    <strong>Test the lock collision:</strong> Acquire the Redis lock manually in your test,
                                    then call <code>BillingService::subscribe()</code> and assert it throws{' '}
                                    <code>ConcurrentOperationException</code>. This confirms that concurrent requests are
                                    rejected, not queued:
                                </p>
                                <pre><code>{`it('rejects concurrent subscription creation', function () {
    $user = User::factory()->create();
    $lock = Cache::lock("billing:{$user->id}", 35);
    $lock->get(); // Simulate another request holding the lock

    expect(fn () => app(BillingService::class)->subscribe($user, 'pro', 'pm_test'))
        ->toThrow(ConcurrentOperationException::class);

    $lock->release();
});`}</code></pre>
                                <p>
                                    <strong>Test webhook handlers</strong> by constructing fake signed payloads. Stripe&apos;s
                                    documentation includes test webhook payloads for every event type. In your test, create the
                                    payload, compute the signature using your test webhook secret, and POST it to your webhook
                                    endpoint.
                                </p>
                                <p>
                                    <strong>Database assertions:</strong> After a successful subscription, assert that{' '}
                                    <code>$user-&gt;subscribed()</code> returns <code>true</code> and the{' '}
                                    <code>subscriptions</code> table has exactly one row for that user. After cancellation,
                                    assert the subscription&apos;s <code>ends_at</code> column is set and{' '}
                                    <code>$user-&gt;subscribed()</code> still returns <code>true</code> (grace period).
                                </p>
                                <p>
                                    <em>
                                        The starter ships with billing tests that cover the happy path, concurrent operation
                                        rejection, and webhook handling. Developers using the starter can read these tests as
                                        executable documentation of expected behavior.
                                    </em>
                                </p>

                                {/* Closing */}
                                <hr />
                                <p>
                                    Production billing is more than calling <code>create()</code>. It&apos;s locks to prevent
                                    duplicates, webhooks to stay in sync, dunning to recover revenue, and tests to make sure
                                    nothing breaks when you ship at 2 AM. If you want a head start, the{' '}
                                    <Link href="/features/billing" className="text-primary hover:underline">
                                        pre-built billing implementation
                                    </Link>{' '}
                                    in the Laravel React Starter has all seven of these areas covered and tested.
                                </p>
                            </article>
                        </div>

                        {/* Desktop sidebar ToC */}
                        <aside className="hidden lg:block">
                            <div className="sticky top-8">
                                <TableOfContents sections={sections} />
                            </div>
                        </aside>
                    </div>

                    {/* CTA block */}
                    <div className="mx-auto mt-16 max-w-2xl rounded-lg border bg-muted/50 p-8 text-center">
                        <h2 className="text-2xl font-bold">Skip the Implementation Work</h2>
                        <p className="mt-3 text-muted-foreground">
                            The Laravel React Starter includes production-grade Stripe billing with Redis locks, webhook
                            handling, dunning emails, and comprehensive tests &mdash; all behind a single feature flag.
                        </p>
                        <div className="mt-6 flex flex-wrap items-center justify-center gap-4">
                            <Button size="lg" asChild>
                                <Link href="/features/billing">
                                    See the billing implementation
                                    <ArrowRight className="ml-2 h-4 w-4" />
                                </Link>
                            </Button>
                            <Button variant="outline" size="lg" asChild>
                                <Link href="/pricing">View pricing</Link>
                            </Button>
                        </div>
                    </div>
                </main>

                <footer className="border-t py-8">
                    <div className="container">
                        <div className="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
                            <p className="text-sm text-muted-foreground">
                                &copy; {new Date().getFullYear()}{' '}
                                {import.meta.env.VITE_APP_NAME || 'Laravel React Starter'}.
                                All rights reserved.
                            </p>
                            <nav className="flex items-center gap-4 text-sm text-muted-foreground">
                                <Link
                                    href="/features/billing"
                                    className="transition-colors hover:text-foreground"
                                >
                                    Billing
                                </Link>
                                <Link
                                    href="/features/feature-flags"
                                    className="transition-colors hover:text-foreground"
                                >
                                    Feature Flags
                                </Link>
                                <Link
                                    href="/features/admin-panel"
                                    className="transition-colors hover:text-foreground"
                                >
                                    Admin Panel
                                </Link>
                                <Link
                                    href="/pricing"
                                    className="transition-colors hover:text-foreground"
                                >
                                    Pricing
                                </Link>
                            </nav>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
