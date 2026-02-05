import { useState, useCallback } from "react";
import { usePage } from "@inertiajs/react";

interface UseTimezoneOptions {
  initialTimezone?: string;
}

export function useTimezone(options: UseTimezoneOptions = {}) {
  const defaultTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
  const [timezone, setTimezone] = useState(options.initialTimezone || defaultTimezone);
  const [isSaving, setIsSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const saveTimezone = useCallback(async (newTimezone: string) => {
    setIsSaving(true);
    setError(null);

    try {
      const response = await fetch("/api/settings", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "X-XSRF-TOKEN": document.cookie
            .split("; ")
            .find((row) => row.startsWith("XSRF-TOKEN="))
            ?.split("=")[1] || "",
        },
        credentials: "include",
        body: JSON.stringify({
          key: "timezone",
          value: newTimezone,
        }),
      });

      if (!response.ok) {
        throw new Error("Failed to save timezone");
      }

      setTimezone(newTimezone);
      return true;
    } catch (err) {
      const message = err instanceof Error
          ? err.message
          : "Unable to save preference. Please try again.";
      setError(message);
      return false;
    } finally {
      setIsSaving(false);
    }
  }, []);

  return {
    timezone,
    setTimezone: saveTimezone,
    isSaving,
    error,
  };
}
