import { render, screen } from "@testing-library/react";
import { beforeEach, describe, expect, it, vi } from "vitest";

import type { ReactNode } from "react";

import { AppLayout } from "./AppLayout";

const mockUsePage = vi.fn();

vi.mock("@inertiajs/react", async () => {
  const actual = await vi.importActual("@inertiajs/react");

  return {
    ...actual,
    Link: ({ children, href, ...props }: { children: ReactNode; href: string }) => (
      <a href={href} {...props}>
        {children}
      </a>
    ),
    usePage: () => mockUsePage(),
  };
});

describe("AppLayout", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    vi.stubEnv("VITE_APP_NAME", "TestApp");
  });

  it("shows only real starter links by default", () => {
    mockUsePage.mockReturnValue({
      props: {
        features: {
          billing: true,
        },
      },
    });

    render(
      <AppLayout>
        <div>Content</div>
      </AppLayout>,
    );

    expect(screen.getAllByRole("link", { name: "Home" }).length).toBeGreaterThan(0);
    expect(screen.getAllByRole("link", { name: "Pricing" }).length).toBeGreaterThan(0);
    expect(screen.queryByRole("link", { name: "Features" })).not.toBeInTheDocument();
    expect(screen.queryByRole("link", { name: "Docs" })).not.toBeInTheDocument();
    expect(screen.queryByRole("link", { name: "About" })).not.toBeInTheDocument();
    expect(screen.queryByRole("link", { name: "Contact" })).not.toBeInTheDocument();
    expect(screen.queryByRole("link", { name: "Blog" })).not.toBeInTheDocument();
  });

  it("hides pricing links when billing is disabled", () => {
    mockUsePage.mockReturnValue({
      props: {
        features: {
          billing: false,
        },
      },
    });

    render(
      <AppLayout>
        <div>Content</div>
      </AppLayout>,
    );

    expect(screen.queryByRole("link", { name: "Pricing" })).not.toBeInTheDocument();
  });

  it("uses starter-safe footer copy and external documentation links", () => {
    mockUsePage.mockReturnValue({
      props: {
        features: {
          billing: false,
        },
      },
    });

    render(
      <AppLayout>
        <div>Content</div>
      </AppLayout>,
    );

    expect(
      screen.getByText(/a starter-ready saas foundation you can adapt with your own routes, copy, and brand/i),
    ).toBeInTheDocument();

    const laravelDocs = screen.getByRole("link", { name: "Laravel Docs" });
    expect(laravelDocs).toHaveAttribute("href", "https://laravel.com/docs");
    expect(laravelDocs).toHaveAttribute("target", "_blank");

    const inertiaDocs = screen.getByRole("link", { name: "Inertia Docs" });
    expect(inertiaDocs).toHaveAttribute("href", "https://inertiajs.com");
    expect(inertiaDocs).toHaveAttribute("target", "_blank");
  });
});
