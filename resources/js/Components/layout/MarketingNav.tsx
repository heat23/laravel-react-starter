import { ArrowRight } from 'lucide-react';

import { Link } from '@inertiajs/react';

import { Logo, TextLogo } from '@/Components/branding/Logo';
import { Button } from '@/Components/ui/button';

interface MarketingNavProps {
  canLogin?: boolean;
  canRegister?: boolean;
  currentPath?: string;
}

export function MarketingNav({
  canLogin = true,
  canRegister = true,
  currentPath = '',
}: MarketingNavProps) {
  return (
    <nav className="container relative z-10 flex items-center justify-between py-6">
      <Link href="/" className="flex items-center gap-2">
        <Logo className="h-8 w-8" />
        <TextLogo className="text-xl font-bold" />
      </Link>

      <div className="flex items-center gap-4">
        <Link
          href="/features/billing"
          className={`hidden text-sm transition-colors hover:text-foreground sm:inline ${
            currentPath === '/features/billing'
              ? 'text-foreground font-medium'
              : 'text-muted-foreground'
          }`}
        >
          Billing
        </Link>
        <Link
          href="/features/feature-flags"
          className={`hidden text-sm transition-colors hover:text-foreground sm:inline ${
            currentPath === '/features/feature-flags'
              ? 'text-foreground font-medium'
              : 'text-muted-foreground'
          }`}
        >
          Feature Flags
        </Link>
        <Link
          href="/features/admin-panel"
          className={`hidden text-sm transition-colors hover:text-foreground sm:inline ${
            currentPath === '/features/admin-panel'
              ? 'text-foreground font-medium'
              : 'text-muted-foreground'
          }`}
        >
          Admin Panel
        </Link>
        <Link
          href="/pricing"
          className={`hidden text-sm transition-colors hover:text-foreground sm:inline ${
            currentPath === '/pricing'
              ? 'text-foreground font-medium'
              : 'text-muted-foreground'
          }`}
        >
          Pricing
        </Link>
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
    </nav>
  );
}
