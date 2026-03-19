import DOMPurify from 'dompurify';

import type { BreadcrumbItem } from '@/types/index';

interface BreadcrumbJsonLdProps {
  breadcrumbs: BreadcrumbItem[];
}

export function BreadcrumbJsonLd({ breadcrumbs }: BreadcrumbJsonLdProps) {
  const schema = JSON.stringify({
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: breadcrumbs.map((item, index) => ({
      '@type': 'ListItem',
      position: index + 1,
      name: item.name,
      item: item.url,
    })),
  });

  return (
    <script
      type="application/ld+json"
      dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(schema) }}
    />
  );
}
