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

import { Head, Link } from '@inertiajs/react';

import { Logo, TextLogo } from '@/Components/branding/Logo';
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

export default function Billing({ title, metaDescription }: FeaturePageProps) {
    return (
        <>
            <Head title={title}>
                <meta name="description" content={metaDescription} />
                <meta property="og:title" content={title} />
                <meta property="og:description" content={metaDescription} />
                <meta property="og:type" content="website" />
                <meta name="twitter:card" content="summary_large_image" />
                <meta name="twitter:title" content={title} />
                <meta name="twitter:description" content={metaDescription} />
            </Head>

            <div className="min-h-screen bg-background">
                <nav className="container flex items-center justify-between py-6">
                    <Link href="/" className="flex items-center gap-2">
                        <Logo className="h-8 w-8" />
                        <TextLogo className="text-xl font-bold" />
                    </Link>
                    <div className="flex items-center gap-4">
                        <Link
                            href="/features/feature-flags"
                            className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                        >
                            Feature Flags
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
                                mid-cycle. This starter has solved all of those problems and
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

                        <section className="flex flex-wrap items-center justify-center gap-4 border-t pt-12">
                            <Button size="lg" asChild>
                                <Link href="/pricing">
                                    See pricing
                                    <ArrowRight className="ml-2 h-4 w-4" />
                                </Link>
                            </Button>
                            <Button variant="outline" size="lg" asChild>
                                <Link href="/">Back to overview</Link>
                            </Button>
                        </section>
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
