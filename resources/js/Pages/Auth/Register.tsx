import { useState, FormEventHandler } from "react";
import { Link, useForm } from "@inertiajs/react";
import {
  Eye,
  EyeOff,
  Mail,
  Lock,
  User,
  Github,
  ArrowRight,
  Check,
  X,
  Shield,
  Zap,
  Bell,
  GitBranch,
  CheckCircle2,
  LucideIcon,
} from "lucide-react";
import InputError from "@/Components/InputError";
import { LegalContentModal } from "@/Components/legal/LegalContentModal";
import { Button } from "@/Components/ui/button";
import { Checkbox } from "@/Components/ui/checkbox";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Progress } from "@/Components/ui/progress";
import { Separator } from "@/Components/ui/separator";
import AuthLayout from "@/Layouts/AuthLayout";

interface PasswordRequirement {
  id: string;
  label: string;
  test: (p: string) => boolean;
}

interface Feature {
  icon: LucideIcon;
  title: string;
  description: string;
}

interface RegisterProps {
  error?: string;
  features?: {
    socialAuth?: boolean;
  };
}

const passwordRequirements: PasswordRequirement[] = [
  { id: "length", label: "At least 8 characters", test: (p) => p.length >= 8 },
  { id: "uppercase", label: "One uppercase letter", test: (p) => /[A-Z]/.test(p) },
  { id: "lowercase", label: "One lowercase letter", test: (p) => /[a-z]/.test(p) },
  { id: "number", label: "One number", test: (p) => /\d/.test(p) },
];

const features: Feature[] = [
  {
    icon: Shield,
    title: "Secure by Default",
    description: "Built with security best practices including encryption and secure authentication.",
  },
  {
    icon: GitBranch,
    title: "Modern Stack",
    description: "Powered by Laravel, React, and TypeScript for a great developer experience.",
  },
  {
    icon: Bell,
    title: "Stay Informed",
    description: "Real-time notifications keep you updated on what matters most.",
  },
  {
    icon: Zap,
    title: "Lightning Fast",
    description: "Optimized for performance with instant page loads and smooth interactions.",
  },
];

