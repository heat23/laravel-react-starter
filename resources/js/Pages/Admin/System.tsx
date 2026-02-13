import { Head } from "@inertiajs/react";

import PageHeader from "@/Components/layout/PageHeader";
import { Badge } from "@/Components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { EmptyState } from "@/Components/ui/empty-state";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table";
import AdminLayout from "@/Layouts/AdminLayout";
import type { AdminSystemProps } from "@/types/admin";

export default function AdminSystem({ system }: AdminSystemProps) {
  return (
    <AdminLayout>
      <Head title="Admin - System Info" />
      <PageHeader title="System Info" subtitle="Runtime and dependency information" />

      <div className="container py-8 space-y-6">
        <div className="grid gap-6 md:grid-cols-2">
          {/* Runtime */}
          <Card>
            <CardHeader>
              <CardTitle>Runtime</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              <div className="flex justify-between">
                <span className="text-sm text-muted-foreground">PHP</span>
                <span className="text-sm font-mono">{system.php_version}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-sm text-muted-foreground">Laravel</span>
                <span className="text-sm font-mono">{system.laravel_version}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-sm text-muted-foreground">Node.js</span>
                <span className="text-sm font-mono">
                  {system.node_version ?? <span className="text-muted-foreground">Not available</span>}
                </span>
              </div>
            </CardContent>
          </Card>

          {/* Server */}
          <Card>
            <CardHeader>
              <CardTitle>Server</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              <div className="flex justify-between">
                <span className="text-sm text-muted-foreground">OS</span>
                <span className="text-sm font-mono">{system.server.os}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-sm text-muted-foreground">Server Software</span>
                <span className="text-sm font-mono">{system.server.server_software}</span>
              </div>
            </CardContent>
          </Card>

          {/* Database */}
          <Card>
            <CardHeader>
              <CardTitle>Database</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              <div className="flex justify-between">
                <span className="text-sm text-muted-foreground">Driver</span>
                <span className="text-sm font-mono">{system.database.driver}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-sm text-muted-foreground">Version</span>
                <span className="text-sm font-mono">
                  {system.database.version ?? <span className="text-muted-foreground">Unknown</span>}
                </span>
              </div>
            </CardContent>
          </Card>

          {/* Queue */}
          <Card>
            <CardHeader>
              <CardTitle>Queue</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              <div className="flex justify-between">
                <span className="text-sm text-muted-foreground">Driver</span>
                <span className="text-sm font-mono">{system.queue.driver}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-sm text-muted-foreground">Pending Jobs</span>
                <span className="text-sm">
                  {system.queue.pending_jobs !== null ? system.queue.pending_jobs : "N/A"}
                </span>
              </div>
              <div className="flex justify-between">
                <span className="text-sm text-muted-foreground">Failed Jobs</span>
                <span className="text-sm">
                  {system.queue.failed_jobs !== null ? (
                    system.queue.failed_jobs > 0 ? (
                      <Badge variant="destructive">{system.queue.failed_jobs}</Badge>
                    ) : (
                      system.queue.failed_jobs
                    )
                  ) : (
                    "N/A"
                  )}
                </span>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Packages */}
        <Card>
          <CardHeader>
            <CardTitle>Key Packages</CardTitle>
          </CardHeader>
          <CardContent>
            {system.packages.length === 0 ? (
              <EmptyState
                title="Could not read composer.lock"
                description="Package version information is not available."
                size="sm"
              />
            ) : (
              <div className="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Package</TableHead>
                    <TableHead>Version</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {system.packages.map((pkg) => (
                    <TableRow key={pkg.name}>
                      <TableCell className="font-medium">{pkg.name}</TableCell>
                      <TableCell className="font-mono text-sm">{pkg.version}</TableCell>
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
