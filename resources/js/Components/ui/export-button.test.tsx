import { render, screen, fireEvent, act } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";

import { ExportButton } from "./export-button";

describe("ExportButton", () => {
  it("renders with default label", () => {
    render(<ExportButton href="/export/users" />);
    expect(screen.getByText("Export CSV")).toBeInTheDocument();
  });

  it("renders with custom label", () => {
    render(<ExportButton href="/export/users" label="Download Users" />);
    expect(screen.getByText("Download Users")).toBeInTheDocument();
  });

  it("constructs correct href without params", () => {
    render(<ExportButton href="/export/users" />);
    const link = screen.getByRole("link");
    expect(link).toHaveAttribute("href", "/export/users");
  });

  it("constructs correct href with params", () => {
    render(<ExportButton href="/export/users" params={{ search: "john" }} />);
    const link = screen.getByRole("link");
    expect(link).toHaveAttribute("href", "/export/users?search=john");
  });

  it("shows loading state on click", () => {
    vi.useFakeTimers();
    render(<ExportButton href="/export/users" />);

    const link = screen.getByRole("link");

    // Before click, not disabled
    expect(link).not.toHaveAttribute("disabled");

    act(() => {
      fireEvent.click(link);
    });

    // During loading, disabled
    expect(link).toHaveAttribute("disabled");

    act(() => {
      vi.advanceTimersByTime(3000);
    });

    // After timeout, no longer disabled
    expect(link).not.toHaveAttribute("disabled");

    vi.useRealTimers();
  });

  it("renders in disabled state", () => {
    render(<ExportButton href="/export/users" disabled />);
    const link = screen.getByRole("link");
    expect(link).toHaveAttribute("disabled");
  });

  it("has download attribute on link", () => {
    render(<ExportButton href="/export/users" />);
    const link = screen.getByRole("link");
    expect(link).toHaveAttribute("download");
  });
});
