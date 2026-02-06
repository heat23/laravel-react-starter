import { Table } from "@tanstack/react-table";
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from "lucide-react";

import { Button } from "./button";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "./select";

interface DataTablePaginationProps<TData> {
  table: Table<TData>;
  pageSizes?: number[];
}

export function DataTablePagination<TData>({
  table,
  pageSizes = [10, 20, 30, 50],
}: DataTablePaginationProps<TData>) {
  return (
    <div className="flex flex-col items-center gap-4 px-2 sm:flex-row sm:justify-between">
      <div className="text-sm text-muted-foreground">
        {table.getFilteredSelectedRowModel().rows.length} of{" "}
        {table.getFilteredRowModel().rows.length} row(s) selected.
      </div>
      <div className="flex flex-col items-center gap-4 sm:flex-row sm:space-x-6 lg:space-x-8">
        <div className="flex items-center space-x-2">
          <p className="text-sm font-medium" id="rows-per-page-label">Rows per page</p>
          <Select
            value={`${table.getState().pagination.pageSize}`}
            onValueChange={(value) => table.setPageSize(Number(value))}
            aria-labelledby="rows-per-page-label"
          >
            <SelectTrigger className="h-8 w-[70px]">
              <SelectValue placeholder={table.getState().pagination.pageSize} />
            </SelectTrigger>
            <SelectContent side="top">
              {pageSizes.map((size) => (
                <SelectItem key={size} value={`${size}`}>
                  {size}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
        <div
          className="flex w-[100px] items-center justify-center text-sm font-medium"
          aria-live="polite"
          aria-atomic="true"
        >
          Page {table.getState().pagination.pageIndex + 1} of {table.getPageCount()}
        </div>
        <div className="flex items-center space-x-2">
          <Button
            variant="outline"
            className="hidden h-8 w-8 p-0 lg:flex lg:min-h-11 lg:min-w-11"
            onClick={() => table.setPageIndex(0)}
            disabled={!table.getCanPreviousPage()}
            aria-label="Go to first page"
          >
            <ChevronsLeft className="h-4 w-4" />
          </Button>
          <Button
            variant="outline"
            className="h-8 w-8 min-h-11 min-w-11 p-0"
            onClick={() => table.previousPage()}
            disabled={!table.getCanPreviousPage()}
            aria-label="Go to previous page"
          >
            <ChevronLeft className="h-4 w-4" />
          </Button>
          <Button
            variant="outline"
            className="h-8 w-8 min-h-11 min-w-11 p-0"
            onClick={() => table.nextPage()}
            disabled={!table.getCanNextPage()}
            aria-label="Go to next page"
          >
            <ChevronRight className="h-4 w-4" />
          </Button>
          <Button
            variant="outline"
            className="hidden h-8 w-8 p-0 lg:flex lg:min-h-11 lg:min-w-11"
            onClick={() => table.setPageIndex(table.getPageCount() - 1)}
            disabled={!table.getCanNextPage()}
            aria-label="Go to last page"
          >
            <ChevronsRight className="h-4 w-4" />
          </Button>
        </div>
      </div>
    </div>
  );
}
