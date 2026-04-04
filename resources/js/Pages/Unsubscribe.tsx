import { CheckCircle2 } from 'lucide-react';

import { useEffect } from 'react';

import { Head, Link } from '@inertiajs/react';

import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';

interface UnsubscribeProps {
  email: string;
}

export default function Unsubscribe({ email }: UnsubscribeProps) {
  const { track } = useAnalytics();

  useEffect(() => {
    track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'unsubscribe' });
  }, [track]); // mount-only in practice: track is stable (see useAnalytics)

  return (
    <>
      <Head title="Unsubscribed" />
      <div className="flex min-h-screen items-center justify-center bg-background p-4">
        <Card className="w-full max-w-md text-center">
          <CardHeader>
            <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-success/10">
              <CheckCircle2 className="h-6 w-6 text-success" />
            </div>
            <CardTitle>You've been unsubscribed</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <p className="text-sm text-muted-foreground">
              <span className="font-medium">{email}</span> will no longer receive
              marketing emails. Transactional emails (password resets, billing
              receipts) are unaffected.
            </p>
            <Button asChild variant="outline">
              <Link href="/">Return to home</Link>
            </Button>
          </CardContent>
        </Card>
      </div>
    </>
  );
}
