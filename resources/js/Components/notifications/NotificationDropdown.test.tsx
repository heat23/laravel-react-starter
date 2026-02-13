import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";

import axios from "axios";
import { toast } from "sonner";

import { usePage } from "@inertiajs/react";

import { NotificationDropdown } from "./NotificationDropdown";

vi.mock("@inertiajs/react", () => ({
  usePage: vi.fn(),
  router: {
    visit: vi.fn(),
    reload: vi.fn(),
  },
}));

vi.mock("sonner", () => ({
  toast: { success: vi.fn(), error: vi.fn() },
}));

vi.mock("axios");

function mockPageProps(overrides: Record<string, unknown> = {}) {
  (usePage as ReturnType<typeof vi.fn>).mockReturnValue({
    props: {
      notifications_unread_count: 0,
      ...overrides,
    },
  });
}

const sampleNotifications = [
  {
    id: "1",
    type: "App\\Notifications\\Test",
    data: { title: "Welcome", message: "Hello world", icon: "info" },
    read_at: null,
    created_at: new Date().toISOString(),
  },
  {
    id: "2",
    type: "App\\Notifications\\Test",
    data: { title: "Done", message: "Task completed", icon: "success" },
    read_at: "2026-01-01T00:00:00Z",
    created_at: "2026-01-01T00:00:00Z",
  },
];

describe("NotificationDropdown", () => {
  const user = userEvent.setup();

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("renders bell icon", () => {
    mockPageProps();
    render(<NotificationDropdown />);
    expect(screen.getByLabelText("Notifications")).toBeInTheDocument();
  });

  it("renders bell icon with unread count badge", () => {
    mockPageProps({ notifications_unread_count: 5 });
    render(<NotificationDropdown />);
    expect(screen.getByText("5")).toBeInTheDocument();
  });

  it("hides badge when count is 0", () => {
    mockPageProps({ notifications_unread_count: 0 });
    render(<NotificationDropdown />);
    expect(screen.queryByText("0")).not.toBeInTheDocument();
  });

  it("shows 99+ for large counts", () => {
    mockPageProps({ notifications_unread_count: 150 });
    render(<NotificationDropdown />);
    expect(screen.getByText("99+")).toBeInTheDocument();
  });

  it("includes unread count in aria-label", () => {
    mockPageProps({ notifications_unread_count: 3 });
    render(<NotificationDropdown />);
    expect(
      screen.getByLabelText("Notifications, 3 unread"),
    ).toBeInTheDocument();
  });

  it("fetches notifications when popover opens", async () => {
    mockPageProps({ notifications_unread_count: 1 });
    vi.mocked(axios.get).mockResolvedValueOnce({
      data: { data: sampleNotifications },
    });

    render(<NotificationDropdown />);
    await user.click(screen.getByLabelText("Notifications, 1 unread"));

    await waitFor(() => {
      expect(axios.get).toHaveBeenCalledWith("/api/notifications");
    });

    expect(screen.getByText("Welcome")).toBeInTheDocument();
    expect(screen.getByText("Done")).toBeInTheDocument();
  });

  it("shows loading state on first open", async () => {
    mockPageProps();
    let resolvePromise: (value: unknown) => void;
    const pending = new Promise((r) => {
      resolvePromise = r;
    });
    vi.mocked(axios.get).mockReturnValueOnce(pending as never);

    render(<NotificationDropdown />);
    await user.click(screen.getByLabelText("Notifications"));

    expect(screen.getByText("Loading...")).toBeInTheDocument();

    resolvePromise!({ data: { data: [] } });
    await waitFor(() => {
      expect(screen.queryByText("Loading...")).not.toBeInTheDocument();
    });
  });

  it("shows empty state when no notifications", async () => {
    mockPageProps();
    vi.mocked(axios.get).mockResolvedValueOnce({
      data: { data: [] },
    });

    render(<NotificationDropdown />);
    await user.click(screen.getByLabelText("Notifications"));

    await waitFor(() => {
      expect(screen.getByText("No notifications yet")).toBeInTheDocument();
    });
  });

  it("shows error state with retry button on fetch failure", async () => {
    mockPageProps();
    vi.mocked(axios.get).mockRejectedValueOnce(new Error("Network error"));

    render(<NotificationDropdown />);
    await user.click(screen.getByLabelText("Notifications"));

    await waitFor(() => {
      expect(
        screen.getByText("Failed to load notifications"),
      ).toBeInTheDocument();
    });
    expect(screen.getByText("Retry")).toBeInTheDocument();
  });

  it("shows mark all as read button when unread notifications exist", async () => {
    mockPageProps({ notifications_unread_count: 1 });
    vi.mocked(axios.get).mockResolvedValueOnce({
      data: { data: sampleNotifications },
    });

    render(<NotificationDropdown />);
    await user.click(screen.getByLabelText("Notifications, 1 unread"));

    await waitFor(() => {
      expect(screen.getByText("Mark all as read")).toBeInTheDocument();
    });
  });

  it("shows error toast when mark-as-read fails", async () => {
    mockPageProps({ notifications_unread_count: 1 });
    vi.mocked(axios.get).mockResolvedValueOnce({
      data: { data: sampleNotifications },
    });
    vi.mocked(axios.patch).mockRejectedValueOnce(new Error("Server error"));

    render(<NotificationDropdown />);
    await user.click(screen.getByLabelText("Notifications, 1 unread"));

    await waitFor(() => {
      expect(screen.getByText("Welcome")).toBeInTheDocument();
    });

    // Click the unread notification to mark as read
    await user.click(screen.getByText("Welcome"));

    await waitFor(() => {
      expect(toast.error).toHaveBeenCalledWith(
        "Failed to mark notification as read",
      );
    });
  });

  it("shows error toast when mark-all-as-read fails", async () => {
    mockPageProps({ notifications_unread_count: 1 });
    vi.mocked(axios.get).mockResolvedValueOnce({
      data: { data: sampleNotifications },
    });
    vi.mocked(axios.post).mockRejectedValueOnce(new Error("Server error"));

    render(<NotificationDropdown />);
    await user.click(screen.getByLabelText("Notifications, 1 unread"));

    await waitFor(() => {
      expect(screen.getByText("Mark all as read")).toBeInTheDocument();
    });

    await user.click(screen.getByText("Mark all as read"));

    await waitFor(() => {
      expect(toast.error).toHaveBeenCalledWith(
        "Failed to mark all as read",
      );
    });
  });
});
