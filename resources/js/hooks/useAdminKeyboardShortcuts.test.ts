import { renderHook } from "@testing-library/react";
import { describe, it, expect, vi, afterEach } from "vitest";

import { useAdminKeyboardShortcuts } from "./useAdminKeyboardShortcuts";

function fireKey(key: string, opts: Partial<KeyboardEventInit> = {}) {
  document.dispatchEvent(new KeyboardEvent("keydown", { key, bubbles: true, ...opts }));
}

describe("useAdminKeyboardShortcuts", () => {
  afterEach(() => {
    vi.restoreAllMocks();
  });

  it("calls onSearch when / is pressed", () => {
    const onSearch = vi.fn();
    renderHook(() => useAdminKeyboardShortcuts({ onSearch }));
    fireKey("/");
    expect(onSearch).toHaveBeenCalledOnce();
  });

  it("calls onNextPage when n is pressed", () => {
    const onNextPage = vi.fn();
    renderHook(() => useAdminKeyboardShortcuts({ onNextPage }));
    fireKey("n");
    expect(onNextPage).toHaveBeenCalledOnce();
  });

  it("calls onPrevPage when p is pressed", () => {
    const onPrevPage = vi.fn();
    renderHook(() => useAdminKeyboardShortcuts({ onPrevPage }));
    fireKey("p");
    expect(onPrevPage).toHaveBeenCalledOnce();
  });

  it("ignores shortcuts when modifier keys are held", () => {
    const onSearch = vi.fn();
    const onNextPage = vi.fn();
    renderHook(() => useAdminKeyboardShortcuts({ onSearch, onNextPage }));

    fireKey("/", { metaKey: true });
    fireKey("n", { ctrlKey: true });
    fireKey("p", { altKey: true });

    expect(onSearch).not.toHaveBeenCalled();
    expect(onNextPage).not.toHaveBeenCalled();
  });

  it("ignores n/p when target is an input element", () => {
    const onNextPage = vi.fn();
    renderHook(() => useAdminKeyboardShortcuts({ onNextPage }));

    const input = document.createElement("input");
    document.body.appendChild(input);
    input.dispatchEvent(new KeyboardEvent("keydown", { key: "n", bubbles: true }));
    document.body.removeChild(input);

    expect(onNextPage).not.toHaveBeenCalled();
  });

  it("ignores / when target is an input element", () => {
    const onSearch = vi.fn();
    renderHook(() => useAdminKeyboardShortcuts({ onSearch }));

    const input = document.createElement("input");
    document.body.appendChild(input);
    input.dispatchEvent(new KeyboardEvent("keydown", { key: "/", bubbles: true }));
    document.body.removeChild(input);

    expect(onSearch).not.toHaveBeenCalled();
  });

  it("does nothing when no callbacks provided", () => {
    renderHook(() => useAdminKeyboardShortcuts({}));
    // Should not throw
    fireKey("/");
    fireKey("n");
    fireKey("p");
  });

  it("cleans up event listener on unmount", () => {
    const onSearch = vi.fn();
    const { unmount } = renderHook(() => useAdminKeyboardShortcuts({ onSearch }));
    unmount();
    fireKey("/");
    expect(onSearch).not.toHaveBeenCalled();
  });
});
