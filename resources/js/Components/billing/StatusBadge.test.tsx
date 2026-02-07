import { render, screen } from "@testing-library/react";
import { describe, it, expect } from "vitest";

import { StatusBadge } from "./StatusBadge";

describe("StatusBadge", () => {
  it("renders Active badge for active status", () => {
    render(<StatusBadge status="active" />);
    expect(screen.getByText("Active")).toBeInTheDocument();
  });

  it("renders Trial badge for trialing status", () => {
    render(<StatusBadge status="trialing" />);
    expect(screen.getByText("Trial")).toBeInTheDocument();
  });

  it("renders Canceled badge for canceled status", () => {
    render(<StatusBadge status="canceled" />);
    expect(screen.getByText("Canceled")).toBeInTheDocument();
  });

  it("renders Past Due badge for past_due status", () => {
    render(<StatusBadge status="past_due" />);
    expect(screen.getByText("Past Due")).toBeInTheDocument();
  });

  it("renders Unpaid badge for unpaid status", () => {
    render(<StatusBadge status="unpaid" />);
    expect(screen.getByText("Unpaid")).toBeInTheDocument();
  });

  it("renders Incomplete badge for incomplete status", () => {
    render(<StatusBadge status="incomplete" />);
    expect(screen.getByText("Incomplete")).toBeInTheDocument();
  });

  it("renders Expired badge for incomplete_expired status", () => {
    render(<StatusBadge status="incomplete_expired" />);
    expect(screen.getByText("Expired")).toBeInTheDocument();
  });

  it("renders raw status for unknown statuses", () => {
    render(<StatusBadge status="custom_status" />);
    expect(screen.getByText("custom_status")).toBeInTheDocument();
  });

  it("handles case-insensitive status", () => {
    render(<StatusBadge status="ACTIVE" />);
    expect(screen.getByText("Active")).toBeInTheDocument();
  });
});
