import { AlertTriangle, CheckCircle, HeartPulse, XCircle } from 'lucide-react';

import { Head, router } from '@inertiajs/react';

import PageHeader from '@/Components/layout/PageHeader';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import AdminLayout from '@/Layouts/AdminLayout';
import { formatRelativeTime } from '@/lib/format';
import type { AdminDataHealthProps, DataHealthCheck } from '@/types/admin';

function statusIcon(status: DataHealthCheck['status']) {
  switch (status) {
    case 'ok':
      return <CheckCircle className="h-5 w-5 text-success" />;
    case 'warning':
      return <AlertTriangle className="h-5 w-5 text-warning" />;
    case 'error':
      return <XCircle className="h-5 w-5 text-destructive" />;
  }
}

function statusVariant(
  status: DataHealthCheck['status']
): 'default' | 'secondary' | 'destructive' {
  switch (status) {
    case 'ok':
      return 'secondary';
    case 'warning':
      return 'default';
    case 'error':
      return 'destructive';
  }
}

function formatCheckName(key: string): string {
  return key
    .split('_')
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
}

export default function AdminDataHealth({ checks, ran_at }: AdminDataHealthProps) {
  const checkEntries = Object.entries(checks);
  const hasIssues = checkEntries.some(([, check]) => check.status !== 'ok');

  return (
    <AdminLayout>
      <Head title="Admin - Data Health" />
      <PageHeader
        title="Data Health"
        subtitle="Check for orphaned records, stale data, and inconsistencies"
        actions={
          <div className="flex items-center gap-3">
            <span className="text-sm text-muted-foreground">
              Last checked: {formatRelativeTime(ran_at)}
            </span>
            <Button
              variant="outline"
              onClick={() => router.reload({ only: ['checks', 'ran_at'] })}
            >
              Re-run Checks
            </Button>
          </div>
        }
      />

      <div className="container py-8 space-y-4">
        <div className="flex items-center gap-2 mb-4">
          <HeartPulse className="h-5 w-5" />
          <span className="text-sm font-medium">
            {hasIssues ? 'Issues detected' : 'All checks passing'}
          </span>
          <Badge variant={hasIssues ? 'destructive' : 'secondary'}>
            {checkEntries.filter(([, c]) => c.status !== 'ok').length} issues
          </Badge>
        </div>

        <div className="grid gap-4 md:grid-cols-2">
          {checkEntries.map(([key, check]) => (
            <Card key={key}>
              <CardHeader className="pb-2">
                <CardTitle className="flex items-center justify-between text-base">
                  <span className="flex items-center gap-2">
                    {statusIcon(check.status)}
                    {formatCheckName(key)}
                  </span>
                  <Badge variant={statusVariant(check.status)}>
                    {check.count} {check.count === 1 ? 'record' : 'records'}
                  </Badge>
                </CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-sm text-muted-foreground">
                  {check.description}
                </p>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    </AdminLayout>
  );
}
