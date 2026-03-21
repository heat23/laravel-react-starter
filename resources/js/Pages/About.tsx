import { ArrowRight, CheckCircle2, Code2, FlaskConical, Lock, Layers } from 'lucide-react';

import { useEffect } from 'react';

import { Head, Link } from '@inertiajs/react';

import { PublicFooter } from '@/Components/marketing/PublicFooter';
import { PublicNav } from '@/Components/marketing/PublicNav';
import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';
import { Button } from '@/Components/ui/button';

const APP_NAME = import.meta.env.VITE_APP_NAME || 'Laravel React Starter';

const stats = [
    { value: '11', label: 'Feature flags', description: 'Every subsystem is independently toggleable' },
    { value: '90+', label: 'Tests', description: 'Pest, Vitest, and Playwright coverage' },
    { value: '4', label: 'Billing plans', description: 'Free, Pro, Team (3-50 seats), Enterprise' },
    { value: '1', label: 'Codebase', description: 'Full ownership — no framework lock-in' },
];

const decisions = [
    {
        icon: Layers,
        title: 'Same-stack admin panel',
        body: 'The admin panel runs on the same Laravel + Inertia + React stack as the rest of the app. No Filament, no second framework to learn, no separate dependency tree to maintain.',
    },
    {
        icon: CheckCircle2,
        title: 'Feature flags with DB overrides',
        body: 'Flags live in config files but can be overridden per-user or globally via database rows — without a deploy. Ship to a subset of users, run A/B tests, or kill a feature in production instantly.',
    },
    {
        icon: Lock,
        title: 'Redis locks for billing safety',
        body: 'Every subscription mutation holds a Redis lock for 35 seconds. Concurrent Stripe API calls are rejected with a clear error rather than silently producing inconsistent state.',
    },
    {
        icon: FlaskConical,
        title: 'Pest + Vitest + Playwright pyramid',
        body: 'Fast unit tests at the base, feature tests for controller/service integration in the middle, and Playwright smoke tests at the top. Each layer catches a different failure mode.',
    },
    {
        icon: Code2,
        title: 'TypeScript strict mode throughout',
        body: 'Every Inertia page, component, and hook is fully typed. Shared props flow from PHP through Inertia to TypeScript types — type errors surface at build time, not in production.',
    },
];

