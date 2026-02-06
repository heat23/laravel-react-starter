import { ColumnDef } from "@tanstack/react-table";
import { render, screen, within } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { afterEach, beforeAll, beforeEach, describe, it, expect, vi } from "vitest";

import { DataTable } from "./data-table";

interface TestItem {
  id: number;
  name: string;
  email: string;
}

const columns: ColumnDef<TestItem, unknown>[] = [
  { accessorKey: "name", header: "Name" },
  { accessorKey: "email", header: "Email" },
];

const testData: TestItem[] = [
  { id: 1, name: "Alice", email: "alice@example.com" },
  { id: 2, name: "Bob", email: "bob@example.com" },
  { id: 3, name: "Charlie", email: "charlie@example.com" },
];

// Mock ResizeObserver for Radix UI components
beforeAll(() => {
  global.ResizeObserver = vi.fn().mockImplementation(() => ({
    observe: vi.fn(),
    unobserve: vi.fn(),
    disconnect: vi.fn(),
  }));
});

describe("DataTable", () => {
  it("renders with columns and data", () => {
    render(<DataTable columns={columns} data={testData} />);

    expect(screen.getByText("Alice")).toBeInTheDocument();
    expect(screen.getByText("Bob")).toBeInTheDocument();
    expect(screen.getByText("Charlie")).toBeInTheDocument();
    expect(screen.getByText("alice@example.com")).toBeInTheDocument();
  });

  it("shows empty state when data is empty", () => {
    render(
      <DataTable
        columns={columns}
        data={[]}
        emptyState={{
          title: "No users found",
          description: "Try adjusting your search.",
        }}
      />
    );

    expect(screen.getByText("No users found")).toBeInTheDocument();
    expect(screen.getByText("Try adjusting your search.")).toBeInTheDocument();
  });

  it("shows default no results message when no emptyState provided", () => {
    render(<DataTable columns={columns} data={[]} />);

    expect(screen.getByText("No results.")).toBeInTheDocument();
  });

  it("renders selection checkboxes when enableSelection is true", () => {
    render(<DataTable columns={columns} data={testData} enableSelection />);

    const checkboxes = screen.getAllByRole("checkbox");
    // 1 header "select all" + 3 row checkboxes
    expect(checkboxes.length).toBe(4);
  });

  it("selects a row when checkbox is clicked", async () => {
    const user = userEvent.setup();
    render(<DataTable columns={columns} data={testData} enableSelection />);

    const checkboxes = screen.getAllByRole("checkbox");
    // Click the first row checkbox (index 1, since index 0 is "select all")
    await user.click(checkboxes[1]);

    expect(screen.getByText("1 of 3 row(s) selected.")).toBeInTheDocument();
  });

  it("selects all rows when header checkbox is clicked", async () => {
    const user = userEvent.setup();
    render(<DataTable columns={columns} data={testData} enableSelection />);

    const selectAllCheckbox = screen.getAllByRole("checkbox")[0];
    await user.click(selectAllCheckbox);

    expect(screen.getByText("3 of 3 row(s) selected.")).toBeInTheDocument();
  });

  it("paginates data correctly", () => {
    const largeData = Array.from({ length: 25 }, (_, i) => ({
      id: i + 1,
      name: `User ${i + 1}`,
      email: `user${i + 1}@example.com`,
    }));

    render(<DataTable columns={columns} data={largeData} pageSize={10} />);

    // First page shows 10 rows
    const table = screen.getByRole("table");
    const rows = within(table).getAllByRole("row");
    // 1 header row + 10 data rows
    expect(rows.length).toBe(11);

    expect(screen.getByText("Page 1 of 3")).toBeInTheDocument();
  });

  it("navigates to next page", async () => {
    const user = userEvent.setup();
    const largeData = Array.from({ length: 25 }, (_, i) => ({
      id: i + 1,
      name: `User ${i + 1}`,
      email: `user${i + 1}@example.com`,
    }));

    render(<DataTable columns={columns} data={largeData} pageSize={10} />);

    const nextButton = screen.getByRole("button", { name: "Go to next page" });
    await user.click(nextButton);

    expect(screen.getByText("Page 2 of 3")).toBeInTheDocument();
  });

  it("shows column visibility toggle", () => {
    render(<DataTable columns={columns} data={testData} />);

    expect(screen.getByRole("button", { name: "Toggle column visibility" })).toBeInTheDocument();
  });

  it("renders search input when searchKey is provided", () => {
    render(<DataTable columns={columns} data={testData} searchKey="name" />);

    expect(screen.getByPlaceholderText("Filter name...")).toBeInTheDocument();
  });

  it("renders custom search placeholder", () => {
    render(
      <DataTable
        columns={columns}
        data={testData}
        searchKey="name"
        searchPlaceholder="Search users..."
      />
    );

    expect(screen.getByPlaceholderText("Search users...")).toBeInTheDocument();
  });

  it("filters data when search input is used", async () => {
    const user = userEvent.setup();
    render(<DataTable columns={columns} data={testData} searchKey="name" />);

    const searchInput = screen.getByPlaceholderText("Filter name...");
    await user.type(searchInput, "Alice");

    expect(screen.getByText("Alice")).toBeInTheDocument();
    expect(screen.queryByText("Bob")).not.toBeInTheDocument();
    expect(screen.queryByText("Charlie")).not.toBeInTheDocument();
  });

  it("shows bulk actions bar when rows are selected", async () => {
    const user = userEvent.setup();
    const bulkActions = vi.fn().mockReturnValue(<button>Delete Selected</button>);

    render(
      <DataTable
        columns={columns}
        data={testData}
        enableSelection
        bulkActions={bulkActions}
      />
    );

    const checkboxes = screen.getAllByRole("checkbox");
    await user.click(checkboxes[1]);

    expect(screen.getByText("1 row(s) selected")).toBeInTheDocument();
    expect(screen.getByText("Delete Selected")).toBeInTheDocument();
  });

  describe("persistState", () => {
    let replaceStateSpy: ReturnType<typeof vi.spyOn>;

    beforeEach(() => {
      replaceStateSpy = vi.spyOn(history, "replaceState");
      // Reset URL to clean state
      history.replaceState(null, "", "/");
    });

    afterEach(() => {
      replaceStateSpy.mockRestore();
      history.replaceState(null, "", "/");
    });

    it("syncs pagination to URL params when persistState is enabled", async () => {
      const user = userEvent.setup();
      const largeData = Array.from({ length: 25 }, (_, i) => ({
        id: i + 1,
        name: `User ${i + 1}`,
        email: `user${i + 1}@example.com`,
      }));

      render(<DataTable columns={columns} data={largeData} pageSize={10} persistState />);

      const nextButton = screen.getByRole("button", { name: "Go to next page" });
      await user.click(nextButton);

      expect(replaceStateSpy).toHaveBeenCalledWith(
        null,
        "",
        expect.stringContaining("page=2"),
      );
    });

    it("initializes state from URL params", () => {
      // jsdom doesn't update window.location via history.replaceState,
      // so we mock the search property directly
      const originalSearch = window.location.search;
      Object.defineProperty(window, "location", {
        writable: true,
        value: { ...window.location, search: "?page=2", pathname: "/" },
      });

      const largeData = Array.from({ length: 25 }, (_, i) => ({
        id: i + 1,
        name: `User ${i + 1}`,
        email: `user${i + 1}@example.com`,
      }));

      render(<DataTable columns={columns} data={largeData} pageSize={10} persistState />);

      expect(screen.getByText("Page 2 of 3")).toBeInTheDocument();

      // Restore
      Object.defineProperty(window, "location", {
        writable: true,
        value: { ...window.location, search: originalSearch, pathname: "/" },
      });
    });

    it("does not modify URL when persistState is disabled", async () => {
      const user = userEvent.setup();
      const largeData = Array.from({ length: 25 }, (_, i) => ({
        id: i + 1,
        name: `User ${i + 1}`,
        email: `user${i + 1}@example.com`,
      }));

      render(<DataTable columns={columns} data={largeData} pageSize={10} />);

      const nextButton = screen.getByRole("button", { name: "Go to next page" });
      await user.click(nextButton);

      // replaceState should not have been called with page params
      const pageCallArgs = replaceStateSpy.mock.calls.filter(
        (call) => typeof call[2] === "string" && call[2].includes("page="),
      );
      expect(pageCallArgs).toHaveLength(0);
    });

    it("initializes sorting state from URL params", () => {
      const sortState = JSON.stringify([{ id: "name", desc: true }]);
      Object.defineProperty(window, "location", {
        writable: true,
        value: { ...window.location, search: `?sort=${encodeURIComponent(sortState)}`, pathname: "/" },
      });

      render(<DataTable columns={columns} data={testData} persistState />);

      // Sort state is applied — descending by name means Charlie first
      const table = screen.getByRole("table");
      const rows = within(table).getAllByRole("row");
      // First data row (index 1, after header) should be Charlie
      expect(rows[1]).toHaveTextContent("Charlie");

      Object.defineProperty(window, "location", {
        writable: true,
        value: { ...window.location, search: "", pathname: "/" },
      });
    });

    it("initializes filter state from URL params", () => {
      const filterState = JSON.stringify([{ id: "name", value: "Alice" }]);
      Object.defineProperty(window, "location", {
        writable: true,
        value: { ...window.location, search: `?filter=${encodeURIComponent(filterState)}`, pathname: "/" },
      });

      render(<DataTable columns={columns} data={testData} persistState />);

      // Only Alice should be visible
      expect(screen.getByText("Alice")).toBeInTheDocument();
      expect(screen.queryByText("Bob")).not.toBeInTheDocument();

      Object.defineProperty(window, "location", {
        writable: true,
        value: { ...window.location, search: "", pathname: "/" },
      });
    });

    it("handles malformed JSON in URL params gracefully", () => {
      Object.defineProperty(window, "location", {
        writable: true,
        value: { ...window.location, search: "?sort=invalid-json&filter={bad&page=abc", pathname: "/" },
      });

      // Should not throw — falls back to defaults
      render(<DataTable columns={columns} data={testData} persistState />);

      expect(screen.getByText("Alice")).toBeInTheDocument();
      expect(screen.getByText("Page 1 of 1")).toBeInTheDocument();

      Object.defineProperty(window, "location", {
        writable: true,
        value: { ...window.location, search: "", pathname: "/" },
      });
    });

    it("ignores non-array parsed JSON in URL params", () => {
      // JSON.parse succeeds but result is not an array
      const notArray = JSON.stringify({ id: "name", desc: true });
      Object.defineProperty(window, "location", {
        writable: true,
        value: { ...window.location, search: `?sort=${encodeURIComponent(notArray)}`, pathname: "/" },
      });

      render(<DataTable columns={columns} data={testData} persistState />);

      // Falls back to default (no sorting) — Alice first alphabetically by insertion order
      expect(screen.getByText("Alice")).toBeInTheDocument();

      Object.defineProperty(window, "location", {
        writable: true,
        value: { ...window.location, search: "", pathname: "/" },
      });
    });
  });
});
