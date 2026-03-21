import { X } from 'lucide-react';

import { useEffect, useState } from 'react';

import { router } from '@inertiajs/react';

import { Button } from '@/Components/ui/button';
import { cn } from '@/lib/utils';

export function NpsBanner() {
  const [visible, setVisible] = useState(false);
  const [score, setScore] = useState<number | null>(null);
  const [comment, setComment] = useState('');
  const [submitted, setSubmitted] = useState(false);

  useEffect(() => {
    // Check eligibility once on mount
    fetch('/nps/eligible', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then((r) => r.json())
      .then((data) => {
        if (data.eligible) setVisible(true);
      })
      .catch(() => {/* silently ignore */});
  }, []);

  const handleSubmit = () => {
    if (score === null) return;

    router.post(
      '/nps',
      { score, comment: comment || undefined },
      {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          setSubmitted(true);
          setTimeout(() => setVisible(false), 2000);
        },
      }
    );
  };

  if (!visible) return null;

  return (
    <div
      role="region"
      aria-label="NPS Survey"
      aria-live="polite"
      className="fixed bottom-16 left-0 right-0 z-40 flex justify-center px-4 pointer-events-none"
    >
      <div className="pointer-events-auto w-full max-w-lg rounded-xl border bg-background shadow-lg p-4 space-y-3">
        <div className="flex items-start justify-between gap-2">
          <p className="text-sm font-medium">
            {submitted
              ? 'Thanks for your feedback! 🎉'
              : 'How likely are you to recommend us to a friend?'}
          </p>
          <button
            onClick={() => setVisible(false)}
            aria-label="Dismiss survey"
            className="text-muted-foreground hover:text-foreground transition-colors shrink-0"
          >
            <X className="h-4 w-4" />
          </button>
        </div>

        {!submitted && (
          <>
            <div className="flex gap-1 flex-wrap">
              {Array.from({ length: 11 }, (_, i) => (
                <button
                  key={i}
                  onClick={() => setScore(i)}
                  aria-label={`Score ${i}`}
                  aria-pressed={score === i}
                  className={cn(
                    'h-8 w-8 rounded-md text-xs font-medium border transition-colors',
                    score === i
                      ? 'bg-primary text-primary-foreground border-primary'
                      : 'border-border text-muted-foreground hover:border-primary hover:text-foreground'
                  )}
                >
                  {i}
                </button>
              ))}
            </div>
            <div className="flex justify-between text-xs text-muted-foreground px-0.5">
              <span>Not likely</span>
              <span>Very likely</span>
            </div>

            {score !== null && (
              <div className="space-y-2">
                <textarea
                  value={comment}
                  onChange={(e) => setComment(e.target.value)}
                  placeholder="Any additional comments? (optional)"
                  maxLength={500}
                  rows={2}
                  className="w-full rounded-md border bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring resize-none"
                />
                <Button size="sm" onClick={handleSubmit} disabled={score === null}>
                  Submit
                </Button>
              </div>
            )}
          </>
        )}
      </div>
    </div>
  );
}
