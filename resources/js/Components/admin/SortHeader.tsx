import { TableHead } from "@/Components/ui/table";

interface SortHeaderProps {
  column: string;
  label: string;
  currentSort?: string;
  currentDir?: string;
  onSort: (column: string) => void;
}

export function SortHeader({ column, label, currentSort, currentDir, onSort }: SortHeaderProps) {
  const isActive = currentSort === column;
  return (
    <TableHead
      className="cursor-pointer select-none hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
      onClick={() => onSort(column)}
      onKeyDown={(e) => {
        if (e.key === "Enter" || e.key === " ") {
          e.preventDefault();
          onSort(column);
        }
      }}
      tabIndex={0}
      role="columnheader"
      aria-sort={isActive ? (currentDir === "asc" ? "ascending" : "descending") : "none"}
    >
      {label}
      {isActive && <span className="ml-1">{currentDir === "asc" ? "↑" : "↓"}</span>}
    </TableHead>
  );
}
