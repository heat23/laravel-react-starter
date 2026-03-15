import { ArrowLeft, RefreshCw, Trash2 } from 'lucide-react';

import { useState } from 'react';

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
import { ConfirmDialog } from '@/Components/ui/confirm-dialog';
import AdminLayout from '@/Layouts/AdminLayout';
import { formatDate } from '@/lib/format';
import type { AdminFailedJobShowProps } from '@/types/admin';

function formatPayload(payload: string): string {
  try {
    return JSON.stringify(JSON.parse(payload), null, 2);
  } catch {
    return payload;
  }
}

export default function AdminFailedJobShow({ job }: AdminFailedJobShowProps) {
  const [showRetryDialog, setShowRetryDialog] = useState(false);
  const [showDeleteDialog, setShowDeleteDialog] = useState(false);

  return (
    <AdminLayout>
      <Head title={`Admin - Failed Job #${job.id}`} />

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
                <Link href="/admin/failed-jobs">Failed Jobs</Link>
              </BreadcrumbLink>
            </BreadcrumbItem>
            <BreadcrumbSeparator />
            <BreadcrumbItem>
              <BreadcrumbPage>#{job.id}</BreadcrumbPage>
            </BreadcrumbItem>
          </BreadcrumbList>
        </Breadcrumb>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-3">
              <Badge variant="secondary" className="text-sm">
                {job.payload_summary}
              </Badge>
              <span className="text-muted-foreground text-sm font-normal">
                #{job.id}
              </span>
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid gap-4 md:grid-cols-2">
              <div>
                <p className="text-sm text-muted-foreground mb-1">UUID</p>
                <p className="text-sm font-mono">{job.uuid}</p>
              </div>
              <div>
                <p className="text-sm text-muted-foreground mb-1">Queue</p>
                <p className="text-sm">{job.queue}</p>
              </div>
              <div>
                <p className="text-sm text-muted-foreground mb-1">Connection</p>
                <p className="text-sm">{job.connection}</p>
              </div>
              <div>
                <p className="text-sm text-muted-foreground mb-1">Failed At</p>
                <p className="text-sm">{formatDate(job.failed_at)}</p>
              </div>
            </div>

            <div>
              <p className="text-sm text-muted-foreground mb-2">Exception</p>
              <pre className="bg-muted p-4 rounded-lg overflow-auto text-sm max-h-96">
                {job.exception}
              </pre>
            </div>

            <div>
              <p className="text-sm text-muted-foreground mb-2">Payload</p>
              <pre className="bg-muted p-4 rounded-lg overflow-auto text-sm max-h-96">
                {formatPayload(job.payload)}
              </pre>
            </div>
          </CardContent>
        </Card>

        <div className="flex items-center gap-2">
          <Button variant="outline" asChild>
            <Link href="/admin/failed-jobs">
              <ArrowLeft className="mr-2 h-4 w-4" />
              Back to Failed Jobs
            </Link>
          </Button>
          <Button variant="default" onClick={() => setShowRetryDialog(true)}>
            <RefreshCw className="mr-2 h-4 w-4" />
            Retry
          </Button>
          <Button
            variant="destructive"
            onClick={() => setShowDeleteDialog(true)}
          >
            <Trash2 className="mr-2 h-4 w-4" />
            Delete
          </Button>
        </div>

        <ConfirmDialog
          open={showRetryDialog}
          onOpenChange={setShowRetryDialog}
          title="Retry Failed Job"
          description="This will re-queue the job for processing. It will be removed from the failed jobs list."
          resourceName={job.payload_summary}
          resourceType="Job"
          confirmLabel="Retry Job"
          onConfirm={() => {
            router.post(`/admin/failed-jobs/${job.id}/retry`);
          }}
        />

        <ConfirmDialog
          open={showDeleteDialog}
          onOpenChange={setShowDeleteDialog}
          title="Delete Failed Job"
          description="This will permanently delete the failed job record. This action cannot be undone."
          resourceName={job.payload_summary}
          resourceType="Job"
          variant="destructive"
          confirmLabel="Delete Job"
          onConfirm={() => {
            router.delete(`/admin/failed-jobs/${job.id}`);
          }}
        />
      </div>
    </AdminLayout>
  );
}
