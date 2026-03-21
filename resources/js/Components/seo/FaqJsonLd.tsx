interface FaqItem {
  question: string;
  answer: string;
}

interface FaqJsonLdProps {
  questions: FaqItem[];
}

export function FaqJsonLd({ questions }: FaqJsonLdProps) {
  if (questions.length === 0) return null;

  const schema = JSON.stringify({
    '@context': 'https://schema.org',
    '@type': 'FAQPage',
    mainEntity: questions.map((item) => ({
      '@type': 'Question',
      name: item.question,
      acceptedAnswer: {
        '@type': 'Answer',
        text: item.answer,
      },
    })),
  });

  // Prevent </script> injection; DOMPurify omitted — it corrupts JSON with special chars
  return (
    <script
      type="application/ld+json"
      dangerouslySetInnerHTML={{ __html: schema.replace(/<\/script>/gi, '<\\/script>') }}
    />
  );
}
