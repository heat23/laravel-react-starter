import { ArrowLeft } from 'lucide-react';

import { Head, Link, useForm } from '@inertiajs/react';

import PageHeader from '@/Components/layout/PageHeader';
import { Button } from '@/Components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
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

export default function AdminRoadmapCreate() {
  const { data, setData, post, processing, errors } = useForm({
    title: '',
    description: '',
    status: 'planned' as const,
    display_order: 0,
  });

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    post('/admin/roadmap');
  }

  return (
    <AdminLayout>
      <Head title="New Roadmap Entry" />
      <PageHeader
        title="New Roadmap Entry"
        subtitle="Create a new entry to share your product direction"
        actions={
          <Button variant="outline" size="sm" asChild>
            <Link href="/admin/roadmap">
              <ArrowLeft className="mr-2 h-4 w-4" />
              Back to Roadmap
            </Link>
          </Button>
        }
      />

      <div className="container py-6 max-w-xl">
        <Card>
          <CardHeader>
            <CardTitle>Entry Details</CardTitle>
            <CardDescription>
              This will be visible to users on the public roadmap.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="space-y-1.5">
                <Label htmlFor="title">Title *</Label>
                <Input
                  id="title"
                  value={data.title}
                  onChange={(e) => setData('title', e.target.value)}
                  placeholder="e.g. Dark mode support"
                  required
                  aria-describedby={errors.title ? 'title-error' : undefined}
                />
                {errors.title && (
                  <p id="title-error" className="text-xs text-destructive">
                    {errors.title}
                  </p>
                )}
              </div>

              <div className="space-y-1.5">
                <Label htmlFor="description">Description</Label>
                <Textarea
                  id="description"
                  rows={4}
                  value={data.description}
                  onChange={(e) => setData('description', e.target.value)}
                  placeholder="Optional details about this feature or improvement..."
                />
                {errors.description && (
                  <p className="text-xs text-destructive">{errors.description}</p>
                )}
              </div>

              <div className="space-y-1.5">
                <Label htmlFor="status">Status *</Label>
                <Select
                  value={data.status}
                  onValueChange={(v) =>
                    setData('status', v as 'planned' | 'in_progress' | 'completed')
                  }
                >
                  <SelectTrigger id="status">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="planned">Planned</SelectItem>
                    <SelectItem value="in_progress">In Progress</SelectItem>
                    <SelectItem value="completed">Completed</SelectItem>
                  </SelectContent>
                </Select>
                {errors.status && (
                  <p className="text-xs text-destructive">{errors.status}</p>
                )}
              </div>

              <div className="space-y-1.5">
                <Label htmlFor="display_order">Display Order</Label>
                <Input
                  id="display_order"
                  type="number"
                  min={0}
                  value={data.display_order}
                  onChange={(e) => setData('display_order', Number(e.target.value))}
                />
                <p className="text-xs text-muted-foreground">
                  Lower numbers appear first within each column.
                </p>
              </div>

              <div className="flex justify-end gap-2 pt-2">
                <Button variant="outline" type="button" asChild>
                  <Link href="/admin/roadmap">Cancel</Link>
                </Button>
                <Button type="submit" disabled={processing}>
                  {processing ? 'Creating...' : 'Create Entry'}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </AdminLayout>
  );
}
