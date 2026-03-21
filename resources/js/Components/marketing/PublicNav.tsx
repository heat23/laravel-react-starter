import { ArrowRight, Menu, X } from 'lucide-react';

import { useEffect, useRef, useState } from 'react';

import { Link } from '@inertiajs/react';

import { Logo, TextLogo } from '@/Components/branding/Logo';
import { Button } from '@/Components/ui/button';

interface PublicNavProps {
  canLogin?: boolean;
  canRegister?: boolean;
  currentPath?: string;
}

const navLinks = [
  { href: '/features/billing', label: 'Billing' },
  { href: '/features/feature-flags', label: 'Feature Flags' },
  { href: '/features/admin-panel', label: 'Admin Panel' },
  { href: '/pricing', label: 'Pricing' },
];

export function PublicNav({
  canLogin = false,
  canRegister = false,
  currentPath = '',
}: PublicNavProps) {
  const [mobileOpen, setMobileOpen] = useState(false);
  const menuRef = useRef<HTMLDivElement>(null);

  // Move focus to the first menu item when mobile menu opens
  useEffect(() => {
    if (mobileOpen && menuRef.current) {
      const firstFocusable = menuRef.current.querySelector<HTMLElement>('a, button');
      firstFocusable?.focus();
    }
  }, [mobileOpen]);

  return (
    <nav className="container relative z-10">
      <div className="flex items-center justify-between py-6">
        <Link href="/" className="flex items-center gap-2">
          <Logo className="h-8 w-8" />
          <TextLogo className="text-xl font-bold" />
        </Link>

        {/* Desktop nav */}
        <div className="hidden items-center gap-4 sm:flex">
          {navLinks.map(({ href, label }) => (
            <Link
              key={href}
              href={href}
              className={`text-sm transition-colors hover:text-foreground ${
                currentPath === href
                  ? 'font-medium text-foreground'
                  : 'text-muted-foreground'
              }`}
            >
              {label}
            </Link>
          ))}
          {canLogin && (
            <Button variant="ghost" size="sm" asChild>
              <Link href="/login">Log in</Link>
            </Button>
          )}
          {canRegister && (
            <Button size="sm" asChild>
              <Link href="/register">
                Get Started
                <ArrowRight className="ml-1 h-3 w-3" />
              </Link>
            </Button>
          )}
        </div>

        {/* Mobile hamburger */}
        <button
          className="sm:hidden p-2 text-muted-foreground hover:text-foreground"
          onClick={() => setMobileOpen((prev) => !prev)}
          aria-label={mobileOpen ? 'Close menu' : 'Open menu'}
          aria-expanded={mobileOpen}
          aria-controls="mobile-nav-menu"
        >
          {mobileOpen ? (
            <X className="h-5 w-5" aria-hidden="true" />
          ) : (
            <Menu className="h-5 w-5" aria-hidden="true" />
          )}
        </button>
      </div>

      {/* Mobile menu */}
      {mobileOpen && (
        <div id="mobile-nav-menu" ref={menuRef} className="sm:hidden border-t border-border/70 pb-4">
          <div className="flex flex-col gap-1 pt-4">
            {navLinks.map(({ href, label }) => (
              <Link
                key={href}
                href={href}
                className={`rounded-md px-2 py-2 text-sm transition-colors hover:text-foreground ${
                  currentPath === href
                    ? 'bg-muted font-medium text-foreground'
                    : 'text-muted-foreground'
                }`}
                onClick={() => setMobileOpen(false)}
              >
                {label}
              </Link>
            ))}
            {(canLogin || canRegister) && (
              <div className="mt-3 flex flex-col gap-2 border-t border-border/70 pt-3">
                {canLogin && (
                  <Button variant="ghost" size="sm" asChild className="w-full justify-start">
                    <Link href="/login" onClick={() => setMobileOpen(false)}>
                      Log in
                    </Link>
                  </Button>
                )}
                {canRegister && (
                  <Button size="sm" asChild className="w-full">
                    <Link href="/register" onClick={() => setMobileOpen(false)}>
                      Get Started
                      <ArrowRight className="ml-1 h-3 w-3" />
                    </Link>
                  </Button>
                )}
              </div>
            )}
          </div>
        </div>
      )}
    </nav>
  );
}
