import { renderHook } from '@testing-library/react';
import { toast } from 'sonner';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { usePage } from '@inertiajs/react';


import { useFlashToasts } from './useFlashToasts';

// sonner is mocked per-file so toast.* spies are fully isolated to this suite.
// The global setup (resources/js/test/setup.ts) does NOT mock sonner.
vi.mock('sonner', () => ({
  toast: {
    success: vi.fn(),
    error: vi.fn(),
    warning: vi.fn(),
    info: vi.fn(),
  },
}));

// usePage is provided as vi.fn() by the global test setup at
// resources/js/test/setup.ts:32 via vi.mock('@inertiajs/react', ...).
// The runtime assertion in beforeEach guards against isolation failures —
// if the global mock is absent, every test fails with a clear error rather
// than a confusing "mockReturnValue is not a function" message.

describe('useFlashToasts', () => {
  let successSpy: ReturnType<typeof vi.fn>;
  let errorSpy: ReturnType<typeof vi.fn>;
  let warningSpy: ReturnType<typeof vi.fn>;
  let infoSpy: ReturnType<typeof vi.fn>;

  beforeEach(() => {
    // Confirm the global setup has provided a vi.fn() for usePage.
    // Points to: resources/js/test/setup.ts line 32.
    expect(vi.isMockFunction(usePage)).toBe(true);

    vi.clearAllMocks();

    successSpy = toast.success as ReturnType<typeof vi.fn>;
    errorSpy = toast.error as ReturnType<typeof vi.fn>;
    warningSpy = toast.warning as ReturnType<typeof vi.fn>;
    infoSpy = toast.info as ReturnType<typeof vi.fn>;
  });

  function renderWithFlash(flash: Record<string, unknown>) {
    vi.mocked(usePage).mockReturnValue({
      props: { flash },
    } as ReturnType<typeof usePage>);
    return renderHook(() => useFlashToasts());
  }

  it('shows a success toast when flash.success is set', () => {
    renderWithFlash({ success: 'Saved successfully' });
    expect(successSpy).toHaveBeenCalledOnce();
    expect(successSpy).toHaveBeenCalledWith('Saved successfully');
  });

  it('shows an error toast when flash.error is set', () => {
    renderWithFlash({ error: 'Something went wrong' });
    expect(errorSpy).toHaveBeenCalledOnce();
    expect(errorSpy).toHaveBeenCalledWith('Something went wrong');
  });

  it('shows a warning toast when flash.warning is set', () => {
    renderWithFlash({ warning: 'Subscription expiring soon' });
    expect(warningSpy).toHaveBeenCalledOnce();
    expect(warningSpy).toHaveBeenCalledWith('Subscription expiring soon');
  });

  it('shows an info toast when flash.info is set', () => {
    renderWithFlash({ info: 'Profile updated' });
    expect(infoSpy).toHaveBeenCalledOnce();
    expect(infoSpy).toHaveBeenCalledWith('Profile updated');
  });

  it('shows both success and error toasts when both flash values are set simultaneously', () => {
    // Guards against a refactor that returns early after the first truthy flash value —
    // both keys must fire in a single render pass.
    renderWithFlash({ success: 'Created', error: 'Partial failure' });
    expect(successSpy).toHaveBeenCalledOnce();
    expect(successSpy).toHaveBeenCalledWith('Created');
    expect(errorSpy).toHaveBeenCalledOnce();
    expect(errorSpy).toHaveBeenCalledWith('Partial failure');
  });

  it('does not show a toast when flash.success is empty string', () => {
    // Guards against a regression that changes the truthiness guard to !== undefined,
    // which would incorrectly fire toasts for empty strings.
    renderWithFlash({ success: '' });
    expect(successSpy).not.toHaveBeenCalled();
  });

  it('does not show any toast when all flash values are undefined', () => {
    renderWithFlash({
      success: undefined,
      error: undefined,
      warning: undefined,
      info: undefined,
    });
    expect(successSpy).not.toHaveBeenCalled();
    expect(errorSpy).not.toHaveBeenCalled();
    expect(warningSpy).not.toHaveBeenCalled();
    expect(infoSpy).not.toHaveBeenCalled();
  });

  it('calls toast.success with non-string truthy value — contract: no typeof guard applied', () => {
    // Intentional: the hook uses truthiness only, not typeof === 'string'.
    // This test documents that numeric/truthy flash values flow through unchanged.
    // If a typeof guard is ever added, this test will catch the regression.
    renderWithFlash({ success: 42 as unknown as string });
    expect(successSpy).toHaveBeenCalledWith(42);
  });

  it('does not fire again on re-render when flash is the same reference', () => {
    const flash = { success: 'Saved' };
    vi.mocked(usePage).mockReturnValue({
      props: { flash },
    } as ReturnType<typeof usePage>);

    const { rerender } = renderHook(() => useFlashToasts());
    expect(successSpy).toHaveBeenCalledTimes(1);

    rerender();
    // Same flash reference → useEffect dependency unchanged → hook does not re-fire.
    expect(successSpy).toHaveBeenCalledTimes(1);
  });

  it('fires again when flash reference changes with different content', () => {
    vi.mocked(usePage).mockReturnValue({
      props: { flash: { success: 'First save' } },
    } as ReturnType<typeof usePage>);
    const { rerender } = renderHook(() => useFlashToasts());
    expect(successSpy).toHaveBeenCalledTimes(1);

    vi.mocked(usePage).mockReturnValue({
      props: { flash: { success: 'Second save' } },
    } as ReturnType<typeof usePage>);
    rerender();

    expect(successSpy).toHaveBeenCalledTimes(2);
    expect(successSpy).toHaveBeenLastCalledWith('Second save');
  });

  it('does not fire again on re-render when flash reference changes but values are identical', () => {
    // The hook uses JSON.stringify for deduplication, not object reference equality.
    // Two distinct flash objects with the same content must only trigger one toast.
    const flashA = { success: 'Saved' };
    const flashB = { success: 'Saved' }; // new reference, identical value

    vi.mocked(usePage).mockReturnValue({
      props: { flash: flashA },
    } as ReturnType<typeof usePage>);
    const { rerender } = renderHook(() => useFlashToasts());
    expect(successSpy).toHaveBeenCalledTimes(1);

    vi.mocked(usePage).mockReturnValue({
      props: { flash: flashB },
    } as ReturnType<typeof usePage>);
    rerender();

    // flashA !== flashB by reference, but JSON.stringify produces the same key → no re-fire.
    expect(successSpy).toHaveBeenCalledTimes(1);
  });
});
