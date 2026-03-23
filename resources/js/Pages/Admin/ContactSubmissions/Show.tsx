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
import AdminLayout from '@/Layouts/AdminLayout';
import { formatDate } from '@/lib/format';
import type { PageProps } from '@/types';
import type { AdminContactSubmissionShowProps } from '@/types/admin';

const statusVariant: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
  new: 'default',
  replied: 'outline',
  spam: 'destructive',
};

export default function AdminContactSubmissionShow({ submission }: AdminContactSubmissionShowProps) {
  const isSuperAdmin = usePage<PageProps>().props.auth.user?.is_super_admin ?? false;
  const [deleteConfirmOpen, setDeleteConfirmOpen] = useState(false);

  const { data, setData, patch, processing } = useForm({
    status: submission.status,
  });

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    patch(`/admin/contact-submissions/${submission.id}`);
  }

  function handleDelete(): Promise<void> {
    return new Promise((resolve, reject) => {
      router.delete(`/admin/contact-submissions/${submission.id}`, {
        onSuccess: () => resolve(),
        onError: () => reject(),
      });
    });
  }

  return (
    <AdminLayout>
      <Head title={`Contact #${submission.id}`} />
      <PageHeader
        title={`Contact #${submission.id}`}
        subtitle={`Submitted ${formatDate(submission.created_at)}`}
        actions={
          <div className="flex gap-2">
            <Button variant="outline" size="sm" asChild>
              <Link href="/admin/contact-submissions">
                <ArrowLeft className="mr-2 h-4 w-4" />
                Back to Submissions
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
        {/* Left: Submission content */}
        <div className="md:col-span-2 space-y-6">
          <Card>
            <CardHeader>
              <div className="flex items-center gap-2 flex-wrap">
                <Badge variant={statusVariant[submission.status] ?? 'outline'}>
                  {submission.status}
                </Badge>
                {submission.replied_at && (
                  <span className="text-xs text-muted-foreground">
                    Replied {formatDate(submission.replied_at)}
                  </span>
                )}
              </div>
              <CardTitle className="text-base">{submission.subject}</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-sm leading-relaxed whitespace-pre-wrap">{submission.message}</p>
            </CardContent>
          </Card>

          {/* Submitter info */}
          <Card>
            <CardHeader>
              <CardTitle className="text-base">Submitter</CardTitle>
            </CardHeader>
            <CardContent className="space-y-1 text-sm">
              <div className="font-medium">{submission.name}</div>
              <div className="text-muted-foreground">{submission.email}</div>
            </CardContent>
          </Card>
        </div>

        {/* Right: Admin controls */}
        <div className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle className="text-base">Update Status</CardTitle>
              <CardDescription>Mark as replied or flag as spam</CardDescription>
            </CardHeader>
            <CardContent>
              <form onSubmit={handleSubmit} className="space-y-4">
                <div className="space-y-1.5">
                  <Label htmlFor="status">Status</Label>
                  <Select
                    value={data.status}
                    onValueChange={(v) => setData('status', v as 'new' | 'replied' | 'spam')}
                  >
                    <SelectTrigger id="status">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="new">New</SelectItem>
                      <SelectItem value="replied">Replied</SelectItem>
                      <SelectItem value="spam">Spam</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <Button type="submit" className="w-full" disabled={processing}>
                  {processing ? 'Saving...' : 'Save Changes'}
                </Button>
              </form>
            </CardContent>
          </Card>
        </div>
      </div>

      <ConfirmDialog
        open={deleteConfirmOpen}
        onOpenChange={(open) => !open && setDeleteConfirmOpen(false)}
        onConfirm={handleDelete}
        title="Delete Submission"
        description="This will permanently delete this contact submission. This action cannot be undone."
        confirmLabel="Delete"
        loadingLabel="Deleting..."
        variant="destructive"
      />
    </AdminLayout>
  );
}
