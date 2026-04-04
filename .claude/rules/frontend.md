---
description: Frontend conventions — Inertia fire-and-forget, state management, React patterns
globs:
  - resources/js/**
---

# Frontend Conventions

**UI:** Primitives in `Components/ui/` (Radix + CVA + `cn()` from `lib/utils`). Theme via CSS variables (semantic tokens like `bg-background`, `text-foreground`). Icons: Lucide React only.

**Forms:** Inertia `useForm()` hook. Shared props via `usePage()`, feature-gated UI via `features` prop.

**Shared Inertia props must stay minimal** (auth summary + feature flags + flash). Never send whole Eloquent models — use explicit arrays.

## State Management Decision Tree

- **URL Params** (shareable/bookmarkable): Pagination, filters, search, sort order
- **React useState** (ephemeral UI): Dialog open/closed, form validation errors, hover/focus state
- **Inertia Props** (server-driven): Current user auth, feature flags, flash messages, paginated data
- **localStorage** (UI preferences NOT in user_settings): Sidebar collapsed, table column widths
- **user_settings table** (sync across devices): Theme, timezone, notification preferences

*Rule:* Use a single source of truth for filters — all URL params or all useState, never mixed. `clearFilters` must reset ALL state.

## Inertia Router Fire-and-Forget Behavior (CRITICAL)

`router.post()`, `router.patch()`, `router.delete()` return immediately, NOT a Promise.

```tsx
// WRONG: Awaiting Inertia router calls
async function deleteUser(id: number) {
    setLoading(true);
    await router.delete(`/users/${id}`); // Returns immediately! await does nothing
    setLoading(false); // Executes before server response
}

// CORRECT: Use onSuccess callback
function deleteUser(id: number) {
    setLoading(true);
    router.delete(`/users/${id}`, {
        onSuccess: () => setLoading(false),
        onError: () => setLoading(false),
    });
}

// BETTER: Use LoadingButton component
<LoadingButton onClick={() => router.delete(`/users/${id}`)}>
    Delete
</LoadingButton>
```
