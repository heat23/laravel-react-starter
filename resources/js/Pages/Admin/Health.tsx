import { AlertTriangle, CheckCircle2, Loader2, XCircle } from "lucide-react";

import { useEffect, useRef, useState } from "react";

import { Head, router } from "@inertiajs/react";

import PageHeader from "@/Components/layout/PageHeader";
import { Badge } from "@/Components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Label } from "@/Components/ui/label";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/Components/ui/select";
import { Switch } from "@/Components/ui/switch";
import AdminLayout from "@/Layouts/AdminLayout";
import { capitalize } from "@/lib/format";
import type { AdminHealthProps, HealthCheck } from "@/types/admin";

function StatusIcon({ status }: { status: string }) {
  switch (status) {
    case "ok":
    case "healthy":
      return <CheckCircle2 className="h-4 w-4 text-green-600 dark:text-green-400" aria-label="Healthy" />;
    case "warning":
    case "degraded":
      return <AlertTriangle className="h-4 w-4 text-yellow-600 dark:text-yellow-400" aria-label="Warning" />;
    case "error":
    case "unhealthy":
      return <XCircle className="h-4 w-4 text-red-600 dark:text-red-400" aria-label="Error" />;
    default:
      return <div className="h-3 w-3 rounded-full bg-muted-foreground" aria-label="Unknown" />;
  }
}

function statusBadgeVariant(status: string): "default" | "secondary" | "destructive" {
  switch (status) {
    case "healthy":
      return "default";
    case "degraded":
      return "secondary";
    case "unhealthy":
      return "destructive";
    default:
      return "secondary";
  }
}

export default function AdminHealth({ health }: AdminHealthProps) {
  const [autoRefresh, setAutoRefresh] = useState(false);
  const [refreshInterval, setRefreshInterval] = useState("30");
  const [refreshing, setRefreshing] = useState(false);
  const intervalRef = useRef<ReturnType<typeof setInterval> | null>(null);

  useEffect(() => {
    if (autoRefresh) {
      intervalRef.current = setInterval(() => {
        setRefreshing(true);
        router.reload({ only: ["health"], onFinish: () => setRefreshing(false) });
      }, parseInt(refreshInterval) * 1000);
    }

    return () => {
      if (intervalRef.current) {
        clearInterval(intervalRef.current);
        intervalRef.current = null;
      }
    };
  }, [autoRefresh, refreshInterval]);

  return (
    <AdminLayout>
      <Head title="Admin - Health" />
      <PageHeader title="Health Status" subtitle="System health checks" />

      <div className="container py-8 space-y-6">
        {/* Overall status + Controls */}
        <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div className="flex items-center gap-3">
            <Badge variant={statusBadgeVariant(health.status)} className="text-sm px-3 py-1">
              {capitalize(health.status)}
            </Badge>
            <span className="text-sm text-muted-foreground">
              {new Date(health.timestamp).toLocaleString()}
            </span>
            {refreshing && <Loader2 className="h-4 w-4 animate-spin text-muted-foreground" />}
          </div>

          <div className="flex items-center gap-4">
            <div className="flex items-center gap-2">
              <Switch
                id="auto-refresh"
                checked={autoRefresh}
                onCheckedChange={setAutoRefresh}
              />
              <Label htmlFor="auto-refresh" className="text-sm">Auto-refresh</Label>
            </div>
            {autoRefresh && (
              <Select value={refreshInterval} onValueChange={setRefreshInterval}>
                <SelectTrigger className="w-[100px]">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="10">10s</SelectItem>
                  <SelectItem value="30">30s</SelectItem>
                  <SelectItem value="60">60s</SelectItem>
                </SelectContent>
              </Select>
            )}
          </div>
        </div>

        {/* Check Cards */}
        <div className="grid gap-4 md:grid-cols-2">
          {Object.entries(health.checks).map(([name, check]) => (
            <Card key={name}>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium capitalize">{name}</CardTitle>
                <div className="flex items-center gap-2">
                  <StatusIcon status={check.status} />
                  <span className="text-xs text-muted-foreground capitalize">{check.status}</span>
                </div>
              </CardHeader>
              <CardContent>
                <p className="text-sm">{check.message}</p>
                <p className="text-xs text-muted-foreground mt-1">
                  Response time: {check.response_time_ms}ms
                </p>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    </AdminLayout>
  );
}
