import { ArrowLeft } from 'lucide-react';

import { useEffect } from 'react';

import { Head, Link } from '@inertiajs/react';

import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';

interface ChangelogEntry {
  version: string;
  date: string;
  title: string;
  description: string;
  type: 'feature' | 'fix' | 'improvement';
}

interface ChangelogProps {
  entries: ChangelogEntry[];
}

const typeBadgeVariant: Record<
  ChangelogEntry['type'],
  'default' | 'secondary' | 'outline'
> = {
  feature: 'default',
  fix: 'secondary',
  improvement: 'outline',
};

const typeLabel: Record<ChangelogEntry['type'], string> = {
  feature: 'Feature',
  fix: 'Fix',
  improvement: 'Improvement',
};

export default function Changelog({ entries }: ChangelogProps) {
  const { track } = useAnalytics();

  useEffect(() => {
    track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'changelog' });
  }, [track]);

  return (
    <>
      <Head title="Changelog" />
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
              <CardTitle className="text-2xl">Changelog</CardTitle>
              <p className="text-sm text-muted-foreground">
                Stay up to date with the latest changes and improvements.
              </p>
            </CardHeader>
            <CardContent>
              {entries.length === 0 ? (
                <p className="text-center text-muted-foreground py-8">
                  No changelog entries yet. Check back soon!
                </p>
              ) : (
                <div className="space-y-6">
                  {entries.map((entry, index) => (
                    <div
                      key={`${entry.version}-${index}`}
                      className="border-b pb-6 last:border-0 last:pb-0"
                    >
                      <div className="flex items-center gap-3 mb-2">
                        <Badge variant={typeBadgeVariant[entry.type]}>
                          {typeLabel[entry.type]}
                        </Badge>
                        <span className="text-sm font-mono text-muted-foreground">
                          v{entry.version}
                        </span>
                        <span className="text-sm text-muted-foreground">
                          {entry.date}
                        </span>
                      </div>
                      <h3 className="text-lg font-semibold">{entry.title}</h3>
                      <p className="mt-1 text-sm text-muted-foreground">
                        {entry.description}
                      </p>
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>
        </div>
      </div>
    </>
  );
}
