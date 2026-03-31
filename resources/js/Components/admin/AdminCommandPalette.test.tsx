import { render, screen, fireEvent, act, waitFor } from "@testing-library/react";
import { describe, it, expect, vi, beforeEach } from "vitest";

import { buildCommands } from "@/Components/command-palette/command-registry";
import { LayoutDashboard, Users } from "lucide-react";

// Mock inertia
vi.mock("@inertiajs/react", async () => {
  const actual = await vi.importActual("@inertiajs/react");
  return {
    ...actual,
    usePage: vi.fn(() => ({
      url: "/admin",
      props: {
        auth: {
          user: { id: 1, name: "Admin User", email: "admin@example.com", is_admin: true, is_super_admin: false },
        },
        features: {
          billing: false,
          socialAuth: false,
          emailVerification: true,
          apiTokens: true,
          userSettings: true,
          notifications: false,
          onboarding: false,
          apiDocs: false,
          twoFactor: false,
          webhooks: false,
          admin: true,
        },
      },
    })),
    router: {
      visit: vi.fn(),
      post: vi.fn(),
    },
    Link: ({ children, href }: { children: React.ReactNode; href: string }) => (
      <a href={href}>{children}</a>
    ),
  };
});

vi.mock("@/Components/theme/use-theme", () => ({
  useTheme: vi.fn(() => ({
    theme: "system",
    setTheme: vi.fn(),
    resolvedTheme: "light",
  })),
}));

const baseFeatures = {
  billing: false,
  socialAuth: false,
  emailVerification: true,
  apiTokens: true,
  userSettings: true,
  notifications: false,
};

describe("AdminCommandPalette — admin navigation commands", () => {
  const navigate = vi.fn();
  const close = vi.fn();
  const setTheme = vi.fn();

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("includes admin nav items under Admin group when isAdmin is true", () => {
    const commands = buildCommands({
      features: baseFeatures,
      resolvedTheme: "light",
      setTheme,
      navigate,
      close,
      isAdmin: true,
      adminNavItems: [
        { href: "/admin/users", label: "Users", icon: Users },
        { href: "/admin", label: "Metrics", icon: LayoutDashboard },
      ],
    });

    const adminCommands = commands.filter((c) => c.group === "Admin");
    expect(adminCommands).toHaveLength(2);
    expect(adminCommands.map((c) => c.label)).toContain("Users");
    expect(adminCommands.map((c) => c.label)).toContain("Metrics");
  });

  it("does not include admin nav items when isAdmin is false", () => {
    const commands = buildCommands({
      features: baseFeatures,
      resolvedTheme: "light",
      setTheme,
      navigate,
      close,
      isAdmin: false,
      adminNavItems: [
        { href: "/admin/users", label: "Users", icon: Users },
      ],
    });

    const adminCommands = commands.filter((c) => c.group === "Admin");
    expect(adminCommands).toHaveLength(0);
  });

  it("excludes admin group when adminNavItems is empty even if isAdmin is true", () => {
    const commands = buildCommands({
      features: baseFeatures,
      resolvedTheme: "light",
      setTheme,
      navigate,
      close,
      isAdmin: true,
      adminNavItems: [],
    });

    const adminCommands = commands.filter((c) => c.group === "Admin");
    expect(adminCommands).toHaveLength(0);
  });

  it("admin nav command calls navigate to admin route and closes", () => {
    const commands = buildCommands({
      features: baseFeatures,
      resolvedTheme: "light",
      setTheme,
      navigate,
      close,
      isAdmin: true,
      adminNavItems: [{ href: "/admin/users", label: "Users", icon: Users }],
    });

    const usersCmd = commands.find((c) => c.id === "admin-nav-/admin/users");
    expect(usersCmd).toBeDefined();
    usersCmd?.action();
    expect(navigate).toHaveBeenCalledWith("/admin/users");
    expect(close).toHaveBeenCalled();
  });

  it("admin nav command id is prefixed with admin-nav-", () => {
    const commands = buildCommands({
      features: baseFeatures,
      resolvedTheme: "light",
      setTheme,
      navigate,
      close,
      isAdmin: true,
      adminNavItems: [{ href: "/admin/failed-jobs", label: "Failed Jobs", icon: LayoutDashboard }],
    });

    const cmd = commands.find((c) => c.group === "Admin");
    expect(cmd?.id).toBe("admin-nav-/admin/failed-jobs");
  });

  it("does not include admin nav items when isAdmin option is omitted", () => {
    const commands = buildCommands({
      features: baseFeatures,
      resolvedTheme: "light",
      setTheme,
      navigate,
      close,
      adminNavItems: [{ href: "/admin", label: "Admin", icon: LayoutDashboard }],
    });

    const adminCommands = commands.filter((c) => c.group === "Admin");
    expect(adminCommands).toHaveLength(0);
  });
});

describe("AdminCommandPalette — user search", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    vi.restoreAllMocks();
  });

  it("fetches users from /admin/feature-flags/search-users when admin types 2+ chars", async () => {
    const mockUsers = [
      { id: 1, name: "Alice Smith", email: "alice@example.com" },
      { id: 2, name: "Bob Jones", email: "bob@example.com" },
    ];

    vi.stubGlobal(
      "fetch",
      vi.fn().mockResolvedValue({
        ok: true,
        json: async () => mockUsers,
      }),
    );

    const { CommandPalette } = await import(
      "@/Components/command-palette/command-palette"
    );

    render(
      <CommandPalette
        open={true}
        onOpenChange={vi.fn()}
      />,
    );

    const input = screen.getByPlaceholderText("Type a command or search...");
    await act(async () => {
      fireEvent.change(input, { target: { value: "ali" } });
    });

    await waitFor(
      () => {
        expect(fetch).toHaveBeenCalledWith(
          "/admin/feature-flags/search-users?q=ali",
        );
      },
      { timeout: 500 },
    );
  });

  it("does not fetch users when query is less than 2 characters", async () => {
    vi.stubGlobal("fetch", vi.fn());

    const { CommandPalette } = await import(
      "@/Components/command-palette/command-palette"
    );

    render(
      <CommandPalette
        open={true}
        onOpenChange={vi.fn()}
      />,
    );

    const input = screen.getByPlaceholderText("Type a command or search...");
    await act(async () => {
      fireEvent.change(input, { target: { value: "a" } });
    });

    // Wait 400ms to ensure no debounced call fires
    await new Promise((resolve) => setTimeout(resolve, 400));
    expect(fetch).not.toHaveBeenCalled();
  });
});
