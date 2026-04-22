import { renderHook, act } from "@testing-library/react";
import { describe, it, expect, vi, beforeEach } from "vitest";

import { router } from "@inertiajs/react";

import { useAdminAction, type AdminActionTarget } from "./useAdminAction";

type RouterOptions = {
  onSuccess?: () => void;
  onError?: () => void;
};

vi.mock("@inertiajs/react", () => ({
  router: {
    patch: vi.fn((_url: string, _data: unknown, options?: RouterOptions) => {
      options?.onSuccess?.();
    }),
    post: vi.fn((_url: string, _data: unknown, options?: RouterOptions) => {
      options?.onSuccess?.();
    }),
  },
}));

describe("useAdminAction", () => {
  const mockUser: AdminActionTarget = {
    id: 1,
    name: "John Doe",
    is_admin: false,
    deleted_at: null,
  };

  const mockAdmin: AdminActionTarget = {
    id: 2,
    name: "Admin User",
    is_admin: true,
    deleted_at: null,
  };

  const mockDeleted: AdminActionTarget = {
    id: 3,
    name: "Deleted User",
    is_admin: false,
    deleted_at: "2025-01-01T00:00:00Z",
  };

  beforeEach(() => {
    vi.clearAllMocks();
    (router.patch as ReturnType<typeof vi.fn>).mockImplementation(
      (_url: string, _data: unknown, options?: RouterOptions) => {
        options?.onSuccess?.();
      }
    );
    (router.post as ReturnType<typeof vi.fn>).mockImplementation(
      (_url: string, _data: unknown, options?: RouterOptions) => {
        options?.onSuccess?.();
      }
    );
  });

  it("starts with no confirm action", () => {
    const { result } = renderHook(() => useAdminAction());
    expect(result.current.confirmAction).toBeNull();
  });

  it("sets confirm action", () => {
    const { result } = renderHook(() => useAdminAction());
    act(() => {
      result.current.setConfirmAction({ type: "toggleAdmin", user: mockUser });
    });
    expect(result.current.confirmAction).toEqual({ type: "toggleAdmin", user: mockUser });
  });

  describe("getDialogProps", () => {
    it("returns empty defaults when no action set", () => {
      const { result } = renderHook(() => useAdminAction());
      const props = result.current.getDialogProps();
      expect(props.title).toBe("");
      expect(props.confirmLabel).toBe("Confirm");
    });

    it("returns grant admin dialog for non-admin user", () => {
      const { result } = renderHook(() => useAdminAction());
      act(() => {
        result.current.setConfirmAction({ type: "toggleAdmin", user: mockUser });
      });
      const props = result.current.getDialogProps();
      expect(props.title).toBe("Grant Admin Access");
      expect(props.description).toContain("grant admin access to");
      expect(props.description).toContain("John Doe");
      expect(props.variant).toBe("default");
    });

    it("returns remove admin dialog for admin user", () => {
      const { result } = renderHook(() => useAdminAction());
      act(() => {
        result.current.setConfirmAction({ type: "toggleAdmin", user: mockAdmin });
      });
      const props = result.current.getDialogProps();
      expect(props.title).toBe("Remove Admin Access");
      expect(props.description).toContain("remove admin access from");
      expect(props.variant).toBe("destructive");
    });

    it("returns deactivate dialog for active user", () => {
      const { result } = renderHook(() => useAdminAction());
      act(() => {
        result.current.setConfirmAction({ type: "toggleActive", user: mockUser });
      });
      const props = result.current.getDialogProps();
      expect(props.title).toBe("Deactivate User");
      expect(props.variant).toBe("destructive");
    });

    it("returns restore dialog for deleted user", () => {
      const { result } = renderHook(() => useAdminAction());
      act(() => {
        result.current.setConfirmAction({ type: "toggleActive", user: mockDeleted });
      });
      const props = result.current.getDialogProps();
      expect(props.title).toBe("Restore User");
      expect(props.description).toContain("Restore");
      expect(props.variant).toBe("default");
    });

  });

  describe("executeAction", () => {
    it("resolves immediately when no action set", async () => {
      const { result } = renderHook(() => useAdminAction());
      await act(async () => {
        await result.current.executeAction();
      });
      expect(router.patch).not.toHaveBeenCalled();
      expect(router.post).not.toHaveBeenCalled();
    });

    it("calls router.patch for toggleAdmin", async () => {
      const { result } = renderHook(() => useAdminAction());
      act(() => {
        result.current.setConfirmAction({ type: "toggleAdmin", user: mockUser });
      });

      await act(async () => {
        await result.current.executeAction();
      });

      expect(router.patch).toHaveBeenCalledWith(
        "/admin/users/1/toggle-admin",
        {},
        expect.objectContaining({ preserveState: true }),
      );
    });

    it("calls router.patch for toggleActive", async () => {
      const { result } = renderHook(() => useAdminAction());
      act(() => {
        result.current.setConfirmAction({ type: "toggleActive", user: mockUser });
      });

      await act(async () => {
        await result.current.executeAction();
      });

      expect(router.patch).toHaveBeenCalledWith(
        "/admin/users/1/toggle-active",
        {},
        expect.objectContaining({ preserveState: true }),
      );
    });

  });

  describe("optimistic updates", () => {
    it("calls onOptimisticUpdate before the router call", async () => {
      const callOrder: string[] = [];
      (router.patch as ReturnType<typeof vi.fn>).mockImplementation(
        (_url: string, _data: unknown, options?: RouterOptions) => {
          callOrder.push("router");
          options?.onSuccess?.();
        }
      );

      const { result } = renderHook(() => useAdminAction());
      act(() => {
        result.current.setConfirmAction({
          type: "toggleAdmin",
          user: mockUser,
          onOptimisticUpdate: () => callOrder.push("optimistic"),
        });
      });

      await act(async () => {
        await result.current.executeAction();
      });

      expect(callOrder).toEqual(["optimistic", "router"]);
    });

    it("calls onSuccess after a successful router call", async () => {
      const onSuccess = vi.fn();
      const { result } = renderHook(() => useAdminAction());
      act(() => {
        result.current.setConfirmAction({
          type: "toggleAdmin",
          user: mockUser,
          onSuccess,
        });
      });

      await act(async () => {
        await result.current.executeAction();
      });

      expect(onSuccess).toHaveBeenCalledTimes(1);
    });

    it("calls onRollback instead of onSuccess when router fails", async () => {
      (router.patch as ReturnType<typeof vi.fn>).mockImplementation(
        (_url: string, _data: unknown, options?: RouterOptions) => {
          options?.onError?.();
        }
      );

      const onOptimisticUpdate = vi.fn();
      const onRollback = vi.fn();
      const onSuccess = vi.fn();
      const { result } = renderHook(() => useAdminAction());
      act(() => {
        result.current.setConfirmAction({
          type: "toggleActive",
          user: mockUser,
          onOptimisticUpdate,
          onRollback,
          onSuccess,
        });
      });

      await act(async () => {
        await result.current.executeAction().catch(() => {});
      });

      expect(onOptimisticUpdate).toHaveBeenCalledTimes(1);
      expect(onRollback).toHaveBeenCalledTimes(1);
      expect(onSuccess).not.toHaveBeenCalled();
    });

    it("does not require optimistic callbacks (backward compatible)", async () => {
      const { result } = renderHook(() => useAdminAction());
      act(() => {
        result.current.setConfirmAction({ type: "toggleAdmin", user: mockUser });
      });

      await act(async () => {
        await result.current.executeAction();
      });

      expect(router.patch).toHaveBeenCalledTimes(1);
    });
  });
});
