import { Calendar } from 'lucide-react';

import { Head } from '@inertiajs/react';

import PageHeader from '@/Components/layout/PageHeader';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { EmptyState } from '@/Components/ui/empty-state';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table';
import AdminLayout from '@/Layouts/AdminLayout';
import { formatDate } from '@/lib/format';
import type { AdminScheduleIndexProps } from '@/types/admin';

export default function AdminScheduleIndex({ tasks }: AdminScheduleIndexProps) {
  return (
    <AdminLayout>
      <Head title="Admin - Schedule Monitor" />
      <PageHeader
        title="Schedule Monitor"
        subtitle="Registered scheduled tasks and their next run times"
      />

      <div className="container py-8">
        <Card>
          <CardHeader>
            <CardTitle>Scheduled Tasks</CardTitle>
            <CardDescription>
              All tasks registered in the Laravel scheduler. This is read-only — tasks are defined in{' '}
              <code className="text-sm font-mono">routes/console.php</code>.
            </CardDescription>
          </CardHeader>
          <CardContent>
            {tasks.length === 0 ? (
              <EmptyState
                title="No scheduled tasks"
                description="No tasks have been registered with the Laravel scheduler."
                size="sm"
              />
            ) : (
              <div className="overflow-x-auto">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Command / Description</TableHead>
                      <TableHead>Cron Expression</TableHead>
                      <TableHead>Next Run</TableHead>
                      <TableHead>Timezone</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {tasks.map((task, i) => (
                      <TableRow key={i}>
                        <TableCell>
                          <div className="space-y-0.5">
                            <code className="text-sm font-mono">{task.command}</code>
                            {task.description && task.description !== task.command && (
                              <div className="text-xs text-muted-foreground">{task.description}</div>
                            )}
                          </div>
                        </TableCell>
                        <TableCell>
                          <code className="text-sm font-mono text-muted-foreground">
                            {task.expression}
                          </code>
                        </TableCell>
                        <TableCell className="text-sm text-muted-foreground">
                          {task.next_run_date ? formatDate(task.next_run_date) : '—'}
                        </TableCell>
                        <TableCell className="text-sm text-muted-foreground">
                          {task.timezone}
                        </TableCell>
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
