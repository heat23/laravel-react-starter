import { Download, GripVertical, Map, Plus, Trash2 } from 'lucide-react';

import { useEffect, useRef, useState } from 'react';

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
import { useAdminFilters } from '@/hooks/useAdminFilters';
import { useAdminKeyboardShortcuts } from '@/hooks/useAdminKeyboardShortcuts';
import AdminLayout from '@/Layouts/AdminLayout';
import { cn } from '@/lib/utils';
import { formatDate } from '@/lib/format';
import type { PageProps } from '@/types';
import type { AdminRoadmapIndexProps, RoadmapEntry, RoadmapFilters } from '@/types/admin';

type RoadmapStatus = 'planned' | 'in_progress' | 'completed';

const statusLabels: Record<RoadmapStatus, string> = {
  planned: 'Planned',
  in_progress: 'In Progress',
  completed: 'Completed',
};

const statusVariant: Record<RoadmapStatus, 'default' | 'secondary' | 'outline'> = {
  planned: 'secondary',
  in_progress: 'default',
  completed: 'outline',
};

const columns: RoadmapStatus[] = ['planned', 'in_progress', 'completed'];

function InlineEditForm({
  entry,
  onClose,
}: {
  entry: RoadmapEntry;
  onClose: () => void;
}) {
  const { data, setData, patch, processing, errors } = useForm({
    status: entry.status,
    description: entry.description ?? '',
    display_order: entry.display_order,
  });

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    patch(`/admin/roadmap/${entry.id}`, {
      onSuccess: () => onClose(),
    });
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-3 mt-4 border-t pt-4">
      <div className="space-y-1.5">
        <Label htmlFor={`status-${entry.id}`}>Status</Label>
        <Select
          value={data.status}
          onValueChange={(v) => setData('status', v as RoadmapStatus)}
        >
          <SelectTrigger id={`status-${entry.id}`}>
            <SelectValue />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="planned">Planned</SelectItem>
            <SelectItem value="in_progress">In Progress</SelectItem>
            <SelectItem value="completed">Completed</SelectItem>
          </SelectContent>
        </Select>
        {errors.status && <p className="text-xs text-destructive">{errors.status}</p>}
      </div>

      <div className="space-y-1.5">
        <Label htmlFor={`desc-${entry.id}`}>Description</Label>
        <Textarea
          id={`desc-${entry.id}`}
          rows={3}
          value={data.description}
          onChange={(e) => setData('description', e.target.value)}
        />
      </div>

      <div className="flex gap-2">
        <Button type="submit" size="sm" disabled={processing}>
          {processing ? 'Saving...' : 'Save'}
        </Button>
        <Button type="button" size="sm" variant="ghost" onClick={onClose}>
          Cancel
        </Button>
      </div>
    </form>
  );
}

