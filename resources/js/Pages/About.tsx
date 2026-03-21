import { ArrowRight } from 'lucide-react';

import { useEffect } from 'react';

import { Head, Link } from '@inertiajs/react';

import { PublicFooter } from '@/Components/marketing/PublicFooter';
import { PublicNav } from '@/Components/marketing/PublicNav';
import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';
import { Button } from '@/Components/ui/button';

export default function About() {
    const { track } = useAnalytics();

    useEffect(() => {
        track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'about' });
    }, [track]);

    return (
        <>
            <Head title="About — Laravel React Starter">
                <meta
                    name="description"
                    content="The origin story behind Laravel React Starter — why it exists, what it is, and who it's built for."
                />
            </Head>
            <div className="min-h-screen bg-background">
                <PublicNav />

                <main className="container pb-24">
                    <article className="mx-auto max-w-3xl">
                        <header className="py-16 text-center">
                            <h1 className="text-4xl font-bold tracking-tight sm:text-5xl">
                                About Laravel React Starter
                            </h1>
                            <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                                Why this template exists, what it is, and who it&apos;s built for.
                            </p>
                        </header>

                        {/* Origin story */}
                        <section className="prose prose-neutral dark:prose-invert max-w-none">
                            <h2>Origin story</h2>
                            <p>
                                I built this after the fifth time setting up the same auth + billing +
                                admin scaffold for a new project. Every project started the same way
                                &mdash; two weeks of wiring before any real feature work. Stripe
                                subscriptions, user management, feature flags, an admin panel, CI
                                configuration. The same decisions, the same bugs, the same tests.
                            </p>
                            <p>
                                This template is that setup, done once, tested thoroughly, so you never
                                have to do it again. Every design decision has been made and validated
                                against production workloads. The billing layer handles race conditions.
                                The admin panel runs on the same TypeScript stack as the rest of the app.
                                The test suite covers the paths that matter before you write a single line
                                of your own product code.
                            </p>
                        </section>

                        {/* What it is and is not */}
                        <section className="prose prose-neutral dark:prose-invert max-w-none mt-10">
                            <h2>What it is &mdash; and is not</h2>
                            <p>
                                <strong>This is an opinionated production template.</strong> It makes
                                specific choices: Laravel 12, React 18, TypeScript strict mode, Inertia.js,
                                Tailwind CSS v4, concurrent payment protection, Pest for tests. These choices are
                                deliberate and have been validated together.
                            </p>
                            <p>
                                <strong>It is not a drag-and-drop no-code tool.</strong> It assumes you
                                know Laravel and React. You will read the code, extend it, and make it
                                your own. The value is not that you never have to think &mdash; it&apos;s
                                that you skip the two weeks of infrastructure work and go straight to
                                building your product.
                            </p>
                            <p>
                                <strong>It is not a framework or a library.</strong> You own the source
                                code outright. There is no dependency on a third-party package that can
                                break your build on a bad release day. When Stripe&apos;s API changes or
                                Laravel releases a major version, you decide when and how to update.
                            </p>
                        </section>

                        {/* Positioning */}
                        <section className="prose prose-neutral dark:prose-invert max-w-none mt-10">
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
                        </section>

                        {/* CTA */}
                        <section className="mt-16 rounded-2xl border border-border bg-card p-8 text-center shadow-sm">
                            <h2 className="text-2xl font-bold">Ready to skip the setup?</h2>
                            <p className="mt-2 text-muted-foreground">
                                Auth, billing, admin panel, and 90+ tests &mdash; ready on day one.
                            </p>
                            <div className="mt-6 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                                <Button asChild size="lg">
                                    <Link href="/pricing">
                                        View pricing
                                        <ArrowRight className="ml-2 h-4 w-4" />
                                    </Link>
                                </Button>
                                <Button asChild variant="outline" size="lg">
                                    <Link href="/">Read the docs</Link>
                                </Button>
                            </div>
                        </section>
                    </article>
                </main>

                <PublicFooter />
            </div>
        </>
    );
}
