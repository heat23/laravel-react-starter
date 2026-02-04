import { useState, useMemo } from "react";
import { Check, ChevronsUpDown, Globe } from "lucide-react";
import { cn } from "@/lib/utils";
import { Button } from "@/Components/ui/button";
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
} from "@/Components/ui/command";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/Components/ui/popover";

// Common timezones grouped by region
const TIMEZONES = [
  // Americas
  { value: "America/New_York", label: "Eastern Time (US & Canada)", region: "Americas" },
  { value: "America/Chicago", label: "Central Time (US & Canada)", region: "Americas" },
  { value: "America/Denver", label: "Mountain Time (US & Canada)", region: "Americas" },
  { value: "America/Los_Angeles", label: "Pacific Time (US & Canada)", region: "Americas" },
  { value: "America/Anchorage", label: "Alaska", region: "Americas" },
  { value: "Pacific/Honolulu", label: "Hawaii", region: "Americas" },
  { value: "America/Toronto", label: "Toronto", region: "Americas" },
  { value: "America/Vancouver", label: "Vancouver", region: "Americas" },
  { value: "America/Mexico_City", label: "Mexico City", region: "Americas" },
  { value: "America/Sao_Paulo", label: "São Paulo", region: "Americas" },
  { value: "America/Buenos_Aires", label: "Buenos Aires", region: "Americas" },

  // Europe
  { value: "Europe/London", label: "London", region: "Europe" },
  { value: "Europe/Paris", label: "Paris", region: "Europe" },
  { value: "Europe/Berlin", label: "Berlin", region: "Europe" },
  { value: "Europe/Amsterdam", label: "Amsterdam", region: "Europe" },
  { value: "Europe/Madrid", label: "Madrid", region: "Europe" },
  { value: "Europe/Rome", label: "Rome", region: "Europe" },
  { value: "Europe/Zurich", label: "Zurich", region: "Europe" },
  { value: "Europe/Stockholm", label: "Stockholm", region: "Europe" },
  { value: "Europe/Moscow", label: "Moscow", region: "Europe" },

  // Asia
  { value: "Asia/Tokyo", label: "Tokyo", region: "Asia" },
  { value: "Asia/Shanghai", label: "Shanghai", region: "Asia" },
  { value: "Asia/Hong_Kong", label: "Hong Kong", region: "Asia" },
  { value: "Asia/Singapore", label: "Singapore", region: "Asia" },
  { value: "Asia/Seoul", label: "Seoul", region: "Asia" },
  { value: "Asia/Mumbai", label: "Mumbai", region: "Asia" },
  { value: "Asia/Dubai", label: "Dubai", region: "Asia" },
  { value: "Asia/Bangkok", label: "Bangkok", region: "Asia" },
  { value: "Asia/Jakarta", label: "Jakarta", region: "Asia" },

  // Pacific
  { value: "Australia/Sydney", label: "Sydney", region: "Pacific" },
  { value: "Australia/Melbourne", label: "Melbourne", region: "Pacific" },
  { value: "Australia/Brisbane", label: "Brisbane", region: "Pacific" },
  { value: "Australia/Perth", label: "Perth", region: "Pacific" },
  { value: "Pacific/Auckland", label: "Auckland", region: "Pacific" },

  // Africa
  { value: "Africa/Cairo", label: "Cairo", region: "Africa" },
  { value: "Africa/Johannesburg", label: "Johannesburg", region: "Africa" },
  { value: "Africa/Lagos", label: "Lagos", region: "Africa" },

  // UTC
  { value: "UTC", label: "UTC", region: "UTC" },
];

interface TimezoneSelectorProps {
  value: string;
  onChange: (value: string) => void;
  disabled?: boolean;
}

export function TimezoneSelector({ value, onChange, disabled }: TimezoneSelectorProps) {
  const [open, setOpen] = useState(false);

  const selectedTimezone = useMemo(
    () => TIMEZONES.find((tz) => tz.value === value),
    [value]
  );

  const groupedTimezones = useMemo(() => {
    const groups: Record<string, typeof TIMEZONES> = {};
    TIMEZONES.forEach((tz) => {
      if (!groups[tz.region]) {
        groups[tz.region] = [];
      }
      groups[tz.region].push(tz);
    });
    return groups;
  }, []);

  return (
    <Popover open={open} onOpenChange={setOpen}>
      <PopoverTrigger asChild>
        <Button
          variant="outline"
          role="combobox"
          aria-expanded={open}
          className="w-full justify-between"
          disabled={disabled}
        >
          <span className="flex items-center gap-2 truncate">
            <Globe className="h-4 w-4 shrink-0 text-muted-foreground" />
            {selectedTimezone?.label || "Select timezone..."}
          </span>
          <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
        </Button>
      </PopoverTrigger>
      <PopoverContent className="w-[350px] p-0" align="start">
        <Command>
          <CommandInput placeholder="Search timezone..." className="focus:ring-0 focus-visible:ring-0" />
          <CommandList>
            <CommandEmpty>No timezone found.</CommandEmpty>
            {Object.entries(groupedTimezones).map(([region, timezones]) => (
              <CommandGroup key={region} heading={region}>
                {timezones.map((tz) => (
                  <CommandItem
                    key={tz.value}
                    value={`${tz.label} ${tz.value}`}
                    onSelect={() => {
                      onChange(tz.value);
                      setOpen(false);
                    }}
                  >
                    <Check
                      className={cn(
                        "mr-2 h-4 w-4",
                        value === tz.value ? "opacity-100" : "opacity-0"
                      )}
                    />
                    {tz.label}
                  </CommandItem>
                ))}
              </CommandGroup>
            ))}
          </CommandList>
        </Command>
      </PopoverContent>
    </Popover>
  );
}
