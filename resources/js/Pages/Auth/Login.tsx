import { Eye, EyeOff, Mail, Lock, ArrowRight } from "lucide-react";
import { z } from "zod";

import { useState, FormEventHandler } from "react";

import { Link, useForm } from "@inertiajs/react";

import { SocialAuthButtons } from "@/Components/auth/SocialAuthButtons";
import InputError from "@/Components/InputError";
import { LegalContentModal } from "@/Components/legal/LegalContentModal";
import { Checkbox } from "@/Components/ui/checkbox";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { LoadingButton } from "@/Components/ui/loading-button";
import { useFormValidation } from "@/hooks/useFormValidation";
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
  const { errors: clientErrors, validateField, validateAll, clearError } = useFormValidation(loginSchema);
  const [legalModal, setLegalModal] = useState<"terms" | "privacy" | null>(null);

  // Helper for grammatically correct day/days
  const dayText = rememberDays === 1 ? 'day' : 'days';

  const { data, setData, post, processing, errors, reset } = useForm({
    email: "",
    password: "",
    remember: false,
  });

  const handleSubmit: FormEventHandler = (e) => {
    e.preventDefault();

    if (!validateAll(data)) return;

    post(route("login"), {
      onFinish: () => reset("password"),
    });
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
          <SocialAuthButtons processing={processing} separatorText="or continue with email" />
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
                  if (clientErrors.email) clearError("email");
                }}
                onBlur={(e) => validateField("email", e.target.value)}
                className="pl-10"
                autoComplete="username"
                autoFocus
                required
                aria-describedby={(clientErrors.email || errors.email) ? "login-email-error" : undefined}
                aria-invalid={!!(clientErrors.email || errors.email)}
              />
            </div>
            <InputError id="login-email-error" message={clientErrors.email || errors.email} className="text-xs" />
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
                  if (clientErrors.password) clearError("password");
                }}
                onBlur={(e) => validateField("password", e.target.value)}
                className="pl-10 pr-10"
                autoComplete="current-password"
                required
                aria-describedby={(clientErrors.password || errors.password) ? "login-password-error" : undefined}
                aria-invalid={!!(clientErrors.password || errors.password)}
              />
              <button
                type="button"
                onClick={() => setShowPassword(!showPassword)}
                className="absolute right-3 text-muted-foreground hover:text-foreground transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 rounded-sm"
                aria-label={showPassword ? "Hide password" : "Show password"}
                aria-pressed={showPassword}
              >
                {showPassword ? (
                  <EyeOff className="h-4 w-4" />
                ) : (
                  <Eye className="h-4 w-4" />
                )}
              </button>
            </div>
            <InputError id="login-password-error" message={clientErrors.password || errors.password} className="text-xs" />
          </div>

          <div className="flex items-center space-x-2">
            <Checkbox
              id="remember"
              checked={data.remember}
              onCheckedChange={(checked) => setData("remember", checked === true)}
            />
            <Label htmlFor="remember" className="text-sm font-normal text-muted-foreground cursor-pointer">
              Keep me signed in for {rememberDays} {dayText}
            </Label>
          </div>

          <LoadingButton type="submit" className="w-full group" size="lg" loading={processing} loadingText="Signing in...">
            Sign in
            <ArrowRight className="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
          </LoadingButton>
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
