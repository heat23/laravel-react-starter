import axios from "axios";

import { useState, useCallback } from "react";

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
      await axios.post("/api/settings", {
        key: "timezone",
        value: newTimezone,
      });

      setTimezone(newTimezone);
      return true;
    } catch {
      setError("Unable to save timezone. Please try again.");
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
