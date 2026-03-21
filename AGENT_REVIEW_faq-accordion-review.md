# Code Review: FAQ Accordion Implementation

**Files Reviewed:**
1. `resources/js/Components/marketing/FaqAccordion.tsx` (new)
2. `resources/js/Pages/Welcome.tsx` (modified)
3. `app/Http/Controllers/WelcomeController.php` (modified)

**Review Date:** 2026-03-20

---

## Summary

The FAQ accordion implementation converts static FAQ cards into a fully interactive, accessible accordion pattern using Radix UI primitives. The refactoring is **sound** with proper accessibility patterns, TypeScript typing, and schema compliance. One **HIGH** finding and several **MEDIUM** items require attention before merge.

---

## CRITICAL Issues

None identified.

---

## HIGH Issues

### 1. Accordion Key Prop Antipattern - Risk of State Loss

**Location:** `FaqAccordion.tsx:17`

```typescript
{faqs.map((faq, index) => (
  <AccordionItem
    key={faq.question}  // ❌ ANTI-PATTERN
    value={`faq-${index}`}
```

**Problem:**
- Using `faq.question` as the key is a **key antipattern**. If the FAQ list is reordered, filtered, or duplicates exist, React will misidentify items.
- The component uses `index` for the `value` prop (correct for accordion state), but uses `question` for `key` (incorrect for identity).
- **Risk:** If FAQ content changes server-side and questions are reordered, expanded state persists on wrong items; if two FAQs have the same question text, only one renders.

**Fix:**
```typescript
key={`faq-${index}`}  // Use index since server order is stable
// OR if FAQs have stable IDs from the server, use those instead
```

**Severity:** HIGH — affects correct accordion state management and could cause UX confusion on data updates.

---

## MEDIUM Issues

### 1. Accessibility: Semantic Heading Hierarchy Inconsistency

**Location:** `Welcome.tsx:588 vs 589`

```typescript
<h2 id="faq-heading" className="mb-2 text-center text-3xl font-bold">
  Frequently asked questions
</h2>
<p className="mb-10 text-center text-muted-foreground">
  Common questions before buying.
</p>
```

**Problem:**
- The `<h2>` tag is preceded by "Tech Stack" section (also `<h2>` at line 568) without a parent `<h1>`.
- No `<h1>` exists on the page — only multiple `<h2>` siblings.
- **WCAG violation:** Heading hierarchy should start with `<h1>` and nest properly. Skipping from no `<h1>` to `<h2>` confuses screen readers about document structure.

**Context:** The `<h1>` (line 251) is buried in the hero section. Semantic structure expects `<h1>` somewhere prominent before any `<h2>` descendants.

**Severity:** MEDIUM — screen reader users may lose context; SEO impact (Google rewards proper heading hierarchy).

**Note:** This is a **pre-existing issue** not introduced by this change, but the FAQ section amplifies it.

---

### 2. Type Safety: FaqItem Duplicated Interface

**Location:** `Welcome.tsx:27-30 vs FaqAccordion.tsx:3-6`

```typescript
// Welcome.tsx
interface FaqItem {
  question: string;
  answer: string;
}

// FaqAccordion.tsx
interface FaqItem {
  question: string;
  answer: string;
}
```

**Problem:**
- The `FaqItem` interface is defined in both files, violating DRY principle.
- If a new property is added (e.g., `answer_html: boolean`), it must be updated in two places.
- No shared types file or export from the component.

**Fix:**
Export from `FaqAccordion.tsx`:
```typescript
// FaqAccordion.tsx
export interface FaqItem {
  question: string;
  answer: string;
}

// Welcome.tsx
import { FaqAccordion, type FaqItem } from '@/Components/marketing/FaqAccordion';
```

**Severity:** MEDIUM — maintainability debt, not a runtime bug.

---

### 3. Potential XSS via Unescaped HTML in FAQ Answers

**Location:** `WelcomeController.php:35-64 (FAQ answers)`

```php
'answer' => 'No. Most boilerplates give you scaffolding and leave you to figure out billing race conditions, feature flag resolution, and admin panel security. This starter kit includes 90+ tests, Redis-locked billing, and a same-stack React admin panel — all production-tested.',
```

**Problem:**
- FAQ answers are **plain text** in the current implementation (safe ✓).
- However, the controller passes them as-is to React without sanitization documentation.
- If a future developer adds rich text or HTML answers from a CMS, they'll be rendered directly in `FaqAccordion.tsx:25` without `DOMPurify.sanitize()`.
- The Welcome.tsx file **already uses** `DOMPurify.sanitize()` for the FAQPage JSON-LD schema (line 212), showing XSS awareness.

