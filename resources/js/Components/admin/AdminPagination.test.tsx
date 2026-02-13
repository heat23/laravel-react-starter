import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, it, expect, vi } from "vitest";

import { AdminPagination } from "./AdminPagination";

describe("AdminPagination", () => {
  const defaultProps = {
    currentPage: 1,
    lastPage: 5,
    from: 1,
    to: 25,
    total: 125,
    onPage: vi.fn(),
  };

  it("renders nothing when lastPage is 1", () => {
    const { container } = render(
      <AdminPagination {...defaultProps} lastPage={1} />,
    );
    expect(container.innerHTML).toBe("");
  });

  it("renders showing info with correct range", () => {
    render(<AdminPagination {...defaultProps} />);
    expect(screen.getByText(/Showing 1–25 of 125 items/)).toBeInTheDocument();
  });

  it("uses custom label", () => {
    render(<AdminPagination {...defaultProps} label="users" />);
    expect(screen.getByText(/125 users/)).toBeInTheDocument();
  });

  it("shows all pages when lastPage <= maxVisible", () => {
    render(<AdminPagination {...defaultProps} lastPage={5} />);
    for (let i = 1; i <= 5; i++) {
      expect(screen.getByRole("button", { name: new RegExp(`page ${i}`, "i") })).toBeInTheDocument();
    }
  });

  it("marks current page with aria-current", () => {
    render(<AdminPagination {...defaultProps} currentPage={3} />);
    const btn = screen.getByRole("button", { name: /Page 3, current page/ });
    expect(btn).toHaveAttribute("aria-current", "page");
  });

  it("does not mark non-current pages with aria-current", () => {
    render(<AdminPagination {...defaultProps} currentPage={3} />);
    const btn = screen.getByRole("button", { name: /Go to page 2/ });
    expect(btn).not.toHaveAttribute("aria-current");
  });

  it("calls onPage when a page button is clicked", async () => {
    const user = userEvent.setup();
    const onPage = vi.fn();
    render(<AdminPagination {...defaultProps} onPage={onPage} />);
    await user.click(screen.getByRole("button", { name: /Go to page 3/ }));
    expect(onPage).toHaveBeenCalledWith(3);
  });

  it("shows ellipsis for long page lists", () => {
    render(
      <AdminPagination
        {...defaultProps}
        currentPage={8}
        lastPage={20}
        maxVisible={5}
      />,
    );
    // Should show "..." between first page and window
    expect(screen.getAllByText("...").length).toBeGreaterThanOrEqual(1);
  });

  it("shows first page button when window doesn't start at 1", () => {
    render(
      <AdminPagination
        {...defaultProps}
        currentPage={10}
        lastPage={20}
        maxVisible={5}
      />,
    );
    expect(screen.getByRole("button", { name: "Go to page 1" })).toBeInTheDocument();
  });

  it("shows last page button when window doesn't end at lastPage", () => {
    render(
      <AdminPagination
        {...defaultProps}
        currentPage={5}
        lastPage={20}
        maxVisible={5}
      />,
    );
    expect(screen.getByRole("button", { name: /Go to page 20/ })).toBeInTheDocument();
  });

  it("handles null from/to gracefully", () => {
    render(<AdminPagination {...defaultProps} from={null} to={null} total={0} />);
    expect(screen.getByText(/Showing 0–0 of 0 items/)).toBeInTheDocument();
  });

  it("has pagination nav with aria-label", () => {
    render(<AdminPagination {...defaultProps} />);
    expect(screen.getByRole("navigation", { name: "Pagination" })).toBeInTheDocument();
  });
});
