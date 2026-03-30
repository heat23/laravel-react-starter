import { Mail } from 'lucide-react';

import { useRef } from 'react';

import { Head } from '@inertiajs/react';

import { AdminDataTable } from '@/Components/admin/AdminDataTable';
import { SortHeader } from '@/Components/admin/SortHeader';
import PageHeader from '@/Components/layout/PageHeader';
import { Button } from '@/Components/ui/button';
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
import type { AdminEmailSendLogIndexProps, EmailSendLogFilters } from '@/types/admin';

export default function AdminEmailSendLogsIndex({
  logs,
  sequenceTypes,
  filters,
}: AdminEmailSendLogIndexProps) {
  const searchInputRef = useRef<HTMLInputElement>(null);

  const { search, setSearch, updateFilter, handleSort, handlePage, clearFilters } =
    useAdminFilters<EmailSendLogFilters>({
      route: '/admin/email-send-logs',
      filters,
    });

  const exportParams = Object.fromEntries(
    Object.entries({ search: filters.search, sequence_type: filters.sequence_type }).filter(
      ([, v]) => v != null,
    ) as [string, string][],
  );

  const isNavigating = useNavigationState();
  const currentPage = logs.current_page;
  const lastPage = logs.last_page;

  useAdminKeyboardShortcuts({
    onSearch: () => searchInputRef.current?.focus(),
    onNextPage: currentPage < lastPage ? () => handlePage(currentPage + 1) : undefined,
    onPrevPage: currentPage > 1 ? () => handlePage(currentPage - 1) : undefined,
  });

  return (
    <AdminLayout>
      <Head title="Email Send Logs" />
      <PageHeader
        title="Email Send Logs"
        subtitle={`${logs.total.toLocaleString()} log entries`}
        actions={
          <ExportButton href="/admin/email-send-logs/export" params={exportParams} label="Export CSV" />
        }
      />

      <div className="container py-6 space-y-4">
        <div className="flex flex-col sm:flex-row gap-3 flex-wrap">
          <Input
            ref={searchInputRef}
            className="max-w-xs"
            placeholder="Search by user name or email..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            aria-label="Search email send logs"
          />

          <Select
            value={filters.sequence_type ?? 'all'}
            onValueChange={(v) =>
              updateFilter({ sequence_type: v === 'all' ? undefined : v })
            }
          >
            <SelectTrigger className="w-48" aria-label="Filter by sequence type">
              <SelectValue placeholder="All sequences" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All sequences</SelectItem>
              {sequenceTypes.map((type) => (
                <SelectItem key={type} value={type}>
                  {type.replace(/_/g, ' ')}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>

          {(filters.sequence_type || filters.search) && (
            <Button variant="ghost" size="sm" onClick={clearFilters}>
              Clear filters
            </Button>
          )}
        </div>

        <AdminDataTable
          isEmpty={logs.data.length === 0}
          isNavigating={isNavigating}
          pagination={logs}
          onPage={handlePage}
          paginationLabel="log entries"
          emptyIcon={Mail}
          emptyTitle="No email send logs found"
          emptyDescription={
            filters.sequence_type || filters.search
              ? 'No logs match the current filters.'
              : 'No email sequences have been sent yet.'
          }
          emptyAction={
            (filters.sequence_type || filters.search) ? (
              <Button variant="outline" size="sm" onClick={clearFilters}>
                Clear filters
              </Button>
            ) : undefined
          }
        >
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>User</TableHead>
                <SortHeader
                  column="sequence_type"
                  label="Sequence"
                  currentSort={filters.sort}
                  currentDir={filters.dir}
                  onSort={handleSort}
                />
                <SortHeader
                  column="email_number"
                  label="Email #"
                  currentSort={filters.sort}
                  currentDir={filters.dir}
                  onSort={handleSort}
                />
                <SortHeader
                  column="sent_at"
                  label="Sent At"
                  currentSort={filters.sort}
                  currentDir={filters.dir}
                  onSort={handleSort}
                />
              </TableRow>
            </TableHeader>
            <TableBody>
              {logs.data.map((log) => (
                <TableRow key={log.id}>
                  <TableCell className="text-sm">
                    {log.user ? (
                      <div>
                        <div className="font-medium">{log.user.name}</div>
                        <div className="text-muted-foreground text-xs">{log.user.email}</div>
                      </div>
                    ) : (
                      <span className="text-muted-foreground">[Deleted User]</span>
                    )}
                  </TableCell>
                  <TableCell className="text-sm font-mono">
                    {log.sequence_type.replace(/_/g, ' ')}
                  </TableCell>
                  <TableCell className="text-sm text-center">
                    {log.email_number}
                  </TableCell>
                  <TableCell className="text-xs text-muted-foreground">
                    {formatDate(log.sent_at)}
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
