import { ArrowLeft } from "lucide-react";

import { Head, Link } from "@inertiajs/react";

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
import AdminLayout from "@/Layouts/AdminLayout";
import type { AdminAuditLogShowProps } from "@/types/admin";

export default function AdminAuditLogShow({ auditLog }: AdminAuditLogShowProps) {
  return (
    <AdminLayout>
      <Head title={`Admin - Audit Log #${auditLog.id}`} />

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
                <Link href="/admin/audit-logs">Audit Logs</Link>
              </BreadcrumbLink>
            </BreadcrumbItem>
            <BreadcrumbSeparator />
            <BreadcrumbItem>
              <BreadcrumbPage>#{auditLog.id}</BreadcrumbPage>
            </BreadcrumbItem>
          </BreadcrumbList>
        </Breadcrumb>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-3">
              <Badge variant="secondary" className="text-sm">{auditLog.event}</Badge>
              <span className="text-muted-foreground text-sm font-normal">#{auditLog.id}</span>
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid gap-4 md:grid-cols-2">
              <div>
                <p className="text-sm text-muted-foreground mb-1">User</p>
                <p className="text-sm">
                  {auditLog.user_name ? (
                    <Link href={`/admin/users/${auditLog.user_id}`} className="hover:underline">
                      {auditLog.user_name} ({auditLog.user_email})
                    </Link>
                  ) : (
                    <span className="text-muted-foreground">System</span>
                  )}
                </p>
              </div>
              <div>
                <p className="text-sm text-muted-foreground mb-1">IP Address</p>
                <p className="text-sm font-mono">{auditLog.ip ?? "N/A"}</p>
              </div>
              <div>
                <p className="text-sm text-muted-foreground mb-1">Date</p>
                <p className="text-sm">{new Date(auditLog.created_at).toLocaleString()}</p>
              </div>
              <div>
                <p className="text-sm text-muted-foreground mb-1">User Agent</p>
                <p className="text-sm text-muted-foreground truncate">{auditLog.user_agent ?? "N/A"}</p>
              </div>
            </div>

            {auditLog.metadata && Object.keys(auditLog.metadata).length > 0 && (
              <div>
                <p className="text-sm text-muted-foreground mb-2">Metadata</p>
                <pre className="bg-muted p-4 rounded-lg overflow-auto text-sm">
                  {JSON.stringify(auditLog.metadata, null, 2)}
                </pre>
              </div>
            )}
          </CardContent>
        </Card>

        <Button variant="outline" asChild>
          <Link href="/admin/audit-logs">
            <ArrowLeft className="mr-2 h-4 w-4" />
            Back to Audit Logs
          </Link>
        </Button>
      </div>
    </AdminLayout>
  );
}
