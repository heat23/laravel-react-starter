import { ArrowLeft } from 'lucide-react';

import { useEffect } from 'react';

import { Head, Link } from '@inertiajs/react';

import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';

export default function About() {
    const appName = import.meta.env.VITE_APP_NAME || 'App';
    const { track } = useAnalytics();

    useEffect(() => {
        track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'about' });
    }, [track]);

    return (
        <>
            <Head title="About" />
            <div className="min-h-screen bg-background">
                <div className="container max-w-3xl py-12">
                    <div className="mb-6">
                        <Button variant="ghost" size="sm" asChild>
                            <Link href="/">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Back to Home
                            </Link>
                        </Button>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle className="text-2xl">About {appName}</CardTitle>
                        </CardHeader>
                        <CardContent className="prose prose-neutral dark:prose-invert max-w-none">
                            <p className="text-muted-foreground text-lg mb-4">
                                {appName} is a production-ready Laravel + React starter template
                                built for solo developers and small teams who want to ship fast
                                without cutting corners.
                            </p>

                            <h3 className="text-xl font-semibold text-foreground mt-8 mb-3">
                                Our Mission
                            </h3>
                            <p className="text-muted-foreground">
                                Every developer deserves a solid, tested foundation to build
                                from &mdash; one that handles auth, billing, admin tooling, and
                                DevOps so you can focus on what makes your product unique.
                            </p>

                            <h3 className="text-xl font-semibold text-foreground mt-8 mb-3">
                                Built With
                            </h3>
                            <ul className="list-disc pl-5 text-muted-foreground space-y-1">
                                <li>Laravel 12 + Inertia.js</li>
                                <li>React 18 + TypeScript</li>
                                <li>Tailwind CSS v4</li>
                                <li>Stripe Cashier for billing</li>
                                <li>Pest for testing</li>
                            </ul>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
