import { Badge } from "@/Components/ui/badge";

type BadgeVariant = "default" | "secondary" | "destructive" | "outline" | "success";

interface StatusBadgeProps {
  status: string;
}

export function StatusBadge({ status }: StatusBadgeProps) {
  const getVariant = (status: string): BadgeVariant => {
    switch (status.toLowerCase()) {
      case "active":
        return "success";
      case "trialing":
        return "secondary";
      case "canceled":
      case "past_due":
      case "unpaid":
      case "incomplete":
      case "incomplete_expired":
        return "destructive";
      default:
        return "outline";
    }
  };

  const getLabel = (status: string): string => {
    switch (status.toLowerCase()) {
      case "active":
        return "Active";
      case "trialing":
        return "Trial";
      case "canceled":
        return "Canceled";
      case "past_due":
        return "Past Due";
      case "unpaid":
        return "Unpaid";
      case "incomplete":
        return "Incomplete";
      case "incomplete_expired":
        return "Expired";
      default:
        return status;
    }
  };

  return <Badge variant={getVariant(status)}>{getLabel(status)}</Badge>;
}
