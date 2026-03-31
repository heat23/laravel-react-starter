import { MessageSquare } from 'lucide-react';

import { useState, useCallback, useEffect, useRef } from 'react';

import { Head, Link, router } from '@inertiajs/react';

import { AdminDataTable } from '@/Components/admin/AdminDataTable';
import { SortHeader } from '@/Components/admin/SortHeader';
import PageHeader from '@/Components/layout/PageHeader';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Checkbox } from '@/Components/ui/checkbox';
import { ConfirmDialog } from '@/Components/ui/confirm-dialog';
import { ExportButton } from '@/Components/ui/export-button';
import { Input } from '@/Components/ui/input';
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
import { useAdminKeyboardShortcuts } from '@/hooks/useAdminKeyboardShortcuts';
import { useNavigationState } from '@/hooks/useNavigationState';
import AdminLayout from '@/Layouts/AdminLayout';
import { formatDate } from '@/lib/format';
import type { AdminFeedbackIndexProps, FeedbackFilters } from '@/types/admin';

const statusVariant: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
  open: 'default',
  in_review: 'secondary',
  resolved: 'outline',
  declined: 'destructive',
};

const priorityVariant: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
  low: 'outline',
  medium: 'secondary',
  high: 'destructive',
};

export default function AdminFeedbackIndex({ feedback, filters, counts }: AdminFeedbackIndexProps) {
  const { search, setSearch, updateFilter, handleSort, handlePage, clearFilters } =
    useAdminFilters<FeedbackFilters>({
      route: '/admin/feedback',
      filters,
    });

  const isNavigating = useNavigationState();
  const searchInputRef = useRef<HTMLInputElement>(null);
  const [selectedIds, setSelectedIds] = useState<Set<number>>(new Set());
  const [bulkDeleteConfirmOpen, setBulkDeleteConfirmOpen] = useState(false);

  const currentPage = feedback.current_page;
  const lastPage = feedback.last_page;

  useAdminKeyboardShortcuts({
    onSearch: () => searchInputRef.current?.focus(),
    onNextPage: currentPage < lastPage ? () => handlePage(currentPage + 1) : undefined,
    onPrevPage: currentPage > 1 ? () => handlePage(currentPage - 1) : undefined,
  });

  // Clear selection on page change
  useEffect(() => {
    setSelectedIds(new Set());
  }, [feedback.current_page]);

  const allSelected =
    feedback.data.length > 0 && feedback.data.every((f) => selectedIds.has(f.id));
  const someSelected = selectedIds.size > 0;

  const toggleItem = useCallback((id: number) => {
    setSelectedIds((prev) => {
      const next = new Set(prev);
      if (next.has(id)) next.delete(id);
      else next.add(id);
      return next;
    });
  }, []);

  const toggleAll = useCallback(() => {
    if (allSelected) {
      setSelectedIds(new Set());
    } else {
      setSelectedIds(new Set(feedback.data.map((f) => f.id)));
    }
  }, [allSelected, feedback.data]);

  const doBulkAction = useCallback(
    (action: 'resolve' | 'decline' | 'delete'): Promise<void> => {
      const ids = Array.from(selectedIds);
      return new Promise((resolve, reject) => {
        router.post(
          '/admin/feedback/bulk-update',
          { ids, action },
          {
            onSuccess: () => {
              setSelectedIds(new Set());
              resolve();
            },
            onError: () => reject(),
          },
        );
      });
    },
    [selectedIds],
  );

  const exportParams: Record<string, string> = {};
  if (filters.type) exportParams.type = filters.type;
  if (filters.status) exportParams.status = filters.status;
  if (filters.search) exportParams.search = filters.search;

  return (
    <AdminLayout>
      <Head title="Feedback Inbox" />
      <PageHeader
        title="Feedback Inbox"
        subtitle={`${counts.open} open · ${counts.in_review} in review · ${counts.resolved} resolved`}
        actions={
          <ExportButton href="/admin/feedback/export" params={exportParams} label="Export CSV" />
        }
      />

      <div className="container py-8 space-y-4">
        <div className="flex flex-col sm:flex-row gap-3 flex-wrap">
          <Input
            ref={searchInputRef}
            className="max-w-xs"
            placeholder="Search message, user..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            aria-label="Search feedback"
          />

          <Select
            value={filters.type ?? 'all'}
            onValueChange={(v) =>
              updateFilter({ type: v === 'all' ? undefined : v })
            }
          >
            <SelectTrigger className="w-36" aria-label="Filter by type">
              <SelectValue placeholder="Type" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All types</SelectItem>
              <SelectItem value="bug">Bug</SelectItem>
              <SelectItem value="feature">Feature</SelectItem>
              <SelectItem value="general">General</SelectItem>
            </SelectContent>
          </Select>

          <Select
            value={filters.status ?? 'all'}
            onValueChange={(v) =>
              updateFilter({ status: v === 'all' ? undefined : v })
            }
          >
            <SelectTrigger className="w-36" aria-label="Filter by status">
              <SelectValue placeholder="Status" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All statuses</SelectItem>
              <SelectItem value="open">Open</SelectItem>
              <SelectItem value="in_review">In Review</SelectItem>
              <SelectItem value="resolved">Resolved</SelectItem>
              <SelectItem value="declined">Declined</SelectItem>
            </SelectContent>
          </Select>

          {(filters.type || filters.status || filters.search) && (
            <Button variant="ghost" size="sm" onClick={clearFilters}>
              Clear filters
            </Button>
          )}
        </div>

        {someSelected && (
          <div className="flex items-center gap-3 p-3 bg-muted rounded-md flex-wrap">
            <span className="text-sm text-muted-foreground">
              {selectedIds.size} selected
            </span>
            <Button size="sm" variant="outline" onClick={() => doBulkAction('resolve')}>
              Resolve
            </Button>
            <Button size="sm" variant="outline" onClick={() => doBulkAction('decline')}>
              Decline
            </Button>
            <Button
              size="sm"
              variant="destructive"
              onClick={() => setBulkDeleteConfirmOpen(true)}
            >
              Delete
            </Button>
          </div>
        )}

        <AdminDataTable
          isEmpty={feedback.data.length === 0}
          isNavigating={isNavigating}
          pagination={feedback}
          onPage={handlePage}
          paginationLabel="feedback items"
          emptyIcon={MessageSquare}
          emptyTitle="No feedback found"
          emptyDescription={
            filters.type || filters.status || filters.search
              ? 'No feedback matches the current filters.'
              : 'No feedback submissions yet.'
          }
          emptyAction={
            (filters.type || filters.status || filters.search) ? (
              <Button variant="outline" size="sm" onClick={clearFilters}>
                Clear filters
              </Button>
            ) : undefined
          }
        >
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead className="w-[40px]">
                  <Checkbox
                    checked={allSelected}
                    onCheckedChange={toggleAll}
                    aria-label="Select all feedback"
                  />
                </TableHead>
                <SortHeader column="type" label="Type" currentSort={filters.sort} currentDir={filters.dir} onSort={handleSort} />
                <TableHead>User</TableHead>
                <TableHead>Message</TableHead>
                <SortHeader column="priority" label="Priority" currentSort={filters.sort} currentDir={filters.dir} onSort={handleSort} />
                <SortHeader column="status" label="Status" currentSort={filters.sort} currentDir={filters.dir} onSort={handleSort} />
                <SortHeader column="created_at" label="Date" currentSort={filters.sort} currentDir={filters.dir} onSort={handleSort} />
                <TableHead />
              </TableRow>
            </TableHeader>
            <TableBody>
              {feedback.data.map((item) => (
                <TableRow key={item.id}>
                  <TableCell>
                    <Checkbox
                      checked={selectedIds.has(item.id)}
                      onCheckedChange={() => toggleItem(item.id)}
                      aria-label={`Select feedback ${item.id}`}
                    />
                  </TableCell>
                  <TableCell>
                    <Badge variant="outline">{item.type}</Badge>
                  </TableCell>
                  <TableCell className="text-sm">
                    {item.user ? (
                      <div>
                        <div className="font-medium">{item.user.name}</div>
                        <div className="text-muted-foreground text-xs">{item.user.email}</div>
                      </div>
                    ) : (
                      <span className="text-muted-foreground">Guest</span>
                    )}
                  </TableCell>
                  <TableCell className="max-w-xs truncate text-sm">
                    {item.message}
                  </TableCell>
                  <TableCell>
                    <Badge variant={priorityVariant[item.priority] ?? 'outline'}>
                      {item.priority}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <Badge variant={statusVariant[item.status] ?? 'outline'}>
                      {item.status.replace('_', ' ')}
                    </Badge>
                  </TableCell>
                  <TableCell className="text-xs text-muted-foreground">
                    {formatDate(item.created_at)}
                  </TableCell>
                  <TableCell>
                    <Button variant="ghost" size="sm" asChild>
                      <Link href={`/admin/feedback/${item.id}`}>View</Link>
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </AdminDataTable>
      </div>

      <ConfirmDialog
        open={bulkDeleteConfirmOpen}
        onOpenChange={(open) => !open && setBulkDeleteConfirmOpen(false)}
        onConfirm={() => doBulkAction('delete')}
        title="Delete Feedback"
        description={`This will permanently delete ${selectedIds.size} feedback item(s). This action cannot be undone.`}
        confirmLabel="Delete"
        loadingLabel="Deleting..."
        variant="destructive"
      />
    </AdminLayout>
  );
}
