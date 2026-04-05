// Generic JSON-LD structured-data injector.
// Mirrors the FaqJsonLd/BreadcrumbJsonLd component pattern for arbitrary schema types.
export function JsonLd({ data }: { data: object }) {
  const schema = JSON.stringify(data).replace(/<\/script>/gi, '<\\/script>');
  return (
    // Audit SD009: sanitizeHtml() / DOMPurify.sanitize() are intentionally omitted for JSON-LD.
    // DOMPurify corrupts structured data: encodes & to &amp;, strips @type values it
    // misidentifies as HTML, and rewrites URLs. The </script> replace above is the only
    // protection required in a <script> element. Use sanitizeHtml() for user HTML instead.
    <script
      type="application/ld+json"
      dangerouslySetInnerHTML={{ __html: schema }}
    />
  );
}
