# ADR 0004: Inertia Router Fire-and-Forget Pattern

## Status
Accepted

## Context
Inertia.js `router.post()`, `router.patch()`, `router.delete()` do not return Promises — they fire the request and return immediately. Developers (including AI assistants) frequently write `await router.delete(...)` expecting it to wait for the server response, leading to race conditions where loading states are cleared before the server responds.

## Decision
1. Never `await` Inertia router calls — use `onSuccess`/`onError` callbacks instead
2. Use the `LoadingButton` component for any button that triggers a server mutation (handles loading state automatically)
3. When wrapping Inertia calls in async functions (e.g., `onConfirm` in dialogs), the function must use callbacks, not await
4. Tests that mock Inertia router calls must invoke `onSuccess` callbacks to simulate realistic async behavior

## Consequences

### Positive
- Loading states are correctly managed via callbacks
- `LoadingButton` component provides consistent UX across all mutation buttons
- Test patterns catch the async bug before it reaches production

### Negative
- Callback-based code is more verbose than async/await
- Easy to regress — requires constant vigilance in code review

### Testing Requirements
- Component tests that wrap `router.*` must mock with `onSuccess` callback invocation
- `useFlashToasts` hook tests verify flash messages are consumed after navigation

## References
- `resources/js/Components/ui/loading-button.tsx` — loading state component
- `resources/js/hooks/useFlashToasts.ts` — flash message consumption
- CLAUDE.md "Inertia Router Fire-and-Forget Behavior" section
