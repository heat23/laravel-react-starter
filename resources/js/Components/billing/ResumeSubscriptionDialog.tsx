import axios from "axios";
import { toast } from "sonner";

import { useState } from "react";

import {
  AlertDialog,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/Components/ui/alert-dialog";
import { LoadingButton } from "@/Components/ui/loading-button";

interface ResumeSubscriptionDialogProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  onSuccess?: () => void;
}

export function ResumeSubscriptionDialog({
  open,
  onOpenChange,
  onSuccess,
}: ResumeSubscriptionDialogProps) {
  const [processing, setProcessing] = useState(false);

  const handleResume = async () => {
    setProcessing(true);
    try {
      const response = await axios.post(route("billing.resume"));
      const message = response.data?.message || "Subscription resumed successfully!";
      toast.success("Subscription Resumed", {
        description: message,
      });
      onOpenChange(false);
      onSuccess?.();
    } catch (error: unknown) {
      const response = axios.isAxiosError(error) ? error.response : null;
      const message =
        response?.data?.message ||
        Object.values(response?.data?.errors ?? {})[0]?.[0] ||
        "Failed to resume subscription";
      toast.error("Resume Failed", {
        description: message,
      });
    } finally {
      setProcessing(false);
    }
  };

  return (
    <AlertDialog open={open} onOpenChange={onOpenChange}>
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Resume Subscription?</AlertDialogTitle>
          <AlertDialogDescription>
            Your subscription will be reactivated and continue with the current
            billing cycle. You won't lose access at the end of your grace
            period.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel disabled={processing}>Cancel</AlertDialogCancel>
          <LoadingButton onClick={handleResume} loading={processing} loadingText="Resuming...">
            Resume Subscription
          </LoadingButton>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  );
}
