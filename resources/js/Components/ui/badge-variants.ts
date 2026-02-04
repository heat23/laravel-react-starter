import { cva } from "class-variance-authority";

export const badgeVariants = cva(
  "inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2",
  {
    variants: {
      variant: {
        default: "border-transparent bg-primary text-primary-foreground hover:bg-primary/80",
        secondary: "border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80",
        destructive: "border-transparent bg-destructive text-destructive-foreground hover:bg-destructive/80",
        outline: "text-foreground",
        // Severity variants with gradients
        critical: "border-transparent severity-critical text-white font-bold tracking-wide uppercase",
        high: "border-transparent severity-high text-white font-bold tracking-wide uppercase",
        medium: "border-transparent severity-medium text-white font-bold tracking-wide uppercase",
        low: "border-transparent severity-low text-white font-bold tracking-wide uppercase",
        // Success variant
        success: "border-transparent bg-success text-success-foreground hover:bg-success/80",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  },
);
