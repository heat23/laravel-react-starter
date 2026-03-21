import { Link } from '@inertiajs/react';

import { Logo, TextLogo } from '@/Components/branding/Logo';

const productLinks = [
  { href: '/features/billing', label: 'Billing' },
  { href: '/features/feature-flags', label: 'Feature Flags' },
  { href: '/features/admin-panel', label: 'Admin Panel' },
  { href: '/pricing', label: 'Pricing' },
];

const companyLinks = [
  { href: '/contact', label: 'Contact' },
  { href: '/changelog', label: 'Changelog' },
  { href: '/roadmap', label: 'Roadmap' },
];

const legalLinks = [
  { href: '/terms', label: 'Terms' },
  { href: '/privacy', label: 'Privacy' },
];

function resetCookieConsent() {
  try {
    localStorage.removeItem('cookie_consent');
    localStorage.removeItem('cookie_consent_categories');
    localStorage.removeItem('cookie_consent_version');
    localStorage.removeItem('cookie_consent_date');
  } catch {
    // localStorage unavailable
  }
  window.location.reload();
}

export function PublicFooter() {
  const appName = import.meta.env.VITE_APP_NAME || 'Laravel React Starter';

  return (
    <footer className="border-t py-10">
      <div className="container">
        <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
          {/* Brand */}
          <div>
            <Link href="/" className="mb-3 flex items-center gap-2">
              <Logo className="h-6 w-6" />
              <TextLogo className="text-base font-bold" />
            </Link>
            <p className="text-xs text-muted-foreground">
              Production-ready Laravel + React SaaS starter kit.
            </p>
          </div>

          {/* Product */}
          <div>
            <p className="mb-3 text-xs font-semibold uppercase tracking-wider text-foreground">
              Product
            </p>
            <nav aria-label="Product links">
              <ul className="flex flex-col gap-2">
                {productLinks.map(({ href, label }) => (
                  <li key={href}>
                    <Link
                      href={href}
                      className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                    >
                      {label}
                    </Link>
                  </li>
                ))}
              </ul>
            </nav>
          </div>

          {/* Company */}
          <div>
            <p className="mb-3 text-xs font-semibold uppercase tracking-wider text-foreground">
              Company
            </p>
            <nav aria-label="Company links">
              <ul className="flex flex-col gap-2">
                {companyLinks.map(({ href, label }) => (
                  <li key={href}>
                    <Link
                      href={href}
                      className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                    >
                      {label}
                    </Link>
                  </li>
                ))}
              </ul>
            </nav>
          </div>

          {/* Legal */}
          <div>
            <p className="mb-3 text-xs font-semibold uppercase tracking-wider text-foreground">
              Legal
            </p>
            <nav aria-label="Legal links">
              <ul className="flex flex-col gap-2">
                {legalLinks.map(({ href, label }) => (
                  <li key={href}>
                    <Link
                      href={href}
                      className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                    >
                      {label}
                    </Link>
                  </li>
                ))}
                <li>
                  <button
                    type="button"
                    onClick={resetCookieConsent}
                    className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                  >
                    Cookie Preferences
                  </button>
                </li>
              </ul>
            </nav>
          </div>
        </div>

        <div className="mt-8 border-t border-border/70 pt-6">
          <p className="text-center text-xs text-muted-foreground">
            &copy; {new Date().getFullYear()} {appName}. All rights reserved.
          </p>
        </div>
      </div>
    </footer>
  );
}
