import {
    ArrowRight,
    CreditCard,
    Database,
    Lock,
    Mail,
    Settings,
    Shield,
    Users,
} from 'lucide-react';

import { useEffect } from 'react';

import { Head, Link } from '@inertiajs/react';

import { BreadcrumbJsonLd } from '@/Components/seo/BreadcrumbJsonLd';
import { PublicFooter } from '@/Components/marketing/PublicFooter';
import { PublicNav } from '@/Components/marketing/PublicNav';
import { FaqJsonLd } from '@/Components/seo/FaqJsonLd';
import { RelatedContent } from '@/Components/seo/RelatedContent';
import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';
import { Button } from '@/Components/ui/button';
import type { FeaturePageProps } from '@/types/index';

const billingFeatures = [
    {
        icon: Lock,
        title: 'Redis-locked mutations',
        description:
            'Every subscribe, cancel, resume, and swap operation acquires a Redis lock (35-second timeout). Concurrent requests are rejected with a clear error, not a double-charge.',
    },
    {
        icon: CreditCard,
        title: '4 plan tiers',
        description:
            'Free, Pro, Team (3–50 seats), Enterprise (custom pricing). Plan definitions live in config/plans.php — change a price in one place, it propagates everywhere.',
    },
    {
        icon: Users,
        title: 'Team seat billing',
        description:
            'Team plans enforce min/max seat counts. The UI for seat quantity management is included — no building a custom seats form.',
    },
    {
        icon: Mail,
        title: 'Dunning emails',
        description:
            'The subscriptions:check-incomplete artisan command finds failed payments and sends reminder emails at 1h and 12h intervals. Run it via cron — it\u2019s already written.',
    },
    {
        icon: Shield,
        title: 'Incomplete payment recovery',
        description:
            'Stripe\u2019s PaymentIntent flow handled. Customers with incomplete subscriptions are detected, notified, and guided through payment completion.',
    },
    {
        icon: Database,
        title: 'Admin billing dashboard',
        description:
            'See subscription counts, MRR, plan distribution, and individual subscription history without leaving the app.',
    },
];

const faqs = [
    {
        question: 'Does this work with Stripe Tax?',
        answer: 'Yes — BillingService passes through any Stripe subscription options. Add tax_rates to the subscription options in config/plans.php.',
    },
    {
        question: 'Can I add metered billing?',
        answer: 'Yes — Stripe\u2019s metered billing is a subscription item type. Add a metered price ID to the plan config and update updateQuantity() in BillingService.',
    },
    {
        question: 'What happens if Redis is down?',
        answer: 'The BillingService catches lock acquisition failures and throws ConcurrentOperationException, which returns a user-facing error. Billing never silently double-processes.',
    },
];

