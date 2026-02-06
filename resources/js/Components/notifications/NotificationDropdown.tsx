import axios from "axios";
import { Bell, BellOff, RefreshCw } from "lucide-react";
import { toast } from "sonner";

import { memo, useCallback, useRef, useState } from "react";

import { router, usePage } from "@inertiajs/react";

import { Button } from "@/Components/ui/button";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/Components/ui/popover";
import { type AppNotification, type PageProps } from "@/types";

import { NotificationItem } from "./NotificationItem";

const REFETCH_INTERVAL_MS = 5000;

export const NotificationDropdown = memo(function NotificationDropdown() {
  const { notifications_unread_count: unreadCount } =
    usePage<PageProps>().props;
  const [notifications, setNotifications] = useState<AppNotification[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(false);
  const [open, setOpen] = useState(false);
  const lastFetchRef = useRef(0);

  const fetchNotifications = useCallback(async () => {
    if (Date.now() - lastFetchRef.current < REFETCH_INTERVAL_MS) return;

    setLoading(true);
    setError(false);
    try {
      const response = await axios.get<{ data: AppNotification[] }>(
        "/api/notifications",
      );
      setNotifications(response.data.data);
      lastFetchRef.current = Date.now();
    } catch {
      setError(true);
    } finally {
      setLoading(false);
    }
  }, []);

  function handleOpenChange(isOpen: boolean) {
    setOpen(isOpen);
    if (isOpen) {
      fetchNotifications();
    }
  }

  async function handleMarkAsRead(id: string) {
    const prev = notifications;
    setNotifications((current) =>
      current.map((n) =>
        n.id === id ? { ...n, read_at: new Date().toISOString() } : n,
      ),
    );
    try {
      await axios.patch(`/api/notifications/${id}/read`);
      router.reload({ only: ["notifications_unread_count"] });
    } catch {
      setNotifications(prev);
      toast.error("Failed to mark notification as read");
    }
  }

  async function handleMarkAllAsRead() {
    const prev = notifications;
    setNotifications((current) =>
      current.map((n) => ({
        ...n,
        read_at: n.read_at ?? new Date().toISOString(),
      })),
    );
    try {
      await axios.post("/api/notifications/read-all");
      router.reload({ only: ["notifications_unread_count"] });
    } catch {
      setNotifications(prev);
      toast.error("Failed to mark all as read");
    }
  }

  const buttonLabel =
    unreadCount > 0
      ? `Notifications, ${unreadCount} unread`
      : "Notifications";

  return (
    <Popover open={open} onOpenChange={handleOpenChange}>
      <PopoverTrigger asChild>
        <Button
          variant="ghost"
          size="icon"
          className="relative"
          aria-label={buttonLabel}
        >
          <Bell className="h-5 w-5" />
          {unreadCount > 0 && (
            <span
              aria-hidden="true"
              className="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-destructive px-1 text-[10px] font-medium text-destructive-foreground"
            >
              {unreadCount > 99 ? "99+" : unreadCount}
            </span>
          )}
        </Button>
      </PopoverTrigger>
      <PopoverContent className="w-80 p-0" align="end">
        <div className="flex items-center justify-between border-b px-4 py-3">
          <h3 className="text-sm font-semibold">Notifications</h3>
          {notifications.some((n) => !n.read_at) && (
            <Button
              variant="ghost"
              size="sm"
              className="h-auto px-2 py-1 text-xs"
              onClick={handleMarkAllAsRead}
            >
              Mark all as read
            </Button>
          )}
        </div>
        <div className="max-h-80 overflow-y-auto">
          {loading && notifications.length === 0 ? (
            <div
              role="status"
              aria-live="polite"
              className="flex items-center justify-center py-8"
            >
              <p className="text-sm text-muted-foreground">Loading...</p>
            </div>
          ) : error ? (
            <div className="flex flex-col items-center justify-center gap-2 py-8">
              <p className="text-sm text-muted-foreground">
                Failed to load notifications
              </p>
              <Button
                variant="ghost"
                size="sm"
                className="h-auto gap-1.5 px-2 py-1 text-xs"
                onClick={() => {
                  lastFetchRef.current = 0;
                  fetchNotifications();
                }}
              >
                <RefreshCw className="h-3 w-3" />
                Retry
              </Button>
            </div>
          ) : notifications.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-8">
              <BellOff className="mb-2 h-8 w-8 text-muted-foreground/50" />
              <p className="text-sm text-muted-foreground">
                No notifications yet
              </p>
            </div>
          ) : (
            <div role="list" className="divide-y">
              {notifications.map((notification) => (
                <NotificationItem
                  key={notification.id}
                  notification={notification}
                  onMarkAsRead={handleMarkAsRead}
                />
              ))}
            </div>
          )}
        </div>
      </PopoverContent>
    </Popover>
  );
});
