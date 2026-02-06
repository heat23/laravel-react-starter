import {
  ColumnDef,
  ColumnFiltersState,
  PaginationState,
  SortingState,
  VisibilityState,
  flexRender,
  getCoreRowModel,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useReactTable,
} from "@tanstack/react-table";
import { LucideIcon } from "lucide-react";

import { ReactNode, useCallback, useEffect, useMemo, useState } from "react";

import { Checkbox } from "./checkbox";
import { DataTablePagination } from "./data-table-pagination";
import { DataTableToolbar } from "./data-table-toolbar";
import { EmptyState } from "./empty-state";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "./table";

/**
 * Generic data table with sorting, filtering, pagination, and row selection.
 *
 * For datasets larger than ~500 rows, use `manualPagination={true}` with server-side
 * pagination to avoid loading the entire dataset into browser memory.
 */
interface DataTableProps<TData, TValue> {
  columns: ColumnDef<TData, TValue>[];
  data: TData[];
  searchKey?: string;
  searchPlaceholder?: string;
  caption?: string;
  emptyState?: {
    title: string;
    description?: string;
    icon?: LucideIcon;
    action?: ReactNode;
  };
  toolbar?: (table: ReturnType<typeof useReactTable<TData>>) => ReactNode;
  bulkActions?: (selectedRows: TData[]) => ReactNode;
  pageSize?: number;
  enableSelection?: boolean;
  manualPagination?: boolean;
  pageCount?: number;
  onPaginationChange?: (pagination: PaginationState) => void;
  persistState?: boolean;
}

