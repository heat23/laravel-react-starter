import { ArrowRight, CheckCircle2, Shield, Zap, Users } from "lucide-react";

import { Head, Link } from "@inertiajs/react";

import { Logo, TextLogo } from "@/Components/branding/Logo";
import { Button } from "@/Components/ui/button";

interface WelcomeProps {
  canLogin: boolean;
  canRegister: boolean;
}

const features = [
  {
    icon: Shield,
    title: "Secure by Default",
    description: "Built with security best practices including CSRF protection, XSS prevention, and secure authentication.",
  },
  {
    icon: Zap,
    title: "Lightning Fast",
    description: "Optimized for performance with Laravel Octane support and efficient React rendering.",
  },
  {
    icon: Users,
    title: "User Management",
    description: "Complete authentication system with registration, login, password reset, and email verification.",
  },
];

export default function Welcome({ canLogin, canRegister }: WelcomeProps) {
  const appName = import.meta.env.VITE_APP_NAME || "Laravel";

  return (
    <>
      <Head title="Welcome">
        <meta name="description" content="A modern Laravel starter template with React, TypeScript, and Tailwind CSS. Everything you need to ship faster." />
        <meta property="og:title" content={`${appName} - Build your next great application`} />
        <meta property="og:description" content="A modern Laravel starter template with React, TypeScript, and Tailwind CSS. Everything you need to ship faster." />
        <meta property="og:type" content="website" />
        <meta name="twitter:card" content="summary" />
        <meta name="twitter:title" content={`${appName} - Build your next great application`} />
        <meta name="twitter:description" content="A modern Laravel starter template with React, TypeScript, and Tailwind CSS. Everything you need to ship faster." />
      </Head>

      <div className="min-h-screen bg-gradient-to-b from-background to-muted/30">
        {/* Navigation */}
        <nav className="container flex items-center justify-between py-6">
          <Link href="/" className="flex items-center gap-2">
            <Logo className="h-8 w-8" />
            <TextLogo className="text-xl font-bold" />
          </Link>

          <div className="flex items-center gap-4">
            {canLogin && (
              <Button variant="ghost" asChild>
                <Link href={route("login")}>Log in</Link>
              </Button>
            )}
            {canRegister && (
              <Button asChild>
                <Link href={route("register")}>
                  Get Started
                  <ArrowRight className="ml-2 h-4 w-4" />
                </Link>
              </Button>
            )}
          </div>
        </nav>

        {/* Hero Section */}
        <section className="container py-24 text-center">
          <div className="mx-auto max-w-3xl space-y-6">
            <h1 className="text-4xl font-bold tracking-tight sm:text-5xl md:text-6xl">
              Build your next
              <br />
              <span className="text-primary">great application</span>
            </h1>
            <p className="text-lg text-muted-foreground md:text-xl">
              A modern Laravel starter template with React, TypeScript, and Tailwind CSS.
              Everything you need to ship faster.
            </p>
            <div className="flex flex-wrap items-center justify-center gap-4 pt-4">
              {canRegister && (
                <Button size="lg" asChild>
                  <Link href={route("register")}>
                    Start Building
                    <ArrowRight className="ml-2 h-4 w-4" />
                  </Link>
                </Button>
              )}
              <Button variant="outline" size="lg" asChild>
                <a
                  href="https://laravel.com/docs"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  Documentation
                </a>
              </Button>
            </div>
          </div>
        </section>

        {/* Features Section */}
        <section className="container py-24">
          <div className="mx-auto max-w-5xl">
            <h2 className="text-center text-3xl font-bold mb-12">
              Everything you need to get started
            </h2>
            <div className="grid gap-8 md:grid-cols-3">
              {features.map((feature) => (
                <div
                  key={feature.title}
                  className="rounded-lg border bg-card p-6 text-card-foreground shadow-sm"
                >
                  <div className="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                    <feature.icon className="h-5 w-5 text-primary" />
                  </div>
                  <h3 className="mb-2 font-semibold">{feature.title}</h3>
                  <p className="text-sm text-muted-foreground">{feature.description}</p>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* Tech Stack Section */}
        <section className="container py-24 border-t">
          <div className="mx-auto max-w-3xl text-center">
            <h2 className="text-2xl font-bold mb-8">Built with modern technologies</h2>
            <div className="flex flex-wrap items-center justify-center gap-x-8 gap-y-4 text-muted-foreground">
              <div className="flex items-center gap-2">
                <CheckCircle2 className="h-4 w-4 text-success" />
                <span>Laravel 12</span>
              </div>
              <div className="flex items-center gap-2">
                <CheckCircle2 className="h-4 w-4 text-success" />
                <span>React 18</span>
              </div>
              <div className="flex items-center gap-2">
                <CheckCircle2 className="h-4 w-4 text-success" />
                <span>TypeScript</span>
              </div>
              <div className="flex items-center gap-2">
                <CheckCircle2 className="h-4 w-4 text-success" />
                <span>Tailwind CSS v4</span>
              </div>
              <div className="flex items-center gap-2">
                <CheckCircle2 className="h-4 w-4 text-success" />
                <span>Inertia.js</span>
              </div>
            </div>
          </div>
        </section>

        {/* Footer */}
        <footer className="border-t py-8">
          <div className="container text-center text-sm text-muted-foreground">
            <p>
              &copy; {new Date().getFullYear()} {appName}. All rights reserved.
            </p>
          </div>
        </footer>
      </div>
    </>
  );
}
