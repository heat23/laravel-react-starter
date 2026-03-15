import { ArrowLeft, CheckCircle2, Circle, Loader2 } from 'lucide-react';

import { Head, Link } from '@inertiajs/react';

import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';

interface RoadmapEntry {
  title: string;
  description: string;
  status: 'planned' | 'in_progress' | 'completed';
  votes: number;
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
  const groupedEntries = statusOrder.reduce(
    (acc, status) => {
      acc[status] = entries.filter((e) => e.status === status);
      return acc;
    },
    {} as Record<RoadmapEntry['status'], RoadmapEntry[]>
  );

  return (
    <>
      <Head title="Roadmap" />
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
              <CardTitle className="text-2xl">Roadmap</CardTitle>
              <p className="text-sm text-muted-foreground">
                See what we're working on and what's coming next.
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
                          {items.map((item, index) => (
                            <div
                              key={`${status}-${index}`}
                              className="rounded-lg border p-4"
                            >
                              <h3 className="font-medium">{item.title}</h3>
                              <p className="mt-1 text-sm text-muted-foreground">
                                {item.description}
                              </p>
                            </div>
                          ))}
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
