import { ArrowLeft, Trash2 } from 'lucide-react';

import { useState } from 'react';

import { Head, Link, router, useForm, usePage } from '@inertiajs/react';

import PageHeader from '@/Components/layout/PageHeader';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/Components/ui/card';
import { ConfirmDialog } from '@/Components/ui/confirm-dialog';
import { Label } from '@/Components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';
import AdminLayout from '@/Layouts/AdminLayout';
import { formatDate } from '@/lib/format';
import type { PageProps } from '@/types';
import type { AdminFeedbackShowProps } from '@/types/admin';

const statusVariant: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
  open: 'default',
  in_review: 'secondary',
  resolved: 'outline',
  declined: 'destructive',
};

const priorityVariant: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
  low: 'outline',
  medium: 'secondary',
  high: 'destructive',
};

export default function AdminFeedbackShow({ feedback }: AdminFeedbackShowProps) {
  const isSuperAdmin = usePage<PageProps>().props.auth.user?.is_super_admin ?? false;
  const [deleteConfirmOpen, setDeleteConfirmOpen] = useState(false);

  const { data, setData, patch, processing } = useForm({
    status: feedback.status,
    priority: feedback.priority,
    admin_notes: feedback.admin_notes ?? '',
  });

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    patch(`/admin/feedback/${feedback.id}`);
  }

  function handleDelete(): Promise<void> {
    return new Promise((resolve, reject) => {
      router.delete(`/admin/feedback/${feedback.id}`, {
        onSuccess: () => resolve(),
        onError: () => reject(),
      });
    });
  }

  return (
    <AdminLayout>
      <Head title={`Feedback #${feedback.id}`} />
      <PageHeader
        title={`Feedback #${feedback.id}`}
        subtitle={`Submitted ${formatDate(feedback.created_at)}`}
        actions={
          <div className="flex gap-2">
            <Button variant="outline" size="sm" asChild>
              <Link href="/admin/feedback">
                <ArrowLeft className="mr-2 h-4 w-4" />
                Back to Inbox
              </Link>
            </Button>
            {isSuperAdmin && (
              <Button
                variant="destructive"
                size="sm"
                onClick={() => setDeleteConfirmOpen(true)}
              >
                <Trash2 className="mr-2 h-4 w-4" />
                Delete
              </Button>
            )}
          </div>
        }
      />

      <div className="container py-6 grid gap-6 md:grid-cols-3">
        {/* Left: Feedback content */}
        <div className="md:col-span-2 space-y-6">
          <Card>
            <CardHeader>
              <div className="flex items-center gap-2 flex-wrap">
                <Badge variant="outline">{feedback.type}</Badge>
                <Badge variant={priorityVariant[feedback.priority] ?? 'outline'}>
                  {feedback.priority} priority
                </Badge>
                <Badge variant={statusVariant[feedback.status] ?? 'outline'}>
                  {feedback.status.replace('_', ' ')}
                </Badge>
              </div>
            </CardHeader>
            <CardContent>
              <p className="text-sm leading-relaxed whitespace-pre-wrap">{feedback.message}</p>
            </CardContent>
          </Card>

          {/* Submitter info */}
          <Card>
            <CardHeader>
              <CardTitle className="text-base">Submitter</CardTitle>
            </CardHeader>
            <CardContent>
              {feedback.user ? (
                <div className="space-y-1 text-sm">
                  <div className="font-medium">{feedback.user.name}</div>
                  <div className="text-muted-foreground">{feedback.user.email}</div>
                  <Link
                    href={`/admin/users/${feedback.user.id}`}
                    className="text-primary text-xs hover:underline"
                  >
                    View user profile →
                  </Link>
                </div>
              ) : (
                <p className="text-sm text-muted-foreground">Guest submission (no account)</p>
              )}
            </CardContent>
          </Card>
        </div>

        {/* Right: Admin controls */}
        <div className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle className="text-base">Update Feedback</CardTitle>
              <CardDescription>Change status, priority, or add notes</CardDescription>
            </CardHeader>
            <CardContent>
              <form onSubmit={handleSubmit} className="space-y-4">
                <div className="space-y-1.5">
                  <Label htmlFor="status">Status</Label>
                  <Select
                    value={data.status}
                    onValueChange={(v) => setData('status', v)}
                  >
                    <SelectTrigger id="status">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="open">Open</SelectItem>
                      <SelectItem value="in_review">In Review</SelectItem>
                      <SelectItem value="resolved">Resolved</SelectItem>
                      <SelectItem value="declined">Declined</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <div className="space-y-1.5">
                  <Label htmlFor="priority">Priority</Label>
                  <Select
                    value={data.priority}
                    onValueChange={(v) => setData('priority', v)}
                  >
                    <SelectTrigger id="priority">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="low">Low</SelectItem>
                      <SelectItem value="medium">Medium</SelectItem>
                      <SelectItem value="high">High</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <div className="space-y-1.5">
                  <Label htmlFor="admin_notes">Admin Notes</Label>
                  <Textarea
                    id="admin_notes"
                    rows={4}
                    placeholder="Internal notes visible only to admins..."
                    value={data.admin_notes}
                    onChange={(e) => setData('admin_notes', e.target.value)}
                  />
                </div>

                <Button type="submit" className="w-full" disabled={processing}>
                  {processing ? 'Saving...' : 'Save Changes'}
                </Button>
              </form>
            </CardContent>
          </Card>

          {feedback.resolved_at && (
            <Card>
              <CardContent className="pt-6">
                <p className="text-xs text-muted-foreground">
                  Resolved {formatDate(feedback.resolved_at)}
                </p>
              </CardContent>
            </Card>
          )}
        </div>
      </div>

      <ConfirmDialog
        open={deleteConfirmOpen}
        onOpenChange={(open) => !open && setDeleteConfirmOpen(false)}
        onConfirm={handleDelete}
        title="Delete Feedback"
        description="This will permanently delete this feedback submission. This action cannot be undone."
        confirmLabel="Delete"
        loadingLabel="Deleting..."
        variant="destructive"
      />
    </AdminLayout>
  );
}
