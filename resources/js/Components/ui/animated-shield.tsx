import { Shield, FolderLock } from "lucide-react";

import { cn } from "@/lib/utils";

interface AnimatedShieldProps {
  size?: "sm" | "md" | "lg";
  variant?: "default" | "success" | "warning" | "error";
}

const sizeClasses = {
  sm: "h-12 w-12",
  md: "h-16 w-16",
  lg: "h-20 w-20",
};

const variantClasses = {
  default: "text-primary/70",
  success: "text-success/70",
  warning: "text-warning/70",
  error: "text-destructive/70",
};

export function AnimatedShield({ size = "md", variant = "default" }: AnimatedShieldProps) {
  return (
    <div className="animate-float">
      <Shield className={cn(sizeClasses[size], variantClasses[variant])} />
    </div>
  );
}

interface AnimatedFolderShieldProps {
  size?: "sm" | "md" | "lg";
}

export function AnimatedFolderShield({ size = "md" }: AnimatedFolderShieldProps) {
  return (
    <div className="animate-float">
      <FolderLock className={cn(sizeClasses[size], "text-primary/70")} />
    </div>
  );
}
