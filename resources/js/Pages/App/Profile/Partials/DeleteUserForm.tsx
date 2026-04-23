import { AlertTriangle, Loader2, Trash2 } from "lucide-react";

import { useRef, useState } from "react";

import { useForm } from "@inertiajs/react";

import { useAnalytics } from "@/hooks/useAnalytics";
import { AnalyticsEvents } from "@/lib/events";

import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from "@/Components/ui/alert-dialog";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";

interface DeleteUserFormProps {
  className?: string;
}

const CHURN_REASONS = [
  { value: "too_expensive", label: "Too expensive" },
  { value: "missing_feature", label: "Missing a feature I need" },
  { value: "found_alternative", label: "Found a better alternative" },
  { value: "just_testing", label: "Just testing / evaluating" },
  { value: "other", label: "Other" },
] as const;

type ChurnReason = (typeof CHURN_REASONS)[number]["value"];

export default function DeleteUserForm({ className = "" }: DeleteUserFormProps) {
  const [open, setOpen] = useState(false);
  const [reason, setReason] = useState<ChurnReason | "">("");
  const passwordInput = useRef<HTMLInputElement>(null);
  const { track } = useAnalytics();

  const {
    data,
    setData,
    delete: destroy,
    processing,
    reset,
    errors,
    clearErrors,
  } = useForm({
    password: "",
    reason: "" as ChurnReason | "",
  });

  const handleDelete = (e: React.FormEvent) => {
    e.preventDefault();

    destroy(route("profile.destroy"), {
      preserveScroll: true,
      onSuccess: () => {
        track(AnalyticsEvents.ACCOUNT_DELETED, { reason: reason || undefined });
        setOpen(false);
      },
      onError: () => {
        passwordInput.current?.focus();
      },
      onFinish: () => {
        reset();
        setReason("");
      },
    });
  };

  const handleReasonChange = (value: ChurnReason) => {
    setReason(value);
    setData("reason", value);
  };

  const handleOpenChange = (newOpen: boolean) => {
    setOpen(newOpen);
    if (!newOpen) {
      clearErrors();
      reset();
      setReason("");
    }
  };

  return (
    <section className={className}>
      <AlertDialog open={open} onOpenChange={handleOpenChange}>
        <AlertDialogTrigger asChild>
          <Button
            variant="outline"
            className="border-destructive text-destructive hover:bg-destructive hover:text-destructive-foreground"
          >
            <Trash2 className="mr-2 h-4 w-4" />
            Delete Account
          </Button>
        </AlertDialogTrigger>
        <AlertDialogContent>
          <form onSubmit={handleDelete}>
            <AlertDialogHeader>
              <AlertDialogTitle className="flex items-center gap-2 text-destructive">
                <AlertTriangle className="h-5 w-5" />
                Are you sure you want to delete your account?
              </AlertDialogTitle>
              <AlertDialogDescription>
                This action cannot be undone. This will permanently delete your
                account and remove all your data from our servers, including:
              </AlertDialogDescription>
            </AlertDialogHeader>

            <ul className="my-4 ml-4 list-disc text-sm text-muted-foreground space-y-1">
              <li>All your projects and packages</li>
              <li>All scan history and results</li>
              <li>Your subscription and billing information</li>
              <li>API tokens and integrations</li>
            </ul>

            {/* Churn reason */}
            <div className="space-y-2 mb-4">
              <Label className="text-sm font-medium">
                Why are you leaving? <span className="text-muted-foreground">(Optional)</span>
              </Label>
              <div className="grid gap-1.5">
                {CHURN_REASONS.map((opt) => (
                  <label
                    key={opt.value}
                    className="flex items-center gap-2.5 cursor-pointer rounded-md border px-3 py-2 text-sm hover:bg-muted/50 has-[:checked]:border-destructive/50 has-[:checked]:bg-destructive/5 transition-colors"
                  >
                    <input
                      type="radio"
                      name="churn_reason"
                      value={opt.value}
                      checked={reason === opt.value}
                      onChange={() => handleReasonChange(opt.value)}
                      className="accent-destructive"
                    />
                    {opt.label}
                  </label>
                ))}
              </div>
            </div>

            <div className="space-y-2">
              <Label htmlFor="password" className="text-sm font-medium">
                Enter your password to confirm
              </Label>
              <Input
                id="password"
                type="password"
                ref={passwordInput}
                value={data.password}
                onChange={(e) => setData("password", e.target.value)}
                placeholder="Your password"
                autoFocus
              />
              {errors.password && (
                <p className="text-sm text-destructive">{errors.password}</p>
              )}
            </div>

            <AlertDialogFooter className="mt-6">
              <AlertDialogCancel type="button" disabled={processing}>
                Cancel
              </AlertDialogCancel>
              <AlertDialogAction
                type="submit"
                disabled={processing || !data.password}
                className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
              >
                {processing ? (
                  <>
                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                    Deleting...
                  </>
                ) : (
                  "Delete Account"
                )}
              </AlertDialogAction>
            </AlertDialogFooter>
          </form>
        </AlertDialogContent>
      </AlertDialog>
    </section>
  );
}
