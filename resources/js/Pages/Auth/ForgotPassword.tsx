import { Mail, CheckCircle2 } from "lucide-react";

import { FormEventHandler } from "react";

import { Link, useForm } from "@inertiajs/react";

import InputError from "@/Components/InputError";
import { Alert, AlertDescription, AlertTitle } from "@/Components/ui/alert";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { LoadingButton } from "@/Components/ui/loading-button";
import AuthLayout from "@/Layouts/AuthLayout";

interface ForgotPasswordProps {
  status?: string;
}

export default function ForgotPassword({ status }: ForgotPasswordProps) {
  const { data, setData, post, processing, errors } = useForm({
    email: "",
  });

  const submit: FormEventHandler = (e) => {
    e.preventDefault();
    post(route("password.email"));
  };

  return (
    <AuthLayout>
      <div className="space-y-8">
        <div className="text-center lg:text-left">
          <h2 className="text-2xl md:text-3xl font-bold text-foreground">Forgot your password?</h2>
          <p className="mt-2 text-muted-foreground">
            No problem. Enter your email and we'll send you a password reset link.
          </p>
        </div>

        {status && (
          <Alert className="border-success/20 bg-success/5 text-success">
            <CheckCircle2 className="h-4 w-4" />
            <AlertTitle>Email sent</AlertTitle>
            <AlertDescription>{status}</AlertDescription>
          </Alert>
        )}

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
                autoComplete="email"
                autoFocus
                onChange={(e) => setData("email", e.target.value)}
                required
                aria-describedby={errors.email ? "forgot-email-error" : undefined}
                aria-invalid={!!errors.email}
              />
            </div>
            <InputError id="forgot-email-error" message={errors.email} className="text-xs" />
          </div>

          <LoadingButton type="submit" className="w-full" size="lg" loading={processing} loadingText="Sending...">
            Email Password Reset Link
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
