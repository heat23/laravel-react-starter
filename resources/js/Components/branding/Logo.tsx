import { cn } from "@/lib/utils";

interface LogoProps {
  size?: number;
  className?: string;
}

/**
 * Placeholder Logo Component
 *
 * Replace this SVG with your own logo. The component accepts:
 * - size: number (default: 24) - controls width/height
 * - className: string - additional Tailwind classes
 *
 * Tips for customization:
 * - Keep SVG viewBox consistent for scaling
 * - Use currentColor for fill/stroke to inherit text color
 * - Or use CSS variables like hsl(var(--primary)) for brand colors
 */
export function Logo({ size = 24, className }: LogoProps) {
  return (
    <svg
      width={size}
      height={size}
      viewBox="0 0 24 24"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      className={cn("text-primary", className)}
    >
      {/* Simple placeholder shape - replace with your logo */}
      <rect
        x="3"
        y="3"
        width="18"
        height="18"
        rx="4"
        fill="currentColor"
        fillOpacity="0.1"
        stroke="currentColor"
        strokeWidth="2"
      />
      <path
        d="M8 12L11 15L16 9"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </svg>
  );
}

/**
 * Text Logo Component
 *
 * A simple text-based logo for when you don't have an SVG logo yet.
 * Uses the app name from environment or falls back to placeholder.
 */
export function TextLogo({ className }: { className?: string }) {
  const appName = import.meta.env.VITE_APP_NAME || "App";

  return (
    <span className={cn("font-bold text-xl tracking-tight", className)}>
      {appName}
    </span>
  );
}
