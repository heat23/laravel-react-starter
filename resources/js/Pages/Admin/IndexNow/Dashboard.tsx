import { AlertTriangle, CheckCircle2, Clock, Globe, Zap } from 'lucide-react';

import { Head, Link, router } from '@inertiajs/react';

import {
  AdminStatsGrid,
  type StatCard,
} from '@/Components/admin/AdminStatsGrid';
import PageHeader from '@/Components/layout/PageHeader';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/Components/ui/card';
import { EmptyState } from '@/Components/ui/empty-state';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/Components/ui/select';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table';
import AdminLayout from '@/Layouts/AdminLayout';
import { formatRelativeTime } from '@/lib/format';
import type { AdminIndexNowDashboardProps } from '@/types/admin';

function statusVariant(
  status: string
): 'default' | 'destructive' | 'secondary' {
  if (status === 'success') return 'default';
  if (status === 'failed') return 'destructive';
  return 'secondary';
}

export default function IndexNowDashboard({
  stats,
  submissions,
  triggers,
  filters,
  key_location,
  configured,
}: AdminIndexNowDashboardProps) {
  const applyFilter = (field: 'status' | 'trigger', value: string) => {
    const next = { ...filters, [field]: value || undefined };
    router.get('/admin/indexnow', next, {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    });
  };

  const clearFilters = () => {
    router.get(
      '/admin/indexnow',
      {},
      { preserveScroll: true, replace: true }
    );
  };

  const hasFilters = Object.keys(filters).length > 0;

  return (
    <AdminLayout>
      <Head title="Admin - IndexNow" />
      <PageHeader
        title="IndexNow Submissions"
        subtitle="Search engine instant-indexing activity (Bing, Yandex, Seznam, Naver, Yep)"
      />

      <div className="container py-8 space-y-8">
        {!configured && (
          <Card className="border-destructive/50 bg-destructive/5">
            <CardHeader>
              <CardTitle className="text-base">
                IndexNow is not fully configured
              </CardTitle>
              <CardDescription>
                Set <code>INDEXNOW_API_KEY</code> (and optionally{' '}
                <code>INDEXNOW_HOST</code>) in <code>.env</code>. Generate a key
                with <code>php artisan indexnow:generate-key</code>.
              </CardDescription>
            </CardHeader>
          </Card>
        )}

        <AdminStatsGrid
          stats={
            [
              {
                title: 'Submissions (30d)',
                value: stats.total_submissions_30d,
                icon: Zap,
                description: `${stats.total_urls_30d} URLs pinged`,
              },
              {
                title: 'Successful',
                value: stats.successful_submissions_30d,
                icon: CheckCircle2,
                description: 'HTTP 200/202',
              },
              {
                title: 'Failed',
                value: stats.failed_submissions_30d,
                icon: AlertTriangle,
                description: `${stats.failure_rate}% failure rate`,
                valueClassName:
                  stats.failure_rate > 10 ? 'text-destructive' : undefined,
              },
              {
                title: 'Pending',
                value: stats.pending_submissions_30d,
                icon: Clock,
                description: 'Queued for delivery',
              },
            ] satisfies StatCard[]
          }
        />

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2 text-base">
              <Globe className="h-4 w-4" aria-hidden="true" />
              Key verification file
            </CardTitle>
            <CardDescription>
              Search engines fetch this URL to prove you own the host.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <code
              className="block break-all text-xs bg-muted px-3 py-2 rounded"
              aria-label="IndexNow key location URL"
            >
              {key_location}
            </code>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between gap-4">
            <div>
              <CardTitle>Recent Submissions</CardTitle>
              <CardDescription>
                Each row is one HTTP call to the IndexNow endpoint.
              </CardDescription>
            </div>
            <div className="flex items-center gap-2">
              <Select
                value={filters.status ?? ''}
                onValueChange={(value) =>
                  applyFilter('status', value === 'all' ? '' : value)
                }
              >
                <SelectTrigger
                  className="w-[140px]"
                  aria-label="Filter by status"
                >
                  <SelectValue placeholder="Status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All statuses</SelectItem>
                  <SelectItem value="pending">Pending</SelectItem>
                  <SelectItem value="success">Success</SelectItem>
                  <SelectItem value="failed">Failed</SelectItem>
                </SelectContent>
              </Select>
              {triggers.length > 0 && (
                <Select
                  value={filters.trigger ?? ''}
                  onValueChange={(value) =>
                    applyFilter('trigger', value === 'all' ? '' : value)
                  }
                >
                  <SelectTrigger
                    className="w-[160px]"
                    aria-label="Filter by trigger"
                  >
                    <SelectValue placeholder="Trigger" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All triggers</SelectItem>
                    {triggers.map((trigger) => (
                      <SelectItem key={trigger} value={trigger}>
                        {trigger}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              )}
              {hasFilters && (
                <Button variant="ghost" size="sm" onClick={clearFilters}>
                  Clear
                </Button>
              )}
            </div>
          </CardHeader>
          <CardContent>
            {submissions.data.length === 0 ? (
              <EmptyState
                icon={Zap}
                title="No submissions yet"
                description="IndexNow pings appear here as your app submits URLs."
                size="sm"
              />
            ) : (
              <div className="overflow-x-auto">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>#</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead>URLs</TableHead>
                      <TableHead>Response</TableHead>
                      <TableHead>Trigger</TableHead>
                      <TableHead>Attempts</TableHead>
                      <TableHead>Time</TableHead>
                      <TableHead className="sr-only">Actions</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {submissions.data.map((row) => (
                      <TableRow key={row.id}>
                        <TableCell className="text-xs font-mono">
                          {row.id}
                        </TableCell>
                        <TableCell>
                          <Badge variant={statusVariant(row.status)}>
                            {row.status}
                          </Badge>
                        </TableCell>
                        <TableCell className="text-sm">
                          {row.url_count}
                        </TableCell>
                        <TableCell>
                          {row.response_code ? (
                            <Badge
                              variant={
                                row.response_code >= 200 &&
                                row.response_code < 300
                                  ? 'default'
                                  : 'destructive'
                              }
                            >
                              {row.response_code}
                            </Badge>
                          ) : (
                            <span className="text-xs text-muted-foreground">
                              —
                            </span>
                          )}
                        </TableCell>
                        <TableCell className="text-xs text-muted-foreground max-w-[180px] truncate">
                          {row.trigger ?? '—'}
                        </TableCell>
                        <TableCell className="text-sm">
                          {row.attempts}
                        </TableCell>
                        <TableCell className="text-sm text-muted-foreground">
                          {formatRelativeTime(row.created_at)}
                        </TableCell>
                        <TableCell>
                          <Link
                            href={`/admin/indexnow/${row.id}`}
                            className="text-xs text-muted-foreground hover:text-foreground hover:underline"
                          >
                            View
                          </Link>
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </AdminLayout>
  );
}
