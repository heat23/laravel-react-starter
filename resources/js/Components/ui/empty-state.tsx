import { ReactNode } from "react";
import { LucideIcon } from "lucide-react";
import { cn } from "@/lib/utils";
import { AnimatedShield, AnimatedFolderShield } from "./animated-shield";

interface EmptyStateProps {
  icon?: LucideIcon;
  title: string;
  description?: string;
  action?: ReactNode;
  secondaryAction?: ReactNode;
  className?: string;
  animated?: boolean;
  size?: "sm" | "md" | "lg";
  illustration?: "shield" | "folder" | "none";
  illustrationVariant?: "default" | "success" | "warning" | "error";
}

export function EmptyState({
  icon: Icon,
  title,
  description,
  action,
  secondaryAction,
  className,
  animated = true,
  size = "md",
  illustration,
  illustrationVariant = "default",
}: EmptyStateProps) {
  const sizeClasses = {
    sm: "py-6 px-3",
    md: "py-12 px-4",
    lg: "py-16 px-6",
  };

  const iconSizes = {
    sm: "h-6 w-6",
    md: "h-8 w-8",
    lg: "h-10 w-10",
  };

  const iconPadding = {
    sm: "p-3",
    md: "p-5",
    lg: "p-6",
  };

  const renderIllustration = () => {
    if (illustration === "shield") {
      return (
        <div className="mb-4">
          <AnimatedShield size={size} variant={illustrationVariant} />
        </div>
      );
    }
    if (illustration === "folder") {
      return (
        <div className="mb-4">
          <AnimatedFolderShield size={size} />
        </div>
      );
    }
    if (Icon) {
      return (
        <div className={cn(
          "mb-4 rounded-full bg-gradient-to-br from-primary/10 to-primary/5 ring-1 ring-primary/10",
          iconPadding[size],
          animated && "animate-float"
        )}>
          <Icon className={cn(iconSizes[size], "text-primary/70")} />
        </div>
      );
    }
    return null;
  };

  return (
    <div
      role="status"
      aria-label={title}
      className={cn(
        "flex flex-col items-center justify-center text-center",
        sizeClasses[size],
        animated && "animate-fade-in",
        className
      )}
    >
      {renderIllustration()}
      <h3 className={cn(
        "font-semibold text-foreground mb-2",
        size === "lg" ? "text-xl" : "text-lg"
      )}>{title}</h3>
      {description && (
        <p className="text-muted-foreground max-w-sm mb-4 text-sm leading-relaxed">{description}</p>
      )}
      {action && <div className="mt-2">{action}</div>}
      {secondaryAction && <div className="mt-3">{secondaryAction}</div>}
    </div>
  );
}
