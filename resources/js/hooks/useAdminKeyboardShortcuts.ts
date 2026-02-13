import { useEffect, useRef, useCallback } from "react";

interface UseAdminKeyboardShortcutsOptions {
  /** Callback when "/" is pressed — typically focuses search input. */
  onSearch?: () => void;
  /** Callback when "n" is pressed — typically goes to next page. */
  onNextPage?: () => void;
  /** Callback when "p" is pressed — typically goes to previous page. */
  onPrevPage?: () => void;
}

/**
 * Admin keyboard shortcuts for list pages.
 *
 * - `/` — Focus search input
 * - `n` — Next page
 * - `p` — Previous page
 *
 * All shortcuts are ignored when the user is typing in an input, textarea, or
 * contenteditable element, or when modifier keys are held.
 */
export function useAdminKeyboardShortcuts({
  onSearch,
  onNextPage,
  onPrevPage,
}: UseAdminKeyboardShortcutsOptions) {
  const onSearchRef = useRef(onSearch);
  const onNextPageRef = useRef(onNextPage);
  const onPrevPageRef = useRef(onPrevPage);

  onSearchRef.current = onSearch;
  onNextPageRef.current = onNextPage;
  onPrevPageRef.current = onPrevPage;

  const handleKeyDown = useCallback((e: KeyboardEvent) => {
    if (e.metaKey || e.ctrlKey || e.altKey) return;

    const target = e.target as HTMLElement;
    const isEditing =
      target.tagName === "INPUT" ||
      target.tagName === "TEXTAREA" ||
      target.isContentEditable;

    if (e.key === "/" && !isEditing) {
      e.preventDefault();
      onSearchRef.current?.();
      return;
    }

    if (isEditing) return;

    if (e.key === "n") {
      onNextPageRef.current?.();
    } else if (e.key === "p") {
      onPrevPageRef.current?.();
    }
  }, []);

  useEffect(() => {
    document.addEventListener("keydown", handleKeyDown);
    return () => document.removeEventListener("keydown", handleKeyDown);
  }, [handleKeyDown]);
}
