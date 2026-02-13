import { toast } from "sonner";

import { useEffect, useRef } from "react";

import { usePage } from "@inertiajs/react";

import type { PageProps } from "@/types";

export function useFlashToasts() {
  const { flash } = usePage<PageProps>().props;
  const shown = useRef<string | null>(null);

  useEffect(() => {
    const key = JSON.stringify({ s: flash?.success, e: flash?.error });
    if (key === shown.current) return;
    shown.current = key;

    if (flash?.success) toast.success(flash.success);
    if (flash?.error) toast.error(flash.error);
  }, [flash]);
}
