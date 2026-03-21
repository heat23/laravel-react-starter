import { MessageSquare } from 'lucide-react';

import { Head, Link, router } from '@inertiajs/react';

import PageHeader from '@/Components/layout/PageHeader';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
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
import AdminLayout from '@/Layouts/AdminLayout';
import { formatDate } from '@/lib/format';

interface FeedbackItem {
  id: number;
  type: string;
  status: string;
  priority: string;
  message: string;
  created_at: string;
  user: { name: string; email: string } | null;
}

interface Paginated<T> {
  data: T[];
  current_page: number;
  last_page: number;
  links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface Props {
  feedback: Paginated<FeedbackItem>;
  filters: { type?: string; status?: string };
  counts: { open: number; in_review: number; resolved: number };
}

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

export default function AdminFeedbackIndex({ feedback, filters, counts }: Props) {
  function setFilter(key: string, value: string) {
    router.get('/admin/feedback', { ...filters, [key]: value === 'all' ? undefined : value }, { preserveState: true, replace: true });
  }

  return (
    <AdminLayout>
      <Head title="Feedback Inbox" />
      <PageHeader
        title="Feedback Inbox"
        subtitle={`${counts.open} open · ${counts.in_review} in review · ${counts.resolved} resolved`}
        icon={MessageSquare}
      />

      <div className="container py-6 space-y-4">
        <div className="flex gap-2">
          <Select value={filters.type ?? 'all'} onValueChange={(v) => setFilter('type', v)}>
            <SelectTrigger className="w-36">
              <SelectValue placeholder="Type" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All types</SelectItem>
              <SelectItem value="bug">Bug</SelectItem>
              <SelectItem value="feature">Feature</SelectItem>
              <SelectItem value="general">General</SelectItem>
            </SelectContent>
          </Select>

          <Select value={filters.status ?? 'all'} onValueChange={(v) => setFilter('status', v)}>
            <SelectTrigger className="w-36">
              <SelectValue placeholder="Status" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All statuses</SelectItem>
              <SelectItem value="open">Open</SelectItem>
              <SelectItem value="in_review">In Review</SelectItem>
              <SelectItem value="resolved">Resolved</SelectItem>
              <SelectItem value="declined">Declined</SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div className="rounded-md border">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Type</TableHead>
                <TableHead>User</TableHead>
                <TableHead>Message</TableHead>
                <TableHead>Priority</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Date</TableHead>
                <TableHead />
              </TableRow>
            </TableHeader>
            <TableBody>
              {feedback.data.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={7} className="text-center text-muted-foreground py-8">
                    No feedback submissions found.
                  </TableCell>
                </TableRow>
              ) : (
                feedback.data.map((item) => (
                  <TableRow key={item.id}>
                    <TableCell>
                      <Badge variant="outline">{item.type}</Badge>
                    </TableCell>
                    <TableCell className="text-sm">
                      {item.user ? (
                        <div>
                          <div className="font-medium">{item.user.name}</div>
                          <div className="text-muted-foreground text-xs">{item.user.email}</div>
                        </div>
                      ) : (
                        <span className="text-muted-foreground">Guest</span>
                      )}
                    </TableCell>
                    <TableCell className="max-w-xs truncate text-sm">
                      {item.message}
                    </TableCell>
                    <TableCell>
                      <Badge variant={priorityVariant[item.priority] ?? 'outline'}>
                        {item.priority}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      <Badge variant={statusVariant[item.status] ?? 'outline'}>
                        {item.status.replace('_', ' ')}
                      </Badge>
                    </TableCell>
                    <TableCell className="text-xs text-muted-foreground">
                      {formatDate(item.created_at)}
                    </TableCell>
                    <TableCell>
                      <Button variant="ghost" size="sm" asChild>
                        <Link href={`/admin/feedback/${item.id}`}>View</Link>
                      </Button>
                    </TableCell>
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>
        </div>
      </div>
    </AdminLayout>
  );
}
