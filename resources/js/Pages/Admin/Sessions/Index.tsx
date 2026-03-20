import { AlertTriangle, Monitor } from 'lucide-react';

import { Head, router, usePage } from '@inertiajs/react';

import PageHeader from '@/Components/layout/PageHeader';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { ConfirmDialog } from '@/Components/ui/confirm-dialog';
import { EmptyState } from '@/Components/ui/empty-state';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table';
import { LoadingButton } from '@/Components/ui/loading-button';
import AdminLayout from '@/Layouts/AdminLayout';
import { formatDate } from '@/lib/format';
import type { PageProps, PaginatedResponse } from '@/types';
import type { AdminSessionsIndexProps } from '@/types/admin';
import { useState } from 'react';
import { Link } from '@inertiajs/react';

export default function AdminSessionsIndex({
  sessions,
  driverSupported,
  driver,
}: AdminSessionsIndexProps) {
  const isSuperAdmin = usePage<PageProps>().props.auth.user?.is_super_admin ?? false;
  const [confirmUserId, setConfirmUserId] = useState<number | null>(null);
  const [isTerminating, setIsTerminating] = useState(false);

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

  const sessionData = driverSupported && sessions && 'data' in sessions ? sessions : null;

  return (
    <AdminLayout>
      <Head title="Admin - Session Manager" />
      <PageHeader
        title="Session Manager"
        subtitle="View and terminate active user sessions"
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
          <Card>
            <CardHeader>
              <CardTitle>Active Sessions</CardTitle>
              <CardDescription>
                Currently authenticated users. Terminating a session forces immediate logout.
              </CardDescription>
            </CardHeader>
            <CardContent>
              {!sessionData || sessionData.data.length === 0 ? (
                <EmptyState
                  title="No active sessions"
                  description="No authenticated users have active sessions right now."
                  size="sm"
                />
              ) : (
                <div className="overflow-x-auto">
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>User</TableHead>
                        <TableHead>IP Address</TableHead>
                        <TableHead>User Agent</TableHead>
                        <TableHead>Last Active</TableHead>
                        {isSuperAdmin && <TableHead className="text-right">Actions</TableHead>}
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {sessionData.data.map((session) => (
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
                          <TableCell className="text-sm text-muted-foreground max-w-xs truncate" title={session.user_agent ?? undefined}>
                            {session.user_agent ? session.user_agent.slice(0, 60) + (session.user_agent.length > 60 ? '…' : '') : '—'}
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
                </div>
              )}
            </CardContent>
          </Card>
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
