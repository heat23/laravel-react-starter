import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";

import { LoadingButton } from "./loading-button";

describe("LoadingButton", () => {
  it("sets aria-busy when loading", () => {
    render(<LoadingButton loading>Submit</LoadingButton>);

    const button = screen.getByRole("button");
    expect(button).toHaveAttribute("aria-busy", "true");
  });

  it("does not set aria-busy when not loading", () => {
    render(<LoadingButton>Submit</LoadingButton>);

    const button = screen.getByRole("button");
    expect(button).not.toHaveAttribute("aria-busy", "true");
  });

  it("shows loading text when loading", () => {
    render(
      <LoadingButton loading loadingText="Saving...">
        Submit
      </LoadingButton>,
    );

    expect(screen.getByRole("button")).toHaveTextContent("Saving...");
  });

  it("shows children when not loading", () => {
    render(<LoadingButton>Submit</LoadingButton>);

    expect(screen.getByRole("button")).toHaveTextContent("Submit");
  });

  it("is disabled when loading", () => {
    render(<LoadingButton loading>Submit</LoadingButton>);

    expect(screen.getByRole("button")).toBeDisabled();
  });
});
