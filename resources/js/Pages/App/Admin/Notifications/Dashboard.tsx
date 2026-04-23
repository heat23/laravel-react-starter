import { Bell, CheckCircle, Mail, Send, TrendingUp, User } from 'lucide-react';

import { useState } from 'react';

import { Head, useForm } from '@inertiajs/react';

import { AdminAreaChart, AdminBarChart } from '@/Components/admin/AdminCharts';
import {
  AdminStatsGrid,
  type StatCard,
} from '@/Components/admin/AdminStatsGrid';
import PageHeader from '@/Components/layout/PageHeader';
import { Button } from '@/Components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/Components/ui/card';
import { ConfirmDialog } from '@/Components/ui/confirm-dialog';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/Components/ui/select';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table';
import { Textarea } from '@/Components/ui/textarea';
import AdminLayout from '@/Layouts/AdminLayout';
import { formatDate, formatRelativeTime } from '@/lib/format';
import type { AdminNotificationsDashboardProps } from '@/types/admin';

const recipientLabels: Record<'all' | 'admins', string> = {
  all: 'All users',
  admins: 'Admins only',
};

export default function NotificationsDashboard({
  stats,
  volume_chart,
  recent_notifications,
}: AdminNotificationsDashboardProps) {
  const { data, setData, post, processing, errors, reset } = useForm({
    subject: '',
    body: '',
    recipient: 'all' as 'all' | 'admins',
  });
  const [showConfirm, setShowConfirm] = useState(false);

  function handleSend(e: React.FormEvent) {
    e.preventDefault();
    setShowConfirm(true);
  }

  function handleConfirm(): Promise<void> {
    return new Promise((resolve, reject) => {
      post('/admin/notifications/send', {
        onSuccess: () => {
          reset();
          resolve();
        },
        onError: () => reject(),
      });
    });
  }

  return (
    <AdminLayout>
      <Head title="Admin - Notifications" />
      <PageHeader
        title="Notifications"
        subtitle="Delivery stats and read rates"
      />

      <div className="container py-8 space-y-8">
        {/* Stats */}
        <AdminStatsGrid
          stats={
            [
              { title: 'Total Sent', value: stats.total_sent, icon: Mail },
              { title: 'Unread', value: stats.unread, icon: Bell },
              {
                title: 'Read Rate',
                value: stats.read_rate,
                icon: CheckCircle,
                format: (n) => `${n}%`,
              },
              {
                title: 'Sent Last 7d',
                value: stats.sent_last_7d,
                icon: TrendingUp,
              },
            ] satisfies StatCard[]
          }
        />

        <div className="grid gap-6 md:grid-cols-2">
          {/* Volume Chart */}
          <Card>
            <CardHeader>
              <CardTitle>Notification Volume (14d)</CardTitle>
              <CardDescription>Notifications sent per day</CardDescription>
            </CardHeader>
            <CardContent>
              <AdminAreaChart
                data={volume_chart}
                dataKey="count"
                name="Sent"
                gradientId="notifGradient"
                emptyIcon={Mail}
                emptyTitle="No data yet"
                emptyDescription="Notification volume will appear here."
              />
            </CardContent>
          </Card>

          {/* By Type */}
          <Card>
            <CardHeader>
              <CardTitle>By Type</CardTitle>
              <CardDescription>Notifications grouped by type</CardDescription>
            </CardHeader>
            <CardContent>
              <AdminBarChart
                data={stats.by_type}
                dataKey="count"
                xAxisKey="type"
                name="Count"
                emptyIcon={Bell}
                emptyTitle="No data"
                emptyDescription="Type breakdown will appear here."
              />
            </CardContent>
          </Card>
        </div>

        {/* Recent Notification History */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Bell className="h-4 w-4" />
              Recent Notifications
            </CardTitle>
            <CardDescription>
              Latest {recent_notifications.length} notifications delivered to users
            </CardDescription>
          </CardHeader>
          <CardContent>
            {recent_notifications.length === 0 ? (
              <div className="flex flex-col items-center justify-center py-10 text-muted-foreground gap-2">
                <Bell className="h-8 w-8 opacity-40" />
                <p className="text-sm">No notifications sent yet.</p>
              </div>
            ) : (
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>User</TableHead>
                    <TableHead>Type</TableHead>
                    <TableHead>Subject</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead className="text-right">Sent</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {recent_notifications.map((n) => (
                    <TableRow key={n.id}>
                      <TableCell>
                        {n.user_name ? (
                          <div className="flex items-center gap-2">
                            <User className="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                            <div>
                              <div className="font-medium text-sm">{n.user_name}</div>
                              <div className="text-xs text-muted-foreground">{n.user_email}</div>
                            </div>
                          </div>
                        ) : (
                          <span className="text-muted-foreground text-sm">—</span>
                        )}
                      </TableCell>
                      <TableCell>
                        <span className="text-xs font-mono text-muted-foreground">{n.type}</span>
                      </TableCell>
                      <TableCell className="max-w-xs truncate text-sm">
                        {n.subject ?? <span className="text-muted-foreground">—</span>}
                      </TableCell>
                      <TableCell>
                        {n.read_at ? (
                          <span className="inline-flex items-center gap-1 text-xs text-green-600 dark:text-green-400">
                            <CheckCircle className="h-3 w-3" />
                            Read
                          </span>
                        ) : (
                          <span className="inline-flex items-center gap-1 text-xs text-muted-foreground">
                            <Bell className="h-3 w-3" />
                            Unread
                          </span>
                        )}
                      </TableCell>
                      <TableCell className="text-right text-xs text-muted-foreground whitespace-nowrap">
                        <span title={formatDate(n.created_at)}>
                          {formatRelativeTime(n.created_at)}
                        </span>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            )}
          </CardContent>
        </Card>

        {/* Compose Announcement */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Send className="h-4 w-4" />
              Send Announcement
            </CardTitle>
            <CardDescription>
              Send an in-app notification to a user segment. Messages appear in the
              user's notification inbox.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSend} className="space-y-4 max-w-lg">
              <div className="space-y-1.5">
                <Label htmlFor="recipient">Recipients</Label>
                <Select
                  value={data.recipient}
                  onValueChange={(v) => setData('recipient', v as 'all' | 'admins')}
                >
                  <SelectTrigger id="recipient">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All users</SelectItem>
                    <SelectItem value="admins">Admins only</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div className="space-y-1.5">
                <Label htmlFor="subject">Subject *</Label>
                <Input
                  id="subject"
                  value={data.subject}
                  onChange={(e) => setData('subject', e.target.value)}
                  placeholder="Brief subject line..."
                  required
                  aria-describedby={errors.subject ? 'subject-error' : undefined}
                />
                {errors.subject && (
                  <p id="subject-error" className="text-xs text-destructive">
                    {errors.subject}
                  </p>
                )}
              </div>

              <div className="space-y-1.5">
                <Label htmlFor="body">Message *</Label>
                <Textarea
                  id="body"
                  rows={5}
                  value={data.body}
                  onChange={(e) => setData('body', e.target.value)}
                  placeholder="Write your announcement..."
                  required
                  aria-describedby={errors.body ? 'body-error' : undefined}
                />
                {errors.body && (
                  <p id="body-error" className="text-xs text-destructive">
                    {errors.body}
                  </p>
                )}
              </div>

              <Button type="submit" disabled={processing}>
                <Send className="mr-2 h-4 w-4" />
                {processing ? 'Sending...' : 'Send Announcement'}
              </Button>
            </form>
          </CardContent>
        </Card>
      </div>

      <ConfirmDialog
        open={showConfirm}
        onOpenChange={(open) => !processing && setShowConfirm(open)}
        onConfirm={handleConfirm}
        title="Send Announcement"
        description={
          <span>
            This will send an in-app notification to{' '}
            <strong>{recipientLabels[data.recipient]}</strong>.
            <span className="mt-3 block space-y-1 rounded-md bg-muted p-3 text-foreground">
              <span className="block font-medium">{data.subject}</span>
              <span className="block text-sm font-normal text-muted-foreground line-clamp-4">
                {data.body}
              </span>
            </span>
          </span>
        }
        confirmLabel="Send"
        loadingLabel="Sending..."
      />
    </AdminLayout>
  );
}
