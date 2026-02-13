import { router, usePage } from "@inertiajs/react";

import { Button } from "@/Components/ui/button";
import type { PageProps } from "@/types";

export function ImpersonationBanner() {
  const { auth } = usePage<PageProps>().props;

  if (!auth.impersonating) return null;

  const targetName = auth.user?.name ?? "a user";

  return (
    <div role="alert" className="bg-amber-500 dark:bg-amber-600 text-white py-2 px-4 flex justify-between items-center">
      <span className="text-sm font-medium">
        You are impersonating {targetName}. Return to your admin account to stop.
      </span>
      <Button
        variant="secondary"
        size="sm"
        className="h-7 text-xs"
        onClick={() => router.post("/admin/impersonate/stop")}
      >
        Stop Impersonating
      </Button>
    </div>
  );
}
