import {
  Eye,
  EyeOff,
  Mail,
  Lock,
  User,
  ArrowRight,
  Shield,
  Zap,
  Bell,
  GitBranch,
  CheckCircle2,
  LucideIcon,
} from "lucide-react";
import { z } from "zod";

import { useState, FormEventHandler } from "react";

import { Link, useForm } from "@inertiajs/react";

import { PasswordStrengthIndicator, type PasswordRequirement } from "@/Components/auth/PasswordStrengthIndicator";
import { SocialAuthButtons } from "@/Components/auth/SocialAuthButtons";
import InputError from "@/Components/InputError";
import { LegalContentModal } from "@/Components/legal/LegalContentModal";
import { Checkbox } from "@/Components/ui/checkbox";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { LoadingButton } from "@/Components/ui/loading-button";
import { useFormValidation } from "@/hooks/useFormValidation";
import AuthLayout from "@/Layouts/AuthLayout";

const registerSchema = z.object({
  name: z.string().min(1, "Name is required").max(255, "Name is too long"),
  email: z.string().min(1, "Email is required").email("Please enter a valid email address"),
  password: z.string().min(8, "Password must be at least 8 characters"),
  password_confirmation: z.string().min(1, "Please confirm your password"),
}).refine((data) => data.password === data.password_confirmation, {
  message: "Passwords do not match",
  path: ["password_confirmation"],
});

interface Feature {
  icon: LucideIcon;
  title: string;
  description: string;
}

interface RegisterProps {
  error?: string;
  rememberDays?: number;
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

const _features: Feature[] = [
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

export default function Register({ error, rememberDays = 30, features }: RegisterProps) {
  const [showPassword, setShowPassword] = useState(false);
  const [acceptTerms, setAcceptTerms] = useState(false);
  const [legalModal, setLegalModal] = useState<"terms" | "privacy" | null>(null);
  const { errors: clientErrors, validateField, validateAll, clearError, setErrors: setClientErrors } = useFormValidation(registerSchema);

  // Helper for grammatically correct day/days
  const dayText = rememberDays === 1 ? 'day' : 'days';

  const { data, setData, post, processing, errors, reset } = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    remember: false,
  });

  const passedRequirements = passwordRequirements.filter((req) => req.test(data.password)).length;
  const passwordStrength = passwordRequirements.length > 0 ? (passedRequirements / passwordRequirements.length) * 100 : 0;

  const handleSubmit: FormEventHandler = (e) => {
    e.preventDefault();

    if (!validateAll(data)) return;

    post(route("register"), {
      onFinish: () => reset("password", "password_confirmation"),
    });
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
        {_features.map((feature, index) => (
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
          <SocialAuthButtons processing={processing} separatorText="or register with email" />
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
                onChange={(e) => {
                  setData("name", e.target.value);
                  if (clientErrors.name) clearError("name");
                }}
                onBlur={(e) => validateField("name", e.target.value)}
                className="pl-10"
                autoComplete="name"
                required
                aria-describedby={(clientErrors.name || errors.name) ? "register-name-error" : undefined}
                aria-invalid={!!(clientErrors.name || errors.name)}
              />
            </div>
            <InputError id="register-name-error" message={clientErrors.name || errors.name} className="text-xs" />
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
                onChange={(e) => {
                  setData("email", e.target.value);
                  if (clientErrors.email) clearError("email");
                }}
                onBlur={(e) => validateField("email", e.target.value)}
                className="pl-10"
                autoComplete="username"
                required
                aria-describedby={(clientErrors.email || errors.email) ? "register-email-error" : undefined}
                aria-invalid={!!(clientErrors.email || errors.email)}
              />
            </div>
            <InputError id="register-email-error" message={clientErrors.email || errors.email} className="text-xs" />
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
                onChange={(e) => {
                  setData("password", e.target.value);
                  if (clientErrors.password) clearError("password");
                }}
                onBlur={(e) => validateField("password", e.target.value)}
                className="pl-10 pr-10"
                autoComplete="new-password"
                required
                aria-describedby={(clientErrors.password || errors.password) ? "register-password-error" : undefined}
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

            <PasswordStrengthIndicator password={data.password} passwordRequirements={passwordRequirements} />
            <InputError id="register-password-error" message={clientErrors.password || errors.password} className="text-xs" />
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
                onChange={(e) => {
                  setData("password_confirmation", e.target.value);
                  if (clientErrors.password_confirmation) clearError("password_confirmation");
                }}
                onBlur={(e) => {
                  const val = e.target.value;
                  if (!val) {
                    validateField("password_confirmation", val);
                  } else if (val !== data.password) {
                    setClientErrors(prev => ({ ...prev, password_confirmation: "Passwords do not match" }));
                  } else {
                    setClientErrors(prev => ({ ...prev, password_confirmation: undefined }));
                  }
                }}
                className="pl-10 pr-10"
                autoComplete="new-password"
                required
                aria-describedby={(clientErrors.password_confirmation || errors.password_confirmation) ? "register-password-confirmation-error" : undefined}
                aria-invalid={!!(clientErrors.password_confirmation || errors.password_confirmation)}
              />
            </div>
            <InputError id="register-password-confirmation-error" message={clientErrors.password_confirmation || errors.password_confirmation} className="text-xs" />
          </div>

          <div className="flex items-center space-x-2">
            <Checkbox
              id="remember-register"
              checked={data.remember}
              onCheckedChange={(checked) => setData("remember", checked === true)}
            />
            <Label htmlFor="remember-register" className="text-sm font-normal text-muted-foreground cursor-pointer">
              Keep me signed in for {rememberDays} {dayText}
            </Label>
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

          <LoadingButton
            type="submit"
            className="w-full group"
            size="lg"
            loading={processing}
            loadingText="Creating account..."
            disabled={!acceptTerms || passwordStrength < 100}
          >
            Create account
            <ArrowRight className="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
          </LoadingButton>
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
