import { fireEvent, render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { toast } from "sonner";
import { describe, expect, it, vi } from "vitest";

vi.mock("sonner", () => ({
  toast: { success: vi.fn(), error: vi.fn() },
}));

import { FileUpload } from "./FileUpload";

function createFile(
  name: string,
  size: number,
  type: string,
): File {
  const content = new Array(size).fill("a").join("");
  return new File([content], name, { type });
}

describe("FileUpload", () => {
  it("renders dropzone with default text", () => {
    render(<FileUpload onFilesSelected={vi.fn()} />);
    expect(
      screen.getByText("Drag & drop or click to upload"),
    ).toBeInTheDocument();
  });

  it("accepts files via click", async () => {
    const onFilesSelected = vi.fn();
    render(<FileUpload onFilesSelected={onFilesSelected} />);

    const input = document.querySelector(
      'input[type="file"]',
    ) as HTMLInputElement;
    const file = createFile("test.png", 1024, "image/png");

    await userEvent.upload(input, file);

    expect(onFilesSelected).toHaveBeenCalledWith([file]);
  });

  it("shows file name after selection", async () => {
    render(<FileUpload onFilesSelected={vi.fn()} />);

    const input = document.querySelector(
      'input[type="file"]',
    ) as HTMLInputElement;
    const file = createFile("document.pdf", 2048, "application/pdf");

    await userEvent.upload(input, file);

    expect(screen.getByText("document.pdf")).toBeInTheDocument();
  });

  it("validates file size", async () => {
    const maxSize = 1024; // 1KB
    render(
      <FileUpload onFilesSelected={vi.fn()} maxSize={maxSize} />,
    );

    const input = document.querySelector(
      'input[type="file"]',
    ) as HTMLInputElement;
    const file = createFile("large.png", 2048, "image/png");

    await userEvent.upload(input, file);

    expect(screen.getByRole("alert")).toBeInTheDocument();
    expect(screen.getByText(/too large/i)).toBeInTheDocument();
  });

  it("validates file type via drag and drop", () => {
    render(
      <FileUpload onFilesSelected={vi.fn()} accept="image/*" />,
    );

    const dropzone = screen.getByRole("button", { name: "Upload files" });
    const file = createFile("doc.pdf", 1024, "application/pdf");

    fireEvent.drop(dropzone, {
      dataTransfer: {
        files: [file],
      },
    });

    expect(screen.getByRole("alert")).toBeInTheDocument();
    expect(screen.getByText(/not an accepted file type/i)).toBeInTheDocument();
  });

  it("removes files when remove button clicked", async () => {
    const onFileRemoved = vi.fn();
    render(
      <FileUpload
        onFilesSelected={vi.fn()}
        onFileRemoved={onFileRemoved}
      />,
    );

    const input = document.querySelector(
      'input[type="file"]',
    ) as HTMLInputElement;
    const file = createFile("test.png", 1024, "image/png");

    await userEvent.upload(input, file);
    expect(screen.getByText("test.png")).toBeInTheDocument();

    const removeButton = screen.getByLabelText("Remove test.png");
    await userEvent.click(removeButton);

    expect(screen.queryByText("test.png")).not.toBeInTheDocument();
    expect(onFileRemoved).toHaveBeenCalledWith(file);
  });

  it("shows error for invalid files via drag and drop", () => {
    render(
      <FileUpload
        onFilesSelected={vi.fn()}
        accept=".jpg,.png"
        maxSize={500}
      />,
    );

    const dropzone = screen.getByRole("button", { name: "Upload files" });
    const file = createFile("bad.exe", 1024, "application/x-msdownload");

    fireEvent.drop(dropzone, {
      dataTransfer: {
        files: [file],
      },
    });

    expect(screen.getByRole("alert")).toBeInTheDocument();
  });

  it("handles drag over visual feedback", () => {
    render(<FileUpload onFilesSelected={vi.fn()} />);

    const dropzone = screen.getByRole("button", { name: "Upload files" });

    fireEvent.dragOver(dropzone, {
      dataTransfer: { files: [] },
    });

    expect(dropzone.className).toContain("border-primary");
  });

  it("is disabled when disabled prop is true", () => {
    render(<FileUpload onFilesSelected={vi.fn()} disabled />);

    const dropzone = screen.getByRole("button", { name: "Upload files" });
    expect(dropzone.className).toContain("opacity-50");
  });

  it("shows toast on successful file upload", async () => {
    render(<FileUpload onFilesSelected={vi.fn()} />);

    const input = document.querySelector(
      'input[type="file"]',
    ) as HTMLInputElement;
    const file = createFile("test.png", 1024, "image/png");

    await userEvent.upload(input, file);

    expect(toast.success).toHaveBeenCalledWith("1 file added");
  });

  it("does not show toast when file validation fails", async () => {
    vi.mocked(toast.success).mockClear();
    render(<FileUpload onFilesSelected={vi.fn()} maxSize={500} />);

    const input = document.querySelector(
      'input[type="file"]',
    ) as HTMLInputElement;
    const file = createFile("large.png", 2048, "image/png");

    await userEvent.upload(input, file);

    expect(toast.success).not.toHaveBeenCalled();
  });

  it("shows progress bar when progress prop is provided", () => {
    render(<FileUpload onFilesSelected={vi.fn()} progress={45} />);

    const progressBar = screen.getByRole("progressbar", { name: "Upload progress" });
    expect(progressBar).toBeInTheDocument();
    expect(progressBar).toHaveAttribute("value", "45");
    expect(screen.getByText("45%")).toBeInTheDocument();
    expect(screen.getByText("Uploading...")).toBeInTheDocument();
  });

  it("hides progress bar when progress is 100", () => {
    render(<FileUpload onFilesSelected={vi.fn()} progress={100} />);

    expect(screen.queryByRole("progressbar")).not.toBeInTheDocument();
  });

  it("shows progress bar at 0%", () => {
    render(<FileUpload onFilesSelected={vi.fn()} progress={0} />);

    const progressBar = screen.getByRole("progressbar", { name: "Upload progress" });
    expect(progressBar).toBeInTheDocument();
    expect(progressBar).toHaveAttribute("value", "0");
    expect(screen.getByText("0%")).toBeInTheDocument();
  });

  it("hides progress bar when progress is null", () => {
    render(<FileUpload onFilesSelected={vi.fn()} progress={null} />);

    expect(screen.queryByRole("progressbar")).not.toBeInTheDocument();
  });

  it("accepts multiple files when multiple is true", async () => {
    const onFilesSelected = vi.fn();
    render(
      <FileUpload
        onFilesSelected={onFilesSelected}
        multiple
        maxFiles={3}
      />,
    );

    const input = document.querySelector(
      'input[type="file"]',
    ) as HTMLInputElement;
    const file1 = createFile("a.png", 100, "image/png");
    const file2 = createFile("b.png", 200, "image/png");

    await userEvent.upload(input, [file1, file2]);

    expect(onFilesSelected).toHaveBeenCalledWith([file1, file2]);
    expect(screen.getByText("a.png")).toBeInTheDocument();
    expect(screen.getByText("b.png")).toBeInTheDocument();
  });

  it("enforces maxFiles limit", async () => {
    const onFilesSelected = vi.fn();
    render(
      <FileUpload
        onFilesSelected={onFilesSelected}
        multiple
        maxFiles={2}
      />,
    );

    const input = document.querySelector(
      'input[type="file"]',
    ) as HTMLInputElement;
    const file1 = createFile("a.png", 100, "image/png");
    const file2 = createFile("b.png", 200, "image/png");
    const file3 = createFile("c.png", 300, "image/png");

    await userEvent.upload(input, [file1, file2, file3]);

    expect(screen.getByRole("alert")).toBeInTheDocument();
    expect(screen.getByText(/maximum 2 files allowed/i)).toBeInTheDocument();
    expect(onFilesSelected).toHaveBeenCalledWith([file1, file2]);
  });

  it("shows plural toast for multiple files", async () => {
    vi.mocked(toast.success).mockClear();
    render(
      <FileUpload onFilesSelected={vi.fn()} multiple maxFiles={5} />,
    );

    const input = document.querySelector(
      'input[type="file"]',
    ) as HTMLInputElement;
    const file1 = createFile("a.png", 100, "image/png");
    const file2 = createFile("b.png", 200, "image/png");

    await userEvent.upload(input, [file1, file2]);

    expect(toast.success).toHaveBeenCalledWith("2 files added");
  });

  it("has aria-disabled attribute when disabled", () => {
    render(<FileUpload onFilesSelected={vi.fn()} disabled />);

    const dropzone = screen.getByRole("button", { name: "Upload files" });
    expect(dropzone).toHaveAttribute("aria-disabled", "true");
  });
});
