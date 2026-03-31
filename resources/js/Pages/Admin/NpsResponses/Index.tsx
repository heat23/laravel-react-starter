import { Star } from 'lucide-react';

import { useRef } from 'react';

import { Head, router } from '@inertiajs/react';

import { AdminDataTable } from '@/Components/admin/AdminDataTable';
import { SortHeader } from '@/Components/admin/SortHeader';
import PageHeader from '@/Components/layout/PageHeader';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { ExportButton } from '@/Components/ui/export-button';
import { Input } from '@/Components/ui/input';
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
import { useAdminFilters } from '@/hooks/useAdminFilters';
import { useAdminKeyboardShortcuts } from '@/hooks/useAdminKeyboardShortcuts';
import { useNavigationState } from '@/hooks/useNavigationState';
import AdminLayout from '@/Layouts/AdminLayout';
import { formatDate } from '@/lib/format';
import type { AdminNpsResponsesIndexProps, NpsResponseFilters } from '@/types/admin';

const categoryVariant: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
  promoter: 'default',
  passive: 'secondary',
  detractor: 'destructive',
};

function NpsScoreDisplay({ score }: { score: number | null }) {
  if (score === null) {
    return <span className="text-muted-foreground text-sm">—</span>;
  }
  const color =
    score >= 50 ? 'text-green-600 dark:text-green-400' :
    score >= 0 ? 'text-yellow-600 dark:text-yellow-400' :
    'text-red-600 dark:text-red-400';
  return (
    <span className={`text-2xl font-bold tabular-nums ${color}`}>
      {score > 0 ? `+${score}` : score}
    </span>
  );
}

