import { Link, InertiaLinkProps } from "@inertiajs/react";
import { cn } from "@/lib/utils";

interface NavLinkProps extends InertiaLinkProps {
  active?: boolean;
  activeClassName?: string;
}

export function NavLink({
  active,
  activeClassName = "text-foreground bg-muted",
  className,
  children,
  ...props
}: NavLinkProps) {
  // Check if current URL matches the href
  const isActive = active ?? (typeof window !== 'undefined' && window.location.pathname === props.href);

  return (
    <Link
      {...props}
      className={cn(
        "transition-colors",
        className,
        isActive && activeClassName
      )}
    >
      {children}
    </Link>
  );
}
