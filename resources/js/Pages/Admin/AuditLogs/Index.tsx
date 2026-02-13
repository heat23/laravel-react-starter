import { FileText } from "lucide-react";

import { useState } from "react";

import { Head, Link } from "@inertiajs/react";

import { AdminDataTable } from "@/Components/admin/AdminDataTable";
import PageHeader from "@/Components/layout/PageHeader";
import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import { ExportButton } from "@/Components/ui/export-button";
import { Input } from "@/Components/ui/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/Components/ui/select";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table";
import { useAdminFilters } from "@/hooks/useAdminFilters";
import { useNavigationState } from "@/hooks/useNavigationState";
import AdminLayout from "@/Layouts/AdminLayout";
import { formatDate } from "@/lib/format";
import type { AdminAuditLogsIndexProps } from "@/types/admin";

export default function AdminAuditLogsIndex({ logs, eventTypes, filters }: AdminAuditLogsIndexProps) {
  const [from, setFrom] = useState(filters.from ?? "");
  const [to, setTo] = useState(filters.to ?? "");
  const [userId, setUserId] = useState(filters.user_id ?? "");
  const { updateFilter, handlePage, clearFilters: baseClearFilters } = useAdminFilters({
    route: "/admin/audit-logs",
    filters,
  });

  const clearFilters = () => {
    setFrom("");
    setTo("");
    setUserId("");
    baseClearFilters();
  };
  const isNavigating = useNavigationState();

  const exportParams: Record<string, string> = {};
  if (filters.event) exportParams.event = filters.event;
  if (filters.user_id) exportParams.user_id = filters.user_id;
  if (filters.from) exportParams.from = filters.from;
  if (filters.to) exportParams.to = filters.to;

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
          <Select value={filters.event ?? "all"} onValueChange={(value) => updateFilter({ event: value === "all" ? undefined : value })}>
            <SelectTrigger className="w-[200px]" aria-label="Filter by event type">
              <SelectValue placeholder="All events" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All Events</SelectItem>
              {eventTypes.map((event) => (
                <SelectItem key={event} value={event}>{event}</SelectItem>
              ))}
            </SelectContent>
          </Select>

          <Input
            type="text"
            inputMode="numeric"
            pattern="[0-9]*"
            placeholder="User ID"
            value={userId}
            onChange={(e) => setUserId(e.target.value)}
            className="w-30"
            aria-label="Filter by user ID"
          />

          <Input
            type="date"
            placeholder="From"
            value={from}
            onChange={(e) => setFrom(e.target.value)}
            className="w-[160px]"
            aria-label="Filter from date"
          />
          <Input
            type="date"
            placeholder="To"
            value={to}
            onChange={(e) => setTo(e.target.value)}
            className="w-[160px]"
            aria-label="Filter to date"
          />
          <Button variant="outline" size="default" onClick={() => updateFilter({ from: from || undefined, to: to || undefined, user_id: userId || undefined })}>
            Apply Filters
          </Button>
        </fieldset>

        <AdminDataTable
          isEmpty={logs.data.length === 0}
          isNavigating={isNavigating}
          pagination={logs}
          onPage={handlePage}
          paginationLabel="entries"
          emptyIcon={FileText}
          emptyTitle="No audit logs found"
          emptyDescription={
            filters.event || filters.user_id || filters.from || filters.to
              ? "No logs match your current filters. Try clearing them."
              : "No audit activity recorded yet."
          }
          emptyAction={
            (filters.event || filters.user_id || filters.from || filters.to) ? (
              <Button variant="outline" size="sm" onClick={clearFilters}>
                Clear filters
              </Button>
            ) : undefined
          }
        >
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Event</TableHead>
                    <TableHead>User</TableHead>
                    <TableHead>IP</TableHead>
                    <TableHead>Date</TableHead>
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
                          <Link href={`/admin/users/${log.user_id}`} className="hover:underline">
                            {log.user_name}
                          </Link>
                        ) : (
                          <span className="text-muted-foreground">System</span>
                        )}
                      </TableCell>
                      <TableCell className="text-sm text-muted-foreground font-mono max-w-50 truncate">{log.ip}</TableCell>
                      <TableCell className="text-sm text-muted-foreground">{formatDate(log.created_at)}</TableCell>
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
