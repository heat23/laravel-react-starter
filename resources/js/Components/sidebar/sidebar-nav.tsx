import { Link, usePage } from "@inertiajs/react";

import { Tooltip, TooltipContent, TooltipTrigger } from "@/Components/ui/tooltip";
import type { NavGroup, NavItem } from "@/config/navigation";
import { cn } from "@/lib/utils";

function isNavActive(url: string, href: string): boolean {
  const segments = href.split("/").filter(Boolean);
  if (segments.length <= 1) {
    return url === href || url === href + "/";
  }
  return url.startsWith(href);
}

interface SidebarNavGroupProps {
  group: NavGroup;
  collapsed?: boolean;
}

export function SidebarNavGroup({ group, collapsed = false }: SidebarNavGroupProps) {
  return (
    <div className="px-3 py-2">
      {!collapsed && (
        <h3 className="mb-1 px-2 text-xs font-medium uppercase tracking-wider text-sidebar-foreground/50">
          {group.label}
        </h3>
      )}
      <nav className="flex flex-col gap-0.5">
        {group.items.map((item) => (
          <SidebarNavItem key={item.href} item={item} collapsed={collapsed} />
        ))}
      </nav>
    </div>
  );
}

interface SidebarNavItemProps {
  item: NavItem;
  collapsed?: boolean;
}

export function SidebarNavItem({ item, collapsed = false }: SidebarNavItemProps) {
  const { url } = usePage();
  const isActive = isNavActive(url, item.href);
  const Icon = item.icon;

  const linkContent = (
    <Link
      href={item.href}
      aria-current={isActive ? "page" : undefined}
      className={cn(
        "flex items-center gap-3 rounded-md px-2 py-2 text-sm font-medium transition-colors",
        "hover:bg-sidebar-accent hover:text-sidebar-accent-foreground",
        isActive
          ? "bg-sidebar-accent text-sidebar-accent-foreground border-l-2 border-primary font-semibold"
          : "text-sidebar-foreground/80 border-l-2 border-transparent",
        collapsed && "justify-center px-0 border-l-0",
        collapsed && isActive && "border-b-2 border-primary border-l-0",
        collapsed && !isActive && "border-b-2 border-transparent border-l-0",
      )}
    >
      <Icon className="h-4 w-4 shrink-0" />
      {!collapsed && <span>{item.label}</span>}
    </Link>
  );

  if (collapsed) {
    return (
      <Tooltip>
        <TooltipTrigger asChild>{linkContent}</TooltipTrigger>
        <TooltipContent side="right">{item.label}</TooltipContent>
      </Tooltip>
    );
  }

  return linkContent;
}

interface MobileSidebarNavProps {
  groups: NavGroup[];
  onNavigate?: () => void;
}

export function MobileSidebarNav({ groups, onNavigate }: MobileSidebarNavProps) {
  const { url } = usePage();

  return (
    <nav className="flex flex-col gap-1 px-3 py-2">
      {groups.map((group) => (
        <div key={group.label}>
          <h3 className="mb-1 px-2 text-xs font-medium uppercase tracking-wider text-sidebar-foreground/50">
            {group.label}
          </h3>
          {group.items.map((item) => {
            const Icon = item.icon;
            const isActive = isNavActive(url, item.href);
            return (
              <Link
                key={item.href}
                href={item.href}
                onClick={onNavigate}
                aria-current={isActive ? "page" : undefined}
                className={cn(
                  "flex items-center gap-3 rounded-md px-2 py-2 text-sm font-medium transition-colors",
                  "hover:bg-sidebar-accent hover:text-sidebar-accent-foreground",
                  isActive
                    ? "bg-sidebar-accent text-sidebar-accent-foreground border-l-2 border-primary font-semibold"
                    : "text-sidebar-foreground/80 border-l-2 border-transparent",
                )}
              >
                <Icon className="h-4 w-4 shrink-0" />
                <span>{item.label}</span>
              </Link>
            );
          })}
        </div>
      ))}
    </nav>
  );
}
