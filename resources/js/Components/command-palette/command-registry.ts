import { Clipboard, LogOut, Monitor, Moon, Sun } from "lucide-react";
import type { LucideIcon } from "lucide-react";

import { getVisibleNavItems } from "@/config/navigation";

export interface CommandItem {
  id: string;
  label: string;
  icon: LucideIcon;
  group: string;
  shortcut?: string;
  action: () => void;
}

interface BuildCommandsOptions {
  features: Record<string, boolean>;
  resolvedTheme: "light" | "dark";
  setTheme: (theme: "light" | "dark" | "system") => void;
  navigate: (href: string) => void;
  close: () => void;
}

export function buildCommands({
  features,
  resolvedTheme: _resolvedTheme,
  setTheme,
  navigate,
  close,
}: BuildCommandsOptions): CommandItem[] {
  const commands: CommandItem[] = [];

  // Navigation commands from shared config
  const navItems = getVisibleNavItems(features);
  for (const item of navItems) {
    commands.push({
      id: `nav-${item.href}`,
      label: item.label,
      icon: item.icon,
      group: "Navigation",
      shortcut: item.shortcut,
      action: () => {
        navigate(item.href);
        close();
      },
    });
  }

  // Action commands
  commands.push({
    id: "action-copy-url",
    label: "Copy Current URL",
    icon: Clipboard,
    group: "Actions",
    action: () => {
      navigator.clipboard.writeText(window.location.href);
      close();
    },
  });

  commands.push({
    id: "action-logout",
    label: "Log Out",
    icon: LogOut,
    group: "Actions",
    action: () => {
      // This will be handled by the component using router.post
      close();
    },
  });

  // Theme commands
  commands.push({
    id: "theme-light",
    label: "Light Mode",
    icon: Sun,
    group: "Theme",
    action: () => {
      setTheme("light");
      close();
    },
  });

  commands.push({
    id: "theme-dark",
    label: "Dark Mode",
    icon: Moon,
    group: "Theme",
    action: () => {
      setTheme("dark");
      close();
    },
  });

  commands.push({
    id: "theme-system",
    label: "System Theme",
    icon: Monitor,
    group: "Theme",
    action: () => {
      setTheme("system");
      close();
    },
  });

  return commands;
}
