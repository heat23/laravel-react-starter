import { AlertCircle } from 'lucide-react';

import { useState, useCallback, useEffect } from 'react';

import { Head, Link, router } from '@inertiajs/react';

import { AdminDataTable } from '@/Components/admin/AdminDataTable';
import PageHeader from '@/Components/layout/PageHeader';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Checkbox } from '@/Components/ui/checkbox';
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
import { formatDate } from '@/lib/format';
import type { AdminFailedJobsIndexProps } from '@/types/admin';

export default function AdminFailedJobsIndex({
  jobs,
  queues,
  filters,
}: AdminFailedJobsIndexProps) {
  const { updateFilter, handlePage, clearFilters } = useAdminFilters({
    route: '/admin/failed-jobs',
    filters,
  });
  const isNavigating = useNavigationState();
  const [selectedUuids, setSelectedUuids] = useState<Set<string>>(new Set());

  // Clear selection on page navigation
  useEffect(() => {
    setSelectedUuids(new Set());
  }, [jobs.current_page]);

  const allSelected =
    jobs.data.length > 0 && jobs.data.every((j) => selectedUuids.has(j.uuid));
  const someSelected = selectedUuids.size > 0;

  const toggleJob = useCallback((uuid: string) => {
    setSelectedUuids((prev) => {
      const next = new Set(prev);
      if (next.has(uuid)) next.delete(uuid);
      else next.add(uuid);
      return next;
    });
  }, []);

  const toggleAll = useCallback(() => {
    if (allSelected) {
      setSelectedUuids(new Set());
    } else {
      setSelectedUuids(new Set(jobs.data.map((j) => j.uuid)));
    }
  }, [allSelected, jobs.data]);

  const bulkRetry = useCallback(() => {
    const ids = Array.from(selectedUuids);
    router.post(
      '/admin/failed-jobs/bulk-retry',
      { ids },
      { onSuccess: () => setSelectedUuids(new Set()) },
    );
  }, [selectedUuids]);

  const bulkDelete = useCallback(() => {
    const ids = Array.from(selectedUuids);
    router.delete('/admin/failed-jobs/bulk', {
      data: { ids },
      onSuccess: () => setSelectedUuids(new Set()),
    });
  }, [selectedUuids]);

  return (
    <AdminLayout>
      <Head title="Admin - Failed Jobs" />
      <PageHeader
        title="Failed Jobs"
        subtitle="View, retry, or delete failed queue jobs"
      />

      <div className="container py-8 space-y-4">
        <fieldset className="flex flex-col sm:flex-row gap-3 flex-wrap">
          <legend className="sr-only">Failed Job Filters</legend>
          <Select
            value={filters.queue ?? 'all'}
            onValueChange={(value) =>
              updateFilter({ queue: value === 'all' ? undefined : value })
            }
          >
            <SelectTrigger className="w-[200px]" aria-label="Filter by queue">
              <SelectValue placeholder="All queues" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All Queues</SelectItem>
              {queues.map((queue) => (
                <SelectItem key={queue} value={queue}>
                  {queue}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </fieldset>

        {someSelected && (
          <div className="flex items-center gap-3 p-3 bg-muted rounded-md">
            <span className="text-sm text-muted-foreground">
              {selectedUuids.size} selected
            </span>
            <Button size="sm" variant="outline" onClick={bulkRetry}>
              Retry Selected
            </Button>
            <Button size="sm" variant="destructive" onClick={bulkDelete}>
              Delete Selected
            </Button>
          </div>
        )}

        <AdminDataTable
          isEmpty={jobs.data.length === 0}
          isNavigating={isNavigating}
          pagination={jobs}
          onPage={handlePage}
          paginationLabel="jobs"
          emptyIcon={AlertCircle}
          emptyTitle="No failed jobs"
          emptyDescription={
            filters.queue
              ? 'No failed jobs match the selected queue.'
              : 'All queue jobs are running successfully.'
          }
          emptyAction={
            filters.queue ? (
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
                    aria-label="Select all jobs"
                  />
                </TableHead>
                <TableHead>Job</TableHead>
                <TableHead>Queue</TableHead>
                <TableHead>Failed At</TableHead>
                <TableHead>Exception</TableHead>
                <TableHead className="w-[80px]" />
              </TableRow>
            </TableHeader>
            <TableBody>
              {jobs.data.map((job) => (
                <TableRow key={job.id}>
                  <TableCell>
                    <Checkbox
                      checked={selectedUuids.has(job.uuid)}
                      onCheckedChange={() => toggleJob(job.uuid)}
                      aria-label={`Select job ${job.id}`}
                    />
                  </TableCell>
                  <TableCell>
                    <Badge variant="secondary">{job.payload_summary}</Badge>
                  </TableCell>
                  <TableCell className="text-sm text-muted-foreground">
                    {job.queue}
                  </TableCell>
                  <TableCell className="text-sm text-muted-foreground">
                    {formatDate(job.failed_at)}
                  </TableCell>
                  <TableCell className="text-sm text-muted-foreground max-w-xs truncate">
                    {job.exception_summary}
                  </TableCell>
                  <TableCell>
                    <Button variant="ghost" size="sm" asChild>
                      <Link href={`/admin/failed-jobs/${job.id}`}>View</Link>
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </AdminDataTable>
      </div>
    </AdminLayout>
  );
}