export default function Register({ error, features }: RegisterProps) {
  const [showPassword, setShowPassword] = useState(false);
  const [acceptTerms, setAcceptTerms] = useState(false);
  const [socialLoading, setSocialLoading] = useState<string | null>(null);
  const [legalModal, setLegalModal] = useState<"terms" | "privacy" | null>(null);
  const { data, setData, post, processing, errors, reset } = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
  });

  const passedRequirements = passwordRequirements.filter((req) => req.test(data.password)).length;
  const passwordStrength = passwordRequirements.length > 0 ? (passedRequirements / passwordRequirements.length) * 100 : 0;

  const handleSubmit: FormEventHandler = (e) => {
    e.preventDefault();
    post(route("register"), {
      onFinish: () => reset("password", "password_confirmation"),
    });
  };

  const handleSocialLogin = (provider: string) => {
    setSocialLoading(provider);
    window.location.href = route("social.redirect", { provider });
  };

  const appName = import.meta.env.VITE_APP_NAME || "Our Application";

  const leftContent = (
    <div className="max-w-lg space-y-8">
      <div className="space-y-4">
        <h1 className="text-4xl font-bold leading-tight">
          Welcome to {appName}.
          <br />
          <span className="text-brand-surface-foreground/80">Get started today.</span>
        </h1>
        <p className="text-lg text-brand-surface-foreground/70 leading-relaxed">
          Create your account to access all features and start building something amazing.
        </p>
      </div>

      <div className="space-y-4">
        {features.map((feature, index) => (
          <div
            key={feature.title}
            className="flex items-start gap-4 animate-fade-in"
            style={{ animationDelay: `${index * 0.1}s` }}
          >
            <div className="w-10 h-10 rounded-lg bg-brand-surface-foreground/10 flex items-center justify-center flex-shrink-0">
              <feature.icon className="h-5 w-5" />
            </div>
            <div>
              <h3 className="font-semibold mb-0.5">{feature.title}</h3>
              <p className="text-sm text-brand-surface-foreground/60">{feature.description}</p>
            </div>
          </div>
        ))}
      </div>
    </div>
  );

  const leftFooter = (
    <div className="pt-6 border-t border-brand-surface-foreground/10">
      <div className="flex flex-wrap items-center gap-4 text-sm">
        <span className="flex items-center gap-1.5">
          <CheckCircle2 className="h-4 w-4" />
          Free to get started
        </span>
        <span className="flex items-center gap-1.5">
          <CheckCircle2 className="h-4 w-4" />
          No credit card required
        </span>
      </div>
    </div>
  );

  const footer = (
    <>
      By creating an account, you agree to our{" "}
      <button
        type="button"
        onClick={() => setLegalModal("terms")}
        className="text-primary hover:underline"
      >
        Terms of Service
      </button>
    </>
  );

  return (
    <AuthLayout leftContent={leftContent} leftFooter={leftFooter} footer={footer}>
      <div className="space-y-8">
        {/* Header */}
        <div className="text-center lg:text-left">
          <h2 className="text-2xl md:text-3xl font-bold text-foreground">
            Create your account
          </h2>
          <p className="mt-2 text-muted-foreground">
            Start your journey with us today
          </p>
        </div>

        {/* Social Login - Only show if feature is enabled */}
        {features?.socialAuth && (
          <>
            <div className="grid grid-cols-2 gap-3">
              <Button
                variant="outline"
                className="w-full h-12 text-base"
                type="button"
                onClick={() => handleSocialLogin("github")}
                disabled={socialLoading !== null}
              >
                <Github className="mr-2 h-5 w-5" />
                {socialLoading === "github" ? "Redirecting..." : "GitHub"}
              </Button>
              <Button
                variant="outline"
                className="w-full h-12 text-base"
                type="button"
                onClick={() => handleSocialLogin("google")}
                disabled={socialLoading !== null}
              >
                <svg className="mr-2 h-5 w-5" viewBox="0 0 24 24">
                  <path
                    d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                    fill="#4285F4"
                  />
                  <path
                    d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                    fill="#34A853"
                  />
                  <path
                    d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                    fill="#FBBC05"
                  />
                  <path
                    d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                    fill="#EA4335"
                  />
                </svg>
                {socialLoading === "google" ? "Redirecting..." : "Google"}
              </Button>
            </div>

            <div className="relative">
              <Separator />
              <span className="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 bg-background px-4 text-sm text-muted-foreground">
                or register with email
              </span>
            </div>
          </>
        )}

        {error && (
          <div className="rounded-md border border-destructive/20 bg-destructive/5 px-3 py-2 text-sm text-destructive">
            {error}
          </div>
        )}

        {/* Registration Form */}
        <form onSubmit={handleSubmit} className="space-y-5">
          <div className="space-y-2">
            <Label htmlFor="name">Full name</Label>
            <div className="relative flex items-center">
              <User className="absolute left-3 h-4 w-4 text-muted-foreground" />
              <Input
                id="name"
                type="text"
                placeholder="John Doe"
                value={data.name}
                onChange={(e) => setData("name", e.target.value)}
                className="pl-10"
                autoComplete="name"
                required
              />
            </div>
            <InputError message={errors.name} className="text-xs" />
          </div>

          <div className="space-y-2">
            <Label htmlFor="email">Email address</Label>
            <div className="relative flex items-center">
              <Mail className="absolute left-3 h-4 w-4 text-muted-foreground" />
              <Input
                id="email"
                type="email"
                placeholder="you@example.com"
                value={data.email}
                onChange={(e) => setData("email", e.target.value)}
                className="pl-10"
                autoComplete="username"
                required
              />
            </div>
            <InputError message={errors.email} className="text-xs" />
          </div>

          <div className="space-y-3">
            <Label htmlFor="password">Password</Label>
            <div className="relative flex items-center">
              <Lock className="absolute left-3 h-4 w-4 text-muted-foreground" />
              <Input
                id="password"
                type={showPassword ? "text" : "password"}
                placeholder="Create a strong password"
                value={data.password}
                onChange={(e) => setData("password", e.target.value)}
                className="pl-10 pr-10"
                autoComplete="new-password"
                required
              />
              <button
                type="button"
                onClick={() => setShowPassword(!showPassword)}
                className="absolute right-3 text-muted-foreground hover:text-foreground transition-colors"
                aria-label="Toggle password visibility"
              >
                {showPassword ? (
                  <EyeOff className="h-4 w-4" />
                ) : (
                  <Eye className="h-4 w-4" />
                )}
              </button>
            </div>

            {/* Password Strength Indicator */}
            {data.password.length > 0 && (
              <div className="space-y-3 animate-fade-in">
                <div className="space-y-1.5">
                  <div className="flex justify-between text-xs">
                    <span className="text-muted-foreground">Password strength</span>
                    <span className={`font-medium ${
                      passwordStrength <= 25 ? "text-destructive" :
                      passwordStrength <= 50 ? "text-warning" :
                      passwordStrength <= 75 ? "text-info" : "text-success"
                    }`}>
                      {passwordStrength <= 25 ? "Weak" :
                       passwordStrength <= 50 ? "Fair" :
                       passwordStrength <= 75 ? "Good" : "Strong"}
                    </span>
                  </div>
                  <Progress value={passwordStrength} className="h-1.5" />
                </div>

                <div className="grid grid-cols-2 gap-2 text-xs">
                  {passwordRequirements.map((req) => {
                    const passed = req.test(data.password);
                    return (
                      <div
                        key={req.id}
                        className={`flex items-center gap-1.5 transition-colors ${
                          passed ? "text-success" : "text-muted-foreground"
                        }`}
                      >
                        {passed ? (
                          <Check className="h-3 w-3" />
                        ) : (
                          <X className="h-3 w-3" />
                        )}
                        {req.label}
                      </div>
                    );
                  })}
                </div>
              </div>
            )}
            <InputError message={errors.password} className="text-xs" />
          </div>

          <div className="space-y-2">
            <Label htmlFor="password_confirmation">Confirm password</Label>
            <div className="relative flex items-center">
              <Lock className="absolute left-3 h-4 w-4 text-muted-foreground" />
              <Input
                id="password_confirmation"
                type={showPassword ? "text" : "password"}
                placeholder="Re-enter your password"
                value={data.password_confirmation}
                onChange={(e) => setData("password_confirmation", e.target.value)}
                className="pl-10 pr-10"
                autoComplete="new-password"
                required
              />
            </div>
            <InputError message={errors.password_confirmation} className="text-xs" />
          </div>

          <div className="flex items-start space-x-2">
            <Checkbox
              id="terms"
              checked={acceptTerms}
              onCheckedChange={(checked) => setAcceptTerms(checked === true)}
              className="mt-0.5"
            />
            <Label htmlFor="terms" className="text-sm font-normal text-muted-foreground cursor-pointer leading-relaxed">
              I agree to the{" "}
              <button
                type="button"
                onClick={() => setLegalModal("terms")}
                className="text-primary hover:underline"
              >
                Terms of Service
              </button>
              {" "}and{" "}
              <button
                type="button"
                onClick={() => setLegalModal("privacy")}
                className="text-primary hover:underline"
              >
                Privacy Policy
              </button>
            </Label>
          </div>

          <Button
            type="submit"
            className="w-full group"
            size="lg"
            disabled={processing || !acceptTerms || passwordStrength < 100}
          >
            {processing ? "Creating account..." : "Create account"}
            <ArrowRight className="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
          </Button>
        </form>

        {/* Login Link */}
        <p className="text-center text-sm text-muted-foreground">
          Already have an account?{" "}
          <Link
            href={route("login")}
            className="font-medium text-primary hover:text-primary/80 transition-colors"
          >
            Sign in instead
          </Link>
        </p>
      </div>

      {/* Legal Content Modal */}
      <LegalContentModal
        type={legalModal}
        onClose={() => setLegalModal(null)}
      />
    </AuthLayout>
  );
}
