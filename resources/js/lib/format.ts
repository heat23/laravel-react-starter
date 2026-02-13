export function formatFileSize(bytes: number): string {
  if (bytes < 1024) return `${bytes} B`;
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

export function formatRelativeTime(dateString: string | null): string {
  if (!dateString) return "Never";
  const date = new Date(dateString);
  const now = new Date();
  const diffMs = now.getTime() - date.getTime();
  const diffMins = Math.floor(diffMs / 60000);
  if (diffMins < 1) return "just now";
  if (diffMins < 60) return `${diffMins}m ago`;
  const diffHours = Math.floor(diffMins / 60);
  if (diffHours < 24) return `${diffHours}h ago`;
  const diffDays = Math.floor(diffHours / 24);
  if (diffDays < 30) return `${diffDays}d ago`;
  return date.toLocaleDateString();
}

export function formatDate(dateString: string | null, fallback = "N/A"): string {
  if (!dateString) return fallback;
  return new Date(dateString).toLocaleString();
}

export function formatCurrency(value: number, locale = "en-US", currency = "USD"): string {
  return new Intl.NumberFormat(locale, { style: "currency", currency }).format(value);
}

export function capitalize(str: string): string {
  return str.charAt(0).toUpperCase() + str.slice(1);
}

const PROVIDER_NAMES: Record<string, string> = {
  github: "GitHub",
  google: "Google",
  stripe: "Stripe",
};

export function formatProviderName(provider: string): string {
  return PROVIDER_NAMES[provider.toLowerCase()] ?? capitalize(provider);
}
