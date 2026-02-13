import type { LucideIcon } from "lucide-react";

import type { ReactNode } from "react";

import { Card, CardContent } from "@/Components/ui/card";
import { EmptyState } from "@/Components/ui/empty-state";

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
}: AdminDataTableProps) {
  return (
    <>
      <Card className={className}>
        <CardContent className="p-0">
          {isEmpty ? (
            <EmptyState
              icon={emptyIcon}
              title={emptyTitle}
              description={emptyDescription}
              action={emptyAction}
            />
          ) : (
            <div className={`overflow-x-auto transition-opacity ${isNavigating ? "opacity-50" : ""}`}>
              {children}
            </div>
          )}
        </CardContent>
      </Card>

      <AdminPagination
        currentPage={pagination.current_page}
        lastPage={pagination.last_page}
        from={pagination.from}
        to={pagination.to}
        total={pagination.total}
        onPage={onPage}
        label={paginationLabel}
      />
    </>
  );
}
