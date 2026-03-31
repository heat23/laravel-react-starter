import { Inbox } from 'lucide-react';

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
import type { AdminContactSubmissionsIndexProps, ContactSubmissionFilters } from '@/types/admin';

const statusVariant: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
  new: 'default',
  replied: 'outline',
  spam: 'destructive',
};

export default function AdminContactSubmissionsIndex({
  submissions,
  filters,
  counts,
}: AdminContactSubmissionsIndexProps) {
  const { search, setSearch, updateFilter, handleSort, handlePage, clearFilters } =
    useAdminFilters<ContactSubmissionFilters>({
      route: '/admin/contact-submissions',
      filters,
    });

  const isNavigating = useNavigationState();
  const searchInputRef = useRef<HTMLInputElement>(null);
  const [selectedIds, setSelectedIds] = useState<Set<number>>(new Set());
  const [bulkDeleteConfirmOpen, setBulkDeleteConfirmOpen] = useState(false);

  const currentPage = submissions.current_page;
  const lastPage = submissions.last_page;

  // Clear selection on page change
  useEffect(() => {
    setSelectedIds(new Set());
  }, [submissions.current_page]);

  useAdminKeyboardShortcuts({
    onSearch: () => searchInputRef.current?.focus(),
    onNextPage: currentPage < lastPage ? () => handlePage(currentPage + 1) : undefined,
    onPrevPage: currentPage > 1 ? () => handlePage(currentPage - 1) : undefined,
  });

  const allSelected =
    submissions.data.length > 0 && submissions.data.every((s) => selectedIds.has(s.id));
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
      setSelectedIds(new Set(submissions.data.map((s) => s.id)));
    }
  }, [allSelected, submissions.data]);

  const doBulkAction = useCallback(
    (action: 'spam' | 'replied' | 'delete'): Promise<void> => {
      const ids = Array.from(selectedIds);
      return new Promise((resolve, reject) => {
        router.post(
          '/admin/contact-submissions/bulk-update',
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
  if (filters.status) exportParams.status = filters.status;
  if (filters.search) exportParams.search = filters.search;

  return (
    <AdminLayout>
      <Head title="Contact Submissions" />
      <PageHeader
        title="Contact Submissions"
        subtitle={`${counts.new} new · ${counts.replied} replied · ${counts.spam} spam`}
        actions={
          <ExportButton
            href="/admin/contact-submissions/export"
            params={exportParams}
            label="Export CSV"
          />
        }
      />

      <div className="container py-8 space-y-4">
        <div className="flex flex-col sm:flex-row gap-3 flex-wrap">
          <Input
            ref={searchInputRef}
            className="max-w-xs"
            placeholder="Search name, email, subject..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            aria-label="Search contact submissions"
          />

          <Select
            value={filters.status ?? 'all'}
            onValueChange={(v) => updateFilter({ status: v === 'all' ? undefined : v })}
          >
            <SelectTrigger className="w-36" aria-label="Filter by status">
              <SelectValue placeholder="Status" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All statuses</SelectItem>
              <SelectItem value="new">New</SelectItem>
              <SelectItem value="replied">Replied</SelectItem>
              <SelectItem value="spam">Spam</SelectItem>
            </SelectContent>
          </Select>

          {(filters.status || filters.search) && (
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
            <Button size="sm" variant="outline" onClick={() => doBulkAction('replied')}>
              Mark Replied
            </Button>
            <Button size="sm" variant="outline" onClick={() => doBulkAction('spam')}>
              Mark Spam
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
          isEmpty={submissions.data.length === 0}
          isNavigating={isNavigating}
          pagination={submissions}
          onPage={handlePage}
          paginationLabel="submissions"
          emptyIcon={Inbox}
          emptyTitle="No contact submissions found"
          emptyDescription={
            filters.status || filters.search
              ? 'No submissions match the current filters.'
              : 'No contact form submissions yet.'
          }
          emptyAction={
            filters.status || filters.search ? (
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
                    aria-label="Select all submissions"
                  />
                </TableHead>
                <SortHeader
                  column="name"
                  label="Name"
                  currentSort={filters.sort}
                  currentDir={filters.dir}
                  onSort={handleSort}
                />
                <SortHeader
                  column="email"
                  label="Email"
                  currentSort={filters.sort}
                  currentDir={filters.dir}
                  onSort={handleSort}
                />
                <TableHead>Subject</TableHead>
                <SortHeader
                  column="status"
                  label="Status"
                  currentSort={filters.sort}
                  currentDir={filters.dir}
                  onSort={handleSort}
                />
                <SortHeader
                  column="created_at"
                  label="Date"
                  currentSort={filters.sort}
                  currentDir={filters.dir}
                  onSort={handleSort}
                />
                <TableHead />
              </TableRow>
            </TableHeader>
            <TableBody>
              {submissions.data.map((item) => (
                <TableRow key={item.id}>
                  <TableCell>
                    <Checkbox
                      checked={selectedIds.has(item.id)}
                      onCheckedChange={() => toggleItem(item.id)}
                      aria-label={`Select submission from ${item.name}`}
                    />
                  </TableCell>
                  <TableCell className="font-medium text-sm">{item.name}</TableCell>
                  <TableCell className="text-sm text-muted-foreground">{item.email}</TableCell>
                  <TableCell className="max-w-xs truncate text-sm">{item.subject}</TableCell>
                  <TableCell>
                    <Badge variant={statusVariant[item.status] ?? 'outline'}>{item.status}</Badge>
                  </TableCell>
                  <TableCell className="text-xs text-muted-foreground">
                    {formatDate(item.created_at)}
                  </TableCell>
                  <TableCell>
                    <Button variant="ghost" size="sm" asChild>
                      <Link href={`/admin/contact-submissions/${item.id}`}>View</Link>
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
        title="Delete Submissions"
        description={`This will permanently delete ${selectedIds.size} submission(s). This action cannot be undone.`}
        confirmLabel="Delete"
        loadingLabel="Deleting..."
        variant="destructive"
      />
    </AdminLayout>
  );
}
