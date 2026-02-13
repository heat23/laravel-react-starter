import { Clock, Key, Users } from "lucide-react";

import { Head } from "@inertiajs/react";

import { AdminStatsGrid, type StatCard } from "@/Components/admin/AdminStatsGrid";
import PageHeader from "@/Components/layout/PageHeader";
import { Badge } from "@/Components/ui/badge";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import { EmptyState } from "@/Components/ui/empty-state";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table";
import AdminLayout from "@/Layouts/AdminLayout";
import { formatRelativeTime } from "@/lib/format";
import type { AdminTokensDashboardProps } from "@/types/admin";

export default function TokensDashboard({ stats, most_active }: AdminTokensDashboardProps) {
  return (
    <AdminLayout>
      <Head title="Admin - API Tokens" />
      <PageHeader title="API Tokens" subtitle="Token usage and statistics" />

      <div className="container py-8 space-y-8">
        {/* Stats */}
        <AdminStatsGrid columns="grid-cols-1 md:grid-cols-2 lg:grid-cols-5" stats={[
          { title: "Total Tokens", value: stats.total_tokens, icon: Key },
          { title: "Users with Tokens", value: stats.users_with_tokens, icon: Users },
          { title: "Recently Used (7d)", value: stats.recently_used },
          { title: "Never Used", value: stats.never_used, valueClassName: "text-muted-foreground" },
          { title: "Expired", value: stats.expired_tokens, icon: Clock },
        ] satisfies StatCard[]} />

        {/* Most Active Tokens */}
        <Card>
          <CardHeader>
            <CardTitle>Most Recently Active Tokens</CardTitle>
            <CardDescription>Tokens sorted by last usage</CardDescription>
          </CardHeader>
          <CardContent>
            {most_active.length === 0 ? (
              <EmptyState icon={Key} title="No token activity" description="Token usage data will appear here once tokens are used." size="sm" />
            ) : (
              <div className="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Token Name</TableHead>
                    <TableHead>User</TableHead>
                    <TableHead>Abilities</TableHead>
                    <TableHead>Last Used</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {most_active.map((token) => (
                    <TableRow key={`${token.token_name}-${token.user_email}`}>
                      <TableCell className="text-sm font-medium">{token.token_name}</TableCell>
                      <TableCell>
                        <div className="text-sm">{token.user_name}</div>
                        <div className="text-xs text-muted-foreground">{token.user_email}</div>
                      </TableCell>
                      <TableCell>
                        <div className="flex flex-wrap gap-1">
                          {(token.abilities ?? []).slice(0, 3).map((ability) => (
                            <Badge key={ability} variant="outline" className="text-xs">{ability}</Badge>
                          ))}
                          {(token.abilities ?? []).length > 3 && (
                            <Badge variant="outline" className="text-xs">+{token.abilities.length - 3}</Badge>
                          )}
                        </div>
                      </TableCell>
                      <TableCell className="text-sm text-muted-foreground">{formatRelativeTime(token.last_used_at)}</TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </AdminLayout>
  );
}
