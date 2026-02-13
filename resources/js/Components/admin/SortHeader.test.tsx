import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, it, expect, vi } from "vitest";

import { SortHeader } from "./SortHeader";

// SortHeader renders a <th> which needs a parent table structure
function renderSortHeader(props: React.ComponentProps<typeof SortHeader>) {
  return render(
    <table>
      <thead>
        <tr>
          <SortHeader {...props} />
        </tr>
      </thead>
    </table>,
  );
}

describe("SortHeader", () => {
  const defaultProps = {
    column: "name",
    label: "Name",
    onSort: vi.fn(),
  };

  it("renders the label text", () => {
    renderSortHeader(defaultProps);
    expect(screen.getByText("Name")).toBeInTheDocument();
  });

  it("has role columnheader", () => {
    renderSortHeader(defaultProps);
    expect(screen.getByRole("columnheader")).toBeInTheDocument();
  });

  it("is focusable via tabIndex", () => {
    renderSortHeader(defaultProps);
    const th = screen.getByRole("columnheader");
    expect(th).toHaveAttribute("tabindex", "0");
  });

  it("calls onSort when clicked", async () => {
    const user = userEvent.setup();
    const onSort = vi.fn();
    renderSortHeader({ ...defaultProps, onSort });
    await user.click(screen.getByRole("columnheader"));
    expect(onSort).toHaveBeenCalledWith("name");
  });

  it("calls onSort when Enter is pressed", async () => {
    const user = userEvent.setup();
    const onSort = vi.fn();
    renderSortHeader({ ...defaultProps, onSort });
    screen.getByRole("columnheader").focus();
    await user.keyboard("{Enter}");
    expect(onSort).toHaveBeenCalledWith("name");
  });

  it("calls onSort when Space is pressed", async () => {
    const user = userEvent.setup();
    const onSort = vi.fn();
    renderSortHeader({ ...defaultProps, onSort });
    screen.getByRole("columnheader").focus();
    await user.keyboard(" ");
    expect(onSort).toHaveBeenCalledWith("name");
  });

  it("shows aria-sort none when not active", () => {
    renderSortHeader({ ...defaultProps, currentSort: "email", currentDir: "asc" });
    expect(screen.getByRole("columnheader")).toHaveAttribute("aria-sort", "none");
  });

  it("shows aria-sort ascending when active and asc", () => {
    renderSortHeader({ ...defaultProps, currentSort: "name", currentDir: "asc" });
    expect(screen.getByRole("columnheader")).toHaveAttribute("aria-sort", "ascending");
  });

  it("shows aria-sort descending when active and desc", () => {
    renderSortHeader({ ...defaultProps, currentSort: "name", currentDir: "desc" });
    expect(screen.getByRole("columnheader")).toHaveAttribute("aria-sort", "descending");
  });

  it("shows up arrow when sorted ascending", () => {
    renderSortHeader({ ...defaultProps, currentSort: "name", currentDir: "asc" });
    expect(screen.getByText("↑")).toBeInTheDocument();
  });

  it("shows down arrow when sorted descending", () => {
    renderSortHeader({ ...defaultProps, currentSort: "name", currentDir: "desc" });
    expect(screen.getByText("↓")).toBeInTheDocument();
  });

  it("shows no arrow when not active", () => {
    renderSortHeader({ ...defaultProps, currentSort: "email" });
    expect(screen.queryByText("↑")).not.toBeInTheDocument();
    expect(screen.queryByText("↓")).not.toBeInTheDocument();
  });
});