**Risk:** Latent vulnerability if FAQ source changes from hardcoded PHP strings to user-generated or CMS content.

**Recommendation:**
Add a guard comment in `FaqAccordion.tsx`:
```typescript
// ⚠️ FAQ answers are expected to be plain text only. If HTML is added,
// wrap answer in DOMPurify.sanitize() before rendering.
<AccordionContent className="text-sm text-muted-foreground">
  {faq.answer}
</AccordionContent>
```

**Severity:** MEDIUM — currently safe (plain text), but future-proofing needed.

---

### 4. Missing `aria-live` for Accordion State Changes

**Location:** `FaqAccordion.tsx` (component level)

**Problem:**
- The accordion uses Radix UI, which handles focus management correctly ✓.
- However, screen reader announcements of state changes (expanded/collapsed) depend on Radix's built-in ARIA attributes.
- Radix's accordion automatically sets `aria-expanded` and `role="button"` — no missing attributes ✓.
- **But:** There's no progress indicator for users on slow connections. When a question opens, there's no visual or ARIA indication of content loading state (though this is a minor UX issue, not an accessibility violation).

**Check:** Run accessibility audit to confirm Radix accordion meets WCAG 2.1 AA:
- `aria-expanded` on trigger ✓ (Radix)
- `aria-controls` linking trigger to content ✓ (Radix)
- Keyboard nav (Arrow keys, Home, End) ✓ (Radix)
- Focus trap not needed for accordion (correct) ✓

**Severity:** MEDIUM — Radix handles it, but no explicit test confirms WCAG compliance in this codebase. Recommend adding Playwright E2E test for keyboard navigation.

---

## LOW Issues

### 1. Schema.org FAQPage Sync Risk

**Location:** `Welcome.tsx:152-163` (FAQPage JSON-LD schema generation)

```typescript
const faqSchema = JSON.stringify({
  '@context': 'https://schema.org',
  '@type': 'FAQPage',
  mainEntity: faqs.map((faq) => ({
    '@type': 'Question',
    name: faq.question,
    acceptedAnswer: {
      '@type': 'Answer',
      text: faq.answer,
    },
  })),
});
```

**Problem:**
- The schema is **correctly constructed** ✓.
- It's generated from the same `faqs` prop as the UI ✓.
- However, if the WelcomeController adds or removes FAQs, the schema and visible accordion always match (good).
- **Minor issue:** The schema includes all FAQs even if `faqs.length === 0` (empty array), but the section only renders if `faqs.length > 0` (lines 585, 209). No mismatch in practice ✓.

**Severity:** LOW — working correctly, just noting for future changes.

---

### 2. Accordion Trigger Hover Styling Override

**Location:** `FaqAccordion.tsx:21`

```typescript
<AccordionTrigger className="text-left text-base font-semibold hover:no-underline hover:text-primary [&[data-state=open]]:text-primary">
```

**Problem:**
- The trigger explicitly disables underline on hover: `hover:no-underline`.
- The base `AccordionTrigger` component (ui/accordion.tsx:26) includes `hover:underline`.
- This **overrides** the base with `hover:no-underline` — unclear intent.

