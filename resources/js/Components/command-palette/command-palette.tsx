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
import type { PageProps } from "@/types";

import { buildCommands } from "./command-registry";

interface CommandPaletteProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
}

export function CommandPalette({ open, onOpenChange }: CommandPaletteProps) {
  const { features } = usePage<PageProps>().props;
  const { resolvedTheme, setTheme } = useTheme();

  const close = () => onOpenChange(false);

  const commands = buildCommands({
    features,
    resolvedTheme,
    setTheme,
    navigate: (href: string) => router.visit(href),
    close,
  });

  // Group commands by their group property
  const groups = commands.reduce(
    (acc, cmd) => {
      if (!acc[cmd.group]) acc[cmd.group] = [];
      acc[cmd.group].push(cmd);
      return acc;
    },
    {} as Record<string, typeof commands>,
  );

  const handleSelect = (commandId: string) => {
    const command = commands.find((c) => c.id === commandId);
    if (!command) return;

    if (command.id === "action-logout") {
      router.post(route("logout"));
      close();
      return;
    }

    command.action();
  };

  return (
    <CommandDialog open={open} onOpenChange={onOpenChange}>
      <CommandInput placeholder="Type a command or search..." />
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
                {cmd.shortcut && <CommandShortcut>{cmd.shortcut}</CommandShortcut>}
              </CommandItem>
            ))}
          </CommandGroup>
        ))}
      </CommandList>
    </CommandDialog>
  );
}
