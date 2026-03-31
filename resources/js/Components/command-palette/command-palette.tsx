import { User } from "lucide-react";

import { useEffect, useState } from "react";

import { router, usePage } from "@inertiajs/react";

import { useTheme } from "@/Components/theme";
import {
  CommandDialog,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
  CommandShortcut,
} from "@/Components/ui/command";
import { getVisibleAdminGroups } from "@/config/admin-navigation";
import { useAnalytics } from "@/hooks/useAnalytics";
import { AnalyticsEvents } from "@/lib/events";
import type { PageProps } from "@/types";

import { buildCommands } from "./command-registry";

interface CommandPaletteProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
}

interface UserSearchResult {
  id: number;
  name: string;
  email: string;
}

export function CommandPalette({ open, onOpenChange }: CommandPaletteProps) {
  const { features, auth } = usePage<PageProps>().props;
  const { resolvedTheme, setTheme } = useTheme();
  const { track } = useAnalytics();
  const isAdmin = auth.user?.is_admin ?? false;

  const [inputValue, setInputValue] = useState("");
  const [userResults, setUserResults] = useState<UserSearchResult[]>([]);

  const close = () => onOpenChange(false);

  const adminNavItems = isAdmin
    ? getVisibleAdminGroups(features).flatMap((group) => group.items)
    : [];

  const commands = buildCommands({
    features,
    resolvedTheme,
    setTheme,
    navigate: (href: string) => router.visit(href),
    close,
    isAdmin,
    adminNavItems,
  });

  const groups = commands.reduce(
    (acc, cmd) => {
      if (!acc[cmd.group]) acc[cmd.group] = [];
      acc[cmd.group].push(cmd);
      return acc;
    },
    {} as Record<string, typeof commands>,
  );

  // Debounced user search (admin only)
  useEffect(() => {
    if (!isAdmin || inputValue.length < 2) {
      setUserResults([]);
      return;
    }

    const timer = setTimeout(async () => {
      try {
        const response = await fetch(
          `/admin/feature-flags/search-users?q=${encodeURIComponent(inputValue)}`,
        );
        if (response.ok) {
          const data: unknown = await response.json();
          setUserResults(Array.isArray(data) ? (data as UserSearchResult[]) : []);
        }
      } catch {
        // Silently fail — command palette search should not break the UI
      }
    }, 300);

    return () => clearTimeout(timer);
  }, [inputValue, isAdmin]);

  // Reset when palette closes
  useEffect(() => {
    if (!open) {
      setInputValue("");
      setUserResults([]);
    }
  }, [open]);

  const handleSelect = (commandId: string) => {
    const command = commands.find((c) => c.id === commandId);
    if (!command) return;

    if (command.id === "action-logout") {
      router.post(
        route("logout"),
        {},
        {
          onBefore: () => {
            track(AnalyticsEvents.AUTH_LOGOUT);
          },
        },
      );
      close();
      return;
    }

    command.action();
  };

  const handleUserSelect = (userId: number) => {
    router.visit(`/admin/users/${userId}`);
    close();
  };

  return (
    <CommandDialog open={open} onOpenChange={onOpenChange}>
      <CommandInput
        placeholder="Type a command or search..."
        value={inputValue}
        onValueChange={setInputValue}
      />
      <CommandList>
        <CommandEmpty>No results found.</CommandEmpty>
        {Object.entries(groups).map(([groupName, groupCommands]) => (
          <CommandGroup key={groupName} heading={groupName}>
            {groupCommands.map((cmd) => (
              <CommandItem
                key={cmd.id}
                value={cmd.id}
                keywords={[cmd.label]}
                onSelect={handleSelect}
              >
                <cmd.icon className="mr-2 h-4 w-4" />
                <span>{cmd.label}</span>
                {cmd.shortcut && (
                  <CommandShortcut>{cmd.shortcut}</CommandShortcut>
                )}
              </CommandItem>
            ))}
          </CommandGroup>
        ))}
        {isAdmin && userResults.length > 0 && (
          <CommandGroup heading="Users">
            {userResults.map((user) => (
              <CommandItem
                key={`user-${user.id}`}
                value={`user-${user.id}`}
                keywords={[user.name, user.email]}
                onSelect={() => handleUserSelect(user.id)}
              >
                <User className="mr-2 h-4 w-4" />
                <span>{user.name}</span>
                <span className="ml-2 text-xs text-muted-foreground">
                  {user.email}
                </span>
              </CommandItem>
            ))}
          </CommandGroup>
        )}
      </CommandList>
    </CommandDialog>
  );
}
