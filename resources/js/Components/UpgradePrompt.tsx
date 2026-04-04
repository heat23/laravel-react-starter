import { Sparkles, X } from 'lucide-react';

import { useState } from 'react';

import { Link } from '@inertiajs/react';

import { Button } from '@/Components/ui/button';
import type { UpgradePromptData } from '@/types';

interface UpgradePromptProps {
  prompt: UpgradePromptData;
}

const LIMIT_LABELS: Record<string, string> = {
  api_tokens: 'API token',
  webhook_endpoints: 'webhook endpoint',
  projects: 'project',
  items_per_project: 'item',
};

export function UpgradePrompt({ prompt }: UpgradePromptProps) {
  const [dismissed, setDismissed] = useState(false);

  if (dismissed) return null;

  const limitLabel =
    LIMIT_LABELS[prompt.limit] ?? prompt.limit.replace(/_/g, ' ');

  return (
    <div
      role="alert"
      aria-live="polite"
      className="w-full bg-primary/10 border-b border-primary/30 px-4 py-2"
    >
      <div className="container flex items-center justify-between gap-4">
        <div className="flex items-center gap-2 text-sm text-primary">
          <Sparkles className="h-4 w-4 shrink-0" aria-hidden="true" />
          <span>You've reached your {limitLabel} limit on your current plan.</span>
          <Button asChild size="sm" className="ml-2 h-7 px-3">
            <Link href={prompt.cta_url}>Upgrade to {prompt.plan}</Link>
          </Button>
        </div>
        <Button
          variant="ghost"
          size="icon"
          className="h-7 w-7 text-primary hover:bg-primary/10"
          onClick={() => setDismissed(true)}
          aria-label="Dismiss upgrade prompt"
        >
          <X className="h-4 w-4" />
        </Button>
      </div>
    </div>
  );
}
