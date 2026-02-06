import { Mail, Lock } from "lucide-react";

import { FormEventHandler } from "react";

import { Link, useForm } from "@inertiajs/react";

import InputError from "@/Components/InputError";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { LoadingButton } from "@/Components/ui/loading-button";
import AuthLayout from "@/Layouts/AuthLayout";

interface ResetPasswordProps {
  token: string;
  email: string;
}

export default function ResetPassword({ token, email }: ResetPasswordProps) {
  const { data, setData, post, processing, errors, reset } = useForm({
    token,
    email,
    password: "",
    password_confirmation: "",
  });

  const submit: FormEventHandler = (e) => {
    e.preventDefault();
    post(route("password.store"), {
      onFinish: () => reset("password", "password_confirmation"),
    });
  };

  return (
    <AuthLayout>
      <div className="space-y-8">
        <div className="text-center lg:text-left">
          <h2 className="text-2xl md:text-3xl font-bold text-foreground">Reset your password</h2>
          <p className="mt-2 text-muted-foreground">
            Create a new password for your account.
          </p>
        </div>

        <form onSubmit={submit} className="space-y-5">
          <div className="space-y-2">
            <Label htmlFor="email">Email address</Label>
            <div className="relative flex items-center">
              <Mail className="absolute left-3 h-4 w-4 text-muted-foreground" />
              <Input
                id="email"
                type="email"
                name="email"
                value={data.email}
                className="pl-10"
                autoComplete="username"
                onChange={(e) => setData("email", e.target.value)}
                required
                aria-describedby={errors.email ? "reset-email-error" : undefined}
                aria-invalid={!!errors.email}
              />
            </div>
            <InputError id="reset-email-error" message={errors.email} className="text-xs" />
          </div>

          <div className="space-y-2">
            <Label htmlFor="password">Password</Label>
            <div className="relative flex items-center">
              <Lock className="absolute left-3 h-4 w-4 text-muted-foreground" />
              <Input
                id="password"
                type="password"
                name="password"
                value={data.password}
                className="pl-10"
                autoComplete="new-password"
                autoFocus
                onChange={(e) => setData("password", e.target.value)}
                required
                aria-describedby={errors.password ? "reset-password-error" : undefined}
                aria-invalid={!!errors.password}
              />
            </div>
            <InputError id="reset-password-error" message={errors.password} className="text-xs" />
          </div>

          <div className="space-y-2">
            <Label htmlFor="password_confirmation">Confirm password</Label>
            <div className="relative flex items-center">
              <Lock className="absolute left-3 h-4 w-4 text-muted-foreground" />
              <Input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                value={data.password_confirmation}
                className="pl-10"
                autoComplete="new-password"
                onChange={(e) => setData("password_confirmation", e.target.value)}
                required
                aria-describedby={errors.password_confirmation ? "reset-password-confirmation-error" : undefined}
                aria-invalid={!!errors.password_confirmation}
              />
            </div>
            <InputError id="reset-password-confirmation-error" message={errors.password_confirmation} className="text-xs" />
          </div>

          <LoadingButton type="submit" className="w-full" size="lg" loading={processing} loadingText="Resetting...">
            Reset Password
          </LoadingButton>
        </form>

        <p className="text-center text-sm text-muted-foreground">
          Remembered it?{" "}
          <Link
            href={route("login")}
            className="font-medium text-primary hover:text-primary/80 transition-colors"
          >
            Back to sign in
          </Link>
        </p>
      </div>
    </AuthLayout>
  );
}
