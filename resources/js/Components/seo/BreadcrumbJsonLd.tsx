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

  // Prevent </script> injection; DOMPurify is omitted — it corrupts valid JSON (URLs with < >)
  const safeSchema = schema.replace(/<\/script>/gi, '<\\/script>');

  return (
    <script
      type="application/ld+json"
      dangerouslySetInnerHTML={{ __html: safeSchema }}
    />
  );
}
