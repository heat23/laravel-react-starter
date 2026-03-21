import DOMPurify from 'dompurify';

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

  return (
    <script
      type="application/ld+json"
      dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(schema) }}
    />
  );
}
