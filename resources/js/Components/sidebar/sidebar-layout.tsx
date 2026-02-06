import { LogOut, Menu, PanelLeftClose, PanelLeftOpen } from "lucide-react";

import type { PropsWithChildren } from "react";

import { Link, usePage } from "@inertiajs/react";

import { Logo, TextLogo } from "@/Components/branding/Logo";
import { CommandPalette, useCommandPalette } from "@/Components/command-palette";
import { NotificationDropdown } from "@/Components/notifications/NotificationDropdown";
import { ThemeToggle } from "@/Components/theme";
import { Button } from "@/Components/ui/button";
import { ScrollArea } from "@/Components/ui/scroll-area";
import { Separator } from "@/Components/ui/separator";
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from "@/Components/ui/sheet";
import { TooltipProvider } from "@/Components/ui/tooltip";
import { getVisibleGroups } from "@/config/navigation";
import type { PageProps } from "@/types";

import { SidebarProvider, useSidebar } from "./sidebar-context";
import { MobileSidebarNav, SidebarNavGroup } from "./sidebar-nav";

function SidebarInner({ children }: PropsWithChildren) {
  const { auth, features } = usePage<PageProps>().props;
  const { collapsed, toggleCollapsed, mobileOpen, setMobileOpen } = useSidebar();
  const { open: commandPaletteOpen, setOpen: setCommandPaletteOpen } = useCommandPalette();

  const visibleGroups = getVisibleGroups(features);

  return (
    <div className="flex min-h-screen">
      {/* Desktop Sidebar */}
      <aside
        aria-label="Main navigation"
        className={`hidden md:flex flex-col border-r border-sidebar-border bg-sidebar transition-[width] duration-200 ${
          collapsed ? "w-12" : "w-60"
        }`}
      >
        {/* Sidebar Header */}
        <div className="flex h-16 items-center justify-between border-b border-sidebar-border px-3">
          <Link href="/dashboard" className="flex items-center gap-2 overflow-hidden">
            <Logo className="h-6 w-6 shrink-0" />
            {!collapsed && <TextLogo className="text-sm" />}
          </Link>
          <Button
            variant="ghost"
            size="icon"
            className="h-7 w-7 shrink-0 text-sidebar-foreground/60 hover:text-sidebar-foreground"
            onClick={toggleCollapsed}
            aria-expanded={!collapsed}
            aria-label={collapsed ? "Expand sidebar" : "Collapse sidebar"}
          >
            {collapsed ? (
              <PanelLeftOpen className="h-4 w-4" />
            ) : (
              <PanelLeftClose className="h-4 w-4" />
            )}
          </Button>
        </div>

        {/* Nav Groups */}
        <ScrollArea className="flex-1 py-2">
          <TooltipProvider delayDuration={0}>
            {visibleGroups.map((group) => (
              <SidebarNavGroup key={group.label} group={group} collapsed={collapsed} />
            ))}
          </TooltipProvider>
        </ScrollArea>

        <Separator className="bg-sidebar-border" />

        {/* Sidebar Footer */}
        <div className="flex flex-col gap-2 p-3">
          {features.notifications && <NotificationDropdown />}
          <ThemeToggle />
          <div
            className={`flex items-center gap-2 overflow-hidden ${collapsed ? "justify-center" : ""}`}
          >
            <div className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-sidebar-primary/10 text-sidebar-primary text-xs font-medium">
              {auth.user?.name.charAt(0).toUpperCase()}
            </div>
            {!collapsed && (
              <div className="flex-1 overflow-hidden">
                <p className="truncate text-xs font-medium text-sidebar-foreground">
                  {auth.user?.name}
                </p>
                <p className="truncate text-xs text-sidebar-foreground/60">
                  {auth.user?.email}
                </p>
              </div>
            )}
          </div>
          <Button
            variant="ghost"
            size="sm"
            className="w-full justify-start text-sidebar-foreground/60 hover:text-destructive"
            asChild
          >
            <Link href={route("logout")} method="post" as="button" className="w-full">
              <LogOut className="mr-2 h-4 w-4" />
              {!collapsed && "Log out"}
            </Link>
          </Button>
        </div>
      </aside>

      {/* Mobile Sidebar (Sheet) */}
      <Sheet open={mobileOpen} onOpenChange={setMobileOpen}>
        <SheetContent side="left" className="w-72 bg-sidebar p-0">
          <SheetHeader className="border-b border-sidebar-border px-4 py-4">
            <SheetTitle className="flex items-center gap-2">
              <Logo className="h-6 w-6" />
              <TextLogo className="text-sm" />
            </SheetTitle>
          </SheetHeader>
          <ScrollArea className="flex-1">
            <MobileSidebarNav
              groups={visibleGroups}
              onNavigate={() => setMobileOpen(false)}
            />
          </ScrollArea>
          <div className="border-t border-sidebar-border p-3">
            <div className="flex items-center gap-2 mb-2">
              <div className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-sidebar-primary/10 text-sidebar-primary text-xs font-medium">
                {auth.user?.name.charAt(0).toUpperCase()}
              </div>
              <div className="flex-1 overflow-hidden">
                <p className="truncate text-xs font-medium text-sidebar-foreground">
                  {auth.user?.name}
                </p>
              </div>
            </div>
            <ThemeToggle />
          </div>
        </SheetContent>
      </Sheet>

      {/* Main Content */}
      <div className="flex flex-1 flex-col">
        {/* Mobile Header */}
        <header className="flex h-14 items-center border-b bg-background px-4 md:hidden">
          <Sheet open={mobileOpen} onOpenChange={setMobileOpen}>
            <SheetTrigger asChild>
              <Button variant="ghost" size="icon" className="mr-2">
                <Menu className="h-5 w-5" />
                <span className="sr-only">Toggle menu</span>
              </Button>
            </SheetTrigger>
          </Sheet>
          <Link href="/dashboard" className="flex items-center gap-2">
            <Logo className="h-6 w-6" />
            <TextLogo className="text-sm" />
          </Link>
        </header>

        <main id="main-content" className="flex-1">
          {children}
        </main>
      </div>

      <CommandPalette open={commandPaletteOpen} onOpenChange={setCommandPaletteOpen} />
    </div>
  );
}

export default function SidebarLayout({ children }: PropsWithChildren) {
  return (
    <SidebarProvider>
      <SidebarInner>{children}</SidebarInner>
    </SidebarProvider>
  );
}