export function DataTable<TData, TValue>({
  columns,
  data,
  searchKey,
  searchPlaceholder,
  caption,
  emptyState,
  toolbar,
  bulkActions,
  pageSize = 10,
  enableSelection = false,
  manualPagination = false,
  pageCount,
  onPaginationChange,
  persistState = false,
}: DataTableProps<TData, TValue>) {
  function getInitialState() {
    const defaults = { sorting: [] as SortingState, columnFilters: [] as ColumnFiltersState, pageIndex: 0 };
    if (!persistState || typeof window === "undefined") return defaults;

    const params = new URLSearchParams(window.location.search);
    const sortParam = params.get("sort");
    const filterParam = params.get("filter");
    const pageParam = params.get("page");

    let sorting = defaults.sorting;
    if (sortParam) {
      try {
        const parsed = JSON.parse(sortParam);
        if (Array.isArray(parsed)) sorting = parsed as SortingState;
      } catch { /* malformed URL param — use default */ }
    }

    let columnFilters = defaults.columnFilters;
    if (filterParam) {
      try {
        const parsed = JSON.parse(filterParam);
        if (Array.isArray(parsed)) columnFilters = parsed as ColumnFiltersState;
      } catch { /* malformed URL param — use default */ }
    }

    const parsedPage = pageParam ? parseInt(pageParam, 10) : NaN;
    const pageIndex = Number.isNaN(parsedPage) ? 0 : Math.max(0, parsedPage - 1);

    return { sorting, columnFilters, pageIndex };
  }

  const initial = getInitialState();
  const [sorting, setSorting] = useState<SortingState>(initial.sorting);
  const [columnFilters, setColumnFilters] = useState<ColumnFiltersState>(initial.columnFilters);
  const [columnVisibility, setColumnVisibility] = useState<VisibilityState>({});
  const [rowSelection, setRowSelection] = useState({});
  const [pagination, setPagination] = useState<PaginationState>({
    pageIndex: initial.pageIndex,
    pageSize,
  });

  const syncToUrl = useCallback(() => {
    if (!persistState || typeof window === "undefined") return;
    const params = new URLSearchParams(window.location.search);

    if (sorting.length > 0) {
      params.set("sort", JSON.stringify(sorting));
    } else {
      params.delete("sort");
    }

    if (columnFilters.length > 0) {
      params.set("filter", JSON.stringify(columnFilters));
    } else {
      params.delete("filter");
    }

    if (pagination.pageIndex > 0) {
      params.set("page", String(pagination.pageIndex + 1));
    } else {
      params.delete("page");
    }

    const search = params.toString();
    const url = search ? `${window.location.pathname}?${search}` : window.location.pathname;
    history.replaceState(null, "", url);
  }, [persistState, sorting, columnFilters, pagination.pageIndex]);

  useEffect(() => {
    syncToUrl();
  }, [syncToUrl]);

  const selectionColumn: ColumnDef<TData, TValue>[] = enableSelection
    ? [
        {
          id: "select",
          header: ({ table }) => (
            <Checkbox
              checked={
                table.getIsAllPageRowsSelected() ||
                (table.getIsSomePageRowsSelected() && "indeterminate")
              }
              onCheckedChange={(value) => table.toggleAllPageRowsSelected(!!value)}
              aria-label="Select all rows on this page"
              className="translate-y-[2px]"
            />
          ),
          cell: ({ row }) => (
            <Checkbox
              checked={row.getIsSelected()}
              onCheckedChange={(value) => row.toggleSelected(!!value)}
              aria-label="Select row"
              className="translate-y-[2px]"
            />
          ),
          enableSorting: false,
          enableHiding: false,
        } as ColumnDef<TData, TValue>,
      ]
    : [];

  // eslint-disable-next-line react-hooks/exhaustive-deps -- selectionColumn is derived from enableSelection
  const allColumns = useMemo(() => [...selectionColumn, ...columns], [enableSelection, columns]);

  const table = useReactTable({
    data,
    columns: allColumns,
    state: {
      sorting,
      columnFilters,
      columnVisibility,
      rowSelection,
      pagination,
    },
    enableRowSelection: enableSelection,
    onSortingChange: setSorting,
    onColumnFiltersChange: setColumnFilters,
    onColumnVisibilityChange: setColumnVisibility,
    onRowSelectionChange: setRowSelection,
    onPaginationChange: (updater) => {
      const newPagination = typeof updater === "function" ? updater(pagination) : updater;
      setPagination(newPagination);
      onPaginationChange?.(newPagination);
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getPaginationRowModel: manualPagination ? undefined : getPaginationRowModel(),
    ...(manualPagination && pageCount !== undefined ? { pageCount, manualPagination: true } : {}),
  });

  const filteredSelectedRows = table.getFilteredSelectedRowModel().rows;
  const selectedRows = useMemo(() => filteredSelectedRows.map((row) => row.original), [filteredSelectedRows]);

  return (
    <div className="space-y-4">
      <DataTableToolbar
        table={table}
        searchKey={searchKey}
        searchPlaceholder={searchPlaceholder}
        filterSlot={toolbar?.(table)}
      />

      {enableSelection && selectedRows.length > 0 && bulkActions && (
        <div className="flex items-center gap-2 rounded-md border bg-muted/50 px-4 py-2" aria-live="polite">
          <span className="text-sm text-muted-foreground">
            {selectedRows.length} row(s) selected
          </span>
          <div className="ml-auto flex items-center gap-2">{bulkActions(selectedRows)}</div>
        </div>
      )}

      <div className="rounded-md border">
        <Table aria-label={caption}>
          {caption && (
            <caption className="sr-only">{caption}</caption>
          )}
          <TableHeader>
            {table.getHeaderGroups().map((headerGroup) => (
              <TableRow key={headerGroup.id}>
                {headerGroup.headers.map((header) => (
                  <TableHead key={header.id} colSpan={header.colSpan}>
                    {header.isPlaceholder
                      ? null
                      : flexRender(header.column.columnDef.header, header.getContext())}
                  </TableHead>
                ))}
              </TableRow>
            ))}
          </TableHeader>
          <TableBody>
            {table.getRowModel().rows?.length ? (
              table.getRowModel().rows.map((row) => (
                <TableRow key={row.id} data-state={row.getIsSelected() && "selected"}>
                  {row.getVisibleCells().map((cell) => (
                    <TableCell key={cell.id}>
                      {flexRender(cell.column.columnDef.cell, cell.getContext())}
                    </TableCell>
                  ))}
                </TableRow>
              ))
            ) : (
              <TableRow>
                <TableCell colSpan={allColumns.length} className="h-24">
                  {emptyState ? (
                    <EmptyState
                      icon={emptyState.icon}
                      title={emptyState.title}
                      description={emptyState.description}
                      action={emptyState.action}
                      size="sm"
                    />
                  ) : (
                    <div className="text-center text-muted-foreground">No results.</div>
                  )}
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </div>

      <DataTablePagination table={table} />
    </div>
  );
}
