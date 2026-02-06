import { AlertTriangle, Bell, CheckCircle, Info, XCircle } from "lucide-react";

import { memo } from "react";

import { router } from "@inertiajs/react";

import { cn } from "@/lib/utils";
import { type AppNotification } from "@/types";

const iconMap: Record<string, React.ComponentType<{ className?: string }>> = {
  info: Info,
  success: CheckCircle,
  warning: AlertTriangle,
  error: XCircle,
};

function formatRelativeTime(dateString: string): string {
  const now = new Date();
  const date = new Date(dateString);
  const diffMs = now.getTime() - date.getTime();
  const diffMins = Math.floor(diffMs / 60000);

  if (diffMins < 1) return "Just now";
  if (diffMins < 60) return `${diffMins}m ago`;

  const diffHours = Math.floor(diffMins / 60);
  if (diffHours < 24) return `${diffHours}h ago`;

  const diffDays = Math.floor(diffHours / 24);
  if (diffDays < 7) return `${diffDays}d ago`;

  return date.toLocaleDateString();
}

interface NotificationItemProps {
  notification: AppNotification;
  onMarkAsRead: (id: string) => void;
}

export const NotificationItem = memo(function NotificationItem({
  notification,
  onMarkAsRead,
}: NotificationItemProps) {
  const Icon = iconMap[notification.data.icon ?? ""] ?? Bell;
  const isUnread = !notification.read_at;
  const timeText = formatRelativeTime(notification.created_at);

  function handleClick() {
    if (isUnread) {
      onMarkAsRead(notification.id);
    }

    if (notification.data.action_url) {
      router.visit(notification.data.action_url);
    }
  }

  return (
    <div role="listitem">
      <button
        type="button"
        onClick={handleClick}
        aria-label={`${notification.data.title}. ${notification.data.message}. ${timeText}${isUnread ? ". Unread" : ""}`}
        className={cn(
          "flex w-full items-start gap-3 rounded-md p-3 text-left transition-colors hover:bg-accent",
          isUnread && "bg-accent/50",
        )}
      >
        <div className="mt-0.5 shrink-0">
          <Icon className="h-4 w-4 text-muted-foreground" aria-hidden="true" />
        </div>
        <div className="min-w-0 flex-1">
          <div className="flex items-center gap-2">
            <p
              className={cn(
                "truncate text-sm",
                isUnread ? "font-medium" : "text-muted-foreground",
              )}
            >
              {notification.data.title}
            </p>
            {isUnread && (
              <span
                aria-hidden="true"
                className="h-2 w-2 shrink-0 rounded-full bg-primary"
              />
            )}
          </div>
          <p className="mt-0.5 line-clamp-2 text-xs text-muted-foreground">
            {notification.data.message}
          </p>
          <p className="mt-1 text-xs text-muted-foreground/70">{timeText}</p>
        </div>
      </button>
    </div>
  );
});