export default function AdminRoadmapIndex({ entries, filters }: AdminRoadmapIndexProps) {
  const isSuperAdmin = usePage<PageProps>().props.auth.user?.is_super_admin ?? false;
  const [editingId, setEditingId] = useState<number | null>(null);
  const [deleteEntry, setDeleteEntry] = useState<RoadmapEntry | null>(null);
  const searchInputRef = useRef<HTMLInputElement>(null);

  // Drag-and-drop state
  const [orderedEntries, setOrderedEntries] = useState<RoadmapEntry[]>(entries.data);
  const [draggingId, setDraggingId] = useState<number | null>(null);
  const [dropTarget, setDropTarget] = useState<{ col: RoadmapStatus; beforeId: number | null } | null>(null);
  const dragRef = useRef<{ entry: RoadmapEntry; fromCol: RoadmapStatus } | null>(null);

  // Sync with server when entries prop refreshes
  useEffect(() => {
    setOrderedEntries(entries.data);
  }, [entries.data]);

  const { search, setSearch, updateFilter, clearFilters } =
    useAdminFilters<RoadmapFilters>({
      route: '/admin/roadmap',
      filters,
    });

  const hasFilters = !!(filters.search || filters.status);

  useAdminKeyboardShortcuts({
    onSearch: () => searchInputRef.current?.focus(),
  });

  function handleDelete(): Promise<void> {
    if (!deleteEntry) return Promise.resolve();
    return new Promise((resolve, reject) => {
      router.delete(`/admin/roadmap/${deleteEntry.id}`, {
        onSuccess: () => {
          setDeleteEntry(null);
          resolve();
        },
        onError: () => reject(),
      });
    });
  }

  // --- Drag-and-drop handlers ---

  function handleDragStart(e: React.DragEvent, entry: RoadmapEntry, col: RoadmapStatus) {
    dragRef.current = { entry, fromCol: col };
    setDraggingId(entry.id);
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', String(entry.id));
  }

  function handleDragEnd() {
    dragRef.current = null;
    setDraggingId(null);
    setDropTarget(null);
  }

  function handleDragOver(e: React.DragEvent, col: RoadmapStatus, beforeId: number | null) {
    e.preventDefault();
    e.stopPropagation();
    e.dataTransfer.dropEffect = 'move';
    setDropTarget({ col, beforeId });
  }

  function handleDrop(e: React.DragEvent, toCol: RoadmapStatus, beforeId: number | null) {
    e.preventDefault();
    e.stopPropagation();
    setDropTarget(null);

    if (!dragRef.current) return;
    const { entry, fromCol: _fromCol } = dragRef.current;
    dragRef.current = null;
    setDraggingId(null);

    // Remove entry from current position, insert at target
    let newEntries = orderedEntries.filter((e) => e.id !== entry.id);
    const updated: RoadmapEntry = { ...entry, status: toCol };

    if (beforeId === null) {
      newEntries = [...newEntries, updated];
    } else {
      const idx = newEntries.findIndex((e) => e.id === beforeId);
      if (idx === -1) {
        newEntries = [...newEntries, updated];
      } else {
        newEntries = [...newEntries.slice(0, idx), updated, ...newEntries.slice(idx)];
      }
    }

    // Assign sequential display_orders per column
    const items: Array<{ id: number; status: string; display_order: number }> = [];
    columns.forEach((col) => {
      newEntries
        .filter((e) => e.status === col)
        .forEach((e, i) => items.push({ id: e.id, status: col, display_order: i }));
    });

    const finalEntries = newEntries.map((e) => {
      const item = items.find((i) => i.id === e.id);
      return item ? { ...e, display_order: item.display_order } : e;
    });

    // Capture snapshot before optimistic update so revert is always correct
    const snapshot = orderedEntries;
    setOrderedEntries(finalEntries);

    router.post('/admin/roadmap/reorder', { items }, {
      preserveState: true,
      preserveScroll: true,
      onError: () => setOrderedEntries(snapshot),
    });
  }

  // Build grouped from orderedEntries (sort within column by display_order)
  const grouped = columns.reduce<Record<RoadmapStatus, RoadmapEntry[]>>((acc, col) => {
    acc[col] = orderedEntries
      .filter((e) => e.status === col)
      .sort((a, b) => a.display_order - b.display_order);
    return acc;
  }, { planned: [], in_progress: [], completed: [] });

  const subtitle = hasFilters
    ? `${entries.total} result${entries.total === 1 ? '' : 's'}`
    : `${entries.total} entr${entries.total === 1 ? 'y' : 'ies'}`;

  return (
    <AdminLayout>
      <Head title="Admin - Roadmap" />
      <PageHeader
        title="Roadmap"
        subtitle={subtitle}
        actions={
          <div className="flex items-center gap-2">
            <Button size="sm" variant="outline" asChild>
              <a href="/admin/roadmap/export">
                <Download className="mr-2 h-4 w-4" />
                Export CSV
              </a>
            </Button>
            <Button size="sm" asChild>
              <Link href="/admin/roadmap/create">
                <Plus className="mr-2 h-4 w-4" />
                New Entry
              </Link>
            </Button>
          </div>
        }
      />

      <div className="container py-6 space-y-4">
        <div className="flex flex-col sm:flex-row gap-3 flex-wrap">
          <Input
            ref={searchInputRef}
            className="max-w-xs"
            placeholder="Search title, description..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            aria-label="Search roadmap entries"
          />

          <Select
            value={filters.status ?? 'all'}
            onValueChange={(v) =>
              updateFilter({ status: v === 'all' ? undefined : v })
            }
          >
            <SelectTrigger className="w-40" aria-label="Filter by status">
              <SelectValue placeholder="All statuses" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All statuses</SelectItem>
              <SelectItem value="planned">Planned</SelectItem>
              <SelectItem value="in_progress">In Progress</SelectItem>
              <SelectItem value="completed">Completed</SelectItem>
            </SelectContent>
          </Select>

          {hasFilters && (
            <Button variant="ghost" size="sm" onClick={clearFilters}>
              Clear filters
            </Button>
          )}
        </div>

        {orderedEntries.length === 0 ? (
          <Card>
            <CardContent className="py-16 flex flex-col items-center gap-4">
              <Map className="h-12 w-12 text-muted-foreground/40" />
              <div className="text-center">
                {hasFilters ? (
                  <>
                    <p className="font-medium">No entries match the current filters</p>
                    <p className="text-sm text-muted-foreground">
                      Try adjusting your search or filter criteria.
                    </p>
                  </>
                ) : (
                  <>
                    <p className="font-medium">No roadmap entries</p>
                    <p className="text-sm text-muted-foreground">
                      Create your first entry to share your product direction with users.
                    </p>
                  </>
                )}
              </div>
              {hasFilters ? (
                <Button variant="outline" size="sm" onClick={clearFilters}>
                  Clear filters
                </Button>
              ) : (
                <Button asChild>
                  <Link href="/admin/roadmap/create">
                    <Plus className="mr-2 h-4 w-4" />
                    New Entry
                  </Link>
                </Button>
              )}
            </CardContent>
          </Card>
        ) : (
          <div className="grid gap-6 md:grid-cols-3">
            {columns.map((col) => (
              <div key={col} className="space-y-3">
                <div className="flex items-center gap-2">
                  <h2 className="font-semibold text-sm">{statusLabels[col]}</h2>
                  <Badge variant={statusVariant[col]} className="text-xs">
                    {grouped[col].length}
                  </Badge>
                </div>

                <div
                  className={cn(
                    'min-h-24 rounded-lg space-y-3 transition-colors',
                    draggingId !== null && dropTarget?.col === col && dropTarget?.beforeId === null
                      ? 'bg-primary/5 ring-2 ring-primary/30'
                      : draggingId !== null
                        ? 'ring-1 ring-dashed ring-muted-foreground/30'
                        : '',
                  )}
                  onDragOver={(e) => handleDragOver(e, col, null)}
                  onDrop={(e) => handleDrop(e, col, null)}
                >
                  {grouped[col].length === 0 ? (
                    <Card
                      className={cn(
                        'border-dashed transition-colors',
                        draggingId !== null && dropTarget?.col === col
                          ? 'border-primary bg-primary/5'
                          : '',
                      )}
                    >
                      <CardContent className="py-8 text-center text-sm text-muted-foreground">
                        {draggingId !== null ? 'Drop here' : 'No entries'}
                      </CardContent>
                    </Card>
                  ) : (
                    grouped[col].map((entry) => (
                      <div key={entry.id}>
                        {/* Drop indicator: shown above this card when it's the insert target */}
                        {draggingId !== null &&
                          dropTarget?.col === col &&
                          dropTarget?.beforeId === entry.id && (
                            <div className="h-1 rounded-full bg-primary mx-1 mb-2" aria-hidden />
                          )}
                        <Card
                          className={cn(
                            'group transition-opacity',
                            draggingId === entry.id && 'opacity-40',
                          )}
                          draggable
                          onDragStart={(e) => handleDragStart(e, entry, col)}
                          onDragEnd={handleDragEnd}
                          onDragOver={(e) => handleDragOver(e, col, entry.id)}
                          onDrop={(e) => handleDrop(e, col, entry.id)}
                        >
                          <CardHeader className="pb-2">
                            <div className="flex items-start justify-between gap-2">
                              <div className="flex items-start gap-1.5 min-w-0">
                                <GripVertical
                                  className="h-4 w-4 text-muted-foreground/40 shrink-0 mt-0.5 cursor-grab active:cursor-grabbing opacity-0 group-hover:opacity-100 transition-opacity"
                                  aria-hidden
                                />
                                <CardTitle className="text-base leading-snug">
                                  {entry.title}
                                </CardTitle>
                              </div>
                              {isSuperAdmin && (
                                <Button
                                  variant="ghost"
                                  size="icon"
                                  className="h-7 w-7 opacity-0 group-hover:opacity-100 shrink-0"
                                  aria-label="Delete entry"
                                  onClick={() => setDeleteEntry(entry)}
                                >
                                  <Trash2 className="h-4 w-4 text-destructive" />
                                </Button>
                              )}
                            </div>
                            {entry.description && (
                              <CardDescription className="text-xs line-clamp-3">
                                {entry.description}
                              </CardDescription>
                            )}
                          </CardHeader>
                          <CardContent className="pb-3">
                            <div className="flex items-center justify-between text-xs text-muted-foreground">
                              <span>{entry.feedback_submissions_count} upvotes</span>
                              <span>{formatDate(entry.created_at)}</span>
                            </div>

                            {editingId === entry.id ? (
                              <InlineEditForm
                                entry={entry}
                                onClose={() => setEditingId(null)}
                              />
                            ) : (
                              <Button
                                variant="ghost"
                                size="sm"
                                className="mt-2 h-7 text-xs"
                                onClick={() => setEditingId(entry.id)}
                              >
                                Edit
                              </Button>
                            )}
                          </CardContent>
                        </Card>
                      </div>
                    ))
                  )}
                </div>
              </div>
            ))}
          </div>
        )}

        {entries.last_page > 1 && (
          <div className="flex items-center justify-between text-sm text-muted-foreground pt-2">
            <span>
              Showing {entries.from ?? 0}–{entries.to ?? 0} of {entries.total} entries
            </span>
            <div className="flex items-center gap-1">
              <Button
                variant="outline"
                size="sm"
                disabled={entries.current_page === 1}
                onClick={() =>
                  router.get('/admin/roadmap', {
                    ...filters,
                    page: entries.current_page - 1,
                  }, { preserveState: true, replace: true })
                }
              >
                Previous
              </Button>
              <span className="px-2">
                {entries.current_page} / {entries.last_page}
              </span>
              <Button
                variant="outline"
                size="sm"
                disabled={entries.current_page === entries.last_page}
                onClick={() =>
                  router.get('/admin/roadmap', {
                    ...filters,
                    page: entries.current_page + 1,
                  }, { preserveState: true, replace: true })
                }
              >
                Next
              </Button>
            </div>
          </div>
        )}
      </div>

      <ConfirmDialog
        open={deleteEntry !== null}
        onOpenChange={(open) => !open && setDeleteEntry(null)}
        onConfirm={handleDelete}
        title="Delete Roadmap Entry"
        description="This will permanently delete this roadmap entry. Any feedback linked to it will lose the association."
        resourceName={deleteEntry?.title}
        resourceType="Entry"
        confirmLabel="Delete"
        loadingLabel="Deleting..."
        variant="destructive"
      />
    </AdminLayout>
  );
}
