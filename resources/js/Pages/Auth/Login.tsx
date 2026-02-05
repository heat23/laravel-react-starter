import { Eye, EyeOff, Mail, Lock, Github, ArrowRight } from "lucide-react";
import { z } from "zod";

import { useState, FormEventHandler } from "react";

import { Link, useForm } from "@inertiajs/react";

import InputError from "@/Components/InputError";
import { LegalContentModal } from "@/Components/legal/LegalContentModal";
import { Button } from "@/Components/ui/button";
import { Checkbox } from "@/Components/ui/checkbox";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Separator } from "@/Components/ui/separator";
import AuthLayout from "@/Layouts/AuthLayout";

// P1-004: Client-side Zod validation for instant feedback
const loginSchema = z.object({
  email: z.string().min(1, "Email is required").email("Please enter a valid email address"),
  password: z.string().min(1, "Password is required"),
});

interface LoginProps {
  status?: string;
  canResetPassword: boolean;
  error?: string;
  rememberDays?: number;
  features?: {
    socialAuth?: boolean;
  };
}

export default function Login({ status, canResetPassword, error, rememberDays = 30, features }: LoginProps) {
  const [showPassword, setShowPassword] = useState(false);
  const [socialLoading, setSocialLoading] = useState<string | null>(null);
  const [clientErrors, setClientErrors] = useState<{ email?: string; password?: string }>({});
  const [legalModal, setLegalModal] = useState<"terms" | "privacy" | null>(null);
  const { data, setData, post, processing, errors, reset } = useForm({
    email: "",
    password: "",
    remember: false,
  });

  // Client-side validation on blur for instant feedback
  const validateField = (field: "email" | "password", value: string) => {
    const result = loginSchema.shape[field].safeParse(value);
    setClientErrors((prev) => ({
      ...prev,
      [field]: result.success ? undefined : result.error.errors[0]?.message,
    }));
  };

  const handleSubmit: FormEventHandler = (e) => {
    e.preventDefault();

    // Validate all fields before submission
    const result = loginSchema.safeParse(data);
    if (!result.success) {
      const fieldErrors: { email?: string; password?: string } = {};
      result.error.errors.forEach((err) => {
        const field = err.path[0] as "email" | "password";
        fieldErrors[field] = err.message;
      });
      setClientErrors(fieldErrors);
      return;
    }

    setClientErrors({});
    post(route("login"), {
      onFinish: () => reset("password"),
    });
  };

  const handleSocialLogin = (provider: string) => {
    setSocialLoading(provider);
    window.location.href = route("social.redirect", { provider });
  };

  const footer = (
    <>
      By signing in, you agree to our{" "}
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
    </>
  );

  return (
    <AuthLayout footer={footer}>
      <div className="space-y-8">
        {/* Header */}
        <div className="text-center lg:text-left">
          <h2 className="text-2xl md:text-3xl font-bold text-foreground">
            Welcome back
          </h2>
          <p className="mt-2 text-muted-foreground">
            Sign in to your account to continue
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
                or continue with email
              </span>
            </div>
          </>
        )}

        {/* Login Form */}
        {status && (
          <div className="rounded-md border border-success/20 bg-success/5 px-3 py-2 text-sm text-success">
            {status}
          </div>
        )}

        {error && (
          <div className="rounded-md border border-destructive/20 bg-destructive/5 px-3 py-2 text-sm text-destructive">
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-5">
          <div className="space-y-2">
            <Label htmlFor="email">Email address</Label>
            <div className="relative flex items-center">
              <Mail className="absolute left-3 h-4 w-4 text-muted-foreground" />
              <Input
                id="email"
                type="email"
                placeholder="you@example.com"
                value={data.email}
                onChange={(e) => {
                  setData("email", e.target.value);
                  // Clear client error when user starts typing
                  if (clientErrors.email) {
                    setClientErrors((prev) => ({ ...prev, email: undefined }));
                  }
                }}
                onBlur={(e) => validateField("email", e.target.value)}
                className="pl-10"
                autoComplete="username"
                autoFocus
                required
              />
            </div>
            <InputError message={clientErrors.email || errors.email} className="text-xs" />
          </div>

          <div className="space-y-2">
            <div className="flex items-center justify-between">
              <Label htmlFor="password">Password</Label>
              {canResetPassword && (
                <Link
                  href={route("password.request")}
                  className="text-sm text-primary hover:text-primary/80 transition-colors"
                >
                  Forgot password?
                </Link>
              )}
            </div>
            <div className="relative flex items-center">
              <Lock className="absolute left-3 h-4 w-4 text-muted-foreground" />
              <Input
                id="password"
                type={showPassword ? "text" : "password"}
                placeholder="Enter your password"
                value={data.password}
                onChange={(e) => {
                  setData("password", e.target.value);
                  // Clear client error when user starts typing
                  if (clientErrors.password) {
                    setClientErrors((prev) => ({ ...prev, password: undefined }));
                  }
                }}
                onBlur={(e) => validateField("password", e.target.value)}
                className="pl-10 pr-10"
                autoComplete="current-password"
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
            <InputError message={clientErrors.password || errors.password} className="text-xs" />
          </div>

          <div className="flex items-center space-x-2">
            <Checkbox
              id="remember"
              checked={data.remember}
              onCheckedChange={(checked) => setData("remember", checked === true)}
            />
            <Label htmlFor="remember" className="text-sm font-normal text-muted-foreground cursor-pointer">
              Keep me signed in for {rememberDays} days
            </Label>
          </div>

          <Button type="submit" className="w-full group" size="lg" disabled={processing}>
            {processing ? "Signing in..." : "Sign in"}
            <ArrowRight className="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
          </Button>
        </form>

        {/* Register Link */}
        <p className="text-center text-sm text-muted-foreground">
          Don't have an account?{" "}
          <Link
            href={route("register")}
            className="font-medium text-primary hover:text-primary/80 transition-colors"
          >
            Create one for free
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
