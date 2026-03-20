import { ArrowLeft } from 'lucide-react';

import { useEffect } from 'react';

import { Head, Link } from '@inertiajs/react';

import { TermsContent } from '@/Components/legal/LegalContent';
import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';

export default function Terms() {
  const { track } = useAnalytics();

  useEffect(() => {
    track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'legal-terms' });
  }, [track]);

  return (
    <>
      <Head title="Terms of Service">
        <meta
          name="description"
          content="Terms of service — the agreement governing your use of the platform."
        />
      </Head>
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
              <CardTitle className="text-2xl">Terms of Service</CardTitle>
            </CardHeader>
            <CardContent>
              <TermsContent />
            </CardContent>
          </Card>
        </div>
      </div>
    </>
  );
}
