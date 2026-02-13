import { useCallback, useEffect, useRef, useState } from "react";

import { router } from "@inertiajs/react";

interface UseAdminFiltersOptions<T extends Record<string, string | undefined>> {
  /** The route URL to navigate to when filters change. */
  route: string;
  /** The initial filter values from the server. */
  filters: T;
  /** Debounce delay in ms for search input (default: 300). */
  debounce?: number;
  /** Inertia router options. */
  routerOptions?: { preserveScroll?: boolean };
}

interface UseAdminFiltersReturn<T extends Record<string, string | undefined>> {
  /** Current search input value (local state, debounced before navigation). */
  search: string;
  /** Update the search input — triggers debounced navigation. */
  setSearch: (value: string) => void;
  /** Update one or more filter values — triggers immediate navigation. */
  updateFilter: (updates: Partial<T>) => void;
  /** Toggle sort column — flips direction if already sorted by that column. */
  handleSort: (column: string) => void;
  /** Navigate to a specific page number. */
  handlePage: (page: number) => void;
  /** Clear all filters and navigate. */
  clearFilters: () => void;
}

/**
 * Shared hook for admin list pages that need debounced search,
 * select filters, sortable columns, and pagination.
 *
 * Eliminates the repeated useState/useRef/setTimeout/router.get pattern
 * across Users, Subscriptions, and Audit Logs pages.
 */
export function useAdminFilters<T extends Record<string, string | undefined>>({
  route,
  filters,
  debounce = 300,
  routerOptions,
}: UseAdminFiltersOptions<T>): UseAdminFiltersReturn<T> {
  const [search, setSearchState] = useState((filters as Record<string, string | undefined>).search ?? "");
  const searchTimeout = useRef<ReturnType<typeof setTimeout>>();
  const filtersRef = useRef(filters);
  filtersRef.current = filters;

  useEffect(() => {
    return () => clearTimeout(searchTimeout.current);
  }, []);

  const navigate = useCallback(
    (params: Record<string, string | number | undefined>) => {
      router.get(route, params, {
        preserveState: true,
        replace: true,
        ...routerOptions,
      });
    },
    [route, routerOptions],
  );

  const setSearch = useCallback(
    (value: string) => {
      setSearchState(value);
      clearTimeout(searchTimeout.current);
      searchTimeout.current = setTimeout(() => {
        navigate({ ...filtersRef.current, search: value || undefined });
      }, debounce);
    },
    [navigate, debounce],
  );

  const updateFilter = useCallback(
    (updates: Partial<T>) => {
      navigate({ ...filtersRef.current, ...updates });
    },
    [navigate],
  );

  const handleSort = useCallback(
    (column: string) => {
      const current = filtersRef.current as Record<string, string | undefined>;
      const newDir = current.sort === column && current.dir === "asc" ? "desc" : "asc";
      navigate({ ...filtersRef.current, sort: column, dir: newDir });
    },
    [navigate],
  );

  const handlePage = useCallback(
    (page: number) => {
      navigate({ ...filtersRef.current, page });
    },
    [navigate],
  );

  const clearFilters = useCallback(() => {
    setSearchState("");
    navigate({});
  }, [navigate]);

  return { search, setSearch, updateFilter, handleSort, handlePage, clearFilters };
}
