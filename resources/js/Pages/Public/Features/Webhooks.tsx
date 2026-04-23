import {
    ArrowRight,
    CheckCircle,
    Clock,
    Code,
    Database,
    RefreshCw,
    Shield,
    Users,
    Zap,
} from 'lucide-react';

import { useEffect } from 'react';

import { Head, Link } from '@inertiajs/react';

import { Logo, TextLogo } from '@/Components/branding/Logo';
import { BreadcrumbJsonLd } from '@/Components/seo/BreadcrumbJsonLd';
import { FaqJsonLd } from '@/Components/seo/FaqJsonLd';
import { RelatedContent } from '@/Components/seo/RelatedContent';
import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';
import { Button } from '@/Components/ui/button';
import type { FeaturePageProps } from '@/types/index';

const webhookFeatures = [
    {
        icon: Shield,
        title: 'HMAC-SHA256 signing',
        description:
            'Every outgoing payload is signed with the endpoint\'s secret key using HMAC-SHA256. Recipients can verify authenticity with a single hash_equals() check.',
    },
    {
        icon: RefreshCw,
        title: 'Retry with exponential backoff',
        description:
            'Failed webhook deliveries retry automatically via the queue system. Transient failures (timeouts, 5xx responses) are retried without manual intervention.',
    },
    {
        icon: Database,
        title: 'Delivery tracking dashboard',
        description:
            'Every delivery attempt is recorded: status, HTTP response code, response body, and attempt count. Customers can see exactly what happened to each webhook call.',
    },
    {
        icon: CheckCircle,
        title: 'Incoming webhook verification',
        description:
            'Incoming webhooks from GitHub and Stripe are verified before processing. Invalid signatures are rejected before any application logic runs.',
    },
    {
        icon: Clock,
        title: 'Scheduled stale cleanup',
        description:
            'The webhooks:prune-stale artisan command marks orphaned delivery records as abandoned. Run it via cron to keep the webhook_deliveries table clean.',
    },
    {
        icon: Users,
        title: 'Per-user endpoint management',
        description:
            'Users add, update, and delete their own webhook endpoints from the settings UI. Endpoint secrets are generated automatically and displayed once on creation.',
    },
    {
        icon: Zap,
        title: 'Queue-based delivery',
        description:
            'Webhook delivery never blocks the request lifecycle. The WebhookService dispatches a DispatchWebhookJob and returns immediately — the queue handles delivery.',
    },
    {
        icon: Code,
        title: 'Rate limiting pre-configured',
        description:
            'Webhook endpoints are rate-limited to 30 requests per minute — already wired up in AppServiceProvider. No manual throttle configuration needed.',
    },
];

const faqs = [
    {
        question: 'Does Laravel React Starter support receiving Stripe webhooks?',
        answer: 'Yes — incoming Stripe webhooks are verified using Cashier\'s built-in signature verification. The Stripe webhook route is excluded from CSRF middleware since Cashier validates the Stripe signature internally.',
    },
    {
        question: 'What happens if a webhook delivery fails?',
        answer: 'The DispatchWebhookJob retries automatically with exponential backoff via the Laravel queue system. The delivery status and response are tracked in the webhook_deliveries table so you can diagnose failures.',
    },
    {
        question: 'Can I add my own webhook events?',
        answer: 'Yes — dispatch any event through WebhookService with a custom payload. WebhookService handles signing and dispatching DispatchWebhookJob. Add the event type to your WebhookEndpoint filter logic and the payload is delivered to all matching endpoint subscriptions.',
    },
];

