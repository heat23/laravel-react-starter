import { KeyRound } from "lucide-react";

import { useState, FormEventHandler } from "react";

import { Head, Link, useForm } from "@inertiajs/react";

import InputError from "@/Components/InputError";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import {
  InputOTP,
  InputOTPGroup,
  InputOTPSlot,
} from "@/Components/ui/input-otp";
import { Label } from "@/Components/ui/label";
import { LoadingButton } from "@/Components/ui/loading-button";
import AuthLayout from "@/Layouts/AuthLayout";

export default function TwoFactorChallenge() {
  const [useRecovery, setUseRecovery] = useState(false);

  const { data, setData, post, processing, errors } = useForm({
    code: "",
    recovery_code: "",
  });

  const handleSubmit: FormEventHandler = (e) => {
    e.preventDefault();
    post(route("two-factor.challenge"));
  };

  return (
    <AuthLayout>
      <Head title="Two-Factor Authentication" />
      <div className="space-y-8">
        <div className="text-center lg:text-left">
          <div className="mb-4 flex justify-center lg:justify-start">
            <div className="rounded-full bg-primary/10 p-3">
              <KeyRound className="h-6 w-6 text-primary" />
            </div>
          </div>
          <h2 className="text-2xl md:text-3xl font-bold text-foreground">
            Two-factor authentication
          </h2>
          <p className="mt-2 text-muted-foreground">
            {useRecovery
              ? "Enter one of your emergency recovery codes to access your account."
              : "Enter the 6-digit code from your authenticator app to continue."}
          </p>
        </div>

        <form onSubmit={handleSubmit} className="space-y-5">
          {useRecovery ? (
            <div className="space-y-2">
              <Label htmlFor="recovery_code">Recovery code</Label>
              <Input
                id="recovery_code"
                type="text"
                value={data.recovery_code}
                onChange={(e) => setData("recovery_code", e.target.value)}
                autoComplete="one-time-code"
                autoFocus
                placeholder="XXXXX-XXXXX"
                aria-describedby={errors.code ? "2fa-error" : undefined}
                aria-invalid={!!errors.code}
              />
              <InputError id="2fa-error" message={errors.code} className="text-xs" />
            </div>
          ) : (
            <div className="space-y-2">
              <Label htmlFor="code">Authentication code</Label>
              <div className="flex justify-center lg:justify-start">
                <InputOTP
                  id="code"
                  maxLength={6}
                  value={data.code}
                  onChange={(value) => setData("code", value)}
                  autoFocus
                >
                  <InputOTPGroup>
                    <InputOTPSlot index={0} />
                    <InputOTPSlot index={1} />
                    <InputOTPSlot index={2} />
                    <InputOTPSlot index={3} />
                    <InputOTPSlot index={4} />
                    <InputOTPSlot index={5} />
                  </InputOTPGroup>
                </InputOTP>
              </div>
              <InputError id="2fa-error" message={errors.code} className="text-xs" />
            </div>
          )}

          <LoadingButton
            type="submit"
            className="w-full"
            size="lg"
            loading={processing}
            loadingText="Verifying..."
          >
            Verify
          </LoadingButton>
        </form>

        <div className="text-center text-sm">
          <Button
            type="button"
            variant="link"
            className="text-muted-foreground hover:text-foreground"
            onClick={() => {
              setUseRecovery(!useRecovery);
              setData("code", "");
              setData("recovery_code", "");
            }}
          >
            {useRecovery
              ? "Use authentication code instead"
              : "Use a recovery code instead"}
          </Button>
        </div>

        <p className="text-center text-sm text-muted-foreground">
          Having trouble?{" "}
          <Link
            href={route("login")}
            className="font-medium text-primary hover:text-primary/80 transition-colors"
          >
            Return to login
          </Link>
        </p>
      </div>
    </AuthLayout>
  );
}