export default function Billing({ title, metaDescription, breadcrumbs, canonicalUrl, ogImage, canRegister }: FeaturePageProps) {
    const { track } = useAnalytics();

    useEffect(() => {
        track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'features-billing' });
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
                <PublicNav currentPath="/features/billing" />

                <main className="container pb-24">
                    <article className="mx-auto max-w-4xl">
                        <header className="py-16 text-center">
                            <h1 className="text-4xl font-bold tracking-tight sm:text-5xl">
                                Production-Grade Stripe Billing, Without the Complexity
                            </h1>
                            <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                                Most Laravel billing tutorials show you how to call{' '}
                                <code className="rounded bg-muted px-1.5 py-0.5 text-sm">
                                    $user-&gt;newSubscription()-&gt;create()
                                </code>
                                . What they don&apos;t show: what happens when two requests hit
                                that endpoint simultaneously. Or when a payment is declined at
                                2am. Or when a customer tries to downgrade from team to solo
                                mid-cycle. This SaaS starter kit has solved all of those problems and
                                ships the solutions as working, tested code.
                            </p>
                        </header>

                        <section className="mb-16">
                            <h2 className="mb-8 text-center text-3xl font-bold">
                                What&apos;s already built
                            </h2>
                            <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                                {billingFeatures.map((feature) => (
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

                        {/* Persona section */}
                        <section className="mb-16">
                            <h2 className="mb-6 text-2xl font-bold">Who uses this</h2>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="rounded-2xl border border-border/70 bg-muted/40 p-6">
                                    <p className="font-semibold text-foreground">Solo founder launching a SaaS</p>
                                    <p className="mt-2 text-sm text-muted-foreground">
                                        Charge customers on day one without building a billing system from
                                        scratch — Redis locks, dunning emails, and plan tiers are already wired up.
                                    </p>
                                </div>
                                <div className="rounded-2xl border border-border/70 bg-muted/40 p-6">
                                    <p className="font-semibold text-foreground">Freelancer delivering client projects</p>
                                    <p className="mt-2 text-sm text-muted-foreground">
                                        Hand off a production-ready billing setup without teaching your client
                                        Stripe — the billing portal, incomplete payment recovery, and admin
                                        dashboard are all included.
                                    </p>
                                </div>
                            </div>
                        </section>

                        <section className="prose prose-neutral dark:prose-invert mb-16 max-w-none">
                            <h2>How it&apos;s tested</h2>
                            <p>
                                Billing mutations are tested with Stripe mock responses, so
                                the test suite runs without a live Stripe account. Concurrent
                                operation rejection is tested with a simulated lock collision
                                — the test acquires a Redis lock before calling the billing
                                endpoint, asserting that the second request is rejected with a
                                clear error message. Admin billing stats are tested with
                                seeded subscription data and cache invalidation assertions
                                that verify stale data is never served after a mutation.
                            </p>
                        </section>

                        <section className="prose prose-neutral dark:prose-invert mb-16 max-w-none">
                            <h2>Code you control</h2>
                            <p>
                                Unlike managed billing services, every line of billing code
                                lives in{' '}
                                <code>app/Services/BillingService.php</code>,{' '}
                                <code>app/Http/Controllers/Billing/</code>, and{' '}
                                <code>resources/js/Pages/Billing/</code>. You can read it,
                                audit it, and extend it. There is no black box — no SDK that
                                hides the Stripe API calls behind an abstraction you
                                can&apos;t inspect. When Stripe changes their API or you need
                                custom proration logic, you change one file, not fight a
                                framework. The billing service is approximately 200 lines of
                                PHP. It uses Redis locks, database transactions, and
                                Laravel Cashier. That&apos;s it.
                            </p>
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

                        <section className="prose prose-neutral dark:prose-invert mb-16 max-w-none">
                            <h2>How billing and webhooks work together</h2>
                            <p>
                                Stripe communicates subscription events (payment failed, subscription updated,
                                invoice finalized) via webhooks. The billing system relies on incoming Stripe
                                webhooks for dunning and incomplete payment recovery. If you&apos;re building
                                outgoing webhooks to notify your own customers of subscription changes, see the{' '}
                                <Link href="/guides/laravel-webhook-implementation" className="text-primary hover:underline">
                                    Laravel webhook implementation guide
                                </Link>{' '}
                                for HMAC signing, queue-based retry, and delivery tracking patterns.
                            </p>
                        </section>

                        <section className="flex flex-wrap items-center justify-center gap-4 border-t pt-12">
                            {canRegister && (
                                <Button
                                    size="lg"
                                    asChild
                                    onClick={() =>
                                        track(AnalyticsEvents.ENGAGEMENT_CTA_CLICKED, {
                                            source: 'feature_page_register',
                                            page: 'billing',
                                        })
                                    }
                                >
                                    <Link href="/register">
                                        Get the Starter Kit
                                        <ArrowRight className="ml-2 h-4 w-4" />
                                    </Link>
                                </Button>
                            )}
                            <Button variant="outline" size="lg" asChild>
                                <Link href="/pricing">
                                    See pricing
                                </Link>
                            </Button>
                            <Button variant="ghost" size="lg" asChild>
                                <Link href="/">Back to overview</Link>
                            </Button>
                        </section>

                        <RelatedContent
                            items={[
                                {
                                    title: 'Laravel Stripe Billing Tutorial',
                                    href: '/guides/laravel-stripe-billing-tutorial',
                                    description: 'Subscriptions, webhooks, and race condition prevention',
                                },
                                {
                                    title: 'Compare vs Laravel Spark',
                                    href: '/compare/laravel-spark',
                                    description: 'One-time vs $99/year — full feature comparison',
                                },
                                {
                                    title: 'Feature Flags',
                                    href: '/features/feature-flags',
                                    description: 'Toggle billing and other features with one env var',
                                },
                            ]}
                        />
                    </article>
                </main>

                <PublicFooter />
            </div>
        </>
    );
}
