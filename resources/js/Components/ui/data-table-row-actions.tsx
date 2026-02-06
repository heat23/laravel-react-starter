import { MoreHorizontal } from "lucide-react";

import { ReactNode } from "react";

import { Button } from "./button";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from "./dropdown-menu";

interface DataTableRowActionsProps {
  children: ReactNode;
  label?: string;
}

export function DataTableRowActions({ children, label }: DataTableRowActionsProps) {
  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button variant="ghost" className="flex h-8 w-8 min-h-11 min-w-11 p-0 data-[state=open]:bg-muted">
          <MoreHorizontal className="h-4 w-4" />
          <span className="sr-only">{label ?? "Open menu"}</span>
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" className="w-40">
        {children}
      </DropdownMenuContent>
    </DropdownMenu>
  );
}
