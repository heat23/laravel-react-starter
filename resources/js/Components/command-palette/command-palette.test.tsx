import { render, screen, fireEvent, act } from "@testing-library/react";
import { describe, it, expect, vi, beforeEach } from "vitest";

import { buildCommands } from "./command-registry";
import { useCommandPalette } from "./use-command-palette";

// Mock inertia
vi.mock("@inertiajs/react", async () => {
  const actual = await vi.importActual("@inertiajs/react");
  return {
    ...actual,
    usePage: vi.fn(() => ({
      url: "/dashboard",
      props: {
        auth: {
          user: { name: "Test User", email: "test@example.com" },
        },
        features: {
          billing: false,
          socialAuth: false,
          emailVerification: true,
          apiTokens: true,
          userSettings: true,
          notifications: false,
        },
      },
    })),
    router: {
      visit: vi.fn(),
      post: vi.fn(),
    },
    Link: ({
      children,
      href,
    }: {
      children: React.ReactNode;
      href: string;
    }) => <a href={href}>{children}</a>,
  };
});

vi.mock("@/Components/theme/use-theme", () => ({
  useTheme: vi.fn(() => ({
    theme: "system",
    setTheme: vi.fn(),
    resolvedTheme: "light",
  })),
}));

describe("useCommandPalette", () => {
  it("opens with Cmd+K", () => {
    let hookResult: ReturnType<typeof useCommandPalette>;

    function TestComponent() {
      hookResult = useCommandPalette();
      return <div data-testid="open">{String(hookResult.open)}</div>;
    }

    render(<TestComponent />);
    expect(screen.getByTestId("open").textContent).toBe("false");

    act(() => {
      fireEvent.keyDown(document, { key: "k", metaKey: true });
    });

    expect(screen.getByTestId("open").textContent).toBe("true");
  });

  it("opens with Ctrl+K", () => {
    let hookResult: ReturnType<typeof useCommandPalette>;

    function TestComponent() {
      hookResult = useCommandPalette();
      return <div data-testid="open">{String(hookResult.open)}</div>;
    }

    render(<TestComponent />);

    act(() => {
      fireEvent.keyDown(document, { key: "k", ctrlKey: true });
    });

    expect(screen.getByTestId("open").textContent).toBe("true");
  });

  it("toggles on repeated Cmd+K", () => {
    let hookResult: ReturnType<typeof useCommandPalette>;

    function TestComponent() {
      hookResult = useCommandPalette();
      return <div data-testid="open">{String(hookResult.open)}</div>;
    }

    render(<TestComponent />);

    act(() => {
      fireEvent.keyDown(document, { key: "k", metaKey: true });
    });
    expect(screen.getByTestId("open").textContent).toBe("true");

    act(() => {
      fireEvent.keyDown(document, { key: "k", metaKey: true });
    });
    expect(screen.getByTestId("open").textContent).toBe("false");
  });
});

describe("buildCommands", () => {
  const features = {
    billing: false,
    socialAuth: false,
    emailVerification: true,
    apiTokens: true,
    userSettings: true,
    notifications: false,
  };
  const setTheme = vi.fn();
  const navigate = vi.fn();
  const close = vi.fn();

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("returns navigation items from visible nav items", () => {
    const commands = buildCommands({
      features,
      resolvedTheme: "light",
      setTheme,
      navigate,
      close,
    });
    const navCommands = commands.filter((c) => c.group === "Navigation");
    // Dashboard, Profile, API Tokens (apiTokens enabled, billing disabled)
    expect(navCommands).toHaveLength(3);
    expect(navCommands.map((c) => c.label)).toContain("Dashboard");
    expect(navCommands.map((c) => c.label)).toContain("Profile");
    expect(navCommands.map((c) => c.label)).toContain("API Tokens");
  });

  it("hides feature-gated nav items when feature is disabled", () => {
    const commands = buildCommands({
      features: { ...features, apiTokens: false },
      resolvedTheme: "light",
      setTheme,
      navigate,
      close,
    });
    const navLabels = commands.filter((c) => c.group === "Navigation").map((c) => c.label);
    expect(navLabels).not.toContain("API Tokens");
    expect(navLabels).not.toContain("Billing");
  });

  it("nav command action calls navigate and close", () => {
    const commands = buildCommands({
      features,
      resolvedTheme: "light",
      setTheme,
      navigate,
      close,
    });
    const dashboardCmd = commands.find((c) => c.label === "Dashboard");
    dashboardCmd?.action();
    expect(navigate).toHaveBeenCalledWith("/dashboard");
    expect(close).toHaveBeenCalled();
  });

  it("includes Copy Current URL action", () => {
    const commands = buildCommands({
      features,
      resolvedTheme: "light",
      setTheme,
      navigate,
      close,
    });
    expect(commands.find((c) => c.label === "Copy Current URL")).toBeDefined();
  });

  it("includes Log Out action", () => {
    const commands = buildCommands({
      features,
      resolvedTheme: "light",
      setTheme,
      navigate,
      close,
    });
    expect(commands.find((c) => c.label === "Log Out")).toBeDefined();
  });

  it("includes theme commands", () => {
    const commands = buildCommands({
      features,
      resolvedTheme: "light",
      setTheme,
      navigate,
      close,
    });
    const themeCommands = commands.filter((c) => c.group === "Theme");
    expect(themeCommands).toHaveLength(3);
    expect(themeCommands.map((c) => c.label)).toEqual(
      expect.arrayContaining(["Light Mode", "Dark Mode", "System Theme"]),
    );
  });

  it("theme command calls setTheme", () => {
    const commands = buildCommands({
      features,
      resolvedTheme: "light",
      setTheme,
      navigate,
      close,
    });
    const darkCmd = commands.find((c) => c.label === "Dark Mode");
    darkCmd?.action();
    expect(setTheme).toHaveBeenCalledWith("dark");
    expect(close).toHaveBeenCalled();
  });
});
