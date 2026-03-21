import { ArrowRight } from 'lucide-react';

import { Link } from '@inertiajs/react';

interface RelatedItem {
  title: string;
  href: string;
  description?: string;
}

interface RelatedContentProps {
  heading?: string;
  items: RelatedItem[];
}

export function RelatedContent({
  heading = 'Related reading',
  items,
}: RelatedContentProps) {
  if (items.length === 0) return null;

  return (
    <section className="mt-12 border-t pt-8">
      <h2 className="mb-4 text-lg font-semibold">{heading}</h2>
      <ul className="space-y-3">
        {items.map((item) => (
          <li key={item.href}>
            <Link
              href={item.href}
              className="group flex items-start gap-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
              <ArrowRight className="mt-0.5 h-4 w-4 shrink-0 text-primary opacity-0 transition-opacity group-hover:opacity-100" />
              <span>
                <span className="font-medium text-foreground">{item.title}</span>
                {item.description && (
                  <span className="ml-1 text-muted-foreground">
                    — {item.description}
                  </span>
                )}
              </span>
            </Link>
          </li>
        ))}
      </ul>
    </section>
  );
}
