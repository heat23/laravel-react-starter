---
description: WCAG 2.1 Level AA accessibility requirements
globs:
  - resources/js/**
---

# Accessibility (WCAG 2.1 Level AA Required)

- Keyboard-navigable: all interactive elements focusable via Tab, visible focus ring, dialogs trap focus, Esc to close
- Semantic HTML: `<button>` for actions, `<a>` for navigation, `<label>` for inputs, heading hierarchy
- ARIA: `aria-label` on icon-only buttons, `aria-describedby` for errors, `aria-live="polite"` for toasts
- Contrast: >=4.5:1 normal text, >=3:1 large text/interactive elements, never color-alone
- Verify: complete flow with keyboard only, all images have alt text, loading states announced
- Existing accessible components: `Button`, `Dialog`, `Toast`, `LoadingButton` (Radix-based)