export default function About() {
    const { track } = useAnalytics();

    useEffect(() => {
        track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'about' });
    }, [track]);

    return (
        <>
            <Head title={`About — ${APP_NAME}`}>
                <meta
                    name="description"
                    content={`The origin story behind ${APP_NAME} — why it exists, what it is, and who it's built for.`}
                />
                <meta property="og:title" content={`About — ${APP_NAME}`} />
                <meta
                    property="og:description"
                    content="Production-ready Laravel 12 + React 18 SaaS starter. Auth, billing, admin panel, and 90+ tests — ready on day one."
                />
                <meta property="og:type" content="website" />
            </Head>

            <div className="min-h-screen bg-background">
                <PublicNav />

                <main>
                    {/* Hero */}
                    <section className="container py-20 text-center">
                        <div className="mx-auto max-w-3xl">
                            <h1 className="text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl">
                                Built by developers,{' '}
                                <span className="text-primary">for developers who ship</span>
                            </h1>
                            <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                                {APP_NAME} is the setup you write before the real work starts — done
                                once, tested thoroughly, so you never have to do it again.
                            </p>
                        </div>
                    </section>

                    {/* Origin story */}
                    <section className="container pb-16">
                        <article className="mx-auto max-w-3xl prose prose-neutral dark:prose-invert">
                            <h2>Origin story</h2>
                            <p>
                                I built this after the fifth time setting up the same auth + billing +
                                admin scaffold for a new project. Every project started the same way —
                                two weeks of wiring before any real feature work. Stripe subscriptions,
                                user management, feature flags, an admin panel, CI configuration. The
                                same decisions, the same bugs, the same tests.
                            </p>
                            <p>
                                This template is that setup, done once, tested thoroughly, so you never
                                have to do it again. Every design decision has been made and validated
                                against production workloads. The billing layer handles race conditions.
                                The admin panel runs on the same TypeScript stack as the rest of the app.
                                The test suite covers the paths that matter before you write a single line
                                of your own product code.
                            </p>
                        </article>
                    </section>

                    {/* By the Numbers */}
                    <section className="border-y bg-muted/40 py-16">
                        <div className="container">
                            <div className="mx-auto max-w-3xl text-center">
                                <h2 className="text-2xl font-bold sm:text-3xl">By the numbers</h2>
                                <p className="mt-2 text-muted-foreground">
                                    Concrete signals that this is a complete, production-hardened foundation.
                                </p>
                            </div>
                            <dl className="mx-auto mt-10 grid max-w-4xl gap-6 sm:grid-cols-2 lg:grid-cols-4">
                                {stats.map(({ value, label, description }) => (
                                    <div
                                        key={label}
                                        className="rounded-xl border bg-card p-6 text-center shadow-sm"
                                    >
                                        <dt className="text-4xl font-extrabold text-primary">{value}</dt>
                                        <dd className="mt-1 text-sm font-semibold">{label}</dd>
                                        <p className="mt-2 text-xs text-muted-foreground">{description}</p>
                                    </div>
                                ))}
                            </dl>
                        </div>
                    </section>

                    {/* Philosophy */}
                    <section className="container py-16">
                        <div className="mx-auto max-w-3xl">
                            <h2 className="text-2xl font-bold sm:text-3xl">The philosophy</h2>
                            <div className="mt-8 space-y-8 prose prose-neutral dark:prose-invert max-w-none">
                                <div>
                                    <h3>Why Laravel + React (not Livewire)</h3>
                                    <p>
                                        Livewire is excellent for teams that want to stay entirely in PHP.
                                        But for products that need a rich, interactive frontend — real-time
                                        updates, complex state, component reuse across a mobile app or API
                                        consumer — you eventually reach for a JavaScript framework anyway.
                                        This template starts there. React gives you the full component
                                        ecosystem from day one, and Inertia keeps the PHP routing and
                                        server-side validation you already know.
                                    </p>
                                </div>
                                <div>
                                    <h3>Why feature flags matter</h3>
                                    <p>
                                        Flags decouple deployment from release. You can merge billing
                                        infrastructure to main, ship it to production, and turn it on for
                                        one user at a time. When something breaks in production, you kill
                                        the flag — no rollback, no hotfix branch. The 11 flags in this
                                        template aren&apos;t toggle switches for demo purposes; they are
                                        the architecture that lets a solo developer ship safely.
                                    </p>
                                </div>
                                <div>
                                    <h3>Why tests are non-negotiable</h3>
                                    <p>
                                        A starter template without tests is a liability transfer. You
                                        inherit the code, and now you own the bugs you can&apos;t see. The
                                        90+ tests in this template are not a metric to brag about — they
                                        are the contract that says &ldquo;this works as described.&rdquo;
                                        When you extend a feature, you can run the suite, see green, and
                                        ship with confidence. That is the only way a solo founder operates
                                        safely at speed.
                                    </p>
                                </div>
                                <div>
                                    <h3>Why Redis-locked billing</h3>
                                    <p>
                                        Stripe&apos;s API is not idempotent in the way you want it to be
                                        for subscriptions. A double-click on a &ldquo;Cancel subscription&rdquo;
                                        button, a retry after a network timeout, two browser tabs open
                                        simultaneously — all of these can create duplicate API calls that
                                        leave your subscription state inconsistent. Redis locks eliminate
                                        the race condition entirely. The lock is held for 35 seconds, any
                                        concurrent request fails fast with a clear error, and your data
                                        stays clean.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    {/* Technical Decisions */}
                    <section className="border-t bg-muted/40 py-16">
                        <div className="container">
                            <div className="mx-auto max-w-3xl text-center">
                                <h2 className="text-2xl font-bold sm:text-3xl">Key technical decisions</h2>
                                <p className="mt-2 text-muted-foreground">
                                    Every architectural choice has a reason. Here are the most consequential ones.
                                </p>
                            </div>
                            <ul className="mx-auto mt-10 grid max-w-4xl gap-6 sm:grid-cols-2 lg:grid-cols-3">
                                {decisions.map(({ icon: Icon, title, body }) => (
                                    <li
                                        key={title}
                                        className="rounded-xl border bg-card p-6 shadow-sm"
                                    >
                                        <div className="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                                            <Icon className="h-5 w-5 text-primary" aria-hidden="true" />
                                        </div>
                                        <h3 className="font-semibold">{title}</h3>
                                        <p className="mt-2 text-sm text-muted-foreground">{body}</p>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </section>

                    {/* Who it's for */}
                    <section className="container py-16">
                        <div className="mx-auto max-w-3xl prose prose-neutral dark:prose-invert">
                            <h2>Built for</h2>
                            <p className="text-lg">
                                Developers who value their time more than they value the learning
                                experience of rebuilding auth for the fourth time.
                            </p>
                            <ul>
                                <li>
                                    <strong>Solo founders</strong> who need to ship fast and can&apos;t
                                    afford two weeks of infrastructure work before the first user signs up.
                                </li>
                                <li>
                                    <strong>Small teams</strong> who want a shared, tested foundation
                                    instead of tribal knowledge about why the billing layer works the way
                                    it does.
                                </li>
                                <li>
                                    <strong>Freelancers</strong> who deliver Laravel + React projects and
                                    want a production-ready starting point they can trust across every
                                    engagement.
                                </li>
                            </ul>
                            <h3>What it is not</h3>
                            <p>
                                <strong>Not a drag-and-drop no-code tool.</strong> It assumes you know
                                Laravel and React. You will read the code, extend it, and make it your
                                own. The value is not that you never have to think — it&apos;s that you
                                skip the two weeks of infrastructure work and go straight to building your
                                product.
                            </p>
                            <p>
                                <strong>Not a framework or a library.</strong> You own the source code
                                outright. There is no dependency on a third-party package that can break
                                your build on a bad release day. When Stripe&apos;s API changes or Laravel
                                releases a major version, you decide when and how to update.
                            </p>
                        </div>
                    </section>

                    {/* CTA */}
                    <section className="container pb-24">
                        <div className="mx-auto max-w-3xl rounded-2xl border border-border bg-card p-10 text-center shadow-sm">
                            <h2 className="text-2xl font-bold">Ready to skip the setup?</h2>
                            <p className="mt-2 text-muted-foreground">
                                Auth, billing, admin panel, and 90+ tests — ready on day one.
                            </p>
                            <div className="mt-6 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                                <Button asChild size="lg">
                                    <Link href="/pricing">
                                        View pricing
                                        <ArrowRight className="ml-2 h-4 w-4" aria-hidden="true" />
                                    </Link>
                                </Button>
                                <Button asChild variant="outline" size="lg">
                                    <Link href="/">See the docs</Link>
                                </Button>
                            </div>
                        </div>
                    </section>
                </main>

                <PublicFooter />
            </div>
        </>
    );
}
