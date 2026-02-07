import { Copy, Eye, EyeOff, RefreshCw, Shield, ShieldCheck, ShieldOff } from "lucide-react";
import { toast } from "sonner";

import { useState, FormEventHandler } from "react";

import { Head, useForm, usePage, router } from "@inertiajs/react";

import InputError from "@/Components/InputError";
import PageHeader from "@/Components/layout/PageHeader";
import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import { ConfirmDialog } from "@/Components/ui/confirm-dialog";
import { Input } from "@/Components/ui/input";
import {
  InputOTP,
  InputOTPGroup,
  InputOTPSlot,
} from "@/Components/ui/input-otp";
import { Label } from "@/Components/ui/label";
import { LoadingButton } from "@/Components/ui/loading-button";
import DashboardLayout from "@/Layouts/DashboardLayout";
import type { PageProps } from "@/types";

interface SecurityProps {
  enabled: boolean;
  qr_code: string | null;
  secret: string | null;
  recovery_codes: string[] | null;
}

export default function Security({ enabled, qr_code, secret }: SecurityProps) {
  const { flash } = usePage<PageProps>().props;

  return (
    <DashboardLayout>
      <Head title="Security" />
      <PageHeader
        title="Security"
        subtitle="Manage two-factor authentication for your account"
      />
      <div className="container py-8">
        <div className="max-w-3xl mx-auto space-y-6">
          {flash.success && (
            <div className="rounded-md border border-success/20 bg-success/5 px-4 py-3 text-sm text-success">
              {flash.success}
            </div>
          )}

          {enabled ? (
            <EnabledState />
          ) : qr_code && secret ? (
            <SetupState qrCode={qr_code} secret={secret} />
          ) : (
            <NotEnabledState />
          )}
        </div>
      </div>
    </DashboardLayout>
  );
}

function NotEnabledState() {
  const { post, processing } = useForm({});

  const handleEnable: FormEventHandler = (e) => {
    e.preventDefault();
    post(route("two-factor.enable"));
  };

  return (
    <Card>
      <CardHeader>
        <div className="flex items-center gap-3">
          <div className="rounded-full bg-muted p-2">
            <Shield className="h-5 w-5 text-muted-foreground" />
          </div>
          <div>
            <CardTitle>Two-Factor Authentication</CardTitle>
            <CardDescription>
              Add an extra layer of security to your account using a TOTP authenticator app.
            </CardDescription>
          </div>
        </div>
      </CardHeader>
      <CardContent>
        <div className="space-y-4">
          <div className="flex items-center gap-2">
            <Badge variant="secondary">Not enabled</Badge>
          </div>
          <p className="text-sm text-muted-foreground">
            Two-factor authentication adds an additional layer of security to your account
            by requiring a code from your authenticator app when you sign in.
          </p>
          <form onSubmit={handleEnable}>
            <LoadingButton
              type="submit"
              loading={processing}
              loadingText="Enabling..."
            >
              Enable two-factor authentication
            </LoadingButton>
          </form>
        </div>
      </CardContent>
    </Card>
  );
}

function SetupState({ qrCode, secret }: { qrCode: string; secret: string }) {
  const { data, setData, post, processing, errors } = useForm({
    code: "",
  });

  const handleConfirm: FormEventHandler = (e) => {
    e.preventDefault();
    post(route("two-factor.confirm"));
  };

  return (
    <Card>
      <CardHeader>
        <div className="flex items-center gap-3">
          <div className="rounded-full bg-primary/10 p-2">
            <Shield className="h-5 w-5 text-primary" />
          </div>
          <div>
            <CardTitle>Set Up Two-Factor Authentication</CardTitle>
            <CardDescription>
              Scan the QR code below with your authenticator app, then enter the verification code.
            </CardDescription>
          </div>
        </div>
      </CardHeader>
      <CardContent>
        <div className="space-y-6">
          <div className="flex justify-center rounded-lg border bg-white p-4">
            <div dangerouslySetInnerHTML={{ __html: qrCode }} />
          </div>

          <div className="space-y-2">
            <p className="text-sm font-medium">Manual entry key</p>
            <div className="flex items-center gap-2">
              <code className="flex-1 rounded-md bg-muted px-3 py-2 text-sm font-mono break-all">
                {secret}
              </code>
              <Button
                type="button"
                variant="outline"
                size="icon"
                onClick={() => {
                  navigator.clipboard.writeText(secret);
                  toast.success("Secret key copied to clipboard");
                }}
                aria-label="Copy secret key"
              >
                <Copy className="h-4 w-4" />
              </Button>
            </div>
          </div>

          <form onSubmit={handleConfirm} className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="confirm-code">Verification code</Label>
              <div className="flex justify-start">
                <InputOTP
                  id="confirm-code"
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
              <InputError message={errors.code} className="text-xs" />
            </div>

            <LoadingButton
              type="submit"
              loading={processing}
              loadingText="Verifying..."
            >
              Confirm and enable
            </LoadingButton>
          </form>
        </div>
      </CardContent>
    </Card>
  );
}

