import { Radio } from 'lucide-react';

import { useState } from 'react';

import { Head, Link, router, usePage } from '@inertiajs/react';

import { AdminDataTable } from '@/Components/admin/AdminDataTable';
import PageHeader from '@/Components/layout/PageHeader';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { ConfirmDialog } from '@/Components/ui/confirm-dialog';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table';
import { useAdminFilters } from '@/hooks/useAdminFilters';
import { useNavigationState } from '@/hooks/useNavigationState';
import AdminLayout from '@/Layouts/AdminLayout';
import { formatDate } from '@/lib/format';
import type { PageProps } from '@/types';
import type { AdminWebhookEndpointsProps } from '@/types/admin';

export default function WebhookEndpoints({ endpoints }: AdminWebhookEndpointsProps) {
  const isSuperAdmin = usePage<PageProps>().props.auth.user?.is_super_admin ?? false;
  const [restoreId, setRestoreId] = useState<number | null>(null);
  const [isRestoring, setIsRestoring] = useState(false);

  const { handlePage } = useAdminFilters({
    route: '/admin/webhooks/endpoints',
    filters: {},
  });
  const isNavigating = useNavigationState();

  function handleRestore(): Promise<void> {
    if (restoreId === null) return Promise.resolve();
    return new Promise((resolve, reject) => {
      setIsRestoring(true);
      router.patch(
        `/admin/webhooks/endpoints/${restoreId}/restore`,
        {},
        {
          onSuccess: () => {
            setRestoreId(null);
            setIsRestoring(false);
            resolve();
          },
          onError: () => {
            setIsRestoring(false);
            reject();
          },
        },
      );
    });
  }

  return (
    <AdminLayout>
      <Head title="Admin - Webhook Endpoints" />
      <PageHeader
        title="Webhook Endpoints"
        subtitle="All user webhook endpoints including soft-deleted"
        actions={
          <Button variant="outline" size="sm" asChild>
            <Link href="/admin/webhooks">Back to Dashboard</Link>
          </Button>
        }
      />

      <div className="container py-8 space-y-4">
        <AdminDataTable
          isEmpty={endpoints.data.length === 0}
          isNavigating={isNavigating}
          pagination={endpoints}
          onPage={handlePage}
          paginationLabel="endpoints"
          emptyIcon={Radio}
          emptyTitle="No webhook endpoints"
          emptyDescription="No webhook endpoints have been created yet."
        >
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>URL</TableHead>
                <TableHead>User</TableHead>
                <TableHead>Events</TableHead>
                <TableHead>Deliveries</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Created</TableHead>
                {isSuperAdmin && <TableHead className="text-right">Actions</TableHead>}
              </TableRow>
            </TableHeader>
            <TableBody>
              {endpoints.data.map((endpoint) => (
                <TableRow
                  key={endpoint.id}
                  className={endpoint.deleted_at ? 'opacity-60' : undefined}
                >
                  <TableCell className="font-mono text-sm max-w-[200px] truncate" title={endpoint.url}>
                    {endpoint.url}
                  </TableCell>
                  <TableCell>
                    <div className="text-sm font-medium">
                      <Link
                        href={`/admin/users/${endpoint.user_id}`}
                        className="hover:underline"
                      >
                        {endpoint.user_name}
                      </Link>
                    </div>
                    <div className="text-xs text-muted-foreground">{endpoint.user_email}</div>
                  </TableCell>
                  <TableCell>
                    <Badge variant="secondary">
                      {(endpoint.events ?? []).length} event{(endpoint.events ?? []).length !== 1 ? 's' : ''}
                    </Badge>
                  </TableCell>
                  <TableCell className="text-sm">{endpoint.deliveries_count}</TableCell>
                  <TableCell>
                    {endpoint.deleted_at ? (
                      <Badge variant="destructive">Deleted</Badge>
                    ) : endpoint.active ? (
                      <Badge variant="default">Active</Badge>
                    ) : (
                      <Badge variant="secondary">Inactive</Badge>
                    )}
                  </TableCell>
                  <TableCell className="text-sm text-muted-foreground">
                    {formatDate(endpoint.created_at)}
                  </TableCell>
                  {isSuperAdmin && (
                    <TableCell className="text-right">
                      {endpoint.deleted_at && (
                        <Button
                          variant="outline"
                          size="sm"
                          disabled={isRestoring && restoreId === endpoint.id}
                          onClick={() => setRestoreId(endpoint.id)}
                        >
                          Restore
                        </Button>
                      )}
                    </TableCell>
                  )}
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </AdminDataTable>
      </div>

      <ConfirmDialog
        open={restoreId !== null}
        onOpenChange={(open) => !open && setRestoreId(null)}
        onConfirm={handleRestore}
        title="Restore Webhook Endpoint"
        description="This will restore the deleted webhook endpoint and make it active again."
        confirmLabel="Restore"
      />
    </AdminLayout>
  );
}