export default function Webhooks({ title, metaDescription, breadcrumbs, canonicalUrl, ogImage, canRegister }: FeaturePageProps) {
    const { track } = useAnalytics();

    useEffect(() => {
        track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'features-webhooks' });
    }, [track]);

    return (
        <>
            <Head title={title}>
                <meta name="description" content={metaDescription} />
                <meta property="og:title" content={title} />
                <meta property="og:description" content={metaDescription} />
                <meta property="og:type" content="website" />
                {ogImage && <meta property="og:image" content={ogImage} />}
                <meta name="twitter:card" content="summary_large_image" />
                <meta name="twitter:title" content={title} />
                <meta name="twitter:description" content={metaDescription} />
                {ogImage && <meta name="twitter:image" content={ogImage} />}
                {canonicalUrl && <link rel="canonical" href={canonicalUrl} />}
                {breadcrumbs && <BreadcrumbJsonLd breadcrumbs={breadcrumbs} />}
                <FaqJsonLd questions={faqs} />
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
                            href="/features/admin-panel"
                            className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                        >
                            Admin Panel
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
                    <article className="mx-auto max-w-4xl">
                        <header className="py-16 text-center">
                            <h1 className="text-4xl font-bold tracking-tight sm:text-5xl">
                                Production-Grade Webhooks for Laravel SaaS
                            </h1>
                            <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                                Laravel React Starter ships a complete webhook system: HMAC-SHA256 signed
                                outgoing webhooks, incoming webhook verification for GitHub and Stripe, async
                                delivery via queue jobs, and a delivery tracking dashboard. No third-party
                                webhook service needed.
                            </p>
                        </header>

                        <section className="mb-16">
                            <h2 className="mb-6 text-3xl font-bold">
                                The Problem — Manual Integrations Don&apos;t Scale
                            </h2>
                            <div className="rounded-2xl border border-border/70 bg-card p-8">
                                <ul className="space-y-3 text-muted-foreground">
                                    <li className="flex items-start gap-3">
                                        <span className="mt-1 h-2 w-2 shrink-0 rounded-full bg-destructive/70" />
                                        <span>
                                            Customers need to automate workflows — Zapier, n8n, Make, and custom
                                            integrations all require webhooks to trigger on real-time events
                                        </span>
                                    </li>
                                    <li className="flex items-start gap-3">
                                        <span className="mt-1 h-2 w-2 shrink-0 rounded-full bg-destructive/70" />
                                        <span>
                                            Manual export/import cycles are brittle — polling-based integrations
                                            introduce latency, miss updates, and break when APIs change
                                        </span>
                                    </li>
                                    <li className="flex items-start gap-3">
                                        <span className="mt-1 h-2 w-2 shrink-0 rounded-full bg-destructive/70" />
                                        <span>
                                            Enterprise buyers treat webhook support as a table-stakes requirement —
                                            no webhooks means no integration story, which means no deal
                                        </span>
                                    </li>
                                    <li className="flex items-start gap-3">
                                        <span className="mt-1 h-2 w-2 shrink-0 rounded-full bg-destructive/70" />
                                        <span>
                                            Building a secure, reliable webhook system from scratch takes 2–3 weeks:
                                            HMAC signing, retry logic, delivery tracking, UI — it&apos;s all boilerplate
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </section>

                        <section className="mb-16">
                            <h2 className="mb-6 text-3xl font-bold">
                                The Solution — Built-In Webhooks Out of the Box
                            </h2>
                            <div className="prose prose-neutral dark:prose-invert max-w-none">
                                <p>
                                    Enable the webhook system with a single environment variable:
                                </p>
                                <pre className="rounded-xl bg-muted px-6 py-4 text-sm"><code>FEATURE_WEBHOOKS=true</code></pre>
                                <p>
                                    <strong>Outgoing webhooks:</strong> Users create webhook endpoints in the
                                    settings UI. Every event your app dispatches through{' '}
                                    <code>WebhookService</code> is automatically signed with HMAC-SHA256, queued
                                    for async delivery, and tracked in the <code>webhook_deliveries</code> table.
                                    Failed deliveries retry automatically.
                                </p>
                                <p>
                                    <strong>Incoming webhooks:</strong> GitHub and Stripe events are verified
                                    before processing via the <code>VerifyWebhookSignature</code> middleware.
                                    Requests with invalid signatures are rejected before any application code runs
                                    — no fraudulent events processed.
                                </p>
                                <p>
                                    <strong>Queue-based delivery:</strong> Webhook dispatch never blocks the
                                    request lifecycle. <code>WebhookService</code> fires{' '}
                                    <code>DispatchWebhookJob</code> and returns immediately. The queue worker
                                    handles HTTP delivery, retries, and status recording.
                                </p>
                            </div>
                        </section>

                        <section className="mb-16">
                            <h2 className="mb-8 text-3xl font-bold">Key Features</h2>
                            <div className="grid gap-6 md:grid-cols-2">
                                {webhookFeatures.map((feature) => (
                                    <div
                                        key={feature.title}
                                        className="rounded-2xl border border-border/70 bg-card p-6 text-card-foreground shadow-sm"
                                    >
                                        <div className="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                                            <feature.icon
                                                className="h-5 w-5 text-primary"
                                                aria-hidden="true"
                                            />
                                        </div>
                                        <h3 className="mb-2 text-lg font-semibold">
                                            {feature.title}
                                        </h3>
                                        <p className="text-sm text-muted-foreground">
                                            {feature.description}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        </section>

                        <section className="mb-16">
                            <h2 className="mb-8 text-3xl font-bold">How It Works</h2>
                            <div className="space-y-6">
                                <div className="flex gap-6 rounded-2xl border border-border/70 bg-card p-6">
                                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-lg font-bold text-primary">
                                        1
                                    </div>
                                    <div>
                                        <h3 className="mb-1 font-semibold">User adds a webhook endpoint</h3>
                                        <p className="text-sm text-muted-foreground">
                                            From <code>/settings/webhooks</code>, users enter a destination URL and
                                            select which events to subscribe to. A secret key is generated and
                                            displayed once — stored hashed, never retrievable in plaintext.
                                        </p>
                                    </div>
                                </div>
                                <div className="flex gap-6 rounded-2xl border border-border/70 bg-card p-6">
                                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-lg font-bold text-primary">
                                        2
                                    </div>
                                    <div>
                                        <h3 className="mb-1 font-semibold">Your app dispatches an event</h3>
                                        <p className="text-sm text-muted-foreground">
                                            Call <code>WebhookService::dispatch()</code> with an event type and
                                            payload. The service looks up active endpoints subscribed to that event
                                            and dispatches a <code>DispatchWebhookJob</code> for each one.
                                        </p>
                                        <pre className="mt-3 rounded-lg bg-muted px-4 py-3 text-xs"><code>{`// In a controller, job, or event listener:
$webhookService->dispatch('subscription.created', [
    'subscription_id' => $subscription->id,
    'plan'            => $subscription->name,
    'started_at'      => $subscription->created_at,
]);`}</code></pre>
                                    </div>
                                </div>
                                <div className="flex gap-6 rounded-2xl border border-border/70 bg-card p-6">
                                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-lg font-bold text-primary">
                                        3
                                    </div>
                                    <div>
                                        <h3 className="mb-1 font-semibold">Job signs, delivers, and records</h3>
                                        <p className="text-sm text-muted-foreground">
                                            <code>DispatchWebhookJob</code> signs the payload with the endpoint&apos;s
                                            secret using HMAC-SHA256, sends the HTTP request with an{' '}
                                            <code>X-Webhook-Signature: sha256=...</code> header, and records the
                                            response status, body, and attempt count in <code>webhook_deliveries</code>.
                                            On failure, the job retries with exponential backoff.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section className="mb-16">
                            <h2 className="mb-8 text-3xl font-bold">Common questions</h2>
                            <div className="space-y-6">
                                {faqs.map((faq) => (
                                    <div
                                        key={faq.question}
                                        className="rounded-2xl border border-border/70 bg-card p-6"
                                    >
                                        <h3 className="mb-2 text-lg font-semibold">
                                            {faq.question}
                                        </h3>
                                        <p className="text-sm text-muted-foreground">
                                            {faq.answer}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        </section>

                        <section className="flex flex-wrap items-center justify-center gap-4 border-t pt-12">
                            {canRegister && (
                                <Button
                                    size="lg"
                                    asChild
                                    onClick={() =>
                                        track(AnalyticsEvents.ENGAGEMENT_CTA_CLICKED, {
                                            source: 'feature_page_register',
                                            page: 'webhooks',
                                        })
                                    }
                                >
                                    <Link href="/register">
                                        Start Building Free
                                        <ArrowRight className="ml-2 h-4 w-4" />
                                    </Link>
                                </Button>
                            )}
                            <Button variant="outline" size="lg" asChild>
                                <Link href="/pricing">
                                    View pricing
                                </Link>
                            </Button>
                            <Button variant="ghost" size="lg" asChild>
                                <Link href="/">Back to overview</Link>
                            </Button>
                        </section>

                        <RelatedContent
                            items={[
                                {
                                    title: 'Laravel Webhook Implementation Guide',
                                    href: '/guides/laravel-webhook-implementation',
                                    description: 'HMAC signing, retry logic, and delivery tracking patterns',
                                },
                                {
                                    title: 'Stripe Billing',
                                    href: '/features/billing',
                                    description: 'Incoming Stripe webhooks power payment events and dunning',
                                },
                                {
                                    title: 'Laravel SaaS Architecture Guide',
                                    href: '/guides/building-saas-with-laravel-12',
                                    description: 'Full overview: auth, billing, feature flags, and testing',
                                },
                            ]}
                        />
                    </article>
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
