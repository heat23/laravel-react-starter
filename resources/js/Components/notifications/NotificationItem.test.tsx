import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";

vi.mock("@inertiajs/react", () => ({
  router: {
    visit: vi.fn(),
  },
}));

import { router } from "@inertiajs/react";

import { type AppNotification } from "@/types";

import { NotificationItem } from "./NotificationItem";

function makeNotification(
  overrides: Partial<AppNotification> = {},
): AppNotification {
  return {
    id: "1",
    type: "App\\Notifications\\Test",
    data: {
      title: "Test Title",
      message: "Test message body",
      icon: "info",
      ...overrides.data,
    },
    read_at: null,
    created_at: new Date().toISOString(),
    ...overrides,
  } as AppNotification;
}

describe("NotificationItem", () => {
  const user = userEvent.setup();

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("renders notification title and message", () => {
    const notification = makeNotification();
    render(
      <div role="list">
        <NotificationItem
          notification={notification}
          onMarkAsRead={vi.fn()}
        />
      </div>,
    );
    expect(screen.getByText("Test Title")).toBeInTheDocument();
    expect(screen.getByText("Test message body")).toBeInTheDocument();
  });

  it("shows unread indicator for unread notifications", () => {
    const notification = makeNotification({ read_at: null });
    render(
      <div role="list">
        <NotificationItem
          notification={notification}
          onMarkAsRead={vi.fn()}
        />
      </div>,
    );
    const button = screen.getByRole("button");
    expect(button.className).toContain("bg-accent/50");
  });

  it("does not show unread indicator for read notifications", () => {
    const notification = makeNotification({
      read_at: "2026-01-01T00:00:00Z",
    });
    render(
      <div role="list">
        <NotificationItem
          notification={notification}
          onMarkAsRead={vi.fn()}
        />
      </div>,
    );
    const button = screen.getByRole("button");
    expect(button.className).not.toContain("bg-accent/50");
  });

  it("calls onMarkAsRead when unread notification is clicked", async () => {
    const onMarkAsRead = vi.fn();
    const notification = makeNotification({ id: "abc-123" });
    render(
      <div role="list">
        <NotificationItem
          notification={notification}
          onMarkAsRead={onMarkAsRead}
        />
      </div>,
    );
    await user.click(screen.getByRole("button"));
    expect(onMarkAsRead).toHaveBeenCalledWith("abc-123");
  });

  it("does not call onMarkAsRead when read notification is clicked", async () => {
    const onMarkAsRead = vi.fn();
    const notification = makeNotification({
      read_at: "2026-01-01T00:00:00Z",
    });
    render(
      <div role="list">
        <NotificationItem
          notification={notification}
          onMarkAsRead={onMarkAsRead}
        />
      </div>,
    );
    await user.click(screen.getByRole("button"));
    expect(onMarkAsRead).not.toHaveBeenCalled();
  });

  it("navigates to action_url when clicked", async () => {
    const notification = makeNotification({
      data: {
        title: "Navigate",
        message: "Click me",
        action_url: "/dashboard",
      },
    });
    render(
      <div role="list">
        <NotificationItem
          notification={notification}
          onMarkAsRead={vi.fn()}
        />
      </div>,
    );
    await user.click(screen.getByRole("button"));
    expect(router.visit).toHaveBeenCalledWith("/dashboard");
  });

  it("does not navigate when no action_url", async () => {
    const notification = makeNotification({
      data: { title: "No link", message: "Just info" },
    });
    render(
      <div role="list">
        <NotificationItem
          notification={notification}
          onMarkAsRead={vi.fn()}
        />
      </div>,
    );
    await user.click(screen.getByRole("button"));
    expect(router.visit).not.toHaveBeenCalled();
  });

  it("has accessible label including title, message and status", () => {
    const notification = makeNotification({
      data: { title: "Alert", message: "Something happened", icon: "warning" },
      read_at: null,
    });
    render(
      <div role="list">
        <NotificationItem
          notification={notification}
          onMarkAsRead={vi.fn()}
        />
      </div>,
    );
    const button = screen.getByRole("button");
    expect(button).toHaveAttribute("aria-label");
    const label = button.getAttribute("aria-label")!;
    expect(label).toContain("Alert");
    expect(label).toContain("Something happened");
    expect(label).toContain("Unread");
  });

  it("shows relative time for recent notifications", () => {
    const fiveMinAgo = new Date(Date.now() - 5 * 60 * 1000).toISOString();
    const notification = makeNotification({ created_at: fiveMinAgo });
    render(
      <div role="list">
        <NotificationItem
          notification={notification}
          onMarkAsRead={vi.fn()}
        />
      </div>,
    );
    expect(screen.getByText("5m ago")).toBeInTheDocument();
  });

  it("shows Just now for very recent notifications", () => {
    const notification = makeNotification({
      created_at: new Date().toISOString(),
    });
    render(
      <div role="list">
        <NotificationItem
          notification={notification}
          onMarkAsRead={vi.fn()}
        />
      </div>,
    );
    expect(screen.getByText("Just now")).toBeInTheDocument();
  });

  it("renders with listitem role", () => {
    const notification = makeNotification();
    render(
      <div role="list">
        <NotificationItem
          notification={notification}
          onMarkAsRead={vi.fn()}
        />
      </div>,
    );
    expect(screen.getByRole("listitem")).toBeInTheDocument();
  });
});
