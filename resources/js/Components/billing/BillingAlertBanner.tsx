import { AlertCircle, X } from 'lucide-react';
import { useState } from 'react';
import { Link } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';

interface BillingAlertBannerProps {
  status: 'past_due' | 'incomplete';
}

export function BillingAlertBanner({ status }: BillingAlertBannerProps) {
  const [dismissed, setDismissed] = useState(false);

  if (dismissed) return null;

  const message =
    status === 'past_due'
      ? 'Your payment failed — update your payment method to keep access.'
      : 'Your payment requires confirmation — complete the verification to activate your subscription.';

  return (
    <div
      role="alert"
      aria-live="polite"
      className="sticky top-16 z-40 w-full bg-destructive/10 border-b border-destructive/30 px-4 py-2"
    >
      <div className="container flex items-center justify-between gap-4">
        <div className="flex items-center gap-2 text-sm text-destructive">
          <AlertCircle className="h-4 w-4 shrink-0" aria-hidden="true" />
          <span>{message}</span>
          <Button
            asChild
            size="sm"
            variant="destructive"
            className="ml-2 h-7 px-3"
          >
            <Link href={route('billing.index')}>Update Payment Method</Link>
          </Button>
        </div>
        <Button
          variant="ghost"
          size="icon"
          className="h-7 w-7 text-destructive hover:bg-destructive/10"
          onClick={() => setDismissed(true)}
          aria-label="Dismiss billing alert"
        >
          <X className="h-4 w-4" />
        </Button>
      </div>
    </div>
  );
}
