import axios from "axios";
import { AlertCircle, Info } from "lucide-react";
import { toast } from "sonner";

import { useEffect, useState } from "react";

import { Alert, AlertDescription, AlertTitle } from "@/Components/ui/alert";
import { Button } from "@/Components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/Components/ui/dialog";
import { Label } from "@/Components/ui/label";
import { LoadingButton } from "@/Components/ui/loading-button";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/Components/ui/select";
import { Textarea } from "@/Components/ui/textarea";

interface CancelSubscriptionDialogProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  onSuccess?: () => void;
}

export function CancelSubscriptionDialog({
  open,
  onOpenChange,
  onSuccess,
}: CancelSubscriptionDialogProps) {
  const [reason, setReason] = useState<string>("");
  const [feedback, setFeedback] = useState<string>("");
  const [processing, setProcessing] = useState(false);

  const showRetentionOffer = reason === "too_expensive";
  const showFeedbackField = ["missing_features", "other", "switching_tools"].includes(reason);

  const handleSubmit = async () => {
    setProcessing(true);
    try {
      const response = await axios.post(route("billing.cancel"), {
        reason: reason || undefined,
        feedback: feedback || undefined,
      });
      const message = response.data?.message || "Subscription canceled successfully";
      toast.success("Subscription Canceled", {
        description: message,
      });
      onOpenChange(false);
      setReason("");
      setFeedback("");
      onSuccess?.();
    } catch (error: unknown) {
      const response = axios.isAxiosError(error) ? error.response : null;
      const message =
        response?.data?.message ||
        Object.values(response?.data?.errors ?? {})[0]?.[0] ||
        "Failed to cancel subscription";
      toast.error("Cancellation Failed", {
        description: message,
      });
    } finally {
      setProcessing(false);
    }
  };

  // Reset form state when dialog closes
  useEffect(() => {
    if (!open) {
      setReason("");
      setFeedback("");
      setProcessing(false);
    }
  }, [open]);

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[500px]">
        <DialogHeader>
          <DialogTitle>Cancel Subscription</DialogTitle>
          <DialogDescription>
            We're sorry to see you go. Your subscription will remain active
            until the end of your current billing period.
          </DialogDescription>
        </DialogHeader>

        <div className="space-y-4 py-4">
          <div className="flex items-start gap-3 p-4 bg-accent/50 border border-accent rounded-lg">
            <AlertCircle className="h-5 w-5 text-muted-foreground mt-0.5 flex-shrink-0" />
            <div className="text-sm text-muted-foreground">
              <p className="font-medium mb-1">Important</p>
              <p>
                You'll continue to have access to all features until the end of
                your billing period. You can resume your subscription at any
                time.
              </p>
            </div>
          </div>

          <div className="space-y-2">
            <Label htmlFor="reason">
              Why are you canceling? <span className="text-muted-foreground">(Optional)</span>
            </Label>
            <Select value={reason} onValueChange={setReason}>
              <SelectTrigger id="reason">
                <SelectValue placeholder="Select a reason" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="too_expensive">Too expensive</SelectItem>
                <SelectItem value="switching_tools">
                  Switching to another tool
                </SelectItem>
                <SelectItem value="no_longer_needed">
                  No longer needed
                </SelectItem>
                <SelectItem value="missing_features">
                  Missing features
                </SelectItem>
                <SelectItem value="other">Other</SelectItem>
              </SelectContent>
            </Select>
          </div>

          {showRetentionOffer && (
            <Alert className="border-primary/20 bg-primary/5 animate-in fade-in-50 duration-300">
              <Info className="h-4 w-4 text-primary" />
              <AlertTitle>Before you go...</AlertTitle>
              <AlertDescription>
                We'd love to keep you! Contact us and we may be able to offer a discount.
              </AlertDescription>
            </Alert>
          )}

          {showFeedbackField && (
            <div className="space-y-2 animate-in fade-in-50 duration-300">
              <Label htmlFor="feedback">
                Additional feedback <span className="text-muted-foreground">(Optional)</span>
              </Label>
              <Textarea
                id="feedback"
                placeholder="Help us improve by sharing your feedback..."
                value={feedback}
                onChange={(e) => setFeedback(e.target.value)}
                rows={4}
                maxLength={500}
              />
              <p className="text-xs text-muted-foreground">
                {feedback.length}/500 characters
              </p>
            </div>
          )}
        </div>

        <DialogFooter>
          <Button
            variant="outline"
            onClick={() => onOpenChange(false)}
            disabled={processing}
          >
            Keep Subscription
          </Button>
          <LoadingButton
            variant="destructive"
            onClick={handleSubmit}
            loading={processing}
            loadingText="Canceling..."
          >
            Confirm Cancellation
          </LoadingButton>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
