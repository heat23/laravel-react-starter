import { Button } from "@/Components/ui/button";

interface AdminPaginationProps {
  currentPage: number;
  lastPage: number;
  from: number | null;
  to: number | null;
  total: number;
  maxVisible?: number;
  onPage: (page: number) => void;
  label?: string;
}

export function AdminPagination({
  currentPage,
  lastPage,
  from,
  to,
  total,
  maxVisible = 10,
  onPage,
  label = "items",
}: AdminPaginationProps) {
  if (lastPage <= 1) return null;

  // Sliding window centered on currentPage
  const half = Math.floor(maxVisible / 2);
  const start = Math.max(1, Math.min(currentPage - half, lastPage - maxVisible + 1));
  const end = Math.min(lastPage, start + maxVisible - 1);
  const pages = Array.from({ length: end - start + 1 }, (_, i) => start + i);

  return (
    <div className="flex items-center justify-between">
      <p className="text-sm text-muted-foreground">
        Showing {from ?? 0}–{to ?? 0} of {total} {label}
      </p>
      <nav aria-label="Pagination" className="flex gap-1">
        {start > 1 && (
          <>
            <Button
              variant="outline"
              size="sm"
              onClick={() => onPage(1)}
              aria-label="Go to page 1"
            >
              1
            </Button>
            {start > 2 && (
              <span className="flex items-center px-2 text-sm text-muted-foreground">...</span>
            )}
          </>
        )}
        {pages.map((page) => (
          <Button
            key={page}
            variant={page === currentPage ? "default" : "outline"}
            size="sm"
            onClick={() => onPage(page)}
            aria-current={page === currentPage ? "page" : undefined}
            aria-label={page === currentPage ? `Page ${page}, current page` : `Go to page ${page}`}
          >
            {page}
          </Button>
        ))}
        {end < lastPage && (
          <>
            {end < lastPage - 1 && (
              <span className="flex items-center px-2 text-sm text-muted-foreground">...</span>
            )}
            <Button
              variant="outline"
              size="sm"
              onClick={() => onPage(lastPage)}
              aria-label={`Go to page ${lastPage}`}
            >
              {lastPage}
            </Button>
          </>
        )}
      </nav>
    </div>
  );
}
