import { AlertTriangle, CheckCircle, Info } from "lucide-react";

import { Head } from "@inertiajs/react";

import PageHeader from "@/Components/layout/PageHeader";
import { Alert, AlertDescription, AlertTitle } from "@/Components/ui/alert";
import { Badge } from "@/Components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table";
import AdminLayout from "@/Layouts/AdminLayout";
import type { AdminConfigProps } from "@/types/admin";

export default function AdminConfig({ feature_flags, warnings, environment }: AdminConfigProps) {
  return (
    <AdminLayout>
      <Head title="Admin - Config" />
      <PageHeader title="Configuration" subtitle="Feature flags and environment warnings" />

      <div className="container py-8 space-y-6">
        {/* Warnings */}
        {warnings.length > 0 && (
          <div className="space-y-3">
            {warnings.map((warning, i) => (
              <Alert key={i} variant={warning.level === "critical" ? "destructive" : "default"}>
                <AlertTriangle className="h-4 w-4" />
                <AlertTitle>{warning.level === "critical" ? "Critical" : "Warning"}</AlertTitle>
                <AlertDescription>{warning.message}</AlertDescription>
              </Alert>
            ))}
          </div>
        )}

        {warnings.length === 0 && (
          <Alert>
            <CheckCircle className="h-4 w-4" />
            <AlertTitle>All clear</AlertTitle>
            <AlertDescription>No configuration warnings detected.</AlertDescription>
          </Alert>
        )}

        {/* Feature Flags */}
        <Card>
          <CardHeader>
            <CardTitle>Feature Flags</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Flag</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Env Variable</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {feature_flags.map((flag) => (
                  <TableRow key={flag.key}>
                    <TableCell className="font-medium">{flag.key}</TableCell>
                    <TableCell>
                      {flag.enabled ? (
                        <Badge variant="success">
                          Enabled
                        </Badge>
                      ) : (
                        <Badge variant="secondary" className="text-muted-foreground">
                          Disabled
                        </Badge>
                      )}
                    </TableCell>
                    <TableCell className="font-mono text-sm text-muted-foreground">
                      {flag.env_var}
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
            </div>
          </CardContent>
        </Card>

        {/* Environment (minimal) */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              Environment
              <Info className="h-4 w-4 text-muted-foreground" />
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Setting</TableHead>
                  <TableHead>Value</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {Object.entries(environment).map(([key, value]) => (
                  <TableRow key={key}>
                    <TableCell className="font-medium">{key.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase())}</TableCell>
                    <TableCell className="font-mono text-sm">{String(value)}</TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
            </div>
          </CardContent>
        </Card>
      </div>
    </AdminLayout>
  );
}
