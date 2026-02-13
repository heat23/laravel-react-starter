import { ArrowLeft } from "lucide-react";

import { Head, Link } from "@inertiajs/react";

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
import { EmptyState } from "@/Components/ui/empty-state";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table";
import { SUBSCRIPTION_STATUS_VARIANT } from "@/config/billing-constants";
import AdminLayout from "@/Layouts/AdminLayout";
import { capitalize, formatDate } from "@/lib/format";
import type { AdminBillingShowProps } from "@/types/admin";

export default function BillingShow({ subscription, items, audit_logs }: AdminBillingShowProps) {
  return (
    <AdminLayout>
      <Head title={`Admin - Subscription #${subscription.id}`} />

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
                <Link href="/admin/billing">Billing</Link>
              </BreadcrumbLink>
            </BreadcrumbItem>
            <BreadcrumbSeparator />
            <BreadcrumbItem>
              <BreadcrumbLink asChild>
                <Link href="/admin/billing/subscriptions">Subscriptions</Link>
              </BreadcrumbLink>
            </BreadcrumbItem>
            <BreadcrumbSeparator />
            <BreadcrumbItem>
              <BreadcrumbPage>#{subscription.id}</BreadcrumbPage>
            </BreadcrumbItem>
          </BreadcrumbList>
        </Breadcrumb>

        <PageHeader
          title={`Subscription #${subscription.id}`}
          subtitle={`${subscription.user_name} (${subscription.user_email})`}
        />

        <div className="grid gap-6 md:grid-cols-2">
          {/* Subscription Info */}
          <Card>
            <CardHeader>
              <CardTitle>Subscription Details</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              <div className="flex flex-col sm:flex-row sm:justify-between gap-1">
                <span className="text-sm text-muted-foreground">Status</span>
                <Badge variant={SUBSCRIPTION_STATUS_VARIANT[subscription.stripe_status] ?? "secondary"}>
                  {subscription.stripe_status}
                </Badge>
              </div>
              <div className="flex flex-col sm:flex-row sm:justify-between gap-1">
                <span className="text-sm text-muted-foreground">Tier</span>
                <Badge variant="outline">{capitalize(subscription.tier)}</Badge>
              </div>
              <div className="flex flex-col sm:flex-row sm:justify-between gap-1">
                <span className="text-sm text-muted-foreground">Quantity</span>
                <span className="text-sm">{subscription.quantity}</span>
              </div>
              <div className="flex flex-col sm:flex-row sm:justify-between gap-1">
                <span className="text-sm text-muted-foreground">Stripe ID</span>
                <span className="text-sm font-mono max-w-50 truncate inline-block">{subscription.stripe_id}</span>
              </div>
              <div className="flex flex-col sm:flex-row sm:justify-between gap-1">
                <span className="text-sm text-muted-foreground">Created</span>
                <span className="text-sm">{formatDate(subscription.created_at)}</span>
              </div>
              {subscription.trial_ends_at && (
                <div className="flex flex-col sm:flex-row sm:justify-between gap-1">
                  <span className="text-sm text-muted-foreground">Trial Ends</span>
                  <span className="text-sm">{formatDate(subscription.trial_ends_at)}</span>
                </div>
              )}
              {subscription.ends_at && (
                <div className="flex flex-col sm:flex-row sm:justify-between gap-1">
                  <span className="text-sm text-muted-foreground">Ends At</span>
                  <span className="text-sm">{formatDate(subscription.ends_at)}</span>
                </div>
              )}
            </CardContent>
          </Card>

          {/* User Card */}
          <Card>
            <CardHeader>
              <CardTitle>Subscriber</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              <div className="flex flex-col sm:flex-row sm:justify-between gap-1">
                <span className="text-sm text-muted-foreground">Name</span>
                <span className="text-sm">{subscription.user_name}</span>
              </div>
              <div className="flex flex-col sm:flex-row sm:justify-between gap-1">
                <span className="text-sm text-muted-foreground">Email</span>
                <span className="text-sm">{subscription.user_email}</span>
              </div>
              <Button variant="outline" size="sm" asChild className="mt-2">
                <Link href={`/admin/users/${subscription.user_id}`}>View User Profile</Link>
              </Button>
            </CardContent>
          </Card>
        </div>

        {/* Subscription Items */}
        <Card>
          <CardHeader>
            <CardTitle>Subscription Items</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Product</TableHead>
                  <TableHead>Price</TableHead>
                  <TableHead>Tier</TableHead>
                  <TableHead>Quantity</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {items.map((item) => (
                  <TableRow key={item.id}>
                    <TableCell className="text-sm font-mono max-w-50 truncate">{item.stripe_product}</TableCell>
                    <TableCell className="text-sm font-mono max-w-50 truncate">{item.stripe_price}</TableCell>
                    <TableCell>
                      <Badge variant="outline">{capitalize(item.tier)}</Badge>
                    </TableCell>
                    <TableCell className="text-sm">{item.quantity}</TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
            </div>
          </CardContent>
        </Card>

        {/* Billing Audit Logs */}
        <Card>
          <CardHeader>
            <CardTitle>Billing Activity</CardTitle>
          </CardHeader>
          <CardContent>
            {audit_logs.length === 0 ? (
              <EmptyState
                title="No billing events"
                description="Billing-related audit logs for this user will appear here."
                size="sm"
              />
            ) : (
              <div className="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Event</TableHead>
                    <TableHead>Date</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {audit_logs.map((log) => (
                    <TableRow key={log.id}>
                      <TableCell>
                        <Link href={`/admin/audit-logs/${log.id}`} className="hover:opacity-80 transition-opacity">
                          <Badge variant="secondary">{log.event}</Badge>
                        </Link>
                      </TableCell>
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
          <Link href="/admin/billing/subscriptions">
            <ArrowLeft className="mr-2 h-4 w-4" />
            Back to Subscriptions
          </Link>
        </Button>
      </div>
    </AdminLayout>
  );
}
