import { AlertTriangle, Clock, Hash, RefreshCw, Zap } from 'lucide-react';

import { Head, Link, router } from '@inertiajs/react';

import { Badge } from '@/Components/ui/badge';
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/Components/ui/breadcrumb';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import AdminLayout from '@/Layouts/AdminLayout';
import { formatDate, formatRelativeTime } from '@/lib/format';
import type { AdminIndexNowSubmissionDetailProps } from '@/types/admin';

function statusVariant(
  status: string
): 'default' | 'destructive' | 'secondary' {
  if (status === 'success') return 'default';
  if (status === 'failed') return 'destructive';
  return 'secondary';
}

function responseCodeVariant(
  code: number | null
): 'default' | 'destructive' | 'secondary' {
  if (code === null) return 'secondary';
  if (code >= 200 && code < 300) return 'default';
  return 'destructive';
}

export default function IndexNowSubmissionDetail({
  submission,
}: AdminIndexNowSubmissionDetailProps) {
  const onRetry = () => {
    router.post(
      `/admin/indexnow/${submission.id}/retry`,
      {},
      { preserveScroll: true }
    );
  };

  return (
    <AdminLayout>
      <Head title={`Admin - IndexNow Submission #${submission.id}`} />

      <div className="container py-6 space-y-6">
        <Breadcrumb>
          <BreadcrumbList>
            <BreadcrumbItem>
              <BreadcrumbLink asChild>
                <Link href="/admin">Admin</Link>
              </BreadcrumbLink>
            </BreadcrumbItem>
            <BreadcrumbSeparator />
            <BreadcrumbItem>
              <BreadcrumbLink asChild>
                <Link href="/admin/indexnow">IndexNow</Link>
              </BreadcrumbLink>
            </BreadcrumbItem>
            <BreadcrumbSeparator />
            <BreadcrumbItem>
              <BreadcrumbPage>Submission #{submission.id}</BreadcrumbPage>
            </BreadcrumbItem>
          </BreadcrumbList>
        </Breadcrumb>

        <div className="flex flex-wrap items-center justify-between gap-4">
          <div className="space-y-1">
            <h1 className="text-2xl font-semibold tracking-tight">
              IndexNow Submission
            </h1>
            <p className="text-sm text-muted-foreground font-mono">
              {submission.uuid}
            </p>
          </div>
          {submission.status === 'failed' && (
            <Button onClick={onRetry} variant="secondary">
              <RefreshCw className="h-4 w-4 mr-2" aria-hidden="true" />
              Retry submission
            </Button>
          )}
        </div>

        <div className="grid gap-4 md:grid-cols-3">
          <Card>
            <CardHeader className="flex flex-row items-center gap-2 space-y-0">
              <Zap className="h-4 w-4 text-muted-foreground" aria-hidden="true" />
              <CardTitle className="text-sm font-medium">Status</CardTitle>
            </CardHeader>
            <CardContent>
              <Badge variant={statusVariant(submission.status)}>
                {submission.status}
              </Badge>
              {submission.response_code && (
                <Badge
                  variant={responseCodeVariant(submission.response_code)}
                  className="ml-2"
                >
                  HTTP {submission.response_code}
                </Badge>
              )}
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center gap-2 space-y-0">
              <Hash className="h-4 w-4 text-muted-foreground" aria-hidden="true" />
              <CardTitle className="text-sm font-medium">URLs / Attempts</CardTitle>
            </CardHeader>
            <CardContent className="space-y-1 text-sm">
              <div>{submission.url_count} URL{submission.url_count === 1 ? '' : 's'}</div>
              <div className="text-muted-foreground">
                {submission.attempts} attempt{submission.attempts === 1 ? '' : 's'}
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center gap-2 space-y-0">
              <Clock className="h-4 w-4 text-muted-foreground" aria-hidden="true" />
              <CardTitle className="text-sm font-medium">Timeline</CardTitle>
            </CardHeader>
            <CardContent className="space-y-1 text-sm">
              <div>Queued {formatRelativeTime(submission.created_at)}</div>
              {submission.submitted_at && (
                <div className="text-muted-foreground">
                  Delivered {formatDate(submission.submitted_at)}
                </div>
              )}
              {submission.trigger && (
                <div className="text-xs text-muted-foreground">
                  Trigger: <span className="font-mono">{submission.trigger}</span>
                </div>
              )}
            </CardContent>
          </Card>
        </div>

        <Card>
          <CardHeader>
            <CardTitle className="text-base">Submitted URLs</CardTitle>
          </CardHeader>
          <CardContent>
            {submission.urls.length === 0 ? (
              <p className="text-sm text-muted-foreground">
                No URLs recorded for this submission.
              </p>
            ) : (
              <ul className="space-y-1 max-h-96 overflow-y-auto text-sm font-mono break-all">
                {submission.urls.map((url) => (
                  <li key={url} className="py-1 border-b last:border-0">
                    {url}
                  </li>
                ))}
              </ul>
            )}
          </CardContent>
        </Card>

        {submission.response_body && (
          <Card>
            <CardHeader className="flex flex-row items-center gap-2 space-y-0">
              <AlertTriangle
                className="h-4 w-4 text-muted-foreground"
                aria-hidden="true"
              />
              <CardTitle className="text-base">Response body</CardTitle>
            </CardHeader>
            <CardContent>
              <pre className="text-xs bg-muted p-3 rounded whitespace-pre-wrap break-words max-h-96 overflow-y-auto">
                {submission.response_body}
              </pre>
            </CardContent>
          </Card>
        )}
      </div>
    </AdminLayout>
  );
}
