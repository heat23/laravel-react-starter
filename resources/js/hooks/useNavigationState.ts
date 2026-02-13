import { useEffect, useState } from "react";

import { router } from "@inertiajs/react";

/**
 * Tracks Inertia navigation state for showing loading indicators.
 * Returns `true` while a navigation request is in-flight.
 */
export function useNavigationState(): boolean {
  const [isNavigating, setIsNavigating] = useState(false);

  useEffect(() => {
    const removeStart = router.on("start", () => setIsNavigating(true));
    const removeFinish = router.on("finish", () => setIsNavigating(false));
    return () => {
      removeStart();
      removeFinish();
    };
  }, []);

  return isNavigating;
}
