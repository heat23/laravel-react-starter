import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";

import { FilePreview } from "./FilePreview";

function createFile(
  name: string,
  size: number,
  type: string,
): File {
  const content = new Array(size).fill("a").join("");
  return new File([content], name, { type });
}

// Mock URL.createObjectURL / revokeObjectURL for image preview tests
const mockCreateObjectURL = vi.fn(() => "blob:mock-url");
const mockRevokeObjectURL = vi.fn();
Object.defineProperty(globalThis.URL, "createObjectURL", { value: mockCreateObjectURL });
Object.defineProperty(globalThis.URL, "revokeObjectURL", { value: mockRevokeObjectURL });

describe("FilePreview", () => {
  it("displays file name", () => {
    const file = createFile("report.pdf", 2048, "application/pdf");
    render(<FilePreview file={file} onRemove={vi.fn()} />);

    expect(screen.getByText("report.pdf")).toBeInTheDocument();
  });

  it("displays formatted file size", () => {
    const file = createFile("report.pdf", 2048, "application/pdf");
    render(<FilePreview file={file} onRemove={vi.fn()} />);

    expect(screen.getByText("2.0 KB")).toBeInTheDocument();
  });

  it("shows image preview for image files", () => {
    const file = createFile("photo.png", 1024, "image/png");
    render(<FilePreview file={file} onRemove={vi.fn()} />);

    const img = screen.getByAltText("photo.png");
    expect(img).toBeInTheDocument();
    expect(img).toHaveAttribute("src", "blob:mock-url");
  });

  it("shows file icon for non-image files", () => {
    const file = createFile("doc.pdf", 1024, "application/pdf");
    render(<FilePreview file={file} onRemove={vi.fn()} />);

    expect(screen.queryByRole("img")).not.toBeInTheDocument();
  });

  it("calls onRemove when remove button clicked", async () => {
    const onRemove = vi.fn();
    const file = createFile("test.txt", 512, "text/plain");
    render(<FilePreview file={file} onRemove={onRemove} />);

    const removeButton = screen.getByLabelText("Remove test.txt");
    await userEvent.click(removeButton);

    expect(onRemove).toHaveBeenCalledTimes(1);
  });

  it("has accessible remove button with file name", () => {
    const file = createFile("data.csv", 4096, "text/csv");
    render(<FilePreview file={file} onRemove={vi.fn()} />);

    expect(screen.getByLabelText("Remove data.csv")).toBeInTheDocument();
  });

  it("formats bytes correctly", () => {
    const file = createFile("tiny.txt", 500, "text/plain");
    render(<FilePreview file={file} onRemove={vi.fn()} />);

    expect(screen.getByText("500 B")).toBeInTheDocument();
  });

  it("formats megabytes correctly", () => {
    const file = createFile("big.zip", 1024 * 1024 * 2.5, "application/zip");
    render(<FilePreview file={file} onRemove={vi.fn()} />);

    expect(screen.getByText("2.5 MB")).toBeInTheDocument();
  });
});
