import { CreditCard } from "lucide-react";

import { Head, Link } from "@inertiajs/react";

import { AdminDataTable } from "@/Components/admin/AdminDataTable";
import { SortHeader } from "@/Components/admin/SortHeader";
import PageHeader from "@/Components/layout/PageHeader";
import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Input } from "@/Components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table";
import { SUBSCRIPTION_STATUS_VARIANT } from "@/config/billing-constants";
import { useAdminFilters } from "@/hooks/useAdminFilters";
import { useNavigationState } from "@/hooks/useNavigationState";
import AdminLayout from "@/Layouts/AdminLayout";
import { capitalize, formatDate } from "@/lib/format";
import type { AdminBillingSubscriptionsProps } from "@/types/admin";

export default function BillingSubscriptions({ subscriptions, filters, statuses, tiers }: AdminBillingSubscriptionsProps) {
  const { search, setSearch, updateFilter, handleSort, handlePage } = useAdminFilters({
    route: "/admin/billing/subscriptions",
    filters,
    routerOptions: { preserveScroll: true },
  });
  const isNavigating = useNavigationState();

  return (
    <AdminLayout>
      <Head title="Admin - Subscriptions" />
      <PageHeader
        title="Subscriptions"
        subtitle="All subscription records"
        actions={
          <Button variant="outline" size="sm" asChild>
            <Link href="/admin/billing">Back to Billing</Link>
          </Button>
        }
      />

      <div className="container py-8 space-y-6">
        {/* Filters */}
        <Card>
          <CardHeader>
            <CardTitle className="text-sm font-medium">Filters</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="flex flex-col sm:flex-row gap-4">
              <Input
                placeholder="Search by name or email..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                className="sm:max-w-xs"
                aria-label="Search subscriptions by name or email"
              />
              <Select
                value={filters.status ?? "all"}
                onValueChange={(value) => updateFilter({ status: value === "all" ? undefined : value })}
              >
                <SelectTrigger className="sm:w-[180px]" aria-label="Filter by subscription status">
                  <SelectValue placeholder="All Statuses" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Statuses</SelectItem>
                  {statuses.map((status) => (
                    <SelectItem key={status} value={status}>{status}</SelectItem>
                  ))}
                </SelectContent>
              </Select>
              <Select
                value={filters.tier ?? "all"}
                onValueChange={(value) => updateFilter({ tier: value === "all" ? undefined : value })}
              >
                <SelectTrigger className="sm:w-[180px]" aria-label="Filter by subscription tier">
                  <SelectValue placeholder="All Tiers" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Tiers</SelectItem>
                  {tiers.map((tier) => (
                    <SelectItem key={tier} value={tier}>{capitalize(tier)}</SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
          </CardContent>
        </Card>

        {/* Table */}
        <AdminDataTable
          isEmpty={subscriptions.data.length === 0}
          isNavigating={isNavigating}
          pagination={subscriptions}
          onPage={handlePage}
          paginationLabel="subscriptions"
          emptyIcon={CreditCard}
          emptyTitle="No subscriptions found"
          emptyDescription="No subscriptions match your current filters."
        >
                <Table>
                  <TableHeader>
                    <TableRow>
                      <SortHeader column="user_name" label="User" currentSort={filters.sort} currentDir={filters.dir} onSort={handleSort} />
                      <TableHead>Tier</TableHead>
                      <SortHeader column="stripe_status" label="Status" currentSort={filters.sort} currentDir={filters.dir} onSort={handleSort} />
                      <SortHeader column="quantity" label="Qty" currentSort={filters.sort} currentDir={filters.dir} onSort={handleSort} />
                      <TableHead>Trial Ends</TableHead>
                      <TableHead>Ends At</TableHead>
                      <SortHeader column="created_at" label="Created" currentSort={filters.sort} currentDir={filters.dir} onSort={handleSort} />
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {subscriptions.data.map((sub) => (
                      <TableRow key={sub.id} className="hover:bg-muted/50">
                        <TableCell>
                          <Link href={`/admin/users/${sub.user_id}`} className="hover:underline">
                            <div className="text-sm font-medium">{sub.user_name}</div>
                            <div className="text-xs text-muted-foreground max-w-50 truncate">{sub.user_email}</div>
                          </Link>
                        </TableCell>
                        <TableCell>
                          <Badge variant="outline">{capitalize(sub.tier)}</Badge>
                        </TableCell>
                        <TableCell>
                          <Badge variant={SUBSCRIPTION_STATUS_VARIANT[sub.stripe_status] ?? "secondary"}>
                            {sub.stripe_status}
                          </Badge>
                        </TableCell>
                        <TableCell className="text-sm">{sub.quantity}</TableCell>
                        <TableCell className="text-sm text-muted-foreground">{formatDate(sub.trial_ends_at, "\u2014")}</TableCell>
                        <TableCell className="text-sm text-muted-foreground">{formatDate(sub.ends_at, "\u2014")}</TableCell>
                        <TableCell className="text-sm text-muted-foreground">{formatDate(sub.created_at, "\u2014")}</TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
        </AdminDataTable>
      </div>
    </AdminLayout>
  );
}
