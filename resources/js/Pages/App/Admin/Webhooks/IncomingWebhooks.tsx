import { Radio } from 'lucide-react';

import { useState } from 'react';

import { Head, Link } from '@inertiajs/react';

import { AdminDataTable } from '@/Components/admin/AdminDataTable';
import PageHeader from '@/Components/layout/PageHeader';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/Components/ui/dialog';
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
import { useAdminFilters } from '@/hooks/useAdminFilters';
import { useNavigationState } from '@/hooks/useNavigationState';
import AdminLayout from '@/Layouts/AdminLayout';
import { formatDate, formatProviderName } from '@/lib/format';
import type { AdminIncomingWebhook, AdminIncomingWebhooksProps } from '@/types/admin';

const STATUS_VARIANTS: Record<
  string,
  'default' | 'secondary' | 'destructive' | 'outline'
> = {
  received: 'secondary',
  processing: 'outline',
  processed: 'default',
  failed: 'destructive',
};

export default function IncomingWebhooks({
  webhooks,
  providers,
  filters,
}: AdminIncomingWebhooksProps) {
  const [selectedWebhook, setSelectedWebhook] =
    useState<AdminIncomingWebhook | null>(null);

  const { updateFilter, handlePage, clearFilters } = useAdminFilters({
    route: '/admin/webhooks/incoming',
    filters,
  });
  const isNavigating = useNavigationState();

  const hasFilters = Boolean(filters.provider ?? filters.status ?? filters.event_type);

  return (
    <AdminLayout>
      <Head title="Admin - Incoming Webhooks" />
      <PageHeader
        title="Incoming Webhooks"
        subtitle="Browse and inspect received webhook payloads"
        actions={
          <Button variant="outline" size="sm" asChild>
            <Link href="/admin/webhooks">Back to Dashboard</Link>
          </Button>
        }
      />

      <div className="container py-8 space-y-4">
        {/* Filters */}
        <div className="flex flex-wrap gap-3">
          <Select
            value={filters.provider ?? 'all'}
            onValueChange={(v) => updateFilter({ provider: v === 'all' ? '' : v })}
          >
            <SelectTrigger className="w-[160px]">
              <SelectValue placeholder="All providers" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All providers</SelectItem>
              {providers.map((p) => (
                <SelectItem key={p} value={p}>
                  {formatProviderName(p)}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>

          <Select
            value={filters.status ?? 'all'}
            onValueChange={(v) => updateFilter({ status: v === 'all' ? '' : v })}
          >
            <SelectTrigger className="w-[150px]">
              <SelectValue placeholder="All statuses" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All statuses</SelectItem>
              <SelectItem value="received">Received</SelectItem>
              <SelectItem value="processing">Processing</SelectItem>
              <SelectItem value="processed">Processed</SelectItem>
              <SelectItem value="failed">Failed</SelectItem>
            </SelectContent>
          </Select>

          {hasFilters && (
            <Button variant="ghost" size="sm" onClick={clearFilters}>
              Clear filters
            </Button>
          )}
        </div>

        <AdminDataTable
          isEmpty={webhooks.data.length === 0}
          isNavigating={isNavigating}
          pagination={webhooks}
          onPage={handlePage}
          paginationLabel="webhooks"
          emptyIcon={Radio}
          emptyTitle="No incoming webhooks"
          emptyDescription="No incoming webhooks have been received yet."
        >
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>ID</TableHead>
                <TableHead>Provider</TableHead>
                <TableHead>Event Type</TableHead>
                <TableHead>External ID</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Received</TableHead>
                <TableHead className="text-right">Payload</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {webhooks.data.map((webhook) => (
                <TableRow key={webhook.id}>
                  <TableCell className="text-sm text-muted-foreground">
                    {webhook.id}
                  </TableCell>
                  <TableCell>
                    <Badge variant="secondary">
                      {formatProviderName(webhook.provider)}
                    </Badge>
                  </TableCell>
                  <TableCell className="text-sm font-mono">
                    {webhook.event_type ?? <span className="text-muted-foreground">—</span>}
                  </TableCell>
                  <TableCell className="text-sm font-mono max-w-[160px] truncate" title={webhook.external_id ?? undefined}>
                    {webhook.external_id ?? <span className="text-muted-foreground">—</span>}
                  </TableCell>
                  <TableCell>
                    <Badge variant={STATUS_VARIANTS[webhook.status] ?? 'secondary'}>
                      {webhook.status}
                    </Badge>
                  </TableCell>
                  <TableCell className="text-sm text-muted-foreground">
                    {formatDate(webhook.created_at)}
                  </TableCell>
                  <TableCell className="text-right">
                    <Button
                      variant="ghost"
                      size="sm"
                      aria-label={`View webhook #${webhook.id}`}
                      onClick={() => setSelectedWebhook(webhook)}
                    >
                      View
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </AdminDataTable>
      </div>

      {/* Payload inspector dialog */}
      <Dialog
        open={selectedWebhook !== null}
        onOpenChange={(open) => !open && setSelectedWebhook(null)}
      >
        <DialogContent className="max-w-2xl max-h-[80vh] overflow-hidden flex flex-col">
          <DialogHeader>
            <DialogTitle>
              Webhook #{selectedWebhook?.id} —{' '}
              {selectedWebhook ? formatProviderName(selectedWebhook.provider) : ''}
            </DialogTitle>
            <DialogDescription>
              {selectedWebhook?.event_type ?? 'No event type'} &middot;{' '}
              {selectedWebhook?.status}
            </DialogDescription>
          </DialogHeader>
          <div className="overflow-y-auto flex-1 mt-2">
            <pre className="bg-muted rounded-md p-4 text-xs font-mono whitespace-pre-wrap break-all">
              {selectedWebhook?.payload !== null
                ? JSON.stringify(selectedWebhook?.payload, null, 2)
                : '(empty payload)'}
            </pre>
          </div>
        </DialogContent>
      </Dialog>
    </AdminLayout>
  );
}
