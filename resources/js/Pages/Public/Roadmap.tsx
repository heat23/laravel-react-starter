import { ArrowLeft, CheckCircle2, Circle, Loader2, ThumbsUp } from 'lucide-react';

import { useEffect, useState } from 'react';

import { Head, Link, router, usePage } from '@inertiajs/react';

import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';
import { cn } from '@/lib/utils';
import type { PageProps } from '@/types';

interface RoadmapEntry {
  slug: string;
  title: string;
  description: string;
  status: 'planned' | 'in_progress' | 'completed';
  votes: number;
  has_voted: boolean;
}

interface RoadmapProps {
  entries: RoadmapEntry[];
}

const statusConfig: Record<
  RoadmapEntry['status'],
  {
    label: string;
    icon: typeof Circle;
    variant: 'outline' | 'secondary' | 'default';
  }
> = {
  planned: { label: 'Planned', icon: Circle, variant: 'outline' },
  in_progress: { label: 'In Progress', icon: Loader2, variant: 'secondary' },
  completed: { label: 'Completed', icon: CheckCircle2, variant: 'default' },
};

const statusOrder: RoadmapEntry['status'][] = [
  'in_progress',
  'planned',
  'completed',
];

export default function Roadmap({ entries }: RoadmapProps) {
  const { track } = useAnalytics();
  const { auth } = usePage<PageProps>().props;
  const isAuthenticated = !!auth?.user;

  // Local vote state for optimistic updates
  const [voteState, setVoteState] = useState<Record<string, { votes: number; has_voted: boolean }>>(() => {
    const init: Record<string, { votes: number; has_voted: boolean }> = {};
    entries.forEach((e) => {
      init[e.slug] = { votes: e.votes, has_voted: e.has_voted };
    });
    return init;
  });

  useEffect(() => {
    track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'roadmap' });
  }, [track]);

  const handleVote = (slug: string) => {
    if (!isAuthenticated) return;

    const current = voteState[slug] ?? { votes: 0, has_voted: false };
    // Optimistic update
    setVoteState((prev) => ({
      ...prev,
      [slug]: {
        votes: current.has_voted ? current.votes - 1 : current.votes + 1,
        has_voted: !current.has_voted,
      },
    }));

    router.post(
      `/roadmap/${slug}/vote`,
      {},
      {
        preserveState: true,
        preserveScroll: true,
        onError: () => {
          // Revert on error
          setVoteState((prev) => ({ ...prev, [slug]: current }));
        },
      }
    );
  };

  const groupedEntries = statusOrder.reduce(
    (acc, status) => {
      acc[status] = entries.filter((e) => e.status === status);
      return acc;
    },
    {} as Record<RoadmapEntry['status'], RoadmapEntry[]>
  );

  return (
    <>
      <Head title="Roadmap">
        <meta
          name="description"
          content="See what features are planned, in progress, and recently shipped. The public roadmap — updated weekly."
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
              <CardTitle asChild><h1 className="text-2xl font-bold">Roadmap</h1></CardTitle>
              <p className="text-sm text-muted-foreground">
                See what we're working on and what's coming next.
                {isAuthenticated && ' Vote for features you want most.'}
              </p>
            </CardHeader>
            <CardContent>
              {entries.length === 0 ? (
                <p className="text-center text-muted-foreground py-8">
                  No roadmap entries yet. Check back soon!
                </p>
              ) : (
                <div className="space-y-8">
                  {statusOrder.map((status) => {
                    const items = groupedEntries[status];
                    if (items.length === 0) return null;
                    const config = statusConfig[status];
                    const StatusIcon = config.icon;

                    return (
                      <div key={status}>
                        <div className="flex items-center gap-2 mb-4">
                          <StatusIcon className="h-5 w-5 text-muted-foreground" />
                          <h2 className="text-lg font-semibold">
                            {config.label}
                          </h2>
                          <Badge variant={config.variant}>{items.length}</Badge>
                        </div>
                        <div className="space-y-3">
                          {items.map((item) => {
                            const vs = voteState[item.slug] ?? { votes: item.votes, has_voted: item.has_voted };
                            return (
                              <div
                                key={item.slug}
                                className="rounded-lg border p-4 flex items-start justify-between gap-4"
                              >
                                <div className="flex-1 min-w-0">
                                  <h3 className="font-medium">{item.title}</h3>
                                  <p className="mt-1 text-sm text-muted-foreground">
                                    {item.description}
                                  </p>
                                </div>
                                {isAuthenticated && status !== 'completed' && (
                                  <button
                                    onClick={() => handleVote(item.slug)}
                                    aria-label={vs.has_voted ? 'Remove vote' : 'Vote for this feature'}
                                    className={cn(
                                      'flex flex-col items-center gap-0.5 rounded-md border px-2.5 py-1.5 text-xs font-medium transition-colors min-w-[3rem]',
                                      vs.has_voted
                                        ? 'border-primary bg-primary/10 text-primary'
                                        : 'border-border text-muted-foreground hover:border-primary hover:text-primary'
                                    )}
                                  >
                                    <ThumbsUp className="h-3.5 w-3.5" />
                                    {vs.votes}
                                  </button>
                                )}
                                {(!isAuthenticated || status === 'completed') && vs.votes > 0 && (
                                  <span className="flex items-center gap-1 text-xs text-muted-foreground">
                                    <ThumbsUp className="h-3 w-3" />
                                    {vs.votes}
                                  </span>
                                )}
                              </div>
                            );
                          })}
                        </div>
                      </div>
                    );
                  })}
                </div>
              )}
            </CardContent>
          </Card>
        </div>
      </div>
    </>
  );
}
