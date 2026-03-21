import { FileText } from 'lucide-react';

import { useRef, useState } from 'react';

import { Head, Link } from '@inertiajs/react';

import { AdminDataTable } from '@/Components/admin/AdminDataTable';
import { SortHeader } from '@/Components/admin/SortHeader';
import PageHeader from '@/Components/layout/PageHeader';
import { Badge } from '@/Components/ui/badge';
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
import type { AdminAuditLogsIndexProps } from '@/types/admin';

export default function AdminAuditLogsIndex({
  logs,
  eventTypes,
  filters,
}: AdminAuditLogsIndexProps) {
  const userIdInputRef = useRef<HTMLInputElement>(null);
  const userIdTimeout = useRef<ReturnType<typeof setTimeout>>();
  const ipTimeout = useRef<ReturnType<typeof setTimeout>>();
  const searchTimeout = useRef<ReturnType<typeof setTimeout>>();
  const [from, setFrom] = useState(filters.from ?? '');
  const [to, setTo] = useState(filters.to ?? '');
  const [userId, setUserId] = useState(filters.user_id ?? '');
  const [ip, setIp] = useState(filters.ip ?? '');
  const [search, setSearch] = useState(filters.search ?? '');
  const {
    updateFilter,
    handlePage,
    handleSort,
    clearFilters: baseClearFilters,
  } = useAdminFilters({
    route: '/admin/audit-logs',
    filters,
  });

  const clearFilters = () => {
    clearTimeout(userIdTimeout.current);
    clearTimeout(ipTimeout.current);
    clearTimeout(searchTimeout.current);
    setFrom('');
    setTo('');
    setUserId('');
    setIp('');
    setSearch('');
    baseClearFilters();
  };
  const isNavigating = useNavigationState();

  const currentPage = logs.current_page;
  const lastPage = logs.last_page;
  useAdminKeyboardShortcuts({
    onSearch: () => userIdInputRef.current?.focus(),
    onNextPage:
      currentPage < lastPage ? () => handlePage(currentPage + 1) : undefined,
    onPrevPage: currentPage > 1 ? () => handlePage(currentPage - 1) : undefined,
  });

  const exportParams: Record<string, string> = {};
  if (filters.event) exportParams.event = filters.event;
  if (filters.user_id) exportParams.user_id = filters.user_id;
  if (filters.from) exportParams.from = filters.from;
  if (filters.to) exportParams.to = filters.to;
  if (filters.ip) exportParams.ip = filters.ip;
  if (filters.search) exportParams.search = filters.search;

  return (
    <AdminLayout>
      <Head title="Admin - Audit Logs" />
      <PageHeader
        title="Audit Logs"
        subtitle="System activity log"
        actions={
          <ExportButton href="/admin/audit-logs/export" params={exportParams} />
        }
      />

      <div className="container py-8 space-y-4">
        {/* Filters */}
        <fieldset className="flex flex-col sm:flex-row gap-3 flex-wrap">
          <legend className="sr-only">Audit Log Filters</legend>
          <Select
            value={filters.event ?? 'all'}
            onValueChange={(value) =>
              updateFilter({ event: value === 'all' ? undefined : value })
            }
          >
            <SelectTrigger
              className="w-[200px]"
              aria-label="Filter by event type"
            >
              <SelectValue placeholder="All events" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All Events</SelectItem>
              {eventTypes.map((event) => (
                <SelectItem key={event} value={event}>
                  {event}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>

          <Input
            ref={userIdInputRef}
            type="text"
            inputMode="numeric"
            pattern="[0-9]*"
            placeholder="User ID"
            value={userId}
            onChange={(e) => {
              const val = e.target.value;
              setUserId(val);
              clearTimeout(userIdTimeout.current);
              userIdTimeout.current = setTimeout(() => {
                updateFilter({ user_id: val || undefined });
              }, 400);
            }}
            className="w-30"
            aria-label="Filter by user ID"
          />

          <Input
            type="date"
            placeholder="From"
            value={from}
            onChange={(e) => setFrom(e.target.value)}
            onBlur={() =>
              updateFilter({
                from: from || undefined,
                to: to || undefined,
                user_id: userId || undefined,
              })
            }
            className="w-[160px]"
            aria-label="Filter from date"
          />
          <Input
            type="date"
            placeholder="To"
            value={to}
            onChange={(e) => setTo(e.target.value)}
            onBlur={() =>
              updateFilter({
                from: from || undefined,
                to: to || undefined,
                user_id: userId || undefined,
              })
            }
            className="w-[160px]"
            aria-label="Filter to date"
          />
          <Input
            type="text"
            placeholder="Filter by IP"
            value={ip}
            onChange={(e) => {
              const val = e.target.value;
              setIp(val);
              clearTimeout(ipTimeout.current);
              ipTimeout.current = setTimeout(() => {
                updateFilter({ ip: val || undefined });
              }, 400);
            }}
            className="w-[160px]"
            aria-label="Filter by IP address"
          />
          <Input
            type="text"
            placeholder="Search metadata..."
            value={search}
            onChange={(e) => {
              const val = e.target.value;
              setSearch(val);
              clearTimeout(searchTimeout.current);
              searchTimeout.current = setTimeout(() => {
                updateFilter({ search: val || undefined });
              }, 400);
            }}
            className="w-[200px]"
            aria-label="Search event or metadata"
          />
        </fieldset>

        <AdminDataTable
          isEmpty={logs.data.length === 0}
          isNavigating={isNavigating}
          pagination={logs}
          onPage={handlePage}
          paginationLabel="entries"
          perPage={Number(filters.per_page ?? 50)}
          onPerPageChange={(value) =>
            updateFilter({ per_page: String(value) })
          }
          emptyIcon={FileText}
          emptyTitle="No audit logs found"
          emptyDescription={
            filters.event || filters.user_id || filters.from || filters.to || filters.ip || filters.search
              ? 'No logs match your current filters. Try clearing them.'
              : 'No audit activity recorded yet.'
          }
          emptyAction={
            filters.event || filters.user_id || filters.from || filters.to || filters.ip || filters.search ? (
              <Button variant="outline" size="sm" onClick={clearFilters}>
                Clear filters
              </Button>
            ) : undefined
          }
        >
          <Table>
            <TableHeader>
              <TableRow>
                <SortHeader
                  column="event"
                  label="Event"
                  currentSort={filters.sort}
                  currentDir={filters.dir}
                  onSort={handleSort}
                />
                <TableHead>User</TableHead>
                <TableHead>IP</TableHead>
                <SortHeader
                  column="created_at"
                  label="Date"
                  currentSort={filters.sort}
                  currentDir={filters.dir}
                  onSort={handleSort}
                />
                <TableHead className="w-[80px]" />
              </TableRow>
            </TableHeader>
            <TableBody>
              {logs.data.map((log) => (
                <TableRow key={log.id}>
                  <TableCell>
                    <Badge variant="secondary">{log.event}</Badge>
                  </TableCell>
                  <TableCell className="text-sm">
                    {log.user_name ? (
                      <Link
                        href={`/admin/users/${log.user_id}`}
                        className="hover:underline"
                      >
                        {log.user_name}
                      </Link>
                    ) : (
                      <span className="text-muted-foreground">System</span>
                    )}
                  </TableCell>
                  <TableCell className="text-sm text-muted-foreground font-mono max-w-50 truncate">
                    {log.ip}
                  </TableCell>
                  <TableCell className="text-sm text-muted-foreground">
                    {formatDate(log.created_at)}
                  </TableCell>
                  <TableCell>
                    <Button variant="ghost" size="sm" asChild>
                      <Link href={`/admin/audit-logs/${log.id}`}>View</Link>
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
