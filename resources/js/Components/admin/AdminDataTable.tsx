import type { LucideIcon } from "lucide-react";

import type { ReactNode } from "react";

import { Card, CardContent } from "@/Components/ui/card";
import { EmptyState } from "@/Components/ui/empty-state";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/Components/ui/select";

import { AdminPagination } from "./AdminPagination";

interface PaginationData {
  current_page: number;
  last_page: number;
  from: number | null;
  to: number | null;
  total: number;
}

interface AdminDataTableProps {
  /** The table content (Table + TableHeader + TableBody). */
  children: ReactNode;
  /** Whether the data set is empty. */
  isEmpty: boolean;
  /** Whether a navigation request is in-flight (fades the table). */
  isNavigating?: boolean;
  /** Pagination data from Laravel's LengthAwarePaginator. */
  pagination: PaginationData;
  /** Called when the user clicks a pagination page. */
  onPage: (page: number) => void;
  /** Label for the pagination summary (e.g. "users", "entries"). */
  paginationLabel?: string;
  /** Empty state config. */
  emptyIcon?: LucideIcon;
  emptyTitle?: string;
  emptyDescription?: string;
  /** Optional action to show in the empty state (e.g. "Clear filters" button). */
  emptyAction?: ReactNode;
  /** Extra classes on the Card wrapper. */
  className?: string;
  /** Current per-page value for the selector. Omit to hide the selector. */
  perPage?: number;
  /** Called when the user changes the per-page select. */
  onPerPageChange?: (value: number) => void;
  /** Accessible label for the data region (e.g. "Users table"). */
  "aria-label"?: string;
}

/**
 * Shared wrapper for admin list pages that need:
 * - Card container with zero-padding content
 * - Empty state when data is absent
 * - Navigation fade while loading
 * - Overflow-x scrolling
 * - AdminPagination below
 */
export function AdminDataTable({
  children,
  isEmpty,
  isNavigating = false,
  pagination,
  onPage,
  paginationLabel = "items",
  emptyIcon,
  emptyTitle = "No results found",
  emptyDescription,
  emptyAction,
  className,
  perPage,
  onPerPageChange,
  "aria-label": ariaLabel,
}: AdminDataTableProps) {
  return (
    <>
      <Card className={className} role="region" aria-label={ariaLabel}>
        <CardContent className="p-0">
          {isEmpty ? (
            <EmptyState
              icon={emptyIcon}
              title={emptyTitle}
              description={emptyDescription}
              action={emptyAction}
            />
          ) : (
            <div
              className={`overflow-x-auto transition-opacity ${isNavigating ? "opacity-50" : ""}`}
              aria-busy={isNavigating}
            >
              {children}
            </div>
          )}
        </CardContent>
      </Card>

      <div className="flex items-center justify-between flex-wrap gap-3">
        <AdminPagination
          currentPage={pagination.current_page}
          lastPage={pagination.last_page}
          from={pagination.from}
          to={pagination.to}
          total={pagination.total}
          onPage={onPage}
          label={paginationLabel}
        />
        {perPage !== undefined && onPerPageChange && (
          <div className="flex items-center gap-2 text-sm text-muted-foreground">
            <span>Rows per page:</span>
            <Select
              value={String(perPage)}
              onValueChange={(v) => onPerPageChange(Number(v))}
            >
              <SelectTrigger className="h-8 w-[70px]" aria-label="Rows per page">
                <SelectValue />
              </SelectTrigger>
              <SelectContent side="top">
                {[10, 25, 50, 100].map((n) => (
                  <SelectItem key={n} value={String(n)}>
                    {n}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
        )}
      </div>
    </>
  );
}
