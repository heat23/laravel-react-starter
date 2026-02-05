import { ReactNode } from "react";

import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from "@/Components/ui/tooltip";
import { cn } from "@/lib/utils";

interface TruncateWithTooltipProps {
  /** The text to display and potentially truncate */
  children: ReactNode;
  /** Maximum width before truncation (Tailwind class) */
  maxWidth?: string;
  /** Additional CSS classes for the container */
  className?: string;
  /** Disable tooltip (only truncate) */
  disableTooltip?: boolean;
}

/**
 * Component that truncates text with ellipsis and shows full text in tooltip on hover.
 *
 * @example
 * <TruncateWithTooltip maxWidth="max-w-xs">
 *   This is a very long project name that will be truncated
 * </TruncateWithTooltip>
 */
export function TruncateWithTooltip({
  children,
  maxWidth = "max-w-[200px]",
  className,
  disableTooltip = false,
}: TruncateWithTooltipProps) {
  const content = (
    <span className={cn("truncate block", maxWidth, className)} title={disableTooltip ? String(children) : undefined}>
      {children}
    </span>
  );

  if (disableTooltip) {
    return content;
  }

  return (
    <TooltipProvider>
      <Tooltip>
        <TooltipTrigger asChild>
          {content}
        </TooltipTrigger>
        <TooltipContent>
          <p className="max-w-sm">{children}</p>
        </TooltipContent>
      </Tooltip>
    </TooltipProvider>
  );
}