export default function AdminNpsResponsesIndex({
  responses,
  filters,
  summary,
  surveyTriggers,
}: AdminNpsResponsesIndexProps) {
  const { search, setSearch, updateFilter, handleSort, handlePage, clearFilters } =
    useAdminFilters<NpsResponseFilters>({
      route: '/admin/nps-responses',
      filters,
    });

  const isNavigating = useNavigationState();
  const searchInputRef = useRef<HTMLInputElement>(null);

  const currentPage = responses.current_page;
  const lastPage = responses.last_page;

  useAdminKeyboardShortcuts({
    onSearch: () => searchInputRef.current?.focus(),
    onNextPage: currentPage < lastPage ? () => handlePage(currentPage + 1) : undefined,
    onPrevPage: currentPage > 1 ? () => handlePage(currentPage - 1) : undefined,
  });

  const exportParams: Record<string, string> = {};
  if (filters.category) exportParams.category = filters.category;
  if (filters.survey_trigger) exportParams.survey_trigger = filters.survey_trigger;
  if (filters.search) exportParams.search = filters.search;

  const hasFilters = !!(filters.category || filters.survey_trigger || filters.search);

  return (
    <AdminLayout>
      <Head title="NPS Responses" />
      <PageHeader
        title="NPS Responses"
        subtitle={`${summary.total} total · ${summary.promoters} promoters · ${summary.passives} passives · ${summary.detractors} detractors`}
        actions={
          <ExportButton href="/admin/nps-responses/export" params={exportParams} label="Export CSV" />
        }
      />

      <div className="container py-8 space-y-4">
        {/* NPS Score Summary */}
        <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <div className="rounded-lg border bg-card p-4 text-center">
            <div className="text-xs text-muted-foreground mb-1">NPS Score</div>
            <NpsScoreDisplay score={summary.nps_score} />
          </div>
          <div className="rounded-lg border bg-card p-4 text-center">
            <button
              type="button"
              className="w-full text-center focus:outline-none focus-visible:ring-2 focus-visible:ring-ring rounded"
              onClick={() => updateFilter({ category: filters.category === 'promoter' ? undefined : 'promoter' })}
              aria-label="Filter by promoters"
            >
              <div className="text-xs text-muted-foreground mb-1">Promoters (9–10)</div>
              <div className="text-2xl font-bold text-green-600 dark:text-green-400 tabular-nums">
                {summary.promoters}
              </div>
            </button>
          </div>
          <div className="rounded-lg border bg-card p-4 text-center">
            <button
              type="button"
              className="w-full text-center focus:outline-none focus-visible:ring-2 focus-visible:ring-ring rounded"
              onClick={() => updateFilter({ category: filters.category === 'passive' ? undefined : 'passive' })}
              aria-label="Filter by passives"
            >
              <div className="text-xs text-muted-foreground mb-1">Passives (7–8)</div>
              <div className="text-2xl font-bold text-yellow-600 dark:text-yellow-400 tabular-nums">
                {summary.passives}
              </div>
            </button>
          </div>
          <div className="rounded-lg border bg-card p-4 text-center">
            <button
              type="button"
              className="w-full text-center focus:outline-none focus-visible:ring-2 focus-visible:ring-ring rounded"
              onClick={() => updateFilter({ category: filters.category === 'detractor' ? undefined : 'detractor' })}
              aria-label="Filter by detractors"
            >
              <div className="text-xs text-muted-foreground mb-1">Detractors (0–6)</div>
              <div className="text-2xl font-bold text-red-600 dark:text-red-400 tabular-nums">
                {summary.detractors}
              </div>
            </button>
          </div>
        </div>

        {/* Filters */}
        <div className="flex flex-col sm:flex-row gap-3 flex-wrap">
          <Input
            ref={searchInputRef}
            className="max-w-xs"
            placeholder="Search comment, user..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            aria-label="Search NPS responses"
          />

          <Select
            value={filters.category ?? 'all'}
            onValueChange={(v) =>
              updateFilter({ category: v === 'all' ? undefined : v })
            }
          >
            <SelectTrigger className="w-40" aria-label="Filter by category">
              <SelectValue placeholder="Category" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All categories</SelectItem>
              <SelectItem value="promoter">Promoter (9–10)</SelectItem>
              <SelectItem value="passive">Passive (7–8)</SelectItem>
              <SelectItem value="detractor">Detractor (0–6)</SelectItem>
            </SelectContent>
          </Select>

          {surveyTriggers.length > 0 && (
            <Select
              value={filters.survey_trigger ?? 'all'}
              onValueChange={(v) =>
                updateFilter({ survey_trigger: v === 'all' ? undefined : v })
              }
            >
              <SelectTrigger className="w-44" aria-label="Filter by survey trigger">
                <SelectValue placeholder="Survey trigger" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All triggers</SelectItem>
                {surveyTriggers.map((t) => (
                  <SelectItem key={t} value={t}>
                    {t.replace(/_/g, ' ')}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          )}

          {hasFilters && (
            <Button variant="ghost" size="sm" onClick={clearFilters}>
              Clear filters
            </Button>
          )}
        </div>

        <AdminDataTable
          isEmpty={responses.data.length === 0}
          isNavigating={isNavigating}
          pagination={responses}
          onPage={handlePage}
          paginationLabel="NPS responses"
          emptyIcon={Star}
          emptyTitle="No NPS responses found"
          emptyDescription={
            hasFilters
              ? 'No responses match the current filters.'
              : 'No NPS survey responses yet.'
          }
          emptyAction={
            hasFilters ? (
              <Button variant="outline" size="sm" onClick={clearFilters}>
                Clear filters
              </Button>
            ) : undefined
          }
        >
          <Table>
            <TableHeader>
              <TableRow>
                <SortHeader column="score" label="Score" currentSort={filters.sort} currentDir={filters.dir} onSort={handleSort} />
                <TableHead>Category</TableHead>
                <TableHead>User</TableHead>
                <TableHead>Comment</TableHead>
                <TableHead>Trigger</TableHead>
                <SortHeader column="created_at" label="Date" currentSort={filters.sort} currentDir={filters.dir} onSort={handleSort} />
              </TableRow>
            </TableHeader>
            <TableBody>
              {responses.data.map((item) => (
                <TableRow key={item.id}>
                  <TableCell>
                    <span className="text-lg font-semibold tabular-nums">{item.score}</span>
                  </TableCell>
                  <TableCell>
                    <Badge variant={categoryVariant[item.category] ?? 'outline'}>
                      {item.category}
                    </Badge>
                  </TableCell>
                  <TableCell className="text-sm">
                    {item.user ? (
                      <div>
                        <div className="font-medium">{item.user.name}</div>
                        <div className="text-muted-foreground text-xs">{item.user.email}</div>
                      </div>
                    ) : (
                      <span className="text-muted-foreground">[Deleted User]</span>
                    )}
                  </TableCell>
                  <TableCell className="max-w-xs text-sm">
                    {item.comment ? (
                      <span className="line-clamp-2">{item.comment}</span>
                    ) : (
                      <span className="text-muted-foreground italic">No comment</span>
                    )}
                  </TableCell>
                  <TableCell>
                    <Badge variant="outline">
                      {item.survey_trigger.replace(/_/g, ' ')}
                    </Badge>
                  </TableCell>
                  <TableCell className="text-xs text-muted-foreground whitespace-nowrap">
                    {formatDate(item.created_at)}
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </AdminDataTable>
      </div>
    </AdminLayout>
  );
}
