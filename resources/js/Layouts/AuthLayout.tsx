import { PropsWithChildren, ReactNode } from "react";
import { Head, Link } from "@inertiajs/react";
import { Logo, TextLogo } from "@/Components/branding/Logo";
import { ThemeToggle } from "@/Components/theme";

interface AuthLayoutProps extends PropsWithChildren {
  title?: string;
  leftContent?: ReactNode;
  leftFooter?: ReactNode;
  footer?: ReactNode;
}

export default function AuthLayout({
  children,
  title,
  leftContent,
  leftFooter,
  footer,
}: AuthLayoutProps) {
  const appName = import.meta.env.VITE_APP_NAME || "Laravel";

  return (
    <>
      {title && <Head title={title} />}

      <div className="min-h-screen flex">
        {/* Left Panel - Branding (hidden on mobile) */}
        <div className="hidden lg:flex lg:w-1/2 bg-brand-surface text-brand-surface-foreground p-12 flex-col justify-between">
          <div>
            <Link href="/" className="flex items-center gap-2 mb-12">
              <Logo className="h-10 w-10" />
              <TextLogo className="text-2xl font-bold" />
            </Link>

            {leftContent || (
              <div className="max-w-lg space-y-6">
                <h1 className="text-4xl font-bold leading-tight">
                  Welcome to {appName}
                </h1>
                <p className="text-lg text-brand-surface-foreground/70">
                  Sign in to access your dashboard and manage your account.
                </p>
              </div>
            )}
          </div>

          {leftFooter && <div>{leftFooter}</div>}
        </div>

        {/* Right Panel - Form */}
        <div className="flex-1 flex flex-col">
          {/* Mobile Header */}
          <header className="lg:hidden flex items-center justify-between p-4 border-b">
            <Link href="/" className="flex items-center gap-2">
              <Logo className="h-8 w-8" />
              <TextLogo className="font-bold" />
            </Link>
            <ThemeToggle />
          </header>

          {/* Desktop Theme Toggle */}
          <div className="hidden lg:flex justify-end p-6">
            <ThemeToggle />
          </div>

          {/* Form Container */}
          <div className="flex-1 flex items-center justify-center p-6 lg:p-12">
            <div className="w-full max-w-md">{children}</div>
          </div>

          {/* Footer */}
          {footer && (
            <footer className="p-6 text-center text-sm text-muted-foreground">
              {footer}
            </footer>
          )}
        </div>
      </div>
    </>
  );
}
