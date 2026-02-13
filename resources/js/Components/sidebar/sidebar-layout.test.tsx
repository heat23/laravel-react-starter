import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, it, expect, vi, beforeEach } from "vitest";

import { usePage } from "@inertiajs/react";

import SidebarLayout from "./sidebar-layout";

vi.mock("@inertiajs/react", async () => {
  const actual = await vi.importActual("@inertiajs/react");
  return {
    ...actual,
    usePage: vi.fn(() => ({
      url: "/dashboard",
      props: {
        auth: {
          user: {
            name: "Test User",
            email: "test@example.com",
          },
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
    Link: ({
      children,
      href,
      method: _method,
      as,
      onClick,
      ...rest
    }: {
      children: React.ReactNode;
      href: string;
      method?: string;
      as?: string;
      onClick?: () => void;
      className?: string;
    }) => {
      if (as === "button") {
        return (
          <button data-href={href} onClick={onClick}>
            {children}
          </button>
        );
      }
      return (
        <a href={href} onClick={onClick} {...rest}>
          {children}
        </a>
      );
    },
  };
});

vi.mock("@/Components/theme/use-theme", () => ({
  useTheme: vi.fn(() => ({
    theme: "system",
    setTheme: vi.fn(),
    resolvedTheme: "light",
  })),
}));

const mockedUsePage = vi.mocked(usePage);

const defaultPageProps = {
  url: "/dashboard",
  props: {
    auth: {
      user: {
        name: "Test User",
        email: "test@example.com",
      },
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
};

describe("SidebarLayout", () => {
  const user = userEvent.setup();

  beforeEach(() => {
    vi.clearAllMocks();
    localStorage.clear();
    mockedUsePage.mockReturnValue(defaultPageProps as ReturnType<typeof usePage>);
  });

  describe("rendering", () => {
    it("renders children content", () => {
      render(
        <SidebarLayout>
          <div data-testid="child-content">Child Content</div>
        </SidebarLayout>,
      );
      expect(screen.getByTestId("child-content")).toBeInTheDocument();
    });

    it("renders aside element with aria-label", () => {
      const { container } = render(
        <SidebarLayout>
          <div>Content</div>
        </SidebarLayout>,
      );
      const aside = container.querySelector('aside[aria-label="Main navigation"]');
      expect(aside).toBeInTheDocument();
    });

    it("renders logo", () => {
      render(
        <SidebarLayout>
          <div>Content</div>
        </SidebarLayout>,
      );
      const logoLinks = screen.getAllByRole("link").filter((l) => l.getAttribute("href") === "/dashboard");
      expect(logoLinks.length).toBeGreaterThan(0);
    });

    it("renders main content area with id", () => {
      const { container } = render(
        <SidebarLayout>
          <div>Content</div>
        </SidebarLayout>,
      );
      expect(container.querySelector("main#main-content")).toBeInTheDocument();
    });
  });

  describe("nav groups", () => {
    it("renders nav group headings", () => {
      render(
        <SidebarLayout>
          <div>Content</div>
        </SidebarLayout>,
      );
      expect(screen.getByText("Main")).toBeInTheDocument();
      expect(screen.getByText("Settings")).toBeInTheDocument();
    });

    it("renders Dashboard and Profile nav items", () => {
      render(
        <SidebarLayout>
          <div>Content</div>
        </SidebarLayout>,
      );
      expect(screen.getByRole("link", { name: /dashboard/i })).toBeInTheDocument();
      expect(screen.getByRole("link", { name: /profile/i })).toBeInTheDocument();
    });

    it("shows feature-gated items when feature is enabled", () => {
      render(
        <SidebarLayout>
          <div>Content</div>
        </SidebarLayout>,
      );
      // apiTokens is true by default
      expect(screen.getByRole("link", { name: /api tokens/i })).toBeInTheDocument();
    });

    it("hides feature-gated items when feature is disabled", () => {
      mockedUsePage.mockReturnValue({
        ...defaultPageProps,
        props: {
          ...defaultPageProps.props,
          features: {
            ...defaultPageProps.props.features,
            apiTokens: false,
          },
        },
      } as ReturnType<typeof usePage>);

      render(
        <SidebarLayout>
          <div>Content</div>
        </SidebarLayout>,
      );
      expect(screen.queryByRole("link", { name: /api tokens/i })).not.toBeInTheDocument();
    });

    it("hides billing when billing feature is disabled", () => {
      render(
        <SidebarLayout>
          <div>Content</div>
        </SidebarLayout>,
      );
      expect(screen.queryByRole("link", { name: /billing/i })).not.toBeInTheDocument();
    });
  });

  describe("active route highlighting", () => {
    it("marks active route with aria-current", () => {
      render(
        <SidebarLayout>
          <div>Content</div>
        </SidebarLayout>,
      );
      const dashboardLink = screen.getByRole("link", { name: /dashboard/i });
      expect(dashboardLink).toHaveAttribute("aria-current", "page");
    });

    it("does not mark inactive routes", () => {
      render(
        <SidebarLayout>
          <div>Content</div>
        </SidebarLayout>,
      );
      const profileLink = screen.getByRole("link", { name: /profile/i });
      expect(profileLink).not.toHaveAttribute("aria-current");
    });
  });

  describe("collapse", () => {
    it("reads collapsed state from localStorage", () => {
      localStorage.setItem("sidebar-collapsed", "true");
      const { container } = render(
        <SidebarLayout>
          <div>Content</div>
        </SidebarLayout>,
      );
      const aside = container.querySelector("aside");
      expect(aside?.className).toContain("w-12");
    });

    it("defaults to expanded when no localStorage value", () => {
      const { container } = render(
        <SidebarLayout>
          <div>Content</div>
        </SidebarLayout>,
      );
      const aside = container.querySelector("aside");
      expect(aside?.className).toContain("w-60");
    });

    it("toggle button collapses sidebar", async () => {
      const { container } = render(
        <SidebarLayout>
          <div>Content</div>
        </SidebarLayout>,
      );

      const toggleButton = screen.getByLabelText("Collapse sidebar");
      await user.click(toggleButton);

      const aside = container.querySelector("aside");
      expect(aside?.className).toContain("w-12");
    });

    it("hides nav labels when collapsed", async () => {
      const { container } = render(
        <SidebarLayout>
          <div>Content</div>
        </SidebarLayout>,
      );

      const toggleButton = screen.getByLabelText("Collapse sidebar");
      await user.click(toggleButton);

      // The group heading "Main" should be hidden when collapsed
      const aside = container.querySelector("aside");
      const headings = aside?.querySelectorAll("h3");
      expect(headings?.length).toBe(0);
    });
  });

  describe("accessibility", () => {
    it("has aria-expanded on toggle button", () => {
      render(
        <SidebarLayout>
          <div>Content</div>
        </SidebarLayout>,
      );
      const toggleButton = screen.getByLabelText("Collapse sidebar");
      expect(toggleButton).toHaveAttribute("aria-expanded", "true");
    });

    it("updates aria-expanded when collapsed", async () => {
      render(
        <SidebarLayout>
          <div>Content</div>
        </SidebarLayout>,
      );
      const toggleButton = screen.getByLabelText("Collapse sidebar");
      await user.click(toggleButton);

      const expandButton = screen.getByLabelText("Expand sidebar");
      expect(expandButton).toHaveAttribute("aria-expanded", "false");
    });
  });

  describe("footer", () => {
    it("shows user initial", () => {
      render(
        <SidebarLayout>
          <div>Content</div>
        </SidebarLayout>,
      );
      expect(screen.getByText("T")).toBeInTheDocument();
    });

    it("shows user name and email", () => {
      render(
        <SidebarLayout>
          <div>Content</div>
        </SidebarLayout>,
      );
      expect(screen.getByText("Test User")).toBeInTheDocument();
      expect(screen.getByText("test@example.com")).toBeInTheDocument();
    });

    it("has log out button", () => {
      render(
        <SidebarLayout>
          <div>Content</div>
        </SidebarLayout>,
      );
      expect(screen.getByText("Log out")).toBeInTheDocument();
    });
  });

  describe("mobile", () => {
    it("renders mobile hamburger button", () => {
      render(
        <SidebarLayout>
          <div>Content</div>
        </SidebarLayout>,
      );
      expect(screen.getByRole("button", { name: /toggle navigation menu/i })).toBeInTheDocument();
    });

    it("has logout button in mobile sidebar", async () => {
      render(
        <SidebarLayout>
          <div>Content</div>
        </SidebarLayout>,
      );

      // Open the mobile sheet
      const menuButton = screen.getByRole("button", { name: /toggle navigation menu/i });
      await user.click(menuButton);

      // Both desktop and mobile should now have logout buttons
      const logoutButtons = screen.getAllByText("Log out");
      expect(logoutButtons.length).toBeGreaterThanOrEqual(2);
    });
  });
});
