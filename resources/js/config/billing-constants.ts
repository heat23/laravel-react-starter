export const SUBSCRIPTION_STATUS_VARIANT: Record<string, "default" | "secondary" | "destructive" | "outline" | "success"> = {
  active: "success",
  trialing: "default",
  past_due: "destructive",
  canceled: "secondary",
  incomplete: "outline",
  incomplete_expired: "outline",
};

export const SUBSCRIPTION_STATUS_COLORS: Record<string, string> = {
  active: "hsl(142 71% 45%)",
  trialing: "hsl(217 91% 60%)",
  past_due: "hsl(38 92% 50%)",
  canceled: "hsl(0 84% 60%)",
  incomplete: "hsl(0 0% 45%)",
  incomplete_expired: "hsl(0 0% 30%)",
};
