import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/Components/ui/accordion';

interface FaqItem {
  question: string;
  answer: string;
}

interface FaqAccordionProps {
  faqs: FaqItem[];
}

export function FaqAccordion({ faqs }: FaqAccordionProps) {
  if (!faqs || faqs.length === 0) return null;

  return (
    <Accordion type="single" collapsible className="w-full space-y-3">
      {faqs.map((faq, index) => (
        <AccordionItem
          key={`faq-${index}`}
          value={`faq-${index}`}
          className="rounded-2xl border border-border/70 bg-card px-6 shadow-sm [&:not(:last-child)]:border-b-border/70"
        >
          <AccordionTrigger className="text-left text-base font-semibold hover:no-underline hover:text-primary [&[data-state=open]]:text-primary">
            {faq.question}
          </AccordionTrigger>
          <AccordionContent className="text-sm text-muted-foreground">
            {faq.answer}
          </AccordionContent>
        </AccordionItem>
      ))}
    </Accordion>
  );
}
