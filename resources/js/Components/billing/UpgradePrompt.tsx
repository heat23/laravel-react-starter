import { Lock } from 'lucide-react';

import { Link } from '@inertiajs/react';

import { Button } from '@/Components/ui/button';

interface UpgradePromptProps {
  featureName: string;
  planRequired: string;
  pricingUrl?: string;
}

export function UpgradePrompt({
  featureName,
  planRequired,
  pricingUrl = '/pricing',
}: UpgradePromptProps) {
  return (
    <div className="space-y-3 rounded-lg border border-primary/20 bg-primary/5 px-4 py-4">
      <div className="flex items-start gap-3">
        <Lock className="mt-0.5 h-4 w-4 shrink-0 text-primary" />
        <div className="space-y-1">
          <p className="text-sm font-semibold text-foreground">
            You've outgrown the free plan — in a good way.
          </p>
          <p className="text-sm text-muted-foreground">
            Upgrade to {planRequired} to unlock {featureName}. Takes 30 seconds.
          </p>
        </div>
      </div>
      <Button size="sm" asChild>
        <Link href={`${pricingUrl}?ref=upgrade_prompt`}>
          Upgrade now →
        </Link>
      </Button>
    </div>
  );
}
