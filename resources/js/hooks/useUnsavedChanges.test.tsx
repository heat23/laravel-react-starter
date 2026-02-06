import { renderHook } from '@testing-library/react';
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';

import { useUnsavedChanges } from './useUnsavedChanges';

describe('useUnsavedChanges', () => {
  let addSpy: ReturnType<typeof vi.spyOn>;
  let removeSpy: ReturnType<typeof vi.spyOn>;

  beforeEach(() => {
    addSpy = vi.spyOn(window, 'addEventListener');
    removeSpy = vi.spyOn(window, 'removeEventListener');
  });

  afterEach(() => {
    addSpy.mockRestore();
    removeSpy.mockRestore();
  });

  it('registers beforeunload listener', () => {
    renderHook(() => useUnsavedChanges(true));

    expect(addSpy).toHaveBeenCalledWith('beforeunload', expect.any(Function));
  });

  it('removes listener on unmount', () => {
    const { unmount } = renderHook(() => useUnsavedChanges(true));

    unmount();

    expect(removeSpy).toHaveBeenCalledWith('beforeunload', expect.any(Function));
  });

  it('prevents unload when dirty', () => {
    renderHook(() => useUnsavedChanges(true));

    const handler = addSpy.mock.calls.find(
      ([event]) => event === 'beforeunload',
    )![1] as EventListener;

    const event = new Event('beforeunload') as BeforeUnloadEvent;
    const preventSpy = vi.spyOn(event, 'preventDefault');

    handler(event);

    expect(preventSpy).toHaveBeenCalled();
  });

  it('does not prevent unload when not dirty', () => {
    renderHook(() => useUnsavedChanges(false));

    const handler = addSpy.mock.calls.find(
      ([event]) => event === 'beforeunload',
    )![1] as EventListener;

    const event = new Event('beforeunload') as BeforeUnloadEvent;
    const preventSpy = vi.spyOn(event, 'preventDefault');

    handler(event);

    expect(preventSpy).not.toHaveBeenCalled();
  });

  it('updates behavior when isDirty changes', () => {
    const { rerender } = renderHook(
      ({ isDirty }) => useUnsavedChanges(isDirty),
      { initialProps: { isDirty: false } },
    );

    // Initially not dirty - get the handler
    let handler = addSpy.mock.calls.find(
      ([event]) => event === 'beforeunload',
    )![1] as EventListener;

    let event = new Event('beforeunload') as BeforeUnloadEvent;
    let preventSpy = vi.spyOn(event, 'preventDefault');
    handler(event);
    expect(preventSpy).not.toHaveBeenCalled();

    // Now become dirty
    rerender({ isDirty: true });

    handler = addSpy.mock.calls
      .filter(([event]) => event === 'beforeunload')
      .pop()![1] as EventListener;

    event = new Event('beforeunload') as BeforeUnloadEvent;
    preventSpy = vi.spyOn(event, 'preventDefault');
    handler(event);
    expect(preventSpy).toHaveBeenCalled();
  });
});
