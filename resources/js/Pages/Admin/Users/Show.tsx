import { ArrowLeft, Shield } from "lucide-react";

import { Head, Link, usePage } from "@inertiajs/react";

import PageHeader from "@/Components/layout/PageHeader";
import { Badge } from "@/Components/ui/badge";
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";
import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { ConfirmDialog } from "@/Components/ui/confirm-dialog";
import { EmptyState } from "@/Components/ui/empty-state";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table";
import { SUBSCRIPTION_STATUS_VARIANT } from "@/config/billing-constants";
import { useAdminAction } from "@/hooks/useAdminAction";
import AdminLayout from "@/Layouts/AdminLayout";
import { formatDate } from "@/lib/format";
import type { PageProps } from "@/types";
import type { AdminUsersShowProps } from "@/types/admin";

export default function AdminUserShow({ user, recent_audit_logs, subscription }: AdminUsersShowProps) {
  const { confirmAction, setConfirmAction, executeAction, getDialogProps } = useAdminAction();
  const currentUserId = usePage<PageProps>().props.auth.user?.id;

  return (
    <AdminLayout>
      <Head title={`Admin - ${user.name}`} />

      <div className="container py-6 space-y-6">
        {/* Breadcrumb */}
        <Breadcrumb>
          <BreadcrumbList>
            <BreadcrumbItem>
              <BreadcrumbLink asChild>
                <Link href="/admin">Admin</Link>
              </BreadcrumbLink>
            </BreadcrumbItem>
            <BreadcrumbSeparator />
            <BreadcrumbItem>
              <BreadcrumbLink asChild>
                <Link href="/admin/users">Users</Link>
              </BreadcrumbLink>
            </BreadcrumbItem>
            <BreadcrumbSeparator />
            <BreadcrumbItem>
              <BreadcrumbPage>{user.name}</BreadcrumbPage>
            </BreadcrumbItem>
          </BreadcrumbList>
        </Breadcrumb>

        <PageHeader
          title={user.name}
          subtitle={user.email}
          actions={
            <div className="flex flex-col sm:flex-row gap-2">
              <Button
                variant="outline"
                size="sm"
                onClick={() => setConfirmAction({ type: "toggleAdmin", user })}
              >
                <Shield className="mr-2 h-4 w-4" />
                {user.is_admin ? "Remove Admin" : "Make Admin"}
              </Button>
              <Button
                variant={user.deleted_at ? "default" : "destructive"}
                size="sm"
                onClick={() => setConfirmAction({ type: "toggleActive", user })}
              >
                {user.deleted_at ? "Restore" : "Deactivate"}
              </Button>
              {!user.is_admin && !user.deleted_at && user.id !== currentUserId && (
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => setConfirmAction({ type: "impersonate", user })}
                >
                  Impersonate
                </Button>
              )}
            </div>
          }
        />

        <div className="grid gap-6 md:grid-cols-2">
          {/* User Info */}
          <Card>
            <CardHeader>
              <CardTitle>User Information</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              <div className="flex flex-col sm:flex-row sm:justify-between gap-1">
                <span className="text-sm text-muted-foreground">ID</span>
                <span className="text-sm font-mono">{user.id}</span>
              </div>
              <div className="flex flex-col sm:flex-row sm:justify-between gap-1">
                <span className="text-sm text-muted-foreground">Role</span>
                <span>{user.is_admin ? <Badge variant="success">Admin</Badge> : <Badge variant="secondary">User</Badge>}</span>
              </div>
              <div className="flex flex-col sm:flex-row sm:justify-between gap-1">
                <span className="text-sm text-muted-foreground">Email Verified</span>
                <span>{user.email_verified_at ? <Badge variant="secondary">Verified</Badge> : <Badge variant="outline">Unverified</Badge>}</span>
              </div>
              <div className="flex flex-col sm:flex-row sm:justify-between gap-1">
                <span className="text-sm text-muted-foreground">Status</span>
                <span>{user.deleted_at ? <Badge variant="destructive">Deactivated</Badge> : <Badge variant="success">Active</Badge>}</span>
              </div>
              <div className="flex flex-col sm:flex-row sm:justify-between gap-1">
                <span className="text-sm text-muted-foreground">Has Password</span>
                <span className="text-sm">{user.has_password ? "Yes" : "No (OAuth only)"}</span>
              </div>
              <div className="flex flex-col sm:flex-row sm:justify-between gap-1">
                <span className="text-sm text-muted-foreground">Signup Source</span>
                <span className="text-sm">{user.signup_source ?? "direct"}</span>
              </div>
              <div className="flex flex-col sm:flex-row sm:justify-between gap-1">
                <span className="text-sm text-muted-foreground">API Tokens</span>
                <span className="text-sm">{user.tokens_count}</span>
              </div>
              <div className="flex flex-col sm:flex-row sm:justify-between gap-1">
                <span className="text-sm text-muted-foreground">Last Login</span>
                <span className="text-sm">{formatDate(user.last_login_at)}</span>
              </div>
              <div className="flex flex-col sm:flex-row sm:justify-between gap-1">
                <span className="text-sm text-muted-foreground">Created</span>
                <span className="text-sm">{formatDate(user.created_at)}</span>
              </div>
              {user.deleted_at && (
                <div className="flex flex-col sm:flex-row sm:justify-between gap-1">
                  <span className="text-sm text-muted-foreground">Deactivated</span>
                  <span className="text-sm">{formatDate(user.deleted_at)}</span>
                </div>
              )}
            </CardContent>
          </Card>

          {/* Subscription — backend-gated by billing.enabled; null when billing is disabled */}
          {subscription && (
            <Card>
              <CardHeader>
                <CardTitle>Subscription</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                <div className="flex flex-col sm:flex-row sm:justify-between gap-1">
                  <span className="text-sm text-muted-foreground">Status</span>
                  <Badge variant={SUBSCRIPTION_STATUS_VARIANT[subscription.stripe_status] ?? "secondary"}>
                    {subscription.stripe_status}
                  </Badge>
                </div>
                <div className="flex flex-col sm:flex-row sm:justify-between gap-1">
                  <span className="text-sm text-muted-foreground">Price</span>
                  <span className="text-sm font-mono">{subscription.stripe_price}</span>
                </div>
                <div className="flex flex-col sm:flex-row sm:justify-between gap-1">
                  <span className="text-sm text-muted-foreground">Quantity</span>
                  <span className="text-sm">{subscription.quantity}</span>
                </div>
                {subscription.trial_ends_at && (
                  <div className="flex flex-col sm:flex-row sm:justify-between gap-1">
                    <span className="text-sm text-muted-foreground">Trial Ends</span>
                    <span className="text-sm">{formatDate(subscription.trial_ends_at)}</span>
                  </div>
                )}
              </CardContent>
            </Card>
          )}
        </div>

        {/* Audit Logs */}
        <Card>
          <CardHeader>
            <CardTitle>Recent Activity</CardTitle>
          </CardHeader>
          <CardContent>
            {recent_audit_logs.length === 0 ? (
              <EmptyState
                title="No activity for this user"
                description="Audit log entries will appear here as the user interacts with the app."
                size="sm"
              />
            ) : (
              <div className="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Event</TableHead>
                    <TableHead>IP</TableHead>
                    <TableHead>Date</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {recent_audit_logs.map((log) => (
                    <TableRow key={log.id}>
                      <TableCell>
                        <Link href={`/admin/audit-logs/${log.id}`} className="hover:opacity-80 transition-opacity">
                          <Badge variant="secondary">{log.event}</Badge>
                        </Link>
                      </TableCell>
                      <TableCell className="text-sm text-muted-foreground font-mono">{log.ip}</TableCell>
                      <TableCell className="text-sm text-muted-foreground">{formatDate(log.created_at)}</TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
              </div>
            )}
          </CardContent>
        </Card>

        <Button variant="outline" asChild>
          <Link href="/admin/users">
            <ArrowLeft className="mr-2 h-4 w-4" />
            Back to Users
          </Link>
        </Button>
      </div>

      {/* Confirm Dialog */}
      <ConfirmDialog
        open={!!confirmAction}
        onOpenChange={(open) => !open && setConfirmAction(null)}
        onConfirm={executeAction}
        {...getDialogProps()}
      />
    </AdminLayout>
  );
}
