import { router } from '@inertiajs/react';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/Components/ui/dialog';
import { Button } from '@/Components/ui/button';
import { LoadingButton } from '@/Components/ui/loading-button';
import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';
import type { PlanKey } from '@/lib/events';
import { useState } from 'react';

interface SwapConfirmDialogProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  targetPlanKey: string;
  targetTierName: string;
  priceId: string | null | undefined;
  priceLabel: string;
  currentPlanName?: string;
  couponCode?: string;
}

export function SwapConfirmDialog({
  open,
  onOpenChange,
  targetPlanKey,
  targetTierName,
  priceId,
  priceLabel,
  currentPlanName,
  couponCode,
}: SwapConfirmDialogProps) {
  const [loading, setLoading] = useState(false);
  const { track } = useAnalytics();

  const handleConfirm = () => {
    if (!priceId) return;

    setLoading(true);
    track(AnalyticsEvents.BILLING_SWAP_CONFIRMED, {
      from_plan: currentPlanName,
      to_plan: targetPlanKey as PlanKey,
      price_id: priceId,
    });

    router.post(
      route('billing.swap'),
      { price_id: priceId, ...(couponCode?.trim() ? { coupon: couponCode.trim() } : {}) },
      {
        onFinish: () => {
          setLoading(false);
          onOpenChange(false);
        },
      }
    );
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[420px]">
        <DialogHeader>
          <DialogTitle>Switch to {targetTierName}?</DialogTitle>
          <DialogDescription>
            {currentPlanName && (
              <>
                You are switching from <strong>{currentPlanName}</strong> to{' '}
                <strong>{targetTierName}</strong>.
              </>
            )}
          </DialogDescription>
        </DialogHeader>

        <div className="space-y-3 py-2 text-sm text-muted-foreground">
          <p>
            New plan: <strong className="text-foreground">{targetTierName}</strong> at{' '}
            <strong className="text-foreground">{priceLabel}</strong>
          </p>
          <p className="text-xs bg-muted rounded-md px-3 py-2">
            Changes take effect immediately. Your billing will be prorated — you'll only be charged
            for the days remaining in the current billing period.
          </p>
        </div>

        <DialogFooter>
          <Button
            variant="outline"
            onClick={() => onOpenChange(false)}
            disabled={loading}
          >
            Cancel
          </Button>
          <LoadingButton
            onClick={handleConfirm}
            loading={loading}
            loadingText="Switching..."
            disabled={!priceId}
          >
            Confirm Switch
          </LoadingButton>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
