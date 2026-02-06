import { Download, Loader2 } from "lucide-react";

import { useCallback, useState } from "react";

import { Button, type ButtonProps } from "@/Components/ui/button";
import { cn } from "@/lib/utils";

interface ExportButtonProps {
  href: string;
  params?: Record<string, string>;
  label?: string;
  variant?: ButtonProps["variant"];
  size?: ButtonProps["size"];
  className?: string;
  disabled?: boolean;
}

export function ExportButton({
  href,
  params,
  label = "Export CSV",
  variant = "outline",
  size = "sm",
  className,
  disabled = false,
}: ExportButtonProps) {
  const [loading, setLoading] = useState(false);

  const url = buildUrl(href, params);

  const handleClick = useCallback(() => {
    setLoading(true);
    // Auto-reset after 3 seconds (streamed downloads begin immediately)
    setTimeout(() => setLoading(false), 3000);
  }, []);

  return (
    <Button
      variant={variant}
      size={size}
      className={cn(className)}
      disabled={disabled || loading}
      asChild
    >
      <a href={url} download onClick={handleClick}>
        {loading ? (
          <Loader2 className="mr-2 h-4 w-4 animate-spin" />
        ) : (
          <Download className="mr-2 h-4 w-4" />
        )}
        {label}
      </a>
    </Button>
  );
}

function buildUrl(base: string, params?: Record<string, string>): string {
  if (!params || Object.keys(params).length === 0) return base;
  const searchParams = new URLSearchParams(params);
  return `${base}?${searchParams.toString()}`;
}
