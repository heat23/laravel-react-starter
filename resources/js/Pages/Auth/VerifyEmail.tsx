import { CheckCircle2, Mail, RefreshCw, Loader2 } from "lucide-react";

import { useState } from "react";

import { Link, useForm } from "@inertiajs/react";

import { Alert, AlertDescription, AlertTitle } from "@/Components/ui/alert";
import { Button } from "@/Components/ui/button";
import AuthLayout from "@/Layouts/AuthLayout";

interface VerifyEmailProps {
  status?: string;
}

export default function VerifyEmail({ status }: VerifyEmailProps) {
  const { post, processing } = useForm({});
  const [resent, setResent] = useState(false);

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route("verification.send"), {
      onSuccess: () => setResent(true),
    });
  };

  return (
    <AuthLayout>
      <div className="space-y-8">
        <div className="text-center lg:text-left space-y-3">
          <div className="mx-auto lg:mx-0 flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
            <Mail className="h-6 w-6 text-primary" />
          </div>
          <div>
            <h2 className="text-2xl md:text-3xl font-bold text-foreground">Verify your email</h2>
            <p className="mt-2 text-muted-foreground">
              We've sent a verification link to your email address. Click the link to activate your account.
            </p>
          </div>
        </div>

        {(status === "verification-link-sent" || resent) && (
          <Alert
            className="border-success/30 bg-success/10 text-success"
            role="alert"
            aria-live="polite"
          >
            <CheckCircle2 className="h-4 w-4" />
            <AlertTitle>Email sent!</AlertTitle>
            <AlertDescription>
              A new verification link has been sent to your email address.
            </AlertDescription>
          </Alert>
        )}

        <div className="rounded-lg border bg-muted/50 p-4 text-sm text-muted-foreground">
          <p className="font-medium text-foreground mb-2">What happens next?</p>
          <ol className="list-decimal list-inside space-y-1">
            <li>Check your inbox (and spam folder)</li>
            <li>Click the verification link in the email</li>
            <li>You'll be redirected to your dashboard</li>
          </ol>
        </div>

        <form onSubmit={submit} className="space-y-4">
          <div className="text-center text-sm text-muted-foreground">
            Didn't receive the email?
          </div>
          <Button type="submit" className="w-full" size="lg" disabled={processing}>
            {processing ? (
              <>
                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                Sending...
              </>
            ) : (
              <>
                <RefreshCw className="mr-2 h-4 w-4" />
                Resend Verification Email
              </>
            )}
          </Button>
        </form>

        <Button
          variant="ghost"
          asChild
          className="w-full text-muted-foreground mt-2"
        >
          <Link href={route("logout")} method="post" as="button">
            Log out
          </Link>
        </Button>
      </div>
    </AuthLayout>
  );
}