**Question:** Is the no-underline intentional (FAQ links shouldn't underline on hover) or accidental (should match base styling)?

**Fix:** If intentional, add a comment; if accidental, remove the override.

```typescript
// ✓ Intent: Primary text on hover, no underline (distinguishes from doc links)
<AccordionTrigger className="text-left text-base font-semibold hover:no-underline hover:text-primary [&[data-state=open]]:text-primary">
```

**Severity:** LOW — styling inconsistency, no functional issue.

---

### 3. Missing Error Boundary for Empty FAQ List

**Location:** `Welcome.tsx:585-597`

```typescript
{faqs.length > 0 && (
  <section aria-labelledby="faq-heading" className="container border-t py-24">
    {/* ... */}
    <FaqAccordion faqs={faqs} />
  </section>
)}
```

**Problem:**
- The section only renders if `faqs.length > 0` ✓.
- The component gracefully handles empty arrays (`faqs.map()` returns nothing) ✓.
- **No error state** — if the controller accidentally passes `undefined` instead of `[]`, it crashes.

**Protection:**
```typescript
<FaqAccordion faqs={faqs ?? []} />  // Safe fallback
```

**Current code** assumes `faqs` is always an array or undefined (checked before render). The Welcome component defaults to `faqs = []` (line 133), so this is actually safe. No action needed, but worth documenting.

**Severity:** LOW — safe due to defaults, low-risk if controller changes.

---

## Code Quality & Best Practices

### ✅ Strengths

1. **Component Extraction:** FaqAccordion is properly extracted, reusable, and testable.
2. **TypeScript:** Proper interfaces, no `any` types.
3. **Accessibility (Radix):** Accordion uses Radix UI, which handles WCAG 2.1 AA automatically.
4. **Schema.org Compliance:** FAQPage JSON-LD is valid and matches visible content.
5. **Feature Gating:** FAQ section conditional renders based on `faqs.length > 0`.
6. **Mobile Optimization:** Accordion is touch-friendly (Radix default).

### ⚠️ Minor Gaps

1. No unit tests for FaqAccordion (consider adding to `tests/Feature/WelcomeTest.tsx`).
2. No E2E test for accordion keyboard navigation (arrow keys, enter).
3. No loading state or skeleton for slow data fetches (minor, FAQs are hardcoded).

---

## Security Assessment

### ✅ No Direct Security Issues

- **XSS:** FAQ answers are plain text (safe); schema is sanitized with `DOMPurify.sanitize()` ✓.
- **Injection:** No user input in FAQ rendering ✓.
- **CSRF:** Not applicable (read-only component) ✓.
- **Data Exposure:** No sensitive data in FAQs ✓.

### ⚠️ Future-Proofing

If FAQs ever come from user input or a CMS, add:
```typescript
// In FaqAccordion.tsx
import DOMPurify from 'dompurify';

<AccordionContent className="text-sm text-muted-foreground">
  {DOMPurify.sanitize(faq.answer)}
</AccordionContent>
```

---

## SEO & Performance

### ✅ SEO Compliance

- FAQPage schema included and valid ✓.
- Heading hierarchy uses `<h2>` (needs `<h1>` at page level).
- Meta tags accurate and descriptive ✓.

### ✅ Performance

- No N+1 queries (FAQs are static in controller) ✓.
- Accordion uses Radix, which is optimized ✓.
- No unnecessary re-renders (Radix memoization) ✓.

---

## Testing Recommendations

### Existing Tests

Run current test suite:
```bash
php artisan test
npm test
npm run test:e2e
```

### New Tests to Add

1. **Unit Test (Vitest):**
   ```typescript
   // tests/Components/FaqAccordion.test.tsx
   it('renders all FAQs', () => {
     const faqs = [
       { question: 'Q1?', answer: 'A1' },
       { question: 'Q2?', answer: 'A2' },
     ];
     render(<FaqAccordion faqs={faqs} />);
     expect(screen.getAllByRole('button')).toHaveLength(2);
   });

   it('toggles accordion on click', async () => {
     const user = userEvent.setup();
     render(<FaqAccordion faqs={[{ question: 'Q?', answer: 'A' }]} />);
     const trigger = screen.getByRole('button', { name: /Q\?/ });
     await user.click(trigger);
     expect(screen.getByText('A')).toBeVisible();
   });

   it('handles keyboard navigation (arrow keys)', async () => {
     const user = userEvent.setup();
     render(<FaqAccordion faqs={[...]} />);
     // Radix handles this, but verify behavior
   });
   ```

2. **E2E Test (Playwright):**
   ```typescript
   // tests/e2e/welcome-faq.spec.ts
   test('FAQ accordion expands on click', async ({ page }) => {
     await page.goto('/');
     const firstQ = page.getByRole('button').first();
     await firstQ.click();
     await expect(page.getByText(/Common questions/)).toBeVisible();
   });
   ```

---

## Recommendations Before Merge

### Required (Block Merge)

1. **Fix the key prop:** Change `key={faq.question}` to `key={`faq-${index}`}` in FaqAccordion.tsx.

### Strongly Recommended (Should Fix)

2. **Extract FaqItem type:** Export from FaqAccordion and import in Welcome to avoid duplication.
3. **Add XSS guard comment:** Document that answers are plain text only (safeguard against future richtext additions).

### Nice-to-Have (Can Follow Up)

4. Add Vitest unit test for FaqAccordion keyboard navigation.
5. Add Playwright E2E test for accordion interaction.
6. Document heading hierarchy issue (pre-existing, not introduced here).

---

## Conclusion

The FAQ accordion implementation is **production-ready** with one HIGH-severity fix needed (key prop). The refactoring improves UX (interactive accordion vs. static cards), maintains accessibility via Radix UI, and includes proper schema.org compliance. No security or performance concerns identified.

**Recommendation:** Approve with required fixes applied before merge.
