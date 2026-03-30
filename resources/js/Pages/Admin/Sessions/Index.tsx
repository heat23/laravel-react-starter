import { AlertTriangle, Monitor } from 'lucide-react';

import { useRef } from 'react';

import { Head, Link, router, usePage } from '@inertiajs/react';

import { AdminDataTable } from '@/Components/admin/AdminDataTable';
import { SortHeader } from '@/Components/admin/SortHeader';
import PageHeader from '@/Components/layout/PageHeader';
import { Button } from '@/Components/ui/button';
import { Card, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { ConfirmDialog } from '@/Components/ui/confirm-dialog';
import { Input } from '@/Components/ui/input';
import { LoadingButton } from '@/Components/ui/loading-button';
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
import type { PageProps } from '@/types';
import type { AdminSessionsIndexProps, SessionFilters } from '@/types/admin';
import { useState } from 'react';

export default function AdminSessionsIndex({
  sessions,
  driverSupported,
  driver,
  filters,
}: AdminSessionsIndexProps) {
  const isSuperAdmin = usePage<PageProps>().props.auth.user?.is_super_admin ?? false;
  const [confirmUserId, setConfirmUserId] = useState<number | null>(null);
  const [isTerminating, setIsTerminating] = useState(false);
  const searchInputRef = useRef<HTMLInputElement>(null);

  const { search, setSearch, handleSort, handlePage, clearFilters } =
    useAdminFilters<SessionFilters>({
      route: '/admin/sessions',
      filters,
    });

  const isNavigating = useNavigationState();

  const sessionData = driverSupported && sessions && 'data' in sessions ? sessions : null;
  const currentPage = sessionData?.current_page ?? 1;
  const lastPage = sessionData?.last_page ?? 1;

  useAdminKeyboardShortcuts({
    onSearch: () => searchInputRef.current?.focus(),
    onNextPage: currentPage < lastPage ? () => handlePage(currentPage + 1) : undefined,
    onPrevPage: currentPage > 1 ? () => handlePage(currentPage - 1) : undefined,
  });

  function handleTerminate(): Promise<void> {
    if (confirmUserId === null) return Promise.resolve();
    return new Promise((resolve, reject) => {
      setIsTerminating(true);
      router.delete(`/admin/sessions/${confirmUserId}`, {
        onSuccess: () => {
          setConfirmUserId(null);
          setIsTerminating(false);
          resolve();
        },
        onError: () => {
          setIsTerminating(false);
          reject();
        },
      });
    });
  }

  return (
    <AdminLayout>
      <Head title="Admin - Session Manager" />
      <PageHeader
        title="Session Manager"
        subtitle={
          sessionData
            ? `${sessionData.total.toLocaleString()} active session${sessionData.total !== 1 ? 's' : ''}`
            : 'View and terminate active user sessions'
        }
      />

      <div className="container py-8 space-y-6">
        {!driverSupported && (
          <Card className="border-warning">
            <CardHeader>
              <div className="flex items-center gap-2">
                <AlertTriangle className="h-5 w-5 text-warning" />
                <CardTitle>Session Driver Not Supported</CardTitle>
              </div>
              <CardDescription>
                Session management requires the <code className="font-mono text-sm">database</code> session
                driver. The current driver is <code className="font-mono text-sm">{driver}</code>.
                Update <code className="font-mono text-sm">SESSION_DRIVER=database</code> in your{' '}
                <code className="font-mono text-sm">.env</code> file and run{' '}
                <code className="font-mono text-sm">php artisan migrate</code> to enable this feature.
              </CardDescription>
            </CardHeader>
          </Card>
        )}

        {driverSupported && (
          <>
            <div className="flex flex-col sm:flex-row gap-3">
              <Input
                ref={searchInputRef}
                className="max-w-xs"
                placeholder="Search by name or email..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                aria-label="Search sessions"
              />
              {filters.search && (
                <Button variant="ghost" size="sm" onClick={clearFilters}>
                  Clear filters
                </Button>
              )}
            </div>

            <AdminDataTable
              isEmpty={!sessionData || sessionData.data.length === 0}
              isNavigating={isNavigating}
              pagination={sessionData ?? undefined}
              onPage={handlePage}
              paginationLabel="sessions"
              emptyIcon={Monitor}
              emptyTitle="No active sessions"
              emptyDescription={
                filters.search
                  ? 'No sessions match the current search.'
                  : 'No authenticated users have active sessions right now.'
              }
              emptyAction={
                filters.search ? (
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
                      column="ip_address"
                      label="IP Address"
                      currentSort={filters.sort}
                      currentDir={filters.dir}
                      onSort={handleSort}
                    />
                    <TableHead>User Agent</TableHead>
                    <SortHeader
                      column="last_activity"
                      label="Last Active"
                      currentSort={filters.sort}
                      currentDir={filters.dir}
                      onSort={handleSort}
                    />
                    {isSuperAdmin && <TableHead className="text-right">Actions</TableHead>}
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {sessionData?.data.map((session) => (
                    <TableRow key={session.session_id}>
                      <TableCell>
                        <div>
                          <Link
                            href={`/admin/users/${session.user_id}`}
                            className="font-medium hover:underline"
                          >
                            {session.user_name}
                          </Link>
                          <div className="text-sm text-muted-foreground">{session.user_email}</div>
                        </div>
                      </TableCell>
                      <TableCell className="font-mono text-sm">
                        {session.ip_address ?? '—'}
                      </TableCell>
                      <TableCell
                        className="text-sm text-muted-foreground max-w-xs truncate"
                        title={session.user_agent ?? undefined}
                      >
                        {session.user_agent
                          ? session.user_agent.slice(0, 60) + (session.user_agent.length > 60 ? '…' : '')
                          : '—'}
                      </TableCell>
                      <TableCell className="text-sm text-muted-foreground">
                        {formatDate(session.last_activity)}
                      </TableCell>
                      {isSuperAdmin && (
                        <TableCell className="text-right">
                          <LoadingButton
                            variant="destructive"
                            size="sm"
                            loading={isTerminating && confirmUserId === session.user_id}
                            onClick={() => setConfirmUserId(session.user_id)}
                          >
                            Terminate
                          </LoadingButton>
                        </TableCell>
                      )}
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </AdminDataTable>
          </>
        )}
      </div>

      <ConfirmDialog
        open={confirmUserId !== null}
        onOpenChange={(open) => !open && setConfirmUserId(null)}
        onConfirm={handleTerminate}
        title="Terminate Sessions"
        description="This will immediately log out the user from all active sessions. They will need to log in again."
        variant="destructive"
        confirmLabel="Terminate"
      />
    </AdminLayout>
  );
}
