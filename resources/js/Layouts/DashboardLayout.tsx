import { FileText, LogOut, Menu, Radio, Settings, Shield, User } from "lucide-react";

import { PropsWithChildren } from "react";

import { Link, usePage } from "@inertiajs/react";

import { Logo, TextLogo } from "@/Components/branding/Logo";
import { CommandPalette, useCommandPalette } from "@/Components/command-palette";
import { NotificationDropdown } from "@/Components/notifications/NotificationDropdown";
import { ThemeToggle } from "@/Components/theme";
import { Button } from "@/Components/ui/button";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";
import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from "@/Components/ui/sheet";
import { getVisibleNavItems } from "@/config/navigation";
import type { PageProps } from "@/types";

export default function DashboardLayout({ children }: PropsWithChildren) {
  const { auth, features } = usePage<PageProps>().props;
  const navItems = getVisibleNavItems(features);
  const { open, setOpen } = useCommandPalette();

  return (
    <div className="min-h-screen bg-background">
      <a
        href="#main-content"
        className="sr-only focus-visible:not-sr-only focus-visible:absolute focus-visible:z-100 focus-visible:bg-background focus-visible:px-4 focus-visible:py-2 focus-visible:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
      >
        Skip to main content
      </a>

      {/* Desktop Navigation */}
      <header className="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
        <div className="container flex h-16 items-center justify-between">
          {/* Left side - Logo and Nav */}
          <div className="flex items-center gap-6">
            <Link href="/dashboard" className="flex items-center gap-2">
              <Logo className="h-8 w-8" />
              <TextLogo className="hidden sm:block text-lg font-bold" />
            </Link>

            {/* Desktop Nav */}
            <nav className="hidden md:flex items-center gap-1">
              {navItems.map((item) => (
                <Button
                  key={item.href}
                  variant="ghost"
                  size="sm"
                  asChild
                  className="text-muted-foreground hover:text-foreground"
                >
                  <Link href={item.href}>
                    <item.icon className="mr-2 h-4 w-4" />
                    {item.label}
                  </Link>
                </Button>
              ))}
            </nav>
          </div>

          {/* Right side - Theme toggle and User menu */}
          <div className="flex items-center gap-2">
            <ThemeToggle />
            {features.notifications && <NotificationDropdown />}

            {/* User Menu */}
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button variant="ghost" className="relative h-9 w-9 rounded-full">
                  <div className="flex h-9 w-9 items-center justify-center rounded-full bg-primary/10 text-primary">
                    {auth.user!.name.charAt(0).toUpperCase()}
                  </div>
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent className="w-56" align="end" forceMount>
                <DropdownMenuLabel className="font-normal">
                  <div className="flex flex-col space-y-1">
                    <p className="text-sm font-medium leading-none">{auth.user!.name}</p>
                    <p className="text-xs leading-none text-muted-foreground">
                      {auth.user!.email}
                    </p>
                  </div>
                </DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuItem asChild>
                  <Link href="/profile" className="cursor-pointer">
                    <User className="mr-2 h-4 w-4" />
                    Profile
                  </Link>
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
                  <Link href="/settings/tokens" className="cursor-pointer">
                    <Settings className="mr-2 h-4 w-4" />
                    Settings
                  </Link>
                </DropdownMenuItem>
                {features.twoFactor && (
                  <DropdownMenuItem asChild>
                    <Link href="/settings/security" className="cursor-pointer">
                      <Shield className="mr-2 h-4 w-4" />
                      Security
                    </Link>
                  </DropdownMenuItem>
                )}
                {features.webhooks && (
                  <DropdownMenuItem asChild>
                    <Link href="/settings/webhooks" className="cursor-pointer">
                      <Radio className="mr-2 h-4 w-4" />
                      Webhooks
                    </Link>
                  </DropdownMenuItem>
                )}
                {features.apiDocs && (
                  <DropdownMenuItem asChild>
                    <a href="/docs" target="_blank" rel="noopener noreferrer" className="cursor-pointer">
                      <FileText className="mr-2 h-4 w-4" />
                      API Docs
                    </a>
                  </DropdownMenuItem>
                )}
                <DropdownMenuSeparator />
                <DropdownMenuItem asChild>
                  <Link
                    href={route("logout")}
                    method="post"
                    as="button"
                    className="w-full cursor-pointer text-destructive focus:text-destructive"
                  >
                    <LogOut className="mr-2 h-4 w-4" />
                    Log out
                  </Link>
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>

            {/* Mobile Menu */}
            <Sheet>
              <SheetTrigger asChild className="md:hidden">
                <Button variant="ghost" size="icon">
                  <Menu className="h-5 w-5" />
                  <span className="sr-only">Toggle menu</span>
                </Button>
              </SheetTrigger>
              <SheetContent side="left">
                <SheetHeader>
                  <SheetTitle className="flex items-center gap-2">
                    <Logo className="h-6 w-6" />
                    <TextLogo />
                  </SheetTitle>
                </SheetHeader>
                <nav className="mt-8 flex flex-col gap-2">
                  {navItems.map((item) => (
                    <Button
                      key={item.href}
                      variant="ghost"
                      className="justify-start"
                      asChild
                    >
                      <Link href={item.href}>
                        <item.icon className="mr-2 h-4 w-4" />
                        {item.label}
                      </Link>
                    </Button>
                  ))}
                </nav>
              </SheetContent>
            </Sheet>
          </div>
        </div>
      </header>

      {/* Main Content */}
      <main id="main-content" className="flex-1">{children}</main>

      <CommandPalette open={open} onOpenChange={setOpen} />
    </div>
  );
}
