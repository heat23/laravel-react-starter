import { AlertCircle } from 'lucide-react';

import { Head, Link } from '@inertiajs/react';

import { AdminDataTable } from '@/Components/admin/AdminDataTable';
import PageHeader from '@/Components/layout/PageHeader';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
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