function EnabledState() {
  const [showRecoveryCodes, setShowRecoveryCodes] = useState(false);
  const [recoveryCodes, setRecoveryCodes] = useState<string[] | null>(null);
  const [loadingCodes, setLoadingCodes] = useState(false);
  const [disableDialogOpen, setDisableDialogOpen] = useState(false);

  const disableForm = useForm({ password: "" });

  const fetchRecoveryCodes = async () => {
    setLoadingCodes(true);
    try {
      const response = await fetch(route("two-factor.recovery-codes"), {
        headers: { Accept: "application/json" },
      });
      if (!response.ok) throw new Error();
      const data = await response.json();
      setRecoveryCodes(data.recovery_codes);
      setShowRecoveryCodes(true);
    } catch {
      toast.error("Failed to load recovery codes.");
    } finally {
      setLoadingCodes(false);
    }
  };

  const handleRegenerate = () => {
    router.post(route("two-factor.recovery-codes.regenerate"), {}, {
      onSuccess: () => {
        setRecoveryCodes(null);
        setShowRecoveryCodes(false);
        toast.success("Recovery codes have been regenerated.");
      },
    });
  };

  const handleDisable = () => {
    disableForm.delete(route("two-factor.disable"), {
      onSuccess: () => setDisableDialogOpen(false),
      onError: () => {},
    });
  };

  return (
    <>
      <Card>
        <CardHeader>
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className="rounded-full bg-success/10 p-2">
                <ShieldCheck className="h-5 w-5 text-success" />
              </div>
              <div>
                <CardTitle>Two-Factor Authentication</CardTitle>
                <CardDescription>
                  Your account is protected with two-factor authentication.
                </CardDescription>
              </div>
            </div>
            <Badge variant="default" className="bg-success text-success-foreground">
              Enabled
            </Badge>
          </div>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            <p className="text-sm text-muted-foreground">
              You will be asked for a verification code from your authenticator app each time you sign in.
            </p>

            <div className="flex flex-wrap gap-2">
              <Button
                variant="outline"
                onClick={showRecoveryCodes ? () => setShowRecoveryCodes(false) : fetchRecoveryCodes}
                disabled={loadingCodes}
              >
                {showRecoveryCodes ? (
                  <><EyeOff className="mr-2 h-4 w-4" /> Hide recovery codes</>
                ) : (
                  <><Eye className="mr-2 h-4 w-4" /> View recovery codes</>
                )}
              </Button>
              <Button variant="outline" onClick={handleRegenerate}>
                <RefreshCw className="mr-2 h-4 w-4" />
                Regenerate codes
              </Button>
              <Button
                variant="destructive"
                onClick={() => setDisableDialogOpen(true)}
              >
                <ShieldOff className="mr-2 h-4 w-4" />
                Disable
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      {showRecoveryCodes && recoveryCodes && (
        <Card>
          <CardHeader>
            <CardTitle className="text-base">Recovery Codes</CardTitle>
            <CardDescription>
              Store these codes in a safe place. Each code can only be used once.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-2 gap-2">
              {recoveryCodes.map((code) => (
                <code key={code} className="rounded-md bg-muted px-3 py-2 text-sm font-mono text-center">
                  {code}
                </code>
              ))}
            </div>
            <div className="mt-4">
              <Button
                variant="outline"
                size="sm"
                onClick={() => {
                  navigator.clipboard.writeText(recoveryCodes.join("\n"));
                  toast.success("Recovery codes copied to clipboard");
                }}
              >
                <Copy className="mr-2 h-4 w-4" />
                Copy all codes
              </Button>
            </div>
          </CardContent>
        </Card>
      )}

      <ConfirmDialog
        open={disableDialogOpen}
        onOpenChange={setDisableDialogOpen}
        title="Disable Two-Factor Authentication"
        description={
          <div className="space-y-3">
            <p>This will remove the extra security from your account. Enter your password to confirm.</p>
            <div className="space-y-2">
              <Label htmlFor="disable-password">Password</Label>
              <Input
                id="disable-password"
                type="password"
                value={disableForm.data.password}
                onChange={(e) => disableForm.setData("password", e.target.value)}
                autoComplete="current-password"
              />
              <InputError message={disableForm.errors.password} className="text-xs" />
            </div>
          </div>
        }
        confirmLabel="Disable 2FA"
        variant="destructive"
        onConfirm={handleDisable}
      />
    </>
  );
}
