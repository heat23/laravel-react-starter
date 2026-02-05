import { renderHook, act, waitFor } from '@testing-library/react';
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { useTimezone } from './useTimezone';
import { server } from '@/test/setup';
import { http, HttpResponse } from 'msw';

describe('useTimezone Hook', () => {
  const mockBrowserTimezone = 'America/New_York';

  beforeEach(() => {
    // Mock Intl.DateTimeFormat to return consistent timezone
    vi.spyOn(Intl, 'DateTimeFormat').mockImplementation(
      () =>
        ({
          resolvedOptions: () => ({
            timeZone: mockBrowserTimezone,
          }),
        }) as Intl.DateTimeFormat
    );

    // Mock document.cookie for XSRF token
    Object.defineProperty(document, 'cookie', {
      writable: true,
      value: 'XSRF-TOKEN=test-token-value',
    });
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  // ============================================
  // Initial state tests
  // ============================================

  describe('initial state', () => {
    it('returns browser timezone as default', () => {
      const { result } = renderHook(() => useTimezone());

      expect(result.current.timezone).toBe(mockBrowserTimezone);
    });

    it('uses initialTimezone option when provided', () => {
      const { result } = renderHook(() =>
        useTimezone({ initialTimezone: 'Europe/London' })
      );

      expect(result.current.timezone).toBe('Europe/London');
    });

    it('starts with isSaving as false', () => {
      const { result } = renderHook(() => useTimezone());

      expect(result.current.isSaving).toBe(false);
    });

    it('starts with error as null', () => {
      const { result } = renderHook(() => useTimezone());

      expect(result.current.error).toBeNull();
    });
  });

  // ============================================
  // saveTimezone tests
  // ============================================

  describe('saveTimezone', () => {
    it('sets isSaving to true during API call', async () => {
      // Use a delayed response to capture the saving state
      server.use(
        http.post('/api/settings', async () => {
          await new Promise((resolve) => setTimeout(resolve, 100));
          return HttpResponse.json({ success: true });
        })
      );

      const { result } = renderHook(() => useTimezone());

      // Start the save operation
      act(() => {
        result.current.setTimezone('Europe/Paris');
      });

      // Check isSaving is true immediately after call
      expect(result.current.isSaving).toBe(true);

      // Wait for completion
      await waitFor(() => {
        expect(result.current.isSaving).toBe(false);
      });
    });

    it('makes API request with correct payload', async () => {
      let capturedBody: unknown;

      server.use(
        http.post('/api/settings', async ({ request }) => {
          capturedBody = await request.json();
          return HttpResponse.json({ success: true });
        })
      );

      const { result } = renderHook(() => useTimezone());

      await act(async () => {
        await result.current.setTimezone('Asia/Tokyo');
      });

      expect(capturedBody).toEqual({
        key: 'timezone',
        value: 'Asia/Tokyo',
      });
    });

    it('includes correct headers in API request', async () => {
      let capturedHeaders: Headers | undefined;

      server.use(
        http.post('/api/settings', async ({ request }) => {
          capturedHeaders = request.headers;
          return HttpResponse.json({ success: true });
        })
      );

      const { result } = renderHook(() => useTimezone());

      await act(async () => {
        await result.current.setTimezone('Asia/Tokyo');
      });

      expect(capturedHeaders?.get('Content-Type')).toBe('application/json');
      expect(capturedHeaders?.get('Accept')).toBe('application/json');
    });

    it('updates timezone state on success', async () => {
      server.use(
        http.post('/api/settings', () => {
          return HttpResponse.json({ success: true });
        })
      );

      const { result } = renderHook(() => useTimezone());

      expect(result.current.timezone).toBe(mockBrowserTimezone);

      await act(async () => {
        await result.current.setTimezone('Pacific/Auckland');
      });

      expect(result.current.timezone).toBe('Pacific/Auckland');
    });

    it('returns true on success', async () => {
      server.use(
        http.post('/api/settings', () => {
          return HttpResponse.json({ success: true });
        })
      );

      const { result } = renderHook(() => useTimezone());
      let returnValue: boolean | undefined;

      await act(async () => {
        returnValue = await result.current.setTimezone('Europe/Berlin');
      });

      expect(returnValue).toBe(true);
    });

    it('clears error before making request', async () => {
      // First, create an error state
      server.use(
        http.post('/api/settings', () => {
          return new HttpResponse(null, { status: 500 });
        })
      );

      const { result } = renderHook(() => useTimezone());

      await act(async () => {
        await result.current.setTimezone('Invalid/Timezone');
      });

      expect(result.current.error).not.toBeNull();

      // Now make a successful request
      server.use(
        http.post('/api/settings', () => {
          return HttpResponse.json({ success: true });
        })
      );

      await act(async () => {
        await result.current.setTimezone('Europe/London');
      });

      expect(result.current.error).toBeNull();
    });
  });

  // ============================================
  // Error handling tests
  // ============================================

  describe('error handling', () => {
    it('sets error on API failure', async () => {
      server.use(
        http.post('/api/settings', () => {
          return new HttpResponse(null, { status: 500 });
        })
      );

      const { result } = renderHook(() => useTimezone());

      await act(async () => {
        await result.current.setTimezone('Europe/Paris');
      });

      expect(result.current.error).toBe('Failed to save timezone');
    });

    it('returns false on failure', async () => {
      server.use(
        http.post('/api/settings', () => {
          return new HttpResponse(null, { status: 500 });
        })
      );

      const { result } = renderHook(() => useTimezone());
      let returnValue: boolean | undefined;

      await act(async () => {
        returnValue = await result.current.setTimezone('Europe/Paris');
      });

      expect(returnValue).toBe(false);
    });

    it('does not update timezone on failure', async () => {
      server.use(
        http.post('/api/settings', () => {
          return new HttpResponse(null, { status: 500 });
        })
      );

      const { result } = renderHook(() =>
        useTimezone({ initialTimezone: 'America/Los_Angeles' })
      );

      await act(async () => {
        await result.current.setTimezone('Europe/Paris');
      });

      // Should still be original timezone
      expect(result.current.timezone).toBe('America/Los_Angeles');
    });

    it('sets isSaving to false after failure', async () => {
      server.use(
        http.post('/api/settings', () => {
          return new HttpResponse(null, { status: 500 });
        })
      );

      const { result } = renderHook(() => useTimezone());

      await act(async () => {
        await result.current.setTimezone('Europe/Paris');
      });

      expect(result.current.isSaving).toBe(false);
    });

    it('handles network errors', async () => {
      server.use(
        http.post('/api/settings', () => {
          return HttpResponse.error();
        })
      );

      const { result } = renderHook(() => useTimezone());

      await act(async () => {
        await result.current.setTimezone('Europe/Paris');
      });

      expect(result.current.error).not.toBeNull();
    });

    it('provides generic error message for non-Error exceptions', async () => {
      // This tests the catch branch that handles non-Error objects
      server.use(
        http.post('/api/settings', () => {
          return new HttpResponse(null, { status: 403 });
        })
      );

      const { result } = renderHook(() => useTimezone());

      await act(async () => {
        await result.current.setTimezone('Europe/Paris');
      });

      expect(result.current.error).toBe('Failed to save timezone');
    });
  });

  // ============================================
  // Multiple calls tests
  // ============================================

  describe('multiple calls', () => {
    it('handles rapid successive calls', async () => {
      let callCount = 0;

      server.use(
        http.post('/api/settings', async () => {
          callCount++;
          await new Promise((resolve) => setTimeout(resolve, 10));
          return HttpResponse.json({ success: true });
        })
      );

      const { result } = renderHook(() => useTimezone());

      await act(async () => {
        // Fire multiple saves in quick succession
        const promise1 = result.current.setTimezone('Europe/London');
        const promise2 = result.current.setTimezone('Europe/Paris');
        const promise3 = result.current.setTimezone('Europe/Berlin');

        await Promise.all([promise1, promise2, promise3]);
      });

      // All calls should go through
      expect(callCount).toBe(3);
      // Last timezone should be set
      expect(result.current.timezone).toBe('Europe/Berlin');
    });
  });

  // ============================================
  // Return value stability tests
  // ============================================

  describe('return value stability', () => {
    it('returns stable setTimezone function', () => {
      const { result, rerender } = renderHook(() => useTimezone());

      const firstSetTimezone = result.current.setTimezone;
      rerender();
      const secondSetTimezone = result.current.setTimezone;

      expect(firstSetTimezone).toBe(secondSetTimezone);
    });
  });
});
