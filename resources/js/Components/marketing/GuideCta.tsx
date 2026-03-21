import { ArrowRight } from 'lucide-react';

import { Link } from '@inertiajs/react';

interface GuideCtaProps {
  headline: string;
  description: string;
  href?: string;
  linkText?: string;
}

/**
 * Compact inline CTA for guide articles.
 * Placed at natural conversion moments (e.g., after solving a problem the starter addresses).
 * Does not break prose layout on mobile.
 */
export function GuideCta({
  headline,
  description,
  href = '/pricing',
  linkText = 'See pricing',
}: GuideCtaProps) {
  return (
    <div className="not-prose my-8 rounded-xl border border-primary/20 bg-primary/5 px-6 py-5">
      <p className="text-sm font-semibold text-foreground">{headline}</p>
      <p className="mt-1 text-sm text-muted-foreground">{description}</p>
      <Link
        href={href}
        className="mt-3 inline-flex items-center text-sm font-medium text-primary hover:underline"
      >
        {linkText}
        <ArrowRight className="ml-1 h-3 w-3" />
      </Link>
    </div>
  );
}
