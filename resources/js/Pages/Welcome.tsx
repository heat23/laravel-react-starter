import { ArrowRight, CheckCircle2, Layers3, Shield, Sparkles, Zap } from "lucide-react";

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
    title: "Secure foundation",
    description: "Built with security best practices including CSRF protection, XSS prevention, and secure authentication.",
  },
  {
    icon: Layers3,
    title: "Modular by default",
    description: "Feature flags let you enable billing, API tokens, webhooks, and admin tools when your product is ready for them.",
  },
  {
    icon: Zap,
    title: "Production-minded",
    description: "Typed React pages, reusable UI primitives, and tested auth flows keep you moving without rewriting the basics.",
  },
];

const starterHighlights = [
  "Auth, profile, and security flows included",
  "Starter-friendly billing and admin scaffolding",
  "Design tokens you can rebrand quickly",
];

const techStack = ["Laravel 12", "React 18", "TypeScript", "Tailwind CSS v4", "Inertia.js"];

type WelcomeComponent = ((props: WelcomeProps) => JSX.Element) & {
  disableGlobalUi?: boolean;
};

const Welcome: WelcomeComponent = ({ canLogin, canRegister }) => {
  const appName = import.meta.env.VITE_APP_NAME || "Laravel";

  return (
    <>
      <Head title="Welcome">
        <meta name="description" content="A flexible Laravel + React starter with auth, feature flags, billing scaffolding, and a UI foundation you can shape into your product." />
        <meta property="og:title" content={`${appName} - Start with the parts every SaaS needs`} />
        <meta property="og:description" content="A flexible Laravel + React starter with auth, feature flags, billing scaffolding, and a UI foundation you can shape into your product." />
        <meta property="og:type" content="website" />
        <meta name="twitter:card" content="summary" />
        <meta name="twitter:title" content={`${appName} - Start with the parts every SaaS needs`} />
        <meta name="twitter:description" content="A flexible Laravel + React starter with auth, feature flags, billing scaffolding, and a UI foundation you can shape into your product." />
      </Head>

      <div className="relative min-h-screen overflow-hidden bg-gradient-to-b from-background via-background to-muted/30">
        <div
          aria-hidden="true"
          className="absolute inset-x-0 top-0 h-[32rem] bg-[radial-gradient(circle_at_top_right,_hsl(var(--primary)/0.18),_transparent_34%),radial-gradient(circle_at_top_left,_hsl(var(--accent)/0.12),_transparent_28%)]"
        />
        <div
          aria-hidden="true"
          className="absolute inset-x-6 top-28 mx-auto hidden h-64 max-w-5xl rounded-[2.5rem] border border-border/60 bg-card/50 blur-3xl lg:block"
        />
        {/* Navigation */}
        <nav className="container relative z-10 flex items-center justify-between py-6">
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

        <main id="main-content">
          {/* Hero Section */}
          <section className="container relative z-10 py-24">
            <div className="mx-auto max-w-5xl">
              <div className="mx-auto max-w-3xl text-center">
                <div className="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-4 py-2 text-sm font-medium text-primary">
                  <Sparkles className="h-4 w-4" />
                  Starter-ready by default
                </div>
                <h1 className="mt-6 text-4xl font-bold tracking-tight sm:text-5xl md:text-6xl">
                  Start with the parts
                  <br />
                  <span className="text-primary">every SaaS needs</span>
                </h1>
                <p className="mt-6 text-lg text-muted-foreground md:text-xl">
                  A flexible Laravel + React starter with authentication, feature flags,
                  billing scaffolding, and a UI foundation you can shape into your product.
                </p>
              </div>
              <div className="flex flex-wrap items-center justify-center gap-4 pt-4">
                {canRegister && (
                  <Button size="lg" asChild>
                    <Link href={route("register")}>
                      Create Your First Account
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
              <div className="mt-12 grid gap-4 md:grid-cols-3">
                {starterHighlights.map((highlight) => (
                  <div
                    key={highlight}
                    className="rounded-2xl border border-border/70 bg-card/80 px-5 py-4 text-sm font-medium text-foreground shadow-sm backdrop-blur"
                  >
                    {highlight}
                  </div>
                ))}
              </div>
            </div>
          </section>

          {/* Features Section */}
          <section className="container py-24">
            <div className="mx-auto max-w-5xl">
              <h2 className="mb-12 text-center text-3xl font-bold">
                Starter defaults you can actually ship with
              </h2>
              <div className="grid gap-8 md:grid-cols-3">
                {features.map((feature) => (
                  <div
                    key={feature.title}
                    className="rounded-2xl border border-border/70 bg-card p-6 text-card-foreground shadow-sm"
                  >
                    <div className="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                      <feature.icon className="h-5 w-5 text-primary" />
                    </div>
                    <h3 className="mb-2 text-lg font-semibold">{feature.title}</h3>
                    <p className="text-sm text-muted-foreground">{feature.description}</p>
                  </div>
                ))}
              </div>
            </div>
          </section>

          {/* Tech Stack Section */}
          <section className="container border-t py-24">
            <div className="mx-auto max-w-4xl text-center">
              <h2 className="mb-8 text-2xl font-bold">Modern stack, ready to customize</h2>
              <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                {techStack.map((item) => (
                  <div
                    key={item}
                    className="flex items-center justify-center gap-2 rounded-2xl border border-border/70 bg-card/70 px-4 py-3 text-sm font-medium text-foreground"
                  >
                    <CheckCircle2 className="h-4 w-4 text-success" />
                    <span>{item}</span>
                  </div>
                ))}
              </div>
            </div>
          </section>
        </main>

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
};

Welcome.disableGlobalUi = true;

export default Welcome;
